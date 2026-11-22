<?php

namespace App\Modules\GeneralAffair\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SecurityCheckpointLog extends Model
{
    use HasFactory;

    protected $table = 'ga_security_checkpoint_logs';

    protected $fillable = [
        'checkpoint_id',
        'user_id',
        'scanned_at',
        'duration_seconds',
        'status',
        'is_anomaly',
        'anomaly_notes',
        'evidence_photo_path',
    ];

    protected function casts(): array
    {
        return [
            'scanned_at' => 'datetime',
            'duration_seconds' => 'integer',
            'is_anomaly' => 'boolean',
        ];
    }

    /**
     * @return BelongsTo<SecurityCheckpoint, $this>
     */
    public function checkpoint(): BelongsTo
    {
        return $this->belongsTo(SecurityCheckpoint::class, 'checkpoint_id');
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
