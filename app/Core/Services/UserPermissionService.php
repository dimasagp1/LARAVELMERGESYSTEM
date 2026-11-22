<?php

namespace App\Core\Services;

use App\Core\Models\ErpModule;
use App\Core\Models\UserAccessPermission;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class UserPermissionService
{
    public function __construct(
        protected ModuleManagerService $moduleManager
    ) {}

    /**
     * Get permission checklist matrix for a given user.
     *
     * @return array<string, mixed>
     */
    public function getUserPermissionsMatrix(User $user): array
    {
        $allModules = $this->moduleManager->getActiveModules();
        $userPerms = $user->accessPermissions()->get();

        $modulePermMap = $userPerms->whereNull('menu_route')->keyBy('module_slug');
        $menuPermMap = $userPerms->whereNotNull('menu_route')->groupBy('module_slug');

        $hasAnyRestrictions = $userPerms->isNotEmpty();
        $isSuperAdmin = $user->isSuperAdmin();

        $matrix = [];

        foreach ($allModules as $mod) {
            $slug = $mod->slug;
            $modPerm = $modulePermMap->get($slug);

            // Default allowed if superadmin or no restrictions set yet
            $isModuleAllowed = $isSuperAdmin || ($hasAnyRestrictions ? ($modPerm?->can_view ?? false) : true);

            $menus = $this->moduleManager->getModuleMenus($slug, $mod->schema_config_json);
            $menuItems = [];
            $modMenuPerms = $menuPermMap->get($slug) ? $menuPermMap->get($slug)->keyBy('menu_route') : collect();

            foreach ($menus as $m) {
                $route = $m['route'];
                $menuPerm = $modMenuPerms->get($route);

                $isMenuAllowed = $isSuperAdmin || ($hasAnyRestrictions
                    ? ($menuPerm ? $menuPerm->can_view : $isModuleAllowed)
                    : true);

                $menuItems[] = [
                    'name' => $m['name'],
                    'route' => $route,
                    'icon' => $m['icon'] ?? 'Folder',
                    'category' => $m['category'] ?? 'Umum',
                    'description' => $m['description'] ?? '',
                    'is_allowed' => (bool) $isMenuAllowed,
                    'can_create' => $menuPerm ? (bool) $menuPerm->can_create : true,
                    'can_edit' => $menuPerm ? (bool) $menuPerm->can_edit : true,
                    'can_delete' => $menuPerm ? (bool) $menuPerm->can_delete : false,
                    'can_approve' => $menuPerm ? (bool) $menuPerm->can_approve : false,
                ];
            }

            $matrix[] = [
                'id' => $mod->id,
                'slug' => $slug,
                'name' => $mod->name,
                'icon' => $mod->icon ?? 'Boxes',
                'color_theme' => $mod->color_theme ?? 'emerald',
                'is_allowed' => (bool) $isModuleAllowed,
                'can_create' => $modPerm ? (bool) $modPerm->can_create : true,
                'can_edit' => $modPerm ? (bool) $modPerm->can_edit : true,
                'can_delete' => $modPerm ? (bool) $modPerm->can_delete : false,
                'can_approve' => $modPerm ? (bool) $modPerm->can_approve : false,
                'menus' => $menuItems,
                'allowed_menu_count' => count(array_filter($menuItems, fn ($m) => $m['is_allowed'])),
                'total_menu_count' => count($menuItems),
            ];
        }

        return [
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'department' => $user->department?->name ?? 'General',
                'roles' => $user->getRoleNames(),
                'is_superadmin' => $isSuperAdmin,
            ],
            'modules' => $matrix,
        ];
    }

    /**
     * Save the permission checklist for a user.
     *
     * @param  array<string, mixed>  $payload  ['modules' => [...], 'menus' => [...]]
     */
    public function syncUserPermissions(User $user, array $payload): void
    {
        DB::transaction(function () use ($user, $payload) {
            // Remove existing permissions
            UserAccessPermission::where('user_id', $user->id)->delete();

            // 1. Process module level permissions
            $modules = $payload['modules'] ?? [];
            foreach ($modules as $slug => $data) {
                $isAllowed = is_array($data) ? (bool) ($data['is_allowed'] ?? false) : (bool) $data;

                UserAccessPermission::create([
                    'user_id' => $user->id,
                    'module_slug' => $slug,
                    'menu_route' => null,
                    'can_view' => $isAllowed,
                    'can_create' => is_array($data) ? (bool) ($data['can_create'] ?? true) : true,
                    'can_edit' => is_array($data) ? (bool) ($data['can_edit'] ?? true) : true,
                    'can_delete' => is_array($data) ? (bool) ($data['can_delete'] ?? false) : false,
                    'can_approve' => is_array($data) ? (bool) ($data['can_approve'] ?? false) : false,
                ]);
            }

            // 2. Process menu level permissions
            $menus = $payload['menus'] ?? [];
            foreach ($menus as $slug => $routeList) {
                if (! is_array($routeList)) {
                    continue;
                }

                foreach ($routeList as $route => $menuData) {
                    $isAllowed = is_array($menuData) ? (bool) ($menuData['is_allowed'] ?? false) : (bool) $menuData;

                    UserAccessPermission::updateOrCreate(
                        [
                            'user_id' => $user->id,
                            'module_slug' => $slug,
                            'menu_route' => $route,
                        ],
                        [
                            'can_view' => $isAllowed,
                            'can_create' => is_array($menuData) ? (bool) ($menuData['can_create'] ?? true) : true,
                            'can_edit' => is_array($menuData) ? (bool) ($menuData['can_edit'] ?? true) : true,
                            'can_delete' => is_array($menuData) ? (bool) ($menuData['can_delete'] ?? false) : false,
                            'can_approve' => is_array($menuData) ? (bool) ($menuData['can_approve'] ?? false) : false,
                        ]
                    );
                }
            }
        });
    }

    /**
     * Apply predefined role preset permissions to a user.
     */
    public function applyPreset(User $user, string $presetName): void
    {
        $allModules = $this->moduleManager->getActiveModules();
        $allowedModules = [];
        $allowedMenus = [];

        switch ($presetName) {
            case 'superadmin':
            case 'all':
                foreach ($allModules as $mod) {
                    $allowedModules[$mod->slug] = ['is_allowed' => true, 'can_create' => true, 'can_edit' => true, 'can_delete' => true, 'can_approve' => true];
                    $menus = $this->moduleManager->getModuleMenus($mod->slug, $mod->schema_config_json);
                    foreach ($menus as $m) {
                        $allowedMenus[$mod->slug][$m['route']] = ['is_allowed' => true, 'can_create' => true, 'can_edit' => true, 'can_delete' => true, 'can_approve' => true];
                    }
                }
                break;

            case 'operator':
                $operatorSlugs = ['production', 'produksi', 'supply-chain', 'hris'];
                foreach ($allModules as $mod) {
                    $isAllowed = in_array($mod->slug, $operatorSlugs);
                    $allowedModules[$mod->slug] = ['is_allowed' => $isAllowed];
                    $menus = $this->moduleManager->getModuleMenus($mod->slug, $mod->schema_config_json);
                    foreach ($menus as $m) {
                        // Operator can access daily report, timer, stock, attendance
                        $menuAllowed = $isAllowed && ! str_contains($m['route'], 'payroll') && ! str_contains($m['route'], 'matching');
                        $allowedMenus[$mod->slug][$m['route']] = ['is_allowed' => $menuAllowed];
                    }
                }
                break;

            case 'finance':
                $finSlugs = ['finance', 'procurement', 'hris'];
                foreach ($allModules as $mod) {
                    $isAllowed = in_array($mod->slug, $finSlugs);
                    $allowedModules[$mod->slug] = ['is_allowed' => $isAllowed];
                    $menus = $this->moduleManager->getModuleMenus($mod->slug, $mod->schema_config_json);
                    foreach ($menus as $m) {
                        $allowedMenus[$mod->slug][$m['route']] = ['is_allowed' => $isAllowed];
                    }
                }
                break;

            case 'qc_qa':
                $qcSlugs = ['quality', 'supply-chain', 'production', 'produksi'];
                foreach ($allModules as $mod) {
                    $isAllowed = in_array($mod->slug, $qcSlugs);
                    $allowedModules[$mod->slug] = ['is_allowed' => $isAllowed];
                    $menus = $this->moduleManager->getModuleMenus($mod->slug, $mod->schema_config_json);
                    foreach ($menus as $m) {
                        $allowedMenus[$mod->slug][$m['route']] = ['is_allowed' => $isAllowed];
                    }
                }
                break;

            case 'clear':
            default:
                foreach ($allModules as $mod) {
                    $allowedModules[$mod->slug] = ['is_allowed' => false];
                    $menus = $this->moduleManager->getModuleMenus($mod->slug, $mod->schema_config_json);
                    foreach ($menus as $m) {
                        $allowedMenus[$mod->slug][$m['route']] = ['is_allowed' => false];
                    }
                }
                break;
        }

        $this->syncUserPermissions($user, [
            'modules' => $allowedModules,
            'menus' => $allowedMenus,
        ]);
    }

    /**
     * Filter modules for display to user according to checklist permissions.
     *
     * @param  Collection<int, ErpModule>  $modules
     * @return Collection<int, ErpModule>
     */
    public function filterAccessibleModules(?User $user, Collection $modules): Collection
    {
        if (! $user || $user->isSuperAdmin()) {
            return $modules;
        }

        $hasRestrictions = $user->accessPermissions()->exists();
        if (! $hasRestrictions) {
            return $modules;
        }

        return $modules->filter(function (ErpModule $module) use ($user) {
            return $user->hasModuleAccess($module->slug);
        })->map(function (ErpModule $module) use ($user) {
            $menus = $module->menus ?? $this->moduleManager->getModuleMenus($module->slug, $module->schema_config_json);
            $module->menus = $this->filterAccessibleMenus($user, $module->slug, $menus);

            return $module;
        })->values();
    }

    /**
     * Filter menus for a module according to user's checklist permissions.
     *
     * @param  list<array<string, mixed>>  $menus
     * @return list<array<string, mixed>>
     */
    public function filterAccessibleMenus(?User $user, string $moduleSlug, array $menus): array
    {
        if (! $user || $user->isSuperAdmin()) {
            return $menus;
        }

        $hasRestrictions = $user->accessPermissions()->exists();
        if (! $hasRestrictions) {
            return $menus;
        }

        return array_values(array_filter($menus, function (array $menu) use ($user, $moduleSlug) {
            return $user->hasMenuAccess($moduleSlug, $menu['route']);
        }));
    }
}
