# Columns and Labels

Use `SelectedColumn` to choose exactly which source fields appear in report output.

## Selecting fields

```php
use Ihasan\ReportBuilder\DTOs\ReportDefinition;
use Ihasan\ReportBuilder\DTOs\SelectedColumn;

$definition = new ReportDefinition(
    sourceKey: 'orders',
    selectedColumns: [
        new SelectedColumn('name'),
        new SelectedColumn('amount'),
        new SelectedColumn('created_at'),
    ],
);
```

Validation requires selected fields to exist in the source and be marked selectable.

## Custom labels

`SelectedColumn` supports an optional `label`:

```php
new SelectedColumn('name', 'Customer Name');
```

In preview output:

- `columns[*].label` uses custom label when provided.
- `columns[*].output_key` becomes the custom label.
- row keys use the same `output_key`.

Example from preview behavior:

```php
$definition = new ReportDefinition(
    sourceKey: 'orders',
    selectedColumns: [new SelectedColumn('name', 'Customer Name')],
);

$preview = app(\Ihasan\ReportBuilder\Execution\PreviewRunner::class)->preview($definition);

// row keys: ['Customer Name']
```

## Selected column ordering

The effective output order is the array order in `selectedColumns`.

```php
selectedColumns: [
    new SelectedColumn('status', 'State'),
    new SelectedColumn('name'),
    new SelectedColumn('amount', 'Amount'),
]
```

Preview row keys follow this same order:

- `State`
- `name`
- `Amount`

`SelectedColumn` also has `order` and `visible` properties for metadata and serialization, but row output ordering is proven by the selected column array sequence.


## Evidence in tests

Examples in this guide are aligned with the package test suite for DTO serialization, validation, query compilation, and preview execution.
