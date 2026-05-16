<?php

declare(strict_types=1);

namespace Ihasan\ReportBuilder\ReportSources\Fields;

use Ihasan\ReportBuilder\Enums\AggregateFunction;

class RelationAggregateField extends Field
{
    protected bool $filterable = false;

    protected bool $sortable = true;

    protected bool $selectable = true;

    public function __construct(
        string $key,
        protected string $relation,
        protected AggregateFunction $aggregateFunction,
        protected ?string $attribute = null,
    ) {
        parent::__construct($key);
    }

    public static function make(string $key): static
    {
        return new static($key, '', AggregateFunction::Count, null);
    }

    public function countRelation(string $relation): static
    {
        $this->relation = $relation;
        $this->aggregateFunction = AggregateFunction::Count;
        $this->attribute = null;

        return $this;
    }

    public function sumRelation(string $relation, string $attribute): static
    {
        $this->relation = $relation;
        $this->aggregateFunction = AggregateFunction::Sum;
        $this->attribute = $attribute;

        return $this;
    }

    public function avgRelation(string $relation, string $attribute): static
    {
        $this->relation = $relation;
        $this->aggregateFunction = AggregateFunction::Avg;
        $this->attribute = $attribute;

        return $this;
    }

    public function minRelation(string $relation, string $attribute): static
    {
        $this->relation = $relation;
        $this->aggregateFunction = AggregateFunction::Min;
        $this->attribute = $attribute;

        return $this;
    }

    public function maxRelation(string $relation, string $attribute): static
    {
        $this->relation = $relation;
        $this->aggregateFunction = AggregateFunction::Max;
        $this->attribute = $attribute;

        return $this;
    }

    public function relation(): string
    {
        return $this->relation;
    }

    public function aggregateFunction(): AggregateFunction
    {
        return $this->aggregateFunction;
    }

    public function attribute(): ?string
    {
        return $this->attribute;
    }

    public function type(): string
    {
        return 'relation_aggregate';
    }
}
