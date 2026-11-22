<?php

use App\Modules\Hris\Controllers\HrisController;
use Illuminate\Support\Facades\Route;

Route::prefix('hris')
    ->name('hris.')
    ->group(function () {
        Route::get('/', [HrisController::class, 'index'])->name('index');
    });
