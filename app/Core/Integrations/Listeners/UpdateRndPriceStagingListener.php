<?php

namespace App\Core\Integrations\Listeners;

use App\Core\Integrations\Contracts\RndIntegrationContract;
use App\Core\Integrations\Events\PurchaseOrderApprovedEvent;
use Illuminate\Support\Facades\Log;

class UpdateRndPriceStagingListener
{
    public function __construct(
        protected RndIntegrationContract $rndIntegration
    ) {}

    public function handle(PurchaseOrderApprovedEvent $event): void
    {
        $item = $event->item;

        if ($item->product_id && $item->actual_price > 0 && $item->quantity > 0) {
            try {
                $unitPrice = (float) $item->actual_price / (float) $item->quantity;
                $this->rndIntegration->stageRawMaterialPrice(
                    productId: $item->product_id,
                    newPrice: $unitPrice,
                    poNumber: $item->po_number,
                    vendorId: $item->vendor_id,
                    user: $event->user
                );
            } catch (\Throwable $e) {
                Log::warning("UpdateRndPriceStagingListener error: {$e->getMessage()}", [
                    'product_id' => $item->product_id,
                    'po_number' => $item->po_number,
                ]);
            }
        }
    }
}
