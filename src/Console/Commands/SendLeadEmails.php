<?php

namespace Techquity\AeroProductLeads\Console\Commands;

use Illuminate\Console\Command;
use Techquity\AeroProductLeads\Jobs\SendLeadEmailsJob;

class SendLeadEmails extends Command
{
    protected $signature = 'product-leads:send-emails';
    protected $description = 'Send lead emails to stores based on their schedule.';

    public function handle()
    {
        $this->info('Dispatching the lead emails job...');
        dispatch(new SendLeadEmailsJob());
        $this->info('Lead emails job dispatched successfully.');
    }
}
