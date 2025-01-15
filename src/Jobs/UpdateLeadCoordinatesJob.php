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

        // Dynamically assign the queue name from settings
        $this->onQueue(setting('product-leads.queue'));
    }

    public function handle()
    {
        $leads = ProductLead::whereNull('lat')->whereNull('lng')->get();

        foreach ($leads as $lead) {
            try {
                $coordinates = $this->googleGeocodingService->getCoordinates($lead->postcode);

                if ($coordinates) {
                    $lead->update([
                        'lat' => $coordinates['lat'],
                        'lng' => $coordinates['lng'],
                    ]);
                } else {
                    Log::warning("Coordinates not found for postcode: {$lead->postcode}");
                }
            } catch (\Exception $e) {
                Log::error("Error updating coordinates for lead ID {$lead->id}: {$e->getMessage()}");
            }
        }
    }
}
