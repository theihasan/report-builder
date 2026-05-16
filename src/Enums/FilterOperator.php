<?php

namespace Ihasan\ReportBuilder\Enums;

enum FilterOperator: string
{
    case Eq = 'eq';
    case Neq = 'neq';
    case Gt = 'gt';
    case Gte = 'gte';
    case Lt = 'lt';
    case Lte = 'lte';
    case Between = 'between';
    case In = 'in';
    case NotIn = 'not_in';
    case Like = 'like';
    case IsNull = 'is_null';
    case NotNull = 'not_null';
}
