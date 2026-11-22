<?php

namespace App\Modules\Finance\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FinDynamicBudget extends Model
{
    use HasFactory;

    protected $table = 'fin_dynamic_budgets';

    protected $guarded = ['id'];

    protected $casts = [
        'fixed_amount' => 'decimal:2',
        'variable_rate' => 'decimal:4',
        'is_active' => 'boolean',
    ];

    public function calculateFlexibleBudget(float $revenueBasis, float $multiplier = 1.0): float
    {
        return (float) $this->fixed_amount + ($revenueBasis * (float) $this->variable_rate * $multiplier);
    }
}
