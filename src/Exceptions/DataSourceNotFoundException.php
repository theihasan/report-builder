<?php

namespace Ihasan\ReportBuilder\Exceptions;

class DataSourceNotFoundException extends ReportBuilderException
{
    public static function forKey(string $key): self
    {
        return new self(sprintf('No report data source is registered for key [%s].', $key));
    }
}
