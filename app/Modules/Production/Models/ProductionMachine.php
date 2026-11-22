<?php

namespace App\Modules\Production\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ProductionMachine extends Model
{
    use HasFactory;

    protected $table = 'production_machines';

    protected $guarded = ['id'];

    protected $casts = [
        'cycle_time_seconds' => 'decimal:2',
        'is_active' => 'boolean',
        'custom_attributes' => 'array',
    ];

    protected $appends = [
        'kode_mesin',
        'nama_mesin',
        'ct',
        'status_aktif',
    ];

    public function getKodeMesinAttribute(): string
    {
        return $this->attributes['machine_code'] ?? '';
    }

    public function setKodeMesinAttribute($value): void
    {
        $this->attributes['machine_code'] = $value;
    }

    public function getNamaMesinAttribute(): string
    {
        return $this->attributes['name'] ?? '';
    }

    public function setNamaMesinAttribute($value): void
    {
        $this->attributes['name'] = $value;
    }

    public function getCtAttribute(): float
    {
        return (float) ($this->attributes['cycle_time_seconds'] ?? 1.20);
    }

    public function setCtAttribute($value): void
    {
        $this->attributes['cycle_time_seconds'] = $value;
    }

    public function getStatusAktifAttribute(): bool
    {
        return (bool) ($this->attributes['is_active'] ?? true);
    }

    public function line(): BelongsTo
    {
        return $this->belongsTo(ProductionLine::class, 'line_id');
    }

    public function laporanHarians(): HasMany
    {
        return $this->hasMany(LaporanHarian::class, 'mesin_id');
    }
}
