<?php

namespace App\Modules\Finance\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FinSourceReconciliation extends Model
{
    use HasFactory;

    protected $table = 'fin_source_reconciliations';

    protected $guarded = ['id'];

    protected $casts = [
        'gl_value' => 'decimal:2',
        'target_value' => 'decimal:2',
        'variance' => 'decimal:2',
        'is_passed' => 'boolean',
        'details_json' => 'array',
        'checked_at' => 'datetime',
    ];

    public function period(): BelongsTo
    {
        return $this->belongsTo(FinPeriod::class, 'period_id');
    }
}
