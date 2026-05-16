<?php

declare(strict_types=1);

namespace Ihasan\ReportBuilder\Query;

use Ihasan\ReportBuilder\DTOs\ReportDefinition;
use Ihasan\ReportBuilder\Enums\AggregateFunction;
use Ihasan\ReportBuilder\Exceptions\InvalidReportDefinitionException;
use Ihasan\ReportBuilder\ReportSources\Fields\RelationAggregateField;
use Ihasan\ReportBuilder\ReportSources\Fields\RelationField;
use Ihasan\ReportBuilder\Support\SourceRegistry;
use Ihasan\ReportBuilder\Validation\DefinitionValidator;
use Illuminate\Database\Eloquent\Builder;

class ReportQueryCompiler
{
    public function __construct(
        protected SourceRegistry $sourceRegistry,
        protected DefinitionValidator $validator,
        protected FilterCompiler $filterCompiler,
    ) {}

    public function compile(ReportDefinition $definition): Builder
    {
        $this->validator->assertValid($definition);

        $source = $this->sourceRegistry->source($definition->sourceKey());
        $query = $source->query();
        $model = $query->getModel();

        $selects = [];
        $relationsToLoad = [];
        $aggregateFields = [];

        foreach ($definition->selectedColumns() as $selectedColumn) {
            $fieldKey = $selectedColumn->fieldKey();
            $field = $source->field($fieldKey);

            if ($field instanceof RelationAggregateField) {
                $aggregateFields[$fieldKey] = $field;

                continue;
            }

            if ($field instanceof RelationField) {
                $relationsToLoad[] = $field->relation();
                $selects[] = $field->foreignKey();

                continue;
            }

            $selects[] = $fieldKey;
        }

        $selects[] = $model->getKeyName();
        $query->select(array_values(array_unique($selects)));

        foreach ($aggregateFields as $aggregateKey => $aggregateField) {
            $this->applyAggregateField($query, $aggregateField, $aggregateKey);
        }

        if ($relationsToLoad !== []) {
            $query->with(array_values(array_unique($relationsToLoad)));
        }

        if ($definition->filters() !== null) {
            $this->filterCompiler->apply($query, $definition->filters());
        }

        foreach ($definition->sortDefinitions() as $sortDefinition) {
            $fieldKey = $sortDefinition->fieldKey();
            $field = $source->field($fieldKey);

            if ($field instanceof RelationField) {
                throw new InvalidReportDefinitionException([
                    ['path' => 'sorts', 'message' => sprintf('Sorting by relation fields is not supported: [%s].', $fieldKey)],
                ]);
            }

            $query->orderBy($fieldKey, $sortDefinition->direction());
        }

        return $query;
    }

    private function applyAggregateField(Builder $query, RelationAggregateField $field, string $alias): void
    {
        $relation = $field->relation();

        match ($field->aggregateFunction()) {
            AggregateFunction::Count => $query->withCount([$relation.' as '.$alias]),
            AggregateFunction::Sum => $query->withSum([$relation.' as '.$alias], (string) $field->attribute()),
            AggregateFunction::Avg => $query->withAvg([$relation.' as '.$alias], (string) $field->attribute()),
            AggregateFunction::Min => $query->withMin([$relation.' as '.$alias], (string) $field->attribute()),
            AggregateFunction::Max => $query->withMax([$relation.' as '.$alias], (string) $field->attribute()),
        };
    }
}
