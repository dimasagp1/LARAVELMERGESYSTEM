<?php

namespace App\Modules\Quality\Controllers;

use App\Core\Services\ModuleManagerService;
use App\Http\Controllers\Controller;
use Inertia\Inertia;
use Inertia\Response;

class QualityController extends Controller
{
    public function index(ModuleManagerService $moduleManager): Response
    {
        return Inertia::render('Modules/Quality/Index', [
            'module' => [
                'name' => 'Quality Management System (QMS)',
                'slug' => 'quality',
                'description' => 'Tata kelola dokumen CPOB 4-tingkat, Change Request CRA/CRB, investigasi deviasi Fishbone 6M & 5-Why, auto-spawn CAPA, dan digital signature 21 CFR Part 11.',
                'color_theme' => 'teal',
                'menus' => $moduleManager->getModuleMenus('quality'),
            ],
            'kpis' => [
                'active_qms_docs' => 142,
                'open_change_requests' => 3,
                'open_deviations' => 2,
                'overdue_capa' => 0,
            ],
            'pendingApprovals' => [
                [
                    'id' => 701,
                    'title' => 'Change Request CRA-2026-012 (Major FMEA)',
                    'description' => 'Penyesuaian Suhu HVAC Cleanroom Area Mixing Ekstrak',
                    'amount' => 'RPN Score: 72',
                    'requester' => 'Dr. Rina Wati (QA Spv)',
                    'urgency' => 'high',
                    'date' => '3 jam lalu',
                ],
                [
                    'id' => 702,
                    'title' => 'Rilis Bahan Baku Curcuma #RM-091',
                    'description' => 'Hasil Pengujian Lab HPLC Kadar Kurkuminoid Lulus',
                    'amount' => 'Lot: 500 Kg',
                    'requester' => 'Analis QC Mikrobiologi',
                    'urgency' => 'medium',
                    'date' => '5 jam lalu',
                ],
            ],
            'recentActivities' => [
                [
                    'user' => 'Head of QA / APJ',
                    'action' => 'Tanda tangan elektronik 21 CFR Part 11 untuk Rilis Batch #CAP-042',
                    'module' => 'Digital Signature',
                    'theme' => 'teal',
                    'time' => '1 jam lalu',
                ],
                [
                    'user' => 'QA Officer',
                    'action' => 'Verifikasi efektivitas CAPA-2026-015 Selesai dan Ditutup',
                    'module' => 'CAPA',
                    'theme' => 'teal',
                    'time' => '3 jam lalu',
                ],
            ],
        ]);
    }
}
