<?php

namespace App\Core\Integrations\Contracts;

use App\Models\User;
use App\Modules\Procurement\Models\PrItem;
use App\Modules\SupplyChain\Models\ScmGoodsReceipt;
use App\Modules\SupplyChain\Models\ScmQcInspection;

interface SupplyChainIntegrationContract
{
    /**
     * Open or prepare expected Goods Receipt in SCM from an approved Procurement PO item.
     */
    public function openGoodsReceiptFromPo(PrItem $poItem, User $user, ?int $plantId = null): ScmGoodsReceipt;

    /**
     * Unlock 3-Way Matching AP and transfer passed quantity to active stock upon QC pass.
     *
     * @return array<string, mixed>
     */
    public function handleQcInspectionResult(ScmQcInspection $inspection, User $user): array;
}
