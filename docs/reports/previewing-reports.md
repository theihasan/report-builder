# Previewing Reports

Use `PreviewRunner` to execute a validated report definition and receive a UI-friendly payload.

## Generate a preview

```php
use Ihasan\ReportBuilder\Execution\PreviewRunner;

$preview = app(PreviewRunner::class)->preview(
    definition: $definition,
    perPage: 50,
    page: 1,
);
```

`perPage` and `page` are clamped to minimum `1`.

## Returned payload shape

`preview()` returns:

```php
[
    'columns' => [
        [
            'field_key' => 'name',
            'output_key' => 'Customer Name',
            'label' => 'Customer Name',
            'type' => 'text',
        ],
    ],
    'rows' => [
        ['Customer Name' => 'Alpha'],
    ],
    'pagination' => [
        'page' => 1,
        'per_page' => 50,
        'total' => 3,
        'total_pages' => 1,
    ],
]
```

## Rows and columns behavior

- `columns[*]` describes display metadata (`field_key`, `output_key`, `label`, `type`).
- `rows[*]` keys match `output_key` (custom label if provided; otherwise field key).
- Column/row order follows `selectedColumns` order.
- Pagination fields are always included.

## UI use case example

Typical UI flow:

1. User configures fields/filters/sorts in a builder.
2. Backend builds `ReportDefinition` DTO.
3. Backend calls `PreviewRunner::preview()`.
4. UI renders `columns` as table headers and `rows` as table body.
5. UI uses `pagination.total` and `pagination.total_pages` for paging controls.

This allows a frontend report builder to present a safe, server-validated preview before export or save.


## Evidence in tests

Examples in this guide are aligned with the package test suite for DTO serialization, validation, query compilation, and preview execution.
