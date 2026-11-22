<?php

namespace App\Modules\Rnd\Services;

use App\Models\User;
use App\Modules\Rnd\Models\Formula;
use App\Modules\Rnd\Models\NpdProposal;
use App\Modules\Rnd\Models\Prf;
use App\Modules\Rnd\Models\SampleEvaluation;
use App\Modules\Rnd\Models\TrialPm;
use App\Modules\Rnd\Models\TrialPmApproval;
use App\Modules\Rnd\Models\TrialRm;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class RndWorkflowService
{
    public function __construct(
        protected RndNumberingService $numbering,
        protected FormulaCalculationService $calculation
    ) {}

    /**
     * Create new Formula with items and strict 100% calculation.
     *
     * @param  array<string, mixed>  $data
     * @param  array<int, array<string, mixed>>  $items
     */
    public function createFormula(array $data, array $items, User $user): Formula
    {
        return DB::transaction(function () use ($data, $items, $user) {
            $servingSizeG = (float) ($data['target_serving_size_g'] ?? 2.0);
            $servingsPerPack = (int) ($data['servings_per_pack'] ?? 30);

            $calc = $this->calculation->calculate($items, $servingSizeG, $servingsPerPack);

            $code = $this->numbering->generateFormulaCode($data['version'] ?? 1);

            $formula = Formula::create([
                'code' => $code,
                'product_id' => $data['product_id'],
                'parent_formula_id' => $data['parent_formula_id'] ?? null,
                'version' => $data['version'] ?? 1,
                'formula_type' => $data['formula_type'] ?? 'new_product',
                'stage' => $data['stage'] ?? 'lab_trial',
                'target_serving_size_g' => $servingSizeG,
                'serving_unit' => $data['serving_unit'] ?? 'sachet',
                'servings_per_pack' => $servingsPerPack,
                'total_percentage' => $calc['total_percentage'],
                'total_cost_per_kg' => $calc['total_cost_per_kg'],
                'total_cost_per_pack' => $calc['total_cost_per_pack'],
                'notes' => $data['notes'] ?? null,
                'status' => $data['status'] ?? 'draft',
                'created_by' => $user->id,
                'custom_attributes' => $data['custom_attributes'] ?? null,
            ]);

            foreach ($calc['items'] as $item) {
                $formula->items()->create($item);
            }

            return $formula->load(['items.material', 'items.vendor', 'product', 'creator']);
        });
    }

    /**
     * Submit formula for Stage 1 (OM) approval.
     */
    public function submitFormula(Formula $formula, User $user): Formula
    {
        if ($formula->status !== 'draft' && $formula->status !== 'rejected') {
            throw ValidationException::withMessages([
                'status' => "Formula dengan status {$formula->status} tidak dapat diajukan.",
            ]);
        }

        $formula->update([
            'status' => 'pending_stage_1',
            'rejection_notes' => null,
        ]);

        return $formula;
    }

    /**
     * Operational Manager approves Stage 1.
     */
    public function approveFormulaStage1(Formula $formula, User $omUser): Formula
    {
        if ($formula->status !== 'pending_stage_1') {
            throw ValidationException::withMessages([
                'status' => 'Formula belum diajukan atau telah diproses pada tahap ini.',
            ]);
        }

        $formula->update([
            'status' => 'pending_stage_2',
            'approved_by_om' => $omUser->id,
        ]);

        return $formula;
    }

    /**
     * General Manager approves Stage 2 (Final Approved & Locked).
     */
    public function approveFormulaStage2(Formula $formula, User $gmUser): Formula
    {
        if ($formula->status !== 'pending_stage_2') {
            throw ValidationException::withMessages([
                'status' => 'Formula harus disetujui Tahap 1 oleh OM terlebih dahulu.',
            ]);
        }

        $formula->update([
            'status' => 'approved',
            'approved_by_gm' => $gmUser->id,
            'locked_at' => now(),
        ]);

        return $formula;
    }

    /**
     * Reject formula with notes.
     */
    public function rejectFormula(Formula $formula, User $user, string $notes): Formula
    {
        $formula->update([
            'status' => 'rejected',
            'rejection_notes' => $notes,
        ]);

        return $formula;
    }

    /**
     * Auto-Clone Reformulation (Loop Reformulasi Otomatis V1 -> V2 -> V3).
     */
    public function reformulaClone(Formula $parentFormula, User $user): Formula
    {
        return DB::transaction(function () use ($parentFormula, $user) {
            $nextVersion = $parentFormula->version + 1;
            $newCode = $this->numbering->generateFormulaCode($nextVersion, $parentFormula->code);

            $newFormula = Formula::create([
                'code' => $newCode,
                'product_id' => $parentFormula->product_id,
                'parent_formula_id' => $parentFormula->id,
                'version' => $nextVersion,
                'formula_type' => 'existing',
                'stage' => 'lab_trial',
                'target_serving_size_g' => $parentFormula->target_serving_size_g,
                'serving_unit' => $parentFormula->serving_unit,
                'servings_per_pack' => $parentFormula->servings_per_pack,
                'total_percentage' => $parentFormula->total_percentage,
                'total_cost_per_kg' => $parentFormula->total_cost_per_kg,
                'total_cost_per_pack' => $parentFormula->total_cost_per_pack,
                'notes' => "Reformulasi turunan dari {$parentFormula->code}",
                'status' => 'draft',
                'created_by' => $user->id,
            ]);

            foreach ($parentFormula->items as $item) {
                $newFormula->items()->create([
                    'material_product_id' => $item->material_product_id,
                    'vendor_id' => $item->vendor_id,
                    'percentage' => $item->percentage,
                    'weight_per_serving_g' => $item->weight_per_serving_g,
                    'price_per_kg' => $item->price_per_kg,
                    'price_per_serving' => $item->price_per_serving,
                    'notes' => $item->notes,
                ]);
            }

            return $newFormula->load(['items.material', 'items.vendor', 'product']);
        });
    }

    /**
     * Create Trial RM with target vs actual verification parameters.
     *
     * @param  array<string, mixed>  $data
     * @param  array<int, array<string, mixed>>  $verifications
     */
    public function createTrialRm(array $data, array $verifications, User $user): TrialRm
    {
        return DB::transaction(function () use ($data, $verifications, $user) {
            $code = $this->numbering->generateTrialRmCode();

            $trial = TrialRm::create([
                'code' => $code,
                'formula_id' => $data['formula_id'],
                'product_id' => $data['product_id'],
                'batch_qty_kg' => $data['batch_qty_kg'] ?? 1.0,
                'process_steps' => $data['process_steps'] ?? null,
                'decision' => $data['decision'] ?? 'lulus',
                'status' => $data['status'] ?? 'draft',
                'created_by' => $user->id,
                'notes' => $data['notes'] ?? null,
                'custom_attributes' => $data['custom_attributes'] ?? null,
            ]);

            foreach ($verifications as $v) {
                $trial->verifications()->create([
                    'parameter' => $v['parameter'],
                    'target_spec' => $v['target_spec'] ?? null,
                    'actual_result' => $v['actual_result'] ?? null,
                    'is_compliant' => (bool) ($v['is_compliant'] ?? true),
                    'notes' => $v['notes'] ?? null,
                ]);
            }

            return $trial->load(['verifications', 'formula', 'product']);
        });
    }

    /**
     * Create Trial PM with 4-departments collective approvals initialized.
     *
     * @param  array<string, mixed>  $data
     */
    public function createTrialPm(array $data, User $user): TrialPm
    {
        return DB::transaction(function () use ($data) {
            $code = $this->numbering->generateTrialPmCode();

            $trialPm = TrialPm::create([
                'code' => $code,
                'product_id' => $data['product_id'],
                'vendor_id' => $data['vendor_id'] ?? null,
                'packaging_specs_json' => $data['packaging_specs_json'] ?? null,
                'machine_params_json' => $data['machine_params_json'] ?? null,
                'leakage_test_result' => $data['leakage_test_result'] ?? null,
                'seal_strength_result' => $data['seal_strength_result'] ?? null,
                'barcode_readability' => $data['barcode_readability'] ?? null,
                'photos_json' => $data['photos_json'] ?? null,
                'status' => 'draft',
                'custom_attributes' => $data['custom_attributes'] ?? null,
            ]);

            // Initialize 4-Department Collective Approvals
            $departments = ['R&D', 'QC', 'Production', 'Engineering'];
            foreach ($departments as $dept) {
                $trialPm->approvals()->create([
                    'department_name' => $dept,
                    'status' => 'pending',
                ]);
            }

            return $trialPm->load(['approvals', 'product', 'vendor']);
        });
    }

    /**
     * Sign Trial PM on behalf of one department.
     */
    public function signTrialPmDepartment(TrialPm $trialPm, string $deptName, User $user, string $status = 'approved', ?string $notes = null): TrialPmApproval
    {
        return DB::transaction(function () use ($trialPm, $deptName, $user, $status, $notes) {
            $approval = $trialPm->approvals()->firstOrCreate(
                ['department_name' => $deptName],
                ['status' => 'pending']
            );

            $approval->update([
                'user_id' => $user->id,
                'status' => $status,
                'notes' => $notes,
                'signed_at' => now(),
            ]);

            // If all 4 approved, transition Trial PM status to pending_approval for OM
            if ($trialPm->isFullyDepartmentApproved()) {
                $trialPm->update(['status' => 'pending_approval']);
            }

            return $approval;
        });
    }

    /**
     * Operational Manager final approval for Trial PM (requires 4/4 departments consensus).
     */
    public function approveTrialPmFinal(TrialPm $trialPm, User $omUser): TrialPm
    {
        if (! $trialPm->isFullyDepartmentApproved()) {
            throw ValidationException::withMessages([
                'approvals' => 'Trial PM belum dapat disahkan: seluruh 4 departemen (R&D, QC, Produksi, Engineering) wajib memberikan paraf disetujui terlebih dahulu.',
            ]);
        }

        $trialPm->update([
            'status' => 'approved',
            'om_approved_by' => $omUser->id,
            'om_approved_at' => now(),
        ]);

        return $trialPm;
    }

    /**
     * Create PRF.
     *
     * @param  array<string, mixed>  $data
     */
    public function createPrf(array $data, User $user): Prf
    {
        $code = $this->numbering->generatePrfCode();

        return Prf::create([
            'code' => $code,
            'product_id' => $data['product_id'],
            'target_market' => $data['target_market'] ?? null,
            'product_category' => $data['product_category'] ?? null,
            'concept' => $data['concept'] ?? null,
            'target_launch_date' => $data['target_launch_date'] ?? null,
            'attachments_json' => $data['attachments_json'] ?? null,
            'status' => 'submitted',
            'created_by' => $user->id,
            'custom_attributes' => $data['custom_attributes'] ?? null,
        ]);
    }

    /**
     * Create NPD Proposal.
     *
     * @param  array<string, mixed>  $data
     */
    public function createNpdProposal(array $data, User $user): NpdProposal
    {
        $code = $this->numbering->generateNpdCode();

        return NpdProposal::create([
            'code' => $code,
            'prf_id' => $data['prf_id'] ?? null,
            'product_id' => $data['product_id'],
            'target_cogs' => $data['target_cogs'] ?? 0,
            'target_selling_price' => $data['target_selling_price'] ?? 0,
            'development_start' => $data['development_start'] ?? null,
            'development_end' => $data['development_end'] ?? null,
            'pic_user_id' => $data['pic_user_id'] ?? $user->id,
            'project_team_json' => $data['project_team_json'] ?? null,
            'status' => 'in_progress',
            'notes' => $data['notes'] ?? null,
            'attachments_json' => $data['attachments_json'] ?? null,
            'custom_attributes' => $data['custom_attributes'] ?? null,
        ]);
    }

    /**
     * Create Sample Evaluation with auto overall score calculation.
     *
     * @param  array<string, mixed>  $data
     */
    public function createSampleEvaluation(array $data): SampleEvaluation
    {
        $taste = (float) ($data['average_taste'] ?? 0);
        $color = (float) ($data['average_color'] ?? 0);
        $aroma = (float) ($data['average_aroma'] ?? 0);
        $texture = (float) ($data['average_texture'] ?? 0);
        $aftertaste = (float) ($data['average_aftertaste'] ?? 0);

        $overall = ($taste + $color + $aroma + $texture + $aftertaste) / 5.0;

        return SampleEvaluation::create([
            'npd_proposal_id' => $data['npd_proposal_id'] ?? null,
            'product_id' => $data['product_id'],
            'sample_code' => $data['sample_code'],
            'session_date' => $data['session_date'] ?? now()->toDateString(),
            'batch_code' => $data['batch_code'] ?? null,
            'panelist_count' => $data['panelist_count'] ?? 15,
            'average_taste' => $taste,
            'average_color' => $color,
            'average_aroma' => $aroma,
            'average_texture' => $texture,
            'average_aftertaste' => $aftertaste,
            'overall_score' => round($overall, 2),
            'status' => $data['status'] ?? 'completed',
            'conclusion' => $data['conclusion'] ?? null,
            'custom_attributes' => $data['custom_attributes'] ?? null,
        ]);
    }
}
