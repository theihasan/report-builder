# Customer Revenue Report Example

This example demonstrates relation aggregates for customer analytics.

## Use case

For each customer, show:

- customer name
- number of orders
- total revenue across related orders

## Source with relation aggregate fields

```php
<?php

declare(strict_types=1);

namespace App\Reports\Sources;

use App\Models\Customer;
use Ihasan\ReportBuilder\ReportSources\Fields\RelationAggregateField;
use Ihasan\ReportBuilder\ReportSources\Fields\TextField;
use Ihasan\ReportBuilder\ReportSources\ReportSource;
use Illuminate\Database\Eloquent\Builder;

class CustomerRevenueSource extends ReportSource
{
    public function __construct()
    {
        parent::__construct('customers_revenue', 'Customer Revenue');
    }

    public function query(): Builder
    {
        return Customer::query();
    }

    public function fields(): array
    {
        return [
            TextField::make('name')->selectable()->sortable(),
            RelationAggregateField::make('orders_count')
                ->countRelation('orders')
                ->selectable()
                ->sortable(),
            RelationAggregateField::make('orders_total_sum')
                ->sumRelation('orders', 'total')
                ->selectable()
                ->sortable(),
        ];
    }
}
```

## Build and preview

```php
<?php

use Ihasan\ReportBuilder\DTOs\ReportDefinition;
use Ihasan\ReportBuilder\DTOs\SelectedColumn;
use Ihasan\ReportBuilder\DTOs\SortDefinition;

$definition = new ReportDefinition(
    sourceKey: 'customers_revenue',
    selectedColumns: [
        new SelectedColumn('name'),
        new SelectedColumn('orders_count', 'Order Count'),
        new SelectedColumn('orders_total_sum', 'Total Revenue'),
    ],
    sortDefinitions: [new SortDefinition('orders_total_sum', 'desc')],
);

$preview = app(\Ihasan\ReportBuilder\Execution\PreviewRunner::class)
    ->preview($definition, perPage: 50, page: 1);
```

> Note: aggregate fields can be selected/sorted when configured as sortable; filtering aggregate fields is rejected unless you explicitly model a filterable strategy.
