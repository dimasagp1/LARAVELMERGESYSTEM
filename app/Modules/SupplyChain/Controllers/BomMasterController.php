<?php

namespace App\Modules\SupplyChain\Controllers;

use App\Core\Models\Bom;
use App\Core\Models\BomItem;
use App\Core\Models\Product;
use App\Core\Models\Uom;
use App\Core\Services\ModuleManagerService;
use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

class BomMasterController extends Controller
{
    public function __construct(
        protected ModuleManagerService $moduleManager
    ) {}

    /**
     * Display a listing of Bill of Materials (BOM).
     */
    public function index(Request $request): Response
    {
        $search = trim((string) $request->input('search', ''));
        $status = $request->input('status', 'all');

        $query = Bom::query()
            ->with(['product', 'batchUom', 'items.product', 'items.uom', 'creator'])
            ->latest('id');

        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('bom_number', 'like', "%{$search}%")
                    ->orWhereHas('product', fn ($pq) => $pq->where('name', 'like', "%{$search}%")->orWhere('sku', 'like', "%{$search}%"));
            });
        }

        if ($status !== 'all' && in_array($status, ['draft', 'approved', 'obsolete', 'pending_qa'])) {
            $query->where('status', $status);
        }

        $boms = $query->paginate(12)->withQueryString();

        $fgProducts = Product::where('is_active', true)
            ->whereIn('type', ['fg', 'bulk', 'wip'])
            ->orderBy('name')
            ->get(['id', 'sku', 'name', 'uom_id']);

        $materialProducts = Product::where('is_active', true)
            ->whereIn('type', ['rm', 'pm', 'bulk', 'wip'])
            ->orderBy('name')
            ->get(['id', 'sku', 'name', 'type', 'uom_id', 'standard_cost']);

        $uoms = Uom::where('is_active', true)->orderBy('name')->get(['id', 'code', 'name']);

        return Inertia::render('Modules/SupplyChain/Boms/Index', [
            'module' => [
                'name' => 'Supply Chain (LogiFlow SCM)',
                'slug' => 'supply-chain',
                'color_theme' => 'emerald',
                'menus' => $this->moduleManager->getModuleMenus('supply-chain'),
            ],
            'boms' => $boms,
            'fgProducts' => $fgProducts,
            'materialProducts' => $materialProducts,
            'uoms' => $uoms,
            'summary' => [
                'total' => Bom::count(),
                'approved' => Bom::where('status', 'approved')->count(),
                'draft' => Bom::where('status', 'draft')->count(),
                'active' => Bom::where('is_active', true)->count(),
            ],
            'filters' => [
                'search' => $search,
                'status' => $status,
            ],
        ]);
    }

    /**
     * Store a newly created BOM in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'product_id' => 'required|exists:products,id',
            'version' => 'required|string|max:20',
            'batch_size' => 'required|numeric|min:0.0001',
            'batch_uom_id' => 'required|exists:uoms,id',
            'status' => 'required|in:draft,approved,pending_qa,obsolete',
            'notes' => 'nullable|string|max:1000',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|numeric|min:0.000001',
            'items.*.uom_id' => 'required|exists:uoms,id',
            'items.*.percentage' => 'nullable|numeric|min:0|max:100',
            'items.*.wastage_percentage' => 'nullable|numeric|min:0|max:100',
            'items.*.stage' => 'nullable|string|in:mixing,filling,packing',
            'items.*.instructions' => 'nullable|string',
        ]);

        $product = Product::findOrFail($validated['product_id']);
        $bomNumber = 'BOM-'.strtoupper($product->sku).'-V'.str_replace('.', '', $validated['version']).'-'.date('y');

        DB::transaction(function () use ($validated, $bomNumber) {
            $bom = Bom::create([
                'bom_number' => $bomNumber,
                'product_id' => $validated['product_id'],
                'version' => $validated['version'],
                'batch_size' => $validated['batch_size'],
                'batch_uom_id' => $validated['batch_uom_id'],
                'status' => $validated['status'],
                'notes' => $validated['notes'] ?? null,
                'is_active' => true,
                'created_by' => auth()->id(),
                'approved_at' => $validated['status'] === 'approved' ? now() : null,
                'approved_by' => $validated['status'] === 'approved' ? auth()->id() : null,
            ]);

            foreach ($validated['items'] as $idx => $item) {
                BomItem::create([
                    'bom_id' => $bom->id,
                    'product_id' => $item['product_id'],
                    'quantity' => $item['quantity'],
                    'percentage' => $item['percentage'] ?? null,
                    'uom_id' => $item['uom_id'],
                    'wastage_percentage' => $item['wastage_percentage'] ?? 0,
                    'stage' => $item['stage'] ?? 'mixing',
                    'instructions' => $item['instructions'] ?? null,
                    'sort_order' => $idx + 1,
                ]);
            }
        });

        return redirect()->back()->with('success', "Master BOM {$bomNumber} berhasil dibuat dengan ".count($validated['items']).' item resep.');
    }

    /**
     * Update the specified BOM in storage.
     */
    public function update(Request $request, Bom $bom): RedirectResponse
    {
        $validated = $request->validate([
            'version' => 'required|string|max:20',
            'batch_size' => 'required|numeric|min:0.0001',
            'batch_uom_id' => 'required|exists:uoms,id',
            'status' => 'required|in:draft,approved,pending_qa,obsolete',
            'notes' => 'nullable|string|max:1000',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|numeric|min:0.000001',
            'items.*.uom_id' => 'required|exists:uoms,id',
            'items.*.percentage' => 'nullable|numeric|min:0|max:100',
            'items.*.wastage_percentage' => 'nullable|numeric|min:0|max:100',
            'items.*.stage' => 'nullable|string|in:mixing,filling,packing',
            'items.*.instructions' => 'nullable|string',
        ]);

        DB::transaction(function () use ($bom, $validated) {
            $bom->update([
                'version' => $validated['version'],
                'batch_size' => $validated['batch_size'],
                'batch_uom_id' => $validated['batch_uom_id'],
                'status' => $validated['status'],
                'notes' => $validated['notes'] ?? null,
                'approved_at' => $validated['status'] === 'approved' && ! $bom->approved_at ? now() : $bom->approved_at,
                'approved_by' => $validated['status'] === 'approved' && ! $bom->approved_by ? auth()->id() : $bom->approved_by,
            ]);

            // Re-sync items
            $bom->items()->delete();

            foreach ($validated['items'] as $idx => $item) {
                BomItem::create([
                    'bom_id' => $bom->id,
                    'product_id' => $item['product_id'],
                    'quantity' => $item['quantity'],
                    'percentage' => $item['percentage'] ?? null,
                    'uom_id' => $item['uom_id'],
                    'wastage_percentage' => $item['wastage_percentage'] ?? 0,
                    'stage' => $item['stage'] ?? 'mixing',
                    'instructions' => $item['instructions'] ?? null,
                    'sort_order' => $idx + 1,
                ]);
            }
        });

        return redirect()->back()->with('success', "Master BOM {$bom->bom_number} berhasil diperbarui.");
    }

    /**
     * Remove the specified BOM.
     */
    public function destroy(Bom $bom): RedirectResponse
    {
        $num = $bom->bom_number;
        $bom->items()->delete();
        $bom->delete();

        return redirect()->back()->with('success', "Master BOM {$num} berhasil dihapus.");
    }

    /**
     * Export BOM summary to CSV.
     */
    public function export(): StreamedResponse
    {
        $boms = Bom::with(['product', 'batchUom', 'items.product'])->orderBy('bom_number')->get();

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="Master_BOM_'.date('Ymd_His').'.csv"',
        ];

        return response()->stream(function () use ($boms) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['Nomor BOM', 'Target Produk', 'Versi', 'Batch Size', 'Satuan', 'Status', 'Total Komponen', 'Tanggal Rilis']);

            foreach ($boms as $b) {
                fputcsv($handle, [
                    $b->bom_number,
                    $b->product?->name ?? '-',
                    $b->version,
                    $b->batch_size,
                    $b->batchUom?->code ?? '-',
                    strtoupper($b->status),
                    $b->items->count(),
                    $b->approved_at ? $b->approved_at->format('Y-m-d') : '-',
                ]);
            }

            fclose($handle);
        }, 200, $headers);
    }
}
