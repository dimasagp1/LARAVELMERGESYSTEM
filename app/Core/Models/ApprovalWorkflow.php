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
 * Class ApprovalWorkflow
 *
 * @property int $id
 * @property string $name
 * @property string $code
 * @property int $module_id
 * @property string $target_model
 * @property float|null $min_amount
 * @property float|null $max_amount
 * @property array|null $condition_rules_json
 * @property bool $is_active
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property Carbon|null $deleted_at
 */
class ApprovalWorkflow extends Model
{
    use HasFactory;
    use LogsActivity;
    use SoftDeletes;

    protected $table = 'approval_workflows';

    protected $fillable = [
        'name',
        'code',
        'module_id',
        'target_model',
        'min_amount',
        'max_amount',
        'condition_rules_json',
        'is_active',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'min_amount' => 'decimal:2',
            'max_amount' => 'decimal:2',
            'condition_rules_json' => 'array',
            'is_active' => 'boolean',
        ];
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly([
                'name',
                'code',
                'module_id',
                'target_model',
                'min_amount',
                'max_amount',
                'is_active',
            ])
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
     * @return HasMany<ApprovalStep, $this>
     */
    public function steps(): HasMany
    {
        return $this->hasMany(ApprovalStep::class, 'workflow_id')->orderBy('step_order');
    }

    /**
     * @return HasMany<ApprovalLog, $this>
     */
    public function logs(): HasMany
    {
        return $this->hasMany(ApprovalLog::class, 'workflow_id');
    }
}
