<?php

namespace App\Core\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

/**
 * Class ApprovalLog
 *
 * @property int $id
 * @property int $workflow_id
 * @property int|null $step_id
 * @property string $approvable_type
 * @property int $approvable_id
 * @property int $user_id
 * @property string $action
 * @property string|null $comments
 * @property string|null $ip_address
 * @property string|null $user_agent
 * @property string|null $signature_snapshot
 * @property Carbon $acted_at
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property Carbon|null $deleted_at
 */
class ApprovalLog extends Model
{
    use HasFactory;
    use LogsActivity;
    use SoftDeletes;

    protected $table = 'approval_logs';

    protected $fillable = [
        'workflow_id',
        'step_id',
        'approvable_type',
        'approvable_id',
        'user_id',
        'action',
        'comments',
        'ip_address',
        'user_agent',
        'signature_snapshot',
        'acted_at',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'acted_at' => 'datetime',
        ];
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly([
                'workflow_id',
                'step_id',
                'approvable_type',
                'approvable_id',
                'user_id',
                'action',
                'acted_at',
            ])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }

    /**
     * @return BelongsTo<ApprovalWorkflow, $this>
     */
    public function workflow(): BelongsTo
    {
        return $this->belongsTo(ApprovalWorkflow::class, 'workflow_id');
    }

    /**
     * @return BelongsTo<ApprovalStep, $this>
     */
    public function step(): BelongsTo
    {
        return $this->belongsTo(ApprovalStep::class, 'step_id');
    }

    /**
     * @return MorphTo<Model, $this>
     */
    public function approvable(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
