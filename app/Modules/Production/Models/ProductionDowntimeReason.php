<?php

namespace App\Modules\Production\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ProductionDowntimeReason extends Model
{
    use HasFactory;

    protected $table = 'production_downtime_reasons';

    protected $guarded = ['id'];

    protected $casts = [
        'default_duration_minutes' => 'decimal:2',
        'requires_maintenance' => 'boolean',
        'is_active' => 'boolean',
        'custom_attributes' => 'array',
    ];

    protected $appends = [
        'kode_alasan',
        'nama_alasan',
        'status_aktif',
    ];

    public function getKodeAlasanAttribute(): string
    {
        return $this->attributes['code'] ?? '';
    }

    public function setKodeAlasanAttribute($value): void
    {
        $this->attributes['code'] = $value;
    }

    public function getNamaAlasanAttribute(): string
    {
        return $this->attributes['name'] ?? '';
    }

    public function setNamaAlasanAttribute($value): void
    {
        $this->attributes['name'] = $value;
    }

    public function getStatusAktifAttribute(): bool
    {
        return (bool) ($this->attributes['is_active'] ?? true);
    }

    public function downtimeDetails(): HasMany
    {
        return $this->hasMany(DowntimeDetail::class, 'alasan_downtime_id');
    }
}
