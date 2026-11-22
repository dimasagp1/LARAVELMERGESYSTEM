<?php

namespace App\Modules\Procurement\Controllers;

use App\Core\Services\ModuleManagerService;
use App\Http\Controllers\Controller;
use Inertia\Inertia;
use Inertia\Response;

class ProcurementController extends Controller
{
    public function index(ModuleManagerService $moduleManager): Response
    {
        return Inertia::render('Modules/Procurement/Index', [
            'module' => [
                'name' => 'Procurement',
                'slug' => 'procurement',
                'description' => 'Manajemen rantai pengadaan barang/jasa, granular 8-step PR item workflow, vendor bidding matrix, integrasi Odoo PO, dan evaluasi AVL.',
                'color_theme' => 'violet',
                'menus' => $moduleManager->getModuleMenus('procurement'),
            ],
            'kpis' => [
                'active_pr_count' => 18,
                'po_value_mtd' => 'Rp 920.000.000',
                'avg_lead_time_days' => 5.2,
                'cost_saving_pct' => 7.8,
            ],
            'pendingApprovals' => [
                [
                    'id' => 501,
                    'title' => 'Purchase Request PR-2026-0089',
                    'description' => 'Pengadaan Ekstrak Curcuma Xanthorrhiza 500 Kg',
                    'amount' => 'Rp 67.500.000',
                    'requester' => 'Budi Santoso (R&D)',
                    'urgency' => 'high',
                    'date' => '2 jam lalu',
                ],
                [
                    'id' => 502,
                    'title' => 'Purchase Order PO-2026-0043 (Revisi Penawaran)',
                    'description' => 'Botol Kaca Amber 100ml 50.000 Pcs (Quotation Matrix Terpilih)',
                    'amount' => 'Rp 45.000.000',
                    'requester' => 'Buyer Bahan Kemas',
                    'urgency' => 'medium',
                    'date' => '4 jam lalu',
                ],
            ],
            'recentActivities' => [
                [
                    'user' => 'Procurement Manager',
                    'action' => 'Approval Baris Item PR-2026-0088 Granular 8-Step Selesai',
                    'module' => 'PR Workflow',
                    'theme' => 'violet',
                    'time' => '45 menit lalu',
                ],
                [
                    'user' => 'Odoo JSON-RPC Bridge',
                    'action' => 'Sync PO-2026-0042 ke Odoo General Ledger Sukses',
                    'module' => 'Odoo Sync',
                    'theme' => 'violet',
                    'time' => '2 jam lalu',
                ],
            ],
        ]);
    }
}
