# Relation Field Report Example

Use `RelationField` when you need values from a related model in the same report row.

## Example source

```php
<?php

declare(strict_types=1);

namespace App\Reports\Sources;

use App\Models\Order;
use Ihasan\ReportBuilder\ReportSources\Fields\NumberField;
use Ihasan\ReportBuilder\ReportSources\Fields\RelationField;
use Ihasan\ReportBuilder\ReportSources\ReportSource;
use Illuminate\Database\Eloquent\Builder;

class OrderCustomerSource extends ReportSource
{
    public function __construct()
    {
        parent::__construct('order_customer', 'Order + Customer');
    }

    public function query(): Builder
    {
        return Order::query();
    }

    public function fields(): array
    {
        return [
            NumberField::make('total')->selectable()->sortable()->filterable(),
            RelationField::make('customer.name', 'customer', 'name', 'customer_id'),
            RelationField::make('customer.email', 'customer', 'email', 'customer_id'),
        ];
    }
}
```

## Definition

```php
$definition = new \Ihasan\ReportBuilder\DTOs\ReportDefinition(
    sourceKey: 'order_customer',
    selectedColumns: [
        new \Ihasan\ReportBuilder\DTOs\SelectedColumn('total'),
        new \Ihasan\ReportBuilder\DTOs\SelectedColumn('customer.name'),
    ],
);
```

If an order has no related customer, relation fields resolve to `null` safely in output rows.
