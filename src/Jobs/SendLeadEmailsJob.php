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

        $this->onQueue(setting('product-leads.queue'));
    }

    public function handle()
    {
        $radius = setting('product-leads.radius');
        $firstWaitTime = setting('product-leads.first-email-wait-time');
        $secondWaitTime = setting('product-leads.second-email-wait-time');
        $fallbackEnabled = setting('product-leads.fallback-email-enabled');
        $fallbackEmail = setting('product-leads.fallback-email');

        $now = now();

        // Handle leads for the first email
        $this->processEmails(
            $radius,
            $now->copy()->subDays($firstWaitTime),
            'email1_sent_at',
            'first',
            $fallbackEnabled,
            $fallbackEmail
        );

        // Handle leads for the second email
        $this->processEmails(
            $radius,
            $now->copy()->subDays($secondWaitTime),
            'email2_sent_at',
            'second',
            $fallbackEnabled,
            $fallbackEmail
        );
    }

    protected function processEmails(
        int $radius,
        \Carbon\Carbon $cutoffDate,
        string $emailSentColumn,
        string $emailType,
        bool $fallbackEnabled,
        ?string $fallbackEmail
    ) {
        $leads = ProductLead::whereNull($emailSentColumn)
            ->where('created_at', '<=', $cutoffDate)
            ->get();

        foreach ($leads as $lead) {
            $nearestStoreEmail = $this->locationService->findNearestStore(
                $lead->lat,
                $lead->lng,
                $radius
            );

            $recipientEmail = $nearestStoreEmail;

            // Use fallback email if no store is found and fallback is enabled
            if (!$recipientEmail && $fallbackEnabled) {
                $recipientEmail = $fallbackEmail;
            }

            // Send the email if a recipient was determined
            if ($recipientEmail) {
                Mail::to($recipientEmail)->send(new LeadEmail($lead, $emailType));

                $lead->update([
                    $emailSentColumn => now(),
                ]);
            }
        }
    }
}
