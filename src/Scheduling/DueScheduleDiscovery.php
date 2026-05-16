<?php

declare(strict_types=1);

namespace Ihasan\ReportBuilder\Scheduling;

use Cron\CronExpression;
use Ihasan\ReportBuilder\DTOs\ScheduleDefinition;
use Ihasan\ReportBuilder\Models\ReportSchedule;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

class DueScheduleDiscovery
{
    /**
     * @return Collection<int, ReportSchedule>
     */
    public function dueSchedules(?Carbon $referenceTime = null): Collection
    {
        $referenceTime ??= Carbon::now('UTC');

        return ReportSchedule::query()
            ->with('savedReport')
            ->enabled()
            ->get()
            ->filter(fn (ReportSchedule $schedule): bool => $this->isDue($schedule, $referenceTime))
            ->values();
    }

    public function isDue(ReportSchedule $schedule, Carbon $referenceTime): bool
    {
        $timezone = $schedule->timezone ?: 'UTC';
        $nowInScheduleTz = $referenceTime->copy()->setTimezone($timezone);

        $expression = ScheduleDefinition::fromArray([
            'frequency_type' => $schedule->frequency_type,
            'timezone' => $schedule->timezone,
            'cron_expression' => $schedule->cron_expression,
        ])->cronExpression();

        $cron = new CronExpression($expression);

        return $cron->isDue($nowInScheduleTz);
    }
}
