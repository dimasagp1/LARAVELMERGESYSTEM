<?php

use App\Modules\Production\Controllers\ProductionApiController;
use Illuminate\Support\Facades\Route;

// Production External API Endpoints (HRIS & Finance Feed)
Route::prefix('v1/production')->name('api.production.')->group(function () {
    Route::get('/metrics', [ProductionApiController::class, 'metrics'])->name('metrics');
    Route::get('/finance-feed', [ProductionApiController::class, 'financeFeed'])->name('finance-feed');
});
