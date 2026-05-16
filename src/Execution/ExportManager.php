<?php

declare(strict_types=1);

namespace Ihasan\ReportBuilder\Execution;

use Ihasan\ReportBuilder\Contracts\ExporterContract;
use Ihasan\ReportBuilder\DTOs\ReportDefinition;
use InvalidArgumentException;

class ExportManager
{
    /**
     * @param  iterable<int, ExporterContract>  $exporters
     */
    public function __construct(iterable $exporters)
    {
        foreach ($exporters as $exporter) {
            $this->register($exporter);
        }
    }

    /**
     * @var array<string, ExporterContract>
     */
    private array $exporters = [];

    public function register(ExporterContract $exporter): self
    {
        $this->exporters[strtolower($exporter->format())] = $exporter;

        return $this;
    }

    /**
     * @return array{filename: string, mime_type: string, content: string}
     */
    public function export(ReportDefinition $definition): array
    {
        $format = strtolower($definition->outputDefinition()->format());

        if (! isset($this->exporters[$format])) {
            throw new InvalidArgumentException(sprintf('No exporter registered for format [%s].', $format));
        }

        return $this->exporters[$format]->export($definition);
    }
}
