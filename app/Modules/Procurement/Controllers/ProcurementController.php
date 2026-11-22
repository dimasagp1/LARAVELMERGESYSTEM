<?php

namespace App\Modules\Procurement\Controllers;

use App\Http\Controllers\Controller;
use Inertia\Inertia;
use Inertia\Response;

class ProcurementController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('Modules/Procurement/Index', [
            'module' => [
                'name' => 'Procurement',
                'slug' => 'procurement',
                'description' => 'Manajemen rantai pengadaan barang/jasa, granular 8-step PR item workflow, vendor bidding matrix, integrasi Odoo PO, dan evaluasi AVL.',
            ],
            'kpis' => [
                'active_pr_count' => 18,
                'po_value_mtd' => 'Rp 920.000.000',
                'avg_lead_time_days' => 5.2,
                'cost_saving_pct' => 7.8,
            ],
        ]);
    }
}
