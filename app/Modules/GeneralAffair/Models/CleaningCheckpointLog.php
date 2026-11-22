<?php

namespace App\Modules\GeneralAffair\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CleaningCheckpointLog extends Model
{
    use HasFactory;

    protected $table = 'ga_cleaning_checkpoint_logs';

    protected $fillable = [
        'checkpoint_id',
        'user_id',
        'scanned_at',
        'is_sanitized',
        'checklist_json',
        'photo_path',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'scanned_at' => 'datetime',
            'is_sanitized' => 'boolean',
            'checklist_json' => 'array',
        ];
    }

    /**
     * @return BelongsTo<CleaningCheckpoint, $this>
     */
    public function checkpoint(): BelongsTo
    {
        return $this->belongsTo(CleaningCheckpoint::class, 'checkpoint_id');
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
