<?php

declare(strict_types=1);

namespace Ihasan\ReportBuilder\Contracts;

use Ihasan\ReportBuilder\ReportSources\Contracts\FieldContract;

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
}
