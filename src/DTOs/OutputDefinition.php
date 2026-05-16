<?php

declare(strict_types=1);

namespace Ihasan\ReportBuilder\DTOs;

class OutputDefinition
{
    public function __construct(
        protected string $format,
        protected ?string $filename = null,
    ) {}

    public function format(): string
    {
        return $this->format;
    }

    public function filename(): ?string
    {
        return $this->filename;
    }

    /**
     * @return array{format: string, filename: ?string}
     */
    public function toArray(): array
    {
        return [
            'format' => $this->format(),
            'filename' => $this->filename(),
        ];
    }

    /**
     * @param  array{format: string, filename?: ?string}  $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            format: $data['format'],
            filename: $data['filename'] ?? null,
        );
    }
}
