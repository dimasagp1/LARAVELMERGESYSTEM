<?php

namespace App\Core\Integrations\Contracts;

use App\Models\User;
use App\Modules\Procurement\Models\PrItem;
use App\Modules\Procurement\Models\PurchaseRequest;

interface ProcurementIntegrationContract
{
    /**
     * Convert MRP net requirement recommendations into a Draft PR in Procurement.
     *
     * @param  array<string, mixed>  $mrpData
     * @param  list<array<string, mixed>>  $itemsData
     */
    public function createDraftPrFromMrp(array $mrpData, array $itemsData, ?User $user = null): PurchaseRequest;

    /**
     * Handle an approved PO / ordered PR item event.
     */
    public function handlePoApproved(PrItem $item, User $user): void;
}
