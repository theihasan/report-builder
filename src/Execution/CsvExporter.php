<?php

declare(strict_types=1);

namespace Ihasan\ReportBuilder\Execution;

use Ihasan\ReportBuilder\Contracts\ExporterContract;
use Ihasan\ReportBuilder\DTOs\ReportDefinition;
use RuntimeException;

class CsvExporter implements ExporterContract
{
    public function __construct(
        protected ReportQueryCompilerAdapter $queryCompiler,
        protected RowMapper $rowMapper,
    ) {}

    public function format(): string
    {
        return 'csv';
    }

    /**
     * @return array{filename: string, mime_type: string, content: string}
     */
    public function export(ReportDefinition $definition): array
    {
        $stream = fopen('php://temp', 'w+');

        if ($stream === false) {
            throw new RuntimeException('Unable to create temporary stream for CSV export.');
        }

        $headers = array_map(
            fn ($selectedColumn): string => $this->rowMapper->outputKey($selectedColumn),
            $definition->selectedColumns(),
        );

        fputcsv($stream, $headers);

        $query = $this->queryCompiler->compile($definition);

        foreach ($query->cursor() as $row) {
            $mapped = $this->rowMapper->map($row, $definition->selectedColumns());
            fputcsv($stream, array_values($mapped));
        }

        rewind($stream);
        $content = stream_get_contents($stream);
        fclose($stream);

        if ($content === false) {
            throw new RuntimeException('Unable to read temporary CSV stream.');
        }

        $filename = $definition->outputDefinition()->filename() ?? 'report.csv';

        return [
            'filename' => $filename,
            'mime_type' => 'text/csv; charset=UTF-8',
            'content' => $content,
        ];
    }
}
