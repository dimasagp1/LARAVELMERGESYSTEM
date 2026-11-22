<?php

namespace App\Modules\Procurement\Services;

use App\Core\Integrations\Events\PurchaseOrderApprovedEvent;
use App\Models\User;
use App\Modules\Procurement\Models\PrApproval;
use App\Modules\Procurement\Models\PrItem;
use App\Modules\Procurement\Models\PrItemDelivery;
use App\Modules\Procurement\Models\PrItemDeliveryPlan;
use App\Modules\Procurement\Models\PurchaseRequest;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class PrWorkflowService
{
    public function __construct(
        protected ProcurementNumberingService $numberingService,
        protected BudgetControlService $budgetService
    ) {}

    /**
     * Create PR with Granular Items (either draft or submitted).
     */
    public function createPr(array $prData, array $itemsData, User $user, bool $asDraft = false): PurchaseRequest
    {
        return DB::transaction(function () use ($prData, $itemsData, $user, $asDraft) {
            $departmentId = $prData['department_id'] ?? $user->department_id;
            $requestDate = isset($prData['request_date']) ? Carbon::parse($prData['request_date']) : Carbon::now();

            $prNumber = $this->numberingService->generatePrNumber((int) $departmentId, $requestDate);

            $pr = PurchaseRequest::create([
                'pr_number' => $prNumber,
                'user_id' => $user->id,
                'department_id' => $departmentId,
                'request_date' => $requestDate,
                'purpose' => $prData['purpose'] ?? 'Biaya Operasional Rutin',
                'pr_type' => $prData['pr_type'] ?? 'operational',
                'status' => $asDraft ? 'draft' : 'pending',
                'total_amount' => 0,
                'bypass_budget_check' => $prData['bypass_budget_check'] ?? false,
                'bypass_budget_reason' => $prData['bypass_budget_reason'] ?? null,
                'notes' => $prData['notes'] ?? null,
                'custom_attributes' => $prData['custom_attributes'] ?? null,
            ]);

            $totalEstimated = 0;
            $initialItemStatus = $asDraft ? 'draft' : 'pending_estimate';

            foreach ($itemsData as $item) {
                $qty = (float) ($item['quantity'] ?? 1);
                $estPrice = (float) ($item['estimated_price'] ?? 0);
                $lineTotal = $estPrice > 0 ? ($qty * $estPrice) : 0;
                $totalEstimated += $lineTotal;

                PrItem::create([
                    'purchase_request_id' => $pr->id,
                    'product_id' => $item['product_id'] ?? null,
                    'item_name' => $item['item_name'],
                    'item_code' => $item['item_code'] ?? null,
                    'quantity' => $qty,
                    'ready_quantity' => 0,
                    'uom' => $item['uom'] ?? 'pcs',
                    'estimated_price' => $estPrice > 0 ? $lineTotal : 0,
                    'status' => $initialItemStatus,
                    'rekap_po_odoo' => ($pr->pr_type === 'operational'),
                    'is_incoming' => ($pr->pr_type === 'operational'),
                    'need_holding_quotation' => $item['need_holding_quotation'] ?? false,
                    'need_holding_delivery' => $item['need_holding_delivery'] ?? false,
                    'is_bon' => $item['is_bon'] ?? false,
                    'attachment_path' => $item['attachment_path'] ?? null,
                    'custom_attributes' => $item['custom_attributes'] ?? null,
                ]);
            }

            $pr->update(['total_amount' => $totalEstimated]);

            return $pr->load('items');
        });
    }

    /**
     * Step 2: Procurement inputs price estimate & workflow flags.
     */
    public function updateItemEstimates(PrItem $item, array $data, User $user): PrItem
    {
        return DB::transaction(function () use ($item, $data) {
            $estPrice = (float) ($data['estimated_price'] ?? $item->estimated_price);
            $qty = (float) $item->quantity;
            $lineTotal = $estPrice * $qty;

            $item->update([
                'estimated_price' => $lineTotal,
                'rekap_po_odoo' => $data['rekap_po_odoo'] ?? $item->rekap_po_odoo,
                'is_incoming' => $data['is_incoming'] ?? $item->is_incoming,
                'need_holding_quotation' => $data['need_holding_quotation'] ?? $item->need_holding_quotation,
                'need_holding_delivery' => $data['need_holding_delivery'] ?? $item->need_holding_delivery,
                'is_bon' => $data['is_bon'] ?? $item->is_bon,
                'status' => 'pending', // Ready for L1 Manager approval
            ]);

            $pr = $item->purchaseRequest;
            $pr->recalculateTotal();

            // Validate Budget unless bypassed
            if (! $pr->bypass_budget_check) {
                $budgetCheck = $this->budgetService->checkBudget(
                    (int) $pr->department_id,
                    $pr->purpose,
                    (float) $pr->total_amount
                );

                if (! $budgetCheck['is_sufficient']) {
                    throw ValidationException::withMessages([
                        'budget' => 'Pagu anggaran tidak mencukupi untuk item ini (Kurang: Rp '.number_format($budgetCheck['shortage'], 0, ',', '.').'). Silakan ajukan Budget Bypass atau sesuaikan nominal.',
                    ]);
                }
            }

            return $item->fresh();
        });
    }

    /**
     * Step 3: Approval Level 1 by OM (Operational) or Manager FAT (Non-Operational).
     */
    public function approveL1(PrItem $item, User $user, ?string $notes = null): PrItem
    {
        return DB::transaction(function () use ($item, $user, $notes) {
            $pr = $item->purchaseRequest;
            $approvalType = ($pr->pr_type === 'operational') ? 'om' : 'fatm';

            PrApproval::create([
                'purchase_request_id' => $pr->id,
                'pr_item_id' => $item->id,
                'user_id' => $user->id,
                'approval_type' => $approvalType,
                'status' => 'approved',
                'notes' => $notes,
            ]);

            // Next step in granular workflow:
            // If holding quotation required -> waiting_quotation_holding
            // Else -> waiting_actual_price
            $nextStatus = $item->need_holding_quotation ? 'waiting_quotation_holding' : 'waiting_actual_price';

            $item->update([
                'status' => $nextStatus,
                'validation_notes' => null,
            ]);

            // Book budget commitment
            $this->budgetService->bookBudget(
                (int) $pr->department_id,
                $pr->purpose,
                (float) $item->estimated_price
            );

            return $item->fresh();
        });
    }

    /**
     * Step 4: Submit Holding Quotation if need_holding_quotation = true.
     */
    public function submitHoldingQuotation(PrItem $item, array $data, User $user): PrItem
    {
        return DB::transaction(function () use ($item, $data, $user) {
            PrApproval::create([
                'purchase_request_id' => $item->purchase_request_id,
                'pr_item_id' => $item->id,
                'user_id' => $user->id,
                'approval_type' => 'holding',
                'status' => 'approved',
                'notes' => $data['notes'] ?? 'Quotation holding terlampir',
            ]);

            $item->update([
                'status' => 'waiting_actual_price',
                'attachment_path' => $data['attachment_path'] ?? $item->attachment_path,
            ]);

            return $item->fresh();
        });
    }

    /**
     * Step 5: Procurement inputs Actual Price and Delivery Plans.
     */
    public function inputActualPriceAndPlan(PrItem $item, array $data, User $user): PrItem
    {
        return DB::transaction(function () use ($item, $data) {
            $unitPrice = (float) ($data['actual_unit_price'] ?? 0);
            $qty = (float) $item->quantity;
            $actualPrice = $unitPrice > 0 ? ($unitPrice * $qty) : (float) ($data['actual_price'] ?? $item->estimated_price);

            // Determine next status:
            // If is_bon = true -> waiting_bon (will be grouped in PR Bon)
            // Else -> waiting_gm (Approval Level 2)
            $nextStatus = $item->is_bon ? 'waiting_bon' : 'waiting_gm';

            $item->update([
                'actual_price' => $actualPrice,
                'bon_unit_price' => $item->is_bon ? $actualPrice : null,
                'vendor_id' => $data['vendor_id'] ?? null,
                'vendor_name' => $data['vendor_name'] ?? null,
                'status' => $nextStatus,
            ]);

            // Save delivery plans if provided
            if (! empty($data['delivery_plans']) && is_array($data['delivery_plans'])) {
                $item->deliveryPlans()->delete();
                foreach ($data['delivery_plans'] as $plan) {
                    PrItemDeliveryPlan::create([
                        'pr_item_id' => $item->id,
                        'plan_date' => $plan['plan_date'],
                        'plan_quantity' => $plan['plan_quantity'] ?? $qty,
                        'notes' => $plan['notes'] ?? null,
                    ]);
                }
            }

            $item->purchaseRequest->recalculateTotal();

            return $item->fresh();
        });
    }

    /**
     * Step 6: Approval Level 2 by General Manager (Conventional PR Item).
     */
    public function approveL2Gm(PrItem $item, User $user, ?string $notes = null): PrItem
    {
        return DB::transaction(function () use ($item, $user, $notes) {
            PrApproval::create([
                'purchase_request_id' => $item->purchase_request_id,
                'pr_item_id' => $item->id,
                'user_id' => $user->id,
                'approval_type' => 'gm',
                'status' => 'approved',
                'notes' => $notes,
            ]);

            $item->update([
                'status' => 'ready_to_po',
            ]);

            return $item->fresh();
        });
    }

    /**
     * Rejection by OM or GM with mandatory reason.
     */
    public function rejectItem(PrItem $item, User $user, string $reason, string $step = 'om'): PrItem
    {
        return DB::transaction(function () use ($item, $user, $reason, $step) {
            $rejectedStatus = match ($step) {
                'gm' => 'rejected_gm',
                'proc' => 'rejected_proc',
                default => 'rejected_om',
            };

            PrApproval::create([
                'purchase_request_id' => $item->purchase_request_id,
                'pr_item_id' => $item->id,
                'user_id' => $user->id,
                'approval_type' => $step,
                'status' => 'rejected',
                'notes' => $reason,
            ]);

            $item->update([
                'status' => $rejectedStatus,
                'rejection_reason' => $reason,
            ]);

            // Release budget
            $pr = $item->purchaseRequest;
            $this->budgetService->releaseBudget(
                (int) $pr->department_id,
                $pr->purpose,
                (float) $item->estimated_price
            );

            return $item->fresh();
        });
    }

    /**
     * Bulk Revision: creates a new child PR with draft status containing rejected items.
     */
    public function bulkRevise(PurchaseRequest $originalPr, array $rejectedItemIds, User $user): PurchaseRequest
    {
        return DB::transaction(function () use ($originalPr, $rejectedItemIds, $user) {
            $items = PrItem::whereIn('id', $rejectedItemIds)
                ->where('purchase_request_id', $originalPr->id)
                ->get();

            if ($items->isEmpty()) {
                throw ValidationException::withMessages([
                    'items' => 'Tidak ada item tertolak yang valid untuk direvisi.',
                ]);
            }

            $revCount = $originalPr->revision_count + 1;
            $newPrNumber = $this->numberingService->generatePrNumber((int) $originalPr->department_id)."-R{$revCount}";

            $newPr = PurchaseRequest::create([
                'pr_number' => $newPrNumber,
                'user_id' => $user->id,
                'department_id' => $originalPr->department_id,
                'request_date' => Carbon::now(),
                'purpose' => $originalPr->purpose,
                'pr_type' => $originalPr->pr_type,
                'status' => 'draft',
                'total_amount' => 0,
                'bypass_budget_check' => $originalPr->bypass_budget_check,
                'bypass_budget_reason' => $originalPr->bypass_budget_reason,
                'revision_count' => $revCount,
                'parent_pr_id' => $originalPr->id,
                'notes' => "Revisi dari PR: {$originalPr->pr_number}",
            ]);

            $newTotal = 0;
            foreach ($items as $oldItem) {
                PrItem::create([
                    'purchase_request_id' => $newPr->id,
                    'product_id' => $oldItem->product_id,
                    'item_name' => $oldItem->item_name,
                    'item_code' => $oldItem->item_code,
                    'quantity' => $oldItem->quantity,
                    'ready_quantity' => 0,
                    'uom' => $oldItem->uom,
                    'estimated_price' => $oldItem->estimated_price,
                    'status' => 'draft',
                    'rekap_po_odoo' => $oldItem->rekap_po_odoo,
                    'is_incoming' => $oldItem->is_incoming,
                    'need_holding_quotation' => $oldItem->need_holding_quotation,
                    'need_holding_delivery' => $oldItem->need_holding_delivery,
                    'is_bon' => $oldItem->is_bon,
                    'validation_notes' => 'Catatan revisi sebelumnya: '.($oldItem->rejection_reason ?: 'Item ditolak'),
                ]);

                $newTotal += (float) $oldItem->estimated_price;
            }

            $newPr->update(['total_amount' => $newTotal]);

            return $newPr->load('items');
        });
    }

    /**
     * Step 7: Process PO (either Odoo Sync or Manual PO / Internal).
     */
    public function processPo(PrItem $item, array $poData, User $user): PrItem
    {
        return DB::transaction(function () use ($item, $poData, $user) {
            $poNumber = $poData['po_number'] ?? $this->numberingService->generateInternalPoNumber();
            $isManual = (bool) ($poData['po_manual_odoo'] ?? false);

            $item->update([
                'po_number' => $poNumber,
                'po_manual_odoo' => $isManual,
                'po_manual_reason' => $isManual ? ($poData['po_manual_reason'] ?? 'Manual input Odoo') : null,
                'po_manual_by' => $isManual ? $user->id : null,
                'po_manual_at' => $isManual ? Carbon::now() : null,
                'status' => 'ordered',
            ]);

            $pr = $item->purchaseRequest;
            $pr->update(['status' => 'ordered']);

            // Realize expense staging
            $this->budgetService->syncExpenseStaging($pr, $item, 'realized');

            // Dispatch Cross-Module PO Approved Event (Triggers R&D Price Staging & SCM Expected GR)
            event(new PurchaseOrderApprovedEvent($item, $user));

            return $item->fresh();
        });
    }

    /**
     * Step 8: Record Physical Delivery (Partial or Full).
     */
    public function recordDelivery(PrItem $item, array $deliveryData, User $user): PrItemDelivery
    {
        return DB::transaction(function () use ($item, $deliveryData, $user) {
            $deliveredQty = (float) ($deliveryData['delivered_quantity'] ?? 0);

            $delivery = PrItemDelivery::create([
                'pr_item_id' => $item->id,
                'delivery_date' => $deliveryData['delivery_date'] ?? Carbon::now()->toDateString(),
                'delivered_quantity' => $deliveredQty,
                'delivery_order_number' => $deliveryData['delivery_order_number'] ?? 'SJ-'.time(),
                'attachment_path' => $deliveryData['attachment_path'] ?? null,
                'received_by' => $user->id,
                'is_rejected' => false,
            ]);

            $item->increment('ready_quantity', $deliveredQty);
            $item->refresh();

            if ($item->isDelivered()) {
                $item->update(['status' => 'delivered']);
            }

            // Check if all items in PR are delivered
            $pr = $item->purchaseRequest;
            $allDelivered = $pr->items()->whereNotIn('status', ['delivered', 'completed', 'rejected_om', 'rejected_gm'])->count() === 0;
            if ($allDelivered) {
                $pr->update(['status' => 'delivered']);
            }

            return $delivery;
        });
    }

    /**
     * Record Defective / Rejected Delivery & Retur.
     */
    public function recordRetur(PrItem $item, array $returData, User $user): PrItemDelivery
    {
        return DB::transaction(function () use ($item, $returData, $user) {
            return PrItemDelivery::create([
                'pr_item_id' => $item->id,
                'retur_for_delivery_id' => $returData['retur_for_delivery_id'] ?? null,
                'delivery_date' => $returData['delivery_date'] ?? Carbon::now()->toDateString(),
                'delivered_quantity' => 0,
                'delivery_order_number' => $returData['delivery_order_number'] ?? 'RET-'.time(),
                'received_by' => $user->id,
                'is_rejected' => true,
                'reject_reason' => $returData['reject_reason'] ?? 'Barang cacat/tidak sesuai spek',
                'rejected_quantity' => (float) ($returData['rejected_quantity'] ?? 0),
            ]);
        });
    }

    /**
     * Send validation note to requester without rejecting.
     */
    public function sendValidationNote(PrItem $item, string $note, User $user): void
    {
        $item->update([
            'validation_notes' => $note,
        ]);

        PrApproval::create([
            'purchase_request_id' => $item->purchase_request_id,
            'pr_item_id' => $item->id,
            'user_id' => $user->id,
            'approval_type' => 'om',
            'status' => 'note',
            'notes' => $note,
        ]);
    }
}
