<?php

namespace App\Modules\Production\Models;

use App\Core\Models\Product;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class WeeklyPlan extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'weekly_plans';

    protected $guarded = ['id'];

    protected $casts = [
        'tanggal' => 'date',
        'target_output' => 'integer',
        'mp_count' => 'integer',
        'multiplier' => 'integer',
        'packing_hold' => 'boolean',
        'custom_attributes' => 'array',
    ];

    protected $appends = [
        'target_per_mp',
        'calculated_target',
        'is_odoo_confirmed',
    ];

    public function getTargetPerMpAttribute(): int
    {
        return (int) ($this->multiplier ?? 2000);
    }

    public function getCalculatedTargetAttribute(): int
    {
        return (int) (($this->mp_count ?? 10) * ($this->multiplier ?? 2000));
    }

    public function getIsOdooConfirmedAttribute(): bool
    {
        return strtolower($this->mo_status ?? '') === 'confirmed';
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'produk_id');
    }

    public function produk(): BelongsTo
    {
        return $this->product();
    }

    public function line(): BelongsTo
    {
        return $this->belongsTo(ProductionLine::class, 'line_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function laporanHarians(): HasMany
    {
        return $this->hasMany(LaporanHarian::class, 'weekly_plan_id');
    }
}
