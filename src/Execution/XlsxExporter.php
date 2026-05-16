<?php

declare(strict_types=1);

namespace Ihasan\ReportBuilder\Execution;

use Ihasan\ReportBuilder\Contracts\ExporterContract;
use Ihasan\ReportBuilder\DTOs\ReportDefinition;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Excel;
use Maatwebsite\Excel\Facades\Excel as ExcelFacade;

class XlsxExporter implements ExporterContract
{
    public function __construct(
        protected ReportQueryCompilerAdapter $queryCompiler,
        protected RowMapper $rowMapper,
    ) {}

    public function format(): string
    {
        return 'xlsx';
    }

    /**
     * @return array{filename: string, mime_type: string, content: string}
     */
    public function export(ReportDefinition $definition): array
    {
        $headings = array_map(
            fn ($selectedColumn): string => $this->rowMapper->outputKey($selectedColumn),
            $definition->selectedColumns(),
        );

        $query = $this->queryCompiler->compile($definition);
        $rows = [];

        foreach ($query->cursor() as $row) {
            $mapped = $this->rowMapper->map($row, $definition->selectedColumns());
            $rows[] = array_values($mapped);
        }

        $content = ExcelFacade::raw(new class($rows, $headings) implements FromArray, WithHeadings
        {
            /**
             * @param  array<int, array<int, mixed>>  $rows
             * @param  array<int, string>  $headings
             */
            public function __construct(
                protected array $rows,
                protected array $headings,
            ) {}

            /**
             * @return array<int, array<int, mixed>>
             */
            public function array(): array
            {
                return $this->rows;
            }

            /**
             * @return array<int, string>
             */
            public function headings(): array
            {
                return $this->headings;
            }
        }, Excel::XLSX);

        $filename = $definition->outputDefinition()->filename() ?? 'report.xlsx';

        return [
            'filename' => $filename,
            'mime_type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'content' => $content,
        ];
    }
}
