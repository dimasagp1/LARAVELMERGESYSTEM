<?php

namespace App\Modules\Production\Services;

use App\Modules\Production\Models\LaporanHarian;
use Illuminate\Support\Facades\DB;

class OeeCalculationService
{
    /**
     * Compute OEE, Availability, Performance, Yield, and Productivity metrics according to Sasaran Mutu formulas.
     */
    public function calculateOeeMetrics(
        int $reportId,
        int $capacityFisik,
        int $outputFisik,
        int $totalReject = 0
    ): LaporanHarian {
        return DB::transaction(function () use ($reportId, $capacityFisik, $outputFisik, $totalReject) {
            $report = LaporanHarian::with(['weeklyPlan'])->findOrFail($reportId);

            $grossTime = max(1, (int) $report->gross_time_menit);
            if ($grossTime <= 0) {
                $grossTime = 480; // default 8 jam kerja (480 menit) jika belum di-end
            }

            $totalDowntime = (int) $report->total_pause_menit;
            $waktuBersih = max(0, $grossTime - $totalDowntime);

            // 1. Availability (%) = (Waktu Bersih / Gross Time) * 100
            $availability = $grossTime > 0
                ? round(($waktuBersih / $grossTime) * 100, 2)
                : 100.0;
            $availability = min(100.0, max(0.0, $availability));

            // 2. Performance (%) = ((Output Fisik * Cycle Time Detik / 60) / Waktu Bersih Menit) * 100
            $ctDetik = (float) ($report->ct > 0 ? $report->ct : 1.20);
            $waktuTeoritisMenit = ($outputFisik * $ctDetik) / 60;

            if ($waktuBersih > 0) {
                $performance = round(($waktuTeoritisMenit / $waktuBersih) * 100, 2);
            } else {
                $performance = 100.0;
            }
            $performance = min(120.0, max(0.0, $performance)); // Normalisasi cap 120%

            // 3. Quality / Yield (%) = (Output Lolos / (Output Lolos + Total Reject)) * 100
            $totalProcessed = $outputFisik + $totalReject;
            if ($totalProcessed > 0) {
                $yield = round(($outputFisik / $totalProcessed) * 100, 2);
            } else {
                $yield = 100.0;
            }
            $yield = min(100.0, max(0.0, $yield));

            // 4. Overall Equipment Effectiveness (OEE %)
            $oee = round(($availability / 100) * ($performance / 100) * ($yield / 100) * 100, 2);

            // 5. Man Power Productivity (%) = (Output Fisik / (Target per MP * Total MP)) * 100
            $mpCount = (int) ($report->weeklyPlan?->mp_count ?? 10);
            $multiplier = (int) ($report->weeklyPlan?->multiplier ?? 2000);
            $targetOutputTotal = $mpCount * $multiplier;

            if ($targetOutputTotal > 0) {
                $productivity = round(($outputFisik / $targetOutputTotal) * 100, 2);
            } else {
                $productivity = 100.0;
            }

            // Update Report Record
            $report->update([
                'capacity_fisik' => $capacityFisik,
                'output_fisik' => $outputFisik,
                'total_reject' => $totalReject,
                'waktu_bersih_menit' => $waktuBersih,
                'availability_persen' => $availability,
                'performance_persen' => $performance,
                'yield_persen' => $yield,
                'oee_persen' => $oee,
                'produktivitas_persen' => $productivity,
            ]);

            return $report->fresh(['weeklyPlan', 'downtimeDetails', 'rejectDetails']);
        });
    }

    /**
     * Lock and officially approve a Daily Report (Supervisor level).
     */
    public function lockReport(int $reportId, int $lockedById): LaporanHarian
    {
        return DB::transaction(function () use ($reportId, $lockedById) {
            $report = LaporanHarian::findOrFail($reportId);

            $report->update([
                'status' => 'locked',
                'locked_by' => $lockedById,
                'locked_at' => now(),
            ]);

            return $report->fresh(['weeklyPlan', 'operator', 'lockedBy']);
        });
    }
}
