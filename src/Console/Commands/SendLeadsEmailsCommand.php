<?php

namespace Techquity\AeroProductLeads\Console\Commands;

use Aero\Cart\Models\Order;
use Illuminate\Console\Command;

class SendLeadsEmailsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'leads:emails {--order=}';
    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send Lead email for orders with associated leads order items';

    public function handle()
    {
        $order = Order::find($this->option('order'));

        if ($order) {
            //JOB::dispatch($order)
        }
    }
}
