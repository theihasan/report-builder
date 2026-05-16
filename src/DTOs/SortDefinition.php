<?php

declare(strict_types=1);

namespace Ihasan\ReportBuilder\DTOs;

use InvalidArgumentException;

class SortDefinition
{
    public function __construct(
        protected string $fieldKey,
        protected string $direction,
    ) {
        if (! in_array($direction, ['asc', 'desc'], true)) {
            throw new InvalidArgumentException('Sort direction must be either "asc" or "desc".');
        }
    }

    public function fieldKey(): string
    {
        return $this->fieldKey;
    }

    public function direction(): string
    {
        return $this->direction;
    }

    /**
     * @return array{field_key: string, direction: string}
     */
    public function toArray(): array
    {
        return [
            'field_key' => $this->fieldKey(),
            'direction' => $this->direction(),
        ];
    }

    /**
     * @param  array{field_key: string, direction: string}  $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            fieldKey: $data['field_key'],
            direction: strtolower($data['direction']),
        );
    }
}
