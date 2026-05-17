# Laravel Report Builder Engine

[![Latest Version on Packagist](https://img.shields.io/packagist/v/ihasan/report-builder.svg?style=flat-square)](https://packagist.org/packages/ihasan/report-builder)
[![GitHub Tests Action Status](https://img.shields.io/github/actions/workflow/status/ihasan/report-builder/run-tests.yml?branch=main&label=tests&style=flat-square)](https://github.com/ihasan/report-builder/actions?query=workflow%3Arun-tests+branch%3Amain)
[![GitHub Code Style Action Status](https://img.shields.io/github/actions/workflow/status/ihasan/report-builder/fix-php-code-style-issues.yml?branch=main&label=code%20style&style=flat-square)](https://github.com/ihasan/report-builder/actions?query=workflow%3A"Fix+PHP+code+style+issues"+branch%3Amain)
[![Total Downloads](https://img.shields.io/packagist/dt/ihasan/report-builder.svg?style=flat-square)](https://packagist.org/packages/ihasan/report-builder)

`ihasan/report-builder` is a Laravel package for building safe, source-driven reports from Eloquent queries.

It provides:

- explicit report sources with explicit fields
- validated report definitions (columns, filters, sorts, output format)
- preview execution with pagination metadata
- CSV and XLSX export
- saved report and schedule persistence primitives

## Installation

```bash
composer require ihasan/report-builder
```

See full setup steps in [docs/installation.md](docs/installation.md).

## Quickstart

Define a source, register it, then run a preview.

```php
<?php

use App\Models\User;
use Ihasan\ReportBuilder\DTOs\ReportDefinition;
use Ihasan\ReportBuilder\DTOs\SelectedColumn;
use Ihasan\ReportBuilder\Execution\PreviewRunner;
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
        ];
    }
}

// config/report-builder.php
// 'report_sources' => [App\Reports\Sources\UsersReportSource::class],

$definition = new ReportDefinition(
    sourceKey: 'users',
    selectedColumns: [
        new SelectedColumn('name'),
        new SelectedColumn('email'),
    ],
);

$preview = app(PreviewRunner::class)->preview($definition, perPage: 25, page: 1);
```

For the full first-run flow (including filters, export, and saving definitions), see [docs/quickstart.md](docs/quickstart.md).

## Documentation

- [Installation](docs/installation.md)
- [Quickstart](docs/quickstart.md)
- [Core Concepts](docs/core-concepts.md)
- [Architecture Notes](docs/ARCHITECTURE.md)
- [Documentation Index](docs/index.md)

Examples and recipe docs are not published yet. Add future examples under `docs/examples/` and link them here when available.

## Testing

```bash
composer test
```

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
