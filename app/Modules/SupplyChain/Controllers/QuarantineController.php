<?php

namespace App\Modules\SupplyChain\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\SupplyChain\Models\ScmQcInspection;
use App\Modules\SupplyChain\Services\QcIncomingGatekeeperService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class QuarantineController extends Controller
{
    public function __construct(
        protected QcIncomingGatekeeperService $qcService
    ) {}

    /**
     * Display the QC Incoming Quarantine inspection queue.
     */
    public function index(Request $request): Response
    {
        $query = ScmQcInspection::with([
            'goodsReceiptItem.goodsReceipt.vendor',
            'goodsReceiptItem.goodsReceipt.plant',
            'product.uom',
            'inspector',
        ]);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('inspection_number', 'like', "%{$search}%")
                    ->orWhere('batch_number', 'like', "%{$search}%")
                    ->orWhereHas('product', function ($p) use ($search) {
                        $p->where('name', 'like', "%{$search}%")
                            ->orWhere('code', 'like', "%{$search}%");
                    })
                    ->orWhereHas('goodsReceiptItem.goodsReceipt', function ($g) use ($search) {
                        $g->where('grn_number', 'like', "%{$search}%")
                            ->orWhere('po_number', 'like', "%{$search}%");
                    });
            });
        }

        $inspections = $query->latest()->paginate(10)->withQueryString();

        $stats = [
            'total_pending' => ScmQcInspection::where('status', 'pending')->count(),
            'total_passed' => ScmQcInspection::where('status', 'passed')->count(),
            'total_rejected' => ScmQcInspection::where('status', 'rejected')->count(),
        ];

        return Inertia::render('Modules/SupplyChain/Quarantine/Index', [
            'inspections' => $inspections,
            'filters' => $request->only(['status', 'search']),
            'stats' => $stats,
        ]);
    }

    /**
     * Submit 4-parameter QC test result (Visual, Lab, Qty, CoA/Docs).
     */
    public function inspect(Request $request, int $id): RedirectResponse
    {
        $validated = $request->validate([
            'is_visual_passed' => 'required|boolean',
            'is_lab_passed' => 'required|boolean',
            'is_quantity_passed' => 'required|boolean',
            'is_docs_passed' => 'required|boolean',
            'sample_qty' => 'nullable|numeric|min:0',
            'passed_qty' => 'nullable|numeric|min:0',
            'rejected_qty' => 'nullable|numeric|min:0',
            'notes' => 'nullable|string|max:500',
            'coa_attachment_path' => 'nullable|string|max:255',
        ]);

        $inspection = $this->qcService->inspect(
            inspectionId: $id,
            data: $validated,
            inspectorId: auth()->id()
        );

        $statusText = $inspection->status === 'passed' ? 'LULUS (Passed)' : ($inspection->status === 'partial' ? 'LULUS SEBAGIAN' : 'DITOLAK (Reject)');

        return redirect()->back()
            ->with('success', "Inspeksi Mutu {$inspection->inspection_number} berhasil diperbarui: {$statusText}.");
    }
}
