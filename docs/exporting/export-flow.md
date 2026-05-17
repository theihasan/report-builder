# Export Flow

This package resolves exports by reading the requested output format from a `ReportDefinition`, then routing to a registered exporter driver.

## High-level flow

`ReportDefinition` → `ExportManager` → concrete exporter (`CsvExporter` or `XlsxExporter`).

1. Your code calls `ReportRunner::export($definition)`.
2. `ReportRunner` delegates to `ExportManager::export($definition)`.
3. `ExportManager` reads `$definition->outputDefinition()->format()`, lowercases it, and finds a matching registered exporter.
4. The matching exporter compiles the report query, maps selected columns in order, and returns an export payload:
   - `filename`
   - `mime_type`
   - `content` (raw file bytes / text)

If no exporter is registered for the requested format, an `InvalidArgumentException` is thrown.

## Driver registration and resolution

The service provider registers both exporters into `ExportManager`:

- `CsvExporter` (`format() === 'csv'`)
- `XlsxExporter` (`format() === 'xlsx'`)

Resolution is case-insensitive because formats are normalized to lowercase.

## Real usage example

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

// $export = [
//   'filename' => 'orders-export.csv',
//   'mime_type' => 'text/csv; charset=UTF-8',
//   'content' => 'State,"Customer Name",amount\npaid,Alpha,120\n...'
// ];
```

## Limitations

- Export execution is synchronous in the package APIs shown above.
- `ReportRunner::run()` returns preview-style data, while `ReportRunner::export()` returns file payloads.
- No built-in export history/run logging is wired into `ExportManager` or `ReportRunner`.
