<?php

namespace App\Modules\Quality\Controllers;

use App\Core\Services\ModuleManagerService;
use App\Http\Controllers\Controller;
use App\Models\User;
use App\Modules\Quality\Models\Deviation;
use App\Modules\Quality\Services\DeviationInvestigationService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response as HttpResponse;
use Inertia\Inertia;
use Inertia\Response;

class DeviationController extends Controller
{
    public function __construct(
        protected DeviationInvestigationService $deviationService,
        protected ModuleManagerService $moduleManager
    ) {}

    public function index(Request $request): Response
    {
        $filters = $request->only(['search', 'status', 'identifikasi', 'department']);
        $deviations = $this->deviationService->getDeviations($filters);

        return Inertia::render('Modules/Quality/Deviations/Index', [
            'module' => [
                'name' => 'Quality Management System',
                'slug' => 'quality',
                'color_theme' => 'teal',
                'menus' => $this->moduleManager->getModuleMenus('quality'),
            ],
            'deviations' => $deviations,
            'filters' => $filters,
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Modules/Quality/Deviations/Form', [
            'module' => [
                'name' => 'Quality Management System',
                'slug' => 'quality',
                'color_theme' => 'teal',
                'menus' => $this->moduleManager->getModuleMenus('quality'),
            ],
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'topik' => ['required', 'string', 'max:255'],
            'department' => ['required', 'string'],
            'pic' => ['nullable', 'string'],
            'nomor_bets' => ['nullable', 'string'],
            'nomor_alat' => ['nullable', 'string'],
            'nomor_dokumen' => ['nullable', 'string'],
            'objek_terdampak' => ['nullable', 'string'],
            'jenis_penyimpangan' => ['required', 'string'],
            'identifikasi_penyimpangan' => ['required', 'in:Kritis,Mayor,Minor'],
            'tanggal_temuan' => ['required', 'date'],
            'is_other_batch_affected' => ['boolean'],
            'other_batch_details' => ['nullable', 'string'],
            'frekuensi_kejadian' => ['required', 'string'],
            'is_production_stopped' => ['boolean'],
            'immediate_action_details' => ['nullable', 'string'],
            'attachments' => ['nullable', 'array'],
            'action' => ['nullable', 'in:draft,submit'],
        ]);

        $deviation = $this->deviationService->createDeviation($validated, $request->user());

        return redirect()->route('quality.deviations.investigation', $deviation->id)
            ->with('success', "Laporan Deviasi {$deviation->deviation_number} berhasil didaftarkan. Silakan lengkapi analisis investigasi.");
    }

    public function investigation(Deviation $deviation): Response
    {
        $deviation->load(['initiator', 'capa']);
        $users = User::select('id', 'name', 'email')->get();

        return Inertia::render('Modules/Quality/Deviations/Investigation', [
            'module' => [
                'name' => 'Quality Management System',
                'slug' => 'quality',
                'color_theme' => 'teal',
                'menus' => $this->moduleManager->getModuleMenus('quality'),
            ],
            'deviation' => $deviation,
            'users' => $users,
        ]);
    }

    public function updateInvestigation(Request $request, Deviation $deviation): RedirectResponse
    {
        $validated = $request->validate([
            'root_cause' => ['nullable', 'string'],
            'fishbone_6m' => ['nullable', 'array'],
            'five_why' => ['nullable', 'array'],
            'evaluasi_tindakan' => ['nullable', 'array'],
        ]);

        $this->deviationService->updateInvestigation($deviation, $validated, $request->user());

        return redirect()->back()->with('success', 'Hasil analisis investigasi berhasil disimpan.');
    }

    public function assignMengetahui(Request $request, Deviation $deviation): RedirectResponse
    {
        $validated = $request->validate([
            'user_id' => ['required', 'exists:users,id'],
            'role' => ['required', 'string'],
        ]);

        $this->deviationService->assignMengetahui($deviation, (int) $validated['user_id'], $validated['role']);

        return redirect()->back()->with('success', 'Atasan terkait berhasil ditugaskan.');
    }

    public function submitMengetahui(Request $request, Deviation $deviation): RedirectResponse
    {
        $validated = $request->validate([
            'feedback' => ['required', 'string'],
            'signature_path' => ['nullable', 'string'],
        ]);

        $this->deviationService->submitMengetahuiFeedback(
            $deviation,
            $request->user(),
            $validated['feedback'],
            $validated['signature_path'] ?? null
        );

        return redirect()->back()->with('success', 'Tanggapan & Tanda Tangan Atasan berhasil diserahkan.');
    }

    public function decide(Request $request, Deviation $deviation): RedirectResponse
    {
        $validated = $request->validate([
            'decision' => ['required', 'in:APPROVE,REJECT'],
            'notes' => ['nullable', 'string'],
        ]);

        $result = $this->deviationService->decideDeviation(
            $deviation,
            $validated['decision'],
            $validated['notes'] ?? null,
            $request->user()
        );

        if ($validated['decision'] === 'APPROVE') {
            $capaNumber = $result['capa']?->capa_number ?? 'CAPA Baru';

            return redirect()->route('quality.capa.show', $result['capa']->id)
                ->with('success', "Deviasi disetujui! Sistem otomatis membuat {$capaNumber} untuk tindak lanjut perbaikan.");
        }

        return redirect()->back()->with('warning', 'Deviasi ditolak/dikembalikan ke inisiator untuk revisi.');
    }

    public function printPdf(Deviation $deviation): HttpResponse
    {
        $deviation->load(['initiator', 'capa']);

        $pdf = Pdf::loadView('pdf.quality_deviation_investigation', [
            'deviation' => $deviation,
            'companyName' => 'PT HerbaTech Indo Utama',
            'printedAt' => now()->format('d/m/Y H:i:s'),
        ]);

        $safeName = str_replace(['/', '\\'], '-', $deviation->deviation_number);

        return $pdf->stream("Form-Penyelidikan-Ketidaksesuaian-{$safeName}.pdf");
    }
}
