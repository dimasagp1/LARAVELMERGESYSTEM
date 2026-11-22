<?php

use App\Core\Models\Department;
use App\Core\Models\Plant;
use App\Core\Models\Product;
use App\Core\Services\ModuleManagerService;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function (Request $request, ModuleManagerService $moduleManager) {
    $user = $request->user() ?? User::where('email', 'admin@herbatech.com')->first();
    $modules = $moduleManager->getModulesForUser($user);
    $pendingApprovals = $moduleManager->getPendingApprovalsForUser($user);
    $searchItems = $moduleManager->getGlobalSearchItems();

    return Inertia::render('AppLauncher', [
        'modules' => $modules,
        'pendingApprovals' => $pendingApprovals,
        'searchItems' => $searchItems,
        'stats' => [
            'total_modules' => $modules->count(),
            'active_plants' => Plant::where('is_active', true)->count(),
            'total_departments' => Department::where('is_active', true)->count(),
            'total_products' => Product::count(),
            'total_pending_approvals' => count($pendingApprovals),
        ],
    ]);
})->name('app-launcher');

Route::post('/modules/{id}/toggle-pin', function (int $id, Request $request, ModuleManagerService $moduleManager) {
    $user = $request->user() ?? User::where('email', 'admin@herbatech.com')->first();
    if ($user) {
        $moduleManager->togglePin($user, $id);
    }

    return back()->with('success', 'Status pin modul berhasil diperbarui.');
})->name('modules.toggle-pin');

Route::post('/modules/reorder', function (Request $request, ModuleManagerService $moduleManager) {
    $request->validate([
        'module_ids' => 'required|array',
        'module_ids.*' => 'integer',
    ]);

    $user = $request->user() ?? User::where('email', 'admin@herbatech.com')->first();
    if ($user) {
        $moduleManager->reorderModules($user, $request->input('module_ids'));
    }

    return back()->with('success', 'Urutan modul berhasil disimpan.');
})->name('modules.reorder');

Route::post('/approvals/{id}/action', function (int $id, Request $request) {
    $request->validate([
        'action' => 'required|in:approve,reject,review',
        'comments' => 'nullable|string',
    ]);

    $actionName = match ($request->input('action')) {
        'approve' => 'disetujui',
        'reject' => 'ditolak',
        'review' => 'dikembalikan untuk revisi',
    };

    return back()->with('success', "Item persetujuan #{$id} berhasil {$actionName}.");
})->name('approvals.action');

Route::get('/api/search', function (Request $request, ModuleManagerService $moduleManager) {
    $q = strtolower(trim((string) $request->query('q', '')));
    $items = $moduleManager->getGlobalSearchItems();

    if ($q !== '') {
        $items = array_filter($items, function ($item) use ($q) {
            return str_contains(strtolower($item['title']), $q)
                || str_contains(strtolower($item['subtitle'] ?? ''), $q)
                || str_contains(strtolower($item['category'] ?? ''), $q);
        });
    }

    return response()->json(array_values($items));
})->name('api.search');
