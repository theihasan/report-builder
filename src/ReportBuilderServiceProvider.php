<?php

namespace Ihasan\ReportBuilder;

use Ihasan\ReportBuilder\Commands\ReportBuilderCommand;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class ReportBuilderServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        /*
         * This class is a Package Service Provider
         *
         * More info: https://github.com/spatie/laravel-package-tools
         */
        $package
            ->name('report-builder')
            ->hasConfigFile()
            ->hasViews()
            ->hasMigration('create_report_builder_table')
            ->hasCommand(ReportBuilderCommand::class);
    }
}
