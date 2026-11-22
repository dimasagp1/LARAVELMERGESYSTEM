<?php

namespace App\Modules\GeneralAffair\Controllers;

use App\Core\Models\Department;
use App\Core\Models\Uom;
use App\Core\Services\ModuleManagerService;
use App\Http\Controllers\Controller;
use App\Models\User;
use App\Modules\GeneralAffair\Models\AtkCategory;
use App\Modules\GeneralAffair\Models\AtkItem;
use App\Modules\GeneralAffair\Models\AtkRequest;
use App\Modules\GeneralAffair\Services\AtkRequestService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response as HttpResponse;
use Inertia\Inertia;
use Inertia\Response;

class AtkRequestController extends Controller
{
    public function __construct(
        protected AtkRequestService $atkService,
        protected ModuleManagerService $moduleManager
    ) {}

    /**
     * Display list of ATK & RT requests.
     */
    public function index(Request $request): Response
    {
        $filters = $request->only(['search', 'status', 'type', 'department_id']);
        $requests = $this->atkService->getRequests($filters, 15);
        $departments = Department::where('is_active', true)->get(['id', 'name']);

        return Inertia::render('Modules/GeneralAffair/Atk/Index', [
            'module' => [
                'name' => 'General Affair (FPUGA)',
                'slug' => 'general-affair',
                'color_theme' => 'amber',
                'menus' => $this->moduleManager->getModuleMenus('general-affair'),
            ],
            'requests' => $requests,
            'filters' => $filters,
            'departments' => $departments,
            'stats' => [
                'total' => AtkRequest::count(),
                'menunggu_manager' => AtkRequest::where('status', 'Menunggu Persetujuan Manager')->count(),
                'menunggu_admin' => AtkRequest::where('status', 'Menunggu Admin GA')->count(),
                'disetujui' => AtkRequest::where('status', 'Disetujui Admin GA')->count(),
                'selesai' => AtkRequest::where('status', 'Selesai')->count(),
            ],
        ]);
    }

    /**
     * Show form for new ATK/RT request.
     */
    public function create(): Response
    {
        $departments = Department::where('is_active', true)->get(['id', 'name']);
        $categories = AtkCategory::with('items.uom')->get();
        $items = AtkItem::with('uom')->where('is_active', true)->get();
        $uoms = Uom::all(['id', 'code', 'name']);

        return Inertia::render('Modules/GeneralAffair/Atk/Form', [
            'module' => [
                'name' => 'General Affair (FPUGA)',
                'slug' => 'general-affair',
                'color_theme' => 'amber',
                'menus' => $this->moduleManager->getModuleMenus('general-affair'),
            ],
            'departments' => $departments,
            'categories' => $categories,
            'items' => $items,
            'uoms' => $uoms,
        ]);
    }

    /**
     * Store new ATK/RT request.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'type' => 'required|in:ATK,RT',
            'department_id' => 'required|exists:departments,id',
            'alasan_pengajuan' => 'required|string|min:5',
            'tanggal_pengajuan' => 'nullable|date',
            'items' => 'required|array|min:1',
            'items.*.atk_item_id' => 'nullable|exists:ga_atk_items,id',
            'items.*.custom_item_name' => 'nullable|string',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.uom_id' => 'nullable|exists:uoms,id',
            'custom_attributes' => 'nullable|array',
        ]);

        $user = $request->user() ?? User::where('email', 'admin@herbatech.com')->first();
        $atkRequest = $this->atkService->createRequest($validated, $user);

        return redirect()->route('general-affair.atk.show', $atkRequest->id)
            ->with('success', "Permintaan {$atkRequest->type} {$atkRequest->request_number} berhasil diajukan.");
    }

    /**
     * Show ATK/RT request detail.
     */
    public function show(int $id): Response
    {
        $atkRequest = AtkRequest::with([
            'user',
            'department',
            'items.atkItem',
            'items.uom',
            'managerApprover',
            'adminApprover',
            'revisions',
            'parent',
        ])->findOrFail($id);

        return Inertia::render('Modules/GeneralAffair/Atk/Show', [
            'module' => [
                'name' => 'General Affair (FPUGA)',
                'slug' => 'general-affair',
                'color_theme' => 'amber',
                'menus' => $this->moduleManager->getModuleMenus('general-affair'),
            ],
            'request' => $atkRequest,
        ]);
    }

    /**
     * Manager approves, rejects, or requests revision.
     */
    public function approveManager(int $id, Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'action' => 'required|in:approve,reject,revision',
            'notes' => 'nullable|string',
            'items' => 'nullable|array',
            'items.*.id' => 'required_with:items|exists:ga_atk_request_items,id',
            'items.*.approved_quantity' => 'required_with:items|integer|min:0',
            'items.*.rejection_note' => 'nullable|string',
        ]);

        $atkRequest = AtkRequest::findOrFail($id);
        $manager = $request->user() ?? User::where('email', 'admin@herbatech.com')->first();

        $this->atkService->approveByManager($atkRequest, $manager, $validated);

        return back()->with('success', "Keputusan manager untuk {$atkRequest->request_number} berhasil disimpan.");
    }

    /**
     * Admin GA approves the request.
     */
    public function approveAdmin(int $id, Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'notes' => 'nullable|string',
        ]);

        $atkRequest = AtkRequest::findOrFail($id);
        $admin = $request->user() ?? User::where('email', 'admin@herbatech.com')->first();

        $this->atkService->approveByGaAdmin($atkRequest, $admin, $validated['notes'] ?? null);

        return back()->with('success', "Permintaan {$atkRequest->request_number} telah disetujui Admin GA.");
    }

    /**
     * Create revision for the request (spawns -R1).
     */
    public function revision(int $id, Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'alasan_pengajuan' => 'required|string|min:5',
            'items' => 'required|array|min:1',
            'items.*.atk_item_id' => 'nullable|exists:ga_atk_items,id',
            'items.*.custom_item_name' => 'nullable|string',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.uom_id' => 'nullable|exists:uoms,id',
        ]);

        $original = AtkRequest::findOrFail($id);
        $user = $request->user() ?? User::where('email', 'admin@herbatech.com')->first();

        $revised = $this->atkService->createRevision($original, $validated, $user);

        return redirect()->route('general-affair.atk.show', $revised->id)
            ->with('success', "Revisi {$revised->request_number} berhasil dibuat dan diajukan.");
    }

    /**
     * Handover / distribution of requested items.
     */
    public function handover(int $id, Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'receiver_name' => 'required|string|min:2',
            'receiver_signature' => 'nullable|string',
        ]);

        $atkRequest = AtkRequest::with('items')->findOrFail($id);
        $actor = $request->user() ?? User::where('email', 'admin@herbatech.com')->first();

        $this->atkService->handover($atkRequest, $actor, $validated);

        return back()->with('success', "Penyerahan barang untuk {$atkRequest->request_number} selesai.");
    }

    /**
     * Generate PDF for ATK/RT Request.
     */
    public function printPdf(int $id): HttpResponse
    {
        $atkRequest = AtkRequest::with([
            'user',
            'department',
            'items.atkItem',
            'items.uom',
            'managerApprover',
            'adminApprover',
        ])->findOrFail($id);

        $pdf = Pdf::loadView('pdf.ga_atk_request', [
            'request' => $atkRequest,
            'companyName' => 'PT HerbaTech Indo Utama',
            'printedAt' => now()->format('d/m/Y H:i:s'),
        ]);

        $cleanNumber = str_replace(['/', '\\'], '-', $atkRequest->request_number);

        return $pdf->stream("Form-Permintaan-{$cleanNumber}.pdf");
    }
}
