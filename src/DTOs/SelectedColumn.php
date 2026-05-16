<?php

declare(strict_types=1);

namespace Ihasan\ReportBuilder\DTOs;

class SelectedColumn
{
    public function __construct(
        protected string $fieldKey,
        protected ?string $label = null,
        protected ?int $order = null,
        protected bool $visible = true,
    ) {}

    public function fieldKey(): string
    {
        return $this->fieldKey;
    }

    public function label(): ?string
    {
        return $this->label;
    }

    public function order(): ?int
    {
        return $this->order;
    }

    public function visible(): bool
    {
        return $this->visible;
    }

    /**
     * @return array{field_key: string, label: ?string, order: ?int, visible: bool}
     */
    public function toArray(): array
    {
        return [
            'field_key' => $this->fieldKey(),
            'label' => $this->label(),
            'order' => $this->order(),
            'visible' => $this->visible(),
        ];
    }

    /**
     * @param  array{field_key: string, label?: ?string, order?: ?int, visible?: bool}  $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            fieldKey: $data['field_key'],
            label: $data['label'] ?? null,
            order: $data['order'] ?? null,
            visible: $data['visible'] ?? true,
        );
    }
}
