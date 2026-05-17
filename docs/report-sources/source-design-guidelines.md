# Source Design Guidelines

These guidelines follow the package’s implemented source-driven architecture.

## Why sources should expose safe reportable fields

A source is an explicit allow-list of fields. Validation and query compilation check selected/filter/sort keys against `fields()` on that source. This prevents accidental access to undeclared columns or model internals.

## Why arbitrary model exposure is avoided

The package persists report definitions using a `sourceKey` plus field keys—not raw model class names in the report definition payload. That keeps persistence stable and avoids coupling saved reports to arbitrary model class references.

## Practical design advice

### 1) Use one source per reportable business area

Examples:

- `orders` source for order operations reporting,
- `customers` source for customer-level reporting,
- `invoices` source for billing reporting.

This keeps `fields()` focused and reduces overexposure of unrelated fields.

### 2) Keep field keys stable

`SelectedColumn`, sort definitions, and filter conditions all reference field keys by string. Renaming keys breaks existing saved definitions.

### 3) Avoid exposing sensitive fields

If a field should not be reportable, do not add it to `fields()`. The source is where exposure is controlled.

### 4) Design labels for UI readiness

Fields default labels from the key, but `label('...')` lets you provide intentional display names (`Order Number`, `Customer Name`, etc.).

### 5) Explicitly model relations and aggregates

Use `RelationField` and `RelationAggregateField` instead of generic dot-notation magic. Define relation name, attribute, foreign key (for relation fields), and aggregate function configuration explicitly.
