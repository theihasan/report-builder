# Scheduling

Scheduling is implemented at persistence/discovery level. The package includes:

- `ReportSchedule` model (`report_builder_report_schedules` table)
- `DueScheduleDiscovery` service for identifying currently due schedules
- `report-builder:schedules:due` command for printing due schedules

There is no built-in delivery/execution pipeline that automatically runs and sends exports for due schedules.

## Data model

Each schedule belongs to a `SavedReport` and stores:

- `enabled`
- `frequency_type` (`daily`, `weekly`, `monthly`, `custom`, etc. as interpreted by `ScheduleDefinition`)
- `cron_expression` (for custom frequency)
- `timezone`
- `format` (`csv` / `xlsx` etc.)
- `recipients` JSON array
- `last_run_at`, `next_run_at` timestamps (nullable)

## Create or update schedules

Use the public Eloquent relationship from `SavedReport`:

```php
use Ihasan\ReportBuilder\Models\SavedReport;

$savedReport = SavedReport::query()->where('public_id', $publicId)->firstOrFail();

$schedule = $savedReport->schedules()->create([
    'enabled' => true,
    'frequency_type' => 'daily',
    'timezone' => 'America/New_York',
    'format' => 'csv',
    'recipients' => ['ops@example.com', 'finance@example.com'],
]);

$schedule->update([
    'enabled' => false,
    'frequency_type' => 'custom',
    'cron_expression' => '30 5 * * *',
    'timezone' => 'UTC',
]);
```

## Due schedule discovery

```php
use Ihasan\ReportBuilder\Scheduling\DueScheduleDiscovery;
use Illuminate\Support\Carbon;

$dueSchedules = app(DueScheduleDiscovery::class)
    ->dueSchedules(referenceTime: Carbon::now('UTC'));
```

Discovery behavior:

- only enabled schedules are considered
- timezone is respected per schedule
- cron expressions are resolved using `ScheduleDefinition` + `CronExpression`

## Command

```bash
php artisan report-builder:schedules:due
```

The command prints due schedule identifiers and associated saved report ids.

## What the package handles vs app responsibilities

### Package handles

- schedule persistence schema/model
- due-time evaluation
- command to inspect due schedules

### Consuming app must handle

- executing exports for each due schedule
- recipient delivery (email, webhook, storage links, etc.)
- updating `last_run_at` / `next_run_at` lifecycle fields in your own execution flow
- wiring Laravel scheduler/queue infrastructure

## Limitations

- No automatic schedule processor job is provided.
- No built-in schedule API endpoints/repository abstractions beyond model-level usage.
