<?php

declare(strict_types=1);

namespace Ihasan\ReportBuilder\Tests\Unit;

use Ihasan\ReportBuilder\ReportSources\Fields\BooleanField;
use Ihasan\ReportBuilder\ReportSources\Fields\DateField;
use Ihasan\ReportBuilder\ReportSources\Fields\MoneyField;
use Ihasan\ReportBuilder\ReportSources\Fields\NumberField;
use Ihasan\ReportBuilder\ReportSources\Fields\TextField;
use Ihasan\ReportBuilder\ReportSources\ReportSource;
use Ihasan\ReportBuilder\Tests\TestCase;

class ReportSourceFieldsTest extends TestCase
{
    public function test_it_creates_each_field_type(): void
    {
        $this->assertSame('text', TextField::make('name')->type());
        $this->assertSame('number', NumberField::make('total')->type());
        $this->assertSame('date', DateField::make('created_at')->type());
        $this->assertSame('boolean', BooleanField::make('is_active')->type());
        $this->assertSame('money', MoneyField::make('amount')->type());
    }

    public function test_it_supports_fluent_field_options(): void
    {
        $field = TextField::make('name')
            ->label('Customer Name')
            ->filterable()
            ->sortable()
            ->selectable(false);

        $this->assertSame('Customer Name', $field->label());
        $this->assertTrue($field->isFilterable());
        $this->assertTrue($field->isSortable());
        $this->assertFalse($field->isSelectable());
    }

    public function test_it_exposes_field_key_label_and_type(): void
    {
        $field = NumberField::make('order_total');

        $this->assertSame('order_total', $field->key());
        $this->assertSame('Order Total', $field->label());
        $this->assertSame('number', $field->type());
    }

    public function test_money_field_supports_currency(): void
    {
        $field = MoneyField::make('total')->currency('BDT');

        $this->assertSame('BDT', $field->currencyCode());
    }

    public function test_report_source_exposes_fields_and_can_find_by_key(): void
    {
        $source = new TestFieldReportSource('orders', 'Orders');

        $this->assertCount(2, $source->fields());
        $this->assertTrue($source->hasField('name'));
        $this->assertFalse($source->hasField('missing'));
        $this->assertSame('name', $source->field('name')?->key());
        $this->assertNull($source->field('missing'));
    }
}

class TestFieldReportSource extends ReportSource
{
    public function fields(): array
    {
        return [
            TextField::make('name')->label('Customer Name')->filterable()->sortable(),
            MoneyField::make('total')->label('Total')->currency('BDT')->sortable(),
        ];
    }
}
