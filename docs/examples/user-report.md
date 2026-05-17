# User Report Example

This example shows a complete **scalar-field report** flow for users: select columns, apply filters/sorts, preview, and export.

## Goal

Build a report for account managers that lists users with:

- name
- email
- status
- joined date

## 1) Define a source

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
            TextField::make('status')->selectable()->sortable()->filterable(),
            DateField::make('created_at')->selectable()->sortable()->filterable(),
        ];
    }
}
```

Register this source in `config/report-builder.php` under `report_sources`.

## 2) Build selected columns + filter + sort

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
        new SelectedColumn('status'),
        new SelectedColumn('created_at', 'Joined At'),
    ],
    filters: new FilterGroup('and', [
        new FilterCondition('status', FilterOperator::Equal, 'active'),
        new FilterCondition('email', FilterOperator::Like, '%@example.com'),
    ]),
    sortDefinitions: [
        new SortDefinition('created_at', 'desc'),
    ],
    outputDefinition: new OutputDefinition('json'),
);
```

## 3) Preview

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

## 4) Export (CSV/XLSX)

```php
<?php

use Ihasan\ReportBuilder\DTOs\OutputDefinition;
use Ihasan\ReportBuilder\DTOs\ReportDefinition;
use Ihasan\ReportBuilder\Execution\ReportRunner;

$csvExport = app(ReportRunner::class)->export(new ReportDefinition(
    sourceKey: $definition->sourceKey(),
    selectedColumns: $definition->selectedColumns(),
    sortDefinitions: $definition->sortDefinitions(),
    filters: $definition->filters(),
    outputDefinition: new OutputDefinition('csv', 'users-report.csv'),
));

$xlsxExport = app(ReportRunner::class)->export(new ReportDefinition(
    sourceKey: $definition->sourceKey(),
    selectedColumns: $definition->selectedColumns(),
    sortDefinitions: $definition->sortDefinitions(),
    filters: $definition->filters(),
    outputDefinition: new OutputDefinition('xlsx', 'users-report.xlsx'),
));
```

Both payloads return `filename`, `mime_type`, and `content`.
