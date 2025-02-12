<?php

namespace Techquity\AeroProductLeads\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Support\Collection;
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

        $mergeOrderItems = setting('product-leads.merge-order-items');

        $leadsQuery = ProductLead::whereNull('email_sent_at')
        ->where('created_at', '<=', $cutoffDate);

        if ($mergeOrderItems) {
            $leadsByOrder = $leadsQuery->get()->groupBy('order_id');
    
            foreach ($leadsByOrder as $orderId => $leads) {
                $order = $leads->first()->order;
                if (!$order) continue;
    
                $orderItems = $leads->pluck('orderItem')->unique(); // Get unique order items
    
                $recipientEmail = $this->resolveRecipientEmail($leads, $radius, $fallbackEnabled, $fallbackEmail);
    
                if ($recipientEmail) {
                    Mail::to($recipientEmail)->send(new LeadEmail($order, $orderItems));
    
                    ProductLead::where('order_id', $orderId)->update([
                        'email_sent_at' => now(),
                        'location_email' => $recipientEmail
                    ]);
                }
            }
        } else {
            // Existing per-lead email logic
            foreach ($leadsQuery->get() as $lead) {
                $recipientEmail = $this->resolveRecipientEmail(collect([$lead]), $radius, $fallbackEnabled, $fallbackEmail);
    
                if ($recipientEmail) {
                    Mail::to($recipientEmail)->send(new LeadEmail($lead->order, collect([$lead->orderItem])));
    
                    $lead->update([
                        'email_sent_at' => now(),
                        'location_email' => $recipientEmail
                    ]);
                }
            }
        }
    }

    protected function resolveRecipientEmail(Collection $leads, int $radius, bool $fallbackEnabled, ?string $fallbackEmail)
    {
        // If merging order items, check the first lead (they all belong to the same order)
        $lead = $leads->first();
        
        if (!$lead || !$lead->latitude || !$lead->longitude) {
            return $fallbackEnabled ? $fallbackEmail : null;
        }

        // Find the nearest store based on the first lead with location data
        return $this->locationService->findNearestStore(
            $lead->latitude,
            $lead->longitude,
            $radius
        ) ?? ($fallbackEnabled ? $fallbackEmail : null);
    }
}
