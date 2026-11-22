<?php

namespace App\Modules\Quality\Services;

use App\Models\User;
use App\Modules\Quality\Models\Capa;
use App\Modules\Quality\Models\Deviation;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class DeviationInvestigationService
{
    public function __construct(
        protected QualityNumberingService $numberingService,
        protected CapaService $capaService
    ) {}

    /**
     * Get paginated deviations with filters.
     *
     * @param  array<string, mixed>  $filters
     * @return LengthAwarePaginator<Deviation>
     */
    public function getDeviations(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $query = Deviation::with(['initiator', 'capa'])
            ->orderByDesc('id');

        if (! empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('deviation_number', 'like', "%{$search}%")
                    ->orWhere('pk_number', 'like', "%{$search}%")
                    ->orWhere('topik', 'like', "%{$search}%")
                    ->orWhere('nomor_bets', 'like', "%{$search}%")
                    ->orWhere('department', 'like', "%{$search}%");
            });
        }

        if (! empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (! empty($filters['identifikasi'])) {
            $query->where('identifikasi_penyimpangan', $filters['identifikasi']);
        }

        if (! empty($filters['department'])) {
            $query->where('department', $filters['department']);
        }

        return $query->paginate($perPage);
    }

    /**
     * Create a new Deviation Report.
     *
     * @param  array<string, mixed>  $data
     */
    public function createDeviation(array $data, User $initiator): Deviation
    {
        return DB::transaction(function () use ($data, $initiator) {
            $deviationNumber = $this->numberingService->generateDeviationNumber();
            $pkNumber = $this->numberingService->generatePkNumber();

            $status = ($data['action'] ?? 'submit') === 'draft' ? 'DRAFT' : 'OPEN';

            return Deviation::create([
                'initiator_id' => $initiator->id,
                'deviation_number' => $deviationNumber,
                'pk_number' => $pkNumber,
                'topik' => $data['topik'],
                'department' => $data['department'] ?? 'Produksi',
                'pic' => $data['pic'] ?? $initiator->name,
                'nomor_bets' => $data['nomor_bets'] ?? null,
                'nomor_alat' => $data['nomor_alat'] ?? null,
                'nomor_dokumen' => $data['nomor_dokumen'] ?? null,
                'objek_terdampak' => $data['objek_terdampak'] ?? null,
                'jenis_penyimpangan' => $data['jenis_penyimpangan'] ?? 'Proses',
                'identifikasi_penyimpangan' => $data['identifikasi_penyimpangan'] ?? 'Minor',
                'tanggal_temuan' => $data['tanggal_temuan'] ?? Carbon::today()->toDateString(),
                'is_other_batch_affected' => (bool) ($data['is_other_batch_affected'] ?? false),
                'other_batch_details' => $data['other_batch_details'] ?? null,
                'frekuensi_kejadian' => $data['frekuensi_kejadian'] ?? 'Tidak Pernah Sebelumnya',
                'is_production_stopped' => (bool) ($data['is_production_stopped'] ?? false),
                'immediate_action_details' => $data['immediate_action_details'] ?? null,
                'attachments' => $data['attachments'] ?? [],
                'status' => $status,
                'custom_attributes' => $data['custom_attributes'] ?? null,
            ]);
        });
    }

    /**
     * Update Investigation details (6M Fishbone, 5-Why, and Action Evaluation FMEA).
     *
     * @param  array<string, mixed>  $data
     */
    public function updateInvestigation(Deviation $deviation, array $data, ?User $user = null): Deviation
    {
        return DB::transaction(function () use ($deviation, $data) {
            $deviation->update([
                'root_cause' => $data['root_cause'] ?? $deviation->root_cause,
                'fishbone_6m' => $data['fishbone_6m'] ?? $deviation->fishbone_6m,
                'five_why' => $data['five_why'] ?? $deviation->five_why,
                'evaluasi_tindakan' => $data['evaluasi_tindakan'] ?? $deviation->evaluasi_tindakan,
                'status' => 'IN REVIEW',
            ]);

            return $deviation->fresh();
        });
    }

    /**
     * Assign Superior ('Mengetahui') to Deviation.
     */
    public function assignMengetahui(Deviation $deviation, int $userId, string $role): Deviation
    {
        return DB::transaction(function () use ($deviation, $userId, $role) {
            $user = User::findOrFail($userId);
            $mengetahui = $deviation->mengetahui_data ?? [];

            $mengetahui[] = [
                'user_id' => $user->id,
                'name' => $user->name,
                'role' => $role,
                'assigned_at' => Carbon::now()->toDateTimeString(),
                'feedback' => null,
                'feedback_at' => null,
                'signature_path' => null,
            ];

            $deviation->mengetahui_data = $mengetahui;
            $deviation->save();

            return $deviation->fresh();
        });
    }

    /**
     * Superior submits feedback and digital signature on Deviation.
     */
    public function submitMengetahuiFeedback(
        Deviation $deviation,
        User $user,
        string $feedback,
        ?string $signaturePath = null
    ): Deviation {
        return DB::transaction(function () use ($deviation, $user, $feedback, $signaturePath) {
            $mengetahui = $deviation->mengetahui_data ?? [];

            $found = false;
            foreach ($mengetahui as &$item) {
                if ($item['user_id'] === $user->id) {
                    $item['feedback'] = $feedback;
                    $item['feedback_at'] = Carbon::now()->toDateTimeString();
                    $item['signature_path'] = $signaturePath ?? $user->signature_path;
                    $found = true;
                    break;
                }
            }

            if (! $found) {
                $mengetahui[] = [
                    'user_id' => $user->id,
                    'name' => $user->name,
                    'role' => 'Atasan Terkait',
                    'assigned_at' => Carbon::now()->toDateTimeString(),
                    'feedback' => $feedback,
                    'feedback_at' => Carbon::now()->toDateTimeString(),
                    'signature_path' => $signaturePath ?? $user->signature_path,
                ];
            }

            $deviation->mengetahui_data = $mengetahui;
            $deviation->save();

            return $deviation->fresh();
        });
    }

    /**
     * QA Decision on Deviation:
     * - APPROVE: Approves deviation and AUTOMATICALLY SPAWNS CAPA
     * - REJECT: Rejects deviation with reason
     *
     * @return array{deviation: Deviation, capa: ?Capa}
     */
    public function decideDeviation(
        Deviation $deviation,
        string $decision, // 'APPROVE' or 'REJECT'
        ?string $notes = null,
        ?User $qaUser = null
    ): array {
        return DB::transaction(function () use ($deviation, $decision, $notes) {
            if ($decision === 'REJECT') {
                $deviation->update([
                    'status' => 'REJECTED',
                    'reject_reason' => $notes,
                ]);

                return [
                    'deviation' => $deviation->fresh(),
                    'capa' => null,
                ];
            }

            // Decision is APPROVE: Mark deviation approved and spawn CAPA
            $deviation->update([
                'status' => 'APPROVED',
            ]);

            $capa = $this->capaService->createFromDeviation($deviation);

            return [
                'deviation' => $deviation->fresh(['capa']),
                'capa' => $capa,
            ];
        });
    }
}
