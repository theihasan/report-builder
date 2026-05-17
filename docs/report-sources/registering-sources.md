# Registering Report Sources

The package registers report sources through config and the service provider.

## How registration works internally

`ReportBuilderServiceProvider` builds a `SourceRegistry` singleton and reads `config('report-builder.report_sources', [])`. For each configured class:

1. it must be a string class name,
2. it must implement `ReportSourceContract`,
3. the class is resolved through the container,
4. then registered into `SourceRegistry` by its `key()`.

If a configured class does not implement `ReportSourceContract`, the provider throws `InvalidArgumentException`.

## Where to register in a Laravel app

Add source classes in your application’s published package config:

```php
// config/report-builder.php

return [
    // ...
    'report_sources' => [
        App\ReportSources\OrdersReportSource::class,
        App\ReportSources\CustomersReportSource::class,
    ],
];
```

That is the integration point used by the package.

## How source keys are referenced by report definitions

Report definitions store and use only `sourceKey` strings, for example:

```php
new ReportDefinition(
    sourceKey: 'orders',
    selectedColumns: [
        new SelectedColumn('order_number'),
    ],
);
```

During validation and execution, `SourceRegistry::source($sourceKey)` resolves that key to the registered source instance.

## Common mistake: source key mismatch

A frequent issue is mismatching:

- source class key: `parent::__construct('orders', 'Orders')`
- definition key: `sourceKey: 'order'` (singular typo)

Result: validation fails with unknown source key (or registry resolution fails for missing key).

### Avoid this

- Treat source keys as constants in your app domain.
- Reuse the exact key string across source class, definition builders, and saved definitions.
- Avoid renaming keys once definitions are persisted.
