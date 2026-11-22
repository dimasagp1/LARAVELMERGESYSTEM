<?php

namespace App\Modules\Hris\Controllers;

use App\Core\Services\ModuleManagerService;
use App\Http\Controllers\Controller;
use Inertia\Inertia;
use Inertia\Response;

class HrisController extends Controller
{
    public function index(ModuleManagerService $moduleManager): Response
    {
        return Inertia::render('Modules/Hris/Index', [
            'module' => [
                'name' => 'HRIS (HRISNOW)',
                'slug' => 'hris',
                'description' => 'Presensi biometrik & geofencing, shift roster, Time Bank, kalkulasi payroll PPh 21 TER, Kaizen Kizuki, dan BSC L3/L4.',
                'color_theme' => 'blue',
                'menus' => $moduleManager->getModuleMenus('hris'),
            ],
            'kpis' => [
                'total_headcount' => 248,
                'attendance_today_pct' => 96.8,
                'overtime_hours_mtd' => 312,
                'performance_bsc_score' => 91.2,
            ],
            'pendingApprovals' => [
                [
                    'id' => 401,
                    'title' => 'Pengajuan Cuti Tahunan: Joko (Operator Ekstraksi)',
                    'description' => 'Cuti 3 Hari (12-14 Des 2026) - Shift Cover Ready',
                    'amount' => null,
                    'requester' => 'Joko Susilo',
                    'urgency' => 'normal',
                    'date' => '30 menit lalu',
                ],
                [
                    'id' => 402,
                    'title' => 'Surat Perintah Lembur (SPL) Lini Packing 2',
                    'description' => 'Target kejar batch kapsul meniran 40.000 blister',
                    'amount' => '4 Jam Lembur x 8 Orang',
                    'requester' => 'Mandor Packing',
                    'urgency' => 'high',
                    'date' => '2 jam lalu',
                ],
            ],
            'recentActivities' => [
                [
                    'user' => 'Face Biometric Matcher',
                    'action' => 'Verifikasi kehadiran 240 karyawan Sukoharjo & Boyolali (Anti-Spoof Valid)',
                    'module' => 'Presensi',
                    'theme' => 'blue',
                    'time' => '1 jam lalu',
                ],
                [
                    'user' => 'Payroll TER Engine',
                    'action' => 'Kalkulasi draf PPh 21 TER masa pajak November selesai',
                    'module' => 'Payroll',
                    'theme' => 'blue',
                    'time' => '4 jam lalu',
                ],
            ],
        ]);
    }
}
