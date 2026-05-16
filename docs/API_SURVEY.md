# API Survey (Implemented Surface)

This survey is intentionally factual and limited to APIs/classes that are currently implemented in this repository.

## Primary entry points

- `Ihasan\ReportBuilder\Facades\ReportBuilder` facade, resolving `Ihasan\ReportBuilder\ReportBuilder`.
- `Ihasan\ReportBuilder\ReportBuilder` supports data-source registration and lookup:
  - `registerDataSource(DataSourceContract $dataSource): void`
  - `dataSource(string $key): DataSourceContract`
  - `dataSources(): array`
  - `authorizedDataSources(Request $request): array`
- Core engine services are container singletons via `ReportBuilderServiceProvider`:
  - `SourceRegistry`, `DefinitionValidator`, `ReportQueryCompiler`, `PreviewRunner`, `ReportRunner`, `ExportManager`, `SavedReportRepository` (constructable directly), `DueScheduleDiscovery`.

## Source registration approach

Two implemented mechanisms exist:

1. **Report sources** for report execution (`ReportSourceContract`):
   - Registered from config key `report-builder.report_sources` as class names in `ReportBuilderServiceProvider`.
   - Stored/resolved in `Support\SourceRegistry` by stable `source->key()`.
2. **Data sources** (`DataSourceContract`) for metadata/API concerns:
   - Registered at runtime through `ReportBuilder::registerDataSource()`.
   - Managed by `Support\DataSourceRegistry`.

`report-builder.report_sources` defaults to an empty array in config.

## Field model and available field types

### Field classes

Implemented field classes under `ReportSources\Fields`:

- `TextField`
- `NumberField`
- `DateField`
- `BooleanField`
- `MoneyField`
- `RelationField`
- `RelationAggregateField`

Field capabilities are explicit (e.g., selectable/filterable/sortable), and tests rely on explicit field registration in each source.

### Enum-backed field type values

`Enums\FieldType` currently defines:

- `string`
- `integer`
- `decimal`
- `boolean`
- `date`
- `datetime`

### Aggregate functions

`Enums\AggregateFunction` currently defines:

- `count`, `sum`, `avg`, `min`, `max`

## Report definition classes

Implemented DTOs for report payloads include:

- `ReportDefinition`
- `SelectedColumn`
- `SortDefinition`
- `FilterGroup`
- `FilterCondition`
- `OutputDefinition`
- `FieldDefinition`
- `ScheduleDefinition`

`ReportDefinition` supports `toArray()/fromArray()` and `toJson()/fromJson()` serialization with `source_key` (not model class names).

## Filter APIs

- Filter operators are implemented as `Enums\FilterOperator` (equality, comparisons, string operations, set operations, null checks, date-relative operators).
- Nested boolean logic is modeled explicitly by `FilterGroup` (`and` / `or`) containing `FilterCondition|FilterGroup` children.
- Query application is implemented through `Query\FilterCompiler` and used by `Query\ReportQueryCompiler`.

## Preview APIs

- `Execution\PreviewRunner::preview(ReportDefinition $definition, int $perPage = 50, int $page = 1): array`
- Returns structured payload with:
  - `columns` (`field_key`, `output_key`, `label`, `type`)
  - `rows`
  - `pagination` (`page`, `per_page`, `total`, `total_pages`)

## Export APIs

- `Execution\ReportRunner::export(ReportDefinition $definition): array`
- `Execution\ExportManager::export(ReportDefinition $definition): array`
- Built-in exporters currently registered:
  - `Execution\CsvExporter` (`csv`)
  - `Execution\XlsxExporter` (`xlsx`)
- Exporter extension point exists via `Contracts\ExporterContract`.

## Persistence APIs

- `Persistence\SavedReportRepository` implemented methods:
  - `saveDefinition(...)`
  - `loadDefinition(SavedReport $savedReport)`
  - `updateDefinition(SavedReport $savedReport, ReportDefinition $definition)`
- Persisted model classes:
  - `Models\SavedReport`
  - `Models\ReportSchedule`
- Migrations for saved reports and schedules are present as package migration stubs.

## Scheduling APIs (implemented now)

- `Scheduling\DueScheduleDiscovery`:
  - `dueSchedules(?Carbon $referenceTime = null): Collection`
  - `isDue(ReportSchedule $schedule, Carbon $referenceTime): bool`
- `ScheduleDefinition` provides frequency-to-cron resolution and custom cron validation.
- `Commands\DiscoverDueSchedulesCommand` exists for due-schedule discovery output.

No end-to-end schedule execution/delivery pipeline is implemented in this repository (discovery/modeling is present).

## APIs/features present in tests but easy to misinterpret

- There is a metadata API route/controller surface (`Http\Controllers\Api\DataSourceController`) for listing registered data-source metadata.
- Exporting supports CSV/XLSX content generation, but queueing/delivery pipelines are not part of current tested behavior.

## Internal classes that should not be treated as user-facing docs surface

Prefer not to present these as primary consumer APIs:

- Query internals: `Query\ReportQueryCompiler`, `Query\FilterCompiler`, `Execution\ReportQueryCompilerAdapter`
- Execution internals: `Execution\RowMapper`
- Registry internals: `Support\SourceRegistry`, `Support\DataSourceRegistry` (document behavior, but avoid encouraging direct mutation outside integration points unless necessary)
- Package bootstrapping and command wiring: `ReportBuilderServiceProvider`, command classes
- Exception classes as primary “getting started” API
