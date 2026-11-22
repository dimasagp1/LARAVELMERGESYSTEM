<?php

namespace App\Modules\Rnd\Controllers;

use App\Core\Services\ModuleManagerService;
use App\Http\Controllers\Controller;
use App\Models\User;
use App\Modules\Rnd\Models\Formula;
use App\Modules\Rnd\Models\TrialPm;
use App\Modules\Rnd\Services\RndWorkflowService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ApprovalCenterController extends Controller
{
    public function __construct(
        protected RndWorkflowService $workflowService,
        protected ModuleManagerService $moduleManager
    ) {}

    public function index(Request $request): Response
    {
        $roleFilter = $request->query('role', 'all');

        // Queue Tahap 1 (Operational Manager)
        $stage1Formulas = Formula::with(['product', 'creator', 'items.material'])
            ->where('status', 'pending_stage_1')
            ->latest()
            ->get();

        $stage1TrialPms = TrialPm::with(['product', 'vendor', 'approvals.user'])
            ->where('status', 'pending_approval')
            ->latest()
            ->get();

        // Queue Tahap 2 (General Manager)
        $stage2Formulas = Formula::with(['product', 'creator', 'omApprover', 'items.material'])
            ->where('status', 'pending_stage_2')
            ->latest()
            ->get();

        return Inertia::render('Modules/Rnd/ApprovalCenter/Index', [
            'module' => [
                'name' => 'Approval Center R&D',
                'slug' => 'rnd',
                'color_theme' => 'cyan',
                'menus' => $this->moduleManager->getModuleMenus('rnd'),
            ],
            'stage1Formulas' => $stage1Formulas,
            'stage1TrialPms' => $stage1TrialPms,
            'stage2Formulas' => $stage2Formulas,
            'stats' => [
                'total_stage_1' => $stage1Formulas->count() + $stage1TrialPms->count(),
                'total_stage_2' => $stage2Formulas->count(),
            ],
            'currentRoleFilter' => $roleFilter,
        ]);
    }

    public function approve(Request $request, string $type, int $id): RedirectResponse
    {
        $user = $request->user() ?? User::first();

        if ($type === 'formula') {
            $formula = Formula::findOrFail($id);
            if ($formula->status === 'pending_stage_1') {
                $this->workflowService->approveFormulaStage1($formula, $user);
                $msg = "Formula {$formula->code} disetujui Tahap 1 oleh OM dan diteruskan ke GM.";
            } elseif ($formula->status === 'pending_stage_2') {
                $this->workflowService->approveFormulaStage2($formula, $user);
                $msg = "Formula {$formula->code} disahkan secara final oleh General Manager dan terkunci permanen.";
            } else {
                return back()->with('error', 'Status formula tidak valid untuk persetujuan.');
            }

            return back()->with('success', $msg);
        }

        if ($type === 'trial_pm') {
            $trialPm = TrialPm::findOrFail($id);
            $this->workflowService->approveTrialPmFinal($trialPm, $user);

            return back()->with('success', "Trial PM {$trialPm->code} berhasil disahkan oleh Operational Manager.");
        }

        return back()->with('error', 'Tipe dokumen persetujuan tidak dikenali.');
    }

    public function reject(Request $request, string $type, int $id): RedirectResponse
    {
        $request->validate([
            'notes' => 'required|string|min:5',
        ]);

        $user = $request->user() ?? User::first();
        $notes = $request->input('notes');

        if ($type === 'formula') {
            $formula = Formula::findOrFail($id);
            $this->workflowService->rejectFormula($formula, $user, $notes);

            return back()->with('success', "Formula {$formula->code} telah ditolak dengan catatan revisi.");
        }

        return back()->with('error', 'Tipe dokumen tidak valid.');
    }
}
