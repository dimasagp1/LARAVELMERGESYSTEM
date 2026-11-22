<?php

namespace App\Modules\Production\Services;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class ProductionNumberingService
{
    /**
     * Generate sequential formatted document numbers.
     * e.g. WPS-202611-0001, LHP-202611-0001
     */
    public function generate(string $prefix, string $table, string $column = 'plan_number'): string
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

    public function generateWpsNumber(): string
    {
        return $this->generate('WPS', 'weekly_plans', 'plan_number');
    }

    public function generateReportNumber(): string
    {
        return $this->generate('LHP', 'laporan_harians', 'report_number');
    }

    public function generateBatchNumber(string $prefix = 'EXT'): string
    {
        $year = Carbon::now()->format('Y');
        $searchPrefix = "{$prefix}-{$year}-";

        return DB::transaction(function () use ($searchPrefix) {
            $lastRecord = DB::table('weekly_plans')
                ->where('batch_number', 'like', "{$searchPrefix}%")
                ->orderByDesc('batch_number')
                ->lockForUpdate()
                ->first();

            $nextSequence = 1;

            if ($lastRecord && isset($lastRecord->batch_number)) {
                $parts = explode('-', $lastRecord->batch_number);
                $lastNum = (int) end($parts);
                $nextSequence = $lastNum + 1;
            }

            return sprintf('%s%03d', $searchPrefix, $nextSequence);
        });
    }
}
