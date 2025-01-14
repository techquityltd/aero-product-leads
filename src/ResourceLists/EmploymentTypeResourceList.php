<?php

namespace Techquity\AeroCareers\ResourceLists;

use Aero\Admin\ResourceLists\AbstractResourceList;
use Aero\Admin\ResourceLists\ResourceListColumn;
use Techquity\AeroCareers\Models\EmploymentType;

class EmploymentTypeResourceList extends AbstractResourceList
{
    protected $headerSlot = 'admin.employment-types.header';

    public function __construct(EmploymentType $employmentType)
    {
        $this->resource = $employmentType;
    }

    protected function columns(): array
    {
        return [
            ResourceListColumn::create('Name', function ($row) {
                $route = route('admin.careers.employment-types.edit', $row);
                $text = $row->name;
                return view('admin::resource-lists.link', compact('route', 'text'));
            }),

            ResourceListColumn::create('', function ($row) {
                $route = route('admin.careers.employment-types.edit', $row);
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
