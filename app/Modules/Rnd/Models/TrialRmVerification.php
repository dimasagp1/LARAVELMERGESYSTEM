<?php

namespace App\Modules\Rnd\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TrialRmVerification extends Model
{
    use HasFactory;

    protected $table = 'rnd_trial_rm_verifications';

    protected $guarded = ['id'];

    protected $casts = [
        'is_compliant' => 'boolean',
    ];

    public function trialRm(): BelongsTo
    {
        return $this->belongsTo(TrialRm::class, 'trial_rm_id');
    }
}
