<?php

namespace App\Modules\SupplyChain\Controllers;

use App\Core\Models\Product;
use App\Core\Models\Vendor;
use App\Http\Controllers\Controller;
use App\Modules\SupplyChain\Models\ScmGoodsReceipt;
use App\Modules\SupplyChain\Models\ScmMaterialReturn;
use App\Modules\SupplyChain\Models\ScmMaterialReturnItem;
use App\Modules\SupplyChain\Services\ScmNumberingService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response as HttpResponse;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class MaterialReturnController extends Controller
{
    public function __construct(
        protected ScmNumberingService $numberingService
    ) {}

    /**
     * Display a listing of Vendor Material Returns.
     */
    public function index(Request $request): Response
    {
        $query = ScmMaterialReturn::with(['vendor', 'goodsReceipt', 'creator'])
            ->withCount('items');

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('return_number', 'like', "%{$search}%")
                    ->orWhereHas('vendor', function ($v) use ($search) {
                        $v->where('name', 'like', "%{$search}%");
                    });
            });
        }

        $returns = $query->latest('return_date')->paginate(10)->withQueryString();

        return Inertia::render('Modules/SupplyChain/MaterialReturn/Index', [
            'returns' => $returns,
            'filters' => $request->only(['search']),
        ]);
    }

    /**
     * Show the form for creating a new Vendor Material Return.
     */
    public function create(): Response
    {
        $vendors = Vendor::where('status', 'active')->get(['id', 'code', 'name']);
        $receipts = ScmGoodsReceipt::latest()->take(20)->get(['id', 'grn_number', 'po_number', 'vendor_id']);
        $products = Product::where('status', 'active')->with('uom')->get(['id', 'code', 'name', 'uom_id', 'standard_cost']);

        return Inertia::render('Modules/SupplyChain/MaterialReturn/Form', [
            'vendors' => $vendors,
            'receipts' => $receipts,
            'products' => $products,
        ]);
    }

    /**
     * Store a newly created Material Return.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'goods_receipt_id' => 'nullable|exists:scm_goods_receipts,id',
            'vendor_id' => 'required|exists:vendors,id',
            'return_date' => 'required|date',
            'reason' => 'required|string|max:500',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.batch_number' => 'nullable|string|max:100',
            'items.*.quantity' => 'required|numeric|min:0.01',
            'items.*.uom' => 'nullable|string|max:50',
            'items.*.unit_price' => 'nullable|numeric|min:0',
            'items.*.reason' => 'nullable|string|max:255',
        ]);

        $return = DB::transaction(function () use ($validated) {
            $user = auth()->id() ?? 1;
            $returnRecord = ScmMaterialReturn::create([
                'return_number' => $this->numberingService->generateReturn(),
                'goods_receipt_id' => $validated['goods_receipt_id'] ?? null,
                'vendor_id' => $validated['vendor_id'],
                'return_date' => $validated['return_date'],
                'reason' => $validated['reason'],
                'total_amount' => 0,
                'status' => 'approved',
                'created_by' => $user,
                'approved_by' => $user,
                'approved_at' => now(),
            ]);

            $totalAmount = 0;
            foreach ($validated['items'] as $itemData) {
                $product = Product::find($itemData['product_id']);
                $qty = (float) $itemData['quantity'];
                $unitPrice = (float) ($itemData['unit_price'] ?? ($product?->standard_cost ?? 0));
                $subtotal = round($qty * $unitPrice, 2);
                $totalAmount += $subtotal;

                ScmMaterialReturnItem::create([
                    'material_return_id' => $returnRecord->id,
                    'product_id' => $itemData['product_id'],
                    'batch_number' => $itemData['batch_number'] ?? null,
                    'quantity' => $qty,
                    'uom' => $itemData['uom'] ?? ($product?->uom?->code ?? 'PCS'),
                    'unit_price' => $unitPrice,
                    'subtotal' => $subtotal,
                    'reason' => $itemData['reason'] ?? $validated['reason'],
                ]);
            }

            $returnRecord->update(['total_amount' => $totalAmount]);

            return $returnRecord;
        });

        return redirect()->route('supply-chain.material-return.index')
            ->with('success', "Memo Retur Barang {$return->return_number} berhasil diterbitkan.");
    }

    /**
     * Export Vendor Material Return Delivery Note to PDF.
     */
    public function exportPdf(int $id): HttpResponse
    {
        $return = ScmMaterialReturn::with([
            'vendor',
            'goodsReceipt',
            'creator',
            'items.product.uom',
        ])->findOrFail($id);

        $pdf = Pdf::loadView('pdf.scm_material_return', [
            'return' => $return,
        ])->setPaper('a4', 'portrait');

        return $pdf->download("RET-{$return->return_number}.pdf");
    }
}
