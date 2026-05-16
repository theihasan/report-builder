<?php

declare(strict_types=1);

namespace Ihasan\ReportBuilder\Tests\Feature;

use Ihasan\ReportBuilder\DTOs\OutputDefinition;
use Ihasan\ReportBuilder\DTOs\ReportDefinition;
use Ihasan\ReportBuilder\DTOs\SelectedColumn;
use Ihasan\ReportBuilder\Execution\ExportManager;
use Ihasan\ReportBuilder\Execution\ReportRunner;
use Ihasan\ReportBuilder\ReportSources\Fields\DateField;
use Ihasan\ReportBuilder\ReportSources\Fields\NumberField;
use Ihasan\ReportBuilder\ReportSources\Fields\TextField;
use Ihasan\ReportBuilder\ReportSources\ReportSource;
use Ihasan\ReportBuilder\Support\SourceRegistry;
use Ihasan\ReportBuilder\Tests\Fixtures\TestModel;
use Ihasan\ReportBuilder\Tests\TestCase;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Schema;
use ZipArchive;

class XlsxExportTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Schema::dropIfExists('report_builder_test_models');
        Schema::create('report_builder_test_models', function ($table): void {
            $table->id();
            $table->string('name');
            $table->date('order_date')->nullable();
            $table->integer('amount');
        });

        TestModel::query()->insert([
            ['name' => 'Alpha', 'order_date' => '2026-01-05', 'amount' => 120],
            ['name' => 'Beta', 'order_date' => '2026-02-10', 'amount' => 50],
        ]);

        $this->app->make(SourceRegistry::class)->register(new XlsxExportTestSource);
    }

    public function test_xlsx_export_can_be_generated(): void
    {
        $export = $this->runner()->export(new ReportDefinition(
            sourceKey: 'orders_xlsx',
            selectedColumns: [new SelectedColumn('name')],
            outputDefinition: new OutputDefinition('xlsx', 'orders-export.xlsx'),
        ));

        $this->assertSame('orders-export.xlsx', $export['filename']);
        $this->assertSame('application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', $export['mime_type']);
        $this->assertNotEmpty($export['content']);
    }

    public function test_xlsx_headings_and_row_order_are_exported_as_selected(): void
    {
        $export = $this->runner()->export(new ReportDefinition(
            sourceKey: 'orders_xlsx',
            selectedColumns: [
                new SelectedColumn('amount', 'Total Amount'),
                new SelectedColumn('name', 'Customer Name'),
            ],
            outputDefinition: new OutputDefinition('xlsx'),
        ));

        $rows = $this->worksheetRows($export['content']);

        $this->assertSame(['Total Amount', 'Customer Name'], $rows[0]);
        $this->assertSame(['120', 'Alpha'], $rows[1]);
        $this->assertSame(['50', 'Beta'], $rows[2]);
    }

    public function test_xlsx_export_preserves_numeric_and_date_compatible_values(): void
    {
        $export = $this->runner()->export(new ReportDefinition(
            sourceKey: 'orders_xlsx',
            selectedColumns: [
                new SelectedColumn('amount'),
                new SelectedColumn('order_date'),
            ],
            outputDefinition: new OutputDefinition('xlsx'),
        ));

        $types = $this->worksheetCellTypes($export['content']);

        $this->assertSame('n', $types['A2']);
        $this->assertContains($types['B2'], ['n', null]);
    }

    public function test_export_manager_resolves_xlsx_driver_through_format_registration(): void
    {
        $manager = $this->app->make(ExportManager::class);

        $export = $manager->export(new ReportDefinition(
            sourceKey: 'orders_xlsx',
            selectedColumns: [new SelectedColumn('name')],
            outputDefinition: new OutputDefinition('xlsx'),
        ));

        $this->assertSame('report.xlsx', $export['filename']);
    }

    private function runner(): ReportRunner
    {
        return $this->app->make(ReportRunner::class);
    }

    /**
     * @return array<int, array<int, string>>
     */
    private function worksheetRows(string $xlsxContent): array
    {
        $xml = $this->worksheetXml($xlsxContent);

        $rows = [];

        foreach ($xml->sheetData->row as $row) {
            $values = [];
            foreach ($row->c as $cell) {
                $values[] = isset($cell->is->t) ? (string) $cell->is->t : (string) $cell->v;
            }
            $rows[] = $values;
        }

        return $rows;
    }

    /**
     * @return array<string, ?string>
     */
    private function worksheetCellTypes(string $xlsxContent): array
    {
        $xml = $this->worksheetXml($xlsxContent);
        $types = [];

        foreach ($xml->sheetData->row as $row) {
            foreach ($row->c as $cell) {
                $reference = (string) $cell['r'];
                $type = isset($cell['t']) ? (string) $cell['t'] : null;
                $types[$reference] = $type;
            }
        }

        return $types;
    }

    private function worksheetXml(string $xlsxContent): \SimpleXMLElement
    {
        $path = tempnam(sys_get_temp_dir(), 'xlsx');
        file_put_contents($path, $xlsxContent);

        $zip = new ZipArchive;
        $this->assertTrue($zip->open($path) === true);

        $xmlString = $zip->getFromName('xl/worksheets/sheet1.xml');
        $zip->close();
        @unlink($path);

        $this->assertIsString($xmlString);

        $xml = simplexml_load_string($xmlString);
        $this->assertInstanceOf(\SimpleXMLElement::class, $xml);

        return $xml;
    }
}

class XlsxExportTestSource extends ReportSource
{
    public function __construct()
    {
        parent::__construct('orders_xlsx', 'Orders Xlsx');
    }

    public function query(): Builder
    {
        return TestModel::query();
    }

    public function fields(): array
    {
        return [
            TextField::make('name')->selectable()->sortable()->filterable(),
            DateField::make('order_date')->selectable()->sortable()->filterable(),
            NumberField::make('amount')->selectable()->sortable()->filterable(),
        ];
    }
}
