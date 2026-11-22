<?php

namespace App\Modules\Production\Services;

use App\Models\User;
use App\Modules\Production\Models\WeeklyPlan;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class WpsScheduleService
{
    public function __construct(
        protected ProductionNumberingService $numberingService
    ) {}

    /**
     * Create a Weekly Production Schedule (WPS) with Odoo guard & linear chain validation.
     *
     * @param  array<string, mixed>  $data
     */
    public function createWeeklyPlan(array $data, ?int $userId = null): WeeklyPlan
    {
        return DB::transaction(function () use ($data, $userId) {
            $creator = $userId ?? (auth()->id() ?? User::first()?->id ?? 1);

            // 1. Odoo MO Status Guard: Optional if manual, validate if cancelled or sync from Odoo
            $moStatus = strtolower((string) ($data['mo_status'] ?? 'confirmed'));
            if ($moStatus === 'cancelled' || (! empty($data['odoo_mo_id']) && ! in_array($moStatus, ['confirmed', 'manual']))) {
                throw new InvalidArgumentException("Jadwal produksi tidak dapat dibuat: Status MO Odoo harus berstatus 'confirmed' (ditemukan: {$moStatus}).");
            }

            $proses = strtolower((string) ($data['proses'] ?? 'mixing'));
            $produkId = (int) $data['produk_id'];
            $batchNumber = trim((string) $data['batch_number']);
            $tanggal = $data['tanggal'];

            // 2. Estafet Validation (Mixing -> Filling -> Packing)
            if ($proses === 'filling') {
                $hasMixing = WeeklyPlan::where('produk_id', $produkId)
                    ->where('batch_number', $batchNumber)
                    ->where('proses', 'mixing')
                    ->exists();

                if (! $hasMixing) {
                    throw new InvalidArgumentException("Proses Filling untuk Batch {$batchNumber} tidak dapat dijadwalkan sebelum proses Mixing terdaftar.");
                }
            } elseif ($proses === 'packing') {
                $hasFilling = WeeklyPlan::where('produk_id', $produkId)
                    ->where('batch_number', $batchNumber)
                    ->where('proses', 'filling')
                    ->exists();

                if (! $hasFilling) {
                    throw new InvalidArgumentException("Proses Packing untuk Batch {$batchNumber} tidak dapat dijadwalkan sebelum proses Filling terdaftar.");
                }
            }

            // 3. Limit: Maksimal 3 proses terdaftar per produk dalam 1 tanggal kerja yang sama
            $existingProcessCount = WeeklyPlan::where('produk_id', $produkId)
                ->where('tanggal', $tanggal)
                ->count();

            if ($existingProcessCount >= 3) {
                throw new InvalidArgumentException("Maksimal 3 proses telah tercapai untuk produk ini pada tanggal {$tanggal}.");
            }

            // 4. Auto-Calculate Target Output
            $mpCount = (int) ($data['mp_count'] ?? 10);
            $multiplier = (int) ($data['multiplier'] ?? 2000);
            $targetOutput = ! empty($data['target_output'])
                ? (int) $data['target_output']
                : ($mpCount * $multiplier);

            $planNumber = $this->numberingService->generateWpsNumber();

            return WeeklyPlan::create([
                'plan_number' => $planNumber,
                'produk_id' => $produkId,
                'line_id' => $data['line_id'],
                'proses' => $proses,
                'batch_number' => $batchNumber,
                'odoo_mo_id' => $data['odoo_mo_id'] ?? null,
                'mo_status' => 'confirmed',
                'target_output' => $targetOutput,
                'mp_count' => $mpCount,
                'multiplier' => $multiplier,
                'packing_hold' => (bool) ($data['packing_hold'] ?? false),
                'tanggal' => $tanggal,
                'shift' => $data['shift'] ?? 'shift1',
                'status' => $data['status'] ?? 'aktif',
                'notes' => $data['notes'] ?? null,
                'created_by' => $creator,
                'custom_attributes' => $data['custom_attributes'] ?? null,
            ]);
        });
    }

    /**
     * Toggle Packing Hold feature (Penundaan pengemasan sekunder).
     */
    public function togglePackingHold(int $planId, bool $isHold, ?string $reason = null): WeeklyPlan
    {
        return DB::transaction(function () use ($planId, $isHold, $reason) {
            $plan = WeeklyPlan::findOrFail($planId);

            $notes = $plan->notes ?? '';
            if ($reason) {
                $statusText = $isHold ? 'DI-HOLD' : 'DI-RESUME';
                $notes .= "\n[".now()->format('Y-m-d H:i')."] Packing {$statusText}: {$reason}";
            }

            $plan->update([
                'packing_hold' => $isHold,
                'notes' => trim($notes),
            ]);

            return $plan;
        });
    }

    /**
     * Synchronize with Odoo MO status updates.
     */
    public function syncOdooMo(int $odooMoId, string $status): int
    {
        return DB::transaction(function () use ($odooMoId, $status) {
            $targetStatus = strtolower($status) === 'cancelled' ? 'cancelled' : 'aktif';

            return WeeklyPlan::where('odoo_mo_id', $odooMoId)->update([
                'mo_status' => $status,
                'status' => $targetStatus,
            ]);
        });
    }
}
