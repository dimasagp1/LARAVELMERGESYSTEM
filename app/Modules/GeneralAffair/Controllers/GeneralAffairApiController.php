<?php

namespace App\Modules\GeneralAffair\Controllers;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Modules\GeneralAffair\Models\CleaningCheckpoint;
use App\Modules\GeneralAffair\Models\SecurityCheckpoint;
use App\Modules\GeneralAffair\Services\GeneralAffairDashboardService;
use App\Modules\GeneralAffair\Services\PatrolService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class GeneralAffairApiController extends Controller
{
    public function __construct(
        protected GeneralAffairDashboardService $dashboardService,
        protected PatrolService $patrolService
    ) {}

    /**
     * Get live dashboard metrics for General Affair.
     */
    public function metrics(): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => $this->dashboardService->getDashboardMetrics(),
        ]);
    }

    /**
     * Verify QR token for security or cleaning checkpoints.
     */
    public function verifyQr(Request $request): JsonResponse
    {
        $token = $request->query('token');
        if (! $token) {
            return response()->json(['success' => false, 'message' => 'Token QR diperlukan.'], 400);
        }

        $secCheckpoint = SecurityCheckpoint::with('plant')->where('qr_token', $token)->first();
        if ($secCheckpoint) {
            return response()->json([
                'success' => true,
                'type' => 'security',
                'checkpoint' => $secCheckpoint,
            ]);
        }

        $cleanCheckpoint = CleaningCheckpoint::with('plant')->where('qr_token', $token)->first();
        if ($cleanCheckpoint) {
            return response()->json([
                'success' => true,
                'type' => 'cleaning',
                'checkpoint' => $cleanCheckpoint,
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'QR Token tidak terdaftar di sistem checkpoint patroli.',
        ], 404);
    }

    /**
     * Mobile/API endpoint for security scan.
     */
    public function scanSecurity(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'qr_token' => 'nullable|string',
            'checkpoint_id' => 'nullable|exists:ga_security_checkpoints,id',
            'duration_seconds' => 'nullable|integer|min:0',
            'is_anomaly' => 'nullable|boolean',
            'anomaly_notes' => 'nullable|string',
            'evidence_photo_path' => 'nullable|string',
        ]);

        $user = $request->user() ?? User::where('email', 'admin@herbatech.com')->first();
        $log = $this->patrolService->logSecurityScan($validated, $user);

        return response()->json([
            'success' => true,
            'message' => 'Scan checkpoint keamanan berhasil.',
            'log' => $log->load('checkpoint'),
        ]);
    }

    /**
     * Mobile/API endpoint for cleaning scan.
     */
    public function scanCleaning(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'qr_token' => 'nullable|string',
            'checkpoint_id' => 'nullable|exists:ga_cleaning_checkpoints,id',
            'is_sanitized' => 'nullable|boolean',
            'checklist_json' => 'nullable|array',
            'notes' => 'nullable|string',
            'photo_path' => 'nullable|string',
        ]);

        $user = $request->user() ?? User::where('email', 'admin@herbatech.com')->first();
        $log = $this->patrolService->logCleaningScan($validated, $user);

        return response()->json([
            'success' => true,
            'message' => 'Pemeriksaan sanitasi berhasil disimpan.',
            'log' => $log->load('checkpoint'),
        ]);
    }
}
