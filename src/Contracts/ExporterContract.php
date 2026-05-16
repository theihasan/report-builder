<?php

declare(strict_types=1);

namespace Ihasan\ReportBuilder\Contracts;

use Ihasan\ReportBuilder\DTOs\ReportDefinition;

interface ExporterContract
{
    public function format(): string;

    /**
     * @return array{filename: string, mime_type: string, content: string}
     */
    public function export(ReportDefinition $definition): array;
}
