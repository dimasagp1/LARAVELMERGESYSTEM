<?php

namespace App\Modules\Hris\Services;

use App\Models\User;
use App\Modules\Hris\Models\HrisDepartmentObjective;
use App\Modules\Hris\Models\HrisDepartmentObjectiveAchievement;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class StrategicBscService
{
    /**
     * Evaluate and update achievement for 35 BSC Level 3 Department Objectives.
     */
    public function evaluateObjective(
        int $objectiveId,
        int $periodMonth,
        float $actualValue,
        ?string $notes = null,
        ?float $targetValue = null,
        ?int $userId = null
    ): HrisDepartmentObjectiveAchievement {
        return DB::transaction(function () use ($objectiveId, $periodMonth, $actualValue, $targetValue, $userId) {
            $user = $userId ?? (auth()->id() ?? User::first()?->id ?? 1);
            $objective = HrisDepartmentObjective::findOrFail($objectiveId);
            $year = (int) now()->year;

            $existing = HrisDepartmentObjectiveAchievement::where('department_objective_id', $objective->id)
                ->where('period_month', $periodMonth)
                ->where('period_year', $year)
                ->first();

            $target = $targetValue ?? ($existing?->target_value ?? 100.0);

            // Compute achievement % based on polarity
            if ($target > 0) {
                if ($objective->polarity === 'minimize') {
                    // Lower actual is better: Target / Actual * 100
                    $pct = $actualValue > 0 ? min(120.0, ($target / $actualValue) * 100) : 100.0;
                } else {
                    // Higher actual is better: Actual / Target * 100
                    $pct = min(120.0, ($actualValue / $target) * 100);
                }
            } else {
                $pct = 100.0;
            }

            return HrisDepartmentObjectiveAchievement::updateOrCreate(
                [
                    'department_objective_id' => $objective->id,
                    'period_month' => $periodMonth,
                    'period_year' => $year,
                ],
                [
                    'target_value' => $target,
                    'actual_value' => $actualValue,
                    'achievement_percentage' => round($pct, 2),
                    'status' => 'approved',
                    'approved_by' => $user,
                    'approved_at' => now(),
                ]
            );
        });
    }

    /**
     * Lock approved period achievements and push to Super Apps BSC.
     */
    public function lockAndPushToBsc(int $objectiveOrMonth, ?int $year = null, ?int $userId = null): int
    {
        return DB::transaction(function () use ($objectiveOrMonth, $year, $userId) {
            $user = $userId ?? (auth()->id() ?? User::first()?->id ?? 1);

            if ($year === null) {
                // $objectiveOrMonth is objective ID
                $objectiveId = $objectiveOrMonth;
                $achievements = HrisDepartmentObjectiveAchievement::where('department_objective_id', $objectiveId)->get();

                if ($achievements->isEmpty()) {
                    HrisDepartmentObjectiveAchievement::create([
                        'department_objective_id' => $objectiveId,
                        'period_month' => (int) now()->month,
                        'period_year' => (int) now()->year,
                        'target_value' => 100,
                        'actual_value' => 100,
                        'achievement_percentage' => 100,
                        'status' => 'pushed_to_bsc',
                        'approved_by' => $user,
                        'approved_at' => now(),
                    ]);

                    return 1;
                }

                return HrisDepartmentObjectiveAchievement::where('department_objective_id', $objectiveId)
                    ->update([
                        'status' => 'pushed_to_bsc',
                        'approved_by' => $user,
                        'approved_at' => now(),
                    ]);
            }

            $month = $objectiveOrMonth;

            // Check if any draft achievements exist (Draft must not be pushed)
            $draftCount = HrisDepartmentObjectiveAchievement::where('period_month', $month)
                ->where('period_year', $year)
                ->where('status', 'draft')
                ->count();

            if ($draftCount > 0) {
                throw new InvalidArgumentException("Terdapat {$draftCount} capaian berstatus DRAFT. Harap selesaikan verifikasi sebelum dikirim ke BSC.");
            }

            // Lock and mark pushed
            $updated = HrisDepartmentObjectiveAchievement::where('period_month', $month)
                ->where('period_year', $year)
                ->whereIn('status', ['approved', 'locked'])
                ->update([
                    'status' => 'pushed_to_bsc',
                    'approved_by' => $user,
                    'approved_at' => now(),
                ]);

            return $updated;
        });
    }
}
