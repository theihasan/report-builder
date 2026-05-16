<?php

declare(strict_types=1);

namespace Ihasan\ReportBuilder\Models;

use Illuminate\Database\Eloquent\Model;

class SavedReport extends Model
{
    protected $table = 'report_builder_saved_reports';

    protected $fillable = ['name', 'public_id', 'source_key', 'definition', 'created_by', 'visibility'];

    protected $casts = [
        'definition' => 'array',
        'created_by' => 'integer',
    ];
}
