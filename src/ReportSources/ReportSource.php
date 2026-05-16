<?php

declare(strict_types=1);

namespace Ihasan\ReportBuilder\ReportSources;

use Ihasan\ReportBuilder\Contracts\ReportSourceContract;
use Ihasan\ReportBuilder\ReportSources\Contracts\FieldContract;

abstract class ReportSource implements ReportSourceContract
{
    public function __construct(
        protected readonly string $key,
        protected readonly string $label,
    ) {}

    public function key(): string
    {
        return $this->key;
    }

    public function label(): string
    {
        return $this->label;
    }

    /**
     * @return array<int, FieldContract>
     */
    public function fields(): array
    {
        return [];
    }

    public function hasField(string $key): bool
    {
        return $this->field($key) !== null;
    }

    public function field(string $key): ?FieldContract
    {
        foreach ($this->fields() as $field) {
            if ($field->key() === $key) {
                return $field;
            }
        }

        return null;
    }
}
