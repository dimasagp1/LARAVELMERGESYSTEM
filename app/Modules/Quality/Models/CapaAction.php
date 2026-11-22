<?php

namespace App\Modules\Quality\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CapaAction extends Model
{
    use HasFactory;

    protected $table = 'quality_capa_actions';

    protected $fillable = [
        'capa_id',
        'pic_id',
        'action_number',
        'tindakan',
        'jenis_tindakan',
        'department',
        'deadline',
        'status',
        'bukti_file_path',
        'catatan_pelaksanaan',
        'verified_at',
        'verified_by_user_id',
    ];

    protected function casts(): array
    {
        return [
            'deadline' => 'date',
            'verified_at' => 'datetime',
        ];
    }

    /**
     * @return BelongsTo<Capa, $this>
     */
    public function capa(): BelongsTo
    {
        return $this->belongsTo(Capa::class, 'capa_id');
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function pic(): BelongsTo
    {
        return $this->belongsTo(User::class, 'pic_id');
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function verifiedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'verified_by_user_id');
    }
}
