<?php

namespace App\Modules\Finance\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FinRevenuePlan extends Model
{
    use HasFactory;

    protected $table = 'fin_revenue_plans';

    protected $guarded = ['id'];

    protected $casts = [
        'target_revenue' => 'decimal:2',
        'actual_revenue' => 'decimal:2',
        'target_units' => 'decimal:2',
    ];

    public function fiscalYear(): BelongsTo
    {
        return $this->belongsTo(FinFiscalYear::class, 'fiscal_year_id');
    }
}
