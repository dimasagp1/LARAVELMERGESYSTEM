<?php

namespace App\Modules\Finance\Controllers;

use App\Http\Controllers\Controller;
use Inertia\Inertia;
use Inertia\Response;

class FinanceController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('Modules/Finance/Index', [
            'module' => [
                'name' => 'Finance & Budgeting',
                'slug' => 'finance',
                'description' => 'Akuntansi keuangan terpusat, penganggaran departemen, rekonsiliasi kas/bank, dan Real-Time Budget Gatekeeper.',
            ],
            'kpis' => [
                'omset_mtd' => 'Rp 1.450.000.000',
                'net_profit_mtd' => 'Rp 385.000.000',
                'budget_absorption_pct' => 68.4,
                'bsc_finance_score' => 88.5,
            ],
        ]);
    }
}
