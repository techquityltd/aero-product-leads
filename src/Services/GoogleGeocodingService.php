<?php

namespace Techquity\AeroProductLeads\Services;

use Illuminate\Support\Facades\Http;

class GoogleGeocodingService
{
    public static function getCoordinates($postcode): array|null
    {
        $apiKey = setting('product-leads.google-maps-api-key');

        // $url = "https://maps.googleapis.com/maps/api/geocode/json?key={$key}&components=postal_code:{$postcode}";

        $response = Http::get("https://maps.googleapis.com/maps/api/geocode/json", [
            'address' => $postcode,
            'key' => $apiKey,
        ]);

        dd($response);

        if ($response->successful() && !empty($response['results'])) {
            $location = $response['results'][0]['geometry']['location'];
            return [
                'latitude' => $location['lat'],
                'longitude' => $location['lng'],
            ];
        }

        return null;
    }
}