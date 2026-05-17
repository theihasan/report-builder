# Troubleshooting

## Source key is not registered

**Symptoms**
- Validation fails or execution throws source-not-found errors.

**Checks**
1. Confirm your source class is listed in `config/report-builder.php` under `report_sources`.
2. Confirm the `sourceKey` in `ReportDefinition` matches the source constructor key exactly.
3. Clear config cache (`php artisan config:clear`) after config changes.

## Field not found in selected columns, sort, or filters

**Symptoms**
- Validation errors like unknown field key.

**Checks**
1. Ensure the field key exists in your source `fields()` array.
2. Verify exact key spelling for relation/aggregate fields (for example `customer.name`, `orders_count`).
3. Ensure the field is marked `selectable()`, `sortable()`, and/or `filterable()` as needed.

## Invalid sort/filter configuration

**Symptoms**
- Validator rejects operator/direction or filter structure.

**Checks**
1. Sort directions should be `asc` or `desc`.
2. Use valid `FilterOperator` values for the field type.
3. Ensure nested `FilterGroup` logic (`and`/`or`) is valid.

## Relation field unsupported for your query

**Symptoms**
- SQL errors or null values where relation data is expected.

**Checks**
1. Define relation fields explicitly using `RelationField::make(...)`.
2. Confirm relation name and foreign key arguments match your Eloquent model relationship.
3. Confirm related columns exist and are queryable.

## Export output not generated

**Symptoms**
- Export format rejected or empty payload when you expect rows.

**Checks**
1. Confirm `outputDefinition` format is supported (`csv` or `xlsx`).
2. Confirm selected columns are valid/selectable.
3. Preview first to validate row availability before export.
4. Ensure `maatwebsite/excel` dependency is installed for XLSX support.

## Migrations/config not published or not migrated

**Symptoms**
- Missing saved report/schedule tables.

**Checks**
1. Publish package assets.
2. Run migrations.
3. Verify tables `report_builder_saved_reports` and `report_builder_report_schedules` exist.

## Docs/API mismatch with installed version

**Symptoms**
- Methods/classes in docs do not exist locally.

**Checks**
1. Confirm installed package version in `composer.lock`.
2. Compare docs to your installed tag/branch.
3. Update package or use docs matching your installed version.
