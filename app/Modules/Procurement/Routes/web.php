<?php

use App\Modules\Procurement\Controllers\ProcurementController;
use Illuminate\Support\Facades\Route;

Route::prefix('procurement')
    ->name('procurement.')
    ->group(function () {
        Route::get('/', [ProcurementController::class, 'index'])->name('index');
    });
