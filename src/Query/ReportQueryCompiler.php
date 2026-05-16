<?php

declare(strict_types=1);

namespace Ihasan\ReportBuilder\Query;

use Ihasan\ReportBuilder\DTOs\ReportDefinition;
use Ihasan\ReportBuilder\Exceptions\InvalidReportDefinitionException;
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

        foreach ($definition->selectedColumns() as $selectedColumn) {
            $fieldKey = $selectedColumn->fieldKey();
            $field = $source->field($fieldKey);

            if ($field instanceof RelationField) {
                $relationsToLoad[] = $field->relation();
                $selects[] = $field->foreignKey();

                continue;
            }

            if (str_contains($fieldKey, '.')) {
                throw new InvalidReportDefinitionException([
                    ['path' => 'selected_columns', 'message' => sprintf('Relation and aggregate fields are not supported yet: [%s].', $fieldKey)],
                ]);
            }

            $selects[] = $fieldKey;
        }

        $selects[] = $model->getKeyName();

        $query->select(array_values(array_unique($selects)));

        if ($relationsToLoad !== []) {
            $query->with(array_values(array_unique($relationsToLoad)));
        }

        if ($definition->filters() !== null) {
            $this->filterCompiler->apply($query, $definition->filters());
        }

        foreach ($definition->sortDefinitions() as $sortDefinition) {
            $fieldKey = $sortDefinition->fieldKey();

            if (str_contains($fieldKey, '.')) {
                throw new InvalidReportDefinitionException([
                    ['path' => 'sorts', 'message' => sprintf('Relation and aggregate fields are not supported yet: [%s].', $fieldKey)],
                ]);
            }

            $query->orderBy($fieldKey, $sortDefinition->direction());
        }

        return $query;
    }
}
