<?php

declare(strict_types=1);

namespace Ihasan\ReportBuilder\DTOs;

use InvalidArgumentException;

final class ScheduleDefinition
{
    public function __construct(
        public readonly string $frequencyType,
        public readonly ?string $timezone = 'UTC',
        public readonly ?string $cronExpression = null,
        public readonly ?int $dayOfWeek = null,
    ) {
    }

    public static function daily(?string $timezone = 'UTC'): self
    {
        return new self('daily', $timezone);
    }

    public static function weekly(int $dayOfWeek = 1, ?string $timezone = 'UTC'): self
    {
        return new self('weekly', $timezone, null, $dayOfWeek);
    }

    public static function customCron(string $cronExpression, ?string $timezone = 'UTC'): self
    {
        return new self('custom', $timezone, $cronExpression);
    }

    public static function fromArray(array $data): self
    {
        return new self(
            frequencyType: (string) ($data['frequency_type'] ?? 'custom'),
            timezone: isset($data['timezone']) ? (string) $data['timezone'] : 'UTC',
            cronExpression: isset($data['cron_expression']) ? (string) $data['cron_expression'] : null,
            dayOfWeek: isset($data['day_of_week']) ? (int) $data['day_of_week'] : null,
        );
    }

    public function cronExpression(): string
    {
        return match ($this->frequencyType) {
            'daily' => '0 0 * * *',
            'weekly' => sprintf('0 0 * * %d', $this->dayOfWeek ?? 1),
            'custom' => $this->cronExpression ?? throw new InvalidArgumentException('Custom schedules require cron expression.'),
            default => throw new InvalidArgumentException('Unsupported frequency type: '.$this->frequencyType),
        };
    }

    public function toArray(): array
    {
        return [
            'frequency_type' => $this->frequencyType,
            'timezone' => $this->timezone,
            'cron_expression' => $this->cronExpression,
            'day_of_week' => $this->dayOfWeek,
        ];
    }
}
