# Validation

`DefinitionValidator` is the canonical validator for report definitions.

## When validation runs

- Explicitly: call `DefinitionValidator::validate()` or `assertValid()`.
- Implicitly: `ReportQueryCompiler::compile()` calls `assertValid()` before building the query.

## Validation result formats

### Non-throwing path

```php
$errors = app(\Ihasan\ReportBuilder\Validation\DefinitionValidator::class)
    ->validate($definition);
```

Returns:

```php
[
    ['path' => '...', 'message' => '...'],
]
```

### Throwing path

```php
app(\Ihasan\ReportBuilder\Validation\DefinitionValidator::class)
    ->assertValid($definition);
```

Throws `InvalidReportDefinitionException` containing the same structured errors in `->errors()`.

## Example failures

## 1) Unknown source

Path/message:

- `source_key`
- `Unknown report source key.`

## 2) Unknown field

Examples:

- Selected column unknown: `selected_columns.0.field_key` / `Selected field is not exposed by source.`
- Filter field unknown: `filters.children.0.field_key` / `Filter field is not exposed by source.`

## 3) Unsupported operator

If an operator string is not part of the enum and you hydrate via `FilterCondition::fromArray()` (or `ReportDefinition::fromArray()` / JSON path), `FilterOperator::from(...)` throws a PHP `ValueError` before validator-level business checks.

## 4) Malformed filter values

Examples enforced by validator:

- Missing value for value-requiring operators: `Operator requires a value.`
- `between`/`not_between` value not exactly two elements: `Between operators require exactly two values.`
- `in`/`not_in` non-array value: `In operators require array-like values.`
- `last_n_days` non-positive int: `last_n_days requires a positive integer value.`

## Practical try/catch handling

```php
use Ihasan\ReportBuilder\Exceptions\InvalidReportDefinitionException;

try {
    app(\Ihasan\ReportBuilder\Query\ReportQueryCompiler::class)->compile($definition);
} catch (InvalidReportDefinitionException $e) {
    $errors = $e->errors(); // array of path/message errors
}
```


## Evidence in tests

Examples in this guide are aligned with the package test suite for DTO serialization, validation, query compilation, and preview execution.
