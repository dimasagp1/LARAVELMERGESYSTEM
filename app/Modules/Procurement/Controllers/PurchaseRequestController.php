<?php

namespace App\Modules\Procurement\Controllers;

use App\Core\Models\Department;
use App\Core\Models\Product;
use App\Core\Models\Uom;
use App\Core\Services\ModuleManagerService;
use App\Http\Controllers\Controller;
use App\Modules\Procurement\Models\PurchaseRequest;
use App\Modules\Procurement\Services\PrWorkflowService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response as HttpResponse;
use Inertia\Inertia;
use Inertia\Response;

class PurchaseRequestController extends Controller
{
    public function __construct(
        protected PrWorkflowService $workflowService,
        protected ModuleManagerService $moduleManager
    ) {}

    public function index(Request $request): Response
    {
        $query = PurchaseRequest::with(['user', 'department', 'items'])
            ->latest('id');

        // RBAC filtering: Regular user can only see their own PRs
        $user = $request->user();
        if ($user && ! $user->hasAnyRole(['superadmin', 'general_manager', 'procurement', 'procurement_holding', 'operational_manager', 'manager_fat'])) {
            $query->where('user_id', $user->id);
        }

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('pr_number', 'like', "%{$search}%")
                    ->orWhere('purpose', 'like', "%{$search}%")
                    ->orWhereHas('items', fn ($iq) => $iq->where('item_name', 'like', "%{$search}%"));
            });
        }

        if ($request->filled('pr_type')) {
            $query->where('pr_type', $request->input('pr_type'));
        }

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        if ($request->filled('department_id')) {
            $query->where('department_id', $request->input('department_id'));
        }

        $purchaseRequests = $query->paginate(12)->withQueryString();

        return Inertia::render('Modules/Procurement/PurchaseRequest/Index', [
            'module' => [
                'name' => 'Purchase Request',
                'slug' => 'procurement',
                'color_theme' => 'violet',
                'menus' => $this->moduleManager->getModuleMenus('procurement'),
            ],
            'purchaseRequests' => $purchaseRequests,
            'departments' => Department::select('id', 'name', 'code')->get(),
            'filters' => $request->only(['search', 'pr_type', 'status', 'department_id']),
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Modules/Procurement/PurchaseRequest/Form', [
            'module' => [
                'name' => 'Purchase Request',
                'slug' => 'procurement',
                'color_theme' => 'violet',
                'menus' => $this->moduleManager->getModuleMenus('procurement'),
            ],
            'departments' => Department::all(),
            'products' => Product::select('id', 'name', 'code', 'uom', 'type')->get(),
            'uoms' => Uom::select('id', 'name', 'symbol')->get(),
            'purposes' => [
                'Bahan Baku Rutin Produksi (Raw Material)',
                'Bahan Kemas Primer & Sekunder',
                'Pemeliharaan & Suku Cadang Mesin Pabrik',
                'Peralatan Laboratorium & Reagen QC/R&D',
                'Perlengkapan Umum, Sanitasi & APD',
                'Alat Tulis Kantor & Keperluan Administrasi',
                'Pengadaan Aset & Renovasi Fasilitas',
            ],
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'department_id' => 'required|exists:departments,id',
            'request_date' => 'nullable|date',
            'purpose' => 'required|string|max:255',
            'pr_type' => 'required|in:operational,non_operational',
            'bypass_budget_check' => 'boolean',
            'bypass_budget_reason' => 'nullable|string',
            'notes' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.item_name' => 'required|string|max:255',
            'items.*.product_id' => 'nullable|exists:products,id',
            'items.*.item_code' => 'nullable|string',
            'items.*.quantity' => 'required|numeric|min:0.01',
            'items.*.uom' => 'required|string|max:20',
            'items.*.estimated_price' => 'nullable|numeric|min:0',
            'items.*.is_bon' => 'nullable|boolean',
            'items.*.need_holding_quotation' => 'nullable|boolean',
            'items.*.need_holding_delivery' => 'nullable|boolean',
            'as_draft' => 'nullable|boolean',
        ]);

        $asDraft = (bool) ($request->input('as_draft', false));
        $user = $request->user() ?? User::first();
        $pr = $this->workflowService->createPr($validated, $validated['items'], $user, $asDraft);

        return redirect()->route('procurement.purchase-requests.show', $pr->id)
            ->with('success', "Purchase Request {$pr->pr_number} berhasil dibuat.");
    }

    public function show(int $id): Response
    {
        $pr = PurchaseRequest::with([
            'user',
            'department',
            'parentPr',
            'childPrs',
            'items.deliveryPlans',
            'items.deliveries',
            'items.approvals.user',
            'items.vendor',
            'bons',
            'approvals.user',
        ])->findOrFail($id);

        return Inertia::render('Modules/Procurement/PurchaseRequest/Show', [
            'module' => [
                'name' => 'Purchase Request',
                'slug' => 'procurement',
                'color_theme' => 'violet',
                'menus' => $this->moduleManager->getModuleMenus('procurement'),
            ],
            'pr' => $pr,
        ]);
    }

    public function bulkRevise(Request $request, int $id): RedirectResponse
    {
        $pr = PurchaseRequest::findOrFail($id);
        $validated = $request->validate([
            'rejected_item_ids' => 'required|array|min:1',
            'rejected_item_ids.*' => 'exists:pr_items,id',
        ]);

        $newPr = $this->workflowService->bulkRevise($pr, $validated['rejected_item_ids'], $request->user());

        return redirect()->route('procurement.purchase-requests.show', $newPr->id)
            ->with('success', "Draft revisi baru {$newPr->pr_number} berhasil dibuat dari item yang ditolak.");
    }

    public function exportPdf(int $id): HttpResponse
    {
        $pr = PurchaseRequest::with(['user', 'department', 'items'])->findOrFail($id);

        $pdf = Pdf::loadView('pdf.procurement_pr', [
            'pr' => $pr,
        ])->setPaper('a4', 'portrait');

        return $pdf->download("PR_{$pr->pr_number}.pdf");
    }
}
