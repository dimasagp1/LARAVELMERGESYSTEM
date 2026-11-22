<?php

namespace App\Modules\Quality\Controllers;

use App\Core\Services\ModuleManagerService;
use App\Http\Controllers\Controller;
use App\Models\User;
use App\Modules\Quality\Models\ChangeRequest;
use App\Modules\Quality\Services\ChangeRequestService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response as HttpResponse;
use Inertia\Inertia;
use Inertia\Response;

class ChangeRequestController extends Controller
{
    public function __construct(
        protected ChangeRequestService $changeRequestService,
        protected ModuleManagerService $moduleManager
    ) {}

    public function index(Request $request): Response
    {
        $filters = $request->only(['search', 'type', 'status']);
        $changeRequests = $this->changeRequestService->getChangeRequests($filters);

        return Inertia::render('Modules/Quality/ChangeRequests/Index', [
            'module' => [
                'name' => 'Quality Management System',
                'slug' => 'quality',
                'color_theme' => 'teal',
                'menus' => $this->moduleManager->getModuleMenus('quality'),
            ],
            'changeRequests' => $changeRequests,
            'filters' => $filters,
        ]);
    }

    public function create(): Response
    {
        $users = User::select('id', 'name', 'email', 'department_id')->get();

        return Inertia::render('Modules/Quality/ChangeRequests/Form', [
            'module' => [
                'name' => 'Quality Management System',
                'slug' => 'quality',
                'color_theme' => 'teal',
                'menus' => $this->moduleManager->getModuleMenus('quality'),
            ],
            'users' => $users,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'type' => ['required', 'in:CRA,CRB'],
            'sifat_perubahan' => ['required', 'string'],
            'nama_produk' => ['required', 'string', 'max:255'],
            'awal_sebelum_perubahan' => ['required', 'string'],
            'usulan_perubahan' => ['required', 'string'],
            'alasan_perubahan' => ['nullable', 'string'],
            'target_pelaksanaan' => ['nullable', 'date'],
            'severity' => ['nullable', 'integer', 'between:1,10'],
            'occurrence' => ['nullable', 'integer', 'between:1,10'],
            'detection' => ['nullable', 'integer', 'between:1,10'],
            'risk_mitigation' => ['nullable', 'string'],
            'pic_id' => ['nullable', 'exists:users,id'],
            'action' => ['nullable', 'in:draft,submit'],
        ]);

        $cr = $this->changeRequestService->createChangeRequest($validated, $request->user());

        return redirect()->route('quality.change-requests.show', $cr->id)
            ->with('success', "Usulan perubahan {$cr->cr_number} berhasil didaftarkan.");
    }

    public function show(ChangeRequest $changeRequest): Response
    {
        $changeRequest->load(['initiator', 'pic']);
        $users = User::select('id', 'name', 'email')->get();

        return Inertia::render('Modules/Quality/ChangeRequests/Show', [
            'module' => [
                'name' => 'Quality Management System',
                'slug' => 'quality',
                'color_theme' => 'teal',
                'menus' => $this->moduleManager->getModuleMenus('quality'),
            ],
            'changeRequest' => $changeRequest,
            'users' => $users,
        ]);
    }

    public function assignAssessments(Request $request, ChangeRequest $changeRequest): RedirectResponse
    {
        $validated = $request->validate([
            'assignments' => ['required', 'array', 'min:1'],
            'assignments.*.user_id' => ['required', 'exists:users,id'],
            'assignments.*.department' => ['required', 'string'],
            'assignments.*.pic_name' => ['required', 'string'],
        ]);

        $this->changeRequestService->assignDepartmentAssessments($changeRequest, $validated['assignments']);

        return redirect()->back()->with('success', 'PIC Pengkajian Departemen berhasil ditugaskan.');
    }

    public function submitAssessment(Request $request, ChangeRequest $changeRequest): RedirectResponse
    {
        $validated = $request->validate([
            'risk_analysis' => ['required', 'string'],
            'recommendation' => ['required', 'string'],
            'decision' => ['nullable', 'in:APPROVED,REJECTED'],
        ]);

        $this->changeRequestService->submitDepartmentAssessment(
            $changeRequest,
            $request->user(),
            $validated['risk_analysis'],
            $validated['recommendation'],
            $validated['decision'] ?? 'APPROVED'
        );

        return redirect()->back()->with('success', 'Kajian dampak & risiko departemen berhasil diserahkan.');
    }

    public function approve(Request $request, ChangeRequest $changeRequest): RedirectResponse
    {
        $validated = $request->validate([
            'approval_level' => ['required', 'in:head_of_quality,operational_manager,general_manager'],
            'decision' => ['required', 'in:APPROVED,REJECTED'],
            'notes' => ['nullable', 'string'],
        ]);

        $this->changeRequestService->processApproval(
            $changeRequest,
            $request->user(),
            $validated['approval_level'],
            $validated['decision'],
            $validated['notes'] ?? null
        );

        return redirect()->back()->with('success', 'Keputusan persetujuan berhasil dicatat.');
    }

    public function submitStage2(Request $request, ChangeRequest $changeRequest): RedirectResponse
    {
        $validated = $request->validate([
            'implementation_summary' => ['required', 'string'],
            'proof_files' => ['nullable', 'array'],
        ]);

        $this->changeRequestService->submitStage2($changeRequest, $validated, $request->user());

        return redirect()->back()->with('success', 'Dokumentasi bukti pelaksanaan Stage 2 berhasil diunggah.');
    }

    public function completeStage2(Request $request, ChangeRequest $changeRequest): RedirectResponse
    {
        $validated = $request->validate([
            'notes' => ['required', 'string'],
        ]);

        $this->changeRequestService->verifyAndCompleteStage2($changeRequest, $validated['notes'], $request->user());

        return redirect()->back()->with('success', 'Change Request telah diverifikasi QA dan ditutup (COMPLETE).');
    }

    public function printPdf(ChangeRequest $changeRequest): HttpResponse
    {
        $changeRequest->load(['initiator', 'pic']);

        $pdf = Pdf::loadView('pdf.quality_cr', [
            'cr' => $changeRequest,
            'companyName' => 'PT HerbaTech Indo Utama',
            'printedAt' => now()->format('d/m/Y H:i:s'),
        ]);

        return $pdf->stream("Form-Change-Request-{$changeRequest->cr_number}.pdf");
    }
}
