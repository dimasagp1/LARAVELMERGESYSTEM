<?php

namespace App\Modules\Rnd\Controllers;

use App\Core\Models\Product;
use App\Core\Services\ModuleManagerService;
use App\Http\Controllers\Controller;
use App\Modules\Rnd\Models\SampleEvaluation;
use App\Modules\Rnd\Services\RndWorkflowService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class SampleEvaluationController extends Controller
{
    public function __construct(
        protected RndWorkflowService $workflowService,
        protected ModuleManagerService $moduleManager
    ) {}

    public function index(Request $request): Response
    {
        $query = SampleEvaluation::with(['product', 'npdProposal'])
            ->latest();

        $evaluations = $query->paginate(12)->withQueryString();

        return Inertia::render('Modules/Rnd/SampleEvaluation/Index', [
            'module' => [
                'name' => 'Uji Sensori & Panelis',
                'slug' => 'rnd',
                'color_theme' => 'cyan',
                'menus' => $this->moduleManager->getModuleMenus('rnd'),
            ],
            'evaluations' => $evaluations,
            'stats' => [
                'total' => SampleEvaluation::count(),
                'avg_score' => round((float) SampleEvaluation::avg('overall_score'), 2),
            ],
        ]);
    }

    public function create(): Response
    {
        $products = Product::where('type', 'fg')->where('is_active', true)->get(['id', 'sku', 'name']);

        return Inertia::render('Modules/Rnd/SampleEvaluation/Form', [
            'module' => [
                'name' => 'Uji Sensori & Panelis',
                'slug' => 'rnd',
                'color_theme' => 'cyan',
                'menus' => $this->moduleManager->getModuleMenus('rnd'),
            ],
            'products' => $products,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'product_id' => 'required|exists:products,id',
            'sample_code' => 'required|string',
            'session_date' => 'required|date',
            'batch_code' => 'nullable|string',
            'panelist_count' => 'required|integer|min:1',
            'average_taste' => 'required|numeric|min:1|max:5',
            'average_color' => 'required|numeric|min:1|max:5',
            'average_aroma' => 'required|numeric|min:1|max:5',
            'average_texture' => 'required|numeric|min:1|max:5',
            'average_aftertaste' => 'required|numeric|min:1|max:5',
            'conclusion' => 'nullable|string',
        ]);

        $evaluation = $this->workflowService->createSampleEvaluation($validated);

        return redirect()->route('rnd.sample-evaluations.index')
            ->with('success', "Evaluasi Sensori {$evaluation->sample_code} berhasil disimpan (Overall Score: {$evaluation->overall_score}/5.0).");
    }
}
