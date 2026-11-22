<?php

namespace App\Modules\SupplyChain\Controllers;

use App\Core\Models\Plant;
use App\Core\Models\Product;
use App\Core\Models\Vendor;
use App\Http\Controllers\Controller;
use App\Modules\SupplyChain\Models\ScmGoodsReceipt;
use App\Modules\SupplyChain\Services\InboundGoodsReceiptService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response as HttpResponse;
use Inertia\Inertia;
use Inertia\Response;

class GoodsReceiptController extends Controller
{
    public function __construct(
        protected InboundGoodsReceiptService $receiptService
    ) {}

    /**
     * Display a listing of Inbound Goods Receipts.
     */
    public function index(Request $request): Response
    {
        $query = ScmGoodsReceipt::with(['vendor', 'plant', 'receiver'])
            ->withCount('items');

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('grn_number', 'like', "%{$search}%")
                    ->orWhere('po_number', 'like', "%{$search}%")
                    ->orWhere('delivery_order_number', 'like', "%{$search}%")
                    ->orWhereHas('vendor', function ($v) use ($search) {
                        $v->where('name', 'like', "%{$search}%");
                    });
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('plant_id')) {
            $query->where('plant_id', $request->plant_id);
        }

        $receipts = $query->latest('receipt_date')->paginate(10)->withQueryString();
        $plants = Plant::where('is_active', true)->get(['id', 'code', 'name']);

        return Inertia::render('Modules/SupplyChain/GoodsReceipt/Index', [
            'receipts' => $receipts,
            'filters' => $request->only(['search', 'status', 'plant_id']),
            'plants' => $plants,
        ]);
    }

    /**
     * Show the form for creating a new Goods Receipt.
     */
    public function create(): Response
    {
        $vendors = Vendor::where('status', 'active')->get(['id', 'code', 'name']);
        $plants = Plant::where('is_active', true)->get(['id', 'code', 'name']);
        $products = Product::where('status', 'active')
            ->whereIn('type', ['raw_material', 'packaging', 'consumable'])
            ->with('uom')
            ->get(['id', 'code', 'name', 'type', 'uom_id', 'standard_cost']);

        return Inertia::render('Modules/SupplyChain/GoodsReceipt/Form', [
            'vendors' => $vendors,
            'plants' => $plants,
            'products' => $products,
        ]);
    }

    /**
     * Store a newly created Goods Receipt and place items into Quarantine QC.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'po_number' => 'nullable|string|max:100',
            'vendor_id' => 'required|exists:vendors,id',
            'plant_id' => 'required|exists:plants,id',
            'delivery_order_number' => 'required|string|max:100',
            'receipt_date' => 'required|date',
            'notes' => 'nullable|string|max:500',
            'custom_attributes' => 'nullable|array',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.item_name' => 'nullable|string|max:255',
            'items.*.received_qty' => 'required|numeric|min:0.01',
            'items.*.uom' => 'nullable|string|max:50',
            'items.*.batch_number' => 'nullable|string|max:100',
            'items.*.expiry_date' => 'nullable|date',
            'items.*.unit_price' => 'nullable|numeric|min:0',
            'items.*.notes' => 'nullable|string|max:255',
        ]);

        $receipt = $this->receiptService->createGoodsReceipt(
            data: $validated,
            items: $validated['items'],
            userId: auth()->id()
        );

        return redirect()->route('supply-chain.goods-receipt.show', $receipt->id)
            ->with('success', "Penerimaan barang {$receipt->grn_number} berhasil dicatat dan masuk Karantina QC.");
    }

    /**
     * Display the specified Goods Receipt details, items, and QC inspection status.
     */
    public function show(int $id): Response
    {
        $receipt = ScmGoodsReceipt::with([
            'vendor',
            'plant',
            'receiver',
            'items.product.uom',
            'items.qcInspection.inspector',
        ])->findOrFail($id);

        return Inertia::render('Modules/SupplyChain/GoodsReceipt/Show', [
            'receipt' => $receipt,
        ]);
    }

    /**
     * Export the official Goods Receipt Note (GRN) to PDF.
     */
    public function exportPdf(int $id): HttpResponse
    {
        $receipt = ScmGoodsReceipt::with([
            'vendor',
            'plant',
            'receiver',
            'items.product.uom',
        ])->findOrFail($id);

        $pdf = Pdf::loadView('pdf.scm_goods_receipt', [
            'receipt' => $receipt,
        ])->setPaper('a4', 'portrait');

        return $pdf->download("GRN-{$receipt->grn_number}.pdf");
    }
}
