<?php

namespace App\Core\Integrations\Contracts;

use App\Models\User;
use App\Modules\Production\Models\LaporanHarian;
use App\Modules\Quality\Models\Deviation;

interface ProductionIntegrationContract
{
    /**
     * Spawn a QMS Deviation Report draft from a critical reject or downtime in Laporan Harian Produksi.
     *
     * @param  array<string, mixed>  $overrideData
     */
    public function spawnQualityDeviation(LaporanHarian $laporanHarian, array $overrideData = [], ?User $user = null): Deviation;

    /**
     * Synchronize production output, downtime, and material usage to Finance P&L and HRIS KPIs.
     *
     * @return array<string, mixed>
     */
    public function syncProductionBatchToFinanceAndHris(LaporanHarian $laporanHarian, ?User $user = null): array;
}
