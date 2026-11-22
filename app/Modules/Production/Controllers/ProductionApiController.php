<?php

namespace App\Modules\Production\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Production\Models\DowntimeDetail;
use App\Modules\Production\Models\LaporanHarian;
use App\Modules\Production\Models\RejectDetail;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProductionApiController extends Controller
{
    /**
     * Provide monthly OEE and operational performance metrics for HRIS Sasaran Mutu.
     * Endpoint: /api/v1/production/metrics
     */
    public function metrics(Request $request): JsonResponse
    {
        $month = $request->input('month', now()->format('Y-m'));

        $reports = LaporanHarian::where('tanggal', 'like', "{$month}%")->get();

        $count = $reports->count();
        $avgOee = $count > 0 ? round($reports->avg('oee_persen'), 2) : 85.0;
        $avgAvailability = $count > 0 ? round($reports->avg('availability_persen'), 2) : 92.0;
        $avgPerformance = $count > 0 ? round($reports->avg('performance_persen'), 2) : 94.0;
        $avgYield = $count > 0 ? round($reports->avg('yield_persen'), 2) : 98.5;
        $avgProductivity = $count > 0 ? round($reports->avg('produktivitas_persen'), 2) : 100.0;

        $totalDowntimeHours = round($reports->sum('total_pause_menit') / 60, 2);
        $totalOutput = (int) $reports->sum('output_fisik');

        return response()->json([
            'status' => 'success',
            'period' => $month,
            'summary' => [
                'batch_count' => $count,
                'avg_oee_percent' => $avgOee,
                'avg_availability_percent' => $avgAvailability,
                'avg_performance_percent' => $avgPerformance,
                'avg_yield_percent' => $avgYield,
                'avg_productivity_percent' => $avgProductivity,
                'total_output_units' => $totalOutput,
                'total_downtime_hours' => $totalDowntimeHours,
            ],
            'timestamp' => now()->toIso8601String(),
        ]);
    }

    /**
     * Provide financial loss metrics (downtime cost, scrap loss) for Finance Feed.
     * Endpoint: /api/v1/production/finance-feed
     */
    public function financeFeed(Request $request): JsonResponse
    {
        $month = $request->input('month', now()->format('Y-m'));

        $totalScrapLoss = (float) RejectDetail::whereHas('laporanHarian', function ($q) use ($month) {
            $q->where('tanggal', 'like', "{$month}%");
        })->sum('total_nilai_scrap');

        $totalDowntimeMinutes = (float) DowntimeDetail::whereHas('laporanHarian', function ($q) use ($month) {
            $q->where('tanggal', 'like', "{$month}%");
        })->sum('durasi_menit');

        // Standard financial loss calculation: Rp 50.000 / hour machine stop rate
        $estimatedDowntimeLoss = round(($totalDowntimeMinutes / 60) * 150000, 2);

        return response()->json([
            'status' => 'success',
            'period' => $month,
            'feed' => [
                'total_scrap_loss_rupiah' => $totalScrapLoss,
                'total_downtime_minutes' => $totalDowntimeMinutes,
                'estimated_downtime_loss_rupiah' => $estimatedDowntimeLoss,
                'total_manufacturing_loss_rupiah' => $totalScrapLoss + $estimatedDowntimeLoss,
            ],
            'timestamp' => now()->toIso8601String(),
        ]);
    }
}
