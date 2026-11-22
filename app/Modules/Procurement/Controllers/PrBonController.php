<?php

namespace App\Modules\Procurement\Controllers;

use App\Core\Services\ModuleManagerService;
use App\Http\Controllers\Controller;
use App\Modules\Procurement\Models\PrBon;
use App\Modules\Procurement\Models\PrItem;
use App\Modules\Procurement\Services\PrBonWorkflowService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response as HttpResponse;
use Inertia\Inertia;
use Inertia\Response;

class PrBonController extends Controller
{
    public function __construct(
        protected PrBonWorkflowService $bonWorkflowService,
        protected ModuleManagerService $moduleManager
    ) {}

    public function index(): Response
    {
        $bons = PrBon::with(['creator', 'approver', 'items.purchaseRequest'])
            ->latest('id')
            ->paginate(12);

        return Inertia::render('Modules/Procurement/PrBon/Index', [
            'module' => [
                'name' => 'PR Bon Sementara',
                'slug' => 'procurement',
                'color_theme' => 'amber',
                'menus' => $this->moduleManager->getModuleMenus('procurement'),
            ],
            'bons' => $bons,
        ]);
    }

    public function staging(): Response
    {
        // Items eligible for Bon grouping: is_bon = true and in waiting_bon or revision
        $stagedItems = PrItem::with(['purchaseRequest.department', 'purchaseRequest.user'])
            ->where('is_bon', true)
            ->whereIn('status', ['waiting_bon', 'revision'])
            ->get();

        return Inertia::render('Modules/Procurement/PrBon/Staging', [
            'module' => [
                'name' => 'Staging Bon Sementara',
                'slug' => 'procurement',
                'color_theme' => 'amber',
                'menus' => $this->moduleManager->getModuleMenus('procurement'),
            ],
            'stagedItems' => $stagedItems,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'vendor_name' => 'required|string|max:255',
            'bon_date' => 'required|date',
            'item_ids' => 'required|array|min:1',
            'item_ids.*' => 'exists:pr_items,id',
            'item_prices' => 'nullable|array',
        ]);

        $bon = $this->bonWorkflowService->createBon(
            $validated,
            $validated['item_ids'],
            $request->user()
        );

        return redirect()->route('procurement.pr-bons.show', $bon->id)
            ->with('success', "Berkas Pengadaan Bon {$bon->bon_number} berhasil dibuat dan diajukan ke General Manager.");
    }

    public function show(int $id): Response
    {
        $bon = PrBon::with([
            'creator',
            'approver',
            'items.purchaseRequest.department',
            'items.purchaseRequest.user',
        ])->findOrFail($id);

        return Inertia::render('Modules/Procurement/PrBon/Show', [
            'module' => [
                'name' => 'PR Bon Sementara',
                'slug' => 'procurement',
                'color_theme' => 'amber',
                'menus' => $this->moduleManager->getModuleMenus('procurement'),
            ],
            'bon' => $bon,
        ]);
    }

    public function gmDecision(Request $request, int $id): RedirectResponse
    {
        $bon = PrBon::findOrFail($id);

        $validated = $request->validate([
            'action' => 'required|in:approve_all,partial,revision,reject_all',
            'approved_item_ids' => 'nullable|array',
            'note' => 'nullable|string',
        ]);

        $this->bonWorkflowService->gmDecision(
            $bon,
            $validated['action'],
            $request->user(),
            $validated['approved_item_ids'] ?? [],
            $validated['note'] ?? null
        );

        return back()->with('success', "Keputusan GM untuk berkas bon {$bon->bon_number} berhasil disimpan.");
    }

    public function printPdf(int $id): HttpResponse
    {
        $bon = PrBon::with(['creator', 'approver', 'items.purchaseRequest'])->findOrFail($id);

        $pdf = Pdf::loadView('pdf.procurement_bon', [
            'bon' => $bon,
        ])->setPaper('a4', 'portrait');

        return $pdf->download("BON_{$bon->bon_number}.pdf");
    }
}
