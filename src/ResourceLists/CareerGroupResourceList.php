<?php

namespace Techquity\AeroCareers\ResourceLists;

use Aero\Admin\ResourceLists\AbstractResourceList;
use Aero\Admin\ResourceLists\ResourceListColumn;
use Techquity\AeroCareers\Models\CareerGroup;

class CareerGroupResourceList extends AbstractResourceList
{
    protected $headerSlot = 'admin.career-groups.header';

    public function __construct(CareerGroup $careerGroup)
    {
        $this->resource = $careerGroup;
    }

    protected function columns(): array
    {
        return [
            ResourceListColumn::create('Name', function ($row) {
                $route = route('admin.careers.career-groups.edit', $row);
                $text = $row->name;
                return view('admin::resource-lists.link', compact('route', 'text'));
            }),

            ResourceListColumn::create('', function ($row) {
                $route = route('admin.careers.career-groups.edit', $row);

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