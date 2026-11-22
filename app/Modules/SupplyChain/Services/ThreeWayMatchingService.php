<?php

namespace App\Modules\SupplyChain\Services;

use App\Models\User;
use App\Modules\SupplyChain\Models\ScmGoodsReceipt;
use App\Modules\SupplyChain\Models\ScmThreeWayMatching;
use App\Modules\SupplyChain\Models\ScmVendorBill;
use Illuminate\Support\Facades\DB;

class ThreeWayMatchingService
{
    public function __construct(
        protected ScmNumberingService $numberingService
    ) {}

    /**
     * Execute 3-Way Matching audit between PO, Goods Receipt (QC Passed Qty), and Vendor Invoice.
     */
    public function executeMatching(array $data, ?int $userId = null): ScmThreeWayMatching
    {
        return DB::transaction(function () use ($data, $userId) {
            $user = $userId ?? (auth()->id() ?? User::first()?->id ?? 1);
            $receipt = ScmGoodsReceipt::with(['items', 'vendor'])->findOrFail($data['goods_receipt_id']);

            $poTotalQty = (float) ($data['po_total_qty'] ?? $receipt->items->sum('received_qty'));
            $poUnitPrice = (float) ($data['po_unit_price'] ?? ($receipt->items->first()?->unit_price ?? 0));
            $grPassedQty = (float) ($data['gr_passed_qty'] ?? $receipt->items->sum('passed_qty'));
            $invoiceQty = (float) $data['invoice_qty'];
            $invoiceAmount = (float) $data['invoice_amount'];

            // Expected payable amount = gr_passed_qty * po_unit_price
            $expectedAmount = round($grPassedQty * $poUnitPrice, 2);
            $discrepancyQty = round($invoiceQty - $grPassedQty, 2);
            $discrepancyAmount = round($invoiceAmount - $expectedAmount, 2);

            // Allowed variance threshold (e.g. <= 0.5% or <= Rp 10.000)
            $isMatched = abs($discrepancyQty) <= 0.01 && abs($discrepancyAmount) <= 1000;
            $status = $isMatched ? 'matched' : 'discrepant';

            $matching = ScmThreeWayMatching::create([
                'matching_number' => $this->numberingService->generateMatching(),
                'po_number' => $data['po_number'] ?? ($receipt->po_number ?? 'PO-MANUAL'),
                'goods_receipt_id' => $receipt->id,
                'vendor_id' => $receipt->vendor_id,
                'vendor_invoice_number' => $data['vendor_invoice_number'],
                'po_total_qty' => $poTotalQty,
                'po_unit_price' => $poUnitPrice,
                'gr_passed_qty' => $grPassedQty,
                'invoice_qty' => $invoiceQty,
                'invoice_amount' => $invoiceAmount,
                'discrepancy_qty' => $discrepancyQty,
                'discrepancy_amount' => $discrepancyAmount,
                'status' => $status,
                'notes' => $data['notes'] ?? ($isMatched ? 'Audit 3-Way Matching klop tanpa selisih' : "Selisih qty: {$discrepancyQty}, selisih nominal: Rp ".number_format($discrepancyAmount, 0, ',', '.')),
                'audited_by' => $user,
                'approved_by' => $isMatched ? $user : null,
                'approved_at' => $isMatched ? now() : null,
            ]);

            // If completely matched, auto-generate vendor bill
            if ($isMatched && ($data['auto_create_bill'] ?? true)) {
                $this->createVendorBill($matching->id, $user);
            }

            return $matching->fresh(['goodsReceipt', 'vendor', 'auditor']);
        });
    }

    /**
     * Create an approved Vendor Bill (AP) from a verified 3-Way Matching record.
     */
    public function createVendorBill(int $matchingId, ?int $userId = null): ScmVendorBill
    {
        return DB::transaction(function () use ($matchingId, $userId) {
            $user = $userId ?? (auth()->id() ?? User::first()?->id ?? 1);
            $matching = ScmThreeWayMatching::with('goodsReceipt')->findOrFail($matchingId);

            // Avoid duplicate bills
            $existing = ScmVendorBill::where('three_way_matching_id', $matching->id)->first();
            if ($existing) {
                return $existing;
            }

            $subtotal = round($matching->gr_passed_qty * $matching->po_unit_price, 2);
            $taxAmount = round($subtotal * 0.11, 2); // PPN 11%
            $totalAmount = $subtotal + $taxAmount;

            $bill = ScmVendorBill::create([
                'bill_number' => $this->numberingService->generateBill(),
                'three_way_matching_id' => $matching->id,
                'goods_receipt_id' => $matching->goods_receipt_id,
                'vendor_id' => $matching->vendor_id,
                'invoice_number' => $matching->vendor_invoice_number,
                'bill_date' => now()->toDateString(),
                'due_date' => now()->addDays(30)->toDateString(),
                'subtotal' => $subtotal,
                'tax_amount' => $taxAmount,
                'total_amount' => $totalAmount,
                'status' => 'approved',
                'created_by' => $user,
                'notes' => "Diterbitkan otomatis via 3-Way Matching {$matching->matching_number} (PO: {$matching->po_number})",
            ]);

            $matching->update([
                'status' => 'approved',
                'approved_by' => $user,
                'approved_at' => now(),
            ]);

            return $bill;
        });
    }
}
