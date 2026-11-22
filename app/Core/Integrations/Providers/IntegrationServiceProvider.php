<?php

namespace App\Core\Integrations\Providers;

use App\Core\Integrations\Contracts\FinanceBudgetIntegrationContract;
use App\Core\Integrations\Contracts\HrisIntegrationContract;
use App\Core\Integrations\Contracts\ProcurementIntegrationContract;
use App\Core\Integrations\Contracts\ProductionIntegrationContract;
use App\Core\Integrations\Contracts\RndIntegrationContract;
use App\Core\Integrations\Contracts\SupplyChainIntegrationContract;
use App\Core\Integrations\Contracts\UnifiedApprovalContract;
use App\Core\Integrations\Events\HrisHeadcountSyncedEvent;
use App\Core\Integrations\Events\ProductionBatchCompletedEvent;
use App\Core\Integrations\Events\ProductionDeviationReportCreatedEvent;
use App\Core\Integrations\Events\PurchaseOrderApprovedEvent;
use App\Core\Integrations\Events\QcInspectionCompletedEvent;
use App\Core\Integrations\Listeners\AutoOpenGoodsReceiptFromPoListener;
use App\Core\Integrations\Listeners\SpawnQualityDeviationListener;
use App\Core\Integrations\Listeners\SyncHrisToFinanceBscListener;
use App\Core\Integrations\Listeners\UnlockThreeWayMatchingAndStockListener;
use App\Core\Integrations\Listeners\UpdateFinanceAndHrisFromProductionListener;
use App\Core\Integrations\Listeners\UpdateRndPriceStagingListener;
use App\Core\Integrations\Services\CrossModuleIntegrationService;
use App\Core\Integrations\Services\NotificationCenterService;
use App\Core\Integrations\Services\UnifiedApprovalInboxService;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;

class IntegrationServiceProvider extends ServiceProvider
{
    /**
     * All of the container singletons that should be registered.
     */
    public array $singletons = [
        NotificationCenterService::class => NotificationCenterService::class,
        UnifiedApprovalContract::class => UnifiedApprovalInboxService::class,
        UnifiedApprovalInboxService::class => UnifiedApprovalInboxService::class,
    ];

    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(CrossModuleIntegrationService::class, CrossModuleIntegrationService::class);

        // Bind all Cross-Module Service Contracts
        $this->app->bind(RndIntegrationContract::class, CrossModuleIntegrationService::class);
        $this->app->bind(ProcurementIntegrationContract::class, CrossModuleIntegrationService::class);
        $this->app->bind(FinanceBudgetIntegrationContract::class, CrossModuleIntegrationService::class);
        $this->app->bind(SupplyChainIntegrationContract::class, CrossModuleIntegrationService::class);
        $this->app->bind(ProductionIntegrationContract::class, CrossModuleIntegrationService::class);
        $this->app->bind(HrisIntegrationContract::class, CrossModuleIntegrationService::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Register Event & Listeners for Event-Driven Cross-Module Sync
        Event::listen(PurchaseOrderApprovedEvent::class, UpdateRndPriceStagingListener::class);
        Event::listen(PurchaseOrderApprovedEvent::class, AutoOpenGoodsReceiptFromPoListener::class);
        Event::listen(QcInspectionCompletedEvent::class, UnlockThreeWayMatchingAndStockListener::class);
        Event::listen(ProductionDeviationReportCreatedEvent::class, SpawnQualityDeviationListener::class);
        Event::listen(ProductionBatchCompletedEvent::class, UpdateFinanceAndHrisFromProductionListener::class);
        Event::listen(HrisHeadcountSyncedEvent::class, SyncHrisToFinanceBscListener::class);
    }
}
