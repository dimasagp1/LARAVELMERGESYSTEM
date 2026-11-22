<?php

namespace App\Modules\GeneralAffair\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AssetCategory extends Model
{
    use HasFactory;

    protected $table = 'ga_asset_categories';

    protected $fillable = [
        'code',
        'name',
        'useful_life_years',
    ];

    /**
     * @return HasMany<GaAsset, $this>
     */
    public function assets(): HasMany
    {
        return $this->hasMany(GaAsset::class, 'category_id');
    }
}
