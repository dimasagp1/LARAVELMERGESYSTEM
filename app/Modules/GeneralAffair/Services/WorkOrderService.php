<?php

namespace App\Modules\GeneralAffair\Services;

use App\Models\User;
use App\Modules\GeneralAffair\Models\FpuMaterial;
use App\Modules\GeneralAffair\Models\FpuRequest;
use App\Modules\GeneralAffair\Models\WoHandover;
use App\Modules\GeneralAffair\Models\WorkOrder;
use App\Modules\GeneralAffair\Models\WorkOrderProgress;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class WorkOrderService
{
    public function __construct(
        protected GaNumberingService $numberingService
    ) {}

    /**
     * Get paginated work orders with filtering.
     *
     * @param  array<string, mixed>  $filters
     * @return LengthAwarePaginator<WorkOrder>
     */
    public function getWorkOrders(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $query = WorkOrder::with(['user', 'department', 'location', 'assignedEngineer', 'fpuRequest', 'handover'])
            ->orderByDesc('id');

        if (! empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('wo_number', 'like', "%{$search}%")
                    ->orWhere('complaint_description', 'like', "%{$search}%");
            });
        }

        if (! empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (! empty($filters['priority'])) {
            $query->where('priority', $filters['priority']);
        }

        if (! empty($filters['category'])) {
            $query->where('category', $filters['category']);
        }

        if (! empty($filters['department_id'])) {
            $query->where('department_id', (int) $filters['department_id']);
        }

        return $query->paginate($perPage);
    }

    /**
     * Form A: Client submits new Work Order.
     *
     * @param  array<string, mixed>  $data
     */
    public function createWorkOrder(array $data, User $user): WorkOrder
    {
        return DB::transaction(function () use ($data, $user) {
            $woNumber = $this->numberingService->generateWoNumber();

            return WorkOrder::create([
                'wo_number' => $woNumber,
                'user_id' => $user->id,
                'department_id' => $data['department_id'] ?? $user->department_id,
                'location_id' => $data['location_id'] ?? null,
                'complaint_description' => $data['complaint_description'],
                'priority' => $data['priority'] ?? 'Normal',
                'category' => $data['category'] ?? 'Gedung & Sipil',
                'attachment_path' => $data['attachment_path'] ?? null,
                'status' => 'Menunggu Penugasan',
                'custom_attributes' => $data['custom_attributes'] ?? null,
            ]);
        });
    }

    /**
     * Triage: Admin GA assigns engineer.
     */
    public function assignEngineer(WorkOrder $wo, int $engineerId): WorkOrder
    {
        return DB::transaction(function () use ($wo, $engineerId) {
            $wo->update([
                'assigned_engineer_id' => $engineerId,
                'assigned_at' => Carbon::now(),
                'status' => 'Pengecekan Engineer',
            ]);

            return $wo->fresh(['assignedEngineer']);
        });
    }

    /**
     * Admin GA rejects invalid or duplicate work order.
     */
    public function rejectWorkOrder(WorkOrder $wo, string $reason): WorkOrder
    {
        return DB::transaction(function () use ($wo, $reason) {
            $wo->update([
                'rejection_note' => $reason,
                'status' => 'Ditolak',
            ]);

            return $wo->fresh();
        });
    }

    /**
     * Form B: Engineer conducts field check & specifies material requirements.
     *
     * @param  array<string, mixed>  $checkData
     */
    public function submitEngineerCheck(WorkOrder $wo, array $checkData): WorkOrder
    {
        return DB::transaction(function () use ($wo, $checkData) {
            $needsMaterial = (bool) ($checkData['needs_material'] ?? false);

            $wo->update([
                'check_result' => $checkData['check_result'],
                'engineer_needs' => $checkData['engineer_needs'] ?? null,
                'needs_material' => $needsMaterial,
                'status' => $needsMaterial ? 'Menunggu Pengadaan Material' : 'Sedang Dikerjakan',
            ]);

            // Auto-instantiate Form C (FPU Request) if materials are needed
            if ($needsMaterial && ! empty($checkData['materials']) && is_array($checkData['materials'])) {
                $fpuNumber = $this->numberingService->generateFpuNumber();

                $fpu = FpuRequest::create([
                    'work_order_id' => $wo->id,
                    'fpu_number' => $fpuNumber,
                    'request_date' => Carbon::today()->toDateString(),
                    'priority' => $checkData['fpu_priority'] ?? 'Penting',
                    'execution_type' => $checkData['execution_type'] ?? 'Internal',
                    'category' => $checkData['fpu_category'] ?? 'Pengadaan',
                    'status' => 'PENDING_HRGA',
                ]);

                foreach ($checkData['materials'] as $mat) {
                    if (! empty($mat['material_name'])) {
                        FpuMaterial::create([
                            'fpu_request_id' => $fpu->id,
                            'material_name' => $mat['material_name'],
                            'specification' => $mat['specification'] ?? null,
                            'quantity' => $mat['quantity'] ?? 1,
                            'uom' => $mat['uom'] ?? 'Pcs',
                            'estimated_cost' => $mat['estimated_cost'] ?? 0,
                        ]);
                    }
                }
            }

            return $wo->fresh(['fpuRequest.materials']);
        });
    }

    /**
     * Process Form C approvals (HRGA, Manager, GM).
     */
    public function approveFpu(FpuRequest $fpu, string $level, User $user, ?string $notes = null): FpuRequest
    {
        return DB::transaction(function () use ($fpu, $level, $user) {
            $now = Carbon::now();

            if ($level === 'hrga') {
                $fpu->verified_by_hrga = true;
                $fpu->verified_by_user_id = $user->id;
                $fpu->verified_at = $now;
                $fpu->status = 'PENDING_MANAGER';
            } elseif ($level === 'manager') {
                $fpu->known_by_manager = true;
                $fpu->manager_user_id = $user->id;
                $fpu->manager_approved_at = $now;
                $fpu->status = 'PENDING_GM';
            } elseif ($level === 'gm' || $level === 'all') {
                $fpu->verified_by_hrga = true;
                $fpu->known_by_manager = true;
                $fpu->approved_by_gm = true;
                $fpu->gm_user_id = $user->id;
                $fpu->gm_approved_at = $now;
                $fpu->status = 'APPROVED';

                // Automatically transition the Work Order back to "Sedang Dikerjakan"
                $wo = $fpu->workOrder;
                if ($wo && $wo->status === 'Menunggu Pengadaan Material') {
                    $wo->update(['status' => 'Sedang Dikerjakan']);
                }
            }

            $fpu->save();

            return $fpu->fresh(['workOrder']);
        });
    }

    /**
     * Log Work Order progress.
     */
    public function logProgress(
        WorkOrder $wo,
        int $percent,
        string $description,
        ?string $photoPath,
        User $user
    ): WorkOrderProgress {
        return DB::transaction(function () use ($wo, $percent, $description, $photoPath, $user) {
            $progress = WorkOrderProgress::create([
                'work_order_id' => $wo->id,
                'user_id' => $user->id,
                'progress_percent' => min(100, max(0, $percent)),
                'description' => $description,
                'photo_path' => $photoPath,
            ]);

            if ($percent >= 100) {
                $wo->update(['status' => 'Menunggu Serah Terima']);
                // Auto prepare Form D Handover if not exists
                $this->prepareHandover($wo, $user);
            }

            return $progress;
        });
    }

    /**
     * Form D: Prepare Handover Berita Acara.
     */
    public function prepareHandover(WorkOrder $wo, User $preparedBy): WoHandover
    {
        return DB::transaction(function () use ($wo, $preparedBy) {
            $existing = WoHandover::where('work_order_id', $wo->id)->first();
            if ($existing) {
                return $existing;
            }

            $bastNumber = $this->numberingService->generateBastNumber();

            return WoHandover::create([
                'work_order_id' => $wo->id,
                'bast_number' => $bastNumber,
                'handover_date' => Carbon::today()->toDateString(),
                'prepared_by_user_id' => $preparedBy->id,
                'client_user_id' => $wo->user_id,
                'is_confirmed' => false,
            ]);
        });
    }

    /**
     * Form D: Client confirms handover, provides rating (1-5), and feedback.
     */
    public function confirmHandover(
        WoHandover $handover,
        int $rating,
        ?string $feedback = null,
        ?string $signaturePath = null
    ): WoHandover {
        return DB::transaction(function () use ($handover, $rating, $feedback, $signaturePath) {
            $handover->update([
                'satisfaction_rating' => min(5, max(1, $rating)),
                'client_feedback' => $feedback,
                'client_signature_path' => $signaturePath,
                'is_confirmed' => true,
                'confirmed_at' => Carbon::now(),
            ]);

            // Mark Work Order as Selesai
            $wo = $handover->workOrder;
            if ($wo) {
                $wo->update(['status' => 'Selesai']);
            }

            return $handover->fresh(['workOrder']);
        });
    }
}
