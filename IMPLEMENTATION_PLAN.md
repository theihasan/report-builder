# Report Builder Package Implementation Plan

## Purpose Of This Document

This document is the build blueprint for the `report-builder` package.

The package is still near skeleton stage. This plan explains:

- what the package is
- what it is not
- how it should be built step by step
- what each module and file is responsible for
- how request data flows through the package
- how exceptions should be handled
- how performance, memory, and stability should be protected

This is meant to be read before implementation and during implementation.

## Current State

Current package status:

- `src/ReportBuilderServiceProvider.php` exists, but only registers placeholder package resources
- `src/ReportBuilder.php` is empty
- `config/report-builder.php` is empty
- package migration is still a placeholder
- no routes, controllers, models, services, contracts, query engine, or real tests exist yet

That means the package architecture can still be designed correctly before code grows in the wrong direction.

## Package Goal

Build a reusable Laravel package that lets any Laravel application:

- register safe reportable data sources
- expose those data sources to a report builder UI or API
- let users create a report configuration
- preview a report before saving it
- save the report configuration
- run the saved report later
- pin reports to dashboards as widgets
- export reports as CSV, Excel, or PDF

The package must work across many applications and must not assume fixed business models such as `Order`, `Product`, or `Customer`.

## What The Package Is

This package is a:

> config-driven reporting package with trusted source definitions and an internal safe query compiler

That means:

- users define reports through configuration
- host applications define what is allowed through source registration
- the package compiles the config into safe Laravel queries internally

## What The Package Is Not

This package is not:

- a raw SQL editor for end users
- a package that allows arbitrary table names or join definitions from the frontend
- a package that stores rendered datasets as the main source of truth
- a package tightly coupled to one frontend stack only
- a package tied to one app's domain models

## Core Architecture Rule

There are three layers in the package.

### Layer 1: Report Config

This is user-controlled intent.

It should contain things like:

- source key
- selected fields
- filters
- sorts
- groups
- aggregations
- visualization type
- visualization settings
- pagination and limits
- visibility metadata

It must never contain:

- raw SQL
- raw join clauses
- raw database table names
- raw database column names from the frontend
- query builder method names
- PHP class names

### Layer 2: Source Definitions

This is developer-controlled trusted code inside the host application.

It should define:

- source key
- label
- base query
- safe field map
- allowed operators
- allowed aggregates
- groupable fields
- sortable fields
- tenant or user scopes
- authorization hooks
- safe computed fields if needed

Trusted advanced SQL may exist here, but only in developer-owned code.

### Layer 3: Query Engine

This is package-controlled safe execution logic.

It should:

- validate config against the source definition
- resolve safe field metadata
- compile filters, sorts, groups, and aggregates
- apply limits or pagination
- execute the Laravel query builder
- return a normalized dataset

The engine must never trust raw frontend SQL-like input.

## Product Behavior Summary

The package behavior, based on the request flow and architecture diagrams, is:

1. user opens the report builder
2. frontend loads sources, fields, and optionally saved reports
3. user builds a report config
4. backend validates config shape and semantics
5. backend resolves the selected registered data source
6. backend compiles config into a safe query
7. database returns rows or aggregated results
8. renderer converts results into table, chart, or KPI payloads
9. user saves the config as a reusable report
10. user optionally pins that report to a dashboard
11. dashboard widgets load saved report configs and render them, ideally with cache reuse
12. user can drill down into the saved report and export it

## Build Strategy

Do not build everything at once.

Build the package in phases.

## Phase 1: Foundations

### Goal

Create the safe source registration layer and metadata API.

### Deliverables

- config file
- service provider bindings
- contracts
- enums
- DTOs
- field definition support classes
- data source registry
- Eloquent data source implementation
- route and controller for source metadata listing

### What Must Work At End Of Phase 1

- host app can register a source
- package can list sources
- package can list fields for a source
- no report execution yet

### Why This Phase Comes First

Every later feature depends on safe source metadata. If source definitions are weak, the whole package becomes unsafe.

## Phase 2: Preview Engine

### Goal

Build preview execution for unsaved reports.

### Deliverables

- `ReportConfiguration` DTO
- preview request validation
- semantic configuration validator
- execution context
- query engine
- table renderer
- preview endpoint

### What Must Work At End Of Phase 2

- frontend can send config
- package validates it
- package executes safe query
- package returns preview dataset and table payload

### Why This Phase Comes Second

Preview is the heart of the package. If preview is not reliable, saving/exporting/widgets should not be built yet.

## Phase 3: Saved Reports

### Goal

Persist report configs and run them later.

### Deliverables

- `reports` table
- `Report` model
- repository abstraction
- report service
- CRUD endpoints
- run saved report endpoint

### What Must Work At End Of Phase 3

- user can save report
- user can edit report
- user can delete report
- user can run saved report

### Why This Phase Comes Third

Once preview is trusted, persistence becomes a straightforward layer above it.

## Phase 4: Exports

### Goal

Add report exporting without changing the query engine design.

### Deliverables

- `report_exports` table
- exporter contract
- CSV exporter first
- export service
- sync export flow first
- async export infrastructure second

### What Must Work At End Of Phase 4

- report can be exported as CSV
- export is memory safe for large datasets

### Why This Phase Comes Fourth

Export should reuse the same config-driven core. It should be another output path, not a second execution engine.

## Phase 5: Dashboards And Widgets

### Goal

Allow saved reports to power dashboards.

### Deliverables

- `dashboards` table
- `report_widgets` table
- `Dashboard` model
- `ReportWidget` model
- dashboard service
- widget payload flow
- widget caching

### What Must Work At End Of Phase 5

- user can pin saved report to dashboard
- dashboard loads widgets
- each widget loads one saved report and renders compact payload

### Why This Phase Comes Fifth

Widgets are not independent reports. They are a presentation layer on top of saved reports.

## Phase 6: Advanced Capabilities

### Goal

Expand the package after the core is stable.

### Deliverables

- chart payload renderer
- KPI renderer
- Excel exporter
- PDF exporter
- queue-based exports
- report run logging
- cache warming
- scheduled reports
- email delivery
- stronger multi-tenant support hooks

## Final Target Directory Structure

The final planned package structure is:

```text
packages/report-builder/
├── CHANGELOG.md
├── composer.json
├── config/
│   └── report-builder.php
├── database/
│   ├── factories/
│   └── migrations/
│       ├── create_reports_table.php.stub
│       ├── create_dashboards_table.php.stub
│       ├── create_report_widgets_table.php.stub
│       ├── create_report_exports_table.php.stub
│       └── create_report_runs_table.php.stub
├── IMPLEMENTATION_PLAN.md
├── README.md
├── resources/
│   └── views/
│       ├── builder/
│       │   └── index.blade.php
│       ├── dashboards/
│       │   └── show.blade.php
│       ├── exports/
│       │   └── pdf.blade.php
│       └── reports/
│           └── show.blade.php
├── routes/
│   ├── api.php
│   └── web.php
├── src/
│   ├── Commands/
│   │   ├── InstallReportBuilderCommand.php
│   │   ├── PruneReportExportsCommand.php
│   │   └── WarmReportCacheCommand.php
│   ├── Contracts/
│   │   ├── DataSourceContract.php
│   │   ├── ExporterContract.php
│   │   ├── QueryEngineContract.php
│   │   ├── RendererContract.php
│   │   ├── ReportRepositoryInterface.php
│   │   ├── TenantResolverContract.php
│   │   └── WidgetContract.php
│   ├── DataSources/
│   │   ├── EloquentDataSource.php
│   │   ├── QueryBuilderDataSource.php
│   │   └── Concerns/
│   │       └── InteractsWithFields.php
│   ├── DTOs/
│   │   ├── AggregationRule.php
│   │   ├── Dataset.php
│   │   ├── ExecutionContext.php
│   │   ├── FieldDefinition.php
│   │   ├── FilterRule.php
│   │   ├── RenderedPayload.php
│   │   ├── ReportConfiguration.php
│   │   └── SortRule.php
│   ├── Enums/
│   │   ├── AggregateFunction.php
│   │   ├── ExportFormat.php
│   │   ├── FieldType.php
│   │   ├── FilterOperator.php
│   │   ├── ReportVisibility.php
│   │   └── VisualizationType.php
│   ├── Exceptions/
│   │   ├── DataSourceNotFoundException.php
│   │   ├── ExportGenerationException.php
│   │   ├── ForbiddenFieldException.php
│   │   ├── InvalidReportConfigurationException.php
│   │   ├── ReportBuilderException.php
│   │   ├── ReportExecutionException.php
│   │   ├── UnsafeQueryOperationException.php
│   │   ├── UnsupportedAggregationException.php
│   │   ├── UnsupportedOperatorException.php
│   │   └── WidgetConfigurationException.php
│   ├── Exporters/
│   │   ├── CsvExporter.php
│   │   ├── ExcelExporter.php
│   │   └── PdfExporter.php
│   ├── Facades/
│   │   └── ReportBuilder.php
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── Api/
│   │   │   │   ├── DashboardController.php
│   │   │   │   ├── DashboardWidgetController.php
│   │   │   │   ├── DataSourceController.php
│   │   │   │   ├── ReportController.php
│   │   │   │   ├── ReportExportController.php
│   │   │   │   ├── ReportPreviewController.php
│   │   │   │   └── ReportRunController.php
│   │   │   └── Web/
│   │   │       ├── BuilderPageController.php
│   │   │       ├── DashboardPageController.php
│   │   │       └── ReportPageController.php
│   │   ├── Requests/
│   │   │   ├── PreviewReportRequest.php
│   │   │   ├── StoreDashboardRequest.php
│   │   │   ├── StoreReportRequest.php
│   │   │   ├── StoreWidgetRequest.php
│   │   │   ├── UpdateReportRequest.php
│   │   │   └── CreateReportExportRequest.php
│   │   └── Resources/
│   │       ├── DashboardResource.php
│   │       ├── DataSourceResource.php
│   │       ├── ReportPreviewResource.php
│   │       ├── ReportResource.php
│   │       └── WidgetResource.php
│   ├── Jobs/
│   │   ├── GenerateReportExportJob.php
│   │   ├── PruneExpiredReportExportsJob.php
│   │   └── WarmWidgetCacheJob.php
│   ├── Models/
│   │   ├── Dashboard.php
│   │   ├── Report.php
│   │   ├── ReportExport.php
│   │   ├── ReportRun.php
│   │   └── ReportWidget.php
│   ├── Policies/
│   │   ├── DashboardPolicy.php
│   │   ├── ReportExportPolicy.php
│   │   └── ReportPolicy.php
│   ├── Query/
│   │   ├── Compilers/
│   │   │   ├── AggregationCompiler.php
│   │   │   ├── FilterCompiler.php
│   │   │   ├── GroupCompiler.php
│   │   │   ├── LimitCompiler.php
│   │   │   └── SortCompiler.php
│   │   ├── Drivers/
│   │   │   ├── DatabaseDriverContract.php
│   │   │   ├── MySqlDriver.php
│   │   │   ├── PostgresDriver.php
│   │   │   └── SqliteDriver.php
│   │   ├── ConfigurationValidator.php
│   │   └── QueryEngine.php
│   ├── Renderers/
│   │   ├── ChartPayloadRenderer.php
│   │   ├── HtmlReportRenderer.php
│   │   ├── KpiRenderer.php
│   │   └── TableRenderer.php
│   ├── Repositories/
│   │   └── EloquentReportRepository.php
│   ├── Services/
│   │   ├── DashboardService.php
│   │   ├── ExportService.php
│   │   └── ReportService.php
│   ├── Support/
│   │   ├── CacheKeyFactory.php
│   │   ├── ConfigHasher.php
│   │   ├── DataSourceRegistry.php
│   │   ├── Field.php
│   │   └── VisualizationResolver.php
│   ├── Widgets/
│   │   ├── ReportWidgetPayloadBuilder.php
│   │   └── WidgetResult.php
│   ├── ReportBuilder.php
│   └── ReportBuilderServiceProvider.php
└── tests/
    ├── Feature/
    │   ├── DataSourceListingTest.php
    │   ├── ReportCrudTest.php
    │   ├── ReportPreviewTest.php
    │   ├── ReportRunTest.php
    │   ├── CsvExportTest.php
    │   └── DashboardWidgetTest.php
    ├── Unit/
    │   ├── ConfigurationValidatorTest.php
    │   ├── DataSourceRegistryTest.php
    │   ├── FilterCompilerTest.php
    │   ├── QueryEngineTest.php
    │   └── TableRendererTest.php
    └── TestCase.php
```

## Package Root Files

### `composer.json`

Purpose:

- declares package name and dependencies
- exposes package discovery
- defines autoload rules

How it works:

- package is installed via Composer into a host Laravel app
- Laravel discovers `ReportBuilderServiceProvider`
- package dependencies define what the core can rely on

Important notes:

- PDF and Excel integrations should stay optional if possible
- avoid forcing heavy dependencies into core unless necessary

### `README.md`

Purpose:

- public installation and quick usage guide

How it works:

- should stay shorter than this implementation plan
- should point to package concepts, install steps, and minimal example

### `IMPLEMENTATION_PLAN.md`

Purpose:

- internal build specification and architecture document

How it works:

- developers use this file while building the package

## Configuration Layer

### `config/report-builder.php`

Purpose:

- package runtime configuration

What it should contain:

- route prefix
- route middleware
- API middleware
- default pagination
- max pagination
- preview row limit
- default cache TTL
- export thresholds
- queue names
- storage disk for exports
- feature toggles for dashboard, PDF, Excel
- optional model and policy overrides

What it should not contain:

- closures
- tenant-specific runtime logic
- source definitions
- app business rules

Example shape:

```php
return [
    'route_prefix' => 'report-builder',
    'web_middleware' => ['web', 'auth'],
    'api_middleware' => ['api', 'auth'],
    'default_per_page' => 25,
    'max_per_page' => 100,
    'preview_limit' => 100,
    'max_chart_points' => 500,
    'default_cache_ttl_seconds' => 300,
    'max_export_rows_sync' => 5000,
    'exports_disk' => 'local',
    'exports_queue' => 'report-exports',
    'enable_dashboards' => true,
    'enable_excel' => false,
    'enable_pdf' => false,
];
```

Line by line logic:

- `route_prefix` keeps package routes isolated from the host app
- middleware arrays let the host app choose auth stack
- page and preview limits protect memory and slow queries
- cache TTL sets default freshness window
- sync export limit prevents huge in-request downloads
- feature toggles avoid booting exporters that are not installed

## Service Provider And Package Bootstrap

### `src/ReportBuilderServiceProvider.php`

Purpose:

- package entry point for Laravel

What it does:

- merges config
- loads routes
- loads views
- loads migrations
- registers commands
- binds services and contracts into container
- publishes config and views if needed

How it works step by step:

1. Laravel discovers the package provider
2. provider registers bindings into the container
3. provider makes registry, services, and engine resolvable
4. provider loads package routes and resources
5. host app can immediately call `ReportBuilder::registerDataSource(...)`

Important design rule:

- do not put business logic here
- keep provider limited to bootstrapping and dependency registration

### `src/ReportBuilder.php`

Purpose:

- package entry service behind facade

What it should do:

- expose a clean API for source registration
- later expose helper methods if useful, but do not turn it into a god object

Example responsibility:

- `registerDataSource(DataSourceContract $dataSource): void`
- `dataSources(): array`

### `src/Facades/ReportBuilder.php`

Purpose:

- provide static-looking package API for host apps

How it works:

- host app can call `ReportBuilder::registerDataSource(...)`
- facade resolves underlying `src/ReportBuilder.php` service from container

## Contracts

Contracts define extension points. They are critical for reuse.

### `src/Contracts/DataSourceContract.php`

Purpose:

- every reportable source must implement this contract

Responsibilities:

- return source key
- return source label
- return safe field definitions
- return base query
- optionally authorize or scope requests

### `src/Contracts/QueryEngineContract.php`

Purpose:

- execution abstraction for report building

Responsibilities:

- preview a report config
- run a report config
- return a normalized dataset

### `src/Contracts/RendererContract.php`

Purpose:

- output abstraction for table, chart, KPI, or HTML payloads

Responsibilities:

- declare supported visualization type
- transform a dataset into a rendered payload

### `src/Contracts/ExporterContract.php`

Purpose:

- export abstraction for CSV, Excel, PDF, and future formats

Responsibilities:

- declare format key
- export using safe report configuration and context

### `src/Contracts/ReportRepositoryInterface.php`

Purpose:

- persistence abstraction for saved reports

Responsibilities:

- create report
- update report
- delete report
- paginate visible reports

### `src/Contracts/TenantResolverContract.php`

Purpose:

- let host apps define tenant or team context without forcing a tenancy package

Responsibilities:

- resolve current tenant or current scope object
- return stable identifiers for caching and scoping

### `src/Contracts/WidgetContract.php`

Purpose:

- optional extension point if non-report widgets are needed later

Important note:

- this contract should not be heavily used in MVP
- dashboard widgets should start as report-backed widgets first

## DTOs

DTOs prevent raw arrays from spreading across the package.

### `src/DTOs/ReportConfiguration.php`

Purpose:

- central runtime object of the package

What it contains:

- source key
- fields
- filters
- sorts
- groups
- aggregations
- visualization block
- pagination block
- limit
- visibility block
- version

How it works:

- created from validated array input
- used by services, engine, exporters, and renderers
- remains the canonical representation of report intent

Important rule:

- saved reports store this data, not SQL, not HTML, and not dataset rows

### `src/DTOs/FieldDefinition.php`

Purpose:

- package-side representation of one allowed field in a source

What it contains:

- public key
- label
- type
- underlying column or expression alias
- sortable flag
- filterable operators
- aggregate allowlist
- groupable settings
- optional format hint

How it works:

- source definitions return many field definitions
- query engine resolves fields through this metadata instead of trusting frontend strings

### `src/DTOs/FilterRule.php`

Purpose:

- represent one validated filter from a config

How it works:

- includes field key, operator enum, and value
- compiler consumes this DTO and applies safe query builder logic

### `src/DTOs/SortRule.php`

Purpose:

- represent one validated sort instruction

### `src/DTOs/AggregationRule.php`

Purpose:

- represent one validated aggregate instruction

### `src/DTOs/ExecutionContext.php`

Purpose:

- carry request-scoped information through the package

What it contains:

- current user
- current tenant or scope
- request mode such as preview, run, widget, or export
- locale or timezone if needed

How it works:

- created near controller boundary
- passed into services and query engine
- prevents repeated global lookups

### `src/DTOs/Dataset.php`

Purpose:

- normalized query result container

What it contains:

- column metadata
- iterable rows
- summary values
- execution metadata such as row count or cache hit

How it works:

- query engine returns this object
- renderers consume it
- exporters may consume it or stream equivalent query output

Important performance rule:

- dataset should support iterable rows and should not force eager in-memory materialization for all use cases

### `src/DTOs/RenderedPayload.php`

Purpose:

- renderer output container

What it contains:

- payload type
- data shape ready for API response or view rendering

## Enums

Enums turn magic strings into safe, explicit decisions.

### `src/Enums/FieldType.php`

Purpose:

- define safe field categories such as `String`, `Integer`, `Decimal`, `Date`, `DateTime`, `Boolean`

### `src/Enums/FilterOperator.php`

Purpose:

- define allowed operators such as `Eq`, `Neq`, `Gt`, `Gte`, `Lt`, `Lte`, `Between`, `In`, `NotIn`, `Like`, `IsNull`, `NotNull`

### `src/Enums/AggregateFunction.php`

Purpose:

- define allowed functions such as `Count`, `Sum`, `Avg`, `Min`, `Max`

### `src/Enums/VisualizationType.php`

Purpose:

- define `Table`, `Chart`, `Kpi`

### `src/Enums/ExportFormat.php`

Purpose:

- define `Csv`, `Xlsx`, `Pdf`

### `src/Enums/ReportVisibility.php`

Purpose:

- define visibility states such as `Private`, `Team`, `Tenant`, `Public`

## Support Layer

### `src/Support/Field.php`

Purpose:

- fluent builder for `FieldDefinition`

How it works:

- source registration becomes readable and safe
- example:

```php
Field::decimal('total_amount')
    ->label('Total Amount')
    ->column('orders.total_amount')
    ->sortable()
    ->filterable(['eq', 'gt', 'gte', 'between'])
    ->aggregates(['sum', 'avg']);
```

What each line means:

- `decimal('total_amount')` creates field definition with public key and type
- `label(...)` sets UI label
- `column(...)` maps to trusted database column
- `sortable()` enables sort compiler for this field
- `filterable(...)` limits legal operators
- `aggregates(...)` limits legal aggregation functions

### `src/Support/DataSourceRegistry.php`

Purpose:

- central in-memory registry of reportable sources

How it works:

- host app registers sources during boot
- registry stores them by source key
- services resolve sources from here

Important safety rule:

- missing source keys throw a clear exception instead of silently failing

### `src/Support/ConfigHasher.php`

Purpose:

- generate stable hashes for saved configs and cache keys

How it works:

- takes normalized config array
- sorts structure if needed
- generates deterministic hash

### `src/Support/CacheKeyFactory.php`

Purpose:

- generate cache keys consistently

How it works:

- includes source key
- includes config hash
- includes user or tenant scope when needed
- includes mode such as preview, widget, or export if needed

### `src/Support/VisualizationResolver.php`

Purpose:

- resolve visualization type to correct renderer

## Data Sources

### `src/DataSources/EloquentDataSource.php`

Purpose:

- base implementation for sources backed by Eloquent models

How it works:

- accepts model class, key, label, and fields
- returns `Model::query()` as base query
- can be extended or configured with source scopes

### `src/DataSources/QueryBuilderDataSource.php`

Purpose:

- base implementation for sources backed by plain query builder definitions

How it works:

- accepts closure or callable that returns a query builder
- useful for pre-joined or reporting-specific SQL structures

Important design rule:

- advanced trusted SQL may exist here, but never through frontend config input

### `src/DataSources/Concerns/InteractsWithFields.php`

Purpose:

- share field storage and lookup behavior across source implementations

## Query Layer

This is the core engine.

### `src/Query/ConfigurationValidator.php`

Purpose:

- validate report config semantics against source metadata

What it checks:

- source exists
- fields exist
- fields are selectable
- operators are allowed for the field
- sort fields are sortable
- group fields are groupable
- aggregate functions are allowed
- visualization settings are compatible
- page and limit values stay within configured caps

How it works:

- this is deeper than request validation
- request validation checks JSON structure
- configuration validator checks domain meaning

### `src/Query/QueryEngine.php`

Purpose:

- central safe execution engine

How it works step by step:

1. receives `DataSourceContract`, `ReportConfiguration`, and `ExecutionContext`
2. validates config semantics
3. gets trusted base query from source
4. applies tenant or user scope
5. applies filters through filter compiler
6. applies grouping through group compiler
7. applies aggregates through aggregation compiler
8. applies sorting through sort compiler
9. applies limits or pagination through limit compiler
10. executes query
11. wraps rows in `Dataset`

Important rule:

- never accept raw SQL fragments from user config
- only compile from allowlisted metadata

### `src/Query/Compilers/FilterCompiler.php`

Purpose:

- apply validated filter rules safely

How it works:

- resolves field from source definition
- maps operator enum to known query builder methods
- binds values using Laravel query builder

Example:

- config says `status eq paid`
- compiler maps that to `where('orders.status', '=', 'paid')`

### `src/Query/Compilers/SortCompiler.php`

Purpose:

- apply validated sorting safely

How it works:

- only applies if field is marked sortable
- only allows `asc` or `desc`

### `src/Query/Compilers/GroupCompiler.php`

Purpose:

- apply grouping rules safely

How it works:

- only group on allowed fields
- uses database driver helpers for day, month, year bucketing if needed

### `src/Query/Compilers/AggregationCompiler.php`

Purpose:

- apply aggregate selections safely

How it works:

- only allows `count`, `sum`, `avg`, `min`, `max`
- uses trusted field metadata to build SQL expressions

### `src/Query/Compilers/LimitCompiler.php`

Purpose:

- enforce preview, page, and export limits

How it works:

- preview mode applies strict small cap
- run mode may paginate
- export mode may choose cursor or streaming strategy

### `src/Query/Drivers/DatabaseDriverContract.php`

Purpose:

- abstract DB-specific behavior needed for grouping or formatting

Why it exists:

- date bucketing often differs across SQLite, MySQL, and Postgres

### `src/Query/Drivers/MySqlDriver.php`

Purpose:

- MySQL implementation for DB-specific query fragments

### `src/Query/Drivers/PostgresDriver.php`

Purpose:

- Postgres implementation for DB-specific query fragments

### `src/Query/Drivers/SqliteDriver.php`

Purpose:

- SQLite implementation for local development and package tests

## Renderers

Renderers convert datasets into presentation payloads.

### `src/Renderers/TableRenderer.php`

Purpose:

- transform dataset into table response payload

How it works:

- returns columns
- returns rows
- may return summary data
- keeps frontend rendering simple

### `src/Renderers/ChartPayloadRenderer.php`

Purpose:

- transform aggregated dataset into chart-friendly payload

Important rule:

- this should not depend on Chart.js specifically
- it should output normalized chart data only

### `src/Renderers/KpiRenderer.php`

Purpose:

- return one metric and optional comparison metadata

### `src/Renderers/HtmlReportRenderer.php`

Purpose:

- server-side view payload for optional Blade pages or printable HTML

Important rule:

- PDF generation should not live here as a first-class runtime renderer
- PDF should be handled by export flow

## Exporters

### `src/Exporters/CsvExporter.php`

Purpose:

- first export format for MVP

How it works:

- uses same report config and query engine principles
- streams rows out using a streamed response
- writes one row at a time using `fputcsv`

Important performance rule:

- do not load full export into memory

### `src/Exporters/ExcelExporter.php`

Purpose:

- optional Excel export using external dependency

Important note:

- should be enabled only if configured and dependency exists

### `src/Exporters/PdfExporter.php`

Purpose:

- optional PDF export adapter

Important note:

- PDF is often expensive in memory and CPU
- keep row limits tighter than CSV or Excel

## Service Layer

Services orchestrate flows and keep controllers thin.

### `src/Services/ReportService.php`

Purpose:

- main orchestration service for preview, save, run, and report loading

How it works step by step for preview:

1. resolve source from registry
2. authorize preview action
3. validate config against source
4. execute query through query engine
5. choose renderer by visualization type
6. return preview result

How it works for saved reports:

1. load report model
2. authorize access
3. rebuild configuration DTO from stored JSON
4. run same core execution path

### `src/Services/ExportService.php`

Purpose:

- orchestrate export format selection and artifact handling

How it works:

- resolve exporter by format
- authorize export action
- choose sync or async path
- return streamed response or export record

### `src/Services/DashboardService.php`

Purpose:

- orchestrate dashboard and widget loading

How it works:

- load dashboard
- authorize dashboard access
- load widget models
- load linked reports
- reuse core report execution flow
- apply widget-specific view overrides
- return dashboard payload

## HTTP Layer

## Routes

### `routes/api.php`

Purpose:

- JSON API endpoints for sources, preview, reports, exports, dashboards, widgets

Planned endpoints:

- `GET /sources`
- `GET /sources/{source}`
- `POST /reports/preview`
- `GET /reports`
- `POST /reports`
- `GET /reports/{report}`
- `PUT /reports/{report}`
- `DELETE /reports/{report}`
- `GET /reports/{report}/run`
- `POST /reports/{report}/exports`
- `GET /dashboards/{dashboard}`
- `POST /dashboards/{dashboard}/widgets`

### `routes/web.php`

Purpose:

- optional package pages if Blade starter UI is shipped

Important note:

- package should remain API-first even if basic Blade pages exist

## Controllers

### `src/Http/Controllers/Api/DataSourceController.php`

Purpose:

- list sources and source metadata

### `src/Http/Controllers/Api/ReportPreviewController.php`

Purpose:

- accept draft config and return preview payload

How it should stay:

- very thin
- should not contain query logic
- should only build DTO and call service

### `src/Http/Controllers/Api/ReportController.php`

Purpose:

- CRUD for saved reports

### `src/Http/Controllers/Api/ReportRunController.php`

Purpose:

- execute saved reports

### `src/Http/Controllers/Api/ReportExportController.php`

Purpose:

- trigger exports and return export state

### `src/Http/Controllers/Api/DashboardController.php`

Purpose:

- return dashboard payloads

### `src/Http/Controllers/Api/DashboardWidgetController.php`

Purpose:

- create, update, and remove widgets

### Web Controllers

Purpose:

- optional Blade pages only

Important rule:

- actual data flow should still go through core services, not separate page-specific logic

## Form Requests

### `src/Http/Requests/PreviewReportRequest.php`

Purpose:

- validate incoming JSON shape for preview requests

What it validates:

- config exists
- source key is a string
- arrays are arrays
- visualization block exists
- per-page and limit values are numeric if present

What it does not validate alone:

- whether a field is really allowed for a source
- whether an operator is legal for that field

That deeper validation belongs in `ConfigurationValidator`

### `StoreReportRequest`, `UpdateReportRequest`, `CreateReportExportRequest`, `StoreDashboardRequest`, `StoreWidgetRequest`

Purpose:

- validate each API input boundary consistently

## API Resources

Purpose:

- normalize API output

Benefits:

- stable response contracts
- less controller duplication
- easier versioning later

## Models And Database Tables

## `reports` table and `src/Models/Report.php`

Purpose:

- persist saved report definitions

Important columns:

- `id`
- `uuid`
- `name`
- `description`
- `data_source_key`
- `visualization_type`
- `config_version`
- `config` JSON
- `meta` JSON
- `config_hash`
- `visibility`
- creator and scope columns
- `cache_ttl_seconds`
- `last_run_at`
- timestamps
- optional soft delete

What this model stores:

- report intent

What it must not store:

- rendered rows
- export file bytes
- HTML payload

## `dashboards` table and `src/Models/Dashboard.php`

Purpose:

- persist dashboard container definitions

## `report_widgets` table and `src/Models/ReportWidget.php`

Purpose:

- persist widget references to saved reports

Important design rule:

- widget should store report reference plus display overrides, not a full duplicated report config by default

## `report_exports` table and `src/Models/ReportExport.php`

Purpose:

- track export jobs and artifacts

Stores:

- format
- status
- path
- row count
- error state
- requester and scope
- timestamps and expiration

## `report_runs` table and `src/Models/ReportRun.php`

Purpose:

- optional observability and audit for executions

Stores:

- runtime
- row count
- cache hit
- status
- error message
- config hash

## Policies

### `src/Policies/ReportPolicy.php`

Purpose:

- authorize report actions such as `view`, `create`, `update`, `delete`, `run`, `export`

### `src/Policies/DashboardPolicy.php`

Purpose:

- authorize dashboard access and widget management

### `src/Policies/ReportExportPolicy.php`

Purpose:

- authorize export request and download access

Important rule:

- saved visibility metadata is not enough by itself
- actual authorization must still run through policies and source scopes

## Repositories

### `src/Repositories/EloquentReportRepository.php`

Purpose:

- persistence implementation for saved reports

Why it exists:

- keeps model query concerns away from controllers and service orchestration
- makes testing easier

## Commands

### `src/Commands/InstallReportBuilderCommand.php`

Purpose:

- optional package setup helper

### `src/Commands/PruneReportExportsCommand.php`

Purpose:

- remove expired export artifacts

### `src/Commands/WarmReportCacheCommand.php`

Purpose:

- optional cache warming for important reports or widgets

## Jobs

### `src/Jobs/GenerateReportExportJob.php`

Purpose:

- generate large exports asynchronously

How it works:

- loads export definition
- executes report in export mode
- writes artifact to disk
- updates export record status

### `src/Jobs/PruneExpiredReportExportsJob.php`

Purpose:

- delete expired export files and clean DB metadata if needed

### `src/Jobs/WarmWidgetCacheJob.php`

Purpose:

- refresh dashboard widget cache in background

Important queue rule:

- queued jobs must avoid serializing large loaded relationships

## Widgets

### `src/Widgets/ReportWidgetPayloadBuilder.php`

Purpose:

- build compact widget payloads from saved reports and widget settings

Why this is better than many widget subclasses for MVP:

- simpler
- report-backed widgets all use same core path
- no premature inheritance tree

### `src/Widgets/WidgetResult.php`

Purpose:

- consistent widget payload container

## Resources Views

### `resources/views/builder/index.blade.php`

Purpose:

- optional basic builder shell page

### `resources/views/reports/show.blade.php`

Purpose:

- optional saved report detail page

### `resources/views/dashboards/show.blade.php`

Purpose:

- optional dashboard page shell

### `resources/views/exports/pdf.blade.php`

Purpose:

- printable HTML view if PDF export is enabled

Important rule:

- PDF rendering should be used through export flow, not normal preview flow

## Exceptions Strategy

Exceptions must be explicit and aligned with boundaries.

### Base Exception

### `src/Exceptions/ReportBuilderException.php`

Purpose:

- package base exception

Responsibilities:

- carry HTTP status intention
- carry safe log context
- optionally render clean JSON for API requests

Recommended behavior:

- expected domain exceptions should return safe API messages
- unexpected execution exceptions should be reportable and context-rich

### Expected Domain Exceptions

These are not system bugs. These are normal invalid usage cases.

- `DataSourceNotFoundException`
- `InvalidReportConfigurationException`
- `ForbiddenFieldException`
- `UnsupportedOperatorException`
- `UnsupportedAggregationException`
- `WidgetConfigurationException`

Typical status codes:

- `404`
- `403`
- `422`

Reporting behavior:

- should not spam logs heavily

### Unexpected Execution Exceptions

These are system or code failures.

- `UnsafeQueryOperationException`
- `ReportExecutionException`
- `ExportGenerationException`

Typical status code:

- `500`

Reporting behavior:

- should be logged with structured context

## Request Data Flow

## Builder Open Flow

1. UI requests available sources
2. controller calls registry-backed service
3. package returns safe metadata only
4. UI builds controls from field definitions

## Preview Flow

1. UI sends config to preview endpoint
2. request class validates structure
3. controller builds `ReportConfiguration`
4. controller builds `ExecutionContext`
5. service resolves source
6. service authorizes access
7. configuration validator checks semantic correctness
8. query engine builds safe query
9. database returns rows
10. dataset is normalized
11. renderer creates table or chart payload
12. controller returns JSON resource

## Save Flow

1. UI sends report name, visibility, and config
2. request validates structure
3. service resolves source and validates config again
4. config hash is generated
5. repository saves report record
6. saved report id is returned

## Run Saved Report Flow

1. UI requests saved report run endpoint
2. package loads report model
3. policy authorizes access
4. stored config JSON becomes `ReportConfiguration`
5. same query engine flow runs
6. renderer returns payload

Important rule:

- do not create a second execution path for saved reports
- saved runs must reuse the same config-driven core path

## Dashboard Flow

1. UI requests dashboard
2. package loads dashboard
3. package loads widgets
4. each widget loads linked report
5. widget display overrides are merged carefully
6. cache key is built
7. cache is checked
8. cache hit returns dataset immediately
9. cache miss executes report
10. renderer creates widget payload
11. dashboard resource returns all widgets

## Export Flow

1. UI requests export format
2. package authorizes export action
3. package loads report config
4. package chooses correct exporter
5. exporter decides sync or async strategy
6. sync exporter streams response
7. async exporter creates `report_exports` record and dispatches job

## Performance, Memory, And Stability Rules

This package must be designed for large data use cases from the start.

## Core Performance Rules

- preview must always be limited
- large exports must stream or queue
- widgets should prefer cached data
- query engine should only select required fields
- grouped or aggregate work should happen in SQL, not PHP loops
- multiple widgets should not cause N+1 report loading

## Preview Protection Rules

- hard cap preview rows to a small number such as `50` or `100`
- use short runtime expectations for preview
- reject very expensive combinations early if possible

Why:

- preview is interactive
- slow preview makes the builder feel broken
- unrestricted preview risks memory spikes and slow DB scans

## Pagination Rules

- default to `paginate` or `simplePaginate` for saved report browsing
- prefer `cursorPaginate` for large scrolling datasets when suitable

Why:

- offset pagination becomes more expensive at large page depths
- cursor pagination is better for large ordered datasets

## Export Memory Rules

### CSV

- use streamed download
- write one row at a time
- prefer `cursor()` or `lazy()` over `get()` for large result sets

### Excel

- queue large exports
- do not build huge workbook state in one request if avoidable

### PDF

- keep PDF exports limited
- prefer summary reports over very large tables
- PDF rendering is CPU and memory heavy

## Widget Performance Rules

- widget payloads should use caching aggressively
- widget should not duplicate full report execution when same report and same scope already have a fresh result

## Cache Strategy

Recommended cache key ingredients:

- source key
- normalized config hash
- report id if saved report exists
- widget id or widget override hash if widget view changes results
- tenant or team scope
- user scope if row-level security differs by user

Recommended cache use cases:

- dashboard widgets
- expensive aggregated chart reports
- repeated report detail views

Recommended cache invalidation strategy:

- start with TTL-based expiration
- optionally allow manual cache flush per report or source
- later add source freshness helpers if needed

## Memory Leak And Long-Running Worker Rules

### Queue Jobs

- do not pass large fully loaded Eloquent graphs into jobs
- if models are passed, strip relations when possible
- release file handles after writing
- delete temporary files after final artifact move if needed
- avoid static in-memory caches that grow unbounded in workers

### Export Jobs

- write output incrementally
- avoid buffering entire export in strings or arrays
- store artifact on disk, not in database blobs
- set realistic timeouts and retry policies

### Renderer Layer

- do not build giant nested arrays if streaming or summarized output is possible
- especially avoid loading all rows into chart payloads beyond configured point caps

## Database Performance Rules

- only expose fields that are indexed or acceptable to query at scale when possible
- encourage host apps to index common filter and sort columns
- avoid `SELECT *`
- eager load only when source definition explicitly needs it
- prefer flattened reporting queries instead of relation walking in render time

## Rate Limiting And Abuse Protection

Recommended endpoint protection:

- preview endpoint should be rate limited
- export endpoint should be rate limited
- dashboard widget refresh endpoints should be rate limited or cached

Why:

- report engines are naturally expensive surfaces
- repeated previews can degrade the app quickly if not limited

## Observability Rules

The package should eventually track:

- runtime in milliseconds
- row count
- cache hit or miss
- export file size
- export failures
- top slow reports

This may be stored in `report_runs` or exposed through logs.

## Security Rules

These rules are non-negotiable.

- never accept raw SQL from users
- never trust frontend field names without source metadata lookup
- never allow raw joins from config
- only use allowlisted fields
- only use allowlisted operators
- only use allowlisted aggregates
- enforce authorization for view, create, update, run, export, pin actions
- enforce row-level and tenant-level scoping through source definitions and context
- never expose hidden or sensitive columns by default
- validate visualization settings to prevent bad references or huge payload requests

## Testing Strategy

The package uses PHPUnit for new work.

### Unit Tests

Test:

- registry behavior
- field definitions
- config normalization
- configuration validation
- operator mapping
- query compiler logic
- renderer payload shaping

### Feature Tests

Test:

- list sources
- preview report
- create report
- update report
- run report
- export CSV
- dashboard widget load
- authorization failures

### Security Tests

Test:

- forbidden field rejection
- unsupported operator rejection
- unsupported aggregate rejection
- over-limit preview rejection
- unauthorized export rejection

### Performance-Oriented Tests

Test:

- preview respects row cap
- CSV export streams
- dashboard widget cache reuse works

## MVP Definition

The smallest useful package version is:

1. source registration
2. source metadata API
3. preview table report
4. save report
5. run saved report
6. export CSV

Do not include in MVP:

- arbitrary joins from UI
- raw SQL editor
- PDF as required feature
- Excel as required feature
- scheduled reports
- email reports
- multi-source reports
- custom widget inheritance tree

## Recommended Implementation Order

Build in this order:

1. `config/report-builder.php`
2. `src/Contracts/*`
3. `src/Enums/*`
4. `src/DTOs/*`
5. `src/Support/Field.php`
6. `src/Support/DataSourceRegistry.php`
7. `src/DataSources/EloquentDataSource.php`
8. `src/Exceptions/*`
9. `src/Query/ConfigurationValidator.php`
10. `src/Query/Compilers/*`
11. `src/Query/QueryEngine.php`
12. `src/Renderers/TableRenderer.php`
13. `routes/api.php`
14. `src/Http/Requests/PreviewReportRequest.php`
15. `src/Http/Controllers/Api/ReportPreviewController.php`
16. `tests/Unit/*` for validator and engine
17. `tests/Feature/ReportPreviewTest.php`
18. `database/migrations/create_reports_table.php.stub`
19. `src/Models/Report.php`
20. `src/Repositories/EloquentReportRepository.php`
21. `src/Services/ReportService.php` save and run methods
22. report CRUD controllers and requests
23. `src/Exporters/CsvExporter.php`
24. export endpoints
25. dashboard models and service
26. chart and KPI renderers
27. async export jobs

## Final Architecture Summary

The package should be understood as this chain:

`Registered Source -> Validated Config -> Safe Query -> Dataset -> Renderer Or Exporter -> Optional Persistence`

The source definition is the trust boundary.

The config is the user intent.

The query engine is the safe compiler.

The renderer and exporter are output layers.

The report model stores intent, not output.

Dashboards reuse reports instead of inventing a second reporting system.

Performance and memory safety are built into the limits, streaming, caching, and job design from the start.
