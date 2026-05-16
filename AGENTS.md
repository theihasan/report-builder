# Report Builder Package Agent Instructions

- This is a Laravel package, not a full Laravel application.
- Keep the architecture modular and package-friendly.
- Use strict types in PHP files.
- Add or update tests for every implementation task.
- Do not add UI, database tables, exporters, or scheduling unless the specific task requests it.
- Do not persist arbitrary raw Eloquent model class names in saved report definitions.
- Saved report definitions should reference registered report source keys.
- Report sources define the safe available fields, not automatic unrestricted schema introspection.
- Relation fields and aggregate fields must be modeled explicitly, not treated as magic generic dot-notation.
- Prefer small focused classes and clear public APIs.
- Preserve compatibility with the existing package skeleton.
- Before completing a task, run the appropriate test command available in `composer.json` and report the result.
