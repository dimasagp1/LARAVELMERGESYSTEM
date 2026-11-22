<?php

namespace App\Modules\Procurement\Services;

use App\Modules\Procurement\Models\ItemStandardPrice;
use App\Modules\Procurement\Models\PrItem;
use App\Modules\Procurement\Models\ProductionLossTimeLog;
use Carbon\Carbon;

class KpiQualityService
{
    /**
     * Calculate 4 Sasaran Mutu 2026 KPIs and composite score.
     */
    public function calculateQualityTargets(?int $year = null, ?int $month = null): array
    {
        $year = $year ?: (int) Carbon::now()->format('Y');

        // 1. Sasaran Mutu 1: Ketepatan Pengadaan Material (Loss Time 0) - Weight 25%
        $lossLogs = ProductionLossTimeLog::whereYear('log_date', $year)->get();
        $totalLossValue = $lossLogs->sum('loss_value');
        $targetRevenue = $lossLogs->avg('target_revenue') ?: 5000000000; // 5 Billion target

        $lossRatioPct = $targetRevenue > 0 ? ($totalLossValue / $targetRevenue) * 100 : 0;

        $scoreKpi1 = match (true) {
            $lossRatioPct <= 0 => 100,
            $lossRatioPct < 1.0 => 90,
            $lossRatioPct < 2.0 => 80,
            $lossRatioPct < 3.0 => 70,
            default => 50,
        };

        // 2. Sasaran Mutu 2: Ketepatan Harga Beli Material - Weight 25%
        $materialStandards = ItemStandardPrice::where('category_type', 'material')
            ->where('effective_year', $year)
            ->where('is_active', true)
            ->get()
            ->keyBy('item_code');

        $materialItems = PrItem::whereHas('purchaseRequest', function ($q) use ($year) {
            $q->whereYear('request_date', $year);
        })->where('actual_price', '>', 0)
            ->whereNotNull('item_code')
            ->get();

        $matDeviations = [];
        foreach ($materialItems as $item) {
            if ($std = $materialStandards->get($item->item_code)) {
                $unitActual = (float) $item->actual_price / max(1, (float) $item->quantity);
                $unitStd = (float) $std->standard_price;
                if ($unitStd > 0) {
                    $devPct = (($unitActual - $unitStd) / $unitStd) * 100;
                    $matDeviations[] = $devPct;
                }
            }
        }

        $avgMatDev = count($matDeviations) > 0 ? array_sum($matDeviations) / count($matDeviations) : 0;
        $scoreKpi2 = match (true) {
            $avgMatDev <= 0 => 100,
            $avgMatDev <= 3 => 90,
            $avgMatDev <= 5 => 80,
            $avgMatDev <= 10 => 70,
            default => 60,
        };

        // 3. Sasaran Mutu 3: Ketepatan Harga Beli Supporting - Weight 25%
        $supportStandards = ItemStandardPrice::where('category_type', 'supporting')
            ->where('effective_year', $year)
            ->where('is_active', true)
            ->get()
            ->keyBy('item_code');

        $supportItems = PrItem::whereHas('purchaseRequest', function ($q) use ($year) {
            $q->whereYear('request_date', $year);
        })->where('actual_price', '>', 0)
            ->whereNotNull('item_code')
            ->get();

        $supDeviations = [];
        foreach ($supportItems as $item) {
            if ($std = $supportStandards->get($item->item_code)) {
                $unitActual = (float) $item->actual_price / max(1, (float) $item->quantity);
                $unitStd = (float) $std->standard_price;
                if ($unitStd > 0) {
                    $supDeviations[] = (($unitActual - $unitStd) / $unitStd) * 100;
                }
            }
        }

        $avgSupDev = count($supDeviations) > 0 ? array_sum($supDeviations) / count($supDeviations) : 0;
        $scoreKpi3 = match (true) {
            $avgSupDev <= 0 => 100,
            $avgSupDev <= 3 => 90,
            $avgSupDev <= 5 => 80,
            $avgSupDev <= 10 => 70,
            default => 60,
        };

        // 4. Sasaran Mutu 4: Efisiensi & Belanja Darurat (Bon) - Weight 25%
        $totalSpend = PrItem::whereHas('purchaseRequest', function ($q) use ($year) {
            $q->whereYear('request_date', $year);
        })->sum('actual_price');

        $bonSpend = PrItem::whereHas('purchaseRequest', function ($q) use ($year) {
            $q->whereYear('request_date', $year);
        })->where('is_bon', true)->sum('actual_price');

        $bonRatioPct = $totalSpend > 0 ? ($bonSpend / $totalSpend) * 100 : 5.0;
        $scoreKpi4 = match (true) {
            $bonRatioPct <= 10 => 100,
            $bonRatioPct <= 15 => 90,
            $bonRatioPct <= 20 => 80,
            $bonRatioPct <= 25 => 70,
            default => 50,
        };

        // Composite Weighted Score
        $compositeScore = round(
            ($scoreKpi1 * 0.25) +
            ($scoreKpi2 * 0.25) +
            ($scoreKpi3 * 0.25) +
            ($scoreKpi4 * 0.25),
            1
        );

        return [
            'fiscal_year' => $year,
            'composite_score' => $compositeScore,
            'grade' => $compositeScore >= 90 ? 'A (Sangat Baik)' : ($compositeScore >= 80 ? 'B (Baik)' : 'C (Perlu Perbaikan)'),
            'kpis' => [
                [
                    'id' => 1,
                    'title' => 'Ketepatan Pengadaan Material (Loss Time 0)',
                    'weight' => 25,
                    'target' => 'NC 0% (Downtime 0 Jam)',
                    'actual' => sprintf('Loss: Rp %s (%.2f%% Revenue)', number_format($totalLossValue, 0, ',', '.'), $lossRatioPct),
                    'score' => $scoreKpi1,
                    'status' => $scoreKpi1 >= 90 ? 'achieved' : 'warning',
                ],
                [
                    'id' => 2,
                    'title' => 'Ketepatan Harga Beli Material (vs Standar)',
                    'weight' => 25,
                    'target' => 'Deviasi <= 0% (Sesuai/Lebih Murah)',
                    'actual' => sprintf('Rata-rata Deviasi: %+.2f%%', $avgMatDev),
                    'score' => $scoreKpi2,
                    'status' => $scoreKpi2 >= 90 ? 'achieved' : 'warning',
                ],
                [
                    'id' => 3,
                    'title' => 'Ketepatan Harga Beli Supporting (vs Standar)',
                    'weight' => 25,
                    'target' => 'Deviasi <= 0%',
                    'actual' => sprintf('Rata-rata Deviasi: %+.2f%%', $avgSupDev),
                    'score' => $scoreKpi3,
                    'status' => $scoreKpi3 >= 90 ? 'achieved' : 'warning',
                ],
                [
                    'id' => 4,
                    'title' => 'Efisiensi Pengadaan & Realisasi Belanja Darurat (Bon)',
                    'weight' => 25,
                    'target' => 'Porsi Belanja Bon <= 15%',
                    'actual' => sprintf('Belanja Bon: Rp %s (%.1f%%)', number_format($bonSpend, 0, ',', '.'), $bonRatioPct),
                    'score' => $scoreKpi4,
                    'status' => $scoreKpi4 >= 90 ? 'achieved' : 'warning',
                ],
            ],
        ];
    }
}
