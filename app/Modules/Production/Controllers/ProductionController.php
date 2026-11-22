<?php

namespace App\Modules\Production\Controllers;

use App\Core\Services\ModuleManagerService;
use App\Http\Controllers\Controller;
use Inertia\Inertia;
use Inertia\Response;

class ProductionController extends Controller
{
    public function index(ModuleManagerService $moduleManager): Response
    {
        return Inertia::render('Modules/Production/Index', [
            'module' => [
                'name' => 'Produksi & Manufaktur',
                'slug' => 'production',
                'description' => 'Perencanaan WPS, estafet batch tracking (Mixing-Filling-Packing), timer digital, OEE realtime, dan integrasi scrap limbah.',
                'color_theme' => 'rose',
                'menus' => $moduleManager->getModuleMenus('production'),
            ],
            'kpis' => [
                'overall_oee_pct' => 84.6,
                'line_availability_pct' => 91.2,
                'performance_rate_pct' => 94.0,
                'batch_yield_pct' => 98.4,
            ],
            'pendingApprovals' => [
                [
                    'id' => 601,
                    'title' => 'Verifikasi Downtime Unplanned Lini 1 (Mixing)',
                    'description' => 'Alasan: Penyesuaian suhu chiller ekstraktor (Durasi: 45 Menit)',
                    'amount' => 'Downtime: 45m',
                    'requester' => 'Operator Mixing (Lantai 2)',
                    'urgency' => 'high',
                    'date' => '1 jam lalu',
                ],
                [
                    'id' => 602,
                    'title' => 'Jadwal WPS Minggu ke-48 Tahun 2026',
                    'description' => 'Alokasi 12 Batch Ekstrak Temulawak & Meniran Plant Sukoharjo',
                    'amount' => 'Target: 24.000 Kg',
                    'requester' => 'Perencana Produksi (PPIC)',
                    'urgency' => 'medium',
                    'date' => '3 jam lalu',
                ],
            ],
            'recentActivities' => [
                [
                    'user' => 'Live Batch Timer',
                    'action' => 'Batch #EXT-2026-088 beralih dari tahap Mixing ke Filling',
                    'module' => 'Estafet Batch',
                    'theme' => 'rose',
                    'time' => '20 menit lalu',
                ],
                [
                    'user' => 'SPV Produksi',
                    'action' => 'Melakukan line clearance area packing bersih sebelum start',
                    'module' => 'CPOB Clearance',
                    'theme' => 'rose',
                    'time' => '1 jam lalu',
                ],
            ],
        ]);
    }
}
