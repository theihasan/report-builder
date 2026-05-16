<?php

declare(strict_types=1);

namespace Ihasan\ReportBuilder\Execution;

use Ihasan\ReportBuilder\DTOs\ReportDefinition;

class ReportRunner
{
    public function __construct(protected PreviewRunner $previewRunner) {}

    /**
     * @return array<string, mixed>
     */
    public function run(ReportDefinition $definition): array
    {
        return $this->previewRunner->preview($definition, perPage: PHP_INT_MAX, page: 1);
    }
}
