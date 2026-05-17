# Nested Filter Groups (AND/OR)

`FilterGroup` lets you express grouped boolean logic explicitly.

- group boolean: `'and'` or `'or'`
- children: `FilterCondition` and/or nested `FilterGroup`

## Simple AND group

### Visual logic

`status = paid AND amount > 100`

### PHP

```php
$group = new \Ihasan\ReportBuilder\DTOs\FilterGroup('and', [
    new \Ihasan\ReportBuilder\DTOs\FilterCondition('status', \Ihasan\ReportBuilder\Enums\FilterOperator::Equals, 'paid'),
    new \Ihasan\ReportBuilder\DTOs\FilterCondition('amount', \Ihasan\ReportBuilder\Enums\FilterOperator::GreaterThan, 100),
]);
```

## OR group

### Visual logic

`status = paid OR status IS NULL`

### PHP

```php
$group = new \Ihasan\ReportBuilder\DTOs\FilterGroup('or', [
    new \Ihasan\ReportBuilder\DTOs\FilterCondition('status', \Ihasan\ReportBuilder\Enums\FilterOperator::Equals, 'paid'),
    new \Ihasan\ReportBuilder\DTOs\FilterCondition('status', \Ihasan\ReportBuilder\Enums\FilterOperator::IsNull),
]);
```

## Nested group

### Visual logic

`amount > 40 AND (status = paid OR status IS NULL)`

### PHP

```php
$group = new \Ihasan\ReportBuilder\DTOs\FilterGroup('and', [
    new \Ihasan\ReportBuilder\DTOs\FilterCondition('amount', \Ihasan\ReportBuilder\Enums\FilterOperator::GreaterThan, 40),
    new \Ihasan\ReportBuilder\DTOs\FilterGroup('or', [
        new \Ihasan\ReportBuilder\DTOs\FilterCondition('status', \Ihasan\ReportBuilder\Enums\FilterOperator::Equals, 'paid'),
        new \Ihasan\ReportBuilder\DTOs\FilterCondition('status', \Ihasan\ReportBuilder\Enums\FilterOperator::IsNull),
    ]),
]);
```

This exact structure is exercised by query compiler tests and returns rows matching the intended grouped boolean logic.

## Serialization of nested groups

Both `FilterGroup` and `FilterCondition` support `toArray()`/`fromArray()`, including nested trees, so definitions can be stored/reloaded without losing structure.


## Evidence in tests

Examples in this guide are aligned with the package test suite for DTO serialization, validation, query compilation, and preview execution.
