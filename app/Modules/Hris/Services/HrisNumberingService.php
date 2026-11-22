<?php

namespace App\Modules\Hris\Services;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class HrisNumberingService
{
    /**
     * Generate sequential formatted document numbers.
     * e.g. SPL-202611-0001, LEV-202611-0001, KIZ-202611-0001
     */
    public function generate(string $prefix, string $table, string $column = 'number'): string
    {
        $yearMonth = Carbon::now()->format('Ym');
        $searchPrefix = "{$prefix}-{$yearMonth}-";

        return DB::transaction(function () use ($searchPrefix, $table, $column) {
            $lastRecord = DB::table($table)
                ->where($column, 'like', "{$searchPrefix}%")
                ->orderByDesc($column)
                ->lockForUpdate()
                ->first();

            $nextSequence = 1;

            if ($lastRecord && isset($lastRecord->$column)) {
                $parts = explode('-', $lastRecord->$column);
                $lastNum = (int) end($parts);
                $nextSequence = $lastNum + 1;
            }

            return sprintf('%s%04d', $searchPrefix, $nextSequence);
        });
    }

    public function generateNik(): string
    {
        $year = Carbon::now()->format('Y');
        $searchPrefix = "HII-{$year}-";

        return DB::transaction(function () use ($searchPrefix) {
            $lastRecord = DB::table('hris_employees')
                ->where('nik', 'like', "{$searchPrefix}%")
                ->orderByDesc('nik')
                ->lockForUpdate()
                ->first();

            $nextSequence = 1;

            if ($lastRecord && isset($lastRecord->nik)) {
                $parts = explode('-', $lastRecord->nik);
                $lastNum = (int) end($parts);
                $nextSequence = $lastNum + 1;
            }

            return sprintf('%s%04d', $searchPrefix, $nextSequence);
        });
    }

    public function generateLeave(): string
    {
        return $this->generate('LEV', 'hris_leaves', 'leave_number');
    }

    public function generateLeaveNumber(): string
    {
        return $this->generateLeave();
    }

    public function generateOvertime(): string
    {
        return $this->generate('SPL', 'hris_overtimes', 'spl_number');
    }

    public function generateSpl(): string
    {
        return $this->generateOvertime();
    }

    public function generateSplNumber(): string
    {
        return $this->generateOvertime();
    }

    public function generateOvertimeNumber(): string
    {
        return $this->generateOvertime();
    }

    public function generateKizuki(): string
    {
        return $this->generate('KIZ', 'hris_kizukis', 'kizuki_number');
    }

    public function generateKizukiNumber(): string
    {
        return $this->generateKizuki();
    }

    public function generateSalary(): string
    {
        return $this->generate('SAL', 'hris_salaries', 'salary_number');
    }

    public function generateSalaryNumber(): string
    {
        return $this->generateSalary();
    }

    public function generateKpi(): string
    {
        return $this->generate('KPI', 'hris_kpis', 'kpi_number');
    }

    public function generateShiftRequest(): string
    {
        return $this->generate('SFT', 'hris_shift_requests', 'request_number');
    }
}
