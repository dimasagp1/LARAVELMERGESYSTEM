<?php

namespace App\Modules\Finance\Controllers;

use App\Core\Services\ModuleManagerService;
use App\Http\Controllers\Controller;
use App\Modules\Finance\Services\FinanceDashboardService;
use Inertia\Inertia;
use Inertia\Response;

class FinanceController extends Controller
{
    public function index(
        ModuleManagerService $moduleManager,
        FinanceDashboardService $dashboardService
    ): Response {
        $metrics = $dashboardService->getDashboardMetrics();

        return Inertia::render('Modules/Finance/Overview', [
            'module' => [
                'name' => 'Finance & Budgeting',
                'slug' => 'finance',
                'description' => 'Akuntansi keuangan terpusat, Kertas Kerja Trial Balance 4-Kolom, Budget P&L 11 Departemen, 19 Rasio Balanced Scorecard (BSC), dan Budget Gatekeeper.',
                'color_theme' => 'emerald',
                'menus' => $moduleManager->getModuleMenus('finance'),
            ],
            'period' => $metrics['period'],
            'kpis' => $metrics['kpis'],
            'reconciliationSummary' => $metrics['reconciliation_summary'],
            'bscPillars' => $metrics['bsc_pillars'],
            'pendingAje' => $metrics['pending_aje'],
            'charts' => $metrics['charts'],
            'recentActivities' => $metrics['recent_activities'],
        ]);
    }
}
