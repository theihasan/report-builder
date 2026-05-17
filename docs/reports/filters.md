# Filters

Filters are built as a `FilterGroup` containing one or more `FilterCondition` nodes (and optionally nested groups).

## Supported operators

The package supports the following `FilterOperator` values:

- `=` (`Equals`)
- `!=` (`NotEquals`)
- `>` (`GreaterThan`)
- `>=` (`GreaterThanOrEqual`)
- `<` (`LessThan`)
- `<=` (`LessThanOrEqual`)
- `like` (`Like`)
- `not_like` (`NotLike`)
- `starts_with` (`StartsWith`)
- `ends_with` (`EndsWith`)
- `in` (`In`)
- `not_in` (`NotIn`)
- `between` (`Between`)
- `not_between` (`NotBetween`)
- `is_null` (`IsNull`)
- `is_not_null` (`IsNotNull`)
- `date_equals` (`DateEquals`)
- `date_before` (`DateBefore`)
- `date_after` (`DateAfter`)
- `this_week` (`ThisWeek`)
- `this_month` (`ThisMonth`)
- `this_year` (`ThisYear`)
- `last_n_days` (`LastNDays`)

## Operator/value shapes

- No value required: `is_null`, `is_not_null`, `this_week`, `this_month`, `this_year`
- Scalar value required: `=`, `!=`, `>`, `>=`, `<`, `<=`, `like`, `not_like`, `starts_with`, `ends_with`, `date_equals`, `date_before`, `date_after`
- Array value required: `in`, `not_in`
- Exactly two values required: `between`, `not_between`
- Positive integer required: `last_n_days`

## Practical examples

```php
use Ihasan\ReportBuilder\DTOs\FilterCondition;
use Ihasan\ReportBuilder\DTOs\FilterGroup;
use Ihasan\ReportBuilder\Enums\FilterOperator;

$filters = new FilterGroup('and', [
    // equals
    new FilterCondition('status', FilterOperator::Equals, 'paid'),

    // greater than
    new FilterCondition('amount', FilterOperator::GreaterThan, 100),

    // between
    new FilterCondition('amount', FilterOperator::Between, [100, 500]),

    // in
    new FilterCondition('status', FilterOperator::In, ['paid', 'pending']),

    // null checks
    new FilterCondition('status', FilterOperator::IsNull),
    new FilterCondition('status', FilterOperator::IsNotNull),

    // date filters
    new FilterCondition('created_at', FilterOperator::DateBefore, '2026-05-16'),
    new FilterCondition('created_at', FilterOperator::ThisWeek),
    new FilterCondition('created_at', FilterOperator::LastNDays, 7),
]);
```

## Notes on date-relative operators

- `this_week`, `this_month`, and `this_year` compile to date-range comparisons for the current period.
- `last_n_days` compiles to a `whereBetween` from start-of-day N-1 days ago through end-of-day now.


## Evidence in tests

Examples in this guide are aligned with the package test suite for DTO serialization, validation, query compilation, and preview execution.
