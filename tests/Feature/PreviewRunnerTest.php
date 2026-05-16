<?php

declare(strict_types=1);

namespace Ihasan\ReportBuilder\Tests\Feature;

use Ihasan\ReportBuilder\DTOs\ReportDefinition;
use Ihasan\ReportBuilder\DTOs\SelectedColumn;
use Ihasan\ReportBuilder\DTOs\SortDefinition;
use Ihasan\ReportBuilder\Execution\PreviewRunner;
use Ihasan\ReportBuilder\ReportSources\Fields\DateField;
use Ihasan\ReportBuilder\ReportSources\Fields\NumberField;
use Ihasan\ReportBuilder\ReportSources\Fields\TextField;
use Ihasan\ReportBuilder\ReportSources\ReportSource;
use Ihasan\ReportBuilder\Support\SourceRegistry;
use Ihasan\ReportBuilder\Tests\Fixtures\TestModel;
use Ihasan\ReportBuilder\Tests\TestCase;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Schema;

class PreviewRunnerTest extends TestCase
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

        $this->app->make(SourceRegistry::class)->register(new PreviewRunnerTestSource);
    }

    public function test_it_returns_expected_preview_payload_shape(): void
    {
        $preview = $this->runner()->preview(new ReportDefinition(
            sourceKey: 'orders',
            selectedColumns: [new SelectedColumn('name'), new SelectedColumn('amount')],
            sortDefinitions: [new SortDefinition('amount', 'desc')],
        ));

        $this->assertSame(['field_key', 'output_key', 'label', 'type'], array_keys($preview['columns'][0]));
        $this->assertSame(['columns', 'rows', 'pagination'], array_keys($preview));
        $this->assertSame(['name', 'amount'], array_keys($preview['rows'][0]));
    }

    public function test_it_applies_custom_labels_only_in_output_mapping(): void
    {
        $preview = $this->runner()->preview(new ReportDefinition(
            sourceKey: 'orders',
            selectedColumns: [new SelectedColumn('name', 'Customer Name')],
        ));

        $this->assertSame('Customer Name', $preview['columns'][0]['output_key']);
        $this->assertSame('Customer Name', $preview['columns'][0]['label']);
        $this->assertSame(['Customer Name'], array_keys($preview['rows'][0]));
    }

    public function test_it_keeps_stable_output_column_ordering(): void
    {
        $preview = $this->runner()->preview(new ReportDefinition(
            sourceKey: 'orders',
            selectedColumns: [
                new SelectedColumn('status', 'State'),
                new SelectedColumn('name'),
                new SelectedColumn('amount', 'Amount'),
            ],
        ));

        $this->assertSame(['State', 'name', 'Amount'], array_keys($preview['rows'][0]));
        $this->assertSame(['status', 'name', 'amount'], array_column($preview['columns'], 'field_key'));
    }

    public function test_it_supports_row_limit_and_pagination(): void
    {
        $definition = new ReportDefinition(
            sourceKey: 'orders',
            selectedColumns: [new SelectedColumn('name')],
            sortDefinitions: [new SortDefinition('amount', 'desc')],
        );

        $pageOne = $this->runner()->preview($definition, perPage: 2, page: 1);
        $pageTwo = $this->runner()->preview($definition, perPage: 2, page: 2);

        $this->assertCount(2, $pageOne['rows']);
        $this->assertSame([['name' => 'Gamma'], ['name' => 'Alpha']], $pageOne['rows']);
        $this->assertSame([['name' => 'Beta']], $pageTwo['rows']);
        $this->assertSame(['page' => 1, 'per_page' => 2, 'total' => 3, 'total_pages' => 2], $pageOne['pagination']);
    }

    private function runner(): PreviewRunner
    {
        return $this->app->make(PreviewRunner::class);
    }
}

class PreviewRunnerTestSource extends ReportSource
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
