<?php

namespace App\Modules\GeneralAffair\Controllers;

use App\Core\Models\Department;
use App\Core\Models\Location;
use App\Core\Services\ModuleManagerService;
use App\Http\Controllers\Controller;
use App\Models\User;
use App\Modules\GeneralAffair\Models\FpuRequest;
use App\Modules\GeneralAffair\Models\WorkOrder;
use App\Modules\GeneralAffair\Services\WorkOrderService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response as HttpResponse;
use Inertia\Inertia;
use Inertia\Response;

class WorkOrderController extends Controller
{
    public function __construct(
        protected WorkOrderService $woService,
        protected ModuleManagerService $moduleManager
    ) {}

    /**
     * Display work order list with filters.
     */
    public function index(Request $request): Response
    {
        $filters = $request->only(['search', 'status', 'priority', 'category', 'department_id']);
        $workOrders = $this->woService->getWorkOrders($filters, 15);
        $departments = Department::where('is_active', true)->get(['id', 'name']);

        return Inertia::render('Modules/GeneralAffair/WorkOrders/Index', [
            'module' => [
                'name' => 'General Affair (FPUGA)',
                'slug' => 'general-affair',
                'color_theme' => 'amber',
                'menus' => $this->moduleManager->getModuleMenus('general-affair'),
            ],
            'workOrders' => $workOrders,
            'filters' => $filters,
            'departments' => $departments,
            'stats' => [
                'total' => WorkOrder::count(),
                'menunggu_penugasan' => WorkOrder::where('status', 'Menunggu Penugasan')->count(),
                'sedang_dikerjakan' => WorkOrder::whereIn('status', ['Pengecekan Engineer', 'Dalam Pengerjaan'])->count(),
                'menunggu_material' => WorkOrder::where('status', 'Menunggu Pembelian Material')->count(),
                'selesai' => WorkOrder::where('status', 'Selesai')->count(),
            ],
        ]);
    }

    /**
     * Show form for Form A (New Work Order).
     */
    public function create(): Response
    {
        $departments = Department::where('is_active', true)->get(['id', 'name']);
        $locations = Location::where('is_active', true)->get(['id', 'name', 'plant_id', 'department_id']);

        return Inertia::render('Modules/GeneralAffair/WorkOrders/Form', [
            'module' => [
                'name' => 'General Affair (FPUGA)',
                'slug' => 'general-affair',
                'color_theme' => 'amber',
                'menus' => $this->moduleManager->getModuleMenus('general-affair'),
            ],
            'departments' => $departments,
            'locations' => $locations,
            'categories' => ['Gedung & Sipil', 'Mekanikal & Kelistrikan', 'AC & HVAC', 'Pipa & Plumbing', 'Peralatan Kantor', 'Lainnya'],
            'priorities' => ['Rendah', 'Normal', 'Tinggi', 'Darurat'],
        ]);
    }

    /**
     * Store Form A submission.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'complaint_description' => 'required|string|min:5',
            'department_id' => 'required|exists:departments,id',
            'location_id' => 'nullable|exists:locations,id',
            'priority' => 'required|in:Rendah,Normal,Tinggi,Darurat',
            'category' => 'required|string',
            'attachment_path' => 'nullable|string',
            'custom_attributes' => 'nullable|array',
        ]);

        $user = $request->user() ?? User::where('email', 'admin@herbatech.com')->first();
        $wo = $this->woService->createWorkOrder($validated, $user);

        return redirect()->route('general-affair.work-orders.show', $wo->id)
            ->with('success', "Work Order {$wo->wo_number} (Form A) berhasil diajukan.");
    }

    /**
     * Show Work Order details with Form A, B, C, and D flow.
     */
    public function show(int $id): Response
    {
        $wo = WorkOrder::with([
            'user',
            'department',
            'location.plant',
            'assignedEngineer',
            'progresses.user',
            'fpuRequest.materials',
            'fpuRequest.verifiedBy',
            'fpuRequest.managerUser',
            'fpuRequest.gmUser',
            'handover.clientUser',
            'handover.preparedBy',
        ])->findOrFail($id);

        $engineers = User::where('is_active', true)->get(['id', 'name', 'email']);

        return Inertia::render('Modules/GeneralAffair/WorkOrders/Show', [
            'module' => [
                'name' => 'General Affair (FPUGA)',
                'slug' => 'general-affair',
                'color_theme' => 'amber',
                'menus' => $this->moduleManager->getModuleMenus('general-affair'),
            ],
            'workOrder' => $wo,
            'engineers' => $engineers,
        ]);
    }

    /**
     * Triage: Admin GA assigns engineer.
     */
    public function assign(int $id, Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'assigned_engineer_id' => 'required|exists:users,id',
        ]);

        $wo = WorkOrder::findOrFail($id);
        $this->woService->assignEngineer($wo, (int) $validated['assigned_engineer_id']);

        return back()->with('success', "Teknisi berhasil ditugaskan untuk WO {$wo->wo_number}.");
    }

    /**
     * Form B: Engineer check & assessment.
     */
    public function check(int $id, Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'check_result' => 'required|string|min:5',
            'action_plan' => 'required|string|min:5',
            'needs_materials' => 'required|boolean',
            'estimated_cost' => 'nullable|numeric|min:0',
            'target_completion_date' => 'nullable|date',
            'materials' => 'nullable|array',
            'materials.*.material_name' => 'required_with:materials|string',
            'materials.*.quantity' => 'required_with:materials|numeric|min:0.01',
            'materials.*.uom_id' => 'nullable|exists:uoms,id',
            'materials.*.estimated_unit_price' => 'nullable|numeric|min:0',
        ]);

        $wo = WorkOrder::findOrFail($id);
        $engineer = $request->user() ?? User::where('email', 'admin@herbatech.com')->first();

        $this->woService->submitEngineerCheck($wo, [
            'check_result' => $validated['check_result'],
            'engineer_needs' => $validated['action_plan'] ?? null,
            'needs_material' => (bool) ($validated['needs_materials'] ?? false),
            'materials' => $validated['materials'] ?? [],
        ]);

        return back()->with('success', 'Hasil pengecekan teknisi (Form B) berhasil dicatat.');
    }

    /**
     * Record work order execution progress.
     */
    public function progress(int $id, Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'progress_percentage' => 'required|integer|min:1|max:100',
            'notes' => 'required|string|min:3',
            'attachment_path' => 'nullable|string',
        ]);

        $wo = WorkOrder::findOrFail($id);
        $user = $request->user() ?? User::where('email', 'admin@herbatech.com')->first();

        $this->woService->logProgress($wo, (int) $validated['progress_percentage'], $validated['notes'], $validated['attachment_path'] ?? null, $user);

        return back()->with('success', "Progres pengerjaan WO {$wo->wo_number} berhasil diperbarui.");
    }

    /**
     * Form C Approval (FPU Multi-Tier).
     */
    public function approveFpu(int $id, Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'tier' => 'required|in:hrga,manager,gm',
            'decision' => 'required|in:approve,reject',
            'notes' => 'nullable|string',
        ]);

        $fpu = FpuRequest::findOrFail($id);
        $user = $request->user() ?? User::where('email', 'admin@herbatech.com')->first();

        $this->woService->approveFpu($fpu, $validated['tier'], $user, $validated['notes'] ?? null);

        return back()->with('success', "Persetujuan Form C FPU {$fpu->fpu_number} berhasil disimpan.");
    }

    /**
     * Form D: Engineer submits handover (BAST).
     */
    public function handover(int $id, Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'root_cause' => 'nullable|string',
            'action_taken' => 'nullable|string',
            'engineer_signature' => 'nullable|string',
        ]);

        $wo = WorkOrder::findOrFail($id);
        $engineer = $request->user() ?? User::where('email', 'admin@herbatech.com')->first();

        $this->woService->prepareHandover($wo, $engineer);

        return back()->with('success', 'Serah terima Form D (BAST) berhasil diajukan kepada pemohon.');
    }

    /**
     * Form D: Client confirms handover with rating and feedback.
     */
    public function confirmHandover(int $id, Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'rating' => 'required|integer|min:1|max:5',
            'feedback' => 'nullable|string',
            'client_signature' => 'nullable|string',
        ]);

        $wo = WorkOrder::with('handover')->findOrFail($id);
        if (! $wo->handover) {
            $client = $request->user() ?? User::where('email', 'admin@herbatech.com')->first();
            $this->woService->prepareHandover($wo, $client);
            $wo->refresh();
        }

        $this->woService->confirmHandover($wo->handover, (int) $validated['rating'], $validated['feedback'] ?? null, $validated['client_signature'] ?? null);

        return back()->with('success', "Work Order {$wo->wo_number} selesai dan BAST berhasil dikonfirmasi.");
    }

    /**
     * Generate PDF for Work Order (Form A & D) or Form C.
     */
    public function printPdf(int $id, Request $request): HttpResponse
    {
        $wo = WorkOrder::with([
            'user',
            'department',
            'location.plant',
            'assignedEngineer',
            'progresses.user',
            'fpuRequest.materials',
            'handover.clientUser',
            'handover.preparedBy',
        ])->findOrFail($id);

        $type = $request->query('type', 'wo');

        if ($type === 'fpu' && $wo->fpuRequest) {
            $pdf = Pdf::loadView('pdf.ga_fpu', [
                'wo' => $wo,
                'fpu' => $wo->fpuRequest,
                'companyName' => 'PT HerbaTech Indo Utama',
                'printedAt' => now()->format('d/m/Y H:i:s'),
            ]);
            $cleanNumber = str_replace(['/', '\\'], '-', $wo->fpuRequest->fpu_number);

            return $pdf->stream("Form-C-FPU-{$cleanNumber}.pdf");
        }

        $pdf = Pdf::loadView('pdf.ga_work_order', [
            'wo' => $wo,
            'companyName' => 'PT HerbaTech Indo Utama',
            'printedAt' => now()->format('d/m/Y H:i:s'),
        ]);

        $cleanNumber = str_replace(['/', '\\'], '-', $wo->wo_number);

        return $pdf->stream("WorkOrder-{$cleanNumber}.pdf");
    }
}
