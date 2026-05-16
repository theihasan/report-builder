<?php

declare(strict_types=1);

namespace Ihasan\ReportBuilder\Query;

use Ihasan\ReportBuilder\DTOs\FilterCondition;
use Ihasan\ReportBuilder\DTOs\FilterGroup;
use Ihasan\ReportBuilder\Enums\FilterOperator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;

class FilterCompiler
{
    public function apply(Builder $query, FilterGroup $group): void
    {
        $this->applyGroup($query, $group, false);
    }

    private function applyGroup(Builder $query, FilterGroup $group, bool $nested): void
    {
        $callback = function (Builder $builder) use ($group): void {
            foreach ($group->children() as $child) {
                if ($child instanceof FilterGroup) {
                    $method = $child->boolean() === 'or' ? 'orWhere' : 'where';
                    $builder->{$method}(fn (Builder $nestedBuilder): Builder => $this->compileGroupCallback($nestedBuilder, $child));

                    continue;
                }

                $this->applyCondition($builder, $child, $group->boolean() === 'or');
            }
        };

        if ($nested) {
            $query->{$group->boolean() === 'or' ? 'orWhere' : 'where'}($callback);

            return;
        }

        $query->where($callback);
    }

    private function compileGroupCallback(Builder $builder, FilterGroup $group): Builder
    {
        foreach ($group->children() as $child) {
            if ($child instanceof FilterGroup) {
                $method = $child->boolean() === 'or' ? 'orWhere' : 'where';
                $builder->{$method}(fn (Builder $nestedBuilder): Builder => $this->compileGroupCallback($nestedBuilder, $child));

                continue;
            }

            $this->applyCondition($builder, $child, $group->boolean() === 'or');
        }

        return $builder;
    }

    private function applyCondition(Builder $query, FilterCondition $condition, bool $or): void
    {
        $method = $or ? 'orWhere' : 'where';
        $field = $condition->fieldKey();

        match ($condition->operator()) {
            FilterOperator::Equals => $query->{$method}($field, '=', $condition->value()),
            FilterOperator::NotEquals => $query->{$method}($field, '!=', $condition->value()),
            FilterOperator::GreaterThan => $query->{$method}($field, '>', $condition->value()),
            FilterOperator::GreaterThanOrEqual => $query->{$method}($field, '>=', $condition->value()),
            FilterOperator::LessThan => $query->{$method}($field, '<', $condition->value()),
            FilterOperator::LessThanOrEqual => $query->{$method}($field, '<=', $condition->value()),
            FilterOperator::Like => $query->{$method}($field, 'like', (string) $condition->value()),
            FilterOperator::NotLike => $query->{$method}($field, 'not like', (string) $condition->value()),
            FilterOperator::StartsWith => $query->{$method}($field, 'like', $condition->value().'%'),
            FilterOperator::EndsWith => $query->{$method}($field, 'like', '%'.$condition->value()),
            FilterOperator::In => $or ? $query->orWhereIn($field, (array) $condition->value()) : $query->whereIn($field, (array) $condition->value()),
            FilterOperator::NotIn => $or ? $query->orWhereNotIn($field, (array) $condition->value()) : $query->whereNotIn($field, (array) $condition->value()),
            FilterOperator::Between => $or ? $query->orWhereBetween($field, (array) $condition->value()) : $query->whereBetween($field, (array) $condition->value()),
            FilterOperator::NotBetween => $or ? $query->orWhereNotBetween($field, (array) $condition->value()) : $query->whereNotBetween($field, (array) $condition->value()),
            FilterOperator::IsNull => $or ? $query->orWhereNull($field) : $query->whereNull($field),
            FilterOperator::IsNotNull => $or ? $query->orWhereNotNull($field) : $query->whereNotNull($field),
            FilterOperator::DateEquals => $or ? $query->orWhereDate($field, '=', (string) $condition->value()) : $query->whereDate($field, '=', (string) $condition->value()),
            FilterOperator::DateBefore => $or ? $query->orWhereDate($field, '<', (string) $condition->value()) : $query->whereDate($field, '<', (string) $condition->value()),
            FilterOperator::DateAfter => $or ? $query->orWhereDate($field, '>', (string) $condition->value()) : $query->whereDate($field, '>', (string) $condition->value()),
            FilterOperator::ThisWeek => $this->applyThisWeek($query, $field, $or),
            FilterOperator::ThisMonth => $this->applyThisMonth($query, $field, $or),
            FilterOperator::ThisYear => $this->applyThisYear($query, $field, $or),
            FilterOperator::LastNDays => $this->applyLastNDays($query, $field, (int) $condition->value(), $or),
        };
    }

    private function applyThisWeek(Builder $query, string $field, bool $or): void
    {
        $start = Carbon::now()->startOfWeek()->toDateString();
        $end = Carbon::now()->endOfWeek()->toDateString();
        $or ? $query->orWhereBetween($field, [$start, $end]) : $query->whereBetween($field, [$start, $end]);
    }

    private function applyThisMonth(Builder $query, string $field, bool $or): void
    {
        $start = Carbon::now()->startOfMonth()->toDateString();
        $end = Carbon::now()->endOfMonth()->toDateString();
        $or ? $query->orWhereBetween($field, [$start, $end]) : $query->whereBetween($field, [$start, $end]);
    }

    private function applyThisYear(Builder $query, string $field, bool $or): void
    {
        $start = Carbon::now()->startOfYear()->toDateString();
        $end = Carbon::now()->endOfYear()->toDateString();
        $or ? $query->orWhereBetween($field, [$start, $end]) : $query->whereBetween($field, [$start, $end]);
    }

    private function applyLastNDays(Builder $query, string $field, int $days, bool $or): void
    {
        $from = Carbon::now()->subDays($days - 1)->startOfDay()->toDateTimeString();
        $to = Carbon::now()->endOfDay()->toDateTimeString();
        $or ? $query->orWhereBetween($field, [$from, $to]) : $query->whereBetween($field, [$from, $to]);
    }
}
