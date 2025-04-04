<?php

use Illuminate\Support\Facades\Route;
use Techquity\AeroProductLeads\Http\Controllers\ProductLeadController;

Route::post('/product-leads/form', [ProductLeadController::class, 'store'])->name('product-leads.form');
