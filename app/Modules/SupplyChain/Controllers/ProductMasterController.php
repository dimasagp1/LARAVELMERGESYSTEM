<?php

namespace App\Modules\SupplyChain\Controllers;

use App\Core\Models\Product;
use App\Core\Models\Uom;
use App\Core\Services\ModuleManagerService;
use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ProductMasterController extends Controller
{
    public function __construct(
        protected ModuleManagerService $moduleManager
    ) {}

    /**
     * Display a listing of Master Produk (RM, PM, Bulk, FG).
     */
    public function index(Request $request): Response
    {
        $search = trim((string) $request->input('search', ''));
        $type = $request->input('type', 'all');
        $status = $request->input('status', 'all');

        $query = Product::query()
            ->with(['uom', 'secondaryUom'])
            ->latest('id');

        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('sku', 'like', "%{$search}%")
                    ->orWhere('name', 'like', "%{$search}%")
                    ->orWhere('category', 'like', "%{$search}%");
            });
        }

        if ($type !== 'all') {
            $query->where('type', $type);
        }

        if ($status === 'active') {
            $query->where('is_active', true);
        } elseif ($status === 'inactive') {
            $query->where('is_active', false);
        }

        $products = $query->paginate(15)->withQueryString();

        // Summary KPI counts
        $totalProducts = Product::count();
        $rmCount = Product::where('type', 'rm')->count();
        $pmCount = Product::where('type', 'pm')->count();
        $fgCount = Product::where('type', 'fg')->count();
        $bulkCount = Product::where('type', 'bulk')->count();

        return Inertia::render('Modules/SupplyChain/Products/Index', [
            'module' => [
                'name' => 'Supply Chain (LogiFlow SCM)',
                'slug' => 'supply-chain',
                'color_theme' => 'emerald',
                'menus' => $this->moduleManager->getModuleMenus('supply-chain'),
            ],
            'products' => $products,
            'uoms' => Uom::where('is_active', true)->orderBy('name')->get(['id', 'code', 'name']),
            'summary' => [
                'total' => $totalProducts,
                'rm' => $rmCount,
                'pm' => $pmCount,
                'fg' => $fgCount,
                'bulk' => $bulkCount,
            ],
            'filters' => [
                'search' => $search,
                'type' => $type,
                'status' => $status,
            ],
        ]);
    }

    /**
     * Store a newly created product in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'sku' => 'required|string|max:100|unique:products,sku',
            'name' => 'required|string|max:200',
            'type' => 'required|in:fg,rm,pm,bulk,wip,atk,sparepart',
            'category' => 'nullable|string|max:100',
            'uom_id' => 'required|exists:uoms,id',
            'secondary_uom_id' => 'nullable|exists:uoms,id',
            'conversion_rate' => 'nullable|numeric|min:0.000001',
            'min_stock' => 'nullable|numeric|min:0',
            'safety_stock' => 'nullable|numeric|min:0',
            'reorder_point' => 'nullable|numeric|min:0',
            'standard_cost' => 'nullable|numeric|min:0',
            'selling_price' => 'nullable|numeric|min:0',
            'description' => 'nullable|string|max:1000',
        ]);

        $validated['conversion_rate'] = $validated['conversion_rate'] ?? 1.0;
        $validated['min_stock'] = $validated['min_stock'] ?? 0;
        $validated['safety_stock'] = $validated['safety_stock'] ?? 0;
        $validated['reorder_point'] = $validated['reorder_point'] ?? 0;
        $validated['standard_cost'] = $validated['standard_cost'] ?? 0;
        $validated['selling_price'] = $validated['selling_price'] ?? 0;
        $validated['is_active'] = true;

        Product::create($validated);

        return redirect()->back()->with('success', "Produk {$validated['sku']} - {$validated['name']} berhasil ditambahkan.");
    }

    /**
     * Update the specified product in storage.
     */
    public function update(Request $request, Product $product): RedirectResponse
    {
        $validated = $request->validate([
            'sku' => "required|string|max:100|unique:products,sku,{$product->id}",
            'name' => 'required|string|max:200',
            'type' => 'required|in:fg,rm,pm,bulk,wip,atk,sparepart',
            'category' => 'nullable|string|max:100',
            'uom_id' => 'required|exists:uoms,id',
            'secondary_uom_id' => 'nullable|exists:uoms,id',
            'conversion_rate' => 'nullable|numeric|min:0.000001',
            'min_stock' => 'nullable|numeric|min:0',
            'safety_stock' => 'nullable|numeric|min:0',
            'reorder_point' => 'nullable|numeric|min:0',
            'standard_cost' => 'nullable|numeric|min:0',
            'selling_price' => 'nullable|numeric|min:0',
            'description' => 'nullable|string|max:1000',
            'is_active' => 'boolean',
        ]);

        $product->update($validated);

        return redirect()->back()->with('success', "Produk {$product->sku} berhasil diperbarui.");
    }

    /**
     * Remove the specified product.
     */
    public function destroy(Product $product): RedirectResponse
    {
        $sku = $product->sku;
        $product->delete();

        return redirect()->back()->with('success', "Produk {$sku} berhasil dihapus.");
    }

    /**
     * Export products to CSV.
     */
    public function export(): StreamedResponse
    {
        $products = Product::with(['uom', 'secondaryUom'])->orderBy('sku')->get();

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="Master_Produk_'.date('Ymd_His').'.csv"',
        ];

        return response()->stream(function () use ($products) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['SKU', 'Nama Produk', 'Tipe', 'Kategori', 'Satuan Utama', 'Safety Stock', 'Reorder Point', 'Biaya Standar', 'Harga Jual', 'Status']);

            foreach ($products as $p) {
                fputcsv($handle, [
                    $p->sku,
                    $p->name,
                    strtoupper($p->type),
                    $p->category ?? '-',
                    $p->uom?->code ?? '-',
                    $p->safety_stock,
                    $p->reorder_point,
                    $p->standard_cost,
                    $p->selling_price,
                    $p->is_active ? 'Aktif' : 'Nonaktif',
                ]);
            }

            fclose($handle);
        }, 200, $headers);
    }
}
