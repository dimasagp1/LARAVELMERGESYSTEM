<?php

namespace App\Modules\Quality\Services;

use App\Modules\Quality\Models\Capa;
use App\Modules\Quality\Models\CapaAction;
use App\Modules\Quality\Models\ChangeRequest;
use App\Modules\Quality\Models\Deviation;
use App\Modules\Quality\Models\MasterDocument;
use Illuminate\Support\Carbon;

class QualityDashboardService
{
    /**
     * Get aggregate metrics for the Quality Overview Dashboard.
     *
     * @return array<string, mixed>
     */
    public function getDashboardMetrics(): array
    {
        $today = Carbon::today()->toDateString();

        // 1. Core KPIs
        $activeDocsCount = MasterDocument::where('status', 'Active')->count();
        $openCrCount = ChangeRequest::whereIn('status', ['OPEN', 'IN REVIEW', 'IN PROGRESS'])->count();
        $openDeviationsCount = Deviation::whereIn('status', ['OPEN', 'IN REVIEW'])->count();
        $activeCapaCount = Capa::whereIn('status', ['DRAFT', 'IN PROGRESS', 'APPROVED'])->count();

        $overdueCapaActions = CapaAction::where('status', 'PENDING')
            ->where('deadline', '<', $today)
            ->count();

        $avgRpn = (int) round(ChangeRequest::where('type', 'CRA')->avg('rpn') ?? 0);

        // 2. Deviations by Severity
        $deviationsBySeverity = [
            'kritis' => Deviation::where('identifikasi_penyimpangan', 'Kritis')->count(),
            'mayor' => Deviation::where('identifikasi_penyimpangan', 'Mayor')->count(),
            'minor' => Deviation::where('identifikasi_penyimpangan', 'Minor')->count(),
        ];

        // 3. CAPA Status Breakdown
        $capaStatus = [
            'draft' => Capa::where('status', 'DRAFT')->count(),
            'in_progress' => Capa::where('status', 'IN PROGRESS')->count(),
            'approved' => Capa::where('status', 'APPROVED')->count(),
            'close' => Capa::where('status', 'CLOSE')->count(),
        ];

        // 4. Documents by Level
        $docsByLevel = [
            'level_1' => MasterDocument::where('level', 1)->count(),
            'level_2' => MasterDocument::where('level', 2)->count(),
            'level_3' => MasterDocument::where('level', 3)->count(),
            'level_4' => MasterDocument::where('level', 4)->count(),
        ];

        // 5. 6M Fishbone Distribution
        $fishboneCounts = [
            'machine' => 0,
            'man' => 0,
            'method' => 0,
            'materials' => 0,
            'milieu' => 0,
            'measurement' => 0,
        ];

        $deviationsWithFishbone = Deviation::whereNotNull('fishbone_6m')->get();
        foreach ($deviationsWithFishbone as $dev) {
            $fb = $dev->fishbone_6m;
            if (is_array($fb)) {
                foreach ($fishboneCounts as $cat => &$count) {
                    if (! empty($fb[$cat]) && is_array($fb[$cat])) {
                        $count += count($fb[$cat]);
                    }
                }
            }
        }

        // 6. Pending Approvals Queue
        $pendingApprovals = [];

        // Change requests needing review or approval
        $pendingCrs = ChangeRequest::whereIn('status', ['OPEN', 'IN REVIEW'])
            ->with(['initiator'])
            ->latest()
            ->take(4)
            ->get();

        foreach ($pendingCrs as $cr) {
            $pendingApprovals[] = [
                'id' => $cr->id,
                'type' => 'Change Request '.$cr->type,
                'title' => $cr->cr_number.' - '.$cr->nama_produk,
                'description' => $cr->usulan_perubahan,
                'amount' => $cr->isCRA() ? "RPN: {$cr->rpn}" : 'Minor CR',
                'requester' => $cr->initiator?->name ?? 'Inisiator',
                'urgency' => ($cr->rpn > 50 || $cr->type === 'CRA') ? 'high' : 'medium',
                'date' => $cr->created_at?->diffForHumans() ?? 'Baru saja',
                'url' => "/quality/change-requests/{$cr->id}",
            ];
        }

        // Deviations needing QA review/decision
        $pendingDeviations = Deviation::whereIn('status', ['OPEN', 'IN REVIEW'])
            ->with(['initiator'])
            ->latest()
            ->take(3)
            ->get();

        foreach ($pendingDeviations as $dev) {
            $pendingApprovals[] = [
                'id' => $dev->id,
                'type' => 'Deviasi '.$dev->identifikasi_penyimpangan,
                'title' => $dev->deviation_number.' - '.$dev->topik,
                'description' => $dev->immediate_action_details ?? $dev->department,
                'amount' => "Bets: {$dev->nomor_bets}",
                'requester' => $dev->initiator?->name ?? 'Pelapor',
                'urgency' => $dev->identifikasi_penyimpangan === 'Kritis' ? 'high' : 'medium',
                'date' => $dev->created_at?->diffForHumans() ?? 'Baru saja',
                'url' => "/quality/deviations/{$dev->id}/investigation",
            ];
        }

        // CAPAs needing QA closing verification
        $pendingCapas = Capa::where('status', 'APPROVED')
            ->with(['initiator'])
            ->latest()
            ->take(3)
            ->get();

        foreach ($pendingCapas as $capa) {
            $pendingApprovals[] = [
                'id' => $capa->id,
                'type' => 'Verifikasi CAPA',
                'title' => $capa->capa_number.' ('.$capa->deviation_number_ref.')',
                'description' => $capa->tindakan_capa,
                'amount' => 'Semua Bukti Siap Verifikasi',
                'requester' => $capa->initiator?->name ?? 'PIC CAPA',
                'urgency' => 'normal',
                'date' => $capa->updated_at?->diffForHumans() ?? 'Baru saja',
                'url' => "/quality/capa/{$capa->id}",
            ];
        }

        // 7. Recent activities
        $recentActivities = [
            [
                'user' => 'Head of QA / APJ',
                'action' => 'Verifikasi Tanda Tangan Digital Rilis Bets CPOB',
                'module' => 'Digital Signature',
                'theme' => 'teal',
                'time' => '1 jam lalu',
            ],
            [
                'user' => 'QA Compliance Specialist',
                'action' => 'Pembaruan Dokumen SOP-QA-008 Revisi 02 (Pengawasan Suhu)',
                'module' => 'Master Dokumen',
                'theme' => 'emerald',
                'time' => '3 jam lalu',
            ],
            [
                'user' => 'Spv QC Pengemasan',
                'action' => 'Pelaporan Deviasi DR/002/2026 Kebocoran Seal Botol Sirup',
                'module' => 'Deviasi',
                'theme' => 'rose',
                'time' => '5 jam lalu',
            ],
            [
                'user' => 'Operational Manager',
                'action' => 'Persetujuan Change Request CRA-2026-0001 (Validasi Granulator)',
                'module' => 'Change Request',
                'theme' => 'amber',
                'time' => '1 hari lalu',
            ],
        ];

        return [
            'kpis' => [
                'active_qms_docs' => $activeDocsCount,
                'open_change_requests' => $openCrCount,
                'open_deviations' => $openDeviationsCount,
                'active_capas' => $activeCapaCount,
                'overdue_capa_actions' => $overdueCapaActions,
                'avg_fmea_rpn' => $avgRpn,
            ],
            'deviationsBySeverity' => $deviationsBySeverity,
            'capaStatus' => $capaStatus,
            'docsByLevel' => $docsByLevel,
            'fishboneCounts' => $fishboneCounts,
            'pendingApprovals' => $pendingApprovals,
            'recentActivities' => $recentActivities,
        ];
    }
}
