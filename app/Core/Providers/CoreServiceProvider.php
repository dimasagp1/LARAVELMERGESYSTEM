<?php

namespace App\Core\Providers;

use App\Core\Services\ApprovalEngineService;
use App\Core\Services\ModuleManagerService;
use Illuminate\Support\ServiceProvider;

class CoreServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(ModuleManagerService::class, fn () => new ModuleManagerService);
        $this->app->singleton(ApprovalEngineService::class, fn () => new ApprovalEngineService);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
