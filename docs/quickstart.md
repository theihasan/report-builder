# Quickstart

This tutorial walks through a complete first flow using the package's existing APIs:

1. Define a `UsersReportSource`
2. Register it in config
3. Build a `ReportDefinition`
4. Validate + preview it
5. Export CSV/XLSX
6. Optionally save the definition

## 1) Define a report source

Create `app/Reports/Sources/UsersReportSource.php`:

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

In your app's `config/report-builder.php`:

```php
'report_sources' => [
    App\Reports\Sources\UsersReportSource::class,
],
```

The service provider reads this config and registers each class in `SourceRegistry`.

## 3) Build a report definition

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

## 4) Validate and preview

```php
<?php

use Ihasan\ReportBuilder\Execution\PreviewRunner;
use Ihasan\ReportBuilder\Validation\DefinitionValidator;

app(DefinitionValidator::class)->assertValid($definition);

$preview = app(PreviewRunner::class)->preview(
    definition: $definition,
    perPage: 25,
    page: 1,
);
```

`$preview` contains:

- `columns` (`field_key`, `output_key`, `label`, `type`)
- `rows` (mapped row output)
- `pagination` (`page`, `per_page`, `total`, `total_pages`)

## 5) Export the same report

Use `ReportRunner::export()` with different output definitions.

### CSV

```php
<?php

use Ihasan\ReportBuilder\DTOs\OutputDefinition;
use Ihasan\ReportBuilder\DTOs\ReportDefinition;
use Ihasan\ReportBuilder\Execution\ReportRunner;

$csvDefinition = new ReportDefinition(
    sourceKey: $definition->sourceKey(),
    selectedColumns: $definition->selectedColumns(),
    sortDefinitions: $definition->sortDefinitions(),
    filters: $definition->filters(),
    outputDefinition: new OutputDefinition('csv', 'users-report.csv'),
);

$csv = app(ReportRunner::class)->export($csvDefinition);
```

### XLSX

```php
<?php

$xlsxDefinition = new ReportDefinition(
    sourceKey: $definition->sourceKey(),
    selectedColumns: $definition->selectedColumns(),
    sortDefinitions: $definition->sortDefinitions(),
    filters: $definition->filters(),
    outputDefinition: new OutputDefinition('xlsx', 'users-report.xlsx'),
);

$xlsx = app(ReportRunner::class)->export($xlsxDefinition);
```

Both exports return:

- `filename`
- `mime_type`
- `content`

## 6) Optional: save for reuse

```php
<?php

use Ihasan\ReportBuilder\Persistence\SavedReportRepository;

$savedReport = app(SavedReportRepository::class)->saveDefinition(
    name: 'Example Users',
    definition: $definition,
    createdBy: auth()->id(),
    visibility: 'private',
);

$loadedDefinition = app(SavedReportRepository::class)->loadDefinition($savedReport);
```

You now have a full source -> definition -> preview -> export -> save flow.
