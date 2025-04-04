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
            'variant_id' => 'required|integer|exists:variants,id',
            'email' => 'required|email|max:255',
            'name' => 'nullable|string|max:255',
            'telephone' => 'nullable|string|max:20',
            'postcode' => 'required|string|max:10',
            // 'preferred_branch' => 'nullable|string|max:255',
        ]);

        // Format preferred branch to match email structure
        // $locationEmail = null;
        // if (!empty($validated['preferred_branch'])) {
        //     $preferredBranch = strtolower(str_replace(' ', '-', $validated['preferred_branch']));
        //     $locationEmail = Location::where('email', 'LIKE', "%$preferredBranch%")->value('email');
        // }

        // if (!$locationEmail) {
        //     $locationEmail = setting('product-leads.fallback-email-enabled') ? setting('product-leads.fallback-email') : null;
        // }

        // Save the product lead
        $productLead = ProductLead::create([
            'variant_id' => $validated['variant_id'],
            'customer_email' => $validated['email'],
            'customer_name' => $validated['name'],
            'customer_phone' => $validated['telephone'],
            'postcode' => $validated['postcode'],
            // 'location_email' => $locationEmail,
            'lead_type' => 'form',
        ]);

        return response()->json(['success' => true]);
    }

}