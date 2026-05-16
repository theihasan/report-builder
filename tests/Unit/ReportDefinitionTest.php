<?php

declare(strict_types=1);

namespace Ihasan\ReportBuilder\Tests\Unit;

use Ihasan\ReportBuilder\DTOs\OutputDefinition;
use Ihasan\ReportBuilder\DTOs\ReportDefinition;
use Ihasan\ReportBuilder\DTOs\SelectedColumn;
use Ihasan\ReportBuilder\DTOs\SortDefinition;
use Ihasan\ReportBuilder\Tests\TestCase;
use InvalidArgumentException;

class ReportDefinitionTest extends TestCase
{
    public function test_it_creates_a_definition(): void
    {
        $definition = new ReportDefinition(sourceKey: 'orders');

        $this->assertSame('orders', $definition->sourceKey());
        $this->assertSame(1, $definition->version());
        $this->assertSame('json', $definition->outputDefinition()->format());
    }

    public function test_it_adds_selected_columns(): void
    {
        $definition = new ReportDefinition(sourceKey: 'orders');

        $definition->addSelectedColumn(new SelectedColumn('order_number', 'Order #', 1, true));

        $this->assertCount(1, $definition->selectedColumns());
        $this->assertSame('order_number', $definition->selectedColumns()[0]->fieldKey());
    }

    public function test_it_adds_sort_definitions(): void
    {
        $definition = new ReportDefinition(sourceKey: 'orders');

        $definition->addSortDefinition(new SortDefinition('created_at', 'desc'));

        $this->assertCount(1, $definition->sortDefinitions());
        $this->assertSame('desc', $definition->sortDefinitions()[0]->direction());
    }

    public function test_output_definition_serialization(): void
    {
        $output = new OutputDefinition(format: 'csv', filename: 'orders-report');

        $this->assertSame([
            'format' => 'csv',
            'filename' => 'orders-report',
        ], $output->toArray());

        $hydrated = OutputDefinition::fromArray($output->toArray());
        $this->assertSame('csv', $hydrated->format());
        $this->assertSame('orders-report', $hydrated->filename());
    }

    public function test_full_json_round_trip(): void
    {
        $definition = new ReportDefinition(
            sourceKey: 'orders',
            selectedColumns: [new SelectedColumn('order_number', 'Order #'), new SelectedColumn('total')],
            sortDefinitions: [new SortDefinition('created_at', 'desc')],
            outputDefinition: new OutputDefinition('xlsx', 'orders-export'),
            version: 2,
        );

        $json = $definition->toJson();
        $hydrated = ReportDefinition::fromJson($json);

        $this->assertSame($definition->toArray(), $hydrated->toArray());
    }

    public function test_it_throws_for_invalid_sort_direction(): void
    {
        $this->expectException(InvalidArgumentException::class);

        new SortDefinition('created_at', 'invalid');
    }
}
