<?php

declare(strict_types=1);

namespace Ihasan\ReportBuilder\Tests\Unit;

use Ihasan\ReportBuilder\DTOs\FilterCondition;
use Ihasan\ReportBuilder\DTOs\FilterGroup;
use Ihasan\ReportBuilder\DTOs\ReportDefinition;
use Ihasan\ReportBuilder\DTOs\SelectedColumn;
use Ihasan\ReportBuilder\DTOs\SortDefinition;
use Ihasan\ReportBuilder\Enums\FilterOperator;
use Ihasan\ReportBuilder\Exceptions\InvalidReportDefinitionException;
use Ihasan\ReportBuilder\ReportSources\Fields\DateField;
use Ihasan\ReportBuilder\ReportSources\Fields\TextField;
use Ihasan\ReportBuilder\ReportSources\ReportSource;
use Ihasan\ReportBuilder\Support\SourceRegistry;
use Ihasan\ReportBuilder\Tests\Fixtures\TestModel;
use Ihasan\ReportBuilder\Tests\TestCase;
use Ihasan\ReportBuilder\Validation\DefinitionValidator;
use Illuminate\Database\Eloquent\Builder;

class DefinitionValidatorTest extends TestCase
{
    public function test_valid_definition_passes_validation(): void
    {
        $validator = $this->validator();

        $definition = new ReportDefinition(
            sourceKey: 'orders',
            selectedColumns: [new SelectedColumn('order_number')],
            sortDefinitions: [new SortDefinition('created_at', 'desc')],
            filters: new FilterGroup('and', [
                new FilterCondition('status', FilterOperator::Equals, 'paid'),
                new FilterCondition('created_at', FilterOperator::LastNDays, 7),
            ]),
        );

        $this->assertSame([], $validator->validate($definition));
    }

    public function test_unknown_source_key_returns_error(): void
    {
        $validator = $this->validator();

        $errors = $validator->validate(new ReportDefinition(sourceKey: 'missing', selectedColumns: [new SelectedColumn('order_number')]));

        $this->assertSame('source_key', $errors[0]['path']);
    }

    public function test_unknown_selected_field_returns_error(): void
    {
        $validator = $this->validator();

        $definition = new ReportDefinition(sourceKey: 'orders', selectedColumns: [new SelectedColumn('unknown')]);

        $errors = $validator->validate($definition);

        $this->assertSame('selected_columns.0.field_key', $errors[0]['path']);
    }

    public function test_non_sortable_sort_field_returns_error(): void
    {
        $validator = $this->validator();

        $definition = new ReportDefinition(
            sourceKey: 'orders',
            selectedColumns: [new SelectedColumn('order_number')],
            sortDefinitions: [new SortDefinition('status', 'asc')],
        );

        $errors = $validator->validate($definition);

        $this->assertSame('sorts.0.field_key', $errors[0]['path']);
    }

    public function test_non_filterable_filter_field_returns_error(): void
    {
        $validator = $this->validator();

        $definition = new ReportDefinition(
            sourceKey: 'orders',
            selectedColumns: [new SelectedColumn('order_number')],
            filters: new FilterGroup('and', [
                new FilterCondition('order_number', FilterOperator::Equals, 'A-1'),
            ]),
        );

        $errors = $validator->validate($definition);

        $this->assertSame('filters.children.0.field_key', $errors[0]['path']);
    }

    public function test_malformed_operator_values_return_errors(): void
    {
        $validator = $this->validator();

        $definition = new ReportDefinition(
            sourceKey: 'orders',
            selectedColumns: [new SelectedColumn('order_number')],
            filters: new FilterGroup('and', [
                new FilterCondition('status', FilterOperator::Between, ['only-one']),
                new FilterCondition('status', FilterOperator::In, 'paid'),
                new FilterCondition('created_at', FilterOperator::LastNDays, 0),
            ]),
        );

        $errors = $validator->validate($definition);

        $this->assertCount(3, $errors);
    }

    public function test_recursive_nested_filter_validation(): void
    {
        $validator = $this->validator();

        $definition = new ReportDefinition(
            sourceKey: 'orders',
            selectedColumns: [new SelectedColumn('order_number')],
            filters: new FilterGroup('and', [
                new FilterGroup('or', [
                    new FilterCondition('status', FilterOperator::Equals, null),
                    new FilterCondition('missing_filter', FilterOperator::Equals, 'x'),
                ]),
            ]),
        );

        $this->expectException(InvalidReportDefinitionException::class);

        $validator->assertValid($definition);
    }

    private function validator(): DefinitionValidator
    {
        $registry = new SourceRegistry;
        $registry->register(new OrdersReportSource);

        return new DefinitionValidator($registry);
    }
}

class OrdersReportSource extends ReportSource
{
    public function __construct()
    {
        parent::__construct('orders', 'Orders');
    }

    public function fields(): array
    {
        return [
            TextField::make('order_number')->selectable()->filterable(false)->sortable(false),
            TextField::make('status')->selectable()->filterable()->sortable(false),
            DateField::make('created_at')->selectable()->filterable()->sortable(),
        ];
    }

    public function query(): Builder
    {
        return TestModel::query();
    }
}
