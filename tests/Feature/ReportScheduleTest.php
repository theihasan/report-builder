<?php

declare(strict_types=1);

namespace Ihasan\ReportBuilder\Tests\Feature;

use Ihasan\ReportBuilder\Models\ReportSchedule;
use Ihasan\ReportBuilder\Models\SavedReport;
use Ihasan\ReportBuilder\Scheduling\DueScheduleDiscovery;
use Ihasan\ReportBuilder\Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Schema;

class ReportScheduleTest extends TestCase
{
    use RefreshDatabase;

    public function test_report_schedules_table_has_expected_columns(): void
    {
        $this->assertTrue(Schema::hasTable('report_builder_report_schedules'));

        $this->assertTrue(Schema::hasColumns('report_builder_report_schedules', [
            'id',
            'saved_report_id',
            'enabled',
            'frequency_type',
            'cron_expression',
            'timezone',
            'format',
            'recipients',
            'last_run_at',
            'next_run_at',
            'created_at',
            'updated_at',
        ]));
    }

    public function test_it_persists_schedule_configuration(): void
    {
        $savedReport = $this->createSavedReport();

        $schedule = $savedReport->schedules()->create([
            'enabled' => true,
            'frequency_type' => 'daily',
            'timezone' => 'America/New_York',
            'format' => 'csv',
            'recipients' => ['a@example.com', 'b@example.com'],
        ]);

        $schedule->refresh();

        $this->assertTrue($schedule->enabled);
        $this->assertSame(['a@example.com', 'b@example.com'], $schedule->recipients);
        $this->assertSame($savedReport->id, $schedule->saved_report_id);
    }

    public function test_it_identifies_due_enabled_schedules_and_excludes_disabled_ones(): void
    {
        Carbon::setTestNow(Carbon::create(2026, 5, 16, 0, 0, 0, 'UTC'));

        $savedReport = $this->createSavedReport();

        $due = ReportSchedule::query()->create([
            'saved_report_id' => $savedReport->id,
            'enabled' => true,
            'frequency_type' => 'daily',
            'timezone' => 'UTC',
            'format' => 'csv',
        ]);

        ReportSchedule::query()->create([
            'saved_report_id' => $savedReport->id,
            'enabled' => false,
            'frequency_type' => 'daily',
            'timezone' => 'UTC',
            'format' => 'csv',
        ]);

        $dueSchedules = app(DueScheduleDiscovery::class)->dueSchedules();

        $this->assertCount(1, $dueSchedules);
        $this->assertTrue($dueSchedules->first()->is($due));
    }

    public function test_it_supports_custom_cron_due_discovery(): void
    {
        Carbon::setTestNow(Carbon::create(2026, 5, 16, 5, 30, 0, 'UTC'));

        $savedReport = $this->createSavedReport();

        ReportSchedule::query()->create([
            'saved_report_id' => $savedReport->id,
            'enabled' => true,
            'frequency_type' => 'custom',
            'cron_expression' => '30 5 * * *',
            'timezone' => 'UTC',
            'format' => 'xlsx',
        ]);

        $dueSchedules = app(DueScheduleDiscovery::class)->dueSchedules();

        $this->assertCount(1, $dueSchedules);
    }

    private function createSavedReport(): SavedReport
    {
        return SavedReport::query()->create([
            'name' => 'Orders report',
            'public_id' => '0f4e2ec8-f2db-4e0d-9be2-e1d7577c4d6a',
            'source_key' => 'orders',
            'definition' => ['source_key' => 'orders', 'selected_columns' => [['field' => 'id']]],
            'visibility' => 'private',
        ]);
    }
}
