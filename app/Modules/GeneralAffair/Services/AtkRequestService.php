<?php

namespace App\Modules\GeneralAffair\Services;

use App\Models\User;
use App\Modules\GeneralAffair\Models\AtkItem;
use App\Modules\GeneralAffair\Models\AtkRequest;
use App\Modules\GeneralAffair\Models\AtkRequestItem;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class AtkRequestService
{
    public function __construct(
        protected GaNumberingService $numberingService
    ) {}

    /**
     * Get paginated ATK & RT requests with filtering.
     *
     * @param  array<string, mixed>  $filters
     * @return LengthAwarePaginator<AtkRequest>
     */
    public function getRequests(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $query = AtkRequest::with(['user', 'department', 'items.atkItem', 'items.uom', 'managerApprover', 'adminApprover', 'revisions'])
            ->orderByDesc('id');

        if (! empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('request_number', 'like', "%{$search}%")
                    ->orWhere('alasan_pengajuan', 'like', "%{$search}%");
            });
        }

        if (! empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (! empty($filters['type'])) {
            $query->where('type', $filters['type']);
        }

        if (! empty($filters['department_id'])) {
            $query->where('department_id', (int) $filters['department_id']);
        }

        return $query->paginate($perPage);
    }

    /**
     * Create new ATK or RT request.
     *
     * @param  array<string, mixed>  $data
     */
    public function createRequest(array $data, User $user): AtkRequest
    {
        return DB::transaction(function () use ($data, $user) {
            $type = $data['type'] ?? 'ATK';
            $requestNumber = $this->numberingService->generateAtkNumber($type);

            $atkRequest = AtkRequest::create([
                'request_number' => $requestNumber,
                'type' => $type,
                'user_id' => $user->id,
                'department_id' => $data['department_id'] ?? $user->department_id,
                'tanggal_pengajuan' => $data['tanggal_pengajuan'] ?? Carbon::now()->toDateString(),
                'alasan_pengajuan' => $data['alasan_pengajuan'],
                'status' => 'PENDING_MANAGER',
                'custom_attributes' => $data['custom_attributes'] ?? null,
            ]);

            if (! empty($data['items']) && is_array($data['items'])) {
                foreach ($data['items'] as $itemData) {
                    $uomId = $itemData['uom_id'] ?? null;
                    if (! $uomId && ! empty($itemData['atk_item_id'])) {
                        $atkItem = AtkItem::find($itemData['atk_item_id']);
                        $uomId = $atkItem?->uom_id;
                    }

                    AtkRequestItem::create([
                        'request_id' => $atkRequest->id,
                        'atk_item_id' => $itemData['atk_item_id'] ?? null,
                        'custom_item_name' => $itemData['custom_item_name'] ?? null,
                        'quantity' => (int) ($itemData['quantity'] ?? 1),
                        'approved_quantity' => (int) ($itemData['quantity'] ?? 1),
                        'uom_id' => $uomId,
                        'status' => 'PENDING',
                        'rejection_note' => null,
                    ]);
                }
            }

            return $atkRequest->load(['items.atkItem', 'items.uom', 'user', 'department']);
        });
    }

    /**
     * Manager approves, partially approves, or requests revision.
     *
     * @param  array<string, mixed>  $data
     */
    public function approveByManager(AtkRequest $request, User $manager, array $data = []): AtkRequest
    {
        return DB::transaction(function () use ($request, $manager, $data) {
            $action = $data['action'] ?? 'approve'; // approve, reject, revision
            $notes = $data['notes'] ?? null;
            $itemsPayload = $data['items'] ?? [];

            if ($action === 'reject') {
                $request->update([
                    'status' => 'REJECTED',
                    'manager_approved_by' => $manager->id,
                    'manager_approved_at' => Carbon::now(),
                    'manager_notes' => $notes,
                ]);

                foreach ($request->items as $item) {
                    $item->update(['status' => 'REJECTED', 'rejection_note' => $notes]);
                }

                return $request->fresh(['items.atkItem', 'items.uom']);
            }

            // Update individual items if provided
            foreach ($itemsPayload as $itemData) {
                if (isset($itemData['id'])) {
                    $item = AtkRequestItem::where('request_id', $request->id)->find($itemData['id']);
                    if ($item) {
                        $approvedQty = isset($itemData['approved_quantity']) ? (int) $itemData['approved_quantity'] : $item->quantity;
                        $itemStatus = $approvedQty > 0 ? 'APPROVED' : 'REJECTED';
                        $item->update([
                            'approved_quantity' => $approvedQty,
                            'status' => $itemStatus,
                            'rejection_note' => $itemData['rejection_note'] ?? null,
                        ]);
                    }
                }
            }

            if ($action === 'revision') {
                $request->update([
                    'status' => 'REJECTED',
                    'manager_approved_by' => $manager->id,
                    'manager_approved_at' => Carbon::now(),
                    'manager_notes' => $notes,
                ]);
            } else {
                $request->update([
                    'status' => 'PENDING_ADMIN_GA',
                    'manager_approved_by' => $manager->id,
                    'manager_approved_at' => Carbon::now(),
                    'manager_notes' => $notes,
                ]);
            }

            return $request->fresh(['items.atkItem', 'items.uom']);
        });
    }

    /**
     * Create Revision for an ATK request (spawns -R1, -R2).
     *
     * @param  array<string, mixed>  $data
     */
    public function createRevision(AtkRequest $originalRequest, array $data, User $user): AtkRequest
    {
        return DB::transaction(function () use ($originalRequest, $data, $user) {
            $revisionNumber = $this->numberingService->generateAtkRevisionNumber($originalRequest);

            $revision = AtkRequest::create([
                'parent_id' => $originalRequest->id,
                'request_number' => $revisionNumber,
                'type' => $originalRequest->type,
                'user_id' => $user->id,
                'department_id' => $originalRequest->department_id,
                'tanggal_pengajuan' => Carbon::now()->toDateString(),
                'alasan_pengajuan' => $data['alasan_pengajuan'] ?? $originalRequest->alasan_pengajuan,
                'status' => 'PENDING_MANAGER',
                'custom_attributes' => $data['custom_attributes'] ?? $originalRequest->custom_attributes,
            ]);

            $items = $data['items'] ?? [];
            foreach ($items as $itemData) {
                $uomId = $itemData['uom_id'] ?? null;
                if (! $uomId && ! empty($itemData['atk_item_id'])) {
                    $atkItem = AtkItem::find($itemData['atk_item_id']);
                    $uomId = $atkItem?->uom_id;
                }

                AtkRequestItem::create([
                    'request_id' => $revision->id,
                    'atk_item_id' => $itemData['atk_item_id'] ?? null,
                    'custom_item_name' => $itemData['custom_item_name'] ?? null,
                    'quantity' => (int) ($itemData['quantity'] ?? 1),
                    'approved_quantity' => (int) ($itemData['quantity'] ?? 1),
                    'uom_id' => $uomId,
                    'status' => 'PENDING',
                ]);
            }

            // Mark original as revised
            $originalRequest->update(['status' => 'REJECTED']);

            return $revision->load(['items.atkItem', 'items.uom', 'user', 'department']);
        });
    }

    /**
     * Admin GA approves the request.
     */
    public function approveByGaAdmin(AtkRequest $request, User $gaAdmin, ?string $notes = null): AtkRequest
    {
        return DB::transaction(function () use ($request, $gaAdmin, $notes) {
            $request->update([
                'status' => 'APPROVED',
                'admin_approved_by' => $gaAdmin->id,
                'admin_approved_at' => Carbon::now(),
                'admin_notes' => $notes,
            ]);

            return $request->fresh(['items.atkItem', 'items.uom']);
        });
    }

    /**
     * Handover / distribution of requested items to user.
     * Decrements actual stock in ga_atk_items.
     *
     * @param  array<string, mixed>  $data
     */
    public function handover(AtkRequest $request, User $actor, array $data): AtkRequest
    {
        return DB::transaction(function () use ($request, $data) {
            foreach ($request->items as $item) {
                if ($item->atk_item_id && $item->approved_quantity > 0) {
                    $atkItem = AtkItem::find($item->atk_item_id);
                    if ($atkItem) {
                        $newStock = max(0, $atkItem->current_stock - $item->approved_quantity);
                        $atkItem->update(['current_stock' => $newStock]);
                    }
                }
            }

            $request->update([
                'status' => 'HANDED_OVER',
                'diberikan_pada' => Carbon::now()->toDateString(),
                'receiver_name' => $data['receiver_name'] ?? $request->user?->name,
                'receiver_signature' => $data['receiver_signature'] ?? null,
            ]);

            return $request->fresh(['items.atkItem', 'items.uom']);
        });
    }
}
