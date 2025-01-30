<?php

namespace Techquity\AeroProductLeads\Http\Controllers;

use Aero\Admin\Http\Controllers\Controller;
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
}