<?php

namespace App\Modules\SupplyChain\Controllers;

use App\Core\Services\ModuleManagerService;
use App\Http\Controllers\Controller;
use App\Modules\SupplyChain\Services\ScmDashboardService;
use Inertia\Inertia;
use Inertia\Response;

class SupplyChainController extends Controller
{
    public function __construct(
        protected ScmDashboardService $dashboardService,
        protected ModuleManagerService $moduleManager
    ) {}

    /**
     * Display the LogiFlow SCM Overview Interactive Dashboard.
     */
    public function index(): Response
    {
        $metrics = $this->dashboardService->getOverviewMetrics();

        return Inertia::render('Modules/SupplyChain/Overview', [
            'module' => [
                'name' => 'Supply Chain & Inventory',
                'slug' => 'supply-chain',
                'description' => 'LogiFlow SCM — MRP, Goods Receipt, QC 4-Parameter, BSTB, 3-Way Matching, Multi-Warehouse.',
                'color_theme' => 'emerald',
                'menus' => $this->moduleManager->getModuleMenus('supply-chain'),
            ],
            'metrics' => $metrics,
        ]);
    }
}
