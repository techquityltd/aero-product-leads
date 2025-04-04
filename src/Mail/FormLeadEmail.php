<?php

namespace Techquity\AeroProductLeads\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Techquity\AeroProductLeads\Models\ProductLead;

class FormLeadEmail extends Mailable
{
    use Queueable, SerializesModels;

    public $lead;
    public $product;
    public $variant;
    protected $subjectLine;

    public function __construct(ProductLead $lead)
    {
        $this->lead = $lead;
        $this->variant = $lead->variant;
        $this->product = $this->variant->product ?? null;
        $this->subjectLine = $this->determineSubject();
    }

    protected function determineSubject(): string
    {
        if (!$this->product) {
            return 'New Product Form Lead';
        }

        $leadTags = setting('product-leads.lead-tags');

        if ($leadTags && $this->product->tags->isNotEmpty()) {
            $matchingTags = $this->product->tags->intersect($leadTags)->pluck('name');

            if ($matchingTags->count() === 1) {
                return "New {$matchingTags->first()} Lead";
            } elseif ($matchingTags->count() > 1) {
                return 'New Product Leads';
            }
        }

        return "New Product Form Lead";
    }

    public function build()
    {
        return $this->subject($this->subjectLine)
            ->view('product-leads::mail.form-lead');
    }
}
