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
            'fake-postcode' => 'nullable|string|max:10',
            'preferred-branch' => 'nullable|string|max:255',
        ]);

        dd($request);

        // // Find the nearest store email based on postcode
        // $locationEmail = Location::where('email', 'LIKE', $request->preferred-branch . '%')->value('email');

        // // Save the product lead
        // $productLead = ProductLead::create([
        //     'order_item_id' => $request->itemId,
        //     'postcode' => $request->fake-postcode,
        //     'location_email' => $locationEmail,
        // ]);

        return response()->json(['success' => true]);
    }
}