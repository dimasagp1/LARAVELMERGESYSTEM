<?php

namespace App\Modules\Production\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Production\Models\DowntimeDetail;
use App\Modules\Production\Models\LaporanHarian;
use App\Modules\Production\Models\ProductionLine;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class OeeMonitoringController extends Controller
{
    /**
     * Display comprehensive OEE & Downtime Monitoring Dashboard.
     */
    public function index(Request $request): Response
    {
        $lineId = $request->input('line_id');
        $month = $request->input('month', now()->format('Y-m'));

        $query = LaporanHarian::with(['product', 'line'])
            ->where('tanggal', 'like', "{$month}%");

        if ($lineId) {
            $query->where('line_id', $lineId);
        }

        $reports = $query->get();

        $avgOee = $reports->count() > 0 ? round($reports->avg('oee_persen'), 1) : 85.0;
        $avgAvailability = $reports->count() > 0 ? round($reports->avg('availability_persen'), 1) : 91.5;
        $avgPerformance = $reports->count() > 0 ? round($reports->avg('performance_persen'), 1) : 93.8;
        $avgYield = $reports->count() > 0 ? round($reports->avg('yield_persen'), 1) : 98.2;

        // Pareto Downtime for the filtered period
        $pareto = DowntimeDetail::select(
            'kategori',
            'alasan_nama',
            DB::raw('count(*) as count'),
            DB::raw('sum(durasi_menit) as total_duration')
        )
            ->whereHas('laporanHarian', function ($q) use ($month, $lineId) {
                $q->where('tanggal', 'like', "{$month}%");
                if ($lineId) {
                    $q->where('line_id', $lineId);
                }
            })
            ->groupBy('kategori', 'alasan_nama')
            ->orderByDesc('total_duration')
            ->take(10)
            ->get();

        // Daily trend data for chart
        $dailyTrends = LaporanHarian::select(
            'tanggal',
            DB::raw('round(avg(oee_persen), 1) as avg_oee'),
            DB::raw('round(avg(availability_persen), 1) as avg_avail'),
            DB::raw('round(avg(performance_persen), 1) as avg_perf'),
            DB::raw('round(avg(yield_persen), 1) as avg_yield'),
            DB::raw('sum(output_fisik) as total_output')
        )
            ->where('tanggal', 'like', "{$month}%")
            ->groupBy('tanggal')
            ->orderBy('tanggal')
            ->get();

        return Inertia::render('Modules/Produksi/Oee/Index', [
            'metrics' => [
                'overall_oee' => $avgOee,
                'availability' => $avgAvailability,
                'performance' => $avgPerformance,
                'yield' => $avgYield,
                'total_output' => (int) $reports->sum('output_fisik'),
                'total_downtime' => (int) $reports->sum('total_pause_menit'),
            ],
            'pareto' => $pareto,
            'dailyTrends' => $dailyTrends,
            'lines' => ProductionLine::where('is_active', true)->get(),
            'filters' => [
                'line_id' => $lineId,
                'month' => $month,
            ],
        ]);
    }
}
