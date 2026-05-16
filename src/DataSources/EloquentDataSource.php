<?php

namespace Ihasan\ReportBuilder\DataSources;

use Closure;
use Ihasan\ReportBuilder\Contracts\DataSourceContract;
use Ihasan\ReportBuilder\DTOs\FieldDefinition;
use Ihasan\ReportBuilder\Exceptions\InvalidDataSourceConfigurationException;
use Ihasan\ReportBuilder\Support\Field;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Illuminate\Http\Request;

class EloquentDataSource implements DataSourceContract
{
    /**
     * @var array<string, FieldDefinition>
     */
    protected array $fields;

    protected Model $model;

    /**
     * @param  array<int, Field|FieldDefinition>  $fields
     */
    public function __construct(
        protected string $key,
        protected string $label,
        Model|string $model,
        array $fields,
        protected ?Closure $scope = null,
        protected ?Closure $authorization = null,
    ) {
        $this->model = $this->resolveModel($model);
        $this->fields = $this->normalizeFields($fields);
    }

    public function key(): string
    {
        return $this->key;
    }

    public function label(): string
    {
        return $this->label;
    }

    /**
     * @return array<string, FieldDefinition>
     */
    public function fields(): array
    {
        return $this->fields;
    }

    public function field(string $key): ?FieldDefinition
    {
        return $this->fields[$key] ?? null;
    }

    public function query(): EloquentBuilder|QueryBuilder
    {
        return $this->model->newQuery();
    }

    public function applyScope(EloquentBuilder|QueryBuilder $query, Request $request): EloquentBuilder|QueryBuilder
    {
        if ($this->scope === null) {
            return $query;
        }

        $scopedQuery = ($this->scope)($query, $request);

        return $scopedQuery instanceof EloquentBuilder || $scopedQuery instanceof QueryBuilder
            ? $scopedQuery
            : $query;
    }

    public function authorize(Request $request): bool
    {
        if ($this->authorization === null) {
            return true;
        }

        return (bool) ($this->authorization)($request);
    }

    /**
     * @param  array<int, Field|FieldDefinition>  $fields
     * @return array<string, FieldDefinition>
     */
    protected function normalizeFields(array $fields): array
    {
        $definitions = [];

        foreach ($fields as $field) {
            $definition = $field instanceof Field ? $field->build() : $field;

            if (! $definition instanceof FieldDefinition) {
                throw InvalidDataSourceConfigurationException::invalidField($this->key, $field);
            }

            if (array_key_exists($definition->key(), $definitions)) {
                throw InvalidDataSourceConfigurationException::duplicateField($this->key, $definition->key());
            }

            $definitions[$definition->key()] = $definition;
        }

        return $definitions;
    }

    protected function resolveModel(Model|string $model): Model
    {
        if ($model instanceof Model) {
            return $model;
        }

        if (! is_string($model) || ! is_subclass_of($model, Model::class)) {
            throw InvalidDataSourceConfigurationException::invalidModel($this->key, $model);
        }

        return new $model;
    }
}
