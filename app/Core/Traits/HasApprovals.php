<?php

namespace App\Core\Traits;

use App\Core\Models\ApprovalLog;
use Illuminate\Database\Eloquent\Relations\MorphMany;

trait HasApprovals
{
    /**
     * @return MorphMany<ApprovalLog, $this>
     */
    public function approvalLogs(): MorphMany
    {
        return $this->morphMany(ApprovalLog::class, 'approvable')->orderBy('acted_at');
    }

    public function latestApprovalLog(): ?ApprovalLog
    {
        return $this->approvalLogs()->latest('acted_at')->first();
    }
}
