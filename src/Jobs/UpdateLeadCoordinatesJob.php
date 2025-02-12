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
        // Get leads grouped by order_id where no coordinates exist
        $leadsByOrder = ProductLead::whereNull('latitude')
            ->whereNull('longitude')
            ->get()
            ->groupBy('order_id');

        foreach ($leadsByOrder as $orderId => $leads) {
            // Get postcode from the first lead (all should be the same within an order)
            $lead = $leads->first();
            if (!$lead || !$lead->postcode) {
                continue;
            }

            try {
                // Fetch coordinates only once per order
                $coordinates = $this->googleGeocodingService->getCoordinates($lead->postcode);

                if ($coordinates) {
                    // Update all leads in this order with the same coordinates
                    ProductLead::where('order_id', $orderId)->update([
                        'latitude' => $coordinates['latitude'],
                        'longitude' => $coordinates['longitude'],
                    ]);
                } else {
                    Log::warning("Coordinates not found for postcode: {$lead->postcode}");
                }
            } catch (\Exception $e) {
                Log::error("Error updating coordinates for order ID {$orderId}: {$e->getMessage()}");
            }
        }
    }
}
