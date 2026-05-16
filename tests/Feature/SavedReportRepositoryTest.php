<?php

declare(strict_types=1);

namespace Ihasan\ReportBuilder\Tests\Feature;

use Ihasan\ReportBuilder\DTOs\ReportDefinition;
use Ihasan\ReportBuilder\DTOs\SelectedColumn;
use Ihasan\ReportBuilder\Models\SavedReport;
use Ihasan\ReportBuilder\Persistence\SavedReportRepository;
use Ihasan\ReportBuilder\Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use InvalidArgumentException;

class SavedReportRepositoryTest extends TestCase
{
    use RefreshDatabase;

    public function test_saved_reports_table_has_expected_columns(): void
    {
        $this->assertTrue(Schema::hasTable('report_builder_saved_reports'));

        $this->assertTrue(Schema::hasColumns('report_builder_saved_reports', [
            'id',
            'name',
            'public_id',
            'source_key',
            'definition',
            'created_by',
            'visibility',
            'created_at',
            'updated_at',
        ]));
    }

    public function test_it_saves_a_report_definition(): void
    {
        $definition = new ReportDefinition(
            sourceKey: 'orders',
            selectedColumns: [new SelectedColumn('order_number')],
        );

        $saved = $this->repository()->saveDefinition('Orders Report', $definition, createdBy: 9, visibility: 'private');

        $this->assertSame('Orders Report', $saved->name);
        $this->assertSame('orders', $saved->source_key);
        $this->assertSame(9, $saved->created_by);
        $this->assertSame('private', $saved->visibility);
        $this->assertNotEmpty($saved->public_id);
        $this->assertSame($definition->toArray(), $saved->definition);
    }

    public function test_it_loads_a_report_definition_from_saved_report(): void
    {
        $definition = new ReportDefinition(
            sourceKey: 'orders',
            selectedColumns: [new SelectedColumn('order_number')],
        );

        $saved = $this->repository()->saveDefinition('Orders Report', $definition);

        $loaded = $this->repository()->loadDefinition($saved);

        $this->assertSame($definition->toArray(), $loaded->toArray());
    }

    public function test_it_updates_saved_report_definition_json(): void
    {
        $saved = $this->repository()->saveDefinition(
            'Orders Report',
            new ReportDefinition(sourceKey: 'orders', selectedColumns: [new SelectedColumn('order_number')]),
        );

        $updatedDefinition = new ReportDefinition(
            sourceKey: 'orders',
            selectedColumns: [new SelectedColumn('order_number'), new SelectedColumn('status')],
        );

        $updated = $this->repository()->updateDefinition($saved, $updatedDefinition);

        $this->assertSame($updatedDefinition->toArray(), $updated->definition);
    }

    public function test_it_throws_for_malformed_definition_json(): void
    {
        $saved = SavedReport::query()->create([
            'name' => 'Bad Report',
            'public_id' => 'a1aa5e5b-6af8-4e72-a87f-6b5dcf8961f2',
            'source_key' => 'orders',
            'definition' => ['source_key' => 'orders'],
            'visibility' => 'private',
        ]);

        $saved->forceFill(['definition' => '{bad-json'])->save();
        $saved->refresh();

        $this->expectException(InvalidArgumentException::class);

        $this->repository()->loadDefinition($saved);
    }

    private function repository(): SavedReportRepository
    {
        return new SavedReportRepository;
    }
}
