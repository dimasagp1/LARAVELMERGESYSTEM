<?php

namespace App\Modules\Procurement\Models;

use App\Core\Models\Department;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DepartmentBudget extends Model
{
    use HasFactory;

    protected $table = 'department_budgets';

    protected $guarded = ['id'];

    protected $casts = [
        'fiscal_year' => 'integer',
        'month' => 'integer',
        'initial_budget' => 'decimal:2',
        'realized_amount' => 'decimal:2',
        'booked_amount' => 'decimal:2',
    ];

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class, 'department_id');
    }

    public function remainingBudget(): float
    {
        return (float) $this->initial_budget - ((float) $this->realized_amount + (float) $this->booked_amount);
    }

    public function isSufficient(float $requestedAmount): bool
    {
        return $this->remainingBudget() >= $requestedAmount;
    }
}
