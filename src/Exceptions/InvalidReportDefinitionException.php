<?php

declare(strict_types=1);

namespace Ihasan\ReportBuilder\Exceptions;

use RuntimeException;

class InvalidReportDefinitionException extends RuntimeException
{
    /**
     * @param  array<int, array{path: string, message: string}>  $errors
     */
    public function __construct(protected array $errors)
    {
        parent::__construct('The report definition is invalid.');
    }

    /**
     * @return array<int, array{path: string, message: string}>
     */
    public function errors(): array
    {
        return $this->errors;
    }
}
