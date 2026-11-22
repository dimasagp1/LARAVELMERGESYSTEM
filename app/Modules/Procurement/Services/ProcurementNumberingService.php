<?php

namespace App\Modules\Procurement\Services;

use App\Core\Models\Department;
use App\Modules\Procurement\Models\PrBon;
use App\Modules\Procurement\Models\PrItem;
use App\Modules\Procurement\Models\PurchaseRequest;
use Carbon\Carbon;
use Illuminate\Support\Str;

class ProcurementNumberingService
{
    public function generatePrNumber(int $departmentId, ?Carbon $date = null): string
    {
        $date = $date ?: Carbon::now();
        $yearMonth = $date->format('Ym');

        $dept = Department::find($departmentId);
        $deptCode = $dept ? strtoupper(Str::slug($dept->code ?? $dept->name, '')) : 'GEN';
        if (strlen($deptCode) > 6) {
            $deptCode = substr($deptCode, 0, 6);
        }

        $prefix = "PR-{$deptCode}-{$yearMonth}-";
        $count = PurchaseRequest::where('pr_number', 'like', "{$prefix}%")->count() + 1;

        return sprintf('%s%04d', $prefix, $count);
    }

    public function generateBonNumber(?Carbon $date = null): string
    {
        $date = $date ?: Carbon::now();
        $yearMonth = $date->format('Ym');
        $prefix = "BON-{$yearMonth}-";
        $count = PrBon::where('bon_number', 'like', "{$prefix}%")->count() + 1;

        return sprintf('%s%04d', $prefix, $count);
    }

    public function generateInternalPoNumber(?Carbon $date = null): string
    {
        $date = $date ?: Carbon::now();
        $yearMonth = $date->format('Ym');
        $prefix = "PO-INT-{$yearMonth}-";
        $count = PrItem::where('po_number', 'like', "{$prefix}%")->count() + 1;

        return sprintf('%s%04d', $prefix, $count);
    }
}
