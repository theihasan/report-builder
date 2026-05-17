# Quickstart

This quickstart walks through a complete first flow using the package's **real** API:

1. Define a report source.
2. Register it in package config.
3. Build a `ReportDefinition`.
4. Preview results.
5. Export CSV/XLSX.
6. (Optional) save the definition.

We'll use a simple `Users` report.

## 1) Create a report source

Create a source class (for example `app/Reports/Sources/UsersReportSource.php`):

```php
<?php

declare(strict_types=1);

namespace App\Reports\Sources;

use App\Models\User;
use Ihasan\ReportBuilder\ReportSources\Fields\DateField;
use Ihasan\ReportBuilder\ReportSources\Fields\TextField;
use Ihasan\ReportBuilder\ReportSources\ReportSource;
use Illuminate\Database\Eloquent\Builder;

class UsersReportSource extends ReportSource
{
    public function __construct()
    {
        parent::__construct('users', 'Users');
    }

    public function query(): Builder
    {
        return User::query();
    }

    public function fields(): array
    {
        return [
            TextField::make('name')->selectable()->sortable()->filterable(),
            TextField::make('email')->selectable()->sortable()->filterable(),
            DateField::make('created_at')->selectable()->sortable()->filterable(),
        ];
    }
}
```

## 2) Register the source

In your app `config/report-builder.php`, add your source class:

```php
'report_sources' => [
    App\Reports\Sources\UsersReportSource::class,
],
```

The package service provider reads this config and registers each source in `SourceRegistry`.

## 3) Build a report definition

Use DTOs to describe selected columns, sorting, and filters.

```php
<?php

use Ihasan\ReportBuilder\DTOs\FilterCondition;
use Ihasan\ReportBuilder\DTOs\FilterGroup;
use Ihasan\ReportBuilder\DTOs\OutputDefinition;
use Ihasan\ReportBuilder\DTOs\ReportDefinition;
use Ihasan\ReportBuilder\DTOs\SelectedColumn;
use Ihasan\ReportBuilder\DTOs\SortDefinition;
use Ihasan\ReportBuilder\Enums\FilterOperator;

$definition = new ReportDefinition(
    sourceKey: 'users',
    selectedColumns: [
        new SelectedColumn('name', 'User Name'),
        new SelectedColumn('email'),
        new SelectedColumn('created_at', 'Joined At'),
    ],
    sortDefinitions: [
        new SortDefinition('created_at', 'desc'),
    ],
    filters: new FilterGroup('and', [
        new FilterCondition('email', FilterOperator::Like, '%@example.com'),
    ]),
    outputDefinition: new OutputDefinition('json'),
);
```

## 4) Preview the report

Resolve `PreviewRunner` from the container:

```php
<?php

use Ihasan\ReportBuilder\Execution\PreviewRunner;

$preview = app(PreviewRunner::class)->preview(
    definition: $definition,
    perPage: 25,
    page: 1,
);
```

`$preview` contains:

- `columns` metadata (`field_key`, `output_key`, `label`, `type`)
- `rows` mapped for output labels
- `pagination` (`page`, `per_page`, `total`, `total_pages`)

## 5) Export the report

Use `ReportRunner`.

### CSV export

```php
<?php

use Ihasan\ReportBuilder\DTOs\OutputDefinition;
use Ihasan\ReportBuilder\Execution\ReportRunner;

$csvDefinition = new ReportDefinition(
    sourceKey: 'users',
    selectedColumns: $definition->selectedColumns(),
    sortDefinitions: $definition->sortDefinitions(),
    filters: $definition->filters(),
    outputDefinition: new OutputDefinition('csv', 'users-report.csv'),
);

$csv = app(ReportRunner::class)->export($csvDefinition);
// ['filename' => 'users-report.csv', 'mime_type' => 'text/csv; charset=UTF-8', 'content' => '...']
```

### XLSX export

```php
<?php

$xlsxDefinition = new ReportDefinition(
    sourceKey: 'users',
    selectedColumns: $definition->selectedColumns(),
    sortDefinitions: $definition->sortDefinitions(),
    filters: $definition->filters(),
    outputDefinition: new OutputDefinition('xlsx', 'users-report.xlsx'),
);

$xlsx = app(ReportRunner::class)->export($xlsxDefinition);
// ['filename' => 'users-report.xlsx', 'mime_type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', 'content' => '...']
```

## 6) Optional: save a definition for reuse

Use `SavedReportRepository` to persist a report definition.

```php
<?php

use Ihasan\ReportBuilder\Persistence\SavedReportRepository;

$saved = app(SavedReportRepository::class)->saveDefinition(
    name: 'Example.com Users',
    definition: $definition,
    createdBy: auth()->id(),
    visibility: 'private',
);
```

Later, load it back:

```php
$loadedDefinition = app(SavedReportRepository::class)->loadDefinition($saved);
```

You now have a full create -> preview -> export -> save flow.
