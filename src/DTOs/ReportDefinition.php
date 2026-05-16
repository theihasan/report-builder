<?php

declare(strict_types=1);

namespace Ihasan\ReportBuilder\DTOs;

class ReportDefinition
{
    /**
     * @param  array<int, SelectedColumn>  $selectedColumns
     * @param  array<int, SortDefinition>  $sortDefinitions
     */
    public function __construct(
        protected string $sourceKey,
        protected array $selectedColumns = [],
        protected array $sortDefinitions = [],
        protected OutputDefinition $outputDefinition = new OutputDefinition('json'),
        protected int $version = 1,
    ) {}

    public function sourceKey(): string
    {
        return $this->sourceKey;
    }

    /**
     * @return array<int, SelectedColumn>
     */
    public function selectedColumns(): array
    {
        return $this->selectedColumns;
    }

    /**
     * @return array<int, SortDefinition>
     */
    public function sortDefinitions(): array
    {
        return $this->sortDefinitions;
    }

    public function outputDefinition(): OutputDefinition
    {
        return $this->outputDefinition;
    }

    public function version(): int
    {
        return $this->version;
    }

    public function addSelectedColumn(SelectedColumn $selectedColumn): self
    {
        $this->selectedColumns[] = $selectedColumn;

        return $this;
    }

    public function addSortDefinition(SortDefinition $sortDefinition): self
    {
        $this->sortDefinitions[] = $sortDefinition;

        return $this;
    }

    /**
     * @return array{
     *     source_key: string,
     *     selected_columns: array<int, array{field_key: string, label: ?string, order: ?int, visible: bool}>,
     *     sorts: array<int, array{field_key: string, direction: string}>,
     *     output: array{format: string, filename: ?string},
     *     version: int
     * }
     */
    public function toArray(): array
    {
        return [
            'source_key' => $this->sourceKey(),
            'selected_columns' => array_map(
                static fn (SelectedColumn $column): array => $column->toArray(),
                $this->selectedColumns()
            ),
            'sorts' => array_map(
                static fn (SortDefinition $sort): array => $sort->toArray(),
                $this->sortDefinitions()
            ),
            'output' => $this->outputDefinition()->toArray(),
            'version' => $this->version(),
        ];
    }

    /**
     * @param  array{
     *     source_key: string,
     *     selected_columns?: array<int, array{field_key: string, label?: ?string, order?: ?int, visible?: bool}>,
     *     sorts?: array<int, array{field_key: string, direction: string}>,
     *     output?: array{format: string, filename?: ?string},
     *     version?: int
     * }  $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            sourceKey: $data['source_key'],
            selectedColumns: array_map(
                static fn (array $column): SelectedColumn => SelectedColumn::fromArray($column),
                $data['selected_columns'] ?? []
            ),
            sortDefinitions: array_map(
                static fn (array $sort): SortDefinition => SortDefinition::fromArray($sort),
                $data['sorts'] ?? []
            ),
            outputDefinition: OutputDefinition::fromArray($data['output'] ?? ['format' => 'json']),
            version: $data['version'] ?? 1,
        );
    }

    public function toJson(): string
    {
        return (string) json_encode($this->toArray(), JSON_THROW_ON_ERROR);
    }

    public static function fromJson(string $json): self
    {
        /** @var array<string, mixed> $decoded */
        $decoded = (array) json_decode($json, true, 512, JSON_THROW_ON_ERROR);

        return self::fromArray($decoded);
    }
}
