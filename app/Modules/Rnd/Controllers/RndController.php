<?php

namespace App\Modules\Rnd\Controllers;

use App\Core\Services\ModuleManagerService;
use App\Http\Controllers\Controller;
use App\Modules\Rnd\Services\RndDashboardService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class RndController extends Controller
{
    public function __construct(
        protected RndDashboardService $dashboardService,
        protected ModuleManagerService $moduleManager
    ) {}

    public function index(Request $request): Response
    {
        $dashboardData = $this->dashboardService->getDashboardData($request->user());

        return Inertia::render('Modules/Rnd/Overview', [
            'module' => [
                'name' => 'R&D (NPD Management)',
                'slug' => 'rnd',
                'description' => 'Siklus hidup produk baru (NPD), formulasi presisi 100%, simulasi HPP, uji sensori, uji stabilitas, dan export Dossier BPOM.',
                'color_theme' => 'cyan',
                'menus' => $this->moduleManager->getModuleMenus('rnd'),
            ],
            'kpis' => $dashboardData['kpis'],
            'pendingQueue' => $dashboardData['pendingQueue'],
            'productCards' => $dashboardData['productCards'],
            'recentActivities' => $dashboardData['recentActivities'],
            'charts' => $dashboardData['charts'],
        ]);
    }
}
