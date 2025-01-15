<?php

namespace Techquity\AeroProductLeads\Console\Commands;

use Illuminate\Console\Command;
use Techquity\AeroProductLeads\Jobs\UpdateLeadCoordinatesJob;

class UpdateLeadCoordinates extends Command
{
    protected $signature = 'product-leads:update-lead';
    protected $description = 'Update lead coordinates';

    public function handle()
    {
        dispatch(new UpdateLeadCoordinatesJob())->onQueue(setting('product-leads.queue'));
    }
}
