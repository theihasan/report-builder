<?php

namespace Ihasan\ReportBuilder\Support;

use Ihasan\ReportBuilder\DTOs\FieldDefinition;
use Ihasan\ReportBuilder\Enums\AggregateFunction;
use Ihasan\ReportBuilder\Enums\FieldType;
use Ihasan\ReportBuilder\Enums\FilterOperator;

class Field
{
    /**
     * @var array<int, FilterOperator|string>
     */
    protected array $filterOperators = [];

    /**
     * @var array<int, AggregateFunction|string>
     */
    protected array $aggregateFunctions = [];

    protected string $label;

    protected string $column;

    protected bool $sortable = false;

    protected bool $groupable = false;

    protected ?string $format = null;

    protected function __construct(protected string $key, protected FieldType $type)
    {
        $this->label = ucwords(str_replace(['-', '_'], ' ', $key));
        $this->column = $key;
    }

    public static function string(string $key): self
    {
        return new self($key, FieldType::String);
    }

    public static function integer(string $key): self
    {
        return new self($key, FieldType::Integer);
    }

    public static function decimal(string $key): self
    {
        return new self($key, FieldType::Decimal);
    }

    public static function boolean(string $key): self
    {
        return new self($key, FieldType::Boolean);
    }

    public static function date(string $key): self
    {
        return new self($key, FieldType::Date);
    }

    public static function dateTime(string $key): self
    {
        return new self($key, FieldType::DateTime);
    }

    public function label(string $label): self
    {
        $this->label = $label;

        return $this;
    }

    public function column(string $column): self
    {
        $this->column = $column;

        return $this;
    }

    public function sortable(bool $sortable = true): self
    {
        $this->sortable = $sortable;

        return $this;
    }

    public function groupable(bool $groupable = true): self
    {
        $this->groupable = $groupable;

        return $this;
    }

    /**
     * @param  array<int, FilterOperator|string>  $operators
     */
    public function filterable(array $operators): self
    {
        $this->filterOperators = $operators;

        return $this;
    }

    /**
     * @param  array<int, AggregateFunction|string>  $aggregates
     */
    public function aggregates(array $aggregates): self
    {
        $this->aggregateFunctions = $aggregates;

        return $this;
    }

    public function format(string $format): self
    {
        $this->format = $format;

        return $this;
    }

    public function build(): FieldDefinition
    {
        return new FieldDefinition(
            key: $this->key,
            label: $this->label,
            type: $this->type,
            column: $this->column,
            sortable: $this->sortable,
            groupable: $this->groupable,
            filterOperators: $this->normalizeFilterOperators(),
            aggregateFunctions: $this->normalizeAggregateFunctions(),
            format: $this->format,
        );
    }

    /**
     * @return array<int, AggregateFunction>
     */
    protected function normalizeAggregateFunctions(): array
    {
        return array_map(
            static fn (AggregateFunction|string $aggregate): AggregateFunction => $aggregate instanceof AggregateFunction
                ? $aggregate
                : AggregateFunction::from($aggregate),
            $this->aggregateFunctions
        );
    }

    /**
     * @return array<int, FilterOperator>
     */
    protected function normalizeFilterOperators(): array
    {
        return array_map(
            static fn (FilterOperator|string $operator): FilterOperator => $operator instanceof FilterOperator
                ? $operator
                : FilterOperator::from($operator),
            $this->filterOperators
        );
    }
}
