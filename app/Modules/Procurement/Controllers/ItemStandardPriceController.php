<?php

namespace App\Modules\Procurement\Controllers;

use App\Core\Models\Product;
use App\Core\Models\Vendor;
use App\Core\Services\ModuleManagerService;
use App\Http\Controllers\Controller;
use App\Modules\Procurement\Models\ItemStandardPrice;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ItemStandardPriceController extends Controller
{
    public function __construct(
        protected ModuleManagerService $moduleManager
    ) {}

    public function index(Request $request): Response
    {
        $year = (int) $request->input('effective_year', Carbon::now()->format('Y'));
        $query = ItemStandardPrice::with(['product', 'vendor'])
            ->where('effective_year', $year)
            ->latest('id');

        if ($request->filled('category_type')) {
            $query->where('category_type', $request->input('category_type'));
        }

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('item_name', 'like', "%{$search}%")
                    ->orWhere('item_code', 'like', "%{$search}%")
                    ->orWhere('vendor_name', 'like', "%{$search}%");
            });
        }

        $prices = $query->paginate(15)->withQueryString();

        return Inertia::render('Modules/Procurement/StandardPrice/Index', [
            'module' => [
                'name' => 'Standar Harga & AVL',
                'slug' => 'procurement',
                'color_theme' => 'cyan',
                'menus' => $this->moduleManager->getModuleMenus('procurement'),
            ],
            'prices' => $prices,
            'vendors' => Vendor::select('id', 'name', 'code')->get(),
            'products' => Product::select('id', 'name', 'code', 'uom')->get(),
            'effectiveYear' => $year,
            'filters' => $request->only(['category_type', 'search']),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'product_id' => 'nullable|exists:products,id',
            'item_code' => 'required|string|max:50',
            'item_name' => 'required|string|max:255',
            'category_type' => 'required|in:material,supporting',
            'uom' => 'required|string|max:20',
            'standard_price' => 'required|numeric|min:0',
            'vendor_id' => 'nullable|exists:vendors,id',
            'vendor_name' => 'nullable|string',
            'effective_year' => 'required|integer',
        ]);

        ItemStandardPrice::create($validated);

        return back()->with('success', 'Standar harga acuan barang berhasil ditambahkan.');
    }

    public function syncMaster(): RedirectResponse
    {
        $products = Product::all();
        $year = (int) Carbon::now()->format('Y');

        foreach ($products as $p) {
            $category = in_array($p->type, ['raw_material', 'packaging']) ? 'material' : 'supporting';
            ItemStandardPrice::firstOrCreate(
                [
                    'item_code' => $p->code,
                    'effective_year' => $year,
                ],
                [
                    'product_id' => $p->id,
                    'item_name' => $p->name,
                    'category_type' => $category,
                    'uom' => $p->uom,
                    'standard_price' => (float) ($p->cost_price ?: 50000),
                    'is_active' => true,
                ]
            );
        }

        return back()->with('success', 'Sinkronisasi standar harga dari katalog master produk berhasil.');
    }
}
