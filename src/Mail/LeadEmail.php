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
     */
    public function __construct(ProductLead $lead)
    {
        $order = $lead->order;
        $this->lead = $lead;
        $this->customerName = $order->shippingAddress->full_name ?? 'Unknown Name';
        $this->customerAddress = $order->shippingAddress->line_1 ?? 'Unknown Address';
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
