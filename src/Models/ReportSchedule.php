<?php

declare(strict_types=1);

namespace Ihasan\ReportBuilder\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ReportSchedule extends Model
{
    protected $table = 'report_builder_report_schedules';

    protected $fillable = [
        'saved_report_id',
        'enabled',
        'frequency_type',
        'cron_expression',
        'timezone',
        'format',
        'recipients',
        'last_run_at',
        'next_run_at',
    ];

    protected $casts = [
        'enabled' => 'boolean',
        'recipients' => 'array',
        'last_run_at' => 'datetime',
        'next_run_at' => 'datetime',
    ];

    public function savedReport(): BelongsTo
    {
        return $this->belongsTo(SavedReport::class, 'saved_report_id');
    }

    public function scopeEnabled(Builder $query): Builder
    {
        return $query->where('enabled', true);
    }
}
