<?php

namespace App\Modules\Production\Services;

use App\Modules\Production\Models\DowntimeDetail;
use App\Modules\Production\Models\LaporanHarian;
use App\Modules\Production\Models\ProductionDowntimeReason;
use App\Modules\Production\Models\WeeklyPlan;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class ProductionTimerExecutionService
{
    public function __construct(
        protected ProductionNumberingService $numberingService
    ) {}

    /**
     * Start the real-time production timer.
     */
    public function startTimer(int $reportId, ?string $startTime = null): LaporanHarian
    {
        return DB::transaction(function () use ($reportId, $startTime) {
            $report = LaporanHarian::findOrFail($reportId);

            if ($report->is_locked) {
                throw new InvalidArgumentException('Laporan telah dikunci oleh Supervisor dan tidak dapat diubah.');
            }

            $nowTime = $startTime ?? now()->format('H:i:s');

            $report->update([
                'start_time' => $report->start_time ?? $nowTime,
                'timer_status' => 'start',
                'pause_started_at' => null,
            ]);

            return $report->fresh(['weeklyPlan', 'downtimeDetails', 'rejectDetails']);
        });
    }

    /**
     * Pause the production timer during line stops / bottlenecks.
     */
    public function pauseTimer(int $reportId, ?string $pauseTime = null): LaporanHarian
    {
        return DB::transaction(function () use ($reportId, $pauseTime) {
            $report = LaporanHarian::findOrFail($reportId);

            if ($report->is_locked) {
                throw new InvalidArgumentException('Laporan telah dikunci oleh Supervisor.');
            }

            $pauseDt = $pauseTime ? Carbon::parse($pauseTime) : now();

            $report->update([
                'timer_status' => 'pause',
                'pause_started_at' => $pauseDt,
            ]);

            return $report->fresh(['weeklyPlan', 'downtimeDetails', 'rejectDetails']);
        });
    }

    /**
     * Resume production with MANDATORY downtime reason & duration tracking.
     *
     * @param  array{alasan_downtime_id?: int|null, kategori?: string, alasan_nama?: string, durasi_menit?: float|int, keterangan?: string|null}  $downtimeData
     */
    public function resumeTimer(int $reportId, array $downtimeData): LaporanHarian
    {
        return DB::transaction(function () use ($reportId, $downtimeData) {
            $report = LaporanHarian::findOrFail($reportId);

            if ($report->is_locked) {
                throw new InvalidArgumentException('Laporan telah dikunci oleh Supervisor.');
            }

            // Calculate elapsed downtime in minutes
            $elapsedMinutes = 0.0;
            if ($report->pause_started_at) {
                $seconds = now()->diffInSeconds($report->pause_started_at);
                $elapsedMinutes = max(1.0, round($seconds / 60, 2));
            }

            // Allow manual override if specified and positive
            if (! empty($downtimeData['durasi_menit']) && (float) $downtimeData['durasi_menit'] > 0) {
                $elapsedMinutes = (float) $downtimeData['durasi_menit'];
            }

            // Resolve Reason Name & Category
            $reasonId = $downtimeData['alasan_downtime_id'] ?? null;
            $category = $downtimeData['kategori'] ?? 'mesin';
            $reasonName = $downtimeData['alasan_nama'] ?? null;

            if ($reasonId) {
                $reasonModel = ProductionDowntimeReason::find($reasonId);
                if ($reasonModel) {
                    $category = $reasonModel->category ?? $category;
                    $reasonName = $reasonName ?: $reasonModel->name;
                }
            }

            if (empty($reasonName)) {
                $reasonName = 'Kendala Operasional / Jeda Mesin';
            }

            // Record Downtime Detail
            DowntimeDetail::create([
                'laporan_harian_id' => $report->id,
                'alasan_downtime_id' => $reasonId,
                'kategori' => $category,
                'alasan_nama' => $reasonName,
                'durasi_menit' => $elapsedMinutes,
                'keterangan' => $downtimeData['keterangan'] ?? null,
            ]);

            $totalPause = $report->total_pause_menit + (int) ceil($elapsedMinutes);

            $report->update([
                'timer_status' => 'start',
                'pause_started_at' => null,
                'total_pause_menit' => $totalPause,
            ]);

            return $report->fresh(['weeklyPlan', 'downtimeDetails', 'rejectDetails']);
        });
    }

    /**
     * End production timer, compute gross & net time, and AUTO-CHAIN next stage.
     */
    public function endTimer(int $reportId, ?string $endTime = null): LaporanHarian
    {
        return DB::transaction(function () use ($reportId, $endTime) {
            $report = LaporanHarian::with(['weeklyPlan'])->findOrFail($reportId);

            if ($report->is_locked) {
                throw new InvalidArgumentException('Laporan telah dikunci oleh Supervisor.');
            }

            $endT = $endTime ?? now()->format('H:i:s');
            $report->end_time = $endT;
            $report->timer_status = 'end';

            // Calculate Gross Time (Jam Selesai - Jam Mulai)
            $startStr = $report->start_time ?? now()->subHours(8)->format('H:i:s');
            $startDate = Carbon::parse($report->tanggal->format('Y-m-d').' '.$startStr);
            $endDate = Carbon::parse($report->tanggal->format('Y-m-d').' '.$endT);

            if ($endDate->lt($startDate)) {
                $endDate->addDay();
            }

            $grossMinutes = max(1, $startDate->diffInMinutes($endDate));
            $netMinutes = max(0, $grossMinutes - $report->total_pause_menit);

            $report->gross_time_menit = $grossMinutes;
            $report->waktu_bersih_menit = $netMinutes;
            $report->save();

            // Mark current WeeklyPlan as completed
            if ($report->weeklyPlan) {
                $report->weeklyPlan->update(['status' => 'selesai']);
            }

            // Auto-chaining: If Mixing -> create Filling draft; if Filling -> create Packing draft
            $this->autoChainNextStage($report);

            return $report->fresh(['weeklyPlan', 'downtimeDetails', 'rejectDetails']);
        });
    }

    /**
     * Auto-chain the next production process in the linear sequence.
     */
    protected function autoChainNextStage(LaporanHarian $report): ?LaporanHarian
    {
        $currentProcess = $report->proses;
        $nextProcess = match ($currentProcess) {
            'mixing' => 'filling',
            'filling' => 'packing',
            default => null,
        };

        if (! $nextProcess) {
            return null;
        }

        // Find or create next WeeklyPlan for this batch
        $nextPlan = WeeklyPlan::where('produk_id', $report->produk_id)
            ->where('batch_number', $report->batch_number)
            ->where('proses', $nextProcess)
            ->first();

        if (! $nextPlan) {
            // If packing, check if parent had packing_hold
            $isPackingHold = false;
            if ($nextProcess === 'packing' && $report->weeklyPlan) {
                $isPackingHold = (bool) $report->weeklyPlan->packing_hold;
            }

            $nextPlan = WeeklyPlan::create([
                'plan_number' => $this->numberingService->generateWpsNumber(),
                'produk_id' => $report->produk_id,
                'line_id' => $report->line_id,
                'proses' => $nextProcess,
                'batch_number' => $report->batch_number,
                'odoo_mo_id' => $report->weeklyPlan?->odoo_mo_id,
                'mo_status' => 'confirmed',
                'target_output' => $report->weeklyPlan?->target_output ?? 20000,
                'mp_count' => $report->weeklyPlan?->mp_count ?? 10,
                'multiplier' => $report->weeklyPlan?->multiplier ?? 2000,
                'packing_hold' => $isPackingHold,
                'tanggal' => now()->toDateString(),
                'shift' => $report->shift,
                'status' => 'aktif',
                'notes' => "Auto-chained dari proses {$currentProcess} (LHP: {$report->report_number})",
                'created_by' => $report->user_id,
            ]);
        }

        // Check if draft report already exists for next plan
        $existingNextReport = LaporanHarian::where('weekly_plan_id', $nextPlan->id)->first();
        if ($existingNextReport) {
            return $existingNextReport;
        }

        // Auto-create initial draft report
        return LaporanHarian::create([
            'report_number' => $this->numberingService->generateReportNumber(),
            'weekly_plan_id' => $nextPlan->id,
            'produk_id' => $report->produk_id,
            'line_id' => $report->line_id,
            'mesin_id' => $report->mesin_id,
            'user_id' => $report->user_id,
            'tanggal' => now()->toDateString(),
            'shift' => $report->shift,
            'batch_number' => $report->batch_number,
            'proses' => $nextProcess,
            'ct' => $report->ct,
            'timer_status' => 'draft',
            'status' => 'draft',
            'notes' => "Estafet lanjutan dari LHP {$report->report_number}",
        ]);
    }
}
