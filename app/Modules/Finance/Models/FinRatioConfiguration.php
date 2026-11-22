<?php

namespace App\Modules\Finance\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class FinRatioConfiguration extends Model
{
    use HasFactory;

    protected $table = 'fin_ratio_configurations';

    protected $guarded = ['id'];

    protected $casts = [
        'weight_pct' => 'decimal:2',
        'target_value' => 'decimal:2',
        'scale_divider' => 'decimal:2',
        'is_annualized' => 'boolean',
        'is_active' => 'boolean',
    ];

    public function values(): HasMany
    {
        return $this->hasMany(FinRatioValue::class, 'ratio_configuration_id');
    }
}
