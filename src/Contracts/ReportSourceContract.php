<?php

declare(strict_types=1);

namespace Ihasan\ReportBuilder\Contracts;

use Ihasan\ReportBuilder\ReportSources\Contracts\FieldContract;
use Illuminate\Database\Eloquent\Builder;

interface ReportSourceContract
{
    public function key(): string;

    public function label(): string;

    /**
     * @return array<int, FieldContract>
     */
    public function fields(): array;

    public function hasField(string $key): bool;

    public function field(string $key): ?FieldContract;

    public function query(): Builder;
}
