<?php

namespace App\Modules\Rnd\Controllers;

use App\Core\Models\Product;
use App\Core\Services\ModuleManagerService;
use App\Http\Controllers\Controller;
use App\Models\User;
use App\Modules\Rnd\Models\Prf;
use App\Modules\Rnd\Services\RndWorkflowService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class PrfController extends Controller
{
    public function __construct(
        protected RndWorkflowService $workflowService,
        protected ModuleManagerService $moduleManager
    ) {}

    public function index(Request $request): Response
    {
        $query = Prf::with(['product', 'creator'])
            ->latest();

        if ($search = $request->query('q')) {
            $query->where('code', 'like', "%{$search}%")
                ->orWhereHas('product', fn ($q) => $q->where('name', 'like', "%{$search}%"));
        }

        $prfs = $query->paginate(12)->withQueryString();

        return Inertia::render('Modules/Rnd/Prf/Index', [
            'module' => [
                'name' => 'Product Request Form (PRF)',
                'slug' => 'rnd',
                'color_theme' => 'cyan',
                'menus' => $this->moduleManager->getModuleMenus('rnd'),
            ],
            'prfs' => $prfs,
            'stats' => [
                'total' => Prf::count(),
                'submitted' => Prf::where('status', 'submitted')->count(),
                'approved' => Prf::where('status', 'approved')->count(),
            ],
        ]);
    }

    public function create(): Response
    {
        $products = Product::where('type', 'fg')->where('is_active', true)->get(['id', 'sku', 'name', 'category']);

        return Inertia::render('Modules/Rnd/Prf/Form', [
            'module' => [
                'name' => 'Product Request Form (PRF)',
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
            'target_market' => 'nullable|string|max:150',
            'product_category' => 'nullable|string|max:100',
            'concept' => 'required|string',
            'target_launch_date' => 'nullable|date',
            'attachments_json' => 'nullable|array',
        ]);

        $user = $request->user() ?? User::first();
        $prf = $this->workflowService->createPrf($validated, $user);

        return redirect()->route('rnd.prfs.index')
            ->with('success', "Dokumen {$prf->code} berhasil disubmit ke pipeline R&D.");
    }
}
