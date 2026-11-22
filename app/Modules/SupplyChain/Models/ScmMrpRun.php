<?php

namespace App\Modules\SupplyChain\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ScmMrpRun extends Model
{
    use HasFactory;

    protected $table = 'scm_mrp_runs';

    protected $guarded = ['id'];

    protected $appends = ['run_date'];

    protected $casts = [
        'parameters_json' => 'array',
    ];

    public function schedule(): BelongsTo
    {
        return $this->belongsTo(ScmProductionSchedule::class, 'schedule_id');
    }

    public function planner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'executed_by');
    }

    public function executor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'executed_by');
    }

    public function items(): HasMany
    {
        return $this->hasMany(ScmMrpItem::class, 'mrp_run_id');
    }

    public function getRunDateAttribute(): string
    {
        return $this->created_at ? $this->created_at->format('Y-m-d') : '';
    }
}
