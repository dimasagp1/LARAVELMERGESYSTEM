<?php

namespace App\Modules\GeneralAffair\Services;

use App\Core\Models\Plant;
use App\Modules\GeneralAffair\Models\AtkRequest;
use App\Modules\GeneralAffair\Models\FpuRequest;
use App\Modules\GeneralAffair\Models\GaAsset;
use App\Modules\GeneralAffair\Models\SecurityCheckpointLog;
use App\Modules\GeneralAffair\Models\WorkOrder;
use Illuminate\Support\Carbon;

class GeneralAffairDashboardService
{
    public function __construct(
        protected PatrolService $patrolService
    ) {}

    /**
     * Get aggregate metrics for the General Affair Overview Dashboard.
     *
     * @return array<string, mixed>
     */
    public function getDashboardMetrics(): array
    {
        // 1. KPIs
        $openWOs = WorkOrder::whereNotIn('status', ['Selesai', 'Ditolak'])->count();
        $pendingAtk = AtkRequest::whereIn('status', ['Menunggu Persetujuan Manager', 'Menunggu Admin GA', 'Revisi Diminta'])->count();

        $activeAssets = GaAsset::where('condition', '!=', 'DISPOSED')->get();
        $totalAssetValueNumber = $activeAssets->sum('book_value');
        $totalAssetValue = 'Rp '.number_format($totalAssetValueNumber, 0, ',', '.');

        $today = Carbon::today();
        $securityAnomalies = SecurityCheckpointLog::whereDate('scanned_at', $today)
            ->where('is_anomaly', true)
            ->count();

        $kpis = [
            'open_work_orders' => $openWOs,
            'pending_atk_requests' => $pendingAtk,
            'total_asset_value' => $totalAssetValue,
            'security_anomalies' => $securityAnomalies,
        ];

        // 2. WO Status Distribution
        $woStatusCounts = WorkOrder::selectRaw('status, count(*) as count')
            ->groupBy('status')
            ->pluck('count', 'status')
            ->toArray();

        // 3. WO Priority Distribution
        $woPriorityCounts = WorkOrder::selectRaw('priority, count(*) as count')
            ->groupBy('priority')
            ->pluck('count', 'priority')
            ->toArray();

        // 4. Asset Valuation by Plant
        $plants = Plant::where('is_active', true)->get();
        $assetsByPlant = $plants->map(function ($plant) {
            $plantAssets = GaAsset::where('plant_id', $plant->id)
                ->where('condition', '!=', 'DISPOSED')
                ->get();

            return [
                'plant_id' => $plant->id,
                'plant_name' => $plant->name,
                'asset_count' => $plantAssets->count(),
                'total_acquisition' => (float) $plantAssets->sum('purchase_cost'),
                'total_book_value' => (float) $plantAssets->sum('book_value'),
            ];
        });

        // 5. Patrol Summary
        $patrolSummary = $this->patrolService->getPatrolSummary();

        // 6. Pending Approvals Queue
        $pendingApprovals = [];

        // Add pending ATK requests
        $atkPending = AtkRequest::with(['user', 'department', 'items'])
            ->whereIn('status', ['Menunggu Persetujuan Manager', 'Menunggu Admin GA'])
            ->latest('id')
            ->limit(5)
            ->get();

        foreach ($atkPending as $atk) {
            $pendingApprovals[] = [
                'id' => $atk->id,
                'title' => "Permintaan {$atk->type}: {$atk->request_number}",
                'description' => ($atk->department?->name ?? 'Dept').' - '.($atk->alasan_pengajuan ?? 'Pengajuan ATK/RT'),
                'amount' => $atk->items->count().' item barang',
                'requester' => $atk->user?->name ?? 'Karyawan',
                'urgency' => 'normal',
                'date' => $atk->created_at?->diffForHumans() ?? 'Baru saja',
                'url' => "/general-affair/atk/{$atk->id}",
            ];
        }

        // Add pending FPU approvals
        $fpuPending = FpuRequest::with(['workOrder.user', 'workOrder.department', 'materials'])
            ->whereIn('status', ['DRAFT_FPU', 'SUBMITTED'])
            ->latest('id')
            ->limit(5)
            ->get();

        foreach ($fpuPending as $fpu) {
            $pendingApprovals[] = [
                'id' => $fpu->id,
                'title' => "Approval FPU Material: {$fpu->fpu_number}",
                'description' => "WO: {$fpu->workOrder?->wo_number} - Kebutuhan Material Perbaikan",
                'amount' => 'Rp '.number_format($fpu->total_estimated_cost, 0, ',', '.'),
                'requester' => $fpu->workOrder?->user?->name ?? 'Teknisi FPU',
                'urgency' => 'high',
                'date' => $fpu->created_at?->diffForHumans() ?? 'Baru saja',
                'url' => "/general-affair/work-orders/{$fpu->work_order_id}",
            ];
        }

        // 7. Recent Activities
        $recentActivities = [
            [
                'user' => 'Officer Keamanan',
                'action' => 'Patroli Checkpoint Sukoharjo selesai dieksekusi (Kepatuhan '.$patrolSummary['security']['compliance_percent'].'%)',
                'module' => 'Keamanan',
                'theme' => 'amber',
                'time' => '15 menit lalu',
            ],
            [
                'user' => 'Tim FPU & Maintenance',
                'action' => 'Penyelesaian perbaikan & Form D BAST diserahkan kepada pemohon',
                'module' => 'Work Order',
                'theme' => 'emerald',
                'time' => '1 jam lalu',
            ],
            [
                'user' => 'Admin GA',
                'action' => 'Pemberian ATK batch mingguan kepada Departemen Produksi & R&D',
                'module' => 'ATK/RT',
                'theme' => 'sky',
                'time' => '3 jam lalu',
            ],
        ];

        return [
            'kpis' => $kpis,
            'woStatusCounts' => $woStatusCounts,
            'woPriorityCounts' => $woPriorityCounts,
            'assetsByPlant' => $assetsByPlant,
            'patrolSummary' => $patrolSummary,
            'pendingApprovals' => $pendingApprovals,
            'recentActivities' => $recentActivities,
        ];
    }
}
