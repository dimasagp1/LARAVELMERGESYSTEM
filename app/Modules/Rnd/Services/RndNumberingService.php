<?php

namespace App\Modules\Rnd\Services;

use App\Modules\Rnd\Models\Formula;
use App\Modules\Rnd\Models\FormulaApproval;
use App\Modules\Rnd\Models\NpdProposal;
use App\Modules\Rnd\Models\PreformulationStudy;
use App\Modules\Rnd\Models\Prf;
use App\Modules\Rnd\Models\TechnologyTransfer;
use App\Modules\Rnd\Models\TrialPm;
use App\Modules\Rnd\Models\TrialRm;

class RndNumberingService
{
    /**
     * Generate PRF code: PRF-YYYYMM-XXX
     */
    public function generatePrfCode(): string
    {
        $prefix = 'PRF-'.date('Ym').'-';
        $count = Prf::where('code', 'like', "{$prefix}%")->count() + 1;

        return sprintf('%s%03d', $prefix, $count);
    }

    /**
     * Generate NPD Proposal code: NPD-YYYYMM-XXX
     */
    public function generateNpdCode(): string
    {
        $prefix = 'NPD-'.date('Ym').'-';
        $count = NpdProposal::where('code', 'like', "{$prefix}%")->count() + 1;

        return sprintf('%s%03d', $prefix, $count);
    }

    /**
     * Generate Preformulation code: PFS-YYYYMM-XXX
     */
    public function generatePreformulationCode(): string
    {
        $prefix = 'PFS-'.date('Ym').'-';
        $count = PreformulationStudy::where('code', 'like', "{$prefix}%")->count() + 1;

        return sprintf('%s%03d', $prefix, $count);
    }

    /**
     * Generate Formula code: FRM-YYYYMM-XXX (or with version: FRM-YYYYMM-XXX-V2)
     */
    public function generateFormulaCode(int $version = 1, ?string $baseCode = null): string
    {
        if ($version > 1 && $baseCode) {
            // Strip any previous -V suffix
            $cleanBase = preg_replace('/-V\d+$/i', '', $baseCode);

            return "{$cleanBase}-V{$version}";
        }

        $prefix = 'FRM-'.date('Ym').'-';
        $count = Formula::where('code', 'like', "{$prefix}%")->count() + 1;

        return sprintf('%s%03d', $prefix, $count);
    }

    /**
     * Generate Trial RM code: TRM-YYYYMM-XXX-A
     */
    public function generateTrialRmCode(): string
    {
        $prefix = 'TRM-'.date('Ym').'-';
        $count = TrialRm::where('code', 'like', "{$prefix}%")->count() + 1;

        return sprintf('%s%03d-A', $prefix, $count);
    }

    /**
     * Generate Trial PM code: TPM-YYYYMM-XXX
     */
    public function generateTrialPmCode(): string
    {
        $prefix = 'TPM-'.date('Ym').'-';
        $count = TrialPm::where('code', 'like', "{$prefix}%")->count() + 1;

        return sprintf('%s%03d', $prefix, $count);
    }

    /**
     * Generate Formula Approval code: FA-XXXX
     */
    public function generateFormulaApprovalCode(): string
    {
        $count = FormulaApproval::count() + 1;

        return sprintf('FA-%04d', $count);
    }

    /**
     * Generate Technology Transfer code: TT-YYYYMM-XXX
     */
    public function generateTechTransferCode(): string
    {
        $prefix = 'TT-'.date('Ym').'-';
        $count = TechnologyTransfer::where('protocol_number', 'like', "{$prefix}%")->count() + 1;

        return sprintf('%s%03d', $prefix, $count);
    }
}
