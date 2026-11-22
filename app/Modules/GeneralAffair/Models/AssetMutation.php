<?php

namespace App\Modules\GeneralAffair\Models;

use App\Core\Models\Department;
use App\Core\Models\Location;
use App\Core\Models\Plant;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AssetMutation extends Model
{
    use HasFactory;

    protected $table = 'ga_asset_mutations';

    protected $fillable = [
        'asset_id',
        'mutation_date',
        'from_plant_id',
        'to_plant_id',
        'from_department_id',
        'to_department_id',
        'from_location_id',
        'to_location_id',
        'pic_receiver',
        'reason',
        'transferred_by_user_id',
    ];

    protected function casts(): array
    {
        return [
            'mutation_date' => 'date',
        ];
    }

    /**
     * @return BelongsTo<GaAsset, $this>
     */
    public function asset(): BelongsTo
    {
        return $this->belongsTo(GaAsset::class, 'asset_id');
    }

    /**
     * @return BelongsTo<Plant, $this>
     */
    public function fromPlant(): BelongsTo
    {
        return $this->belongsTo(Plant::class, 'from_plant_id');
    }

    /**
     * @return BelongsTo<Plant, $this>
     */
    public function toPlant(): BelongsTo
    {
        return $this->belongsTo(Plant::class, 'to_plant_id');
    }

    /**
     * @return BelongsTo<Department, $this>
     */
    public function fromDepartment(): BelongsTo
    {
        return $this->belongsTo(Department::class, 'from_department_id');
    }

    /**
     * @return BelongsTo<Department, $this>
     */
    public function toDepartment(): BelongsTo
    {
        return $this->belongsTo(Department::class, 'to_department_id');
    }

    /**
     * @return BelongsTo<Location, $this>
     */
    public function fromLocation(): BelongsTo
    {
        return $this->belongsTo(Location::class, 'from_location_id');
    }

    /**
     * @return BelongsTo<Location, $this>
     */
    public function toLocation(): BelongsTo
    {
        return $this->belongsTo(Location::class, 'to_location_id');
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function transferredBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'transferred_by_user_id');
    }
}
