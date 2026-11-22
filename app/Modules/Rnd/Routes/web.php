<?php

use App\Modules\Rnd\Controllers\RndController;
use Illuminate\Support\Facades\Route;

Route::prefix('rnd')
    ->name('rnd.')
    ->group(function () {
        Route::get('/', [RndController::class, 'index'])->name('index');
    });
