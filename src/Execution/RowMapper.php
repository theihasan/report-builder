<?php

declare(strict_types=1);

namespace Ihasan\ReportBuilder\Execution;

use Ihasan\ReportBuilder\DTOs\SelectedColumn;
use Illuminate\Database\Eloquent\Model;

class RowMapper
{
    /**
     * @param  iterable<int, SelectedColumn>  $selectedColumns
     * @return array<string, mixed>
     */
    public function map(Model $row, iterable $selectedColumns): array
    {
        $mapped = [];

        foreach ($selectedColumns as $selectedColumn) {
            $mapped[$this->outputKey($selectedColumn)] = $row->getAttribute($selectedColumn->fieldKey());
        }

        return $mapped;
    }

    public function outputKey(SelectedColumn $selectedColumn): string
    {
        $label = $selectedColumn->label();

        if (is_string($label) && trim($label) !== '') {
            return $label;
        }

        return $selectedColumn->fieldKey();
    }
}
