<?php

use App\Modules\Finance\Controllers\FinanceController;
use Illuminate\Support\Facades\Route;

Route::prefix('finance')
    ->name('finance.')
    ->group(function () {
        Route::get('/', [FinanceController::class, 'index'])->name('index');
    });
