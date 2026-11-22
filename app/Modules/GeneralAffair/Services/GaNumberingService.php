<?php

namespace App\Modules\GeneralAffair\Services;

use App\Core\Models\Department;
use App\Core\Models\Plant;
use App\Modules\GeneralAffair\Models\AssetCategory;
use App\Modules\GeneralAffair\Models\AtkRequest;
use App\Modules\GeneralAffair\Models\FpuRequest;
use App\Modules\GeneralAffair\Models\GaAsset;
use App\Modules\GeneralAffair\Models\WoHandover;
use App\Modules\GeneralAffair\Models\WorkOrder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

class GaNumberingService
{
    /**
     * Generate Work Order Number: WO-YYYYMM-XXXX.
     */
    public function generateWoNumber(?int $year = null, ?int $month = null): string
    {
        $year = $year ?? Carbon::now()->year;
        $month = $month ?? Carbon::now()->month;
        $period = sprintf('%04d%02d', $year, $month);

        $last = WorkOrder::where('wo_number', 'like', "WO-{$period}-%")
            ->orderByDesc('id')
            ->first();

        $sequence = 1;
        if ($last && preg_match('/WO-'.$period.'-(\d+)/', $last->wo_number, $matches)) {
            $sequence = (int) $matches[1] + 1;
        }

        return sprintf('WO-%s-%04d', $period, $sequence);
    }

    /**
     * Generate FPU Number: FPU-YYYYMM-XXXX.
     */
    public function generateFpuNumber(?int $year = null, ?int $month = null): string
    {
        $year = $year ?? Carbon::now()->year;
        $month = $month ?? Carbon::now()->month;
        $period = sprintf('%04d%02d', $year, $month);

        $last = FpuRequest::where('fpu_number', 'like', "FPU-{$period}-%")
            ->orderByDesc('id')
            ->first();

        $sequence = 1;
        if ($last && preg_match('/FPU-'.$period.'-(\d+)/', $last->fpu_number, $matches)) {
            $sequence = (int) $matches[1] + 1;
        }

        return sprintf('FPU-%s-%04d', $period, $sequence);
    }

    /**
     * Generate BAST Handover Number: BAST-YYYYMM-XXXX.
     */
    public function generateBastNumber(?int $year = null, ?int $month = null): string
    {
        $year = $year ?? Carbon::now()->year;
        $month = $month ?? Carbon::now()->month;
        $period = sprintf('%04d%02d', $year, $month);

        $last = WoHandover::where('bast_number', 'like', "BAST-{$period}-%")
            ->orderByDesc('id')
            ->first();

        $sequence = 1;
        if ($last && preg_match('/BAST-'.$period.'-(\d+)/', $last->bast_number, $matches)) {
            $sequence = (int) $matches[1] + 1;
        }

        return sprintf('BAST-%s-%04d', $period, $sequence);
    }

    /**
     * Generate ATK/RT Request Number: ATK-YYYYMM-XXXX or RT-YYYYMM-XXXX.
     */
    public function generateAtkNumber(string $type = 'ATK', ?int $year = null, ?int $month = null): string
    {
        $prefix = strtoupper($type) === 'RT' ? 'RT' : 'ATK';
        $year = $year ?? Carbon::now()->year;
        $month = $month ?? Carbon::now()->month;
        $period = sprintf('%04d%02d', $year, $month);

        $last = AtkRequest::where('request_number', 'like', "{$prefix}-{$period}-%")
            ->whereNull('parent_id')
            ->orderByDesc('id')
            ->first();

        $sequence = 1;
        if ($last && preg_match('/'.$prefix.'-'.$period.'-(\d+)/', $last->request_number, $matches)) {
            $sequence = (int) $matches[1] + 1;
        }

        return sprintf('%s-%s-%04d', $prefix, $period, $sequence);
    }

    /**
     * Generate Revision Number: ATK-YYYYMM-XXXX-R1.
     */
    public function generateAtkRevisionNumber(AtkRequest $parent): string
    {
        $baseNumber = preg_replace('/-R\d+$/', '', $parent->request_number);
        $revisionsCount = AtkRequest::where('request_number', 'like', "{$baseNumber}-R%")->count() + 1;

        return sprintf('%s-R%d', $baseNumber, $revisionsCount);
    }

    /**
     * Generate Fixed Asset Code: HII-{PLANT}-{DEPT}-{KATEGORI}-{SINGKATAN}-{NOMOR}.
     */
    public function generateAssetCode(
        Plant $plant,
        Department $department,
        AssetCategory $category,
        string $shortName = 'AST'
    ): string {
        $plantCode = str_pad((string) ($plant->code ?? '01'), 2, '0', STR_PAD_LEFT);
        $deptCode = strtoupper(substr($department->name ?? 'GA', 0, 3));
        $catCode = str_pad((string) ($category->code ?? '01'), 2, '0', STR_PAD_LEFT);
        $shortCode = strtoupper(substr(preg_replace('/[^A-Za-z0-9]/', '', $shortName), 0, 3));

        $prefix = sprintf('HII-%s-%s-%s-%s', $plantCode, $deptCode, $catCode, $shortCode);

        $last = GaAsset::where('asset_code', 'like', "{$prefix}-%")
            ->orderByDesc('id')
            ->first();

        $sequence = 1;
        if ($last && preg_match('/'.preg_quote($prefix, '/').'-(\d+)/', $last->asset_code, $matches)) {
            $sequence = (int) $matches[1] + 1;
        }

        return sprintf('%s-%04d', $prefix, $sequence);
    }

    /**
     * Generate QR Token for Patrol Checkpoints.
     */
    public function generateQrToken(string $prefix = 'CP'): string
    {
        return sprintf('%s-%s-%s', $prefix, date('Ymd'), Str::upper(Str::random(6)));
    }
}
