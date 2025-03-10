<?php

namespace Techquity\AeroProductLeads\Http\Controllers;

use Aero\Admin\Http\Controllers\Controller;
use Aero\Store\Models\Location;
use Illuminate\Http\Request;
use Techquity\AeroProductLeads\Models\ProductLead;
use Techquity\AeroProductLeads\ResourceLists\ProductLeadResourceList;

class ProductLeadController extends Controller
{
    public function index(ProductLeadResourceList $list, Request $request)
    {
        return view('admin::resource-lists.index', [
            'list' => $list = $list(),
            'results' => $list->apply($request->all())
                ->paginate($request->input('per_page', 24) ?? 24),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'itemId' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'telephone' => 'nullable|string|max:20',
            'fake-postcode' => 'nullable|string|max:10',
            'preferred-branch' => 'nullable|string|max:255',
        ]);

        // Format preferred branch to match the email structure
        if (!empty($validated['preferred-branch'])) {
            $preferredBranch = strtolower(str_replace(' ', '-', $validated['preferred-branch']));

            // Find the nearest matching store email
            $locationEmail = Location::where('email', 'LIKE', "%$preferredBranch%")->value('email');
        } else {
            $locationEmail = setting('product-leads.fallback-email-enabled') ? setting('product-leads.fallback-email') : null;
        }

        if (!$locationEmail) {
            return response()->json(['success' => false]);
        }

        // // Save the product lead
        $productLead = ProductLead::create([
            'order_item_id' => $request->itemId,
            'postcode' => $request->fake-postcode,
            'location_email' => $locationEmail,
        ]);

        return response()->json(['success' => true]);
    }
}