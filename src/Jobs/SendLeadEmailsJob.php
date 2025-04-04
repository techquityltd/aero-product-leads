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
use Techquity\AeroProductLeads\Mail\FormLeadEmail;
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
        $mergeOrderItems = setting('product-leads.merge-order-items');

        $cutoffDate = now()->subDays($emailWaitTime);

        // Separate order and form leads
        $orderLeads = ProductLead::whereNull('email_sent_at')
            ->where('lead_type', 'order')
            ->where('created_at', '<=', $cutoffDate)
            ->get();

        $formLeads = ProductLead::whereNull('email_sent_at')
            ->where('lead_type', 'form')
            ->where('created_at', '<=', $cutoffDate)
            ->get();

        // Handle order leads
        if ($mergeOrderItems) {
            $groupedByOrder = $orderLeads->groupBy('order_id');
            foreach ($groupedByOrder as $orderId => $leads) {
                $order = $leads->first()->order;
                if (!$order) continue;

                $orderItems = $leads->pluck('orderItem')->filter()->unique();
                $recipientEmail = $this->resolveRecipientEmail($leads, $radius, $fallbackEnabled, $fallbackEmail);

                if ($recipientEmail) {
                    Mail::to($recipientEmail)->send(new LeadEmail($order, $orderItems));

                    ProductLead::where('order_id', $orderId)->update([
                        'email_sent_at' => now(),
                        'location_email' => $recipientEmail,
                    ]);
                }
            }
        } else {
            foreach ($orderLeads as $lead) {
                $order = $lead->order;
                if (!$order || !$lead->orderItem) continue;

                $recipientEmail = $this->resolveRecipientEmail(collect([$lead]), $radius, $fallbackEnabled, $fallbackEmail);

                if ($recipientEmail) {
                    Mail::to($recipientEmail)->send(new LeadEmail($order, collect([$lead->orderItem])));

                    $lead->update([
                        'email_sent_at' => now(),
                        'location_email' => $recipientEmail,
                    ]);
                }
            }
        }

        // Handle form leads (one by one)
        foreach ($formLeads as $lead) {
            $recipientEmail = $this->resolveRecipientEmail(collect([$lead]), $radius, $fallbackEnabled, $fallbackEmail);

            if ($recipientEmail) {
                Mail::to($recipientEmail)->send(new FormLeadEmail($lead));

                $lead->update([
                    'email_sent_at' => now(),
                    'location_email' => $recipientEmail,
                ]);
            }
        }
    }

    protected function resolveRecipientEmail(Collection $leads, int $radius, bool $fallbackEnabled, ?string $fallbackEmail): ?string
    {
        $lead = $leads->first();

        if (!$lead || !$lead->latitude || !$lead->longitude) {
            return $fallbackEnabled ? $fallbackEmail : null;
        }

        return $this->locationService->findNearestStore(
            $lead->latitude,
            $lead->longitude,
            $radius
        ) ?? ($fallbackEnabled ? $fallbackEmail : null);
    }
}
