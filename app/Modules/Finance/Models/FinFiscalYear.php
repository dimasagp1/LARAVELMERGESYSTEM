<?php

namespace App\Modules\Finance\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class FinFiscalYear extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'fin_fiscal_years';

    protected $guarded = ['id'];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'is_active' => 'boolean',
        'is_closed' => 'boolean',
    ];

    public function periods(): HasMany
    {
        return $this->hasMany(FinPeriod::class, 'fiscal_year_id');
    }

    public function budgets(): HasMany
    {
        return $this->hasMany(FinMonthlyBudget::class, 'fiscal_year_id');
    }

    public function revenuePlans(): HasMany
    {
        return $this->hasMany(FinRevenuePlan::class, 'fiscal_year_id');
    }

    public function productionPlans(): HasMany
    {
        return $this->hasMany(FinProductionPlan::class, 'fiscal_year_id');
    }
}
