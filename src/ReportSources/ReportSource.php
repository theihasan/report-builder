<?php

declare(strict_types=1);

namespace Ihasan\ReportBuilder\ReportSources;

use Ihasan\ReportBuilder\Contracts\ReportSourceContract;

abstract class ReportSource implements ReportSourceContract
{
    public function __construct(
        protected readonly string $key,
        protected readonly string $label,
    ) {}

    public function key(): string
    {
        return $this->key;
    }

    public function label(): string
    {
        return $this->label;
    }
}
