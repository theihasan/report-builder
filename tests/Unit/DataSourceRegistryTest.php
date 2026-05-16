<?php

namespace Ihasan\ReportBuilder\Tests\Unit;

use Ihasan\ReportBuilder\DataSources\EloquentDataSource;
use Ihasan\ReportBuilder\Enums\AggregateFunction;
use Ihasan\ReportBuilder\Enums\FilterOperator;
use Ihasan\ReportBuilder\Exceptions\DataSourceAlreadyRegisteredException;
use Ihasan\ReportBuilder\Exceptions\DataSourceNotFoundException;
use Ihasan\ReportBuilder\Support\DataSourceRegistry;
use Ihasan\ReportBuilder\Support\Field;
use Ihasan\ReportBuilder\Tests\Fixtures\TestModel;
use Ihasan\ReportBuilder\Tests\TestCase;

class DataSourceRegistryTest extends TestCase
{
    public function test_it_registers_and_resolves_a_data_source(): void
    {
        $registry = new DataSourceRegistry();
        $dataSource = $this->makeDataSource();

        $registry->register($dataSource);

        $resolved = $registry->source('orders');

        $this->assertSame('orders', $resolved->key());
        $this->assertSame('Orders', $resolved->label());
        $this->assertCount(1, $registry->all());
    }

    public function test_it_rejects_duplicate_source_keys(): void
    {
        $registry = new DataSourceRegistry();
        $registry->register($this->makeDataSource());

        $this->expectException(DataSourceAlreadyRegisteredException::class);

        $registry->register($this->makeDataSource());
    }

    public function test_it_throws_for_an_unknown_source_key(): void
    {
        $registry = new DataSourceRegistry();

        $this->expectException(DataSourceNotFoundException::class);

        $registry->source('missing');
    }

    public function test_it_builds_safe_public_field_metadata(): void
    {
        $dataSource = $this->makeDataSource();
        $field = $dataSource->field('total_amount');

        $this->assertNotNull($field);
        $this->assertSame('orders.total_amount', $field->column());
        $this->assertSame([
            'key' => 'total_amount',
            'label' => 'Total Amount',
            'type' => 'decimal',
            'sortable' => true,
            'groupable' => true,
            'filter_operators' => ['eq', 'gt', 'between'],
            'aggregate_functions' => ['sum', 'avg'],
            'format' => 'currency',
        ], $field->toArray());
        $this->assertArrayNotHasKey('column', $field->toArray());
    }

    protected function makeDataSource(): EloquentDataSource
    {
        return new EloquentDataSource(
            key: 'orders',
            label: 'Orders',
            model: TestModel::class,
            fields: [
                Field::decimal('total_amount')
                    ->label('Total Amount')
                    ->column('orders.total_amount')
                    ->sortable()
                    ->groupable()
                    ->filterable([
                        FilterOperator::Eq,
                        FilterOperator::Gt,
                        FilterOperator::Between,
                    ])
                    ->aggregates([
                        AggregateFunction::Sum,
                        AggregateFunction::Avg,
                    ])
                    ->format('currency'),
            ],
        );
    }
}
