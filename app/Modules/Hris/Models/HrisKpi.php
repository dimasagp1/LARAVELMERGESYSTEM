<?php

namespace App\Modules\Hris\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class HrisKpi extends Model
{
    use HasFactory;

    protected $table = 'hris_kpis';

    protected $guarded = ['id'];

    protected $casts = [
        'total_score' => 'decimal:2',
        'custom_attributes' => 'array',
    ];

    public function employee(): BelongsTo
    {
        return $this->belongsTo(HrisEmployee::class, 'employee_id');
    }

    public function evaluator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'evaluator_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(HrisKpiItem::class, 'kpi_id');
    }
}
