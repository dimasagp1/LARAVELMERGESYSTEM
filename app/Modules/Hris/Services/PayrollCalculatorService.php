<?php

namespace App\Modules\Hris\Services;

use App\Models\User;
use App\Modules\Hris\Models\HrisAttendance;
use App\Modules\Hris\Models\HrisEmployee;
use App\Modules\Hris\Models\HrisOvertime;
use App\Modules\Hris\Models\HrisSalary;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class PayrollCalculatorService
{
    public function __construct(
        protected HrisNumberingService $numberingService
    ) {}

    /**
     * Compute comprehensive monthly salary for an employee.
     */
    public function calculateSalary(int $employeeId, int $month, int $year, ?int $userId = null): HrisSalary
    {
        return DB::transaction(function () use ($employeeId, $month, $year, $userId) {
            $creator = $userId ?? (auth()->id() ?? User::first()?->id ?? 1);
            $employee = HrisEmployee::with(['grade', 'user', 'department'])->findOrFail($employeeId);

            $startDate = Carbon::createFromDate($year, $month, 1)->startOfMonth()->toDateString();
            $endDate = Carbon::createFromDate($year, $month, 1)->endOfMonth()->toDateString();

            // 1. Attendance Statistics
            $attendances = HrisAttendance::where('employee_id', $employeeId)
                ->whereBetween('date', [$startDate, $endDate])
                ->get();

            $presentDays = $attendances->whereIn('status', ['present', 'late'])->count();
            $lateMinutes = (int) $attendances->sum('late_minutes');

            // 2. Approved Paid Overtime
            $overtimeHours = (float) HrisOvertime::where('employee_id', $employeeId)
                ->where('status', 'approved')
                ->whereIn('compensation_type', ['paid', 'cash'])
                ->whereBetween('date', [$startDate, $endDate])
                ->sum('calculated_hours');

            // 3. Earnings Calculation
            $basicSalary = (float) $employee->basic_salary;
            $fixedAllowances = round($basicSalary * 0.15, 2); // Tunjangan Jabatan & Golongan (15%)
            $attendanceAllowances = round($presentDays * 40000, 2); // Uang makan & transport per hari hadir

            $hourlyRate = $basicSalary > 0 ? ($basicSalary / 173) : 0;
            $overtimePay = round($hourlyRate * $overtimeHours, 2);

            $grossSalary = $basicSalary + $fixedAllowances + $attendanceAllowances + $overtimePay;

            // 4. Deductions Calculation
            // Late penalty: Rp 1.500 / minute
            $lateDeductions = round($lateMinutes * 1500, 2);
            $unpaidLeaveDeductions = 0; // default 0

            // BPJS Ketenagakerjaan: JHT (2%) + JP (1%) = 3%
            $bpjsTk = round($basicSalary * 0.03, 2);

            // BPJS Kesehatan: 1% (Capped at 12 Million)
            $bpjsKesBasis = min($basicSalary, 12000000);
            $bpjsKes = round($bpjsKesBasis * 0.01, 2);

            // PPh 21 TER (Tarif Efektif Rata-Rata) Category A
            $terRate = $this->getPph21TerRate($grossSalary);
            $pph21Ter = round($grossSalary * $terRate, 2);

            $totalDeductions = $lateDeductions + $unpaidLeaveDeductions + $bpjsTk + $bpjsKes + $pph21Ter;
            $netSalary = max(0, round($grossSalary - $totalDeductions, 2));

            // Generate or update salary record
            $salary = HrisSalary::updateOrCreate(
                [
                    'employee_id' => $employee->id,
                    'period_month' => $month,
                    'period_year' => $year,
                ],
                [
                    'salary_number' => $this->numberingService->generateSalary(),
                    'basic_salary' => $basicSalary,
                    'fixed_allowances' => $fixedAllowances,
                    'attendance_allowances' => $attendanceAllowances,
                    'overtime_pay' => $overtimePay,
                    'late_deductions' => $lateDeductions,
                    'unpaid_leave_deductions' => $unpaidLeaveDeductions,
                    'bpjs_tk_deduction' => $bpjsTk,
                    'bpjs_kes_deduction' => $bpjsKes,
                    'pph21_ter_deduction' => $pph21Ter,
                    'net_salary' => $netSalary,
                    'payment_status' => 'approved',
                    'payment_date' => Carbon::createFromDate($year, $month, 28)->toDateString(),
                    'created_by' => $creator,
                ]
            );

            return $salary->load(['employee.user', 'employee.department', 'employee.grade']);
        });
    }

    /**
     * Compute official Indonesian PPh 21 TER (Tarif Efektif Rata-Rata) bracket.
     */
    protected function getPph21TerRate(float $grossSalary): float
    {
        if ($grossSalary <= 5400000) {
            return 0.0;
        } elseif ($grossSalary <= 5650000) {
            return 0.0025; // 0.25%
        } elseif ($grossSalary <= 5950000) {
            return 0.0050; // 0.50%
        } elseif ($grossSalary <= 6300000) {
            return 0.0075; // 0.75%
        } elseif ($grossSalary <= 6750000) {
            return 0.0100; // 1.00%
        } elseif ($grossSalary <= 7500000) {
            return 0.0125; // 1.25%
        } elseif ($grossSalary <= 8550000) {
            return 0.0150; // 1.50%
        } elseif ($grossSalary <= 9650000) {
            return 0.0175; // 1.75%
        } elseif ($grossSalary <= 10050000) {
            return 0.0200; // 2.00%
        } elseif ($grossSalary <= 12500000) {
            return 0.0300; // 3.00%
        }

        return 0.0500; // 5.00%
    }

    public function calculateMonthlySalary(HrisEmployee|int $employee, int $month, int $year, ?int $userId = null): HrisSalary
    {
        $employeeId = $employee instanceof HrisEmployee ? $employee->id : $employee;

        return $this->calculateSalary($employeeId, $month, $year, $userId);
    }
}
