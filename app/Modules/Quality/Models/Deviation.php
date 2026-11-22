<?php

namespace App\Modules\Quality\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class Deviation extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'quality_deviations';

    protected $fillable = [
        'initiator_id',
        'deviation_number',
        'pk_number',
        'topik',
        'department',
        'pic',
        'nomor_bets',
        'nomor_alat',
        'nomor_dokumen',
        'objek_terdampak',
        'jenis_penyimpangan',
        'identifikasi_penyimpangan',
        'tanggal_temuan',
        'is_other_batch_affected',
        'other_batch_details',
        'frekuensi_kejadian',
        'is_production_stopped',
        'immediate_action_details',
        'root_cause',
        'attachments',
        'fishbone_6m',
        'five_why',
        'evaluasi_tindakan',
        'mengetahui_data',
        'reject_reason',
        'status',
        'custom_attributes',
    ];

    protected function casts(): array
    {
        return [
            'tanggal_temuan' => 'date',
            'is_other_batch_affected' => 'boolean',
            'is_production_stopped' => 'boolean',
            'attachments' => 'array',
            'fishbone_6m' => 'array',
            'five_why' => 'array',
            'evaluasi_tindakan' => 'array',
            'mengetahui_data' => 'array',
            'custom_attributes' => 'array',
        ];
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function initiator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'initiator_id');
    }

    /**
     * @return HasOne<Capa, $this>
     */
    public function capa(): HasOne
    {
        return $this->hasOne(Capa::class, 'deviation_id');
    }

    public function isApproved(): bool
    {
        return $this->status === 'APPROVED';
    }

    public function hasCapa(): bool
    {
        return $this->capa()->exists();
    }
}
