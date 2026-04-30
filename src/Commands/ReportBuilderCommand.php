<?php

namespace Ihasan\ReportBuilder\Commands;

use Illuminate\Console\Command;

class ReportBuilderCommand extends Command
{
    public $signature = 'report-builder';

    public $description = 'My command';

    public function handle(): int
    {
        $this->comment('All done');

        return self::SUCCESS;
    }
}
