<?php

namespace App\Http\Middleware;

use App\Core\Services\ModuleManagerService;
use App\Core\Services\SystemSettingService;
use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that's loaded on the first page visit.
     *
     * @see https://inertiajs.com/server-side-setup#root-template
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determines the current asset version.
     *
     * @see https://inertiajs.com/asset-versioning
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @see https://inertiajs.com/shared-data
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        /** @var ModuleManagerService $moduleManager */
        $moduleManager = app(ModuleManagerService::class);
        /** @var SystemSettingService $settingService */
        $settingService = app(SystemSettingService::class);
        $publicSettings = $settingService->getPublicSettings();
        $user = $request->user();

        return [
            ...parent::share($request),
            'appName' => $publicSettings['app_name'] ?? config('app.name', 'HerbaTech ERP'),
            'systemSettings' => $publicSettings,
            'auth' => [
                'user' => $user ? [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'is_superadmin' => $user->isSuperAdmin(),
                    'department' => $user->department?->only('id', 'code', 'name'),
                    'roles' => $user->getRoleNames(),
                    'permissions' => $user->getAllPermissions()->pluck('name'),
                    'signature_path' => $user->signature_path,
                ] : null,
            ],
            'erp' => [
                'modules' => $user ? $moduleManager->getModulesForUser($user) : $moduleManager->getActiveModules(),
            ],
            'flash' => [
                'success' => fn () => $request->session()->get('success'),
                'error' => fn () => $request->session()->get('error'),
                'warning' => fn () => $request->session()->get('warning'),
                'info' => fn () => $request->session()->get('info'),
            ],
        ];
    }
}
