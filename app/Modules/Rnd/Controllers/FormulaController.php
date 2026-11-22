<?php

namespace App\Modules\Rnd\Controllers;

use App\Core\Integrations\Contracts\RndIntegrationContract;
use App\Core\Models\Product;
use App\Core\Models\Vendor;
use App\Core\Services\ModuleManagerService;
use App\Http\Controllers\Controller;
use App\Models\User;
use App\Modules\Rnd\Models\Formula;
use App\Modules\Rnd\Services\RndWorkflowService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response as HttpResponse;
use Inertia\Inertia;
use Inertia\Response;

class FormulaController extends Controller
{
    public function __construct(
        protected RndWorkflowService $workflowService,
        protected ModuleManagerService $moduleManager
    ) {}

    public function index(Request $request): Response
    {
        $query = Formula::with(['product', 'creator', 'items.material'])
            ->latest();

        if ($search = $request->query('q')) {
            $query->where(function ($q) use ($search) {
                $q->where('code', 'like', "%{$search}%")
                    ->orWhereHas('product', fn ($pq) => $pq->where('name', 'like', "%{$search}%"));
            });
        }

        if ($status = $request->query('status')) {
            $query->where('status', $status);
        }

        $formulas = $query->paginate(12)->withQueryString();

        return Inertia::render('Modules/Rnd/Formula/Index', [
            'module' => [
                'name' => 'Formulasi RM',
                'slug' => 'rnd',
                'color_theme' => 'cyan',
                'menus' => $this->moduleManager->getModuleMenus('rnd'),
            ],
            'formulas' => $formulas,
            'stats' => [
                'total' => Formula::count(),
                'draft' => Formula::where('status', 'draft')->count(),
                'pending' => Formula::whereIn('status', ['pending_stage_1', 'pending_stage_2'])->count(),
                'approved' => Formula::where('status', 'approved')->count(),
            ],
            'filters' => $request->only(['q', 'status']),
        ]);
    }

    public function create(): Response
    {
        $finishedGoods = Product::where('type', 'fg')->where('is_active', true)->get(['id', 'sku', 'name', 'category']);
        $rawMaterials = Product::where('type', 'rm')->where('is_active', true)->get(['id', 'sku', 'name', 'standard_cost']);
        $vendors = Vendor::where('is_active', true)->get(['id', 'code', 'name']);

        return Inertia::render('Modules/Rnd/Formula/Form', [
            'module' => [
                'name' => 'Formulasi RM',
                'slug' => 'rnd',
                'color_theme' => 'cyan',
                'menus' => $this->moduleManager->getModuleMenus('rnd'),
            ],
            'finishedGoods' => $finishedGoods,
            'rawMaterials' => $rawMaterials,
            'vendors' => $vendors,
            'formula' => null,
            'mode' => 'create',
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'product_id' => 'required|exists:products,id',
            'formula_type' => 'required|string|in:new_product,existing,substitution',
            'stage' => 'required|string|in:product_form,lab_trial,sensory_test,plant_trial,market_test',
            'target_serving_size_g' => 'required|numeric|min:0.01',
            'serving_unit' => 'required|string',
            'servings_per_pack' => 'required|integer|min:1',
            'notes' => 'nullable|string',
            'status' => 'nullable|string|in:draft,pending_stage_1',
            'items' => 'required|array|min:1',
            'items.*.material_product_id' => 'required|exists:products,id',
            'items.*.vendor_id' => 'nullable|exists:vendors,id',
            'items.*.percentage' => 'required|numeric|min:0.01|max:100',
            'items.*.price_per_kg' => 'required|numeric|min:0',
            'items.*.notes' => 'nullable|string',
        ]);

        $user = $request->user() ?? User::first();

        $formula = $this->workflowService->createFormula(
            $validated,
            $validated['items'],
            $user
        );

        return redirect()->route('rnd.formulas.index')
            ->with('success', "Formula {$formula->code} berhasil dibuat dengan total komposisi 100.00% presisi.");
    }

    public function show(int $id): Response
    {
        $formula = Formula::with(['product', 'creator', 'omApprover', 'gmApprover', 'items.material', 'items.vendor', 'parentFormula', 'trialRms'])
            ->findOrFail($id);

        return Inertia::render('Modules/Rnd/Formula/Show', [
            'module' => [
                'name' => 'Formulasi RM',
                'slug' => 'rnd',
                'color_theme' => 'cyan',
                'menus' => $this->moduleManager->getModuleMenus('rnd'),
            ],
            'formula' => $formula,
        ]);
    }

    public function submit(Request $request, int $id): RedirectResponse
    {
        $formula = Formula::findOrFail($id);
        $user = $request->user() ?? User::first();

        $this->workflowService->submitFormula($formula, $user);

        return back()->with('success', "Formula {$formula->code} berhasil diajukan ke Operational Manager (Tahap 1).");
    }

    public function reformula(Request $request, int $id): RedirectResponse
    {
        $parent = Formula::with('items')->findOrFail($id);
        $user = $request->user() ?? User::first();

        $newFormula = $this->workflowService->reformulaClone($parent, $user);

        return redirect()->route('rnd.formulas.show', $newFormula->id)
            ->with('success', "Auto-Clone Reformulasi Berhasil: Formula versi baru {$newFormula->code} telah diinisiasi dari {$parent->code}.");
    }

    public function promoteToBom(Request $request, int $id, RndIntegrationContract $rndIntegration): RedirectResponse
    {
        $user = $request->user() ?? User::first();
        $bom = $rndIntegration->promoteFormulaToMasterBom($id, $user);

        return back()->with('success', "Formula berhasil dipromosikan ke Master BOM ({$bom->bom_number}) dan kini otomatis aktif di SCM (MRP) & Produksi.");
    }

    public function exportPdf(int $id): HttpResponse
    {
        $formula = Formula::with(['product', 'creator', 'omApprover', 'gmApprover', 'items.material', 'items.vendor'])
            ->findOrFail($id);

        $pdf = Pdf::loadView('pdf.rnd_formula', compact('formula'))
            ->setPaper('a4', 'portrait');

        return $pdf->stream("Formulasi-{$formula->code}.pdf");
    }
}
