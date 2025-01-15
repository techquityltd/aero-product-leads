<?php

namespace Techquity\AeroProductLeads\Models;

use Aero\Cart\Models\Order;
use Aero\Cart\Models\OrderItem;
use Aero\Common\Models\Model;

class ProductLead extends Model
{
    protected $fillable = [
        'order_id',
        'order_item_id',
        'lat',
        'lng',
        'email1_sent_at',
        'email2_sent_at',
    ];

    protected $casts = [
        'email1_sent_at' => 'datetime',
        'email2_sent_at' => 'datetime',
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
}
