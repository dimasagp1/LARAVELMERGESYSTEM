<?php

namespace App\Modules\Hris\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Hris\Services\HrisDashboardService;
use Inertia\Inertia;
use Inertia\Response;

class HrisController extends Controller
{
    public function __construct(
        protected HrisDashboardService $dashboardService
    ) {}

    /**
     * Display the HRISNOW Overview Interactive Dashboard.
     */
    public function index(): Response
    {
        $metrics = $this->dashboardService->getOverviewMetrics();

        return Inertia::render('Modules/Hris/Overview', [
            'metrics' => $metrics,
        ]);
    }
}
