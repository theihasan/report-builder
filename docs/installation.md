# Installation

This guide covers the first-time setup for `ihasan/report-builder` in a Laravel app.

## 1) Require the package

```bash
composer require ihasan/report-builder
```

## 2) Confirm supported versions

From the package `composer.json`, the current constraints are:

- PHP: `^8.3`
- Laravel components: `illuminate/contracts ^11.0 || ^12.0 || ^13.0`

In practice, this package is intended for Laravel 11+ projects running PHP 8.3+.

## 3) Auto-discovery

The package includes Laravel auto-discovery metadata (`extra.laravel.providers` and `extra.laravel.aliases`), so you do **not** need to manually register the service provider or facade in most apps.

Provider:

- `Ihasan\ReportBuilder\ReportBuilderServiceProvider`

Alias:

- `ReportBuilder` => `Ihasan\ReportBuilder\Facades\ReportBuilder`

## 4) Publish config (optional)

The package ships with a config file (`report-builder.php`) and registers it with package tools.

Publish it if you want to customize options:

```bash
php artisan vendor:publish --tag="report-builder-config"
```

Then edit values like `preview_limit`, `max_export_rows_sync`, `enable_excel`, and `report_sources` in your app config.

## 5) Publish and run migrations

The package registers migrations for:

- `report_builder`
- `report_builder_saved_reports`
- `report_builder_report_schedules`

Depending on your app workflow, either run package migrations directly with `migrate`, or publish them first.

### Option A: run migrations directly

```bash
php artisan migrate
```

### Option B: publish then run

```bash
php artisan vendor:publish --tag="report-builder-migrations"
php artisan migrate
```

## 6) XLSX export dependency note

The package requires `maatwebsite/excel` and includes an `xlsx` exporter implementation.

Also note the package config has `enable_excel` (default: `false`). If your application checks this flag before exposing XLSX options in your own UI/API layer, set it to `true` in `config/report-builder.php`.

## 7) Verify installation

A quick verification checklist:

1. Run migrations successfully.
2. Confirm package routes exist (for example, list routes and check `report-builder/*` paths).
3. Resolve core services from the container in `php artisan tinker`, such as:
   - `Ihasan\ReportBuilder\Support\SourceRegistry`
   - `Ihasan\ReportBuilder\Execution\ReportRunner`
4. (Optional) run package-related tests in your app if you add integration tests around sources and definitions.

If all of the above pass, the package is installed and ready for your first report source.
