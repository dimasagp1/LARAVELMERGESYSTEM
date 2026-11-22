<?php

use App\Modules\GeneralAffair\Controllers\GeneralAffairController;
use Illuminate\Support\Facades\Route;

Route::prefix('general-affair')
    ->name('general-affair.')
    ->group(function () {
        Route::get('/', [GeneralAffairController::class, 'index'])->name('index');
    });
