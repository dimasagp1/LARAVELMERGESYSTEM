<?php

use Illuminate\Support\Facades\Route;

Route::prefix('procurement')
    ->name('api.procurement.')
    ->group(function () {
        // Odoo PO Sync & Supplier Bidding API Endpoints
    });
