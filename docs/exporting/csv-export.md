# CSV Export

Use `ReportRunner::export()` with `OutputDefinition('csv', ?filename)`.

## Example

```php
use Ihasan\ReportBuilder\DTOs\OutputDefinition;
use Ihasan\ReportBuilder\DTOs\ReportDefinition;
use Ihasan\ReportBuilder\DTOs\SelectedColumn;
use Ihasan\ReportBuilder\Execution\ReportRunner;

$definition = new ReportDefinition(
    sourceKey: 'orders',
    selectedColumns: [
        new SelectedColumn('status', 'State'),
        new SelectedColumn('name', 'Customer Name'),
        new SelectedColumn('amount'),
    ],
    outputDefinition: new OutputDefinition('csv', 'orders-export.csv'),
);

$export = app(ReportRunner::class)->export($definition);

header('Content-Type: '.$export['mime_type']);
header('Content-Disposition: attachment; filename="'.$export['filename'].'"');
echo $export['content'];
```

## Filename behavior

- If a filename is provided in `OutputDefinition`, that value is returned.
- If omitted, default filename is `report.csv`.

## Headers and selected column labels

The first CSV row is always the selected columns in **selected order**.

Header values use output keys from `RowMapper::outputKey()`:

- If `SelectedColumn` has a custom label, that label is used.
- Otherwise, the field key is used.

So this column list:

```php
[
    new SelectedColumn('status', 'State'),
    new SelectedColumn('name', 'Customer Name'),
    new SelectedColumn('amount'),
]
```

produces header row:

```text
State,Customer Name,amount
```

## Data row ordering

Each row is mapped and emitted in the exact same column order as `selectedColumns`.

## Empty dataset behavior

CSV exports still include the header row even when no rows match the query.

## MIME type

CSV responses use:

`text/csv; charset=UTF-8`

## Limitations

- Package returns the CSV content string; writing to disk/streaming is the consuming app’s responsibility.
- No built-in background queue/export-job abstraction is used by `CsvExporter` itself.
