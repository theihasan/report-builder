# Core Concepts

This package is intentionally source-driven and explicit. These are the concepts you will use most.

## Report Source

A **Report Source** defines where report data comes from.

A source must provide:

- `key()` - stable source key (`users`, `orders`, etc.)
- `label()` - human label
- `fields()` - explicit safe field list
- `query()` - base Eloquent query

Most apps extend `Ihasan\ReportBuilder\ReportSources\ReportSource`.

## Field

A **Field** is an explicitly allowed reporting field for a source.

Built-in field classes include:

- `TextField`
- `NumberField`
- `DateField`
- `BooleanField`
- `MoneyField`
- `RelationField`
- `RelationAggregateField`

Typical field capabilities:

- `selectable()`
- `sortable()`
- `filterable()`

Example:

```php
TextField::make('email')->selectable()->sortable()->filterable();
DateField::make('created_at')->selectable()->sortable()->filterable();
```

## Report Definition

A **Report Definition** (`DTOs\ReportDefinition`) is a serializable blueprint for a report run.

It holds:

- `sourceKey`
- selected columns (`SelectedColumn[]`)
- sorts (`SortDefinition[]`)
- optional filters (`FilterGroup`)
- output format (`OutputDefinition`, e.g. `json`, `csv`, `xlsx`)

Definitions support `toArray()`, `fromArray()`, `toJson()`, and `fromJson()` for persistence.

## Filter Group / Condition

Filtering is tree-based:

- `FilterCondition` = one rule (`field_key`, `operator`, `value`)
- `FilterGroup` = logical group (`and`/`or`) of conditions and/or nested groups

Example:

```php
new FilterGroup('and', [
    new FilterCondition('created_at', FilterOperator::LastNDays, 30),
    new FilterGroup('or', [
        new FilterCondition('email', FilterOperator::Like, '%@example.com'),
        new FilterCondition('email', FilterOperator::Like, '%@example.org'),
    ]),
]);
```

## Validator

`Validation\DefinitionValidator` validates a definition against the registered source.

It checks, for example:

- source key exists
- selected columns exist and are selectable
- sort fields exist and are sortable
- filter fields exist and are filterable
- filter values match operator expectations

Use `assertValid()` to throw `InvalidReportDefinitionException` when invalid.

## Query Compiler

`Query\ReportQueryCompiler` compiles a valid `ReportDefinition` into an Eloquent query.

It applies:

- selected fields
- relation eager loads (for `RelationField`)
- relation aggregates (for `RelationAggregateField`)
- filters via `FilterCompiler`
- sort clauses

`ReportRunner` and `PreviewRunner` both build on this.

## Preview Runner

`Execution\PreviewRunner` runs paginated previews and returns structured response data:

- `columns`
- `rows`
- `pagination`

Use it when building interactive preview endpoints or screens.

## Exporter

Exports are handled through:

- `Execution\ExportManager` (routes by format)
- `Execution\ReportRunner::export()` (simple entry point)

Built-in exporters:

- `CsvExporter` for `csv`
- `XlsxExporter` for `xlsx`

Export return shape:

- `filename`
- `mime_type`
- `content`

## Saved Report

A **Saved Report** persists report definitions for reuse.

- Model: `Models\SavedReport`
- Repository: `Persistence\SavedReportRepository`

Main repository actions:

- `saveDefinition(...)`
- `loadDefinition(...)`
- `updateDefinition(...)`

Saved records store `source_key` + serialized definition payload.

## Scheduler (implemented)

Scheduling primitives are implemented in the package:

- `Models\ReportSchedule`
- `Scheduling\DueScheduleDiscovery`
- command: `php artisan report-builder:schedules:due`

Current implementation discovers due schedules; it does not itself deliver emails or enqueue full delivery workflows.
