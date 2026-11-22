<?php

namespace App\Modules\SupplyChain\Controllers;

use App\Core\Models\Location;
use App\Core\Models\Plant;
use App\Http\Controllers\Controller;
use App\Modules\SupplyChain\Models\ScmInventoryStock;
use App\Modules\SupplyChain\Models\ScmStockMovement;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class InventoryStockController extends Controller
{
    /**
     * Display the Multi-Warehouse stock matrix, batch expiry tracking, and movement ledger.
     */
    public function index(Request $request): Response
    {
        $query = ScmInventoryStock::with(['product.uom', 'location', 'plant']);

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('batch_number', 'like', "%{$search}%")
                    ->orWhereHas('product', function ($p) use ($search) {
                        $p->where('name', 'like', "%{$search}%")
                            ->orWhere('code', 'like', "%{$search}%");
                    });
            });
        }

        if ($request->filled('plant_id')) {
            $query->where('plant_id', $request->plant_id);
        }

        if ($request->filled('location_id')) {
            $query->where('location_id', $request->location_id);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $stocks = $query->latest('updated_at')->paginate(12)->withQueryString();

        $recentMovements = ScmStockMovement::with(['product.uom', 'fromLocation', 'toLocation', 'creator'])
            ->latest()
            ->take(15)
            ->get();

        $plants = Plant::where('is_active', true)->get(['id', 'code', 'name']);
        $locations = Location::all(['id', 'code', 'name', 'plant_id']);

        return Inertia::render('Modules/SupplyChain/Stock/Index', [
            'stocks' => $stocks,
            'recentMovements' => $recentMovements,
            'plants' => $plants,
            'locations' => $locations,
            'filters' => $request->only(['search', 'plant_id', 'location_id', 'status']),
        ]);
    }
}
