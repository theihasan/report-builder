<?php

namespace Ihasan\ReportBuilder\Support;

use Ihasan\ReportBuilder\Contracts\DataSourceContract;
use Ihasan\ReportBuilder\Exceptions\DataSourceAlreadyRegisteredException;
use Ihasan\ReportBuilder\Exceptions\DataSourceNotFoundException;
use Illuminate\Http\Request;

class DataSourceRegistry
{
    /**
     * @var array<string, DataSourceContract>
     */
    protected array $sources = [];

    public function register(DataSourceContract $dataSource): void
    {
        if (array_key_exists($dataSource->key(), $this->sources)) {
            throw DataSourceAlreadyRegisteredException::forKey($dataSource->key());
        }

        $this->sources[$dataSource->key()] = $dataSource;
    }

    /**
     * @return array<int, DataSourceContract>
     */
    public function all(): array
    {
        return array_values($this->sources);
    }

    /**
     * @return array<int, DataSourceContract>
     */
    public function authorized(Request $request): array
    {
        return array_values(array_filter(
            $this->sources,
            static fn (DataSourceContract $dataSource): bool => $dataSource->authorize($request)
        ));
    }

    public function source(string $key): DataSourceContract
    {
        return $this->sources[$key] ?? throw DataSourceNotFoundException::forKey($key);
    }
}
