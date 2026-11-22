<?php

namespace App\Modules\SupplyChain\Controllers;

use App\Http\Controllers\Controller;
use Inertia\Inertia;
use Inertia\Response;

class SupplyChainController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('Modules/SupplyChain/Index', [
            'module' => [
                'name' => 'Supply Chain (LogiFlow SCM)',
                'slug' => 'supply-chain',
                'description' => 'Perencanaan MRP bahan, penerimaan gudang multi-warehouse, isolasi karantina QC, 3-Way Matching AP, dan pelacakan HPP aktual.',
            ],
            'kpis' => [
                'under_stock_alerts' => 3,
                'near_expiry_items' => 5,
                'mrp_accuracy_pct' => 97.4,
                'pending_3way_matching' => 4,
            ],
        ]);
    }
}
