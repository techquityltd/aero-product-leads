<?php

namespace Techquity\AeroProductLeads\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Techquity\AeroProductLeads\Models\ProductLead;

class LeadEmail extends Mailable
{
    use Queueable, SerializesModels;

    public $lead;
    public $emailType;
    public $customerPhone;
    public $customerName;
    public $customerAddress;

    /**
     * Create a new message instance.
     *
     * @param ProductLead $lead
     */
    public function __construct(ProductLead $lead)
    {
        $order = $lead->order;
        $shippingAddress = $order->shippingAddress;

        $this->lead = $lead;
        $this->customerPhone = $shippingAddress->mobile 
            ?? $shippingAddress->phone 
            ?? '';

        $this->customerName = $shippingAddress->full_name ?? 'Unknown Name';

        // Build full address including line_1, line_2, and city
        $addressParts = array_filter([
            $shippingAddress->line_1 ?? null,
            $shippingAddress->line_2 ?? null,
            $shippingAddress->city ?? null
        ]);

        $this->customerAddress = !empty($addressParts) 
            ? implode(', ', $addressParts) 
            : 'Unknown Address';
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        $subject = "New Product Lead: {$this->customerName}";

        return $this->subject($subject)
            ->view('product-leads::mail.lead');
    }
}
