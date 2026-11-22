<?php

namespace App\Modules\GeneralAffair\Controllers;

use App\Http\Controllers\Controller;
use Inertia\Inertia;
use Inertia\Response;

class GeneralAffairController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('Modules/GeneralAffair/Index', [
            'module' => [
                'name' => 'General Affair (FPUGA)',
                'slug' => 'general-affair',
                'description' => 'Pemeliharaan sarana/fasilitas pabrik, pengelolaan ATK/RT, manajemen aset tetap multi-plant, keamanan patroli QR, dan IT Helpdesk.',
            ],
            'kpis' => [
                'open_work_orders' => 12,
                'pending_atk_requests' => 5,
                'total_asset_value' => 'Rp 8.750.000.000',
                'security_anomalies' => 0,
            ],
        ]);
    }
}
