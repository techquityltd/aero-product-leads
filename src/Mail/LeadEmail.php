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
    public $customerName;
    public $customerAddress;

    /**
     * Create a new message instance.
     *
     * @param ProductLead $lead
     * @param string $emailType
     */
    public function __construct(ProductLead $lead, string $emailType)
    {
        $this->lead = $lead;
        $this->emailType = $emailType;

        // Extract customer details from the order's shipping address
        $order = $lead->order;
        $this->customerName = $order->shippingAddress->full_name ?? 'Unknown Name';
        $this->customerAddress = $order->shippingAddress->address_1 ?? 'Unknown Address';
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        $subject = $this->emailType === 'first'
            ? "New Product Lead: {$this->customerName}"
            : "Follow-up: Product Lead for {$this->customerName}";

        return $this->subject($subject)
            ->view('product-leads::mail.lead');
    }
}
