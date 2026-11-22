<?php

namespace App\Modules\Production\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DowntimeDetail extends Model
{
    use HasFactory;

    protected $table = 'downtime_details';

    protected $guarded = ['id'];

    protected $casts = [
        'durasi_menit' => 'decimal:2',
    ];

    public function laporanHarian(): BelongsTo
    {
        return $this->belongsTo(LaporanHarian::class, 'laporan_harian_id');
    }

    public function alasanDowntime(): BelongsTo
    {
        return $this->belongsTo(ProductionDowntimeReason::class, 'alasan_downtime_id');
    }
}
