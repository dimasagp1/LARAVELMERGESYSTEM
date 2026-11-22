<?php

namespace App\Modules\GeneralAffair\Controllers;

use App\Core\Services\ModuleManagerService;
use App\Http\Controllers\Controller;
use App\Modules\GeneralAffair\Services\GeneralAffairDashboardService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class GeneralAffairController extends Controller
{
    public function __construct(
        protected GeneralAffairDashboardService $dashboardService,
        protected ModuleManagerService $moduleManager
    ) {}

    /**
     * Display the General Affair (FPUGA) Overview Dashboard.
     */
    public function index(Request $request): Response
    {
        $metrics = $this->dashboardService->getDashboardMetrics();

        return Inertia::render('Modules/GeneralAffair/Overview', [
            'module' => [
                'name' => 'General Affair (FPUGA)',
                'slug' => 'general-affair',
                'description' => 'Pemeliharaan sarana/fasilitas pabrik (FPU), pengelolaan ATK/RT, manajemen aset tetap multi-plant, keamanan patroli QR, dan sanitasi kebersihan.',
                'color_theme' => 'amber',
                'menus' => $this->moduleManager->getModuleMenus('general-affair'),
            ],
            'kpis' => $metrics['kpis'],
            'woStatusCounts' => $metrics['woStatusCounts'],
            'woPriorityCounts' => $metrics['woPriorityCounts'],
            'assetsByPlant' => $metrics['assetsByPlant'],
            'patrolSummary' => $metrics['patrolSummary'],
            'pendingApprovals' => $metrics['pendingApprovals'],
            'recentActivities' => $metrics['recentActivities'],
        ]);
    }
}
