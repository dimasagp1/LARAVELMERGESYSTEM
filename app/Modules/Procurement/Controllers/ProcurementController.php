<?php

namespace App\Modules\Procurement\Controllers;

use App\Core\Services\ModuleManagerService;
use App\Http\Controllers\Controller;
use App\Modules\Procurement\Services\ProcurementDashboardService;
use Inertia\Inertia;
use Inertia\Response;

class ProcurementController extends Controller
{
    public function index(
        ModuleManagerService $moduleManager,
        ProcurementDashboardService $dashboardService
    ): Response {
        $metrics = $dashboardService->getDashboardMetrics();

        return Inertia::render('Modules/Procurement/Overview', [
            'module' => [
                'name' => 'Procurement',
                'slug' => 'procurement',
                'description' => 'Sistem rantai pengadaan terintegrasi: Granular 8-Step Item Workflow, PR Bon Khusus, Integrasi Odoo & FAT API, Logistik & Sasaran Mutu 2026.',
                'color_theme' => 'violet',
                'menus' => $moduleManager->getModuleMenus('procurement'),
            ],
            'kpis' => $metrics['kpis'],
            'qualityTargets' => $metrics['quality_targets'],
            'pipelineCounts' => $metrics['pipeline_counts'],
            'pendingApprovals' => $metrics['pending_approvals'],
            'pendingBons' => $metrics['pending_bons'],
            'charts' => $metrics['charts'],
            'recentActivities' => $metrics['recent_activities'],
        ]);
    }
}
