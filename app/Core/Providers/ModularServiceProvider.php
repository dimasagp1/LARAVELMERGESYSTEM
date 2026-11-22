<?php

namespace App\Core\Providers;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

class ModularServiceProvider extends ServiceProvider
{
    /**
     * The list of default business modules.
     *
     * @var list<string>
     */
    protected array $modules = [
        'Finance',
        'GeneralAffair',
        'Hris',
        'Procurement',
        'Production',
        'Produksi',
        'Quality',
        'Rnd',
        'SupplyChain',
    ];

    /**
     * Register any application services.
     */
    public function register(): void
    {
        foreach ($this->modules as $module) {
            $providerClass = "App\\Modules\\{$module}\\Providers\\{$module}ServiceProvider";
            if (class_exists($providerClass)) {
                $this->app->register($providerClass);
            }
        }
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        foreach ($this->modules as $module) {
            $this->registerModuleRoutes($module);
            $this->registerModuleMigrations($module);
        }
    }

    /**
     * Register web & api routes for a module.
     */
    protected function registerModuleRoutes(string $module): void
    {
        $modulePath = app_path("Modules/{$module}");

        $webRoutePath = "{$modulePath}/Routes/web.php";
        if (file_exists($webRoutePath)) {
            Route::middleware('web')
                ->group($webRoutePath);
        }

        $apiRoutePath = "{$modulePath}/Routes/api.php";
        if (file_exists($apiRoutePath)) {
            Route::middleware('api')
                ->prefix('api')
                ->group($apiRoutePath);
        }
    }

    /**
     * Register migrations for a module if directory exists.
     */
    protected function registerModuleMigrations(string $module): void
    {
        $migrationPath = app_path("Modules/{$module}/Migrations");
        if (is_dir($migrationPath)) {
            $this->loadMigrationsFrom($migrationPath);
        }
    }
}
