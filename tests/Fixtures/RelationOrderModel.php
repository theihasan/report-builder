<?php

declare(strict_types=1);

namespace Ihasan\ReportBuilder\Tests\Fixtures;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RelationOrderModel extends Model
{
    protected $table = 'report_builder_relation_orders';

    protected $guarded = [];

    public $timestamps = false;

    public function customer(): BelongsTo
    {
        return $this->belongsTo(RelationCustomerModel::class, 'customer_id');
    }
}
