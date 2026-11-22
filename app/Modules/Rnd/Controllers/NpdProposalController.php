<?php

namespace App\Modules\Rnd\Controllers;

use App\Core\Models\Product;
use App\Core\Services\ModuleManagerService;
use App\Http\Controllers\Controller;
use App\Models\User;
use App\Modules\Rnd\Models\NpdProposal;
use App\Modules\Rnd\Models\Prf;
use App\Modules\Rnd\Services\RndWorkflowService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class NpdProposalController extends Controller
{
    public function __construct(
        protected RndWorkflowService $workflowService,
        protected ModuleManagerService $moduleManager
    ) {}

    public function index(Request $request): Response
    {
        $query = NpdProposal::with(['product', 'prf', 'pic'])
            ->latest();

        if ($search = $request->query('q')) {
            $query->where('code', 'like', "%{$search}%")
                ->orWhereHas('product', fn ($q) => $q->where('name', 'like', "%{$search}%"));
        }

        $proposals = $query->paginate(12)->withQueryString();

        return Inertia::render('Modules/Rnd/NpdProposal/Index', [
            'module' => [
                'name' => 'NPD Proposals',
                'slug' => 'rnd',
                'color_theme' => 'cyan',
                'menus' => $this->moduleManager->getModuleMenus('rnd'),
            ],
            'proposals' => $proposals,
            'stats' => [
                'total' => NpdProposal::count(),
                'in_progress' => NpdProposal::where('status', 'in_progress')->count(),
                'completed' => NpdProposal::where('status', 'completed')->count(),
            ],
        ]);
    }

    public function create(): Response
    {
        $prfs = Prf::with('product')->where('status', 'submitted')->get();
        $products = Product::where('type', 'fg')->where('is_active', true)->get(['id', 'sku', 'name']);

        return Inertia::render('Modules/Rnd/NpdProposal/Form', [
            'module' => [
                'name' => 'NPD Proposals',
                'slug' => 'rnd',
                'color_theme' => 'cyan',
                'menus' => $this->moduleManager->getModuleMenus('rnd'),
            ],
            'prfs' => $prfs,
            'products' => $products,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'prf_id' => 'nullable|exists:rnd_prfs,id',
            'product_id' => 'required|exists:products,id',
            'target_cogs' => 'required|numeric|min:0',
            'target_selling_price' => 'required|numeric|min:0',
            'development_start' => 'nullable|date',
            'development_end' => 'nullable|date',
            'notes' => 'nullable|string',
        ]);

        $user = $request->user() ?? User::first();
        $proposal = $this->workflowService->createNpdProposal($validated, $user);

        return redirect()->route('rnd.npd-proposals.index')
            ->with('success', "Proposal Proyek NPD {$proposal->code} berhasil diinisiasi.");
    }
}
