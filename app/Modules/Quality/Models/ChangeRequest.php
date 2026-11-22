<?php

namespace App\Modules\Quality\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class ChangeRequest extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'quality_change_requests';

    protected $fillable = [
        'initiator_id',
        'pic_id',
        'cr_number',
        'type',
        'sifat_perubahan',
        'nama_produk',
        'awal_sebelum_perubahan',
        'usulan_perubahan',
        'alasan_perubahan',
        'target_pelaksanaan',
        'severity',
        'occurrence',
        'detection',
        'rpn',
        'risk_mitigation',
        'qa_verification_data',
        'stage_2_data',
        'status',
        'custom_attributes',
    ];

    protected function casts(): array
    {
        return [
            'target_pelaksanaan' => 'date',
            'severity' => 'integer',
            'occurrence' => 'integer',
            'detection' => 'integer',
            'rpn' => 'integer',
            'qa_verification_data' => 'array',
            'stage_2_data' => 'array',
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
     * @return BelongsTo<User, $this>
     */
    public function pic(): BelongsTo
    {
        return $this->belongsTo(User::class, 'pic_id');
    }

    public function isCRA(): bool
    {
        return $this->type === 'CRA';
    }

    public function isCRB(): bool
    {
        return $this->type === 'CRB';
    }

    public function calculateRpn(): int
    {
        return (int) ($this->severity * $this->occurrence * $this->detection);
    }
}
