<?php

namespace App\Modules\Procurement\Services;

use App\Core\Integrations\Contracts\FinanceBudgetIntegrationContract;
use App\Modules\Procurement\Models\DepartmentBudget;
use App\Modules\Procurement\Models\ExpenseStaging;
use App\Modules\Procurement\Models\PrItem;
use App\Modules\Procurement\Models\PurchaseRequest;
use Carbon\Carbon;

class BudgetControlService
{
    protected ?FinanceBudgetIntegrationContract $financeBudget = null;

    public function __construct(
        ?FinanceBudgetIntegrationContract $financeBudget = null
    ) {
        $this->financeBudget = $financeBudget;
    }

    protected function getFinanceBudget(): ?FinanceBudgetIntegrationContract
    {
        if ($this->financeBudget === null && app()->bound(FinanceBudgetIntegrationContract::class)) {
            $this->financeBudget = app(FinanceBudgetIntegrationContract::class);
        }

        return $this->financeBudget;
    }

    /**
     * Check if department budget is sufficient for requested amount.
     */
    public function checkBudget(
        int $departmentId,
        string $purpose,
        float $requestedAmount,
        ?int $year = null,
        ?int $month = null
    ): array {
        // Enforce Finance Budget Gatekeeper Contract
        $financeBudget = $this->getFinanceBudget();
        if ($financeBudget) {
            $finCheck = $financeBudget->checkBudget($departmentId, $requestedAmount);
            if (! $finCheck['allow_proceed']) {
                return [
                    'has_budget_record' => true,
                    'is_sufficient' => false,
                    'initial_budget' => $finCheck['initial_budget'],
                    'realized' => $finCheck['realized_amount'],
                    'booked' => $finCheck['booked_amount'],
                    'remaining' => $finCheck['remaining_budget'],
                    'requested' => $requestedAmount,
                    'shortage' => $finCheck['shortage'],
                ];
            }
        }

        $year = $year ?: (int) Carbon::now()->format('Y');
        $month = $month ?: (int) Carbon::now()->format('m');

        $budget = DepartmentBudget::where('department_id', $departmentId)
            ->where('fiscal_year', $year)
            ->where('month', $month)
            ->where('purpose', $purpose)
            ->first();

        if (! $budget) {
            // Check fallback for the department overall this month
            $budget = DepartmentBudget::where('department_id', $departmentId)
                ->where('fiscal_year', $year)
                ->where('month', $month)
                ->first();
        }

        if (! $budget) {
            // Default virtual budget if unassigned (allows grace check)
            return [
                'has_budget_record' => false,
                'is_sufficient' => true,
                'initial_budget' => 0.0,
                'realized' => 0.0,
                'booked' => 0.0,
                'remaining' => 0.0,
                'requested' => $requestedAmount,
                'shortage' => 0.0,
            ];
        }

        $remaining = $budget->remainingBudget();
        $isSufficient = $remaining >= $requestedAmount;
        $shortage = $isSufficient ? 0.0 : ($requestedAmount - $remaining);

        return [
            'has_budget_record' => true,
            'is_sufficient' => $isSufficient,
            'initial_budget' => (float) $budget->initial_budget,
            'realized' => (float) $budget->realized_amount,
            'booked' => (float) $budget->booked_amount,
            'remaining' => $remaining,
            'requested' => $requestedAmount,
            'shortage' => round($shortage, 2),
        ];
    }

    /**
     * Book budget when PR / item is approved and prepared for PO.
     */
    public function bookBudget(
        int $departmentId,
        string $purpose,
        float $amount,
        ?int $year = null,
        ?int $month = null
    ): void {
        $year = $year ?: (int) Carbon::now()->format('Y');
        $month = $month ?: (int) Carbon::now()->format('m');

        $budget = DepartmentBudget::firstOrCreate(
            [
                'department_id' => $departmentId,
                'fiscal_year' => $year,
                'month' => $month,
                'purpose' => $purpose,
            ],
            [
                'initial_budget' => 50000000.00, // Default 50 million if auto-created
                'realized_amount' => 0,
                'booked_amount' => 0,
            ]
        );

        $budget->increment('booked_amount', $amount);

        // Commit budget in Finance Budget Gatekeeper
        $financeBudget = $this->getFinanceBudget();
        if ($financeBudget) {
            $financeBudget->commitBudget($departmentId, $amount);
        }
    }

    /**
     * Realize budget when PO is issued / item delivered.
     */
    public function realizeBudget(
        int $departmentId,
        string $purpose,
        float $amount,
        ?int $year = null,
        ?int $month = null
    ): void {
        $year = $year ?: (int) Carbon::now()->format('Y');
        $month = $month ?: (int) Carbon::now()->format('m');

        $budget = DepartmentBudget::where('department_id', $departmentId)
            ->where('fiscal_year', $year)
            ->where('month', $month)
            ->where('purpose', $purpose)
            ->first();

        if ($budget) {
            $budget->decrement('booked_amount', min((float) $budget->booked_amount, $amount));
            $budget->increment('realized_amount', $amount);
        }

        // Realize budget in Finance Budget Gatekeeper
        $financeBudget = $this->getFinanceBudget();
        if ($financeBudget) {
            $financeBudget->realizeBudget($departmentId, $amount);
        }
    }

    /**
     * Release booked budget if item or PR is cancelled / rejected.
     */
    public function releaseBudget(
        int $departmentId,
        string $purpose,
        float $amount,
        ?int $year = null,
        ?int $month = null
    ): void {
        $year = $year ?: (int) Carbon::now()->format('Y');
        $month = $month ?: (int) Carbon::now()->format('m');

        $budget = DepartmentBudget::where('department_id', $departmentId)
            ->where('fiscal_year', $year)
            ->where('month', $month)
            ->where('purpose', $purpose)
            ->first();

        if ($budget && $budget->booked_amount >= $amount) {
            $budget->decrement('booked_amount', $amount);
        }

        // Release budget in Finance Budget Gatekeeper
        $financeBudget = $this->getFinanceBudget();
        if ($financeBudget) {
            $financeBudget->releaseBudget($departmentId, $amount);
        }
    }

    /**
     * Create Expense Staging for Finance synchronization.
     */
    public function syncExpenseStaging(PurchaseRequest $pr, ?PrItem $item = null, string $status = 'committed'): ExpenseStaging
    {
        $amount = $item ? ((float) $item->actual_price ?: (float) $item->estimated_price) : (float) $pr->total_amount;

        return ExpenseStaging::create([
            'purchase_request_id' => $pr->id,
            'pr_item_id' => $item?->id,
            'department_id' => $pr->department_id,
            'purpose' => $pr->purpose,
            'amount' => $amount,
            'status' => $status,
            'staged_at' => Carbon::now(),
            'synced_at' => Carbon::now(),
        ]);
    }
}
