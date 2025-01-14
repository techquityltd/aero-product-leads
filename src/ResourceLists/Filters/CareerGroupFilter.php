<?php

namespace Techquity\AeroCareers\ResourceLists\Filters;

use Aero\Admin\Filters\DropdownAdminFilter;
use Techquity\AeroCareers\Models\CareerGroup;

class CareerGroupFilter extends DropdownAdminFilter
{
    public function title(): string
    {
        return 'Career Group';
    }

    protected function handleDropdown($selected, $query)
    {
        if ($selected == 'none') {
            $query->whereNull('career_group_id');
        } elseif ($selected !== 'all') {
            $query->where('career_group_id', $selected);
        }
    }

    protected function dropdowns(): array
    {
        $groups = CareerGroup::orderBy('name')->get();

        $results = [
            [
                'value' => 'all',
                'name' => 'All',
            ],
            [
                'value' => 'none',
                'name' => 'None',
            ]
        ];

        foreach ($groups as $group) {
            $results[] = [
                'value' => "$group->id",
                'name' => $group->name,
            ];
        }

        return $results;
    }
}