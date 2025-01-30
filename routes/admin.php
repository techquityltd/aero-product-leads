<?php

use Illuminate\Support\Facades\Route;
use Techquity\AeroProductLeads\Http\Controllers\ProductLeadController;

Route::get('product-leads', [ProductLeadController::class, 'index'])->name('admin.productleads');
