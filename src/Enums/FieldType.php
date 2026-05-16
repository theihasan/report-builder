<?php

namespace Ihasan\ReportBuilder\Enums;

enum FieldType: string
{
    case String = 'string';
    case Integer = 'integer';
    case Decimal = 'decimal';
    case Boolean = 'boolean';
    case Date = 'date';
    case DateTime = 'datetime';
}
