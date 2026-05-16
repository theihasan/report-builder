<?php

declare(strict_types=1);

namespace Ihasan\ReportBuilder\Tests\Fixtures;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class RelationCustomerModel extends Model
{
    protected $table = 'report_builder_relation_customers';

    protected $guarded = [];

    public $timestamps = false;

    public function orders(): HasMany
    {
        return $this->hasMany(RelationOrderModel::class, 'customer_id');
    }
}
