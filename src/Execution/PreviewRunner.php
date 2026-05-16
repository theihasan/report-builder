<?php

declare(strict_types=1);

namespace Ihasan\ReportBuilder\Execution;

use Ihasan\ReportBuilder\DTOs\ReportDefinition;
use Ihasan\ReportBuilder\Query\ReportQueryCompiler;
use Ihasan\ReportBuilder\Support\SourceRegistry;

class PreviewRunner
{
    public function __construct(
        protected ReportQueryCompiler $queryCompiler,
        protected RowMapper $rowMapper,
        protected SourceRegistry $sourceRegistry,
    ) {}

    /**
     * @return array{
     *     columns: array<int, array{field_key: string, output_key: string, label: string, type: string}>,
     *     rows: array<int, array<string, mixed>>,
     *     pagination: array{page: int, per_page: int, total: int, total_pages: int}
     * }
     */
    public function preview(ReportDefinition $definition, int $perPage = 50, int $page = 1): array
    {
        $safePerPage = max(1, $perPage);
        $safePage = max(1, $page);

        $baseQuery = $this->queryCompiler->compile($definition);
        $total = (clone $baseQuery)->toBase()->getCountForPagination();

        $rows = $baseQuery
            ->forPage($safePage, $safePerPage)
            ->get()
            ->map(fn ($row): array => $this->rowMapper->map($row, $definition->selectedColumns()))
            ->values()
            ->all();

        $source = $this->sourceRegistry->source($definition->sourceKey());
        $fieldsByKey = collect($source->fields())->keyBy(static fn ($field): string => $field->key());

        $columns = array_map(function ($selectedColumn) use ($fieldsByKey): array {
            $field = $fieldsByKey->get($selectedColumn->fieldKey());

            return [
                'field_key' => $selectedColumn->fieldKey(),
                'output_key' => $this->rowMapper->outputKey($selectedColumn),
                'label' => $selectedColumn->label() ?? $field->label(),
                'type' => $field->type(),
            ];
        }, $definition->selectedColumns());

        return [
            'columns' => $columns,
            'rows' => $rows,
            'pagination' => [
                'page' => $safePage,
                'per_page' => $safePerPage,
                'total' => $total,
                'total_pages' => (int) ceil($total / $safePerPage),
            ],
        ];
    }
}
