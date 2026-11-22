<?php

namespace App\Core\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

/**
 * Class ErpModule
 *
 * @property int $id
 * @property string $slug
 * @property string $name
 * @property string|null $description
 * @property string $icon
 * @property string $color_theme
 * @property string $route_prefix
 * @property bool $is_active
 * @property bool $is_core
 * @property bool $is_custom
 * @property int $sort_order
 * @property array|null $schema_config_json
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property Carbon|null $deleted_at
 */
class ErpModule extends Model
{
    use HasFactory;
    use LogsActivity;
    use SoftDeletes;

    protected $table = 'erp_modules';

    protected $fillable = [
        'slug',
        'name',
        'description',
        'icon',
        'color_theme',
        'route_prefix',
        'is_active',
        'is_core',
        'is_custom',
        'sort_order',
        'schema_config_json',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'is_core' => 'boolean',
            'is_custom' => 'boolean',
            'sort_order' => 'integer',
            'schema_config_json' => 'array',
        ];
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly([
                'slug',
                'name',
                'route_prefix',
                'is_active',
                'is_core',
                'is_custom',
                'sort_order',
            ])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }

    /**
     * @return HasMany<UserModulePreference, $this>
     */
    public function userPreferences(): HasMany
    {
        return $this->hasMany(UserModulePreference::class, 'module_id');
    }

    /**
     * @return HasMany<ApprovalWorkflow, $this>
     */
    public function approvalWorkflows(): HasMany
    {
        return $this->hasMany(ApprovalWorkflow::class, 'module_id');
    }
}
