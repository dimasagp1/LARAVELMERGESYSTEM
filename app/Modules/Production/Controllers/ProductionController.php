<?php

namespace App\Modules\Production\Controllers;

use App\Http\Controllers\Controller;
use Inertia\Inertia;
use Inertia\Response;

class ProductionController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('Modules/Production/Index', [
            'module' => [
                'name' => 'Produksi & Manufaktur',
                'slug' => 'production',
                'description' => 'Perencanaan WPS, estafet batch tracking (Mixing-Filling-Packing), timer digital, OEE realtime, dan integrasi scrap limbah.',
            ],
            'kpis' => [
                'overall_oee_pct' => 84.6,
                'line_availability_pct' => 91.2,
                'performance_rate_pct' => 94.0,
                'batch_yield_pct' => 98.4,
            ],
        ]);
    }
}
