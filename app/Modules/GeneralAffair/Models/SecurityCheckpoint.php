<?php

namespace App\Modules\GeneralAffair\Models;

use App\Core\Models\Plant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SecurityCheckpoint extends Model
{
    use HasFactory;

    protected $table = 'ga_security_checkpoints';

    protected $fillable = [
        'code',
        'name',
        'location_description',
        'plant_id',
        'target_duration_minutes',
        'qr_token',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'target_duration_minutes' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    /**
     * @return BelongsTo<Plant, $this>
     */
    public function plant(): BelongsTo
    {
        return $this->belongsTo(Plant::class);
    }

    /**
     * @return HasMany<SecurityCheckpointLog, $this>
     */
    public function logs(): HasMany
    {
        return $this->hasMany(SecurityCheckpointLog::class, 'checkpoint_id')->latest('scanned_at');
    }
}
