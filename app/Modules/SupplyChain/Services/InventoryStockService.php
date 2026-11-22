<?php

namespace App\Modules\SupplyChain\Services;

use App\Core\Models\Location;
use App\Models\User;
use App\Modules\SupplyChain\Models\ScmInventoryStock;
use App\Modules\SupplyChain\Models\ScmStockMovement;
use Illuminate\Support\Facades\DB;

class InventoryStockService
{
    public function __construct(
        protected ScmNumberingService $numberingService
    ) {}

    /**
     * Add stock into an inventory location (e.g. from Goods Receipt or Manual Adjustment).
     */
    public function addStock(
        int $productId,
        int $plantId,
        int $locationId,
        float $quantity,
        ?string $batchNumber = null,
        ?string $expiryDate = null,
        float $unitCost = 0,
        string $status = 'available',
        string $referenceType = 'goods_receipt',
        ?int $referenceId = null,
        ?string $notes = null,
        ?int $userId = null
    ): ScmInventoryStock {
        return DB::transaction(function () use (
            $productId, $plantId, $locationId, $quantity, $batchNumber,
            $expiryDate, $unitCost, $status, $referenceType, $referenceId, $notes, $userId
        ) {
            $stock = ScmInventoryStock::firstOrCreate(
                [
                    'product_id' => $productId,
                    'plant_id' => $plantId,
                    'location_id' => $locationId,
                    'batch_number' => $batchNumber,
                ],
                [
                    'expiry_date' => $expiryDate,
                    'quantity_on_hand' => 0,
                    'quantity_reserved' => 0,
                    'quantity_available' => 0,
                    'status' => $status,
                    'unit_cost' => $unitCost,
                ]
            );

            $stock->quantity_on_hand += $quantity;
            if ($status === 'available') {
                $stock->quantity_available += $quantity;
            }
            if ($unitCost > 0) {
                $stock->unit_cost = $unitCost;
            }
            if ($expiryDate) {
                $stock->expiry_date = $expiryDate;
            }
            $stock->status = $status;
            $stock->save();

            // Record Movement Ledger
            ScmStockMovement::create([
                'movement_number' => $this->numberingService->generateMovement(),
                'product_id' => $productId,
                'from_location_id' => null,
                'to_location_id' => $locationId,
                'reference_type' => $referenceType,
                'reference_id' => $referenceId,
                'quantity' => $quantity,
                'batch_number' => $batchNumber,
                'notes' => $notes ?? "Penerimaan stok ke {$stock->location?->name}",
                'created_by' => $userId ?? (auth()->id() ?? User::first()?->id ?? 1),
            ]);

            return $stock;
        });
    }

    /**
     * Transfer stock between locations (e.g. from Quarantine to Main Warehouse upon QC Pass).
     */
    public function transferStock(
        int $productId,
        int $plantId,
        int $fromLocationId,
        int $toLocationId,
        float $quantity,
        ?string $batchNumber = null,
        string $targetStatus = 'available',
        string $referenceType = 'transfer',
        ?int $referenceId = null,
        ?string $notes = null,
        ?int $userId = null
    ): void {
        DB::transaction(function () use (
            $productId, $plantId, $fromLocationId, $toLocationId,
            $quantity, $batchNumber, $targetStatus, $referenceType, $referenceId, $notes, $userId
        ) {
            $sourceStock = ScmInventoryStock::where('product_id', $productId)
                ->where('location_id', $fromLocationId)
                ->where('batch_number', $batchNumber)
                ->lockForUpdate()
                ->first();

            if ($sourceStock) {
                $sourceStock->quantity_on_hand = max(0, $sourceStock->quantity_on_hand - $quantity);
                $sourceStock->quantity_available = max(0, $sourceStock->quantity_available - $quantity);
                $sourceStock->save();
            }

            $destStock = ScmInventoryStock::firstOrCreate(
                [
                    'product_id' => $productId,
                    'plant_id' => $plantId,
                    'location_id' => $toLocationId,
                    'batch_number' => $batchNumber,
                ],
                [
                    'expiry_date' => $sourceStock?->expiry_date,
                    'quantity_on_hand' => 0,
                    'quantity_reserved' => 0,
                    'quantity_available' => 0,
                    'status' => $targetStatus,
                    'unit_cost' => $sourceStock?->unit_cost ?? 0,
                ]
            );

            $destStock->quantity_on_hand += $quantity;
            if ($targetStatus === 'available') {
                $destStock->quantity_available += $quantity;
            }
            $destStock->status = $targetStatus;
            $destStock->save();

            // Record Movement Ledger
            ScmStockMovement::create([
                'movement_number' => $this->numberingService->generateMovement(),
                'product_id' => $productId,
                'from_location_id' => $fromLocationId,
                'to_location_id' => $toLocationId,
                'reference_type' => $referenceType,
                'reference_id' => $referenceId,
                'quantity' => $quantity,
                'batch_number' => $batchNumber,
                'notes' => $notes ?? 'Pemindahan stok antargudang/status',
                'created_by' => $userId ?? (auth()->id() ?? User::first()?->id ?? 1),
            ]);
        });
    }

    /**
     * Deduct stock for production usage (BSTB) or disposal.
     */
    public function deductStock(
        int $productId,
        int $locationId,
        float $quantity,
        ?string $batchNumber = null,
        string $referenceType = 'material_usage',
        ?int $referenceId = null,
        ?string $notes = null,
        ?int $userId = null,
        ?int $plantId = null
    ): void {
        DB::transaction(function () use (
            $productId, $locationId, $quantity, $batchNumber,
            $referenceType, $referenceId, $notes, $userId, $plantId
        ) {
            $query = ScmInventoryStock::where('product_id', $productId)
                ->where('location_id', $locationId);

            if ($plantId) {
                $query->where('plant_id', $plantId);
            }

            $stock = $query->when($batchNumber, fn ($q) => $q->where('batch_number', $batchNumber))
                ->lockForUpdate()
                ->first();

            if ($stock) {
                $stock->quantity_on_hand = max(0, $stock->quantity_on_hand - $quantity);
                $stock->quantity_available = max(0, $stock->quantity_available - $quantity);
                $stock->save();
            }

            // Record Movement Ledger
            ScmStockMovement::create([
                'movement_number' => $this->numberingService->generateMovement(),
                'product_id' => $productId,
                'from_location_id' => $locationId,
                'to_location_id' => null,
                'reference_type' => $referenceType,
                'reference_id' => $referenceId,
                'quantity' => $quantity,
                'batch_number' => $batchNumber,
                'notes' => $notes ?? 'Pengeluaran bahan baku / BSTB',
                'created_by' => $userId ?? (auth()->id() ?? User::first()?->id ?? 1),
            ]);
        });
    }

    /**
     * Get stock inventory summary across all warehouses.
     */
    public function getInventorySummary(): array
    {
        $stocks = ScmInventoryStock::with(['product', 'plant', 'location'])->get();

        $totalValuation = $stocks->sum(fn ($s) => (float) $s->quantity_on_hand * (float) $s->unit_cost);
        $totalItems = $stocks->sum('quantity_on_hand');
        $quarantineStocks = $stocks->where('status', 'quarantine');
        $availableStocks = $stocks->where('status', 'available');
        $nearExpiryStocks = $stocks->filter(fn ($s) => $s->isNearExpiry(90));

        return [
            'total_valuation' => $totalValuation,
            'total_items' => $totalItems,
            'quarantine_count' => $quarantineStocks->count(),
            'quarantine_qty' => $quarantineStocks->sum('quantity_on_hand'),
            'available_qty' => $availableStocks->sum('quantity_on_hand'),
            'near_expiry_count' => $nearExpiryStocks->count(),
            'stocks' => $stocks,
        ];
    }
}
