<?php

return [
    'route_prefix' => 'report-builder',
    'web_middleware' => ['web', 'auth'],
    'api_middleware' => ['api'],
    'default_per_page' => 25,
    'max_per_page' => 100,
    'preview_limit' => 100,
    'max_chart_points' => 500,
    'default_cache_ttl_seconds' => 300,
    'max_export_rows_sync' => 5000,
    'exports_disk' => 'local',
    'exports_queue' => 'report-exports',
    'enable_dashboards' => true,
    'enable_excel' => false,
    'enable_pdf' => false,
    'report_sources' => [],
];
