<?php

namespace App\Modules\Production\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RejectDetail extends Model
{
    use HasFactory;

    protected $table = 'reject_details';

    protected $guarded = ['id'];

    protected $casts = [
        'jumlah' => 'decimal:2',
        'biaya_satuan' => 'decimal:2',
        'total_nilai_scrap' => 'decimal:2',
    ];

    public function laporanHarian(): BelongsTo
    {
        return $this->belongsTo(LaporanHarian::class, 'laporan_harian_id');
    }
}
