<?php

declare(strict_types=1);

namespace Ihasan\ReportBuilder\Tests\Feature;

use Ihasan\ReportBuilder\DTOs\ReportDefinition;
use Ihasan\ReportBuilder\DTOs\SelectedColumn;
use Ihasan\ReportBuilder\Execution\PreviewRunner;
use Ihasan\ReportBuilder\ReportSources\Fields\NumberField;
use Ihasan\ReportBuilder\ReportSources\Fields\RelationField;
use Ihasan\ReportBuilder\ReportSources\ReportSource;
use Ihasan\ReportBuilder\Support\SourceRegistry;
use Ihasan\ReportBuilder\Tests\Fixtures\RelationCustomerModel;
use Ihasan\ReportBuilder\Tests\Fixtures\RelationOrderModel;
use Ihasan\ReportBuilder\Tests\TestCase;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Schema;

class RelationFieldSupportTest extends TestCase
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
        ]);

        RelationOrderModel::query()->insert([
            ['id' => 10, 'customer_id' => 1, 'total' => 100],
            ['id' => 11, 'customer_id' => null, 'total' => 250],
            ['id' => 12, 'customer_id' => 2, 'total' => 300],
        ]);

        $this->app->make(SourceRegistry::class)->register(new RelationFieldTestSource);
    }

    public function test_relation_field_appears_in_report_output(): void
    {
        $preview = $this->runner()->preview(new ReportDefinition(
            sourceKey: 'relation_orders',
            selectedColumns: [new SelectedColumn('total'), new SelectedColumn('customer.name')],
        ));

        $this->assertSame(['total', 'customer.name'], array_keys($preview['rows'][0]));
        $this->assertSame('Alice', $preview['rows'][0]['customer.name']);
    }

    public function test_missing_relation_returns_null_safely(): void
    {
        $preview = $this->runner()->preview(new ReportDefinition(
            sourceKey: 'relation_orders',
            selectedColumns: [new SelectedColumn('total'), new SelectedColumn('customer.email')],
        ));

        $rowsByTotal = collect($preview['rows'])->keyBy('total');

        $this->assertNull($rowsByTotal[250]['customer.email']);
        $this->assertNull($rowsByTotal[300]['customer.email']);
    }

    public function test_report_definition_serialization_still_uses_only_field_keys(): void
    {
        $definition = new ReportDefinition(
            sourceKey: 'relation_orders',
            selectedColumns: [new SelectedColumn('customer.name')],
        );

        $payload = $definition->toArray();

        $this->assertSame('customer.name', $payload['selected_columns'][0]['field_key']);
        $this->assertArrayNotHasKey('model', $payload['selected_columns'][0]);
        $this->assertArrayNotHasKey('relation', $payload['selected_columns'][0]);
    }

    private function runner(): PreviewRunner
    {
        return $this->app->make(PreviewRunner::class);
    }
}

class RelationFieldTestSource extends ReportSource
{
    public function __construct()
    {
        parent::__construct('relation_orders', 'Relation Orders');
    }

    public function query(): Builder
    {
        return RelationOrderModel::query();
    }

    public function fields(): array
    {
        return [
            NumberField::make('total')->selectable()->filterable()->sortable(),
            RelationField::make('customer.name', 'customer', 'name', 'customer_id'),
            RelationField::make('customer.email', 'customer', 'email', 'customer_id'),
        ];
    }
}
