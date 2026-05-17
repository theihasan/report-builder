# Saved Report Workflow Example

This workflow persists a report definition and reuses it later.

## 1) Save a definition

```php
<?php

use Ihasan\ReportBuilder\DTOs\ReportDefinition;
use Ihasan\ReportBuilder\DTOs\SelectedColumn;
use Ihasan\ReportBuilder\Persistence\SavedReportRepository;

$repository = app(SavedReportRepository::class);

$definition = new ReportDefinition(
    sourceKey: 'orders',
    selectedColumns: [
        new SelectedColumn('order_number'),
        new SelectedColumn('status'),
        new SelectedColumn('total_amount'),
    ],
);

$saved = $repository->saveDefinition(
    name: 'Ops Orders Snapshot',
    definition: $definition,
    createdBy: auth()->id(),
    visibility: 'private',
);
```

## 2) Load and reuse

```php
<?php

$loaded = $repository->loadDefinition($saved);

$preview = app(\Ihasan\ReportBuilder\Execution\PreviewRunner::class)
    ->preview($loaded, perPage: 25, page: 1);

$export = app(\Ihasan\ReportBuilder\Execution\ReportRunner::class)
    ->export(new ReportDefinition(
        sourceKey: $loaded->sourceKey(),
        selectedColumns: $loaded->selectedColumns(),
        sortDefinitions: $loaded->sortDefinitions(),
        filters: $loaded->filters(),
        outputDefinition: new \Ihasan\ReportBuilder\DTOs\OutputDefinition('csv', 'ops-orders.csv'),
    ));
```

Saved definitions persist `source_key` and report payload, avoiding raw model class persistence.
