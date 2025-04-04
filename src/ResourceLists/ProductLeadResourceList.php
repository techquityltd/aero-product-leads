<?php

namespace Techquity\AeroProductLeads\ResourceLists;

use Aero\Admin\ResourceLists\AbstractResourceList;
use Aero\Admin\ResourceLists\ResourceListColumn;
use Aero\Admin\ResourceLists\ResourceListSortBy;
use Aero\Admin\Traits\IsExtendable;
use Techquity\AeroProductLeads\BulkActions\ExportLeadsBulkAction;
use Techquity\AeroProductLeads\Models\ProductLead;
use Techquity\AeroProductLeads\ResourceLists\Filters\CreatedAtFilter;
use Techquity\AeroProductLeads\ResourceLists\Filters\EmailSentAtFilter;

class ProductLeadResourceList extends AbstractResourceList
{
    use IsExtendable;

    protected $tableRowClasses = ['group'];

    protected $filters = [
        CreatedAtFilter::class,
        EmailSentAtFilter::class,
    ];

    protected $bulkActions = [
        ExportLeadsBulkAction::class,
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

            ResourceListColumn::create('Lead Type', function ($row) {
                return ucfirst(str_replace('_', ' ', $row->lead_type ?? 'N/A'));
            })
            ->addClass('whitespace-no-wrap')
            ->position($positionStart += $positionIncrement),

            ResourceListColumn::create('Order', function ($row) {
                if ($row->order) {
                    return view('admin::resource-lists.link', [
                        'route' => '/admin/orders/' . $row->order->id,
                        'text' => $row->order->reference,
                    ]);
                }
                return view('admin::resource-lists.placeholder');
            })
            ->addClass('whitespace-no-wrap')
            ->position($positionStart += $positionIncrement),

            ResourceListColumn::create('Lead Item SKU', function ($row) {
                // Use order item if available, fallback to variant
                if ($row->orderItem) {
                    return view('admin::resource-lists.link', [
                        'route' => '/admin/catalog/products/' . $row->orderItem->buyable->id,
                        'text' => $row->orderItem->sku . ' (' . $row->orderItem->name . ')',
                    ]);
                } elseif ($row->variant) {
                    return view('admin::resource-lists.link', [
                        'route' => '/admin/catalog/products/' . $row->variant->product->id,
                        'text' => $row->variant->sku . ' (' . $row->variant->product->name . ')',
                    ]);
                }

                return view('admin::resource-lists.placeholder');
            })
            ->addClass('whitespace-no-wrap')
            ->position($positionStart += $positionIncrement),

            ResourceListColumn::create('Location Processed', function ($row) {
                return (!empty($row->latitude) && !empty($row->longitude)) ? 'Yes' : 'No';
            })
            ->addClass('whitespace-no-wrap')
            ->position($positionStart += $positionIncrement),

            ResourceListColumn::create('Email Sent', function ($row) {
                if ($row->email_sent_at) {
                    return 'Yes';
                } elseif (!empty($row->latitude) && !empty($row->longitude)) {
                    return 'Pending';
                } else {
                    return '';
                }
            })
            ->addClass('whitespace-no-wrap')
            ->position($positionStart += $positionIncrement),

            ResourceListColumn::create('Email Sent At', function ($row) {
                return $row->email_sent_at
                    ? $row->email_sent_at->format(setting('admin.short_date_format'))
                    : '';
            })
            ->addClass('whitespace-no-wrap')
            ->position($positionStart += $positionIncrement),

            ResourceListColumn::create('Location Recipient', function ($row) {
                return $row->location_email;
            })
            ->addClass('whitespace-no-wrap')
            ->position($positionStart += $positionIncrement),

            ResourceListColumn::create('Created At', function ($row) {
                return $row->created_at
                    ? $row->created_at->format(setting('admin.short_date_format'))
                    : '';
            })
            ->addClass('whitespace-no-wrap')
            ->position($positionStart += $positionIncrement),
        ];
    }

    public function handleSearch($search)
    {
        $this->query->where(function ($query) use ($search) {
            $query->where('location_email', 'like', "%{$search}%")
                  ->orWhereHas('order', function ($orderQuery) use ($search) {
                      $orderQuery->where('reference', 'like', "%{$search}%");
                  })
                  ->orWhereHas('orderItem', function ($orderItemQuery) use ($search) {
                      $orderItemQuery->where('sku', 'like', "%{$search}%");
                  });
        });
    }

    public function sortBys(): array
    {
        return [
            ResourceListSortBy::create(null, function ($_, $query) {
                return $query->orderByDesc('order_id');
            }),

            ResourceListSortBy::create([
                'email-sent-at-az' => 'Email Sent At New to Old',
                'email-sent-at-za' => 'Email Sent At Old to New',
            ], function ($sortBy, $query) {
                return $sortBy === 'email-sent-at-za' ? $query->orderBy('email_sent_at') : $query->orderByDesc('email_sent_at');
            }),
        ];
    }

    public function backButtonLink()
    {
        return route('admin.modules');
    }
}
