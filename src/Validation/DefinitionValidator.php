<?php

declare(strict_types=1);

namespace Ihasan\ReportBuilder\Validation;

use Ihasan\ReportBuilder\DTOs\FilterCondition;
use Ihasan\ReportBuilder\DTOs\FilterGroup;
use Ihasan\ReportBuilder\DTOs\ReportDefinition;
use Ihasan\ReportBuilder\Enums\FilterOperator;
use Ihasan\ReportBuilder\Exceptions\InvalidReportDefinitionException;
use Ihasan\ReportBuilder\Contracts\ReportSourceContract;
use Ihasan\ReportBuilder\Exceptions\ReportSourceNotFoundException;
use Ihasan\ReportBuilder\Support\SourceRegistry;

class DefinitionValidator
{
    public function __construct(protected SourceRegistry $sourceRegistry) {}

    /**
     * @return array<int, array{path: string, message: string}>
     */
    public function validate(ReportDefinition $definition): array
    {
        $errors = [];

        try {
            $source = $this->sourceRegistry->source($definition->sourceKey());
        } catch (ReportSourceNotFoundException) {
            $errors[] = ['path' => 'source_key', 'message' => 'Unknown report source key.'];

            return $errors;
        }

        if (count($definition->selectedColumns()) === 0) {
            $errors[] = ['path' => 'selected_columns', 'message' => 'At least one selected column is required.'];
        }

        foreach ($definition->selectedColumns() as $index => $selectedColumn) {
            $field = $source->field($selectedColumn->fieldKey());

            if ($field === null) {
                $errors[] = ['path' => "selected_columns.{$index}.field_key", 'message' => 'Selected field is not exposed by source.'];

                continue;
            }

            if (! $field->isSelectable()) {
                $errors[] = ['path' => "selected_columns.{$index}.field_key", 'message' => 'Selected field is not selectable.'];
            }
        }

        foreach ($definition->sortDefinitions() as $index => $sort) {
            $field = $source->field($sort->fieldKey());

            if ($field === null) {
                $errors[] = ['path' => "sorts.{$index}.field_key", 'message' => 'Sort field is not exposed by source.'];

                continue;
            }

            if (! $field->isSortable()) {
                $errors[] = ['path' => "sorts.{$index}.field_key", 'message' => 'Sort field is not sortable.'];
            }
        }

        if ($definition->filters() !== null) {
            $this->validateFilterGroup($definition->filters(), $source, 'filters', $errors);
        }

        return $errors;
    }

    public function assertValid(ReportDefinition $definition): void
    {
        $errors = $this->validate($definition);

        if ($errors !== []) {
            throw new InvalidReportDefinitionException($errors);
        }
    }

    /**
     * @param  array<int, array{path: string, message: string}>  $errors
     */
    private function validateFilterGroup(FilterGroup $group, ReportSourceContract $source, string $path, array &$errors): void
    {
        foreach ($group->children() as $index => $child) {
            $childPath = "{$path}.children.{$index}";

            if ($child instanceof FilterGroup) {
                $this->validateFilterGroup($child, $source, $childPath, $errors);

                continue;
            }

            $this->validateFilterCondition($child, $source, $childPath, $errors);
        }
    }

    /**
     * @param  array<int, array{path: string, message: string}>  $errors
     */
    private function validateFilterCondition(FilterCondition $condition, ReportSourceContract $source, string $path, array &$errors): void
    {
        $field = $source->field($condition->fieldKey());

        if ($field === null) {
            $errors[] = ['path' => "{$path}.field_key", 'message' => 'Filter field is not exposed by source.'];

            return;
        }

        if (! $field->isFilterable()) {
            $errors[] = ['path' => "{$path}.field_key", 'message' => 'Filter field is not filterable.'];
        }

        $operator = $condition->operator();
        $value = $condition->value();

        if (in_array($operator, [FilterOperator::IsNull, FilterOperator::IsNotNull, FilterOperator::ThisWeek, FilterOperator::ThisMonth, FilterOperator::ThisYear], true)) {
            return;
        }

        if ($value === null) {
            $errors[] = ['path' => "{$path}.value", 'message' => 'Operator requires a value.'];

            return;
        }

        if (in_array($operator, [FilterOperator::Between, FilterOperator::NotBetween], true)) {
            if (! is_array($value) || count($value) !== 2) {
                $errors[] = ['path' => "{$path}.value", 'message' => 'Between operators require exactly two values.'];
            }

            return;
        }

        if (in_array($operator, [FilterOperator::In, FilterOperator::NotIn], true) && ! is_array($value)) {
            $errors[] = ['path' => "{$path}.value", 'message' => 'In operators require array-like values.'];

            return;
        }

        if ($operator === FilterOperator::LastNDays && (! is_int($value) || $value <= 0)) {
            $errors[] = ['path' => "{$path}.value", 'message' => 'last_n_days requires a positive integer value.'];
        }
    }
}
