<?php

declare(strict_types=1);

namespace Ihasan\ReportBuilder\Tests\Fixtures;

use Illuminate\Database\Eloquent\Model;

class RelationCustomerModel extends Model
{
    protected $table = 'report_builder_relation_customers';

    protected $guarded = [];

    public $timestamps = false;
}
