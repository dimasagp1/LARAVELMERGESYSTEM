<?php

namespace App\Modules\GeneralAffair\Models;

use App\Core\Models\Plant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CleaningCheckpoint extends Model
{
    use HasFactory;

    protected $table = 'ga_cleaning_checkpoints';

    protected $fillable = [
        'code',
        'name',
        'plant_id',
        'qr_token',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
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
     * @return HasMany<CleaningCheckpointLog, $this>
     */
    public function logs(): HasMany
    {
        return $this->hasMany(CleaningCheckpointLog::class, 'checkpoint_id')->latest('scanned_at');
    }
}
