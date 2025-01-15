<?php

namespace Techquity\AeroProductLeads\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;
use Techquity\AeroProductLeads\Mail\LeadEmail;
use Techquity\AeroProductLeads\Models\ProductLead;
use Techquity\AeroProductLeads\Services\LocationService;

class SendLeadEmailsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $locationService;

    public function __construct()
    {
        $this->locationService = app(LocationService::class);
    }

    public function handle()
    {
        $radius = setting('product-leads.store-radius');
        $emailWaitTime = setting('product-leads.email-wait-time');
        $fallbackEnabled = setting('product-leads.fallback-email-enabled');
        $fallbackEmail = setting('product-leads.fallback-email');

        $now = now();

        // Handle leads for the first email
        $this->processEmails(
            $radius,
            $now->copy()->subDays($emailWaitTime),
            $fallbackEnabled,
            $fallbackEmail
        );
    }

    protected function processEmails(
        int $radius,
        \Carbon\Carbon $cutoffDate,
        bool $fallbackEnabled,
        ?string $fallbackEmail
    ) {
        $leads = ProductLead::whereNull('email_sent_at')
            ->where('created_at', '<=', $cutoffDate)
            ->get();

        foreach ($leads as $lead) {
            $nearestStoreEmail = $this->locationService->findNearestStore(
                $lead->latitude,
                $lead->longitude,
                $radius
            );

            $recipientEmail = $nearestStoreEmail;

            // Use fallback email if no store is found and fallback is enabled
            if (!$recipientEmail && $fallbackEnabled) {
                $recipientEmail = $fallbackEmail;
            }

            // Send the email if a recipient was determined
            if ($recipientEmail) {
                Mail::to($recipientEmail)->send(new LeadEmail($lead));

                $lead->update([
                    'email_sent_at' => now(),
                ]);
            }
        }
    }
}
