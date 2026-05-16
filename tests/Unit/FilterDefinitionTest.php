<?php

declare(strict_types=1);

namespace Ihasan\ReportBuilder\Tests\Unit;

use Ihasan\ReportBuilder\DTOs\FilterCondition;
use Ihasan\ReportBuilder\DTOs\FilterGroup;
use Ihasan\ReportBuilder\Enums\FilterOperator;
use Ihasan\ReportBuilder\Tests\TestCase;
use InvalidArgumentException;

class FilterDefinitionTest extends TestCase
{
    public function test_simple_condition_serialization(): void
    {
        $condition = new FilterCondition('status', FilterOperator::Equals, 'paid');

        $this->assertSame([
            'type' => 'condition',
            'field_key' => 'status',
            'operator' => '=',
            'value' => 'paid',
        ], $condition->toArray());

        $hydrated = FilterCondition::fromArray($condition->toArray());

        $this->assertSame($condition->toArray(), $hydrated->toArray());
    }

    public function test_group_serialization(): void
    {
        $group = new FilterGroup('and', [
            new FilterCondition('status', FilterOperator::Equals, 'paid'),
            new FilterCondition('total', FilterOperator::GreaterThan, 1000),
        ]);

        $hydrated = FilterGroup::fromArray($group->toArray());

        $this->assertSame($group->toArray(), $hydrated->toArray());
    }

    public function test_nested_group_serialization(): void
    {
        $root = new FilterGroup('or', [
            new FilterGroup('and', [
                new FilterCondition('status', FilterOperator::Equals, 'paid'),
                new FilterCondition('total', FilterOperator::GreaterThan, 1000),
            ]),
            new FilterGroup('and', [
                new FilterCondition('status', FilterOperator::Equals, 'pending'),
                new FilterCondition('due_date', FilterOperator::DateBefore, '2026-05-16'),
            ]),
        ]);

        $this->assertSame($root->toArray(), FilterGroup::fromArray($root->toArray())->toArray());
    }

    public function test_invalid_group_boolean_throws_exception(): void
    {
        $this->expectException(InvalidArgumentException::class);

        new FilterGroup('xor');
    }
}
