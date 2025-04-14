<?php

namespace Techquity\AeroProductLeads\BulkActions;

use Aero\Admin\Jobs\BulkActionJob;
use Carbon\Carbon;
use Illuminate\Support\Arr;
use Techquity\AeroProductLeads\ResourceLists\ProductLeadResourceList;
use Illuminate\Support\Facades\File;

class ExportLeadsBulkAction extends BulkActionJob
{
    protected $list;

    public function __construct(ProductLeadResourceList $list)
    {
        $this->list = $list;
    }

    public function response()
    {
        return response()->download(storage_path("app/exports/{$this->name}.csv"));
    }

    public function handle(): void
    {
        $fileName = 'product-leads'.Carbon::now()->format('YmdHis');

        $this->name = $fileName;

        if (!File::exists(storage_path("app/exports"))) {
            File::makeDirectory(storage_path("app/exports"));
        }

        $file = fopen(storage_path("app/exports/{$fileName}.csv"), 'w');

        // Set required CSV Headings
        $csvHeadings = [
            'Lead Type' => '',
            'Order' => '',
            'Lead Item SKU' => '',
            'Email Sent At' => '',
            'Location Recipient' => '',
            'Created At' => '',
        ];
        
        // Open file and write CSV headers
        fputcsv($file, Arr::flatten(array_keys($csvHeadings)));

        // Write lead data
        foreach ($this->list->items() as $lead) {
            $row = array_merge($csvHeadings, array_intersect_key([
                'Lead Type' => $lead->lead_type,
                'Order' => optional($lead->order)->reference,
                'Lead Item SKU' => $lead->orderItem->sku,
                'Email Sent At' => $lead->email_sent_at,
                'Location Recipient' => $lead->location_email,
                'Created At' => $lead->created_at
            ], $csvHeadings));

            fputcsv($file, $row);
        }

        fclose($file);
    }
}
