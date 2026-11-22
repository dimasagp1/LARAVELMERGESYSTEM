<?php

namespace App\Modules\SupplyChain\Services;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class ScmNumberingService
{
    /**
     * Generate sequential formatted document numbers.
     * e.g. GR-202611-0001, BSTB-202611-0001, QC-202611-0001
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

    public function generateGrn(): string
    {
        return $this->generate('GR', 'scm_goods_receipts', 'grn_number');
    }

    public function generateGoodsReceipt(): string
    {
        return $this->generateGrn();
    }

    public function generateGoodsReceiptNumber(): string
    {
        return $this->generateGrn();
    }

    public function generateQcInspection(): string
    {
        return $this->generate('QC', 'scm_qc_inspections', 'inspection_number');
    }

    public function generateInspection(): string
    {
        return $this->generateQcInspection();
    }

    public function generateBstb(): string
    {
        return $this->generate('BSTB', 'scm_material_usages', 'usage_number');
    }

    public function generateUsage(): string
    {
        return $this->generateBstb();
    }

    public function generateReturn(): string
    {
        return $this->generate('RET', 'scm_material_returns', 'return_number');
    }

    public function generateMatching(): string
    {
        return $this->generate('TWM', 'scm_three_way_matchings', 'matching_number');
    }

    public function generateBill(): string
    {
        return $this->generate('BILL', 'scm_vendor_bills', 'bill_number');
    }

    public function generateMps(): string
    {
        return $this->generate('MPS', 'scm_production_schedules', 'schedule_number');
    }

    public function generateMrpRun(): string
    {
        return $this->generate('MRP', 'scm_mrp_runs', 'run_number');
    }

    public function generateMovement(): string
    {
        return $this->generate('MOV', 'scm_stock_movements', 'movement_number');
    }
}
