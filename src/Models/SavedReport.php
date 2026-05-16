<?php

declare(strict_types=1);

namespace Ihasan\ReportBuilder\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SavedReport extends Model
{
    protected $table = 'report_builder_saved_reports';

    protected $fillable = ['name', 'public_id', 'source_key', 'definition', 'created_by', 'visibility'];

    protected $casts = [
        'definition' => 'array',
        'created_by' => 'integer',
    ];

    public function schedules(): HasMany
    {
        return $this->hasMany(ReportSchedule::class, 'saved_report_id');
    }
}
