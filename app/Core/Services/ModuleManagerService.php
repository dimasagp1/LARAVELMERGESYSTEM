<?php

namespace App\Core\Services;

use App\Core\Models\ErpModule;
use App\Core\Models\UserModulePreference;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Cache;

class ModuleManagerService
{
    /**
     * @return Collection<int, ErpModule>
     */
    public function getActiveModules(): Collection
    {
        return Cache::remember('erp_active_modules', 3600, function (): Collection {
            return ErpModule::query()
                ->where('is_active', true)
                ->orderBy('sort_order')
                ->get();
        });
    }

    /**
     * @return Collection<int, ErpModule>
     */
    public function getModulesForUser(User $user): Collection
    {
        $activeModules = $this->getActiveModules();
        $preferences = UserModulePreference::query()
            ->where('user_id', $user->id)
            ->get()
            ->keyBy('module_id');

        return $activeModules->map(function (ErpModule $module) use ($preferences) {
            $pref = $preferences->get($module->id);
            $module->is_pinned = $pref ? $pref->is_pinned : false;
            $module->custom_order = $pref ? $pref->custom_order : $module->sort_order;

            return $module;
        })->sortBy('custom_order')->values();
    }

    public function togglePin(User $user, int $moduleId): bool
    {
        $preference = UserModulePreference::firstOrNew([
            'user_id' => $user->id,
            'module_id' => $moduleId,
        ]);

        $preference->is_pinned = ! $preference->is_pinned;
        $preference->save();

        return $preference->is_pinned;
    }

    public function updateCustomOrder(User $user, array $moduleOrders): void
    {
        foreach ($moduleOrders as $order => $moduleId) {
            UserModulePreference::updateOrCreate(
                [
                    'user_id' => $user->id,
                    'module_id' => $moduleId,
                ],
                [
                    'custom_order' => $order,
                ]
            );
        }
    }

    public function clearCache(): void
    {
        Cache::forget('erp_active_modules');
    }
}
