<?php

namespace App\Core\Integrations\Listeners;

use App\Core\Integrations\Contracts\SupplyChainIntegrationContract;
use App\Core\Integrations\Events\PurchaseOrderApprovedEvent;
use Illuminate\Support\Facades\Log;

class AutoOpenGoodsReceiptFromPoListener
{
    public function __construct(
        protected SupplyChainIntegrationContract $scmIntegration
    ) {}

    public function handle(PurchaseOrderApprovedEvent $event): void
    {
        try {
            $this->scmIntegration->openGoodsReceiptFromPo(
                poItem: $event->item,
                user: $event->user
            );
        } catch (\Throwable $e) {
            Log::warning("AutoOpenGoodsReceiptFromPoListener error: {$e->getMessage()}", [
                'item_id' => $event->item->id ?? null,
                'po_number' => $event->item->po_number ?? null,
            ]);
        }
    }
}
