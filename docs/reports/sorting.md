# Sorting

Sorting is defined with one or more `SortDefinition` DTOs.

## Basic field sorting

```php
use Ihasan\ReportBuilder\DTOs\SortDefinition;

new SortDefinition('created_at', 'desc');
```

Allowed directions are only `asc` and `desc` (case-insensitive when hydrating from arrays).

## Multiple sort clauses

You can pass multiple sort definitions; they are applied in array order.

```php
sortDefinitions: [
    new SortDefinition('amount', 'desc'),
    new SortDefinition('created_at', 'asc'),
]
```

## Unsupported sorting cases

### 1) Invalid direction

Constructing `SortDefinition` with another direction throws `InvalidArgumentException`.

```php
new SortDefinition('created_at', 'invalid'); // throws
```

### 2) Source field is not sortable

Validation adds an error at `sorts.{index}.field_key` with message `Sort field is not sortable.`

### 3) Unknown sort field

Validation adds an error at `sorts.{index}.field_key` with message `Sort field is not exposed by source.`

### 4) Relation field sort (blocked at compile time)

`ReportQueryCompiler` throws `InvalidReportDefinitionException` with message path `sorts` for relation fields:

- `Sorting by relation fields is not supported: [<field_key>].`


## Evidence in tests

Examples in this guide are aligned with the package test suite for DTO serialization, validation, query compilation, and preview execution.
