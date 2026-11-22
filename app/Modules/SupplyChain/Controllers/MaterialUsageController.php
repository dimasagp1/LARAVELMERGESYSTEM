<?php

namespace App\Modules\SupplyChain\Controllers;

use App\Core\Models\Department;
use App\Core\Models\Plant;
use App\Core\Models\Product;
use App\Http\Controllers\Controller;
use App\Modules\SupplyChain\Models\ScmInventoryStock;
use App\Modules\SupplyChain\Models\ScmMaterialUsage;
use App\Modules\SupplyChain\Models\ScmMaterialUsageItem;
use App\Modules\SupplyChain\Services\InventoryStockService;
use App\Modules\SupplyChain\Services\ScmNumberingService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response as HttpResponse;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class MaterialUsageController extends Controller
{
    public function __construct(
        protected InventoryStockService $stockService,
        protected ScmNumberingService $numberingService
    ) {}

    /**
     * Display a listing of Bukti Serah Terima Bahan (BSTB).
     */
    public function index(Request $request): Response
    {
        $query = ScmMaterialUsage::with(['plant', 'department', 'issuer', 'recipient'])
            ->withCount('items');

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('usage_number', 'like', "%{$search}%")
                    ->orWhere('work_order_number', 'like', "%{$search}%");
            });
        }

        $usages = $query->latest('usage_date')->paginate(10)->withQueryString();

        return Inertia::render('Modules/SupplyChain/MaterialUsage/Index', [
            'usages' => $usages,
            'filters' => $request->only(['search']),
        ]);
    }

    /**
     * Show the form for creating a new BSTB material dispatch.
     */
    public function create(): Response
    {
        $plants = Plant::where('is_active', true)->get(['id', 'code', 'name']);
        $departments = Department::where('status', 'active')->get(['id', 'code', 'name']);

        $availableStocks = ScmInventoryStock::with(['product.uom', 'location', 'plant'])
            ->where('status', 'available')
            ->where('quantity_available', '>', 0)
            ->get();

        return Inertia::render('Modules/SupplyChain/MaterialUsage/Form', [
            'plants' => $plants,
            'departments' => $departments,
            'availableStocks' => $availableStocks,
        ]);
    }

    /**
     * Store a newly created BSTB and deduct stock atomically.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'work_order_number' => 'required|string|max:100',
            'plant_id' => 'required|exists:plants,id',
            'department_id' => 'required|exists:departments,id',
            'usage_date' => 'required|date',
            'notes' => 'nullable|string|max:500',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.inventory_stock_id' => 'nullable|exists:scm_inventory_stocks,id',
            'items.*.batch_number' => 'nullable|string|max:100',
            'items.*.quantity' => 'required|numeric|min:0.01',
            'items.*.uom' => 'nullable|string|max:50',
            'items.*.unit_cost' => 'nullable|numeric|min:0',
            'items.*.notes' => 'nullable|string|max:255',
        ]);

        $usage = DB::transaction(function () use ($validated) {
            $user = auth()->id() ?? 1;
            $usageNumber = $this->numberingService->generateUsage();

            $usageRecord = ScmMaterialUsage::create([
                'usage_number' => $usageNumber,
                'work_order_number' => $validated['work_order_number'],
                'plant_id' => $validated['plant_id'],
                'department_id' => $validated['department_id'],
                'usage_date' => $validated['usage_date'],
                'issued_by' => $user,
                'received_by' => $user,
                'status' => 'issued',
                'notes' => $validated['notes'] ?? null,
            ]);

            foreach ($validated['items'] as $itemData) {
                $product = Product::find($itemData['product_id']);
                $quantity = (float) $itemData['quantity'];
                $batchNumber = $itemData['batch_number'] ?? null;
                $unitCost = (float) ($itemData['unit_cost'] ?? ($product?->standard_cost ?? 0));
                $uom = $itemData['uom'] ?? ($product?->uom?->code ?? 'PCS');

                ScmMaterialUsageItem::create([
                    'material_usage_id' => $usageRecord->id,
                    'product_id' => $itemData['product_id'],
                    'inventory_stock_id' => $itemData['inventory_stock_id'] ?? null,
                    'batch_number' => $batchNumber,
                    'quantity' => $quantity,
                    'uom' => $uom,
                    'unit_cost' => $unitCost,
                    'notes' => $itemData['notes'] ?? null,
                ]);

                // Deduct physical inventory stock
                if (! empty($itemData['inventory_stock_id'])) {
                    $stock = ScmInventoryStock::find($itemData['inventory_stock_id']);
                    if ($stock) {
                        $this->stockService->deductStock(
                            productId: $stock->product_id,
                            plantId: $stock->plant_id,
                            locationId: $stock->location_id,
                            quantity: $quantity,
                            batchNumber: $batchNumber,
                            referenceType: 'material_usage',
                            referenceId: $usageRecord->id,
                            notes: "Penyerahan bahan BSTB {$usageRecord->usage_number} untuk SPK {$usageRecord->work_order_number}",
                            userId: $user
                        );
                    }
                }
            }

            return $usageRecord;
        });

        return redirect()->route('supply-chain.material-usage.show', $usage->id)
            ->with('success', "Bukti Serah Terima Bahan (BSTB) {$usage->usage_number} berhasil diterbitkan.");
    }

    /**
     * Display the specified BSTB details.
     */
    public function show(int $id): Response
    {
        $usage = ScmMaterialUsage::with([
            'plant',
            'department',
            'issuer',
            'recipient',
            'items.product.uom',
        ])->findOrFail($id);

        return Inertia::render('Modules/SupplyChain/MaterialUsage/Show', [
            'usage' => $usage,
        ]);
    }

    /**
     * Export BSTB document to PDF.
     */
    public function exportPdf(int $id): HttpResponse
    {
        $usage = ScmMaterialUsage::with([
            'plant',
            'department',
            'issuer',
            'recipient',
            'items.product.uom',
        ])->findOrFail($id);

        $pdf = Pdf::loadView('pdf.scm_bstb', [
            'usage' => $usage,
        ])->setPaper('a4', 'portrait');

        return $pdf->download("BSTB-{$usage->usage_number}.pdf");
    }
}
