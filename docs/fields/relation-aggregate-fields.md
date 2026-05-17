# Relation Aggregate Fields

`RelationAggregateField` models aggregate values computed from a relation.

Type returned: `relation_aggregate`.

## Supported aggregate builders

Implemented aggregate configuration methods:

- `countRelation(string $relation)`
- `sumRelation(string $relation, string $attribute)`
- `avgRelation(string $relation, string $attribute)`
- `minRelation(string $relation, string $attribute)`
- `maxRelation(string $relation, string $attribute)`

## Example 1: Customer order count

```php
RelationAggregateField::make('orders_count')
    ->countRelation('orders')
    ->label('Orders Count')
    ->selectable()
    ->sortable();
```

## Example 2: Customer total revenue

```php
RelationAggregateField::make('orders_total_sum')
    ->sumRelation('orders', 'total')
    ->label('Total Revenue')
    ->selectable()
    ->sortable();
```

## Example 3: Author post count

```php
RelationAggregateField::make('posts_count')
    ->countRelation('posts')
    ->label('Posts Count')
    ->selectable()
    ->sortable();
```

## Selecting, sorting, filtering behavior

- Selectable: yes (default true).
- Sortable: yes (default true).
- Filterable: no (default false).

The package test suite confirms aggregate selection and sorting, and confirms filtering an aggregate field is rejected by validation (`Filter field is not filterable.`).

## Implemented limitations

- Aggregates must be declared explicitly as `RelationAggregateField` instances.
- Filtering on aggregate fields is not supported by default behavior.
- Keep aggregate field keys stable because definitions reference key strings directly.
