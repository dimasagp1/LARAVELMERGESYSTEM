<?php

namespace App\Modules\SupplyChain\Services;

use App\Core\Models\Location;
use App\Core\Models\Product;
use App\Models\User;
use App\Modules\SupplyChain\Models\ScmGoodsReceipt;
use App\Modules\SupplyChain\Models\ScmGoodsReceiptItem;
use App\Modules\SupplyChain\Models\ScmQcInspection;
use Illuminate\Support\Facades\DB;

class InboundGoodsReceiptService
{
    public function __construct(
        protected ScmNumberingService $numberingService,
        protected InventoryStockService $stockService
    ) {}

    /**
     * Create an inbound goods receipt, record quarantine stock, and create pending QC inspections.
     */
    public function createGoodsReceipt(array $data, array $items, ?int $userId = null): ScmGoodsReceipt
    {
        return DB::transaction(function () use ($data, $items, $userId) {
            $user = $userId ?? (auth()->id() ?? User::first()?->id ?? 1);
            $grnNumber = $this->numberingService->generateGoodsReceipt();

            $receipt = ScmGoodsReceipt::create([
                'grn_number' => $grnNumber,
                'po_number' => $data['po_number'] ?? null,
                'vendor_id' => $data['vendor_id'],
                'plant_id' => $data['plant_id'],
                'delivery_order_number' => $data['delivery_order_number'] ?? 'SJ-'.time(),
                'receipt_date' => $data['receipt_date'] ?? now()->toDateString(),
                'received_by' => $user,
                'status' => 'quarantine',
                'notes' => $data['notes'] ?? null,
                'attachment_path' => $data['attachment_path'] ?? null,
                'custom_attributes' => $data['custom_attributes'] ?? null,
            ]);

            // Locate quarantine warehouse
            $quarantineLocation = Location::where('plant_id', $data['plant_id'])
                ->where(function ($q) {
                    $q->where('code', 'WH-QRN')
                        ->orWhere('name', 'like', '%Karantina%')
                        ->orWhere('name', 'like', '%Quarantine%');
                })
                ->first()
                ?? Location::where('plant_id', $data['plant_id'])->first();

            $quarantineLocationId = $quarantineLocation?->id ?? 1;

            foreach ($items as $itemData) {
                $product = Product::find($itemData['product_id']);
                $receivedQty = (float) $itemData['received_qty'];
                $batchNumber = $itemData['batch_number'] ?? ('BATCH-'.strtoupper(substr(uniqid(), -6)));
                $expiryDate = $itemData['expiry_date'] ?? now()->addYears(2)->toDateString();
                $unitPrice = (float) ($itemData['unit_price'] ?? ($product?->standard_cost ?? 0));
                $uom = $itemData['uom'] ?? ($product?->uom?->code ?? 'PCS');

                $receiptItem = ScmGoodsReceiptItem::create([
                    'goods_receipt_id' => $receipt->id,
                    'product_id' => $itemData['product_id'],
                    'item_name' => $itemData['item_name'] ?? ($product?->name ?? 'Material Item'),
                    'received_qty' => $receivedQty,
                    'passed_qty' => 0,
                    'rejected_qty' => 0,
                    'uom' => $uom,
                    'batch_number' => $batchNumber,
                    'expiry_date' => $expiryDate,
                    'unit_price' => $unitPrice,
                    'status' => 'quarantine',
                    'notes' => $itemData['notes'] ?? null,
                ]);

                // Register into physical quarantine stock
                $this->stockService->addStock(
                    productId: (int) $itemData['product_id'],
                    plantId: (int) $data['plant_id'],
                    locationId: $quarantineLocationId,
                    quantity: $receivedQty,
                    batchNumber: $batchNumber,
                    expiryDate: $expiryDate,
                    unitCost: $unitPrice,
                    status: 'quarantine',
                    referenceType: 'goods_receipt',
                    referenceId: $receipt->id,
                    notes: "Inbound Goods Receipt {$receipt->grn_number} - Karantina QC",
                    userId: $user
                );

                // Create pending QC inspection gatekeeper
                $sampleQty = max(1.0, round($receivedQty * 0.05, 2));
                ScmQcInspection::create([
                    'inspection_number' => $this->numberingService->generateInspection(),
                    'goods_receipt_item_id' => $receiptItem->id,
                    'product_id' => (int) $itemData['product_id'],
                    'batch_number' => $batchNumber,
                    'sample_qty' => $sampleQty,
                    'passed_qty' => 0,
                    'rejected_qty' => 0,
                    'inspector_id' => $user,
                    'inspection_date' => now()->toDateString(),
                    'is_visual_passed' => false,
                    'is_lab_passed' => false,
                    'is_quantity_passed' => false,
                    'is_docs_passed' => false,
                    'status' => 'pending',
                    'notes' => 'Menunggu verifikasi 4 parameter CPOTB',
                ]);
            }

            return $receipt->load(['items.product', 'vendor', 'plant', 'receiver']);
        });
    }
}
