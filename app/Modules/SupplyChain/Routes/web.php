<?php

use App\Modules\SupplyChain\Controllers\SupplyChainController;
use Illuminate\Support\Facades\Route;

Route::prefix('supply-chain')
    ->name('supply-chain.')
    ->group(function () {
        Route::get('/', [SupplyChainController::class, 'index'])->name('index');
    });
