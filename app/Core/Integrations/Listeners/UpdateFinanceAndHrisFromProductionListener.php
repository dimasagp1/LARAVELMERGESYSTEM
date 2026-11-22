<?php

namespace App\Core\Integrations\Listeners;

use App\Core\Integrations\Contracts\ProductionIntegrationContract;
use App\Core\Integrations\Events\ProductionBatchCompletedEvent;

class UpdateFinanceAndHrisFromProductionListener
{
    public function __construct(
        protected ProductionIntegrationContract $productionIntegration
    ) {}

    public function handle(ProductionBatchCompletedEvent $event): void
    {
        $this->productionIntegration->syncProductionBatchToFinanceAndHris(
            laporanHarian: $event->laporanHarian,
            user: $event->user
        );
    }
}
