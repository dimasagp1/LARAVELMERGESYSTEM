<?php

namespace App\Modules\Finance\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FinProductionPlan extends Model
{
    use HasFactory;

    protected $table = 'fin_production_plans';

    protected $guarded = ['id'];

    protected $casts = [
        'machine_capacity_hours' => 'decimal:2',
        'target_yield_pct' => 'decimal:2',
        'bom_cost_budget' => 'decimal:2',
        'labor_cost_budget' => 'decimal:2',
    ];

    public function fiscalYear(): BelongsTo
    {
        return $this->belongsTo(FinFiscalYear::class, 'fiscal_year_id');
    }
}
