<?php

namespace Techquity\AeroProductLeads\Services;

use Illuminate\Support\Facades\DB;

class LocationService
{
    /**
     * Find the nearest store to a given location within a radius, excluding ignored locations.
     *
     * @param float $lat
     * @param float $lng
     * @param int $radius
     * @return string|null
     */
    public function findNearestStore(float $lat, float $lng, int $radius): ?string
    {
        // Get ignored locations from settings
        $ignoredLocations = setting('product-leads.ignore-locations');

        // Convert collection to an array of IDs
        $ignoredIds = collect($ignoredLocations)->pluck('id')->toArray();

        $query = "
            SELECT email, (
                3959 * ACOS(
                    COS(RADIANS(?)) * COS(RADIANS(lat)) * 
                    COS(RADIANS(lng) - RADIANS(?)) + 
                    SIN(RADIANS(?)) * SIN(RADIANS(lat))
                )
            ) AS distance
            FROM locations
            " . (count($ignoredIds) ? "WHERE id NOT IN (" . implode(',', array_fill(0, count($ignoredIds), '?')) . ")" : "") . "
            HAVING distance <= ?
            ORDER BY distance ASC
            LIMIT 1
        ";

        // Prepare bindings
        $bindings = array_merge([$lat, $lng, $lat], $ignoredIds, [$radius]);

        // Execute query
        $nearest = DB::selectOne($query, $bindings);

        return $nearest->email ?? null;
    }
}
