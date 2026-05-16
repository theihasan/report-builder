<?php

declare(strict_types=1);

namespace Ihasan\ReportBuilder\ReportSources\Fields;

class RelationField extends Field
{
    protected bool $selectable = true;

    public function __construct(
        string $key,
        protected string $relation,
        protected string $attribute,
        protected string $foreignKey,
    ) {
        parent::__construct($key);
    }

    public static function make(string $key, string $relation, string $attribute, string $foreignKey): static
    {
        return new static($key, $relation, $attribute, $foreignKey);
    }

    public function relation(): string
    {
        return $this->relation;
    }

    public function attribute(): string
    {
        return $this->attribute;
    }

    public function foreignKey(): string
    {
        return $this->foreignKey;
    }

    public function type(): string
    {
        return 'relation';
    }
}
