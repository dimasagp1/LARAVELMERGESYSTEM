<?php

namespace App\Modules\Finance\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FinProductionFeed extends Model
{
    use HasFactory;

    protected $table = 'fin_production_feeds';

    protected $guarded = ['id'];

    protected $casts = [
        'log_date' => 'date',
        'actual_output_units' => 'decimal:2',
        'target_output_units' => 'decimal:2',
        'reject_rate_pct' => 'decimal:2',
        'material_loss_value' => 'decimal:2',
    ];

    public function period(): BelongsTo
    {
        return $this->belongsTo(FinPeriod::class, 'period_id');
    }
}
