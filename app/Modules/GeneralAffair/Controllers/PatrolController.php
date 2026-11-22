<?php

namespace App\Modules\GeneralAffair\Controllers;

use App\Core\Models\Plant;
use App\Core\Services\ModuleManagerService;
use App\Http\Controllers\Controller;
use App\Models\User;
use App\Modules\GeneralAffair\Services\PatrolService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class PatrolController extends Controller
{
    public function __construct(
        protected PatrolService $patrolService,
        protected ModuleManagerService $moduleManager
    ) {}

    /**
     * Security Patrol & Checkpoints Page.
     */
    public function securityIndex(Request $request): Response
    {
        $plantId = $request->query('plant_id') ? (int) $request->query('plant_id') : null;
        $filters = $request->only(['plant_id', 'is_anomaly', 'status', 'date']);

        $checkpoints = $this->patrolService->getSecurityCheckpoints($plantId);
        $logs = $this->patrolService->getSecurityLogs($filters, 15);
        $summary = $this->patrolService->getPatrolSummary($plantId);
        $plants = Plant::where('is_active', true)->get(['id', 'name', 'code']);

        return Inertia::render('Modules/GeneralAffair/Patrols/Security', [
            'module' => [
                'name' => 'General Affair (FPUGA)',
                'slug' => 'general-affair',
                'color_theme' => 'amber',
                'menus' => $this->moduleManager->getModuleMenus('general-affair'),
            ],
            'checkpoints' => $checkpoints,
            'logs' => $logs,
            'summary' => $summary['security'],
            'plants' => $plants,
            'selectedPlantId' => $plantId,
            'filters' => $filters,
        ]);
    }

    /**
     * Log Security Scan.
     */
    public function securityScan(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'qr_token' => 'nullable|string',
            'checkpoint_id' => 'nullable|exists:ga_security_checkpoints,id',
            'duration_seconds' => 'nullable|integer|min:0',
            'is_anomaly' => 'nullable|boolean',
            'anomaly_notes' => 'nullable|string',
            'evidence_photo_path' => 'nullable|string',
        ]);

        $officer = $request->user() ?? User::where('email', 'admin@herbatech.com')->first();
        $this->patrolService->logSecurityScan($validated, $officer);

        return back()->with('success', 'Scan checkpoint keamanan berhasil dicatat.');
    }

    /**
     * Cleaning & Sanitasi Patrol Page.
     */
    public function cleaningIndex(Request $request): Response
    {
        $plantId = $request->query('plant_id') ? (int) $request->query('plant_id') : null;
        $filters = $request->only(['plant_id', 'is_sanitized', 'date']);

        $checkpoints = $this->patrolService->getCleaningCheckpoints($plantId);
        $logs = $this->patrolService->getCleaningLogs($filters, 15);
        $summary = $this->patrolService->getPatrolSummary($plantId);
        $plants = Plant::where('is_active', true)->get(['id', 'name', 'code']);

        return Inertia::render('Modules/GeneralAffair/Patrols/Cleaning', [
            'module' => [
                'name' => 'General Affair (FPUGA)',
                'slug' => 'general-affair',
                'color_theme' => 'amber',
                'menus' => $this->moduleManager->getModuleMenus('general-affair'),
            ],
            'checkpoints' => $checkpoints,
            'logs' => $logs,
            'summary' => $summary['cleaning'],
            'plants' => $plants,
            'selectedPlantId' => $plantId,
            'filters' => $filters,
        ]);
    }

    /**
     * Log Cleaning Scan.
     */
    public function cleaningScan(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'qr_token' => 'nullable|string',
            'checkpoint_id' => 'nullable|exists:ga_cleaning_checkpoints,id',
            'is_sanitized' => 'nullable|boolean',
            'checklist_json' => 'nullable|array',
            'notes' => 'nullable|string',
            'photo_path' => 'nullable|string',
        ]);

        $cleaner = $request->user() ?? User::where('email', 'admin@herbatech.com')->first();
        $this->patrolService->logCleaningScan($validated, $cleaner);

        return back()->with('success', 'Pemeriksaan sanitasi kebersihan berhasil dicatat.');
    }
}
