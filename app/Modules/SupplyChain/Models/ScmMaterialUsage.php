<?php

namespace App\Modules\SupplyChain\Models;

use App\Core\Models\Department;
use App\Core\Models\Plant;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class ScmMaterialUsage extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'scm_material_usages';

    protected $guarded = ['id'];

    protected $casts = [
        'usage_date' => 'date',
        'custom_attributes' => 'array',
    ];

    public function plant(): BelongsTo
    {
        return $this->belongsTo(Plant::class, 'plant_id');
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class, 'department_id');
    }

    public function issuer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'issued_by');
    }

    public function recipient(): BelongsTo
    {
        return $this->belongsTo(User::class, 'received_by');
    }

    public function items(): HasMany
    {
        return $this->hasMany(ScmMaterialUsageItem::class, 'material_usage_id');
    }
}
