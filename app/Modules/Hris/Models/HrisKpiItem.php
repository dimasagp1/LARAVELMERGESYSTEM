<?php

namespace App\Modules\Hris\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HrisKpiItem extends Model
{
    use HasFactory;

    protected $table = 'hris_kpi_items';

    protected $guarded = ['id'];

    protected $casts = [
        'weight' => 'decimal:2',
        'target' => 'decimal:2',
        'actual' => 'decimal:2',
        'score' => 'decimal:2',
        'weighted_score' => 'decimal:2',
    ];

    public function kpi(): BelongsTo
    {
        return $this->belongsTo(HrisKpi::class, 'kpi_id');
    }
}
