<?php

namespace App\Modules\Finance\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class FinBudgetCategory extends Model
{
    use HasFactory;

    protected $table = 'fin_budget_categories';

    protected $guarded = ['id'];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function budgets(): HasMany
    {
        return $this->hasMany(FinMonthlyBudget::class, 'budget_category_id');
    }
}
