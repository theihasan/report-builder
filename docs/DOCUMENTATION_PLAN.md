# Documentation Plan

This plan proposes the final documentation tree and prioritizes pages as **required**, **optional**, or **future**.

## Beginner guides

- **required**: `docs/installation.md` — package install, config publish, route registration basics.
- **required**: `docs/quickstart.md` — minimal end-to-end source -> definition -> preview -> export flow.
- **required**: `docs/concepts.md` — source keys, fields, definitions, validation-first mindset.
- **optional**: `docs/troubleshooting.md` — common setup and validation mistakes.

## Intermediate guides

- **required**: `docs/report-sources.md` — creating `ReportSource` classes, key/label/query/fields.
- **required**: `docs/fields.md` — field classes, capabilities (select/filter/sort), aliases and relation fields.
- **required**: `docs/report-definitions.md` — DTO structure (`ReportDefinition`, columns, sorts, filters, output).
- **required**: `docs/filtering-and-sorting.md` — filter group trees, operators, sort rules.
- **required**: `docs/previewing-reports.md` — `PreviewRunner` payload shape and pagination.
- **required**: `docs/exporting.md` — CSV/XLSX through `ReportRunner`/`ExportManager`.
- **optional**: `docs/http-api.md` — metadata endpoint behavior and integration notes.

## Advanced guides

- **required**: `docs/persistence.md` — saved report model/repository lifecycle and invariants.
- **required**: `docs/scheduling.md` — schedule model, frequency/cron behavior, due discovery.
- **optional**: `docs/custom-exporters.md` — implementing `ExporterContract` and registering formats.
- **optional**: `docs/custom-data-sources.md` — `DataSourceContract` usage and authorization checks.
- **future**: `docs/performance.md` — pagination, query tuning, large dataset strategy.
- **future**: `docs/security.md` — hardening guidelines and field-level safety patterns.

## Reference docs

- **required**: `docs/reference/api-survey.md` — generated from `docs/API_SURVEY.md` and kept factual.
- **required**: `docs/reference/configuration.md` — all `config/report-builder.php` keys.
- **required**: `docs/reference/filter-operators.md` — `FilterOperator` enum values and semantics.
- **required**: `docs/reference/field-types.md` — `FieldType` enum and class mappings.
- **required**: `docs/reference/aggregates.md` — aggregate functions and relation aggregate fields.
- **optional**: `docs/reference/exceptions.md` — user-actionable exceptions.
- **future**: `docs/reference/changelog-notes.md` — upgrade-oriented summaries per release.

## Proposed docs tree

```text
docs/
  index.md
  installation.md
  quickstart.md
  concepts.md
  report-sources.md
  fields.md
  report-definitions.md
  filtering-and-sorting.md
  previewing-reports.md
  exporting.md
  persistence.md
  scheduling.md
  http-api.md
  custom-exporters.md
  custom-data-sources.md
  troubleshooting.md
  performance.md
  security.md
  reference/
    api-survey.md
    configuration.md
    filter-operators.md
    field-types.md
    aggregates.md
    exceptions.md
    changelog-notes.md
```
