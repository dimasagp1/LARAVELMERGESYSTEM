<?php

namespace App\Modules\Production\Services;

use App\Modules\Production\Models\LaporanHarian;
use App\Modules\Production\Models\RejectDetail;
use Illuminate\Support\Facades\DB;

class RejectScrapService
{
    public function __construct(
        protected OeeCalculationService $oeeService
    ) {}

    /**
     * Record a reject/scrap event from the shop floor and recalculate batch yield.
     *
     * @param  array{jenis_reject: string, material_name?: string|null, jumlah: float|int, satuan?: string, biaya_satuan?: float|int, keterangan?: string|null}  $data
     */
    public function recordReject(int $reportId, array $data): RejectDetail
    {
        return DB::transaction(function () use ($reportId, $data) {
            $report = LaporanHarian::findOrFail($reportId);

            $jumlah = (float) ($data['jumlah'] ?? 1);
            $biayaSatuan = (float) ($data['biaya_satuan'] ?? 1500.00);
            $totalNilai = round($jumlah * $biayaSatuan, 2);

            // Mock / Real Odoo Scrap Sync Reference
            $odooScrapId = rand(5000, 9999);

            $reject = RejectDetail::create([
                'laporan_harian_id' => $report->id,
                'odoo_scrap_id' => $odooScrapId,
                'odoo_mo_id' => $report->weeklyPlan?->odoo_mo_id,
                'jenis_reject' => $data['jenis_reject'],
                'material_name' => $data['material_name'] ?? null,
                'jumlah' => $jumlah,
                'satuan' => $data['satuan'] ?? 'PCS',
                'biaya_satuan' => $biayaSatuan,
                'total_nilai_scrap' => $totalNilai,
                'keterangan' => $data['keterangan'] ?? null,
            ]);

            // Update total_reject on LaporanHarian and recompute Yield & OEE
            $newTotalReject = (int) $report->rejectDetails()->sum('jumlah');

            $this->oeeService->calculateOeeMetrics(
                reportId: $report->id,
                capacityFisik: (int) $report->capacity_fisik,
                outputFisik: (int) $report->output_fisik,
                totalReject: $newTotalReject
            );

            return $reject;
        });
    }

    /**
     * Get aggregate reject analytics per defect type.
     *
     * @return array<string, mixed>
     */
    public function getScrapAnalysis(): array
    {
        $rejects = RejectDetail::select(
            'jenis_reject',
            DB::raw('count(*) as count'),
            DB::raw('sum(jumlah) as total_qty'),
            DB::raw('sum(total_nilai_scrap) as total_loss')
        )
            ->groupBy('jenis_reject')
            ->orderByDesc('total_qty')
            ->get();

        $totalLossRupiah = (float) RejectDetail::sum('total_nilai_scrap');
        $totalItemsScrapped = (float) RejectDetail::sum('jumlah');

        return [
            'total_items_scrapped' => $totalItemsScrapped,
            'total_loss_rupiah' => $totalLossRupiah,
            'defects_breakdown' => $rejects,
        ];
    }
}
