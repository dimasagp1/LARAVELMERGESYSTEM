<?php

namespace App\Modules\Production\Models;

use App\Core\Models\Plant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ProductionLine extends Model
{
    use HasFactory;

    protected $table = 'production_lines';

    protected $guarded = ['id'];

    protected $casts = [
        'is_active' => 'boolean',
        'design_capacity_hourly' => 'integer',
        'custom_attributes' => 'array',
    ];

    protected $appends = [
        'nama_line',
        'nomor_line',
        'status_aktif',
    ];

    public function getNamaLineAttribute(): string
    {
        return $this->attributes['name'] ?? '';
    }

    public function setNamaLineAttribute($value): void
    {
        $this->attributes['name'] = $value;
    }

    public function getNomorLineAttribute(): string
    {
        return $this->attributes['line_number'] ?? '';
    }

    public function setNomorLineAttribute($value): void
    {
        $this->attributes['line_number'] = $value;
    }

    public function getStatusAktifAttribute(): bool
    {
        return (bool) ($this->attributes['is_active'] ?? true);
    }

    public function plant(): BelongsTo
    {
        return $this->belongsTo(Plant::class, 'plant_id');
    }

    public function machines(): HasMany
    {
        return $this->hasMany(ProductionMachine::class, 'line_id');
    }

    public function weeklyPlans(): HasMany
    {
        return $this->hasMany(WeeklyPlan::class, 'line_id');
    }

    public function laporanHarians(): HasMany
    {
        return $this->hasMany(LaporanHarian::class, 'line_id');
    }
}
