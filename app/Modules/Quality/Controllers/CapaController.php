<?php

namespace App\Modules\Quality\Controllers;

use App\Core\Services\ModuleManagerService;
use App\Http\Controllers\Controller;
use App\Models\User;
use App\Modules\Quality\Models\Capa;
use App\Modules\Quality\Models\CapaAction;
use App\Modules\Quality\Services\CapaService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response as HttpResponse;
use Inertia\Inertia;
use Inertia\Response;

class CapaController extends Controller
{
    public function __construct(
        protected CapaService $capaService,
        protected ModuleManagerService $moduleManager
    ) {}

    public function index(Request $request): Response
    {
        $filters = $request->only(['search', 'status']);
        $capas = $this->capaService->getCapas($filters);

        return Inertia::render('Modules/Quality/Capas/Index', [
            'module' => [
                'name' => 'Quality Management System',
                'slug' => 'quality',
                'color_theme' => 'teal',
                'menus' => $this->moduleManager->getModuleMenus('quality'),
            ],
            'capas' => $capas,
            'filters' => $filters,
        ]);
    }

    public function show(Capa $capa): Response
    {
        $capa->load(['deviation', 'initiator', 'pic', 'actions.pic', 'actions.verifiedBy']);
        $users = User::select('id', 'name', 'email', 'department_id')->get();

        return Inertia::render('Modules/Quality/Capas/Show', [
            'module' => [
                'name' => 'Quality Management System',
                'slug' => 'quality',
                'color_theme' => 'teal',
                'menus' => $this->moduleManager->getModuleMenus('quality'),
            ],
            'capa' => $capa,
            'users' => $users,
        ]);
    }

    public function storeAction(Request $request, Capa $capa): RedirectResponse
    {
        $validated = $request->validate([
            'tindakan' => ['required', 'string'],
            'jenis_tindakan' => ['required', 'in:korektif,preventif'],
            'department' => ['required', 'string'],
            'pic_id' => ['nullable', 'exists:users,id'],
            'deadline' => ['required', 'date'],
        ]);

        $this->capaService->addAction($capa, $validated);

        return redirect()->back()->with('success', 'Tindakan perbaikan/pencegahan berhasil ditambahkan.');
    }

    public function submitProof(Request $request, CapaAction $action): RedirectResponse
    {
        $validated = $request->validate([
            'bukti_file_path' => ['required', 'string'],
            'catatan_pelaksanaan' => ['nullable', 'string'],
        ]);

        $this->capaService->submitActionProof(
            $action,
            $validated['bukti_file_path'],
            $validated['catatan_pelaksanaan'] ?? null,
            $request->user()
        );

        return redirect()->back()->with('success', 'Bukti pelaksanaan tindakan berhasil diserahkan.');
    }

    public function verify(Request $request, Capa $capa): RedirectResponse
    {
        $validated = $request->validate([
            'decision' => ['required', 'in:CLOSE,REJECTED'],
            'verification_notes' => ['required', 'string'],
        ]);

        $this->capaService->verifyCapa(
            $capa,
            $validated['decision'],
            $validated['verification_notes'],
            $request->user()
        );

        $msg = $validated['decision'] === 'CLOSE'
            ? 'CAPA berhasil diverifikasi dan ditutup (CLOSE).'
            : 'CAPA dikembalikan ke pelaksana untuk revisi.';

        return redirect()->back()->with('success', $msg);
    }

    public function printPdf(Capa $capa): HttpResponse
    {
        $capa->load(['deviation', 'initiator', 'pic', 'actions.pic', 'actions.verifiedBy']);

        $pdf = Pdf::loadView('pdf.quality_capa', [
            'capa' => $capa,
            'companyName' => 'PT HerbaTech Indo Utama',
            'printedAt' => now()->format('d/m/Y H:i:s'),
        ]);

        $safeName = str_replace(['/', '\\'], '-', $capa->capa_number);

        return $pdf->stream("Form-CAPA-{$safeName}.pdf");
    }
}
