<?php

namespace App\Modules\Hris\Controllers;

use App\Http\Controllers\Controller;
use Inertia\Inertia;
use Inertia\Response;

class HrisController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('Modules/Hris/Index', [
            'module' => [
                'name' => 'HRIS (HRISNOW)',
                'slug' => 'hris',
                'description' => 'Presensi biometrik & geofencing, shift roster, Time Bank, kalkulasi payroll PPh 21 TER, Kaizen Kizuki, dan BSC L3/L4.',
            ],
            'kpis' => [
                'total_headcount' => 248,
                'attendance_today_pct' => 96.8,
                'overtime_hours_mtd' => 312,
                'performance_bsc_score' => 91.2,
            ],
        ]);
    }
}
