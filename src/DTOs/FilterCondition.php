<?php

declare(strict_types=1);

namespace Ihasan\ReportBuilder\DTOs;

use Ihasan\ReportBuilder\Enums\FilterOperator;

class FilterCondition
{
    public function __construct(
        protected string $fieldKey,
        protected FilterOperator $operator,
        protected mixed $value = null,
    ) {}

    public function fieldKey(): string
    {
        return $this->fieldKey;
    }

    public function operator(): FilterOperator
    {
        return $this->operator;
    }

    public function value(): mixed
    {
        return $this->value;
    }

    /**
     * @return array{type: string, field_key: string, operator: string, value: mixed}
     */
    public function toArray(): array
    {
        return [
            'type' => 'condition',
            'field_key' => $this->fieldKey(),
            'operator' => $this->operator()->value,
            'value' => $this->value(),
        ];
    }

    /**
     * @param  array{field_key: string, operator: string, value?: mixed}  $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            fieldKey: $data['field_key'],
            operator: FilterOperator::from($data['operator']),
            value: $data['value'] ?? null,
        );
    }
}
