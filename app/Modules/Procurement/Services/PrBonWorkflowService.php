<?php

namespace App\Modules\Procurement\Services;

use App\Models\User;
use App\Modules\Procurement\Models\PrApproval;
use App\Modules\Procurement\Models\PrBon;
use App\Modules\Procurement\Models\PrItem;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class PrBonWorkflowService
{
    public function __construct(
        protected ProcurementNumberingService $numberingService,
        protected BudgetControlService $budgetService
    ) {}

    /**
     * Create Bon file from staged items.
     */
    public function createBon(array $bonData, array $itemIds, User $user): PrBon
    {
        return DB::transaction(function () use ($bonData, $itemIds, $user) {
            $items = PrItem::whereIn('id', $itemIds)
                ->where('is_bon', true)
                ->whereIn('status', ['waiting_bon', 'revision'])
                ->get();

            if ($items->isEmpty()) {
                throw ValidationException::withMessages([
                    'items' => 'Pilih minimal satu item valid di antrean staging bon.',
                ]);
            }

            $bonNumber = $this->numberingService->generateBonNumber();
            $bonDate = isset($bonData['bon_date']) ? Carbon::parse($bonData['bon_date']) : Carbon::now();

            $bon = PrBon::create([
                'purchase_request_id' => $items->first()->purchase_request_id,
                'bon_number' => $bonNumber,
                'vendor_name' => $bonData['vendor_name'] ?? 'Vendor Toko Lokal / Bon',
                'bon_date' => $bonDate,
                'total_amount' => 0,
                'status' => 'waiting_gm',
                'created_by' => $user->id,
                'revision_note' => null,
            ]);

            $totalAmount = 0;
            foreach ($items as $item) {
                $lineTotal = (float) ($bonData['item_prices'][$item->id] ?? $item->bon_unit_price ?? $item->actual_price);

                $item->update([
                    'pr_bon_id' => $bon->id,
                    'bon_unit_price' => $lineTotal,
                    'vendor_name' => $bon->vendor_name,
                    'status' => 'waiting_gm_bon',
                ]);

                $totalAmount += $lineTotal;
            }

            $bon->update(['total_amount' => $totalAmount]);

            return $bon->load('items');
        });
    }

    /**
     * GM Review and Decision for Bon Dossier.
     */
    public function gmDecision(
        PrBon $bon,
        string $action,
        User $user,
        array $approvedItemIds = [],
        ?string $note = null
    ): PrBon {
        return DB::transaction(function () use ($bon, $action, $user, $approvedItemIds, $note) {
            $items = $bon->items;

            switch ($action) {
                case 'approve_all':
                    foreach ($items as $item) {
                        $item->update(['status' => 'ready_to_po']);
                        PrApproval::create([
                            'purchase_request_id' => $item->purchase_request_id,
                            'pr_item_id' => $item->id,
                            'user_id' => $user->id,
                            'approval_type' => 'gm',
                            'status' => 'approved',
                            'notes' => $note ?: 'Persetujuan Bon GM Penuh',
                        ]);
                    }
                    $bon->update([
                        'status' => 'approved_gm',
                        'approved_by' => $user->id,
                        'approved_at' => Carbon::now(),
                    ]);
                    break;

                case 'partial':
                    $approvedCount = 0;
                    foreach ($items as $item) {
                        $isApproved = in_array($item->id, $approvedItemIds);
                        $status = $isApproved ? 'ready_to_po' : 'rejected_gm';
                        $item->update([
                            'status' => $status,
                            'rejection_reason' => $isApproved ? null : ($note ?: 'Ditolak pada penelaahan Bon oleh GM'),
                        ]);

                        PrApproval::create([
                            'purchase_request_id' => $item->purchase_request_id,
                            'pr_item_id' => $item->id,
                            'user_id' => $user->id,
                            'approval_type' => 'gm',
                            'status' => $isApproved ? 'approved' : 'rejected',
                            'notes' => $note,
                        ]);

                        if ($isApproved) {
                            $approvedCount++;
                        }
                    }
                    $bon->update([
                        'status' => $approvedCount > 0 ? 'partial_approved' : 'rejected_gm',
                        'approved_by' => $user->id,
                        'approved_at' => Carbon::now(),
                    ]);
                    break;

                case 'revision':
                    foreach ($items as $item) {
                        $item->update(['status' => 'waiting_bon']);
                    }
                    $bon->update([
                        'status' => 'revision',
                        'revision_note' => $note ?: 'Mohon perbaiki harga/vendor bon',
                    ]);
                    break;

                case 'reject_all':
                default:
                    foreach ($items as $item) {
                        $item->update([
                            'status' => 'rejected_gm',
                            'rejection_reason' => $note ?: 'Berkas bon ditolak oleh GM',
                        ]);
                    }
                    $bon->update([
                        'status' => 'rejected_gm',
                        'approved_by' => $user->id,
                        'approved_at' => Carbon::now(),
                        'revision_note' => $note,
                    ]);
                    break;
            }

            return $bon->fresh(['items']);
        });
    }
}
