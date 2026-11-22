<?php

namespace App\Modules\Finance\Controllers;

use App\Core\Services\ModuleManagerService;
use App\Http\Controllers\Controller;
use Inertia\Inertia;
use Inertia\Response;

class FinanceController extends Controller
{
    public function index(ModuleManagerService $moduleManager): Response
    {
        return Inertia::render('Modules/Finance/Index', [
            'module' => [
                'name' => 'Finance & Budgeting',
                'slug' => 'finance',
                'description' => 'Akuntansi keuangan terpusat, penganggaran departemen, rekonsiliasi kas/bank, dan Real-Time Budget Gatekeeper.',
                'color_theme' => 'emerald',
                'menus' => $moduleManager->getModuleMenus('finance'),
            ],
            'kpis' => [
                'omset_mtd' => 'Rp 1.450.000.000',
                'net_profit_mtd' => 'Rp 385.000.000',
                'budget_absorption_pct' => 68.4,
                'bsc_finance_score' => 88.5,
            ],
            'pendingApprovals' => [
                [
                    'id' => 201,
                    'title' => 'Jurnal Penyesuaian AJE-2026-044',
                    'description' => 'Akrual Biaya Pemeliharaan Mesin Granulasi Plant 1',
                    'amount' => 'Rp 14.800.000',
                    'requester' => 'Ahmad (Staf FAT)',
                    'urgency' => 'normal',
                    'date' => '2 jam lalu',
                ],
                [
                    'id' => 202,
                    'title' => 'Pelepasan Anggaran Darurat PR-EMERGENCY',
                    'description' => 'Suku Cadang Purified Water System CPOB',
                    'amount' => 'Rp 28.500.000',
                    'requester' => 'Hendri (FAT Spv)',
                    'urgency' => 'high',
                    'date' => '4 jam lalu',
                ],
            ],
            'recentActivities' => [
                [
                    'user' => 'Super Administrator',
                    'action' => 'Melakukan approval Jurnal Penyesuaian AJE-2026-042',
                    'module' => 'Finance',
                    'theme' => 'emerald',
                    'time' => '15 menit lalu',
                ],
                [
                    'user' => 'Budget Gatekeeper API',
                    'action' => 'Memvalidasi pagu anggaran PR-2026-0089 Procurement (Committed Rp 67.5M)',
                    'module' => 'Gatekeeper',
                    'theme' => 'emerald',
                    'time' => '1 jam lalu',
                ],
                [
                    'user' => 'Staf Keuangan',
                    'action' => 'Import General Ledger Odoo Sinkronisasi Periode Q3',
                    'module' => 'Finance',
                    'theme' => 'emerald',
                    'time' => '3 jam lalu',
                ],
            ],
        ]);
    }
}
