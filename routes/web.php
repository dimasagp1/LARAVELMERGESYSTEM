<?php

use App\Core\Models\Department;
use App\Core\Models\Plant;
use App\Core\Models\Product;
use App\Core\Services\ModuleManagerService;
use App\Models\User;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function (ModuleManagerService $moduleManager) {
    $modules = $moduleManager->getActiveModules();

    return Inertia::render('Dashboard', [
        'modules' => $modules,
        'stats' => [
            'total_modules' => $modules->count(),
            'active_plants' => Plant::where('is_active', true)->count(),
            'total_departments' => Department::where('is_active', true)->count(),
            'total_products' => Product::count(),
        ],
    ]);
})->name('dashboard');

Route::post('/modules/{id}/toggle-pin', function (int $id, ModuleManagerService $moduleManager) {
    $user = auth()->user() ?? User::first();
    if ($user) {
        $moduleManager->togglePin($user, $id);
    }

    return back();
})->name('modules.toggle-pin');
