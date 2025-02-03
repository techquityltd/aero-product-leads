<?php

namespace Techquity\AeroProductLeads\ResourceLists\Filters;

use Aero\Admin\Filters\DateRangeAdminFilter;

class CreatedAtFilter extends DateRangeAdminFilter
{
    protected function handleDateRange($startDate, $endDate, $query)
    {
        return $query->getQuery()
            ->where('created_at',  '>=', $startDate)
            ->where('created_at',  '<=', $endDate);
    }

    public function baseName(): string
    {
        return 'CreatedAt';
    }
}