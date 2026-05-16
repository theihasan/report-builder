# Architecture Direction

This package is a Laravel report builder engine with a modular, package-first architecture.

## Core Building Blocks

### SourceRegistry
A central registry that manages all registered report sources by stable source keys. It acts as the only entry point for resolving report sources during validation, compilation, and execution.

### ReportSource
A report source defines how a reportable domain is exposed to the engine. It should declare metadata, available fields, supported relations/aggregates, and any source-specific constraints.

### Field Definitions
Fields are explicitly modeled descriptors of safe reportable data. They define names, labels, types, capabilities (filter/sort/group/aggregate), and relation context where applicable.

### ReportDefinition
A serializable, code-driven definition of a report request. It includes source key, selected fields, filter tree, sorting, grouping, aggregate requests, and pagination.

### Filter Tree
Filters should be modeled as an explicit tree structure (groups + rules) to support deterministic boolean logic, nesting, and safe validation.

### Validation Layer
A dedicated validation layer verifies that every report definition is valid for the referenced source. It should enforce source keys, field availability, operator compatibility, and type correctness.

### Query Compiler
A compiler translates validated report definitions into executable query instructions for the underlying data source implementation.

### Export Drivers
Exports should be pluggable through explicit drivers so output formats can be extended without coupling core report-definition logic to specific export concerns.

## Later Phases

### Persistence (Later)
Saved report definitions will be stored as JSON and should reference registered source keys instead of arbitrary model class names.

### Scheduling (Later)
Scheduling should be introduced only after core modeling, validation, and compilation workflows are stable.
