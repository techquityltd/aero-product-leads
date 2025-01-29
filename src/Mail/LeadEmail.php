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
    public $customerPhone;
    public $customerName;
    public $customerAddress;
    protected $subjectLine;

    /**
     * Create a new message instance.
     *
     * @param ProductLead $lead
     */
    public function __construct(ProductLead $lead)
    {
        $order = $lead->order;
        $shippingAddress = $order->shippingAddress;
        $product = $lead->orderItem->buyable->product ?? null;

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

        $this->subjectLine = $this->determineSubject($product);
    }

    protected function determineSubject($product)
    {
        if (!$product) {
            return 'New Product Lead';
        }

        // Get the configured lead-tags from settings
        $leadTags = setting('product-leads.lead-tags');

        if (!$leadTags || !$leadTags->count()) {
            return 'New Product Lead';
        }

        // Find the first matching tag between the product and lead-tags setting
        $matchingTag = $product->tags->intersect($leadTags)->first();

        return $matchingTag 
            ? "New {$matchingTag->name}" 
            : 'New Product Lead';
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->subject($this->subjectLine)
            ->view('product-leads::mail.lead');
    }
}
