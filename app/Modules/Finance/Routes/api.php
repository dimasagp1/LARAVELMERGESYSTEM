<?php

use App\Modules\Finance\Controllers\BudgetGatekeeperApiController;
use Illuminate\Support\Facades\Route;

Route::prefix('finance')
    ->name('api.finance.')
    ->group(function () {
        // Real-Time Budget Gatekeeper API
        Route::post('/budget-gatekeeper/check', [BudgetGatekeeperApiController::class, 'check'])->name('gatekeeper.check');
        Route::post('/budget-gatekeeper/book', [BudgetGatekeeperApiController::class, 'book'])->name('gatekeeper.book');
    });
