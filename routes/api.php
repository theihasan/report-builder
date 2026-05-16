<?php

use Ihasan\ReportBuilder\Http\Controllers\Api\DataSourceController;
use Illuminate\Support\Facades\Route;

Route::middleware(config('report-builder.api_middleware', ['api']))
    ->prefix(config('report-builder.route_prefix', 'report-builder'))
    ->name('report-builder.')
    ->group(function (): void {
        Route::get('sources', [DataSourceController::class, 'index'])->name('sources.index');
        Route::get('sources/{source}', [DataSourceController::class, 'show'])->name('sources.show');
    });
