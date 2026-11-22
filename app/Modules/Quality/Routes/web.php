<?php

use App\Modules\Quality\Controllers\QualityController;
use Illuminate\Support\Facades\Route;

Route::prefix('quality')
    ->name('quality.')
    ->group(function () {
        Route::get('/', [QualityController::class, 'index'])->name('index');
    });
