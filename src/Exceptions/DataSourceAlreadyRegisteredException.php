<?php

namespace Ihasan\ReportBuilder\Exceptions;

class DataSourceAlreadyRegisteredException extends ReportBuilderException
{
    public static function forKey(string $key): self
    {
        return new self(sprintf('A report data source with key [%s] has already been registered.', $key));
    }
}
