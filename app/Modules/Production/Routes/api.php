<?php

use Illuminate\Support\Facades\Route;

Route::prefix('production')
    ->name('api.production.')
    ->group(function () {
        // Floor Machine IoT, Digital Batch Timer & Downtime Lock API Endpoints
    });
