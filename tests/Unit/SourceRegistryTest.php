<?php

declare(strict_types=1);

namespace Ihasan\ReportBuilder\Tests\Unit;

use Ihasan\ReportBuilder\Exceptions\ReportSourceNotFoundException;
use Ihasan\ReportBuilder\ReportSources\ReportSource;
use Ihasan\ReportBuilder\Support\SourceRegistry;
use Ihasan\ReportBuilder\Tests\TestCase;

class SourceRegistryTest extends TestCase
{
    public function test_it_registers_and_resolves_a_source(): void
    {
        $registry = new SourceRegistry;
        $source = new TestReportSource('orders', 'Orders');

        $registry->register($source);

        $resolved = $registry->source('orders');

        $this->assertSame($source, $resolved);
        $this->assertSame('orders', $resolved->key());
        $this->assertSame('Orders', $resolved->label());
    }

    public function test_it_throws_for_unknown_source_key(): void
    {
        $registry = new SourceRegistry;

        $this->expectException(ReportSourceNotFoundException::class);

        $registry->source('missing');
    }

    public function test_it_lists_registered_sources_and_keys(): void
    {
        $registry = new SourceRegistry;

        $orders = new TestReportSource('orders', 'Orders');
        $customers = new TestReportSource('customers', 'Customers');

        $registry->register($orders);
        $registry->register($customers);

        $this->assertSame(['orders', 'customers'], $registry->keys());
        $this->assertSame([$orders, $customers], $registry->all());
    }

    public function test_it_can_register_many_sources(): void
    {
        $registry = new SourceRegistry;

        $orders = new TestReportSource('orders', 'Orders');
        $customers = new TestReportSource('customers', 'Customers');

        $registry->registerMany([$orders, $customers]);

        $this->assertSame([$orders, $customers], $registry->all());
    }
}

class TestReportSource extends ReportSource {}
