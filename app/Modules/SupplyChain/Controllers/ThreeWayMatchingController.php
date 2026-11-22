<?php

namespace App\Modules\SupplyChain\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\SupplyChain\Models\ScmGoodsReceipt;
use App\Modules\SupplyChain\Models\ScmThreeWayMatching;
use App\Modules\SupplyChain\Models\ScmVendorBill;
use App\Modules\SupplyChain\Services\ThreeWayMatchingService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ThreeWayMatchingController extends Controller
{
    public function __construct(
        protected ThreeWayMatchingService $matchingService
    ) {}

    /**
     * Display 3-Way Matching audits and Vendor Bills.
     */
    public function index(Request $request): Response
    {
        $matchings = ScmThreeWayMatching::with(['goodsReceipt.vendor', 'vendor', 'auditor'])
            ->latest()
            ->paginate(10);

        $vendorBills = ScmVendorBill::with(['vendor', 'goodsReceipt', 'threeWayMatching', 'creator'])
            ->latest()
            ->take(10)
            ->get();

        // Receipts ready for 3-Way Matching (Completed QC inspection)
        $completedReceipts = ScmGoodsReceipt::with(['items', 'vendor'])
            ->whereIn('status', ['completed', 'inspected'])
            ->whereDoesntHave('threeWayMatching')
            ->latest()
            ->take(15)
            ->get();

        $stats = [
            'total_matched' => ScmThreeWayMatching::where('status', 'matched')->count(),
            'total_discrepant' => ScmThreeWayMatching::where('status', 'discrepant')->count(),
            'total_bills' => ScmVendorBill::count(),
            'total_bill_amount' => (float) ScmVendorBill::sum('total_amount'),
        ];

        return Inertia::render('Modules/SupplyChain/Matching/Index', [
            'matchings' => $matchings,
            'vendorBills' => $vendorBills,
            'completedReceipts' => $completedReceipts,
            'stats' => $stats,
        ]);
    }

    /**
     * Perform 3-Way Matching comparison.
     */
    public function match(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'goods_receipt_id' => 'required|exists:scm_goods_receipts,id',
            'vendor_invoice_number' => 'required|string|max:100',
            'invoice_qty' => 'required|numeric|min:0.01',
            'invoice_amount' => 'required|numeric|min:0.01',
            'po_total_qty' => 'nullable|numeric|min:0',
            'po_unit_price' => 'nullable|numeric|min:0',
            'notes' => 'nullable|string|max:500',
            'auto_create_bill' => 'nullable|boolean',
        ]);

        $matching = $this->matchingService->executeMatching($validated, auth()->id());

        $isMatchedOrApproved = in_array($matching->status, ['matched', 'approved']);
        $message = $isMatchedOrApproved
            ? "Audit 3-Way Matching {$matching->matching_number} KLOP (Matched). Tagihan Vendor berhasil dibuat."
            : "Audit 3-Way Matching {$matching->matching_number} terdeteksi SELISIH (Discrepancy: Rp ".number_format($matching->discrepancy_amount, 0, ',', '.').').';

        return redirect()->route('supply-chain.matching.index')
            ->with($isMatchedOrApproved ? 'success' : 'warning', $message);
    }

    /**
     * Explicitly generate vendor bill for an audited matching.
     */
    public function createBill(Request $request, int $id): RedirectResponse
    {
        $bill = $this->matchingService->createVendorBill($id, auth()->id());

        return redirect()->route('supply-chain.matching.index')
            ->with('success', "Tagihan Vendor (Bill) {$bill->bill_number} berhasil diterbitkan.");
    }
}
