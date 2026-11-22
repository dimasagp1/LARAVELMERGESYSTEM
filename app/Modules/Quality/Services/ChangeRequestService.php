<?php

namespace App\Modules\Quality\Services;

use App\Models\User;
use App\Modules\Quality\Models\ChangeRequest;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class ChangeRequestService
{
    public function __construct(
        protected QualityNumberingService $numberingService
    ) {}

    /**
     * Get paginated change requests with filters.
     *
     * @param  array<string, mixed>  $filters
     * @return LengthAwarePaginator<ChangeRequest>
     */
    public function getChangeRequests(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $query = ChangeRequest::with(['initiator', 'pic'])
            ->orderByDesc('id');

        if (! empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('cr_number', 'like', "%{$search}%")
                    ->orWhere('nama_produk', 'like', "%{$search}%")
                    ->orWhere('sifat_perubahan', 'like', "%{$search}%")
                    ->orWhere('usulan_perubahan', 'like', "%{$search}%");
            });
        }

        if (! empty($filters['type'])) {
            $query->where('type', strtoupper($filters['type']));
        }

        if (! empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        return $query->paginate($perPage);
    }

    /**
     * Create a new Change Request (CRA or CRB).
     *
     * @param  array<string, mixed>  $data
     */
    public function createChangeRequest(array $data, User $initiator): ChangeRequest
    {
        return DB::transaction(function () use ($data, $initiator) {
            $type = strtoupper($data['type'] ?? 'CRA');
            $crNumber = $this->numberingService->generateCrNumber($type);

            $severity = (int) ($data['severity'] ?? 1);
            $occurrence = (int) ($data['occurrence'] ?? 1);
            $detection = (int) ($data['detection'] ?? 1);
            $rpn = $severity * $occurrence * $detection;

            $status = ($data['action'] ?? 'submit') === 'draft' ? 'DRAFT' : 'OPEN';

            return ChangeRequest::create([
                'initiator_id' => $initiator->id,
                'pic_id' => $data['pic_id'] ?? null,
                'cr_number' => $crNumber,
                'type' => $type,
                'sifat_perubahan' => $data['sifat_perubahan'],
                'nama_produk' => $data['nama_produk'],
                'awal_sebelum_perubahan' => $data['awal_sebelum_perubahan'],
                'usulan_perubahan' => $data['usulan_perubahan'],
                'alasan_perubahan' => $data['alasan_perubahan'] ?? null,
                'target_pelaksanaan' => $data['target_pelaksanaan'] ?? null,
                'severity' => $severity,
                'occurrence' => $occurrence,
                'detection' => $detection,
                'rpn' => $rpn,
                'risk_mitigation' => $data['risk_mitigation'] ?? null,
                'qa_verification_data' => [
                    'stage_1' => [
                        'submitted_at' => $status === 'OPEN' ? Carbon::now()->toDateTimeString() : null,
                        'assessments' => [],
                        'approvals' => [
                            'head_of_quality' => null,
                            'operational_manager' => null,
                            'general_manager' => null,
                        ],
                    ],
                ],
                'status' => $status,
                'custom_attributes' => $data['custom_attributes'] ?? null,
            ]);
        });
    }

    /**
     * Assign multi-department PIC assessment for CRA/CRB.
     *
     * @param  array<int, array{user_id: int, department: string, pic_name: string}>  $assignments
     */
    public function assignDepartmentAssessments(ChangeRequest $cr, array $assignments): ChangeRequest
    {
        return DB::transaction(function () use ($cr, $assignments) {
            $verification = $cr->qa_verification_data ?? [];
            $existingAssessments = $verification['stage_1']['assessments'] ?? [];

            foreach ($assignments as $item) {
                $existingAssessments[] = [
                    'user_id' => $item['user_id'],
                    'department' => $item['department'],
                    'pic_name' => $item['pic_name'],
                    'risk_analysis' => null,
                    'recommendation' => null,
                    'status' => 'PENDING',
                    'signed_at' => null,
                ];
            }

            $verification['stage_1']['assessments'] = $existingAssessments;
            $cr->qa_verification_data = $verification;
            $cr->status = 'IN REVIEW';
            $cr->save();

            return $cr->fresh();
        });
    }

    /**
     * Department PIC submits risk assessment & recommendation.
     */
    public function submitDepartmentAssessment(
        ChangeRequest $cr,
        User $user,
        string $riskAnalysis,
        string $recommendation,
        string $decision = 'APPROVED'
    ): ChangeRequest {
        return DB::transaction(function () use ($cr, $user, $riskAnalysis, $recommendation, $decision) {
            $verification = $cr->qa_verification_data ?? [];
            $assessments = $verification['stage_1']['assessments'] ?? [];

            $found = false;
            foreach ($assessments as &$assessment) {
                if ($assessment['user_id'] === $user->id) {
                    $assessment['risk_analysis'] = $riskAnalysis;
                    $assessment['recommendation'] = $recommendation;
                    $assessment['status'] = $decision;
                    $assessment['signed_at'] = Carbon::now()->toDateTimeString();
                    $found = true;
                    break;
                }
            }

            if (! $found) {
                $assessments[] = [
                    'user_id' => $user->id,
                    'department' => $user->department?->name ?? 'Terkait',
                    'pic_name' => $user->name,
                    'risk_analysis' => $riskAnalysis,
                    'recommendation' => $recommendation,
                    'status' => $decision,
                    'signed_at' => Carbon::now()->toDateTimeString(),
                ];
            }

            $verification['stage_1']['assessments'] = $assessments;
            $cr->qa_verification_data = $verification;
            $cr->save();

            return $cr->fresh();
        });
    }

    /**
     * Process Management Approvals for Change Request.
     *
     * Level 1: Head of Quality (CRA & CRB)
     * Level 2: Operational Manager (Khusus CRA)
     * Level 3: General Manager (Khusus CRA)
     */
    public function processApproval(
        ChangeRequest $cr,
        User $user,
        string $approvalLevel, // 'head_of_quality', 'operational_manager', 'general_manager'
        string $decision, // 'APPROVED', 'REJECTED'
        ?string $notes = null
    ): ChangeRequest {
        return DB::transaction(function () use ($cr, $user, $approvalLevel, $decision, $notes) {
            $verification = $cr->qa_verification_data ?? [];
            $approvals = $verification['stage_1']['approvals'] ?? [];

            $approvals[$approvalLevel] = [
                'user_id' => $user->id,
                'name' => $user->name,
                'status' => $decision,
                'notes' => $notes,
                'signed_at' => Carbon::now()->toDateTimeString(),
                'signature_path' => $user->signature_path,
            ];

            $verification['stage_1']['approvals'] = $approvals;
            $cr->qa_verification_data = $verification;

            if ($decision === 'REJECTED') {
                $cr->status = 'REJECT';
                $cr->save();

                return $cr->fresh();
            }

            // Check if full approval chain is complete
            $isComplete = false;
            if ($cr->isCRB()) {
                // CRB requires only Head of Quality
                $isComplete = ! empty($approvals['head_of_quality']) && $approvals['head_of_quality']['status'] === 'APPROVED';
            } else {
                // CRA requires Head of Quality + Operational Manager + General Manager
                $hasHoq = ! empty($approvals['head_of_quality']) && $approvals['head_of_quality']['status'] === 'APPROVED';
                $hasOm = ! empty($approvals['operational_manager']) && $approvals['operational_manager']['status'] === 'APPROVED';
                $hasGm = ! empty($approvals['general_manager']) && $approvals['general_manager']['status'] === 'APPROVED';

                $isComplete = $hasHoq && $hasOm && $hasGm;
            }

            if ($isComplete) {
                $cr->status = 'IN PROGRESS'; // Ready for Stage 2 implementation
            } else {
                $cr->status = 'IN REVIEW';
            }

            $cr->save();

            return $cr->fresh();
        });
    }

    /**
     * Submit Stage 2 Implementation Documentation & Proof.
     *
     * @param  array<string, mixed>  $stage2Payload
     */
    public function submitStage2(ChangeRequest $cr, array $stage2Payload, User $user): ChangeRequest
    {
        return DB::transaction(function () use ($cr, $stage2Payload, $user) {
            $cr->stage_2_data = [
                'submitted_by' => $user->name,
                'submitted_at' => Carbon::now()->toDateTimeString(),
                'implementation_summary' => $stage2Payload['implementation_summary'] ?? '',
                'proof_files' => $stage2Payload['proof_files'] ?? [],
                'status' => 'SUBMITTED',
            ];

            $cr->status = 'IN PROGRESS';
            $cr->save();

            return $cr->fresh();
        });
    }

    /**
     * QA Verifies Stage 2 and marks Change Request as COMPLETE.
     */
    public function verifyAndCompleteStage2(ChangeRequest $cr, string $notes, User $qaUser): ChangeRequest
    {
        return DB::transaction(function () use ($cr, $notes, $qaUser) {
            $stage2 = $cr->stage_2_data ?? [];
            $stage2['verification'] = [
                'verified_by' => $qaUser->name,
                'verified_at' => Carbon::now()->toDateTimeString(),
                'notes' => $notes,
                'status' => 'VERIFIED',
            ];

            $cr->stage_2_data = $stage2;
            $cr->status = 'COMPLETE';
            $cr->save();

            return $cr->fresh();
        });
    }
}
