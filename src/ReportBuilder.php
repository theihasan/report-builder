<?php

namespace Ihasan\ReportBuilder;

use Illuminate\Http\Request;
use Ihasan\ReportBuilder\Contracts\DataSourceContract;
use Ihasan\ReportBuilder\Support\DataSourceRegistry;

class ReportBuilder
{
    public function __construct(protected DataSourceRegistry $dataSourceRegistry) {}

    public function registerDataSource(DataSourceContract $dataSource): void
    {
        $this->dataSourceRegistry->register($dataSource);
    }

    public function dataSource(string $key): DataSourceContract
    {
        return $this->dataSourceRegistry->source($key);
    }

    /**
     * @return array<int, DataSourceContract>
     */
    public function dataSources(): array
    {
        return $this->dataSourceRegistry->all();
    }

    /**
     * @return array<int, DataSourceContract>
     */
    public function authorizedDataSources(Request $request): array
    {
        return $this->dataSourceRegistry->authorized($request);
    }
}
