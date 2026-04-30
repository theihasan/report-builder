<?php

namespace Ihasan\ReportBuilder\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @see \Ihasan\ReportBuilder\ReportBuilder
 */
class ReportBuilder extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return \Ihasan\ReportBuilder\ReportBuilder::class;
    }
}
