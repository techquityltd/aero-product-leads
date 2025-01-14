<?php

namespace Techquity\AeroCareers\ResourceLists\Filters;

use Aero\Admin\Filters\CheckboxListAdminFilter;
use Techquity\AeroCareers\Models\ContractType;

class ContractTypeFilter extends CheckboxListAdminFilter
{
    public function title(): string
    {
        return "Contract Type Filter";
    }

    protected function handleCheckboxList(array $selected, $query)
    {
        $query->whereHas('contractTypes', function($query) use ($selected) {
            return $query->whereIn('contract_types.id', $selected);
        });
    }

    protected function checkboxes(): array
    {
        $types = ContractType::orderBy('name')->get();

        $results = [];

        foreach ($types as $type) {
            $results[] = [
                'id' => "$type->id",
                'name' => $type->name,
                'url' => $this->getUrlFor($type->id),
            ];
        }

        return $results;
    }
}