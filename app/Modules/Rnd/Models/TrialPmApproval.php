<?php

namespace App\Modules\Rnd\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TrialPmApproval extends Model
{
    use HasFactory;

    protected $table = 'rnd_trial_pm_approvals';

    protected $guarded = ['id'];

    protected $casts = [
        'signed_at' => 'datetime',
    ];

    public function trialPm(): BelongsTo
    {
        return $this->belongsTo(TrialPm::class, 'trial_pm_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
