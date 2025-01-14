<?php

namespace Techquity\AeroCareers\ResourceLists;

use Aero\Admin\ResourceLists\AbstractResourceList;
use Aero\Admin\ResourceLists\ResourceListColumn;
use Techquity\AeroCareers\Models\Career;
use Techquity\AeroCareers\ResourceLists\Filters\AdditionalLocationsFilter;
use Techquity\AeroCareers\ResourceLists\Filters\CareerGroupFilter;
use Techquity\AeroCareers\ResourceLists\Filters\ContractTypeFilter;
use Techquity\AeroCareers\ResourceLists\Filters\LocationFilter;

class CareerResourceList extends AbstractResourceList
{
    protected $headerSlot = 'admin.careers.header';

    protected $filters = [
        LocationFilter::class,
        CareerGroupFilter::class,
        ContractTypeFilter::class,
        AdditionalLocationsFilter::class,
    ];

    public function __construct(Career $career)
    {
        $this->resource = $career;
    }

    protected function columns(): array
    {
        return [
            ResourceListColumn::create('Title', function ($row) {
                $route = route('admin.careers.edit', $row);
                $text = $row->title;
                return view('admin::resource-lists.link', compact('route', 'text'));
            }),

            ResourceListColumn::create('Locations', function ($row) {
                if (count($row->locations)) {
                    return $row->locations->first()->name . ($row->locations->count() > 1 ? ' +' . ($row->locations->count() - 1) : '');
                } else if (count($row->additionalLocations)) {
                    return $row->additionalLocations->first()->name . ($row->additionalLocations->count() > 1 ? ' +' . ($row->additionalLocations->count() - 1) : '');
                } else {
                    return view ('admin::resource-lists.placeholder');
                }
            }),

            ResourceListColumn::create('Employment Type', function ($row) {
                if ($row->employmentTypes()->first()) {
                    return $row->employmentTypes()->first()->name;
                } else {
                    return view ('admin::resource-lists.placeholder');
                }
            }),

            ResourceListColumn::create('Group', function ($row) {
                if ($row->careerGroup) {
                    return $row->careerGroup->name;
                } else {
                    return view ('admin::resource-lists.placeholder');
                }
            }),

            ResourceListColumn::create('Type', function ($row) {
                if (count($row->contractTypes)) {
                    return $row->contractTypes->pluck('name')->implode(', ');
                } else {
                    return view ('admin::resource-lists.placeholder');
                }
            }),

            ResourceListColumn::create('', function ($row) {
                $route = route('careers.show', $row);
                return view('careers::admin.resource-lists.show', compact('route'));
            })->addClasses(['text-right', 'pr-8']),
        ];
    }

    protected function handleSearch($search)
    {
        $search = strtolower($search);

        $this->query->whereLower('title', 'like', "%{$search}%");
    }
}
