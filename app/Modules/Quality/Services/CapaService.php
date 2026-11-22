<?php

namespace App\Modules\Quality\Services;

use App\Models\User;
use App\Modules\Quality\Models\Capa;
use App\Modules\Quality\Models\CapaAction;
use App\Modules\Quality\Models\Deviation;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class CapaService
{
    public function __construct(
        protected QualityNumberingService $numberingService
    ) {}

    /**
     * Get paginated CAPA list with filters.
     *
     * @param  array<string, mixed>  $filters
     * @return LengthAwarePaginator<Capa>
     */
    public function getCapas(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $query = Capa::with(['deviation', 'initiator', 'pic', 'actions.pic'])
            ->orderByDesc('id');

        if (! empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('capa_number', 'like', "%{$search}%")
                    ->orWhere('deviation_number_ref', 'like', "%{$search}%")
                    ->orWhere('tindakan_capa', 'like', "%{$search}%");
            });
        }

        if (! empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        return $query->paginate($perPage);
    }

    /**
     * Automatically spawn a CAPA record from an approved Deviation.
     */
    public function createFromDeviation(Deviation $deviation): Capa
    {
        return DB::transaction(function () use ($deviation) {
            // Check if CAPA already spawned for this deviation
            $existing = Capa::where('deviation_id', $deviation->id)->first();
            if ($existing) {
                return $existing;
            }

            $capaNumber = $this->numberingService->generateCapaNumber();

            $capa = Capa::create([
                'deviation_id' => $deviation->id,
                'initiator_id' => $deviation->initiator_id,
                'pic_id' => null,
                'capa_number' => $capaNumber,
                'deviation_number_ref' => $deviation->deviation_number,
                'tanggal_penyimpangan' => $deviation->tanggal_temuan ?? Carbon::today(),
                'tindakan_capa' => $deviation->immediate_action_details ?? 'Tindakan perbaikan dan pencegahan atas deviasi '.$deviation->topik,
                'status' => 'DRAFT',
            ]);

            // If deviation had evaluasi_tindakan entries from FMEA, auto populate actions
            $evaluasi = $deviation->evaluasi_tindakan;
            if (is_array($evaluasi) && count($evaluasi) > 0) {
                foreach ($evaluasi as $idx => $ev) {
                    if (! empty($ev['tindakan'])) {
                        $this->addAction($capa, [
                            'tindakan' => $ev['tindakan'],
                            'jenis_tindakan' => $idx === 0 ? 'korektif' : 'preventif',
                            'department' => $deviation->department,
                            'deadline' => Carbon::now()->addDays(14)->toDateString(),
                        ]);
                    }
                }
                $capa->status = 'IN PROGRESS';
                $capa->save();
            }

            return $capa->fresh(['actions', 'deviation']);
        });
    }

    /**
     * Add a Sub-Action to CAPA.
     *
     * @param  array<string, mixed>  $data
     */
    public function addAction(Capa $capa, array $data): CapaAction
    {
        return DB::transaction(function () use ($capa, $data) {
            $actionNumber = $this->numberingService->generateCapaActionNumber($capa);

            $action = CapaAction::create([
                'capa_id' => $capa->id,
                'pic_id' => $data['pic_id'] ?? null,
                'action_number' => $actionNumber,
                'tindakan' => $data['tindakan'],
                'jenis_tindakan' => $data['jenis_tindakan'] ?? 'korektif',
                'department' => $data['department'] ?? 'QA',
                'deadline' => $data['deadline'] ?? Carbon::now()->addDays(14)->toDateString(),
                'status' => 'PENDING',
            ]);

            if ($capa->status === 'DRAFT') {
                $capa->update(['status' => 'IN PROGRESS']);
            }

            return $action;
        });
    }

    /**
     * Field PIC submits completion proof for a CAPA Action.
     */
    public function submitActionProof(
        CapaAction $action,
        string $proofPath,
        ?string $notes = null,
        ?User $user = null
    ): CapaAction {
        return DB::transaction(function () use ($action, $proofPath, $notes) {
            $action->update([
                'bukti_file_path' => $proofPath,
                'catatan_pelaksanaan' => $notes,
                'status' => 'SUBMITTED',
            ]);

            // Check if all actions under this CAPA have been submitted
            $capa = $action->capa;
            $pendingCount = $capa->actions()->where('status', 'PENDING')->count();
            if ($pendingCount === 0) {
                // All actions submitted, ready for QA review
                $capa->update(['status' => 'APPROVED']);
            }

            return $action->fresh();
        });
    }

    /**
     * QA Verifies CAPA effectiveness and closes or rejects.
     */
    public function verifyCapa(
        Capa $capa,
        string $decision, // 'CLOSE' or 'REJECTED'
        string $verificationNotes,
        User $qaUser
    ): Capa {
        return DB::transaction(function () use ($capa, $decision, $verificationNotes, $qaUser) {
            if ($decision === 'CLOSE') {
                $capa->update([
                    'status' => 'CLOSE',
                    'hasil_verifikasi_qa' => $verificationNotes,
                ]);

                // Mark all actions as verified
                $capa->actions()->update([
                    'status' => 'VERIFIED',
                    'verified_at' => Carbon::now(),
                    'verified_by_user_id' => $qaUser->id,
                ]);
            } else {
                // Return to IN PROGRESS for revision
                $capa->update([
                    'status' => 'IN PROGRESS',
                    'hasil_verifikasi_qa' => 'Revisi Diminta: '.$verificationNotes,
                ]);

                $capa->actions()->where('status', 'SUBMITTED')->update([
                    'status' => 'REVISE',
                ]);
            }

            return $capa->fresh(['actions.pic', 'deviation']);
        });
    }
}
