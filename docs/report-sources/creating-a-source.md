# Creating a Report Source

A **report source** is the package’s explicit contract for one reportable dataset. A source defines:

- a stable source key (`key()`),
- a display label (`label()`),
- a base Eloquent query (`query()`), and
- an explicit list of allowed fields (`fields()`).

In this package, the standard way to create one is extending `Ihasan\ReportBuilder\ReportSources\ReportSource`.

## Complete source example

```php
<?php

declare(strict_types=1);

namespace App\ReportSources;

use App\Models\Order;
use Ihasan\ReportBuilder\ReportSources\Fields\DateField;
use Ihasan\ReportBuilder\ReportSources\Fields\MoneyField;
use Ihasan\ReportBuilder\ReportSources\Fields\NumberField;
use Ihasan\ReportBuilder\ReportSources\Fields\RelationField;
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
            TextField::make('order_number')->label('Order Number')->selectable()->filterable()->sortable(),
            TextField::make('status')->label('Status')->selectable()->filterable(),
            DateField::make('placed_at')->label('Placed At')->selectable()->filterable()->sortable(),
            NumberField::make('item_count')->label('Item Count')->selectable()->sortable(),
            MoneyField::make('grand_total')->label('Grand Total')->currency('USD')->selectable()->sortable(),
            RelationField::make('customer.name', 'customer', 'name', 'customer_id')->label('Customer Name'),
        ];
    }
}
```

## Source key and label

When you call `parent::__construct('orders', 'Orders')`:

- `'orders'` becomes the persisted/report-definition `sourceKey`.
- `'Orders'` becomes the human-facing label for metadata and UI consumers.

Keep the key stable over time, because report definitions and saved reports reference that key directly.

## Query method behavior

`ReportSource::query()` in the base class throws a `BadMethodCallException`, so concrete sources must implement it.

Typical pattern:

```php
public function query(): Builder
{
    return Order::query();
}
```

The package query compiler uses this builder as the base query and then applies selected fields, filters, relation fields, relation aggregates, and sort definitions.

## Fields definition behavior

`fields()` must return an array of `FieldContract` instances. The source is the allow-list of reportable fields.

You can inspect fields with:

- `hasField(string $key): bool`
- `field(string $key): ?FieldContract`

These methods are used by validation and query compilation to enforce only declared field keys.

## Realistic `OrdersReportSource` pattern

A practical production source usually includes:

- core scalar order fields (`order_number`, `status`, `placed_at`, totals),
- customer-facing relation fields (`customer.name`), and
- explicit filter/sort flags per field.

That pattern matches how the package validates definitions and safely compiles queries.
