<?php

namespace Techquity\AeroCareers\ResourceLists\Filters;

use Aero\Admin\Filters\CheckboxListAdminFilter;
use Illuminate\Support\Str;
use Techquity\AeroCareers\Models\AdditionalLocation;

class AdditionalLocationsFilter extends CheckboxListAdminFilter
{
    public function title(): string
    {
        return "Additional Location Filter";
    }

    protected function handleCheckboxList(array $selected, $query)
    {
        $query->whereHas('additionalLocations', function($query) use ($selected) {
            return $query->whereIn('additional_locations.id', $selected);
        });
    }

    protected function checkboxes(): array
    {
        $additionalLocations = AdditionalLocation::orderBy('name')->get();

        $results = [];

        foreach ($additionalLocations as $additionalLocation) {
            $results[] = [
                'id' => "$additionalLocation->id",
                'name' => $additionalLocation->name,
                'url' => $this->getUrlFor($additionalLocation->id),
            ];
        }

        return $results;
    }
}