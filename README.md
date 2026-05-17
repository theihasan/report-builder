# Laravel Report Builder Engine

`ihasan/report-builder` is a **headless Laravel reporting engine** for building safe, source-driven reports (preview + export) from explicit field definitions.

[![Latest Version on Packagist](https://img.shields.io/packagist/v/ihasan/report-builder.svg?style=flat-square)](https://packagist.org/packages/ihasan/report-builder)
[![GitHub Tests Action Status](https://img.shields.io/github/actions/workflow/status/ihasan/report-builder/run-tests.yml?branch=main&label=tests&style=flat-square)](https://github.com/ihasan/report-builder/actions?query=workflow%3Arun-tests+branch%3Amain)
[![Total Downloads](https://img.shields.io/packagist/dt/ihasan/report-builder.svg?style=flat-square)](https://packagist.org/packages/ihasan/report-builder)

## Why this package

Most internal reporting needs are the same:

- controlled field exposure (not unrestricted model introspection)
- reusable report definitions (columns, filters, sorts, format)
- fast preview in UI/API flows
- file export for operations/finance stakeholders

This package provides those primitives while keeping your app in control of UI, delivery, and orchestration.

## Features

- explicit report sources keyed by stable `source_key`
- explicit scalar, relation, and relation-aggregate fields
- definition validation for selected columns, filters, and sorting
- paginated preview execution with metadata
- built-in CSV and XLSX exporters
- saved report persistence (`SavedReportRepository`)
- schedule persistence + due discovery primitives

## Installation

```bash
composer require ihasan/report-builder
```

Then follow [docs/installation.md](docs/installation.md).

## Quickstart (minimal)

```php
<?php

use App\Models\User;
use Ihasan\ReportBuilder\DTOs\ReportDefinition;
use Ihasan\ReportBuilder\DTOs\SelectedColumn;
use Ihasan\ReportBuilder\Execution\PreviewRunner;
use Ihasan\ReportBuilder\ReportSources\Fields\TextField;
use Ihasan\ReportBuilder\ReportSources\ReportSource;
use Illuminate\Database\Eloquent\Builder;

class UsersSource extends ReportSource
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
        ];
    }
}

$definition = new ReportDefinition(
    sourceKey: 'users',
    selectedColumns: [new SelectedColumn('name'), new SelectedColumn('email')],
);

$preview = app(PreviewRunner::class)->preview($definition, perPage: 25, page: 1);
```

## Example output shape

Preview responses include column metadata, rows, and pagination:

```php
[
    'columns' => [
        ['field_key' => 'name', 'output_key' => 'name', 'label' => 'name', 'type' => 'text'],
        ['field_key' => 'email', 'output_key' => 'email', 'label' => 'email', 'type' => 'text'],
    ],
    'rows' => [
        ['name' => 'Alice', 'email' => 'alice@example.com'],
        ['name' => 'Bob', 'email' => 'bob@example.com'],
    ],
    'pagination' => [
        'page' => 1,
        'per_page' => 25,
        'total' => 2,
        'total_pages' => 1,
    ],
]
```

## Documentation

- [Documentation Index](docs/index.md)
- [Installation](docs/installation.md)
- [Quickstart](docs/quickstart.md)
- [Core Concepts](docs/core-concepts.md)
- [Creating Report Definitions](docs/reports/creating-report-definitions.md)
- [Validation](docs/reports/validation.md)
- [Saved Reports](docs/persistence/saved-reports.md)
- [Scheduling](docs/persistence/scheduling.md)
- [Troubleshooting](docs/troubleshooting.md)
- [FAQ](docs/faq.md)

## Examples

- [User Report](docs/examples/user-report.md)
- [Orders Report](docs/examples/orders-report.md)
- [Customer Revenue Report](docs/examples/customer-revenue-report.md)
- [Relation Field Report](docs/examples/relation-field-report.md)
- [Aggregate Report Recipe](docs/examples/aggregate-report.md)
- [Saved Report Workflow](docs/examples/saved-report-workflow.md)
- [Scheduled Report Workflow](docs/examples/scheduled-report-workflow.md)

## Current capabilities

- Headless reporting primitives (no bundled UI)
- Source-key based report definitions
- Explicit field modeling, including relation and aggregate fields
- Definition validation + preview execution
- CSV/XLSX export payload generation
- Saved report and schedule persistence models
- Due schedule discovery command (`report-builder:schedules:due`)

## Not yet supported (by design)

- bundled frontend UI components
- automatic schedule processing/delivery pipeline
- unrestricted automatic schema discovery from models
- arbitrary model-class persistence inside report definitions

## Testing

```bash
composer test
```

## Changelog

See [CHANGELOG.md](CHANGELOG.md).

## License

MIT. See [LICENSE.md](LICENSE.md).
