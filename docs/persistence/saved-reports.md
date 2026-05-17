# Saved Reports

A saved report is a persisted `ReportDefinition` snapshot plus metadata (name, visibility, creator, stable public id, source key) stored in `report_builder_saved_reports`.

## What is stored

## Metadata columns

- `id`: internal primary key
- `name`: display name for the saved report
- `public_id`: UUID safe for external references
- `source_key`: registered report source key (not model class names)
- `created_by`: optional user id
- `visibility`: e.g. `private`
- `created_at`, `updated_at`

## JSON definition column

- `definition` stores the serialized `ReportDefinition::toArray()` payload.
- This includes selected columns and other report-definition configuration.

Repository methods:

- `saveDefinition(...)`
- `loadDefinition(SavedReport $savedReport): ReportDefinition`
- `updateDefinition(SavedReport $savedReport, ReportDefinition $definition)`

## Save a report definition

```php
use Ihasan\ReportBuilder\DTOs\ReportDefinition;
use Ihasan\ReportBuilder\DTOs\SelectedColumn;
use Ihasan\ReportBuilder\Persistence\SavedReportRepository;

$repository = app(SavedReportRepository::class);

$definition = new ReportDefinition(
    sourceKey: 'orders',
    selectedColumns: [
        new SelectedColumn('order_number'),
        new SelectedColumn('status', 'State'),
    ],
);

$saved = $repository->saveDefinition(
    name: 'Orders Report',
    definition: $definition,
    createdBy: 9,
    visibility: 'private',
);
```

## Load it back

```php
$loadedDefinition = $repository->loadDefinition($saved);

// Equivalent shape to original definition payload
$payload = $loadedDefinition->toArray();
```

`loadDefinition` safely supports raw JSON or cast array input and throws `InvalidArgumentException` for malformed stored JSON.

## Update saved definition

```php
use Ihasan\ReportBuilder\DTOs\ReportDefinition;
use Ihasan\ReportBuilder\DTOs\SelectedColumn;

$updatedDefinition = new ReportDefinition(
    sourceKey: 'orders',
    selectedColumns: [
        new SelectedColumn('order_number'),
        new SelectedColumn('status'),
        new SelectedColumn('amount'),
    ],
);

$updatedSaved = $repository->updateDefinition($saved, $updatedDefinition);
```

This updates both:

- `source_key`
- `definition` JSON payload

## Full realistic workflow

```php
use Ihasan\ReportBuilder\DTOs\OutputDefinition;
use Ihasan\ReportBuilder\Execution\ReportRunner;

// 1) Build definition and persist it.
$saved = $repository->saveDefinition('Orders Export', $definition, createdBy: 9);

// 2) Retrieve record later (e.g., by public id).
$saved = \Ihasan\ReportBuilder\Models\SavedReport::query()
    ->where('public_id', $saved->public_id)
    ->firstOrFail();

// 3) Rehydrate definition object.
$loaded = $repository->loadDefinition($saved);

// 4) Optionally modify and persist a new version.
$loaded = new \Ihasan\ReportBuilder\DTOs\ReportDefinition(
    sourceKey: $loaded->sourceKey(),
    selectedColumns: $loaded->selectedColumns(),
    outputDefinition: new OutputDefinition('csv', 'orders-export.csv'),
);
$repository->updateDefinition($saved, $loaded);

// 5) Export using the loaded/updated definition.
$export = app(ReportRunner::class)->export($loaded);
```

## Limitations

- The repository does not version definitions; updates overwrite the current snapshot.
- No built-in soft deletes/audit trail on saved reports.
