<?php

declare(strict_types=1);

namespace Ihasan\ReportBuilder\Contracts;

interface ReportSourceContract
{
    public function key(): string;

    public function label(): string;
}
