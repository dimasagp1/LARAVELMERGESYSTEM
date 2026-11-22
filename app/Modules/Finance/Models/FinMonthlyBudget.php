<?php

namespace App\Modules\Finance\Models;

use App\Core\Models\Department;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FinMonthlyBudget extends Model
{
    use HasFactory;

    protected $table = 'fin_monthly_budgets';

    protected $guarded = ['id'];

    protected $casts = [
        'initial_budget' => 'decimal:2',
        'revised_budget' => 'decimal:2',
        'realized_amount' => 'decimal:2',
        'booked_amount' => 'decimal:2',
    ];

    public function fiscalYear(): BelongsTo
    {
        return $this->belongsTo(FinFiscalYear::class, 'fiscal_year_id');
    }

    public function period(): BelongsTo
    {
        return $this->belongsTo(FinPeriod::class, 'period_id');
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class, 'department_id');
    }

    public function budgetCategory(): BelongsTo
    {
        return $this->belongsTo(FinBudgetCategory::class, 'budget_category_id');
    }

    public function remainingBudget(): float
    {
        $budget = (float) ($this->revised_budget > 0 ? $this->revised_budget : $this->initial_budget);

        return $budget - ((float) $this->realized_amount + (float) $this->booked_amount);
    }
}
