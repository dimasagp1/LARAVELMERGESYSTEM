<?php

namespace App\Modules\Quality\Controllers;

use App\Core\Services\ModuleManagerService;
use App\Http\Controllers\Controller;
use App\Modules\Quality\Services\QualityDashboardService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class QualityController extends Controller
{
    public function __construct(
        protected QualityDashboardService $dashboardService,
        protected ModuleManagerService $moduleManager
    ) {}

    /**
     * Display the Quality Management System (QMS) Overview Dashboard.
     */
    public function index(Request $request): Response
    {
        $metrics = $this->dashboardService->getDashboardMetrics();

        return Inertia::render('Modules/Quality/Overview', [
            'module' => [
                'name' => 'Quality Management System (QMS)',
                'slug' => 'quality',
                'description' => 'Tata kelola dokumen CPOB 4-tingkat, Change Request CRA/CRB, investigasi deviasi Fishbone 6M & 5-Why, auto-spawn CAPA, dan digital signature.',
                'color_theme' => 'teal',
                'menus' => $this->moduleManager->getModuleMenus('quality'),
            ],
            'kpis' => $metrics['kpis'],
            'deviationsBySeverity' => $metrics['deviationsBySeverity'],
            'capaStatus' => $metrics['capaStatus'],
            'docsByLevel' => $metrics['docsByLevel'],
            'fishboneCounts' => $metrics['fishboneCounts'],
            'pendingApprovals' => $metrics['pendingApprovals'],
            'recentActivities' => $metrics['recentActivities'],
        ]);
    }
}
