<?php

declare(strict_types=1);

namespace Ihasan\ReportBuilder\Tests\Unit;

use Ihasan\ReportBuilder\DTOs\ScheduleDefinition;
use Ihasan\ReportBuilder\Tests\TestCase;

class ScheduleDefinitionTest extends TestCase
{
    public function test_daily_definition_serialization(): void
    {
        $definition = ScheduleDefinition::daily('UTC');

        $this->assertSame('daily', $definition->toArray()['frequency_type']);
        $this->assertSame('0 0 * * *', $definition->cronExpression());
    }

    public function test_weekly_definition_serialization(): void
    {
        $definition = ScheduleDefinition::weekly(5, 'UTC');

        $this->assertSame(5, $definition->toArray()['day_of_week']);
        $this->assertSame('0 0 * * 5', $definition->cronExpression());
    }

    public function test_custom_cron_definition_serialization(): void
    {
        $definition = ScheduleDefinition::customCron('15 6 * * 1', 'UTC');

        $this->assertSame('15 6 * * 1', $definition->toArray()['cron_expression']);
        $this->assertSame('15 6 * * 1', $definition->cronExpression());
    }
}
