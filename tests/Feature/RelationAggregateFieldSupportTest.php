<?php

declare(strict_types=1);

namespace Ihasan\ReportBuilder\Tests\Feature;

use Ihasan\ReportBuilder\DTOs\FilterCondition;
use Ihasan\ReportBuilder\DTOs\FilterGroup;
use Ihasan\ReportBuilder\DTOs\ReportDefinition;
use Ihasan\ReportBuilder\DTOs\SelectedColumn;
use Ihasan\ReportBuilder\DTOs\SortDefinition;
use Ihasan\ReportBuilder\Enums\FilterOperator;
use Ihasan\ReportBuilder\Execution\PreviewRunner;
use Ihasan\ReportBuilder\ReportSources\Fields\RelationAggregateField;
use Ihasan\ReportBuilder\ReportSources\Fields\TextField;
use Ihasan\ReportBuilder\ReportSources\ReportSource;
use Ihasan\ReportBuilder\Support\SourceRegistry;
use Ihasan\ReportBuilder\Tests\Fixtures\RelationCustomerModel;
use Ihasan\ReportBuilder\Tests\Fixtures\RelationOrderModel;
use Ihasan\ReportBuilder\Tests\TestCase;
use Ihasan\ReportBuilder\Validation\DefinitionValidator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Schema;

class RelationAggregateFieldSupportTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Schema::dropIfExists('report_builder_relation_orders');
        Schema::dropIfExists('report_builder_relation_customers');

        Schema::create('report_builder_relation_customers', function ($table): void {
            $table->id();
            $table->string('name');
            $table->string('email')->nullable();
        });

        Schema::create('report_builder_relation_orders', function ($table): void {
            $table->id();
            $table->unsignedBigInteger('customer_id')->nullable();
            $table->integer('total');
        });

        RelationCustomerModel::query()->insert([
            ['id' => 1, 'name' => 'Alice', 'email' => 'alice@example.com'],
            ['id' => 2, 'name' => 'Bob', 'email' => null],
            ['id' => 3, 'name' => 'Carla', 'email' => 'carla@example.com'],
        ]);

        RelationOrderModel::query()->insert([
            ['id' => 10, 'customer_id' => 1, 'total' => 100],
            ['id' => 11, 'customer_id' => 1, 'total' => 250],
            ['id' => 12, 'customer_id' => 2, 'total' => 300],
            ['id' => 13, 'customer_id' => null, 'total' => 75],
        ]);

        $this->app->make(SourceRegistry::class)->register(new RelationAggregateFieldTestSource);
    }

    public function test_count_relation_field_works(): void
    {
        $preview = $this->runner()->preview(new ReportDefinition(
            sourceKey: 'relation_customers',
            selectedColumns: [new SelectedColumn('name'), new SelectedColumn('orders_count')],
        ));

        $rowsByName = collect($preview['rows'])->keyBy('name');

        $this->assertSame(2, $rowsByName['Alice']['orders_count']);
        $this->assertSame(1, $rowsByName['Bob']['orders_count']);
    }

    public function test_sum_relation_field_works_and_empty_related_records_are_zero(): void
    {
        $preview = $this->runner()->preview(new ReportDefinition(
            sourceKey: 'relation_customers',
            selectedColumns: [new SelectedColumn('name'), new SelectedColumn('orders_total_sum')],
        ));

        $rowsByName = collect($preview['rows'])->keyBy('name');

        $this->assertSame(350, $rowsByName['Alice']['orders_total_sum']);
        $this->assertSame(300, $rowsByName['Bob']['orders_total_sum']);
        $this->assertSame(0, $rowsByName['Carla']['orders_total_sum']);
    }

    public function test_sort_by_aggregate_field_works(): void
    {
        $preview = $this->runner()->preview(new ReportDefinition(
            sourceKey: 'relation_customers',
            selectedColumns: [new SelectedColumn('name'), new SelectedColumn('orders_count')],
            sortDefinitions: [new SortDefinition('orders_count', 'desc')],
        ));

        $this->assertSame(['Alice', 'Bob', 'Carla'], array_column($preview['rows'], 'name'));
    }

    public function test_report_definition_with_aggregate_fields_serializes_and_validates(): void
    {
        $definition = new ReportDefinition(
            sourceKey: 'relation_customers',
            selectedColumns: [new SelectedColumn('orders_count')],
            sortDefinitions: [new SortDefinition('orders_total_sum', 'desc')],
        );

        $payload = $definition->toArray();

        $this->assertSame('orders_count', $payload['selected_columns'][0]['field_key']);
        $this->assertSame('orders_total_sum', $payload['sorts'][0]['field_key']);

        $errors = $this->app->make(DefinitionValidator::class)->validate($definition);

        $this->assertSame([], $errors);
    }

    public function test_filtering_by_aggregate_field_is_rejected_with_validation_error(): void
    {
        $definition = new ReportDefinition(
            sourceKey: 'relation_customers',
            selectedColumns: [new SelectedColumn('name')],
            filters: new FilterGroup('and', [
                new FilterCondition('orders_count', FilterOperator::GreaterThan, 1),
            ]),
        );

        $errors = $this->app->make(DefinitionValidator::class)->validate($definition);

        $this->assertSame('Filter field is not filterable.', $errors[0]['message']);
    }

    private function runner(): PreviewRunner
    {
        return $this->app->make(PreviewRunner::class);
    }
}

class RelationAggregateFieldTestSource extends ReportSource
{
    public function __construct()
    {
        parent::__construct('relation_customers', 'Relation Customers');
    }

    public function query(): Builder
    {
        return RelationCustomerModel::query();
    }

    public function fields(): array
    {
        return [
            TextField::make('name')->selectable()->sortable(),
            RelationAggregateField::make('orders_count')->countRelation('orders')->selectable()->sortable(),
            RelationAggregateField::make('orders_total_sum')->sumRelation('orders', 'total')->selectable()->sortable(),
        ];
    }
}
