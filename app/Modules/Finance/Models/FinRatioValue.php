<?php

namespace App\Modules\Finance\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FinRatioValue extends Model
{
    use HasFactory;

    protected $table = 'fin_ratio_values';

    protected $guarded = ['id'];

    protected $casts = [
        'raw_value' => 'decimal:4',
        'computed_score' => 'decimal:2',
        'calculated_at' => 'datetime',
    ];

    public function period(): BelongsTo
    {
        return $this->belongsTo(FinPeriod::class, 'period_id');
    }

    public function configuration(): BelongsTo
    {
        return $this->belongsTo(FinRatioConfiguration::class, 'ratio_configuration_id');
    }
}
