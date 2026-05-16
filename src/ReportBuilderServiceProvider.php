<?php

namespace Ihasan\ReportBuilder;

use Illuminate\Contracts\Foundation\Application;
use Ihasan\ReportBuilder\Commands\ReportBuilderCommand;
use Ihasan\ReportBuilder\Support\DataSourceRegistry;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class ReportBuilderServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        $package
            ->name("report-builder")
            ->hasConfigFile()
            ->hasRoute("api")
            ->hasViews()
            ->hasMigration("create_report_builder_table")
            ->hasCommand(ReportBuilderCommand::class);
    }

    public function packageRegistered(): void
    {
        $this->app->singleton(
            DataSourceRegistry::class,
            static fn(): DataSourceRegistry => new DataSourceRegistry(),
        );

        $this->app->singleton(
            ReportBuilder::class,
            static fn(Application $app): ReportBuilder => new ReportBuilder(
                $app->make(DataSourceRegistry::class),
            ),
        );
    }
}
