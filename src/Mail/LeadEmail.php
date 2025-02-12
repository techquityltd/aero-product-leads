<?php

namespace Techquity\AeroProductLeads\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Support\Collection;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Techquity\AeroProductLeads\Models\ProductLead;

class LeadEmail extends Mailable
{
    use Queueable, SerializesModels;

    public $order;
    public $orderItems;
    public $customerPhone;
    public $customerName;
    public $customerAddress;
    protected $subjectLine;

    /**
     * Create a new message instance.
     *
     * @param ProductLead $lead
     */
    public function __construct($order, Collection $orderItems)
    {
        $this->order = $order;
        $this->orderItems = $orderItems;
        $shippingAddress = $order->shippingAddress;

        $this->customerPhone = $shippingAddress->mobile ?? $shippingAddress->phone ?? '';
        $this->customerName = $shippingAddress->full_name ?? 'Unknown Name';

        $addressParts = array_filter([
            $shippingAddress->line_1 ?? null,
            $shippingAddress->line_2 ?? null,
            $shippingAddress->city ?? null
        ]);

        $this->customerAddress = !empty($addressParts) ? implode(', ', $addressParts) : 'Unknown Address';

        $this->subjectLine = $this->determineSubject($orderItems);
    }

    protected function determineSubject(Collection $orderItems)
    {
        $leadTags = setting('product-leads.lead-tags');

        if (!$leadTags || !$leadTags->count()) {
            return 'New Product Lead';
        }

        $foundTags = collect();

        foreach ($orderItems as $item) {
            $product = $item->buyable->product ?? null;
            if ($product) {
                $matchingTags = $product->tags->intersect($leadTags)->pluck('name');
                $foundTags = $foundTags->merge($matchingTags);
            }
        }

        $foundTags = $foundTags->unique();

        return $foundTags->count() === 1
            ? "New {$foundTags->first()} Lead"
            : 'New Product Leads';
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
