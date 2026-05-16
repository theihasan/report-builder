<?php

declare(strict_types=1);

namespace Ihasan\ReportBuilder\Execution;

use Ihasan\ReportBuilder\DTOs\ReportDefinition;
use Ihasan\ReportBuilder\Query\ReportQueryCompiler;
use Illuminate\Database\Eloquent\Builder;

class ReportQueryCompilerAdapter
{
    public function __construct(protected ReportQueryCompiler $queryCompiler) {}

    public function compile(ReportDefinition $definition): Builder
    {
        return $this->queryCompiler->compile($definition);
    }
}
