<?php

declare(strict_types=1);

namespace Ihasan\ReportBuilder\Tests\Feature;

use Ihasan\ReportBuilder\DTOs\OutputDefinition;
use Ihasan\ReportBuilder\DTOs\ReportDefinition;
use Ihasan\ReportBuilder\DTOs\SelectedColumn;
use Ihasan\ReportBuilder\Execution\ReportRunner;
use Ihasan\ReportBuilder\ReportSources\Fields\NumberField;
use Ihasan\ReportBuilder\ReportSources\Fields\TextField;
use Ihasan\ReportBuilder\ReportSources\ReportSource;
use Ihasan\ReportBuilder\Support\SourceRegistry;
use Ihasan\ReportBuilder\Tests\Fixtures\TestModel;
use Ihasan\ReportBuilder\Tests\TestCase;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Schema;

class CsvExportTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Schema::dropIfExists('report_builder_test_models');
        Schema::create('report_builder_test_models', function ($table): void {
            $table->id();
            $table->string('name');
            $table->string('status')->nullable();
            $table->integer('amount');
        });

        TestModel::query()->insert([
            ['name' => 'Alpha', 'status' => 'paid', 'amount' => 120],
            ['name' => 'Beta', 'status' => 'pending', 'amount' => 50],
        ]);

        $this->app->make(SourceRegistry::class)->register(new CsvExportTestSource);
    }

    public function test_it_exports_headers_using_column_order_and_labels(): void
    {
        $export = $this->runner()->export(new ReportDefinition(
            sourceKey: 'orders',
            selectedColumns: [
                new SelectedColumn('status', 'State'),
                new SelectedColumn('name', 'Customer Name'),
                new SelectedColumn('amount'),
            ],
            outputDefinition: new OutputDefinition('csv'),
        ));

        $rows = $this->parseCsv($export['content']);

        $this->assertSame(['State', 'Customer Name', 'amount'], $rows[0]);
    }

    public function test_it_preserves_csv_row_column_order_for_data_rows(): void
    {
        $export = $this->runner()->export(new ReportDefinition(
            sourceKey: 'orders',
            selectedColumns: [
                new SelectedColumn('amount'),
                new SelectedColumn('name'),
            ],
            outputDefinition: new OutputDefinition('csv'),
        ));

        $rows = $this->parseCsv($export['content']);

        $this->assertSame(['120', 'Alpha'], $rows[1]);
        $this->assertSame(['50', 'Beta'], $rows[2]);
    }

    public function test_it_supports_empty_result_set_with_header_row_only(): void
    {
        TestModel::query()->delete();

        $export = $this->runner()->export(new ReportDefinition(
            sourceKey: 'orders',
            selectedColumns: [new SelectedColumn('name')],
            outputDefinition: new OutputDefinition('csv'),
        ));

        $rows = $this->parseCsv($export['content']);

        $this->assertCount(1, $rows);
        $this->assertSame(['name'], $rows[0]);
    }

    public function test_it_returns_successful_export_payload(): void
    {
        $export = $this->runner()->export(new ReportDefinition(
            sourceKey: 'orders',
            selectedColumns: [new SelectedColumn('name')],
            outputDefinition: new OutputDefinition('csv', 'orders-export.csv'),
        ));

        $this->assertSame('orders-export.csv', $export['filename']);
        $this->assertSame('text/csv; charset=UTF-8', $export['mime_type']);
        $this->assertStringContainsString("name\n", $export['content']);
    }

    private function runner(): ReportRunner
    {
        return $this->app->make(ReportRunner::class);
    }

    /**
     * @return array<int, array<int, string>>
     */
    private function parseCsv(string $content): array
    {
        $handle = fopen('php://temp', 'w+');
        fwrite($handle, $content);
        rewind($handle);

        $rows = [];

        while (($row = fgetcsv($handle)) !== false) {
            $rows[] = $row;
        }

        fclose($handle);

        return $rows;
    }
}

class CsvExportTestSource extends ReportSource
{
    public function __construct()
    {
        parent::__construct('orders', 'Orders');
    }

    public function query(): Builder
    {
        return TestModel::query();
    }

    public function fields(): array
    {
        return [
            TextField::make('name')->selectable()->sortable()->filterable(),
            TextField::make('status')->selectable()->sortable()->filterable(),
            NumberField::make('amount')->selectable()->sortable()->filterable(),
        ];
    }
}
