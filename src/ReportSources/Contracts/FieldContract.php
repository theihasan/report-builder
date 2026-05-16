<?php

declare(strict_types=1);

namespace Ihasan\ReportBuilder\ReportSources\Contracts;

interface FieldContract
{
    public function key(): string;

    public function label(): string;

    public function type(): string;

    public function isFilterable(): bool;

    public function isSortable(): bool;

    public function isSelectable(): bool;
}
