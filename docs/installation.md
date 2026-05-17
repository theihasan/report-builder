# Installation

This guide helps you install `ihasan/report-builder` in a Laravel project for the first time.

## 1) Install with Composer

```bash
composer require ihasan/report-builder
```

## 2) Check version requirements

From the package `composer.json`:

- PHP: `^8.3`
- Laravel components: `illuminate/contracts ^11.0 || ^12.0 || ^13.0`

In practical terms, install this package in a Laravel 11+ app running PHP 8.3+.

## 3) Confirm package auto-discovery

The package includes Laravel auto-discovery metadata, so you usually **do not** manually register anything:

- Service provider: `Ihasan\\ReportBuilder\\ReportBuilderServiceProvider`
- Facade alias: `ReportBuilder`

If your app has auto-discovery enabled (default), installation is enough.

## 4) Publish configuration (optional)

A config file is included (`config/report-builder.php`). Publish it only if you want to customize defaults:

```bash
php artisan vendor:publish --tag="report-builder-config"
```

Useful keys to review right away:

- `report_sources` (where you register source classes)
- `preview_limit`
- `max_export_rows_sync`
- `exports_disk`
- `enable_excel`

## 5) Publish and run migrations

This package ships migrations for:

- `report_builder`
- `report_builder_saved_reports`
- `report_builder_report_schedules`

You can either run migrations directly or publish them first.

### Option A: run directly

```bash
php artisan migrate
```

### Option B: publish then run

```bash
php artisan vendor:publish --tag="report-builder-migrations"
php artisan migrate
```

## 6) XLSX dependency note

The package requires `maatwebsite/excel` and includes an XLSX exporter (`XlsxExporter`) out of the box.

Also note:

- Config includes `enable_excel` (default `false`).
- Export logic exists regardless; this flag is useful for deciding whether your own app UI/API should expose XLSX choices.

## 7) Verify installation

After setup, run this checklist:

1. `php artisan migrate` finishes without errors.
2. `php artisan route:list | grep report-builder` shows package API routes.
3. In `php artisan tinker`, these resolve successfully:
   - `app(Ihasan\\ReportBuilder\\Support\\SourceRegistry::class)`
   - `app(Ihasan\\ReportBuilder\\Execution\\PreviewRunner::class)`
   - `app(Ihasan\\ReportBuilder\\Execution\\ReportRunner::class)`

If all three pass, you are ready for your first report source.
