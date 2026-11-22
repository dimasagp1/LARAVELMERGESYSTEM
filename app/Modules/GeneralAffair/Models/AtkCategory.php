<?php

namespace App\Modules\GeneralAffair\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AtkCategory extends Model
{
    use HasFactory;

    protected $table = 'ga_atk_categories';

    protected $fillable = [
        'name',
        'type',
    ];

    /**
     * @return HasMany<AtkItem, $this>
     */
    public function items(): HasMany
    {
        return $this->hasMany(AtkItem::class, 'category_id');
    }
}
