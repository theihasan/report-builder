<?php

declare(strict_types=1);

namespace Ihasan\ReportBuilder\Tests\Feature;

use Ihasan\ReportBuilder\DTOs\FilterCondition;
use Ihasan\ReportBuilder\DTOs\FilterGroup;
use Ihasan\ReportBuilder\DTOs\ReportDefinition;
use Ihasan\ReportBuilder\DTOs\SelectedColumn;
use Ihasan\ReportBuilder\DTOs\SortDefinition;
use Ihasan\ReportBuilder\Enums\FilterOperator;
use Ihasan\ReportBuilder\Exceptions\InvalidReportDefinitionException;
use Ihasan\ReportBuilder\Query\ReportQueryCompiler;
use Ihasan\ReportBuilder\ReportSources\Fields\DateField;
use Ihasan\ReportBuilder\ReportSources\Fields\NumberField;
use Ihasan\ReportBuilder\ReportSources\Fields\TextField;
use Ihasan\ReportBuilder\ReportSources\ReportSource;
use Ihasan\ReportBuilder\Support\SourceRegistry;
use Ihasan\ReportBuilder\Tests\Fixtures\TestModel;
use Ihasan\ReportBuilder\Tests\TestCase;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Schema;

class ReportQueryCompilerTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Schema::dropIfExists('report_builder_test_models');
        Schema::create('report_builder_test_models', function ($table): void {
            $table->id();
            $table->string('name');
            $table->string('status')->nullable();
            $table->integer('amount');
            $table->dateTime('created_at')->nullable();
        });

        TestModel::query()->insert([
            ['name' => 'Alpha', 'status' => 'paid', 'amount' => 120, 'created_at' => '2026-05-14 10:00:00'],
            ['name' => 'Beta', 'status' => 'pending', 'amount' => 50, 'created_at' => '2026-05-10 10:00:00'],
            ['name' => 'Gamma', 'status' => null, 'amount' => 300, 'created_at' => '2026-01-12 10:00:00'],
        ]);

        $registry = $this->app->make(SourceRegistry::class);
        $registry->register(new QueryCompilerTestSource);
    }

    public function test_it_selects_only_requested_direct_fields(): void
    {
        $results = $this->compiler()->compile(new ReportDefinition(
            sourceKey: 'orders',
            selectedColumns: [new SelectedColumn('name')],
        ))->get();

        $this->assertArrayHasKey('name', $results->first()->getAttributes());
        $this->assertArrayNotHasKey('amount', $results->first()->getAttributes());
    }

    public function test_it_applies_basic_filters_nested_groups_and_sorting(): void
    {
        $definition = new ReportDefinition(
            sourceKey: 'orders',
            selectedColumns: [new SelectedColumn('name'), new SelectedColumn('amount')],
            sortDefinitions: [new SortDefinition('amount', 'desc')],
            filters: new FilterGroup('and', [
                new FilterCondition('amount', FilterOperator::GreaterThan, 40),
                new FilterGroup('or', [
                    new FilterCondition('status', FilterOperator::Equals, 'paid'),
                    new FilterCondition('status', FilterOperator::IsNull),
                ]),
            ]),
        );

        $rows = $this->compiler()->compile($definition)->get();

        $this->assertSame(['Gamma', 'Alpha'], $rows->pluck('name')->all());
    }

    public function test_it_compiles_and_executes_date_relative_filters(): void
    {
        Carbon::setTestNow('2026-05-16 12:00:00');

        $rows = $this->compiler()->compile(new ReportDefinition(
            sourceKey: 'orders',
            selectedColumns: [new SelectedColumn('name')],
            filters: new FilterGroup('and', [
                new FilterCondition('created_at', FilterOperator::ThisWeek),
            ]),
        ))->get();

        $this->assertSame(['Alpha'], $rows->pluck('name')->all());

        $rows = $this->compiler()->compile(new ReportDefinition(
            sourceKey: 'orders',
            selectedColumns: [new SelectedColumn('name')],
            filters: new FilterGroup('and', [
                new FilterCondition('created_at', FilterOperator::LastNDays, 7),
            ]),
        ))->get();

        $this->assertSame(['Alpha', 'Beta'], $rows->pluck('name')->sort()->values()->all());
    }

    public function test_invalid_definition_fails_before_compilation(): void
    {
        $this->expectException(InvalidReportDefinitionException::class);

        $this->compiler()->compile(new ReportDefinition(
            sourceKey: 'orders',
            selectedColumns: [new SelectedColumn('missing_field')],
        ));
    }

    private function compiler(): ReportQueryCompiler
    {
        return $this->app->make(ReportQueryCompiler::class);
    }
}

class QueryCompilerTestSource extends ReportSource
{
    public function __construct()
    {
        parent::__construct('orders', 'Orders');
    }

    public function query(): Builder
    {
        return TestModel::query();
    }

    public function fields(): array
    {
        return [
            TextField::make('name')->selectable()->filterable()->sortable(),
            TextField::make('status')->selectable()->filterable()->sortable(),
            NumberField::make('amount')->selectable()->filterable()->sortable(),
            DateField::make('created_at')->selectable()->filterable()->sortable(),
        ];
    }
}
