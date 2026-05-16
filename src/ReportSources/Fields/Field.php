<?php

declare(strict_types=1);

namespace Ihasan\ReportBuilder\ReportSources\Fields;

use Ihasan\ReportBuilder\ReportSources\Contracts\FieldContract;

abstract class Field implements FieldContract
{
    protected string $label;

    protected bool $filterable = false;

    protected bool $sortable = false;

    protected bool $selectable = true;

    final public function __construct(protected string $key)
    {
        $this->label = ucwords(str_replace(['_', '-'], ' ', $key));
    }

    public static function make(string $key): static
    {
        return new static($key);
    }

    public function key(): string
    {
        return $this->key;
    }

    public function label(): string
    {
        return $this->label;
    }

    public function isFilterable(): bool
    {
        return $this->filterable;
    }

    public function isSortable(): bool
    {
        return $this->sortable;
    }

    public function isSelectable(): bool
    {
        return $this->selectable;
    }

    public function labelAs(string $label): static
    {
        $this->label = $label;

        return $this;
    }

    public function label(string $label): static
    {
        return $this->labelAs($label);
    }

    public function filterable(bool $filterable = true): static
    {
        $this->filterable = $filterable;

        return $this;
    }

    public function sortable(bool $sortable = true): static
    {
        $this->sortable = $sortable;

        return $this;
    }

    public function selectable(bool $selectable = true): static
    {
        $this->selectable = $selectable;

        return $this;
    }
}
