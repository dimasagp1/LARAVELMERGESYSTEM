<?php

namespace App\Core\Integrations\Contracts;

interface FinanceBudgetIntegrationContract
{
    /**
     * Check budget ceiling for a department before committing expense.
     *
     * @return array{
     *     allow_proceed: bool,
     *     has_budget_record: bool,
     *     initial_budget: float,
     *     realized_amount: float,
     *     booked_amount: float,
     *     remaining_budget: float,
     *     requested_amount: float,
     *     shortage: float
     * }
     */
    public function checkBudget(int $departmentId, float $requestedAmount, ?int $periodId = null): array;

    /**
     * Commit (book) budget when PR or expense is submitted/approved.
     *
     * @return array{success: bool, booked_amount: float, remaining_budget: float}
     */
    public function commitBudget(int $departmentId, float $amount, ?int $periodId = null): array;

    /**
     * Release (unbook) committed budget when PR is rejected or cancelled.
     */
    public function releaseBudget(int $departmentId, float $amount, ?int $periodId = null): void;

    /**
     * Realize budget when PO goods receipt is verified into actual Accounts Payable.
     */
    public function realizeBudget(int $departmentId, float $amount, ?int $periodId = null): void;
}
