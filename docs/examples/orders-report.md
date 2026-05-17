# Orders Report Example

Practical e-commerce example using explicit fields for order number, dates, status, and monetary values.

## Source setup

```php
<?php

declare(strict_types=1);

namespace App\Reports\Sources;

use App\Models\Order;
use Ihasan\ReportBuilder\ReportSources\Fields\DateField;
use Ihasan\ReportBuilder\ReportSources\Fields\MoneyField;
use Ihasan\ReportBuilder\ReportSources\Fields\TextField;
use Ihasan\ReportBuilder\ReportSources\ReportSource;
use Illuminate\Database\Eloquent\Builder;

class OrdersReportSource extends ReportSource
{
    public function __construct()
    {
        parent::__construct('orders', 'Orders');
    }

    public function query(): Builder
    {
        return Order::query();
    }

    public function fields(): array
    {
        return [
            TextField::make('order_number')->selectable()->sortable()->filterable(),
            DateField::make('order_date')->selectable()->sortable()->filterable(),
            TextField::make('status')->selectable()->sortable()->filterable(),
            MoneyField::make('total_amount')->selectable()->sortable()->filterable(),
        ];
    }
}
```

## Definition for finance team

```php
<?php

use Ihasan\ReportBuilder\DTOs\FilterCondition;
use Ihasan\ReportBuilder\DTOs\FilterGroup;
use Ihasan\ReportBuilder\DTOs\OutputDefinition;
use Ihasan\ReportBuilder\DTOs\ReportDefinition;
use Ihasan\ReportBuilder\DTOs\SelectedColumn;
use Ihasan\ReportBuilder\DTOs\SortDefinition;
use Ihasan\ReportBuilder\Enums\FilterOperator;

$definition = new ReportDefinition(
    sourceKey: 'orders',
    selectedColumns: [
        new SelectedColumn('order_number'),
        new SelectedColumn('order_date'),
        new SelectedColumn('status'),
        new SelectedColumn('total_amount', 'Total'),
    ],
    filters: new FilterGroup('and', [
        new FilterCondition('status', FilterOperator::In, ['paid', 'refunded']),
        new FilterCondition('order_date', FilterOperator::GreaterThanOrEqual, '2026-01-01'),
        new FilterCondition('order_date', FilterOperator::LessThanOrEqual, '2026-01-31'),
    ]),
    sortDefinitions: [new SortDefinition('order_date', 'desc')],
    outputDefinition: new OutputDefinition('json'),
);
```

## Export for spreadsheet users

If export formats are registered (CSV and XLSX are built in):

```php
$csv = app(\Ihasan\ReportBuilder\Execution\ReportRunner::class)
    ->export(new ReportDefinition(
        sourceKey: $definition->sourceKey(),
        selectedColumns: $definition->selectedColumns(),
        sortDefinitions: $definition->sortDefinitions(),
        filters: $definition->filters(),
        outputDefinition: new OutputDefinition('csv', 'orders-january.csv'),
    ));

$xlsx = app(\Ihasan\ReportBuilder\Execution\ReportRunner::class)
    ->export(new ReportDefinition(
        sourceKey: $definition->sourceKey(),
        selectedColumns: $definition->selectedColumns(),
        sortDefinitions: $definition->sortDefinitions(),
        filters: $definition->filters(),
        outputDefinition: new OutputDefinition('xlsx', 'orders-january.xlsx'),
    ));
```
