<?php

namespace Ihasan\ReportBuilder\DTOs;

use Ihasan\ReportBuilder\Enums\AggregateFunction;
use Ihasan\ReportBuilder\Enums\FieldType;
use Ihasan\ReportBuilder\Enums\FilterOperator;

class FieldDefinition
{
    /**
     * @param  array<int, FilterOperator>  $filterOperators
     * @param  array<int, AggregateFunction>  $aggregateFunctions
     */
    public function __construct(
        protected string $key,
        protected string $label,
        protected FieldType $type,
        protected string $column,
        protected bool $sortable = false,
        protected bool $groupable = false,
        protected array $filterOperators = [],
        protected array $aggregateFunctions = [],
        protected ?string $format = null,
    ) {}

    public function key(): string
    {
        return $this->key;
    }

    public function label(): string
    {
        return $this->label;
    }

    public function type(): FieldType
    {
        return $this->type;
    }

    public function column(): string
    {
        return $this->column;
    }

    public function sortable(): bool
    {
        return $this->sortable;
    }

    public function groupable(): bool
    {
        return $this->groupable;
    }

    /**
     * @return array<int, FilterOperator>
     */
    public function filterOperators(): array
    {
        return $this->filterOperators;
    }

    /**
     * @return array<int, AggregateFunction>
     */
    public function aggregateFunctions(): array
    {
        return $this->aggregateFunctions;
    }

    public function format(): ?string
    {
        return $this->format;
    }

    /**
     * @return array{
     *     key: string,
     *     label: string,
     *     type: string,
     *     sortable: bool,
     *     groupable: bool,
     *     filter_operators: array<int, string>,
     *     aggregate_functions: array<int, string>,
     *     format: ?string
     * }
     */
    public function toArray(): array
    {
        return [
            'key' => $this->key(),
            'label' => $this->label(),
            'type' => $this->type()->value,
            'sortable' => $this->sortable(),
            'groupable' => $this->groupable(),
            'filter_operators' => array_map(
                static fn (FilterOperator $operator): string => $operator->value,
                $this->filterOperators()
            ),
            'aggregate_functions' => array_map(
                static fn (AggregateFunction $aggregate): string => $aggregate->value,
                $this->aggregateFunctions()
            ),
            'format' => $this->format(),
        ];
    }
}
