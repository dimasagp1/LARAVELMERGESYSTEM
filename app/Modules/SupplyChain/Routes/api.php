<?php

use Illuminate\Support\Facades\Route;

Route::prefix('supply-chain')
    ->name('api.supply-chain.')
    ->group(function () {
        // Warehouse Quarantine, MRP Run & 3-Way Matching AP API Endpoints
    });
