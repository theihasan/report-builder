<?php

namespace Ihasan\ReportBuilder\Contracts;

use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Illuminate\Http\Request;
use Ihasan\ReportBuilder\DTOs\FieldDefinition;

interface DataSourceContract
{
    public function key(): string;

    public function label(): string;

    /**
     * @return array<string, FieldDefinition>
     */
    public function fields(): array;

    public function field(string $key): ?FieldDefinition;

    public function query(): EloquentBuilder|QueryBuilder;

    public function applyScope(EloquentBuilder|QueryBuilder $query, Request $request): EloquentBuilder|QueryBuilder;

    public function authorize(Request $request): bool;
}
