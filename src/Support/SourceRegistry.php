<?php

declare(strict_types=1);

namespace Ihasan\ReportBuilder\Support;

use Ihasan\ReportBuilder\Contracts\ReportSourceContract;
use Ihasan\ReportBuilder\Exceptions\ReportSourceNotFoundException;

class SourceRegistry
{
    /**
     * @var array<string, ReportSourceContract>
     */
    protected array $sources = [];

    public function register(ReportSourceContract $source): void
    {
        $this->sources[$source->key()] = $source;
    }

    /**
     * @param  iterable<ReportSourceContract>  $sources
     */
    public function registerMany(iterable $sources): void
    {
        foreach ($sources as $source) {
            $this->register($source);
        }
    }

    public function source(string $key): ReportSourceContract
    {
        return $this->sources[$key] ?? throw ReportSourceNotFoundException::forKey($key);
    }

    /**
     * @return array<int, string>
     */
    public function keys(): array
    {
        return array_keys($this->sources);
    }

    /**
     * @return array<int, ReportSourceContract>
     */
    public function all(): array
    {
        return array_values($this->sources);
    }
}
