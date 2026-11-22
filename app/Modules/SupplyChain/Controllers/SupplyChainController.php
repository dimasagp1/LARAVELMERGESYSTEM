<?php

namespace App\Modules\SupplyChain\Controllers;

use App\Core\Services\ModuleManagerService;
use App\Http\Controllers\Controller;
use Inertia\Inertia;
use Inertia\Response;

class SupplyChainController extends Controller
{
    public function index(ModuleManagerService $moduleManager): Response
    {
        return Inertia::render('Modules/SupplyChain/Index', [
            'module' => [
                'name' => 'Supply Chain (LogiFlow SCM)',
                'slug' => 'supply-chain',
                'description' => 'Perencanaan MRP bahan, penerimaan gudang multi-warehouse, isolasi karantina QC, 3-Way Matching AP, dan pelacakan HPP aktual.',
                'color_theme' => 'indigo',
                'menus' => $moduleManager->getModuleMenus('supply-chain'),
            ],
            'kpis' => [
                'under_stock_alerts' => 3,
                'near_expiry_items' => 5,
                'mrp_accuracy_pct' => 97.4,
                'pending_3way_matching' => 4,
            ],
            'pendingApprovals' => [
                [
                    'id' => 901,
                    'title' => 'Pencocokan 3-Way Matching AP Tagihan #INV-882',
                    'description' => 'PO-0042 vs Goods Receipt GR-0034 vs Invoice PT Herbalindo',
                    'amount' => 'Rp 67.500.000',
                    'requester' => 'Staf Akun Hutang (AP)',
                    'urgency' => 'high',
                    'date' => '2 jam lalu',
                ],
                [
                    'id' => 902,
                    'title' => 'Permintaan Rilis Karantina Gudang QC: Lot #RM-091',
                    'description' => 'Ekstrak Temulawak 400 Kg Lulus QC - Siap Pindah Gudang Utama',
                    'amount' => '400 Kg',
                    'requester' => 'Kepala Gudang Karantina',
                    'urgency' => 'medium',
                    'date' => '3 jam lalu',
                ],
            ],
            'recentActivities' => [
                [
                    'user' => 'MRP Engine',
                    'action' => 'Kalkulasi Net Requirement bahan baku periode Desember 2026 selesai',
                    'module' => 'MRP Calculation',
                    'theme' => 'indigo',
                    'time' => '1 jam lalu',
                ],
                [
                    'user' => 'Staf Gudang Sukoharjo',
                    'action' => 'Penerimaan Goods Receipt GR-2026-0034 (Status: Isolasi Karantina QC)',
                    'module' => 'Goods Receipt',
                    'theme' => 'indigo',
                    'time' => '2 jam lalu',
                ],
            ],
        ]);
    }
}
