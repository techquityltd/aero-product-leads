<?php

namespace Techquity\AeroCareers\ResourceLists;

use Aero\Admin\ResourceLists\AbstractResourceList;
use Aero\Admin\ResourceLists\ResourceListColumn;
use Techquity\AeroCareers\Models\AdditionalLocation;

class AdditionalLocationResourceList extends AbstractResourceList
{
    protected $headerSlot = 'admin.additional-locations.header';

    public function __construct(AdditionalLocation $additionalLocation)
    {
        $this->resource = $additionalLocation;
    }

    protected function columns(): array
    {
        return [
            ResourceListColumn::create('Name', function ($row) {
                $route = route('admin.careers.additional-locations.edit', $row);
                $text = $row->name;
                return view('admin::resource-lists.link', compact('route', 'text'));
            }),

            ResourceListColumn::create('Email', function ($row) {
                if ($row->email) {
                    return $row->email;
                } else {
                    return view('admin::resource-lists.placeholder');
                }
            }),

            ResourceListColumn::create('Phone', function ($row) {
                if ($row->phone) {
                    return $row->phone;
                } else {
                    return view('admin::resource-lists.placeholder');
                }
            }),

            ResourceListColumn::create('Address', function ($row) {
                if ($row->formatted_alt) {
                    return $row->formatted_alt;
                } else {
                    return view('admin::resource-lists.placeholder');
                }
            }),

            ResourceListColumn::create('', function ($row) {
                $route = route('admin.careers.additional-locations.edit', $row);
                return view('admin::resource-lists.manage-link', compact('route'));
            })->addClasses(['text-right', 'pr-8'])
        ];
    }

    protected function handleSearch($search)
    {
        $search = strtolower($search);

        $this->query->whereLower('name', 'like', "%{$search}%");
    }
}