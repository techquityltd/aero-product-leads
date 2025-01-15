<?php

namespace Techquity\AeroProductLeads\Services;

use Illuminate\Support\Facades\DB;

class LocationService
{
    /**
     * Find the nearest store to a given location within a radius.
     *
     * @param float $lat
     * @param float $lng
     * @param int $radius
     * @return string|null
     */
    public function findNearestStore(float $lat, float $lng, int $radius): ?string
    {
        $query = "
            SELECT email, (
                3959 * ACOS(
                    COS(RADIANS(?)) * COS(RADIANS(lat)) * 
                    COS(RADIANS(lng) - RADIANS(?)) + 
                    SIN(RADIANS(?)) * SIN(RADIANS(lat))
                )
            ) AS distance
            FROM locations
            HAVING distance <= ?
            ORDER BY distance ASC
            LIMIT 1
        ";

        $nearest = DB::selectOne($query, [$lat, $lng, $lat, $radius]);

        return $nearest->email ?? null;
    }
}
