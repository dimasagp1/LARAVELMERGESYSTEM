<?php

namespace App\Modules\GeneralAffair\Controllers;

use App\Core\Services\ModuleManagerService;
use App\Http\Controllers\Controller;
use Inertia\Inertia;
use Inertia\Response;

class GeneralAffairController extends Controller
{
    public function index(ModuleManagerService $moduleManager): Response
    {
        return Inertia::render('Modules/GeneralAffair/Index', [
            'module' => [
                'name' => 'General Affair (FPUGA)',
                'slug' => 'general-affair',
                'description' => 'Pemeliharaan sarana/fasilitas pabrik, pengelolaan ATK/RT, manajemen aset tetap multi-plant, keamanan patroli QR, dan IT Helpdesk.',
                'color_theme' => 'amber',
                'menus' => $moduleManager->getModuleMenus('general-affair'),
            ],
            'kpis' => [
                'open_work_orders' => 12,
                'pending_atk_requests' => 5,
                'total_asset_value' => 'Rp 8.750.000.000',
                'security_anomalies' => 0,
            ],
            'pendingApprovals' => [
                [
                    'id' => 301,
                    'title' => 'Permintaan ATK Departemen Produksi (Revisi-1)',
                    'description' => 'Penyesuaian kuantitas label barcode dan kertas A4',
                    'amount' => 'Rp 2.450.000',
                    'requester' => 'Siti (Admin Produksi)',
                    'urgency' => 'normal',
                    'date' => '1 jam lalu',
                ],
                [
                    'id' => 302,
                    'title' => 'Work Order Form C: Penggantian Filter HVAC',
                    'description' => 'Kebutuhan material filter HEPA cleanroom boyolali',
                    'amount' => 'Rp 8.200.000',
                    'requester' => 'Teknisi FPU Boyolali',
                    'urgency' => 'high',
                    'date' => '3 jam lalu',
                ],
            ],
            'recentActivities' => [
                [
                    'user' => 'Officer Keamanan',
                    'action' => 'Menyelesaikan scan QR patroli 12 Checkpoint Plant 1 Sukoharjo (Nol Anomali)',
                    'module' => 'Patrol QR',
                    'theme' => 'amber',
                    'time' => '30 menit lalu',
                ],
                [
                    'user' => 'GA Manager',
                    'action' => 'Menyetujui Work Order WO-2026-0018 Perbaikan Pipa Chiller',
                    'module' => 'FPU',
                    'theme' => 'amber',
                    'time' => '2 jam lalu',
                ],
            ],
        ]);
    }
}
