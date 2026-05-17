# XLSX Export

Use `ReportRunner::export()` with `OutputDefinition('xlsx', ?filename)`.

## Example

```php
use Ihasan\ReportBuilder\DTOs\OutputDefinition;
use Ihasan\ReportBuilder\DTOs\ReportDefinition;
use Ihasan\ReportBuilder\DTOs\SelectedColumn;
use Ihasan\ReportBuilder\Execution\ReportRunner;

$definition = new ReportDefinition(
    sourceKey: 'orders_xlsx',
    selectedColumns: [
        new SelectedColumn('amount', 'Total Amount'),
        new SelectedColumn('name', 'Customer Name'),
    ],
    outputDefinition: new OutputDefinition('xlsx', 'orders-export.xlsx'),
);

$export = app(ReportRunner::class)->export($definition);

header('Content-Type: '.$export['mime_type']);
header('Content-Disposition: attachment; filename="'.$export['filename'].'"');
echo $export['content'];
```

## Dependency and generation strategy

XLSX export is implemented through `maatwebsite/excel` (`Excel::raw(..., Excel::XLSX)`).

The exporter:

1. Builds headings from selected columns (same output-key rules as CSV).
2. Compiles and iterates the report query.
3. Builds a two-dimensional row array.
4. Produces raw XLSX binary content.

## Filename behavior

- Provided filename is returned as-is.
- Default filename is `report.xlsx`.

## MIME type

`application/vnd.openxmlformats-officedocument.spreadsheetml.sheet`

## Differences from CSV

- **Format:** binary XLSX vs plain text CSV.
- **Writer engine:** XLSX uses Laravel Excel; CSV uses native `fputcsv` on a temp stream.
- **Typed cells:** tests verify numeric/date-compatible cells are emitted in Excel-compatible types.

## Limitations

- Package returns raw bytes only; saving to storage or HTTP streaming is handled by consuming app code.
- No package-level run tracking is attached to XLSX exports.
