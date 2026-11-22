<?php

namespace App\Modules\Rnd\Controllers;

use App\Core\Models\Product;
use App\Core\Models\Vendor;
use App\Core\Services\ModuleManagerService;
use App\Http\Controllers\Controller;
use App\Models\User;
use App\Modules\Rnd\Models\TrialPm;
use App\Modules\Rnd\Services\RndWorkflowService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class TrialPmController extends Controller
{
    public function __construct(
        protected RndWorkflowService $workflowService,
        protected ModuleManagerService $moduleManager
    ) {}

    public function index(Request $request): Response
    {
        $query = TrialPm::with(['product', 'vendor', 'approvals.user'])
            ->latest();

        if ($search = $request->query('q')) {
            $query->where('code', 'like', "%{$search}%")
                ->orWhereHas('product', fn ($q) => $q->where('name', 'like', "%{$search}%"));
        }

        $trials = $query->paginate(12)->withQueryString();

        return Inertia::render('Modules/Rnd/TrialPm/Index', [
            'module' => [
                'name' => 'Trial Packaging Material (PM)',
                'slug' => 'rnd',
                'color_theme' => 'cyan',
                'menus' => $this->moduleManager->getModuleMenus('rnd'),
            ],
            'trials' => $trials,
            'stats' => [
                'total' => TrialPm::count(),
                'draft' => TrialPm::where('status', 'draft')->count(),
                'pending' => TrialPm::where('status', 'pending_approval')->count(),
                'approved' => TrialPm::where('status', 'approved')->count(),
            ],
        ]);
    }

    public function create(): Response
    {
        $products = Product::where('type', 'fg')->where('is_active', true)->get(['id', 'sku', 'name']);
        $vendors = Vendor::where('is_active', true)->get(['id', 'code', 'name']);

        return Inertia::render('Modules/Rnd/TrialPm/Form', [
            'module' => [
                'name' => 'Trial Packaging Material (PM)',
                'slug' => 'rnd',
                'color_theme' => 'cyan',
                'menus' => $this->moduleManager->getModuleMenus('rnd'),
            ],
            'products' => $products,
            'vendors' => $vendors,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'product_id' => 'required|exists:products,id',
            'vendor_id' => 'nullable|exists:vendors,id',
            'packaging_specs_json' => 'nullable|array',
            'machine_params_json' => 'nullable|array',
            'leakage_test_result' => 'required|string',
            'seal_strength_result' => 'required|string',
            'barcode_readability' => 'required|string',
            'photos_json' => 'nullable|array',
        ]);

        $user = $request->user() ?? User::first();
        $trialPm = $this->workflowService->createTrialPm($validated, $user);

        return redirect()->route('rnd.trial-pms.show', $trialPm->id)
            ->with('success', "Trial PM {$trialPm->code} berhasil dibuat. Matriks verifikasi 4 Departemen kini terbuka.");
    }

    public function show(int $id): Response
    {
        $trialPm = TrialPm::with(['product', 'vendor', 'omApprover', 'approvals.user'])
            ->findOrFail($id);

        return Inertia::render('Modules/Rnd/TrialPm/Show', [
            'module' => [
                'name' => 'Trial Packaging Material (PM)',
                'slug' => 'rnd',
                'color_theme' => 'cyan',
                'menus' => $this->moduleManager->getModuleMenus('rnd'),
            ],
            'trialPm' => $trialPm,
        ]);
    }

    public function signDepartment(Request $request, int $id): RedirectResponse
    {
        $request->validate([
            'department_name' => 'required|string|in:R&D,QC,Production,Engineering',
            'status' => 'required|string|in:approved,rejected',
            'notes' => 'nullable|string',
        ]);

        $trialPm = TrialPm::findOrFail($id);
        $user = $request->user() ?? User::first();

        $this->workflowService->signTrialPmDepartment(
            $trialPm,
            $request->input('department_name'),
            $user,
            $request->input('status'),
            $request->input('notes')
        );

        return back()->with('success', "Paraf Departemen {$request->input('department_name')} berhasil dibubuhkan!");
    }

    public function approveFinal(Request $request, int $id): RedirectResponse
    {
        $trialPm = TrialPm::findOrFail($id);
        $user = $request->user() ?? User::first();

        $this->workflowService->approveTrialPmFinal($trialPm, $user);

        return back()->with('success', "Trial PM {$trialPm->code} berhasil disahkan oleh Operational Manager!");
    }
}
