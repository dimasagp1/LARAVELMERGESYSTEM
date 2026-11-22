<?php

namespace App\Modules\Rnd\Controllers;

use App\Core\Services\ModuleManagerService;
use App\Http\Controllers\Controller;
use App\Models\User;
use App\Modules\Rnd\Models\Formula;
use App\Modules\Rnd\Models\TrialRm;
use App\Modules\Rnd\Services\RndWorkflowService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class TrialRmController extends Controller
{
    public function __construct(
        protected RndWorkflowService $workflowService,
        protected ModuleManagerService $moduleManager
    ) {}

    public function index(Request $request): Response
    {
        $query = TrialRm::with(['product', 'formula', 'creator'])
            ->latest();

        if ($search = $request->query('q')) {
            $query->where('code', 'like', "%{$search}%")
                ->orWhereHas('product', fn ($q) => $q->where('name', 'like', "%{$search}%"));
        }

        $trials = $query->paginate(12)->withQueryString();

        return Inertia::render('Modules/Rnd/TrialRm/Index', [
            'module' => [
                'name' => 'Trial Raw Material (Lab Test)',
                'slug' => 'rnd',
                'color_theme' => 'cyan',
                'menus' => $this->moduleManager->getModuleMenus('rnd'),
            ],
            'trials' => $trials,
            'stats' => [
                'total' => TrialRm::count(),
                'lulus' => TrialRm::where('decision', 'lulus')->count(),
                'reformulasi' => TrialRm::where('decision', 'reformulasi')->count(),
                'approved' => TrialRm::where('status', 'approved')->count(),
            ],
        ]);
    }

    public function create(): Response
    {
        $approvedFormulas = Formula::where('status', 'approved')
            ->with(['product', 'items.material'])
            ->get();

        return Inertia::render('Modules/Rnd/TrialRm/Form', [
            'module' => [
                'name' => 'Trial Raw Material (Lab Test)',
                'slug' => 'rnd',
                'color_theme' => 'cyan',
                'menus' => $this->moduleManager->getModuleMenus('rnd'),
            ],
            'approvedFormulas' => $approvedFormulas,
            'defaultParameters' => [
                ['parameter' => 'pH Sediaan', 'target_spec' => '6.0 - 7.2', 'actual_result' => '6.5', 'is_compliant' => true],
                ['parameter' => 'Viskositas', 'target_spec' => '150 - 300 cP', 'actual_result' => '220 cP', 'is_compliant' => true],
                ['parameter' => 'Warna Organoleptik', 'target_spec' => 'Coklat herbal jernih', 'actual_result' => 'Coklat jernih', 'is_compliant' => true],
                ['parameter' => 'Rasa Organoleptik', 'target_spec' => 'Manis herbal khas jahe', 'actual_result' => 'Sesuai', 'is_compliant' => true],
                ['parameter' => 'Aroma Organoleptik', 'target_spec' => 'Khas ekstrak temulawak', 'actual_result' => 'Khas temulawak', 'is_compliant' => true],
                ['parameter' => 'Berat Jenis', 'target_spec' => '1.02 - 1.08 g/mL', 'actual_result' => '1.05 g/mL', 'is_compliant' => true],
                ['parameter' => 'Homogenitas', 'target_spec' => 'Homogen bebas partikel asing', 'actual_result' => 'Homogen', 'is_compliant' => true],
                ['parameter' => 'Waktu Larut', 'target_spec' => '< 30 detik', 'actual_result' => '18 detik', 'is_compliant' => true],
            ],
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'formula_id' => 'required|exists:rnd_formulas,id',
            'product_id' => 'required|exists:products,id',
            'batch_qty_kg' => 'required|numeric|min:0.1',
            'process_steps' => 'nullable|string',
            'decision' => 'required|string|in:lulus,reformulasi',
            'notes' => 'nullable|string',
            'verifications' => 'required|array|min:1',
            'verifications.*.parameter' => 'required|string',
            'verifications.*.target_spec' => 'nullable|string',
            'verifications.*.actual_result' => 'nullable|string',
            'verifications.*.is_compliant' => 'boolean',
        ]);

        $user = $request->user() ?? User::first();

        $trial = $this->workflowService->createTrialRm(
            $validated,
            $validated['verifications'],
            $user
        );

        return redirect()->route('rnd.trial-rms.show', $trial->id)
            ->with('success', "Catatan Trial RM {$trial->code} berhasil disimpan dengan keputusan: ".strtoupper($trial->decision));
    }

    public function show(int $id): Response
    {
        $trial = TrialRm::with(['product', 'formula.items.material', 'creator', 'verifications'])
            ->findOrFail($id);

        return Inertia::render('Modules/Rnd/TrialRm/Show', [
            'module' => [
                'name' => 'Trial Raw Material (Lab Test)',
                'slug' => 'rnd',
                'color_theme' => 'cyan',
                'menus' => $this->moduleManager->getModuleMenus('rnd'),
            ],
            'trial' => $trial,
        ]);
    }
}
