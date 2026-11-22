<?php

namespace App\Modules\Finance\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FinHeadcount extends Model
{
    use HasFactory;

    protected $table = 'fin_headcounts';

    protected $guarded = ['id'];

    protected $casts = [
        'working_hours' => 'decimal:2',
        'overtime_cost' => 'decimal:2',
        'total_payroll' => 'decimal:2',
    ];

    public function period(): BelongsTo
    {
        return $this->belongsTo(FinPeriod::class, 'period_id');
    }
}
