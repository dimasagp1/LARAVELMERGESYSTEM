<?php

namespace App\Core\Integrations\Contracts;

use App\Modules\Finance\Models\FinHeadcount;

interface HrisIntegrationContract
{
    /**
     * Synchronize HRIS headcount (TKL/TKTL) and effective working hours to Finance BSC 19 Ratios.
     */
    public function syncHeadcountToFinance(int $periodId): FinHeadcount;

    /**
     * Push published Finance metrics (revenue, profit, ratio achievements) into HRIS Level 3 Quality Objectives.
     *
     * @param  array<string, mixed>  $metrics
     */
    public function publishFinanceMetricsToHris(int $periodId, array $metrics): int;
}
