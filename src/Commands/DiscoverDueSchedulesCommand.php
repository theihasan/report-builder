<?php

declare(strict_types=1);

namespace Ihasan\ReportBuilder\Commands;

use Ihasan\ReportBuilder\Scheduling\DueScheduleDiscovery;
use Illuminate\Console\Command;

class DiscoverDueSchedulesCommand extends Command
{
    protected $signature = 'report-builder:schedules:due';

    protected $description = 'Discover due report schedules';

    public function __construct(private readonly DueScheduleDiscovery $discovery)
    {
        parent::__construct();
    }

    public function handle(): int
    {
        $dueSchedules = $this->discovery->dueSchedules();

        if ($dueSchedules->isEmpty()) {
            $this->info('No due schedules found.');

            return self::SUCCESS;
        }

        $this->info(sprintf('Found %d due schedule(s):', $dueSchedules->count()));

        foreach ($dueSchedules as $schedule) {
            $this->line(sprintf(
                '- schedule_id=%d saved_report_id=%d format=%s',
                $schedule->id,
                $schedule->saved_report_id,
                $schedule->format,
            ));
        }

        return self::SUCCESS;
    }
}
