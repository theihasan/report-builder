<?php

declare(strict_types=1);

namespace Ihasan\ReportBuilder\Enums;

enum FilterOperator: string
{
    case Equals = '=';
    case NotEquals = '!=';
    case GreaterThan = '>';
    case GreaterThanOrEqual = '>=';
    case LessThan = '<';
    case LessThanOrEqual = '<=';
    case Like = 'like';
    case NotLike = 'not_like';
    case StartsWith = 'starts_with';
    case EndsWith = 'ends_with';
    case In = 'in';
    case NotIn = 'not_in';
    case Between = 'between';
    case NotBetween = 'not_between';
    case IsNull = 'is_null';
    case IsNotNull = 'is_not_null';
    case DateEquals = 'date_equals';
    case DateBefore = 'date_before';
    case DateAfter = 'date_after';
    case ThisWeek = 'this_week';
    case ThisMonth = 'this_month';
    case ThisYear = 'this_year';
    case LastNDays = 'last_n_days';
}
