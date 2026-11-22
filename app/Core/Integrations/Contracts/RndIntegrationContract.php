<?php

namespace App\Core\Integrations\Contracts;

use App\Core\Models\Bom;
use App\Models\User;
use App\Modules\Rnd\Models\Formula;
use App\Modules\Rnd\Models\RndPriceStaging;

interface RndIntegrationContract
{
    /**
     * Promote an approved R&D Formula into Master BOM in Core Kernel.
     */
    public function promoteFormulaToMasterBom(int $formulaId, ?User $user = null): Bom;

    /**
     * Record a new raw material price from Procurement PO into R&D price staging.
     */
    public function stageRawMaterialPrice(
        int $productId,
        float $newPrice,
        ?string $poNumber = null,
        ?int $vendorId = null,
        ?User $user = null
    ): RndPriceStaging;

    /**
     * Recalculate HPP for an R&D formula using current staged prices.
     *
     * @return array<string, mixed>
     */
    public function recalculateFormulaHpp(Formula $formula): array;
}
