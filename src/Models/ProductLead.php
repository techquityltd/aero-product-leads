<?php

namespace Techquity\AeroProductLeads\Models;

use Aero\Cart\Models\Order;
use Aero\Cart\Models\OrderItem;
use Aero\Catalog\Models\Variant;
use Aero\Common\Models\Model;

class ProductLead extends Model
{
    protected $fillable = [
        'order_id',
        'order_item_id',
        'variant_id',
        'postcode',
        'customer_email',
        'customer_name',
        'lead_type',
        'latitude',
        'longitude',
        'email_sent_at',
        'location_email',
    ];

    protected $casts = [
        'email_sent_at' => 'datetime',
    ];

    /**
     * Get the order associated with the product lead.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    /**
     * Get the order item associated with the product lead.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function orderItem()
    {
        return $this->belongsTo(OrderItem::class);
    }

    /**
     * Get the variant associated with the product lead.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function variant()
    {
        return $this->belongsTo(Variant::class);
    }
}
