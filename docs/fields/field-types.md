# Field Types

This page documents scalar field classes implemented in `src/ReportSources/Fields`.

> Note: filterable/sortable/selectable are capability flags inherited from `Field` and controlled per field instance via fluent methods.

## TextField

Represents text/string-like values.

```php
TextField::make('status')
    ->label('Status')
    ->selectable()
    ->filterable()
    ->sortable();
```

Typical use cases:

- status,
- order number,
- names,
- email-like display strings.

Filterable/sortable support:

- Supports `filterable(true|false)` and `sortable(true|false)` via base `Field` API.
- Defaults are `false` until enabled.

## NumberField

Represents numeric values.

```php
NumberField::make('item_count')
    ->label('Item Count')
    ->selectable()
    ->sortable();
```

Typical use cases:

- counts,
- quantities,
- integer KPI values.

Filterable/sortable support:

- Supports `filterable()` and `sortable()` via base `Field` API.
- Defaults are `false` until enabled.

## DateField

Represents date/datetime-like values.

```php
DateField::make('created_at')
    ->label('Created At')
    ->selectable()
    ->filterable()
    ->sortable();
```

Typical use cases:

- creation date,
- placement date,
- lifecycle milestone timestamps.

Filterable/sortable support:

- Supports `filterable()` and `sortable()` via base `Field` API.
- Defaults are `false` until enabled.

## BooleanField

Represents true/false flags.

```php
BooleanField::make('is_active')
    ->label('Is Active')
    ->selectable()
    ->filterable();
```

Typical use cases:

- active/inactive,
- archived/not archived,
- verification flags.

Filterable/sortable support:

- Supports `filterable()` and `sortable()` via base `Field` API.
- Defaults are `false` until enabled.

## MoneyField

Represents monetary values and optionally stores a currency code.

```php
MoneyField::make('grand_total')
    ->label('Grand Total')
    ->currency('USD')
    ->selectable()
    ->sortable();
```

Typical use cases:

- order totals,
- invoice totals,
- payable/receivable numeric amounts.

Filterable/sortable support:

- Supports `filterable()` and `sortable()` via base `Field` API.
- Defaults are `false` until enabled.

Money-specific behavior:

- `currency('USD')` sets currency metadata.
- `currencyCode()` returns configured code or `null`.
