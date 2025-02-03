<?php

namespace Techquity\AeroProductLeads\ResourceLists\Filters;

use Aero\Admin\Filters\DateRangeAdminFilter;

class EmailSentAtFilter extends DateRangeAdminFilter
{
    protected function handleDateRange($startDate, $endDate, $query)
    {
        return $query->getQuery()
            ->where('email_sent_at',  '>=', $startDate)
            ->where('email_sent_at',  '<=', $endDate);
    }

    public function baseName(): string
    {
        return 'EmailSentAt';
    }
}