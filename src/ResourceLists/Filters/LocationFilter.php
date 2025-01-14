<?php

namespace Techquity\AeroCareers\ResourceLists\Filters;

use Aero\Admin\Filters\CheckboxListAdminFilter;
use Aero\Store\Models\Location;

class LocationFilter extends CheckboxListAdminFilter
{
    public function title(): string
    {
        return "Location Filter";
    }

    protected function handleCheckboxList(array $selected, $query)
    {
        $query->whereHas('locations', function($query) use ($selected) {
            return $query->whereIn('locations.id', $selected);
        });
    }

    protected function checkboxes(): array
    {
        $locations = Location::query()->orderByTranslated('name')->get();

        $results = [];

        foreach ($locations as $location) {
            $results[] = [
                'id' => "$location->id",
                'name' => $location->name,
                'url' => $this->getUrlFor($location->id),
            ];
        }

        return $results;
    }
}