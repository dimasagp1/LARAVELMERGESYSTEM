<?php

namespace App\Modules\Quality\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Capa extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'quality_capas';

    protected $fillable = [
        'deviation_id',
        'initiator_id',
        'pic_id',
        'capa_number',
        'deviation_number_ref',
        'tanggal_penyimpangan',
        'tindakan_capa',
        'bukti_lapangan_path',
        'hasil_verifikasi_qa',
        'status',
        'custom_attributes',
    ];

    protected function casts(): array
    {
        return [
            'tanggal_penyimpangan' => 'date',
            'custom_attributes' => 'array',
        ];
    }

    /**
     * @return BelongsTo<Deviation, $this>
     */
    public function deviation(): BelongsTo
    {
        return $this->belongsTo(Deviation::class, 'deviation_id');
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

    /**
     * @return HasMany<CapaAction, $this>
     */
    public function actions(): HasMany
    {
        return $this->hasMany(CapaAction::class, 'capa_id');
    }

    public function getTotalActionsCountAttribute(): int
    {
        return $this->actions()->count();
    }

    public function getCompletedActionsCountAttribute(): int
    {
        return $this->actions()->whereIn('status', ['SUBMITTED', 'VERIFIED'])->count();
    }

    public function getProgressPercentageAttribute(): int
    {
        $total = $this->total_actions_count;
        if ($total === 0) {
            return $this->status === 'CLOSE' ? 100 : 0;
        }

        $completed = $this->completed_actions_count;

        return (int) round(($completed / $total) * 100);
    }
}
