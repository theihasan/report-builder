<?php

namespace Ihasan\ReportBuilder\Tests\Fixtures;

use Illuminate\Database\Eloquent\Model;

class TestModel extends Model
{
    protected $table = 'report_builder_test_models';

    protected $guarded = [];

    public $timestamps = false;
}
