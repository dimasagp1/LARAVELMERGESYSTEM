<?php

use App\Modules\Production\Controllers\ProductionController;
use Illuminate\Support\Facades\Route;

Route::prefix('production')
    ->name('production.')
    ->group(function () {
        Route::get('/', [ProductionController::class, 'index'])->name('index');
    });
