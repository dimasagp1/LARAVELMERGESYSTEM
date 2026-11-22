<?php

namespace App\Modules\SupplyChain\Services;

use App\Core\Integrations\Events\QcInspectionCompletedEvent;
use App\Core\Models\Location;
use App\Models\User;
use App\Modules\SupplyChain\Models\ScmGoodsReceiptItem;
use App\Modules\SupplyChain\Models\ScmMaterialReturn;
use App\Modules\SupplyChain\Models\ScmMaterialReturnItem;
use App\Modules\SupplyChain\Models\ScmQcInspection;
use Illuminate\Support\Facades\DB;

class QcIncomingGatekeeperService
{
    public function __construct(
        protected ScmNumberingService $numberingService,
        protected InventoryStockService $stockService
    ) {}

    /**
     * Perform the 4-parameter CPOTB / cGMP incoming material inspection.
     */
    public function inspect(int $inspectionId, array $data, ?int $inspectorId = null): ScmQcInspection
    {
        return DB::transaction(function () use ($inspectionId, $data, $inspectorId) {
            $user = $inspectorId ?? (auth()->id() ?? User::first()?->id ?? 1);
            $inspection = ScmQcInspection::with('goodsReceiptItem.goodsReceipt')->findOrFail($inspectionId);
            $item = $inspection->goodsReceiptItem;
            $receipt = $item->goodsReceipt;

            $isVisual = (bool) ($data['is_visual_passed'] ?? false);
            $isLab = (bool) ($data['is_lab_passed'] ?? false);
            $isQty = (bool) ($data['is_quantity_passed'] ?? false);
            $isDocs = (bool) ($data['is_docs_passed'] ?? false);

            $allPassed = $isVisual && $isLab && $isQty && $isDocs;

            $passedQty = isset($data['passed_qty'])
                ? (float) $data['passed_qty']
                : ($allPassed ? (float) $item->received_qty : 0.0);

            $rejectedQty = isset($data['rejected_qty'])
                ? (float) $data['rejected_qty']
                : ($allPassed ? 0.0 : (float) $item->received_qty);

            // Determine status
            if ($passedQty > 0 && $rejectedQty > 0) {
                $status = 'partial';
            } elseif ($passedQty > 0 && $rejectedQty == 0) {
                $status = 'passed';
            } else {
                $status = 'rejected';
            }

            // Update inspection
            $inspection->update([
                'is_visual_passed' => $isVisual,
                'is_lab_passed' => $isLab,
                'is_quantity_passed' => $isQty,
                'is_docs_passed' => $isDocs,
                'sample_qty' => (float) ($data['sample_qty'] ?? $inspection->sample_qty),
                'passed_qty' => $passedQty,
                'rejected_qty' => $rejectedQty,
                'status' => $status,
                'notes' => $data['notes'] ?? $inspection->notes,
                'coa_attachment_path' => $data['coa_attachment_path'] ?? $inspection->coa_attachment_path,
                'inspector_id' => $user,
                'inspection_date' => $data['inspection_date'] ?? now()->toDateString(),
            ]);

            // Update Goods Receipt Item
            $item->update([
                'passed_qty' => $passedQty,
                'rejected_qty' => $rejectedQty,
                'status' => $status,
            ]);

            // Locate plant and warehouses
            $plantId = $receipt->plant_id;

            $quarantineLoc = Location::where('plant_id', $plantId)
                ->where(function ($q) {
                    $q->where('code', 'WH-QRN')
                        ->orWhere('name', 'like', '%Karantina%')
                        ->orWhere('name', 'like', '%Quarantine%');
                })
                ->first() ?? Location::where('plant_id', $plantId)->first();

            $activeWarehouseLoc = Location::where('plant_id', $plantId)
                ->where(function ($q) {
                    $q->where('code', 'WH-RAW')
                        ->orWhere('code', 'WH-PM')
                        ->orWhere('name', 'like', '%Bahan Baku%')
                        ->orWhere('name', 'like', '%Raw%');
                })
                ->first() ?? Location::where('plant_id', $plantId)->first();

            $quarantineLocId = $quarantineLoc?->id ?? 1;
            $activeLocId = $activeWarehouseLoc?->id ?? $quarantineLocId;

            // Transfer passed stock from Quarantine to Active Stock
            if ($passedQty > 0) {
                $this->stockService->transferStock(
                    productId: $item->product_id,
                    plantId: $plantId,
                    fromLocationId: $quarantineLocId,
                    toLocationId: $activeLocId,
                    quantity: $passedQty,
                    batchNumber: $item->batch_number,
                    targetStatus: 'available',
                    referenceType: 'transfer',
                    referenceId: $inspection->id,
                    notes: "Pelulusan Mutu QC Incoming: {$inspection->inspection_number} -> {$receipt->grn_number}",
                    userId: $user
                );
            }

            // If any quantity is rejected, generate Vendor Material Return Memo
            if ($rejectedQty > 0) {
                // Update quarantine stock status to rejected
                $this->stockService->transferStock(
                    productId: $item->product_id,
                    plantId: $plantId,
                    fromLocationId: $quarantineLocId,
                    toLocationId: $quarantineLocId,
                    quantity: $rejectedQty,
                    batchNumber: $item->batch_number,
                    targetStatus: 'rejected',
                    referenceType: 'material_return',
                    referenceId: $inspection->id,
                    notes: "Penolakan Mutu QC: {$inspection->inspection_number} - Material Reject",
                    userId: $user
                );

                // Create or append to a draft Material Return
                $returnMemo = ScmMaterialReturn::where('goods_receipt_id', $receipt->id)
                    ->whereIn('status', ['draft', 'approved'])
                    ->first();

                if (! $returnMemo) {
                    $returnMemo = ScmMaterialReturn::create([
                        'return_number' => $this->numberingService->generateReturn(),
                        'goods_receipt_id' => $receipt->id,
                        'vendor_id' => $receipt->vendor_id,
                        'return_date' => now()->toDateString(),
                        'reason' => "Penolakan QC Incoming ({$inspection->inspection_number}): ".($data['notes'] ?? 'Gagal spesifikasi mutu'),
                        'total_amount' => 0,
                        'status' => 'approved',
                        'created_by' => $user,
                        'approved_by' => $user,
                        'approved_at' => now(),
                    ]);
                }

                $subtotal = $rejectedQty * (float) $item->unit_price;
                ScmMaterialReturnItem::create([
                    'material_return_id' => $returnMemo->id,
                    'product_id' => $item->product_id,
                    'batch_number' => $item->batch_number,
                    'quantity' => $rejectedQty,
                    'uom' => $item->uom,
                    'unit_price' => $item->unit_price,
                    'subtotal' => $subtotal,
                    'reason' => $data['notes'] ?? 'Gagal uji 4 parameter mutu CPOTB',
                ]);

                $returnMemo->increment('total_amount', $subtotal);
            }

            // Check if all items in this Goods Receipt have finished QC
            $pendingItems = ScmGoodsReceiptItem::where('goods_receipt_id', $receipt->id)
                ->where('status', 'quarantine')
                ->count();

            if ($pendingItems === 0) {
                $receipt->update(['status' => 'completed']);
            } else {
                $receipt->update(['status' => 'inspected']);
            }

            // Dispatch Cross-Module Event for 3-Way Matching AP & Notifications
            $isPassed = $inspection->status === 'passed' || $passedQty > 0;
            $userModel = User::find($user) ?? User::first();
            event(new QcInspectionCompletedEvent(
                inspection: $inspection,
                user: $userModel,
                isPassed: $isPassed,
                passedQty: $passedQty,
                rejectedQty: $rejectedQty
            ));

            return $inspection->fresh(['goodsReceiptItem.product', 'goodsReceiptItem.goodsReceipt.vendor']);
        });
    }
}
