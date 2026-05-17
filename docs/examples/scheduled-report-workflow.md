# Scheduled Report Workflow Example

Scheduling currently covers persistence + due-discovery primitives.

## 1) Create a saved report

Create a saved report first, then attach schedules to it.

## 2) Create a schedule

```php
<?php

use Ihasan\ReportBuilder\Models\SavedReport;

$savedReport = SavedReport::query()->where('public_id', $publicId)->firstOrFail();

$schedule = $savedReport->schedules()->create([
    'enabled' => true,
    'frequency_type' => 'daily',
    'timezone' => 'UTC',
    'format' => 'csv',
    'recipients' => ['ops@example.com'],
]);
```

## 3) Discover due schedules

```php
<?php

$dueSchedules = app(\Ihasan\ReportBuilder\Scheduling\DueScheduleDiscovery::class)->dueSchedules();
```

Or via command:

```bash
php artisan report-builder:schedules:due
```

## Expected behavior

- Only `enabled` schedules are considered.
- Timezone and custom cron expressions are respected.
- The package does **not** auto-run exports or delivery; your app handles execution + notifications.
