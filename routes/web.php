<?php

use App\Core\Controllers\ModuleFeatureController;
use App\Core\Integrations\Controllers\NotificationApiController;
use App\Core\Models\Department;
use App\Core\Models\Plant;
use App\Core\Models\Product;
use App\Core\Services\ModuleManagerService;
use App\Http\Controllers\DeveloperModeController;
use App\Http\Controllers\DynamicEntityController;
use App\Http\Controllers\SystemSettingsController;
use App\Http\Controllers\UserAccessController;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

// Home / App Launcher
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

// Unified Notification Center API
Route::prefix('notifications')->name('notifications.')->group(function () {
    Route::get('/', [NotificationApiController::class, 'index'])->name('index');
    Route::post('/{id}/read', [NotificationApiController::class, 'markAsRead'])->name('read');
    Route::post('/mark-all-read', [NotificationApiController::class, 'markAllAsRead'])->name('mark-all-read');
});

// Developer Mode & No-Code Platform Engine (/developer-mode)
Route::prefix('developer-mode')->name('developer-mode.')->group(function () {
    Route::get('/', [DeveloperModeController::class, 'index'])->name('index');
    Route::post('/modules', [DeveloperModeController::class, 'storeModule'])->name('modules.store');
    Route::post('/entities', [DeveloperModeController::class, 'storeEntity'])->name('entities.store');
    Route::post('/entities/{entityId}/fields', [DeveloperModeController::class, 'storeField'])->name('fields.store');
    Route::delete('/fields/{fieldId}', [DeveloperModeController::class, 'deleteField'])->name('fields.destroy');
    Route::post('/modules/{moduleId}/workflows', [DeveloperModeController::class, 'storeWorkflow'])->name('workflows.store');
    Route::post('/modules/{moduleId}/widgets', [DeveloperModeController::class, 'storeWidgets'])->name('widgets.store');
});

// Settings & System Customization
Route::prefix('settings')->name('settings.')->group(function () {
    // 1. General & Branding, Numbering, Theme, API Integrations
    Route::get('/', [SystemSettingsController::class, 'index'])->name('index');
    Route::post('/general', [SystemSettingsController::class, 'updateGeneral'])->name('general.update');
    Route::post('/theme', [SystemSettingsController::class, 'updateTheme'])->name('theme.update');
    Route::post('/numbering', [SystemSettingsController::class, 'updateNumbering'])->name('numbering.update');
    Route::post('/numbering/preview', [SystemSettingsController::class, 'previewNumbering'])->name('numbering.preview');
    Route::post('/api-tokens', [SystemSettingsController::class, 'createApiToken'])->name('api-tokens.create');
    Route::delete('/api-tokens/{id}', [SystemSettingsController::class, 'revokeApiToken'])->name('api-tokens.revoke');

    // 2. User Management & Permission Checklist
    Route::prefix('users')->name('users.')->group(function () {
        Route::get('/', [UserAccessController::class, 'index'])->name('index');
        Route::post('/', [UserAccessController::class, 'store'])->name('store');
        Route::put('/{id}', [UserAccessController::class, 'update'])->name('update');
        Route::post('/{id}/toggle-status', [UserAccessController::class, 'toggleStatus'])->name('toggle-status');
        Route::get('/{id}/permissions', [UserAccessController::class, 'getPermissions'])->name('permissions');
        Route::post('/{id}/permissions', [UserAccessController::class, 'updatePermissions'])->name('permissions.update');
        Route::post('/{id}/preset', [UserAccessController::class, 'applyPreset'])->name('preset.apply');
    });
});

// Dynamic Entities & Custom Modules Runtime CRUD
Route::prefix('modules/{moduleSlug}')->name('modules.')->group(function () {
    Route::get('/', [DynamicEntityController::class, 'overview'])->name('custom.overview');
    Route::get('/entities/{entitySlug}', [DynamicEntityController::class, 'index'])->name('entities.index');
    Route::get('/entities/{entitySlug}/create', [DynamicEntityController::class, 'create'])->name('entities.create');
    Route::post('/entities/{entitySlug}', [DynamicEntityController::class, 'store'])->name('entities.store');
    Route::get('/entities/{entitySlug}/{recordId}/edit', [DynamicEntityController::class, 'edit'])->name('entities.edit');
    Route::put('/entities/{entitySlug}/{recordId}', [DynamicEntityController::class, 'update'])->name('entities.update');
    Route::delete('/entities/{entitySlug}/{recordId}', [DynamicEntityController::class, 'destroy'])->name('entities.destroy');
    Route::get('/entities/{entitySlug}/export/pdf', [DynamicEntityController::class, 'exportPdf'])->name('entities.export.pdf');
    Route::get('/entities/{entitySlug}/export/excel', [DynamicEntityController::class, 'exportExcel'])->name('entities.export.excel');
});

// Universal Feature Records Dynamic CRUD
Route::prefix('modules-feature')->name('modules-feature.')->group(function () {
    Route::post('/records', [ModuleFeatureController::class, 'store'])->name('records.store');
    Route::put('/records/{id}', [ModuleFeatureController::class, 'update'])->name('records.update');
    Route::delete('/records/{id}', [ModuleFeatureController::class, 'destroy'])->name('records.destroy');
    Route::get('/records/export', [ModuleFeatureController::class, 'export'])->name('records.export');
});
