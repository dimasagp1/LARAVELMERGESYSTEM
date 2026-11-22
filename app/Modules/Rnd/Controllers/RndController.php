<?php

namespace App\Modules\Rnd\Controllers;

use App\Http\Controllers\Controller;
use Inertia\Inertia;
use Inertia\Response;

class RndController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('Modules/Rnd/Index', [
            'module' => [
                'name' => 'R&D (NPD Management)',
                'slug' => 'rnd',
                'description' => 'Siklus hidup produk baru (NPD), formulasi presisi 100%, simulasi HPP, uji sensori, uji stabilitas, dan export Dossier BPOM.',
            ],
            'kpis' => [
                'active_npd_projects' => 8,
                'pipeline_progress_pct' => 74.2,
                'pending_formula_approvals' => 2,
                'nie_issued_ytd' => 6,
            ],
        ]);
    }
}
