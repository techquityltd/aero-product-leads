<?php

use Illuminate\Support\Facades\Route;
use Techquity\AeroProductLeads\Http\Controllers\ProductLeadController;

Route::post('/submit-brochure-lead', [ProductLeadController::class, 'store'])->name('brochure-lead.store');
