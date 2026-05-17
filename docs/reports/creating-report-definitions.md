# Creating Report Definitions

A `ReportDefinition` is the package's runtime-safe contract for what to query and how to present it. It is an explicit DTO (not a free-form array), and it is designed to be serialized to/from arrays or JSON. 

## Core structure

A definition contains:

- `sourceKey` (required): key of a **registered** report source.
- `selectedColumns` (required for valid execution): array of `SelectedColumn` DTOs.
- `sortDefinitions` (optional): array of `SortDefinition` DTOs.
- `filters` (optional): `FilterGroup` tree.
- `outputDefinition` (optional): output format/filename metadata.
- `version` (optional): integer schema/version marker.

## Build a definition with the real API

```php
<?php

use Ihasan\ReportBuilder\DTOs\FilterCondition;
use Ihasan\ReportBuilder\DTOs\FilterGroup;
use Ihasan\ReportBuilder\DTOs\OutputDefinition;
use Ihasan\ReportBuilder\DTOs\ReportDefinition;
use Ihasan\ReportBuilder\DTOs\SelectedColumn;
use Ihasan\ReportBuilder\DTOs\SortDefinition;
use Ihasan\ReportBuilder\Enums\FilterOperator;

$definition = new ReportDefinition(
    sourceKey: 'orders',
    selectedColumns: [
        new SelectedColumn('name'),
        new SelectedColumn('status', 'State'),
        new SelectedColumn('amount', 'Order Amount'),
        new SelectedColumn('created_at', 'Created At'),
    ],
    sortDefinitions: [
        new SortDefinition('amount', 'desc'),
        new SortDefinition('created_at', 'asc'),
    ],
    filters: new FilterGroup('and', [
        new FilterCondition('status', FilterOperator::In, ['paid', 'pending']),
        new FilterCondition('amount', FilterOperator::GreaterThanOrEqual, 50),
    ]),
    outputDefinition: new OutputDefinition('json'),
    version: 1,
);
```

## Source key

The `sourceKey` must match a key from a source already registered in `SourceRegistry`; otherwise validation fails with `source_key` errors. In tests, `orders` is used as the source key for source-backed report definitions.

## Selected columns

Use `SelectedColumn` DTOs, not raw strings. Each selected column can carry:

- `fieldKey` (`string`, required)
- `label` (`?string`, optional)
- `order` (`?int`, optional metadata)
- `visible` (`bool`, default `true`)

## Output metadata

`outputDefinition` is an `OutputDefinition` DTO with:

- `format` (`string`, required)
- `filename` (`?string`, optional)

Common examples:

```php
new OutputDefinition('json');
new OutputDefinition('csv', 'orders-report');
new OutputDefinition('xlsx', 'orders-export');
```

## Full array and JSON examples

### Array shape (`toArray()`)

```php
$array = $definition->toArray();
```

This produces a shape with keys:

- `source_key`
- `selected_columns`
- `sorts`
- `filters`
- `output`
- `version`

### JSON round trip

```php
$json = $definition->toJson();
$hydrated = ReportDefinition::fromJson($json);

assert($hydrated->toArray() === $definition->toArray());
```

### Building incrementally

```php
$definition = new ReportDefinition(sourceKey: 'orders');

$definition
    ->addSelectedColumn(new SelectedColumn('name'))
    ->addSelectedColumn(new SelectedColumn('amount', 'Amount'))
    ->addSortDefinition(new SortDefinition('created_at', 'desc'))
    ->setFilters(new FilterGroup('and', [
        new FilterCondition('status', FilterOperator::Equals, 'paid'),
    ]));
```


## Evidence in tests

Examples in this guide are aligned with the package test suite for DTO serialization, validation, query compilation, and preview execution.
