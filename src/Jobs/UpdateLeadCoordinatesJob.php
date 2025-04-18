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

        foreach ($leads as $key => $lead) {

            if (!$lead || !$lead->postcode) {
                continue;
            }

            try {
                // Fetch coordinates using postcode
                $coordinates = $this->googleGeocodingService->getCoordinates($lead->postcode);

                if ($coordinates) {
                    // Update lead
                    $lead->latitude = $coordinates['latitude'];
                    $lead->longitude = $coordinates['longitude'];

                    $lead->save();
                } else {
                    Log::warning("Coordinates not found for postcode: {$lead->postcode} (Lead Type: {$lead->lead_type})");
                }
            } catch (\Exception $e) {
                Log::error("Error updating coordinates for lead group {$key}: {$e->getMessage()}");
            }
        }
    }
}
