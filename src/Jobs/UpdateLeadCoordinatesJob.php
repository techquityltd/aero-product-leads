<?php

namespace Techquity\AeroProductLeads\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Techquity\AeroProductLeads\Models\ProductLead;
use Techquity\AeroProductLeads\Services\GoogleGeocodingService;

class UpdateLeadCoordinatesJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $googleGeocodingService;

    public function __construct()
    {
        $this->googleGeocodingService = app(GoogleGeocodingService::class);
    }

    public function handle()
    {
        // Get leads where latitude & longitude are missing
        $leads = ProductLead::whereNull('latitude')->whereNull('longitude')->get();

        // Group by order_id for order leads, variant_id for form leads
        $groupedLeads = $leads->groupBy(function ($lead) {
            return $lead->order_id ?? 'form-' . $lead->variant_id;
        });

        foreach ($groupedLeads as $key => $leads) {
            $lead = $leads->first();
            if (!$lead || !$lead->postcode) {
                continue;
            }

            try {
                // Fetch coordinates using postcode
                $coordinates = $this->googleGeocodingService->getCoordinates($lead->postcode);

                if ($coordinates) {
                    // Update all leads in this group
                    ProductLead::whereIn('id', $leads->pluck('id'))->update([
                        'latitude' => $coordinates['latitude'],
                        'longitude' => $coordinates['longitude'],
                    ]);
                } else {
                    Log::warning("Coordinates not found for postcode: {$lead->postcode} (Lead Type: " . ($lead->order_id ? 'Order' : 'Form') . ")");
                }
            } catch (\Exception $e) {
                Log::error("Error updating coordinates for lead group {$key}: {$e->getMessage()}");
            }
        }
    }
}
