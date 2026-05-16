<?php

declare(strict_types=1);

namespace Ihasan\ReportBuilder\ReportSources\Fields;

class NumberField extends Field
{
    public function type(): string
    {
        return 'number';
    }
}
