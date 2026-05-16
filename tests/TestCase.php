<?php

namespace Ihasan\ReportBuilder\Tests;

use Ihasan\ReportBuilder\ReportBuilderServiceProvider;
use Illuminate\Database\Eloquent\Factories\Factory;
use Tests\TestCase as ApplicationTestCase;

abstract class TestCase extends ApplicationTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        config()->set("report-builder.api_middleware", ["api"]);
        config()->set("report-builder.route_prefix", "report-builder");

        Factory::guessFactoryNamesUsing(
            fn(
                string $modelName,
            ) => "Ihasan\\ReportBuilder\\Database\\Factories\\" .
                class_basename($modelName) .
                "Factory",
        );
    }
}
