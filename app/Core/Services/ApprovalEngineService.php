<?php

namespace App\Core\Services;

use App\Core\Enums\ApprovalAction;
use App\Core\Models\ApprovalLog;
use App\Core\Models\ApprovalStep;
use App\Core\Models\ApprovalWorkflow;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Request;

class ApprovalEngineService
{
    /**
     * Find the matching workflow for a target model and amount.
     */
    public function findWorkflow(string $targetModel, ?float $amount = null): ?ApprovalWorkflow
    {
        $query = ApprovalWorkflow::query()
            ->where('target_model', $targetModel)
            ->where('is_active', true);

        if ($amount !== null) {
            $query->where(function ($q) use ($amount): void {
                $q->whereNull('min_amount')->orWhere('min_amount', '<=', $amount);
            })->where(function ($q) use ($amount): void {
                $q->whereNull('max_amount')->orWhere('max_amount', '>=', $amount);
            });
        }

        return $query->with('steps')->first();
    }

    /**
     * Record an action in the approval trail.
     */
    public function logAction(
        ApprovalWorkflow $workflow,
        ?ApprovalStep $step,
        Model $approvable,
        User $user,
        ApprovalAction $action,
        ?string $comments = null,
        ?string $signatureSnapshot = null
    ): ApprovalLog {
        return ApprovalLog::create([
            'workflow_id' => $workflow->id,
            'step_id' => $step?->id,
            'approvable_type' => get_class($approvable),
            'approvable_id' => $approvable->getKey(),
            'user_id' => $user->id,
            'action' => $action->value,
            'comments' => $comments,
            'ip_address' => Request::ip(),
            'user_agent' => Request::userAgent(),
            'signature_snapshot' => $signatureSnapshot ?? $user->signature_path,
            'acted_at' => now(),
        ]);
    }
}
