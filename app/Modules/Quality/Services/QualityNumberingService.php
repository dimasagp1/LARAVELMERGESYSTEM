<?php

namespace App\Modules\Quality\Services;

use App\Modules\Quality\Models\Capa;
use App\Modules\Quality\Models\ChangeRequest;
use App\Modules\Quality\Models\Deviation;
use Illuminate\Support\Carbon;

class QualityNumberingService
{
    /**
     * Generate Change Request Number: CRA-YYYY-XXXX or CRB-YYYY-XXXX.
     */
    public function generateCrNumber(string $type = 'CRA', ?int $year = null): string
    {
        $year = $year ?? Carbon::now()->year;
        $prefix = strtoupper($type);

        $last = ChangeRequest::where('type', $prefix)
            ->whereYear('created_at', $year)
            ->orderByDesc('id')
            ->first();

        $sequence = 1;
        if ($last && preg_match('/'.$prefix.'-'.$year.'-(\d+)/', $last->cr_number, $matches)) {
            $sequence = (int) $matches[1] + 1;
        }

        return sprintf('%s-%d-%04d', $prefix, $year, $sequence);
    }

    /**
     * Generate Deviation Report Number: DR/XXX/YYYY.
     */
    public function generateDeviationNumber(?int $year = null): string
    {
        $year = $year ?? Carbon::now()->year;

        $last = Deviation::whereYear('created_at', $year)
            ->orderByDesc('id')
            ->first();

        $sequence = 1;
        if ($last && preg_match('/DR\/(\d+)\/'.$year.'/', $last->deviation_number, $matches)) {
            $sequence = (int) $matches[1] + 1;
        }

        return sprintf('DR/%03d/%d', $sequence, $year);
    }

    /**
     * Generate Penyelidikan Ketidaksesuaian Number: PK/XXX/YYYY.
     */
    public function generatePkNumber(?int $year = null): string
    {
        $year = $year ?? Carbon::now()->year;

        $last = Deviation::whereYear('created_at', $year)
            ->orderByDesc('id')
            ->first();

        $sequence = 1;
        if ($last && preg_match('/PK\/(\d+)\/'.$year.'/', $last->pk_number, $matches)) {
            $sequence = (int) $matches[1] + 1;
        }

        return sprintf('PK/%03d/%d', $sequence, $year);
    }

    /**
     * Generate CAPA Number: CAPA/YYYY/XXX.
     */
    public function generateCapaNumber(?int $year = null): string
    {
        $year = $year ?? Carbon::now()->year;

        $last = Capa::whereYear('created_at', $year)
            ->orderByDesc('id')
            ->first();

        $sequence = 1;
        if ($last && preg_match('/CAPA\/'.$year.'\/(\d+)/', $last->capa_number, $matches)) {
            $sequence = (int) $matches[1] + 1;
        }

        return sprintf('CAPA/%d/%03d', $year, $sequence);
    }

    /**
     * Generate CAPA Action Number: CAPA-S{order}-{capa_id}.
     */
    public function generateCapaActionNumber(Capa $capa): string
    {
        $count = $capa->actions()->count() + 1;

        return sprintf('CAPA-S%d-%03d', $count, $capa->id);
    }
}
