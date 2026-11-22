<?php

namespace App\Modules\Finance\Services;

use App\Modules\Finance\Models\FinMonthlyBudget;
use App\Modules\Finance\Models\FinPeriod;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class BudgetGatekeeperService
{
    /**
     * Check budget availability for a department and purpose.
     */
    public function checkBudget(int $departmentId, float $requestedAmount, ?int $periodId = null): array
    {
        $period = $periodId
            ? FinPeriod::find($periodId)
            : FinPeriod::where('month', (int) Carbon::now()->format('m'))
                ->where('year', (int) Carbon::now()->format('Y'))
                ->first();

        if (! $period) {
            $period = FinPeriod::latest('id')->first();
        }

        $budget = FinMonthlyBudget::where('period_id', $period?->id)
            ->where('department_id', $departmentId)
            ->first();

        if (! $budget) {
            // Default virtual budget
            return [
                'allow_proceed' => true,
                'has_budget_record' => false,
                'initial_budget' => 0.0,
                'realized_amount' => 0.0,
                'booked_amount' => 0.0,
                'remaining_budget' => 0.0,
                'requested_amount' => $requestedAmount,
                'shortage' => 0.0,
            ];
        }

        $remaining = $budget->remainingBudget();
        $allowProceed = $remaining >= $requestedAmount;
        $shortage = $allowProceed ? 0.0 : ($requestedAmount - $remaining);

        return [
            'allow_proceed' => $allowProceed,
            'has_budget_record' => true,
            'initial_budget' => (float) $budget->initial_budget,
            'realized_amount' => (float) $budget->realized_amount,
            'booked_amount' => (float) $budget->booked_amount,
            'remaining_budget' => $remaining,
            'requested_amount' => $requestedAmount,
            'shortage' => round($shortage, 2),
        ];
    }

    /**
     * Book budget when PR is approved.
     */
    public function bookBudget(int $departmentId, float $amount, ?int $periodId = null): void
    {
        $period = $periodId ? FinPeriod::find($periodId) : FinPeriod::latest('id')->first();
        if (! $period) {
            return;
        }

        DB::transaction(function () use ($departmentId, $amount, $period) {
            $budget = FinMonthlyBudget::firstOrCreate(
                [
                    'period_id' => $period->id,
                    'department_id' => $departmentId,
                ],
                [
                    'fiscal_year_id' => $period->fiscal_year_id,
                    'initial_budget' => 100000000.0,
                    'realized_amount' => 0,
                    'booked_amount' => 0,
                ]
            );

            $budget->increment('booked_amount', $amount);
        });
    }
}
