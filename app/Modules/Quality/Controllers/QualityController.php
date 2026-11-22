<?php

namespace App\Modules\Quality\Controllers;

use App\Http\Controllers\Controller;
use Inertia\Inertia;
use Inertia\Response;

class QualityController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('Modules/Quality/Index', [
            'module' => [
                'name' => 'Quality Management System (QMS)',
                'slug' => 'quality',
                'description' => 'Tata kelola dokumen CPOB 4-tingkat, Change Request CRA/CRB, investigasi deviasi Fishbone 6M & 5-Why, auto-spawn CAPA, dan digital signature 21 CFR Part 11.',
            ],
            'kpis' => [
                'active_qms_docs' => 142,
                'open_change_requests' => 3,
                'open_deviations' => 2,
                'overdue_capa' => 0,
            ],
        ]);
    }
}
