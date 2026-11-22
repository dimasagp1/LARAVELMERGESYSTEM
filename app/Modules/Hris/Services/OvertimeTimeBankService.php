<?php

namespace App\Modules\Hris\Services;

use App\Models\User;
use App\Modules\Hris\Models\HrisOvertime;
use App\Modules\Hris\Models\HrisTimeBank;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class OvertimeTimeBankService
{
    /**
     * Compute Depnaker RI compliant overtime multiplier hours.
     * Weekday: First hour = 1.5x, Next hours = 2.0x
     * Holiday/Weekend: First 8 hours = 2.0x, 9th hour = 3.0x, 10th+ = 4.0x
     */
    public function calculateDepnakerHours(float $actualHours, bool $isHoliday = false): float
    {
        if ($actualHours <= 0) {
            return 0.0;
        }

        if (! $isHoliday) {
            if ($actualHours <= 1.0) {
                return round($actualHours * 1.5, 2);
            }

            return round(1.5 + (($actualHours - 1.0) * 2.0), 2);
        }

        // Holiday calculation
        if ($actualHours <= 8.0) {
            return round($actualHours * 2.0, 2);
        } elseif ($actualHours <= 9.0) {
            return round((8.0 * 2.0) + (($actualHours - 8.0) * 3.0), 2);
        }

        return round((8.0 * 2.0) + (1.0 * 3.0) + (($actualHours - 9.0) * 4.0), 2);
    }

    /**
     * Calculate Depnaker overtime details including multiplier hours and overtime pay.
     *
     * @return array{multiplier_hours: float, hourly_rate: float, total_overtime_pay: float}
     */
    public function calculateDepnakerOvertime(
        float $totalHours,
        bool $isHoliday = false,
        float $basicSalary = 0.0,
        float $fixedAllowance = 0.0
    ): array {
        $multiplierHours = $this->calculateDepnakerHours($totalHours, $isHoliday);
        $totalBaseSalary = $basicSalary + $fixedAllowance;
        $hourlyRate = $totalBaseSalary > 0 ? round($totalBaseSalary / 173, 2) : 0.0;
        $totalOvertimePay = round($multiplierHours * $hourlyRate, 2);

        return [
            'multiplier_hours' => $multiplierHours,
            'hourly_rate' => $hourlyRate,
            'total_overtime_pay' => $totalOvertimePay,
        ];
    }

    /**
     * Transition Overtime approval state and deposit to Time Bank if selected.
     */
    public function approveOvertime(int $overtimeId, string $targetStatus, ?int $approverId = null): HrisOvertime
    {
        return DB::transaction(function () use ($overtimeId, $targetStatus, $approverId) {
            $user = $approverId ?? (auth()->id() ?? User::first()?->id ?? 1);
            $spl = HrisOvertime::with('employee')->findOrFail($overtimeId);

            $spl->update([
                'status' => $targetStatus,
                'approved_by' => $user,
                'approved_at' => now(),
            ]);

            // If fully approved and compensation is Time Bank, deposit hours
            if ($targetStatus === 'approved' && $spl->compensation_type === 'timebank') {
                $this->depositTimeBank(
                    employeeId: $spl->employee_id,
                    hours: (float) $spl->calculated_hours,
                    notes: "Deposit Lembur SPL {$spl->spl_number} ({$spl->total_hours} Jam Riil)",
                    referenceType: 'overtime',
                    referenceId: $spl->id,
                    userId: $user
                );
            }

            return $spl->fresh(['employee.user', 'approver']);
        });
    }

    /**
     * Deposit hours into employee's Time Bank ledger.
     */
    public function depositTimeBank(
        int $employeeId,
        float $hours,
        string $notes,
        string $referenceType = 'overtime',
        ?int $referenceId = null,
        ?int $userId = null
    ): HrisTimeBank {
        return DB::transaction(function () use ($employeeId, $hours, $notes, $referenceType, $referenceId, $userId) {
            $user = $userId ?? (auth()->id() ?? User::first()?->id ?? 1);

            $lastRecord = HrisTimeBank::where('employee_id', $employeeId)
                ->latest()
                ->lockForUpdate()
                ->first();

            $currentBalance = $lastRecord ? (float) $lastRecord->balance_after : 0.0;
            $newBalance = round($currentBalance + $hours, 2);

            return HrisTimeBank::create([
                'employee_id' => $employeeId,
                'transaction_type' => 'deposit',
                'hours' => $hours,
                'balance_after' => $newBalance,
                'reference_type' => $referenceType,
                'reference_id' => $referenceId,
                'notes' => $notes,
                'created_by' => $user,
            ]);
        });
    }

    /**
     * Withdraw hours from employee's Time Bank for time-off.
     */
    public function withdrawTimeBank(
        int $employeeId,
        float $hours,
        string $notes,
        ?int $userId = null
    ): HrisTimeBank {
        return DB::transaction(function () use ($employeeId, $hours, $notes, $userId) {
            $user = $userId ?? (auth()->id() ?? User::first()?->id ?? 1);

            $lastRecord = HrisTimeBank::where('employee_id', $employeeId)
                ->latest()
                ->lockForUpdate()
                ->first();

            $currentBalance = $lastRecord ? (float) $lastRecord->balance_after : 0.0;

            if ($currentBalance < $hours) {
                throw new InvalidArgumentException("Saldo Time Bank tidak mencukupi (Tersedia: {$currentBalance} jam, diminta: {$hours} jam).");
            }

            $newBalance = round($currentBalance - $hours, 2);

            return HrisTimeBank::create([
                'employee_id' => $employeeId,
                'transaction_type' => 'withdraw',
                'hours' => $hours,
                'balance_after' => $newBalance,
                'reference_type' => 'withdraw',
                'reference_id' => null,
                'notes' => $notes,
                'created_by' => $user,
            ]);
        });
    }
}
