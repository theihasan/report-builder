# Relation Fields

`RelationField` models a selectable field that reads an attribute from a related model using explicit relation metadata.

## API shape

```php
RelationField::make(
    string $key,
    string $relation,
    string $attribute,
    string $foreignKey,
)
```

Type returned: `relation`.

## Example 1: Order -> Customer Name

```php
RelationField::make('customer.name', 'customer', 'name', 'customer_id')
    ->label('Customer Name');
```

Use case: include customer name in an order-based source.

## Example 2: Order -> Customer Email

```php
RelationField::make('customer.email', 'customer', 'email', 'customer_id')
    ->label('Customer Email');
```

In fixture-backed tests, missing relation rows safely map to `null` in output.

## Example 3: Ticket -> Assignee Name

```php
RelationField::make('assignee.name', 'assignee', 'name', 'assignee_id')
    ->label('Assignee Name');
```

Use this style whenever you want a flat report column sourced from a relation attribute.

## Supported behavior and limitations

- Relation fields are explicit declarations, not inferred from generic dot-notation.
- You must provide relation name, related attribute, and foreign key.
- Keep field key strings stable (`customer.name`, etc.) because definitions store those keys.

### Filtering and sorting

`RelationField` inherits fluent `filterable()` and `sortable()` methods from `Field`, but relation-field filter/sort execution support should be enabled only if your source/query behavior is designed for it.

### Validation behavior

Validation uses source field definitions:

- if a relation field key is declared and selectable, it can be selected;
- unknown keys fail validation;
- non-filterable fields fail when used in filters;
- non-sortable fields fail when used in sorts.
