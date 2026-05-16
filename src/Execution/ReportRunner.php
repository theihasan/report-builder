<?php

declare(strict_types=1);

namespace Ihasan\ReportBuilder\Execution;

use Ihasan\ReportBuilder\DTOs\ReportDefinition;

class ReportRunner
{
    public function __construct(
        protected PreviewRunner $previewRunner,
        protected ExportManager $exportManager,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function run(ReportDefinition $definition): array
    {
        return $this->previewRunner->preview($definition, perPage: PHP_INT_MAX, page: 1);
    }

    /**
     * @return array{filename: string, mime_type: string, content: string}
     */
    public function export(ReportDefinition $definition): array
    {
        return $this->exportManager->export($definition);
    }
}
