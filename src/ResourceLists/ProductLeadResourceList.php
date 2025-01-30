<?php

namespace Techquity\AeroProductLeads\ResourceLists;

use Aero\Admin\ResourceLists\AbstractResourceList;
use Aero\Admin\ResourceLists\ResourceListColumn;
// use Aero\Admin\ResourceLists\ResourceListSortBy;
use Aero\Admin\Traits\IsExtendable;
use Techquity\AeroProductLeads\Models\ProductLead;

class ProductLeadResourceList extends AbstractResourceList
{
    use IsExtendable;

    protected $tableRowClasses = ['group'];

    protected $filters = [
    ];

    public function __construct(ProductLead $lead)
    {
        $this->resource = $lead;
    }

    protected function columns(): array
    {
        $positionStart = 0;
        $positionIncrement = 10;

        return [

            ResourceListColumn::create('Order', function ($row) {
                return $row->order->reference;
            })
            ->addClass('whitespace-no-wrap')
            ->position($positionStart = $positionStart + $positionIncrement),
        ];
    }

    public function handleSearch($search)
    {
        $this->query->where(static function ($query) use ($search) {
            $query->where('search_term', 'like', "%{$search}%");
        });
    }

    public function sortBys(): array
    {
        return [
        ];
    }

    public function backButtonLink()
    {
        return route('admin.modules');
    }
}
