<?php

namespace App\Modules\Rnd\Controllers;

use App\Core\Services\ModuleManagerService;
use App\Http\Controllers\Controller;
use Inertia\Inertia;
use Inertia\Response;

class RndController extends Controller
{
    public function index(ModuleManagerService $moduleManager): Response
    {
        return Inertia::render('Modules/Rnd/Index', [
            'module' => [
                'name' => 'R&D (NPD Management)',
                'slug' => 'rnd',
                'description' => 'Siklus hidup produk baru (NPD), formulasi presisi 100%, simulasi HPP, uji sensori, uji stabilitas, dan export Dossier BPOM.',
                'color_theme' => 'cyan',
                'menus' => $moduleManager->getModuleMenus('rnd'),
            ],
            'kpis' => [
                'active_npd_projects' => 8,
                'pipeline_progress_pct' => 74.2,
                'pending_formula_approvals' => 2,
                'nie_issued_ytd' => 6,
            ],
            'pendingApprovals' => [
                [
                    'id' => 801,
                    'title' => 'Formulasi R&D V3 (100.00% Presisi)',
                    'description' => 'Kapsul Ekstrak Meniran & Jahe Merah Skala Komersial',
                    'amount' => 'HPP: Rp 16.200/btl',
                    'requester' => 'Formulator HerbaTech',
                    'urgency' => 'medium',
                    'date' => '5 jam lalu',
                ],
                [
                    'id' => 802,
                    'title' => 'Dossier Registrasi BPOM: Sirup Pegagan',
                    'description' => 'Verifikasi Kelengkapan Bagian II-IV Dokumen Legalitas',
                    'amount' => 'Target NIE: Des 2026',
                    'requester' => 'Regulator Officer NPD',
                    'urgency' => 'normal',
                    'date' => '1 hari lalu',
                ],
            ],
            'recentActivities' => [
                [
                    'user' => 'Formulator Tim R&D',
                    'action' => 'Kalkulasi HPP simulasi otomatis formula Sirup Temulawak (Rp 14.850/btl)',
                    'module' => 'Formula HPP',
                    'theme' => 'cyan',
                    'time' => '2 jam lalu',
                ],
                [
                    'user' => 'Lab Stabilitas',
                    'action' => 'Input hasil uji stabilitas dipercepat 40°C Bulan ke-3 (Lulus Spek)',
                    'module' => 'Uji Stabilitas',
                    'theme' => 'cyan',
                    'time' => '4 jam lalu',
                ],
            ],
        ]);
    }
}
