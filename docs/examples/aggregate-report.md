# Aggregate Report Recipe (End-to-End)

This recipe builds a source-backed aggregate report in five steps.

## Step 1: Create explicit aggregate fields

Model each aggregate as an explicit `RelationAggregateField` key (for example `orders_count`, `orders_total_sum`).

## Step 2: Register the source

Add your source class to `config/report-builder.php` under `report_sources`.

## Step 3: Build a definition

```php
$definition = new \Ihasan\ReportBuilder\DTOs\ReportDefinition(
    sourceKey: 'customers_revenue',
    selectedColumns: [
        new \Ihasan\ReportBuilder\DTOs\SelectedColumn('name'),
        new \Ihasan\ReportBuilder\DTOs\SelectedColumn('orders_count'),
        new \Ihasan\ReportBuilder\DTOs\SelectedColumn('orders_total_sum'),
    ],
    sortDefinitions: [
        new \Ihasan\ReportBuilder\DTOs\SortDefinition('orders_total_sum', 'desc'),
    ],
);
```

## Step 4: Validate then preview

```php
app(\Ihasan\ReportBuilder\Validation\DefinitionValidator::class)->assertValid($definition);

$preview = app(\Ihasan\ReportBuilder\Execution\PreviewRunner::class)->preview($definition);
```

## Step 5: Export

```php
$export = app(\Ihasan\ReportBuilder\Execution\ReportRunner::class)->export(
    new \Ihasan\ReportBuilder\DTOs\ReportDefinition(
        sourceKey: $definition->sourceKey(),
        selectedColumns: $definition->selectedColumns(),
        sortDefinitions: $definition->sortDefinitions(),
        outputDefinition: new \Ihasan\ReportBuilder\DTOs\OutputDefinition('csv', 'customer-revenue.csv'),
    )
);
```

You now have a safe aggregate workflow with explicit field modeling and source-key based definitions.
