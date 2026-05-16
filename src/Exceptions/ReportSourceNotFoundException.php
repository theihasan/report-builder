<?php

declare(strict_types=1);

namespace Ihasan\ReportBuilder\Exceptions;

use RuntimeException;

class ReportSourceNotFoundException extends RuntimeException
{
    public static function forKey(string $key): self
    {
        return new self("Report source [{$key}] is not registered.");
    }
}
