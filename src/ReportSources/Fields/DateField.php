<?php

declare(strict_types=1);

namespace Ihasan\ReportBuilder\ReportSources\Fields;

class DateField extends Field
{
    public function type(): string
    {
        return 'date';
    }
}
