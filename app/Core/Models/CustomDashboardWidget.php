<?php

namespace App\Core\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;

/**
 * Class CustomDashboardWidget
 *
 * @property int $id
 * @property int $module_id
 * @property string $widget_type
 * @property string $title
 * @property array|null $data_source_config_json
 * @property int $grid_span
 * @property int $sort_order
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property Carbon|null $deleted_at
 */
class CustomDashboardWidget extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $table = 'custom_dashboard_widgets';

    protected $fillable = [
        'module_id',
        'widget_type',
        'title',
        'data_source_config_json',
        'grid_span',
        'sort_order',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'data_source_config_json' => 'array',
            'grid_span' => 'integer',
            'sort_order' => 'integer',
        ];
    }

    /**
     * @return BelongsTo<ErpModule, $this>
     */
    public function module(): BelongsTo
    {
        return $this->belongsTo(ErpModule::class, 'module_id');
    }
}
