<?php

namespace App\Modules\Production\Controllers;

use App\Core\Services\ModuleManagerService;
use App\Http\Controllers\Controller;
use App\Modules\Production\Services\ProductionDashboardService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ProductionController extends Controller
{
    public function __construct(
        protected ProductionDashboardService $dashboardService,
        protected ModuleManagerService $moduleManager
    ) {}

    /**
     * Display the Production Module Overview Dashboard.
     */
    public function index(Request $request): Response
    {
        $date = $request->input('date', now()->toDateString());
        $shift = $request->input('shift', 'all');

        $metrics = $this->dashboardService->getDashboardMetrics($date, $shift);

        return Inertia::render('Modules/Produksi/Overview', [
            'module' => [
                'name' => 'Produksi & Manufaktur',
                'slug' => 'production',
                'description' => 'Perencanaan WPS, estafet batch tracking (Mixing-Filling-Packing), timer digital, OEE realtime, dan integrasi scrap limbah.',
                'color_theme' => 'rose',
                'menus' => $this->moduleManager->getModuleMenus('production'),
            ],
            'metrics' => $metrics,
            'filters' => [
                'date' => $date,
                'shift' => $shift,
            ],
        ]);
    }
}
