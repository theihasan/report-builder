<?php

declare(strict_types=1);

namespace Ihasan\ReportBuilder;

use Ihasan\ReportBuilder\Commands\ReportBuilderCommand;
use Ihasan\ReportBuilder\Contracts\ReportSourceContract;
use Ihasan\ReportBuilder\Execution\CsvExporter;
use Ihasan\ReportBuilder\Execution\ExportManager;
use Ihasan\ReportBuilder\Execution\ReportQueryCompilerAdapter;
use Ihasan\ReportBuilder\Query\FilterCompiler;
use Ihasan\ReportBuilder\Query\ReportQueryCompiler;
use Ihasan\ReportBuilder\Support\DataSourceRegistry;
use Ihasan\ReportBuilder\Support\SourceRegistry;
use Ihasan\ReportBuilder\Validation\DefinitionValidator;
use Illuminate\Contracts\Foundation\Application;
use InvalidArgumentException;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class ReportBuilderServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        $package
            ->name('report-builder')
            ->hasConfigFile()
            ->hasRoute('api')
            ->hasViews()
            ->hasMigration('create_report_builder_table')
            ->hasCommand(ReportBuilderCommand::class);
    }

    public function packageRegistered(): void
    {
        $this->app->singleton(
            DataSourceRegistry::class,
            static fn (): DataSourceRegistry => new DataSourceRegistry,
        );

        $this->app->singleton(
            SourceRegistry::class,
            function (Application $app): SourceRegistry {
                $registry = new SourceRegistry;

                foreach ((array) config('report-builder.report_sources', []) as $sourceClass) {
                    if (! is_string($sourceClass) || ! is_subclass_of($sourceClass, ReportSourceContract::class)) {
                        throw new InvalidArgumentException('Each configured report source must implement '.ReportSourceContract::class.'.');
                    }

                    $registry->register($app->make($sourceClass));
                }

                return $registry;
            },
        );

        $this->app->singleton(
            ReportBuilder::class,
            static fn (Application $app): ReportBuilder => new ReportBuilder(
                $app->make(DataSourceRegistry::class),
            ),
        );

        $this->app->singleton(
            DefinitionValidator::class,
            static fn (Application $app): DefinitionValidator => new DefinitionValidator(
                $app->make(SourceRegistry::class),
            ),
        );

        $this->app->singleton(FilterCompiler::class);
        $this->app->singleton(ReportQueryCompiler::class);
        $this->app->singleton(ReportQueryCompilerAdapter::class);
        $this->app->singleton(CsvExporter::class);
        $this->app->singleton(
            ExportManager::class,
            static fn (Application $app): ExportManager => new ExportManager([$app->make(CsvExporter::class)]),
        );
    }
}
