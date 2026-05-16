<?php

namespace Ihasan\ReportBuilder\Tests\Feature;

use Illuminate\Http\Request;
use Ihasan\ReportBuilder\DataSources\EloquentDataSource;
use Ihasan\ReportBuilder\Enums\AggregateFunction;
use Ihasan\ReportBuilder\Enums\FilterOperator;
use Ihasan\ReportBuilder\ReportBuilder;
use Ihasan\ReportBuilder\Support\Field;
use Ihasan\ReportBuilder\Tests\Fixtures\TestModel;
use Ihasan\ReportBuilder\Tests\TestCase;

class DataSourceMetadataApiTest extends TestCase
{
    public function test_it_lists_only_authorized_sources(): void
    {
        $reportBuilder = $this->app->make(ReportBuilder::class);

        $reportBuilder->registerDataSource($this->makeDataSource(key: 'orders', label: 'Orders'));
        $reportBuilder->registerDataSource($this->makeDataSource(
            key: 'private-orders',
            label: 'Private Orders',
            authorization: static fn (Request $request): bool => false,
        ));

        $response = $this->getJson('/report-builder/sources');

        $response->assertOk();
        $response->assertExactJson([
            'data' => [
                [
                    'key' => 'orders',
                    'label' => 'Orders',
                ],
            ],
        ]);
    }

    public function test_it_returns_safe_metadata_for_an_authorized_source(): void
    {
        $this->app->make(ReportBuilder::class)->registerDataSource(
            $this->makeDataSource(key: 'orders', label: 'Orders')
        );

        $response = $this->getJson('/report-builder/sources/orders');

        $response->assertOk();
        $response->assertJsonPath('data.key', 'orders');
        $response->assertJsonPath('data.label', 'Orders');
        $response->assertJsonPath('data.fields.0.key', 'total_amount');
        $response->assertJsonPath('data.fields.0.filter_operators.0', 'eq');
        $response->assertJsonMissingPath('data.fields.0.column');
        $response->assertJsonMissingPath('data.model');
    }

    public function test_it_rejects_unauthorized_source_detail_requests(): void
    {
        $this->app->make(ReportBuilder::class)->registerDataSource(
            $this->makeDataSource(
                key: 'private-orders',
                label: 'Private Orders',
                authorization: static fn (Request $request): bool => false,
            )
        );

        $response = $this->getJson('/report-builder/sources/private-orders');

        $response->assertForbidden();
    }

    public function test_it_returns_not_found_for_unknown_sources(): void
    {
        $response = $this->getJson('/report-builder/sources/missing');

        $response->assertNotFound();
    }

    protected function makeDataSource(string $key, string $label, ?callable $authorization = null): EloquentDataSource
    {
        return new EloquentDataSource(
            key: $key,
            label: $label,
            model: TestModel::class,
            fields: [
                Field::decimal('total_amount')
                    ->label('Total Amount')
                    ->column('orders.total_amount')
                    ->sortable()
                    ->groupable()
                    ->filterable([
                        FilterOperator::Eq,
                        FilterOperator::Between,
                    ])
                    ->aggregates([
                        AggregateFunction::Sum,
                    ])
                    ->format('currency'),
            ],
            authorization: $authorization,
        );
    }
}
