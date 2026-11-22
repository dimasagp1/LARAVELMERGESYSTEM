<?php

namespace App\Core\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

/**
 * Class CustomEntity
 *
 * @property int $id
 * @property int $module_id
 * @property string $name
 * @property string $slug
 * @property string $table_type
 * @property string $icon
 * @property string|null $description
 * @property bool $has_approval
 * @property bool $has_audit_log
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property Carbon|null $deleted_at
 */
class CustomEntity extends Model
{
    use HasFactory;
    use LogsActivity;
    use SoftDeletes;

    protected $table = 'custom_entities';

    protected $fillable = [
        'module_id',
        'name',
        'slug',
        'table_type',
        'icon',
        'description',
        'has_approval',
        'has_audit_log',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'has_approval' => 'boolean',
            'has_audit_log' => 'boolean',
        ];
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['module_id', 'name', 'slug', 'table_type', 'has_approval'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }

    /**
     * @return BelongsTo<ErpModule, $this>
     */
    public function module(): BelongsTo
    {
        return $this->belongsTo(ErpModule::class, 'module_id');
    }

    /**
     * @return HasMany<CustomField, $this>
     */
    public function fields(): HasMany
    {
        return $this->hasMany(CustomField::class, 'entity_id')->orderBy('sort_order');
    }

    /**
     * @return HasMany<CustomRecord, $this>
     */
    public function records(): HasMany
    {
        return $this->hasMany(CustomRecord::class, 'entity_id')->latest();
    }
}
