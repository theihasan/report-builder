# FAQ

## Can I use this without a UI?

Yes. The package is headless and service-driven. You can build/report definitions in PHP and run preview/export flows directly.

## Can I build my own UI on top of it?

Yes. Build your UI to construct `ReportDefinition` payloads, then validate and execute through package services.

## Does it expose arbitrary model columns automatically?

No. Fields are explicit per report source. Only registered/selectable fields are available.

## Can I save reports?

Yes. `SavedReportRepository` provides `saveDefinition`, `loadDefinition`, and `updateDefinition`.

## Can I export Excel?

Yes. XLSX export is supported through the built-in exporter when using `OutputDefinition('xlsx')`.

## Can I use relation fields?

Yes, when explicitly modeled via `RelationField` in the source.

## Can I use aggregates?

Yes, relation aggregates are supported via `RelationAggregateField` for explicit aggregate keys.

## What Laravel versions are supported?

The package supports `illuminate/contracts` `^11.0 || ^12.0 || ^13.0`.
