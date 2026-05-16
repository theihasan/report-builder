<?php

declare(strict_types=1);

namespace Ihasan\ReportBuilder\DTOs;

use InvalidArgumentException;

class FilterGroup
{
    /**
     * @param  array<int, FilterCondition|FilterGroup>  $children
     */
    public function __construct(
        protected string $boolean = 'and',
        protected array $children = [],
    ) {
        $normalized = strtolower($this->boolean);

        if (! in_array($normalized, ['and', 'or'], true)) {
            throw new InvalidArgumentException('Filter group boolean must be either "and" or "or".');
        }

        $this->boolean = $normalized;
    }

    public function boolean(): string
    {
        return $this->boolean;
    }

    /**
     * @return array<int, FilterCondition|FilterGroup>
     */
    public function children(): array
    {
        return $this->children;
    }

    public function addChild(FilterCondition|FilterGroup $child): self
    {
        $this->children[] = $child;

        return $this;
    }

    /**
     * @return array{type: string, boolean: string, children: array<int, array<string, mixed>>}
     */
    public function toArray(): array
    {
        return [
            'type' => 'group',
            'boolean' => $this->boolean(),
            'children' => array_map(
                static fn (FilterCondition|FilterGroup $child): array => $child->toArray(),
                $this->children()
            ),
        ];
    }

    /**
     * @param  array{boolean: string, children?: array<int, array<string, mixed>>}  $data
     */
    public static function fromArray(array $data): self
    {
        $group = new self(boolean: $data['boolean']);

        foreach ($data['children'] ?? [] as $child) {
            $group->addChild(self::hydrateChild($child));
        }

        return $group;
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private static function hydrateChild(array $data): FilterCondition|self
    {
        if (($data['type'] ?? null) === 'group') {
            return self::fromArray($data);
        }

        return FilterCondition::fromArray($data);
    }
}
