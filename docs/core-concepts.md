# Core Concepts

This package is centered around a few explicit building blocks. Once you understand them, the rest of the API feels straightforward.

## Report Source

A **Report Source** is the root definition of where report data comes from.

A source must provide:

- a stable key (`key()`), like `users` or `orders`
- a human label (`label()`)
- an Eloquent base query (`query()`)
- an explicit list of available fields (`fields()`)

You usually create it by extending `Ihasan\ReportBuilder\ReportSources\ReportSource`.

```php
class OrdersReportSource extends ReportSource
{
    public function __construct()
    {
        parent::__construct('orders', 'Orders');
    }

    public function query(): Builder
    {
        return Order::query();
    }
}
```

## Field

A **Field** is a safe, explicit column-like unit that a source exposes.

Built-in field classes include:

- `TextField`
- `NumberField`
- `DateField`
- `BooleanField`
- `MoneyField`
- relation-aware fields:
  - `RelationField`
  - `RelationAggregateField`

Each field has a key and capabilities like selectable/sortable/filterable.

```php
TextField::make('status')->selectable()->sortable()->filterable();
NumberField::make('amount')->selectable()->sortable()->filterable();
```

## Report Definition

A **Report Definition** is the serializable blueprint of a report run (`DTOs\ReportDefinition`).

It includes:

- `sourceKey`
- selected columns (`SelectedColumn[]`)
- sorts (`SortDefinition[]`)
- optional filters (`FilterGroup`)
- output (`OutputDefinition`, e.g. json/csv/xlsx)

```php
$definition = new ReportDefinition(
    sourceKey: 'orders',
    selectedColumns: [new SelectedColumn('amount')],
    outputDefinition: new OutputDefinition('csv'),
);
```

## Filter Group / Condition

Filters are tree-based:

- `FilterCondition`: one rule (`field_key`, operator, value)
- `FilterGroup`: combines conditions/groups with `and` or `or`

```php
new FilterGroup('and', [
    new FilterCondition('amount', FilterOperator::GreaterThan, 100),
    new FilterGroup('or', [
        new FilterCondition('status', FilterOperator::Equals, 'paid'),
        new FilterCondition('status', FilterOperator::IsNull),
    ]),
]);
```

## Validator

`Validation\DefinitionValidator` checks that a definition is valid for a registered source.

Examples of what it enforces:

- source key exists
- selected columns refer to defined fields
- sort fields are sortable
- filter fields/operators are valid for the field
- relation/aggregate constraints are respected

`ReportQueryCompiler` uses this validation before compiling queries.

## Query Compiler

`Query\ReportQueryCompiler` transforms a validated `ReportDefinition` into an Eloquent query:

- applies safe field selection
- applies filters through `FilterCompiler`
- applies sorts
- handles relation and aggregate field compilation

You can call it directly for advanced use cases, or use higher-level runners.

## Preview Runner

`Execution\PreviewRunner` executes a paginated preview and returns structured payload data:

- `columns` metadata
- output-mapped `rows`
- `pagination`

This is the usual API for interactive report previews.

## Exporter

Exports are managed through:

- `Execution\ExportManager` (format-to-exporter routing)
- `Execution\ReportRunner::export()` (easy entry point)

Built-in exporters:

- `csv` via `CsvExporter`
- `xlsx` via `XlsxExporter`

The export result payload is:

- `filename`
- `mime_type`
- `content` (file bytes/string)

## Saved Report

A **Saved Report** stores report definitions for later reuse.

Persistence is handled by `Persistence\SavedReportRepository`, backed by `Models\SavedReport`.

Key behavior:

- `saveDefinition(...)` stores name, `source_key`, definition JSON/array, visibility
- `loadDefinition(...)` hydrates `ReportDefinition`
- `updateDefinition(...)` updates stored definition

## Scheduler

Scheduling **is implemented**.

Relevant pieces:

- `Models\ReportSchedule`
- `Scheduling\DueScheduleDiscovery`
- command: `php artisan report-builder:schedules:due`

Current scheduling support discovers due schedules (cron-based) for saved reports. It does **not** automatically send emails or dispatch exports by itself in this package layer.
