<?php

namespace App\Core\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Permission\Models\Role;

/**
 * Class ApprovalStep
 *
 * @property int $id
 * @property int $workflow_id
 * @property int $step_order
 * @property string $step_name
 * @property string $approver_type
 * @property int|null $approver_role_id
 * @property int|null $approver_user_id
 * @property int|null $sla_hours
 * @property bool $requires_digital_signature
 * @property bool $can_reject
 * @property bool $can_revise
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property Carbon|null $deleted_at
 */
class ApprovalStep extends Model
{
    use HasFactory;
    use LogsActivity;
    use SoftDeletes;

    protected $table = 'approval_steps';

    protected $fillable = [
        'workflow_id',
        'step_order',
        'step_name',
        'approver_type',
        'approver_role_id',
        'approver_user_id',
        'sla_hours',
        'requires_digital_signature',
        'can_reject',
        'can_revise',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'step_order' => 'integer',
            'sla_hours' => 'integer',
            'requires_digital_signature' => 'boolean',
            'can_reject' => 'boolean',
            'can_revise' => 'boolean',
        ];
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly([
                'workflow_id',
                'step_order',
                'step_name',
                'approver_type',
                'approver_role_id',
                'approver_user_id',
                'requires_digital_signature',
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
     * @return BelongsTo<Role, $this>
     */
    public function approverRole(): BelongsTo
    {
        return $this->belongsTo(Role::class, 'approver_role_id');
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function approverUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approver_user_id');
    }

    /**
     * @return HasMany<ApprovalLog, $this>
     */
    public function logs(): HasMany
    {
        return $this->hasMany(ApprovalLog::class, 'step_id');
    }
}
