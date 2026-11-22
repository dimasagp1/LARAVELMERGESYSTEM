<?php

namespace App\Modules\Rnd\Services;

use App\Core\Models\Product;
use App\Models\User;
use App\Modules\Rnd\Models\Formula;
use App\Modules\Rnd\Models\NieApproval;
use App\Modules\Rnd\Models\NpdProposal;
use App\Modules\Rnd\Models\PreformulationStudy;
use App\Modules\Rnd\Models\Prf;
use App\Modules\Rnd\Models\SampleEvaluation;
use App\Modules\Rnd\Models\StabilityTest;
use App\Modules\Rnd\Models\TrialPm;
use App\Modules\Rnd\Models\TrialRm;
use Spatie\Activitylog\Models\Activity;

class RndDashboardService
{
    /**
     * Get aggregated R&D dashboard summary metrics and lists.
     *
     * @return array<string, mixed>
     */
    public function getDashboardData(?User $user = null): array
    {
        // 1. KPI Counts
        $totalPrf = Prf::count();
        $totalNpd = NpdProposal::count();
        $totalFormula = Formula::count();
        $totalTrialRm = TrialRm::count();
        $totalTrialPm = TrialPm::count();
        $totalNie = NieApproval::count();

        $totalItems = $totalPrf + $totalNpd + $totalFormula + $totalTrialRm + $totalTrialPm + $totalNie;

        $approvedFormula = Formula::where('status', 'approved')->count();
        $approvedTrialRm = TrialRm::where('status', 'approved')->count();
        $approvedTrialPm = TrialPm::where('status', 'approved')->count();
        $completedNpd = NpdProposal::where('status', 'completed')->count();
        $activeNie = NieApproval::where('status', 'active')->count();

        $approvedCompleted = $approvedFormula + $approvedTrialRm + $approvedTrialPm + $completedNpd + $activeNie;
        $pipelinePercentage = $totalItems > 0 ? round(($approvedCompleted / $totalItems) * 100, 1) : 0;

        // Pending approvals count
        $pendingOmFormulas = Formula::where('status', 'pending_stage_1')->count();
        $pendingGmFormulas = Formula::where('status', 'pending_stage_2')->count();
        $pendingTrialPm = TrialPm::where('status', 'pending_approval')->count();
        $totalPending = $pendingOmFormulas + $pendingGmFormulas + $pendingTrialPm;

        // 2. Pending Approvals Queue
        $pendingQueue = [];
        $pendingStage1 = Formula::with(['product', 'creator'])
            ->where('status', 'pending_stage_1')
            ->latest()
            ->take(5)
            ->get();

        foreach ($pendingStage1 as $f) {
            $pendingQueue[] = [
                'id' => $f->id,
                'type' => 'Formula (Tahap 1)',
                'code' => $f->code,
                'title' => "Formulasi RM: {$f->code} ({$f->product?->name})",
                'description' => "Dosis {$f->target_serving_size_g}g ({$f->servings_per_pack} {$f->serving_unit}/pack) &bull; HPP: Rp ".number_format((float) $f->total_cost_per_pack, 0, ',', '.'),
                'amount' => 'HPP: Rp '.number_format((float) $f->total_cost_per_pack, 0, ',', '.'),
                'requester' => $f->creator?->name ?? 'Staff Formulator',
                'stage' => 'Operational Manager',
                'status' => 'pending_stage_1',
                'date' => $f->created_at->diffForHumans(),
            ];
        }

        $pendingStage2 = Formula::with(['product', 'creator'])
            ->where('status', 'pending_stage_2')
            ->latest()
            ->take(5)
            ->get();

        foreach ($pendingStage2 as $f) {
            $pendingQueue[] = [
                'id' => $f->id,
                'type' => 'Formula (Tahap 2 Final)',
                'code' => $f->code,
                'title' => "Pengesahan Formula: {$f->code} ({$f->product?->name})",
                'description' => 'Telah disetujui OM. Menunggu tandatangan General Manager.',
                'amount' => 'HPP: Rp '.number_format((float) $f->total_cost_per_pack, 0, ',', '.'),
                'requester' => $f->creator?->name ?? 'Staff Formulator',
                'stage' => 'General Manager',
                'status' => 'pending_stage_2',
                'date' => $f->created_at->diffForHumans(),
            ];
        }

        $pendingTrialPmItems = TrialPm::with(['product', 'vendor'])
            ->where('status', 'pending_approval')
            ->latest()
            ->take(5)
            ->get();

        foreach ($pendingTrialPmItems as $tp) {
            $pendingQueue[] = [
                'id' => $tp->id,
                'type' => 'Trial PM (Kemasan Mesin)',
                'code' => $tp->code,
                'title' => "Uji Kemasan: {$tp->code} ({$tp->product?->name})",
                'description' => 'Paraf 4 Departemen Lengkap (R&D, QC, Prod, Eng). Menunggu approval akhir OM.',
                'amount' => $tp->vendor?->name ?? 'Vendor Kemasan',
                'requester' => 'Packdev Engineer',
                'stage' => 'Operational Manager',
                'status' => 'pending_approval',
                'date' => $tp->created_at->diffForHumans(),
            ];
        }

        // 3. Central Product Hub Progress Cards
        $productCards = Product::where('type', 'fg')
            ->with([
                'uom',
            ])
            ->latest()
            ->take(6)
            ->get()
            ->map(function ($product) {
                $hasPrf = Prf::where('product_id', $product->id)->exists();
                $npd = NpdProposal::where('product_id', $product->id)->first();
                $formula = Formula::where('product_id', $product->id)->latest()->first();
                $trialRm = TrialRm::where('product_id', $product->id)->latest()->first();
                $trialPm = TrialPm::where('product_id', $product->id)->latest()->first();
                $sensory = SampleEvaluation::where('product_id', $product->id)->latest()->first();
                $stability = StabilityTest::where('product_id', $product->id)->latest()->first();
                $nie = NieApproval::where('product_id', $product->id)->first();

                // Compute completed steps out of 8
                $completedSteps = 0;
                if ($hasPrf) {
                    $completedSteps++;
                }
                if ($npd) {
                    $completedSteps++;
                }
                if ($formula && $formula->status === 'approved') {
                    $completedSteps++;
                }
                if ($trialRm && $trialRm->status === 'approved') {
                    $completedSteps++;
                }
                if ($trialPm && $trialPm->status === 'approved') {
                    $completedSteps++;
                }
                if ($sensory) {
                    $completedSteps++;
                }
                if ($stability) {
                    $completedSteps++;
                }
                if ($nie) {
                    $completedSteps++;
                }

                return [
                    'id' => $product->id,
                    'sku' => $product->sku,
                    'name' => $product->name,
                    'category' => $product->category,
                    'selling_price' => $product->selling_price,
                    'stages' => [
                        'prf' => $hasPrf,
                        'npd' => $npd !== null,
                        'formula' => $formula?->status ?? 'none',
                        'formula_version' => $formula?->version ?? 1,
                        'trial_rm' => $trialRm?->decision ?? 'none',
                        'trial_pm' => $trialPm?->status ?? 'none',
                        'sensory_score' => $sensory?->overall_score ?? null,
                        'stability' => $stability?->status ?? 'none',
                        'nie' => $nie?->nie_number ?? null,
                    ],
                    'progress_pct' => round(($completedSteps / 8) * 100),
                ];
            });

        // 4. Audit Log Feed
        $activities = Activity::where('log_name', 'like', 'rnd_%')
            ->latest()
            ->take(8)
            ->get()
            ->map(function ($act) {
                return [
                    'id' => $act->id,
                    'user' => $act->causer?->name ?? 'System R&D',
                    'action' => $act->description ?? 'Mutasi data dokumen',
                    'module' => strtoupper(str_replace('rnd_', '', (string) $act->log_name)),
                    'theme' => 'cyan',
                    'time' => $act->created_at->diffForHumans(),
                ];
            });

        return [
            'kpis' => [
                'total_items' => $totalItems,
                'approved_completed' => $approvedCompleted,
                'pipeline_progress_pct' => $pipelinePercentage,
                'pending_approval' => $totalPending,
                'active_npd_projects' => NpdProposal::where('status', 'in_progress')->count(),
                'nie_issued_ytd' => $totalNie,
            ],
            'pendingQueue' => $pendingQueue,
            'productCards' => $productCards,
            'recentActivities' => $activities,
            'charts' => [
                'pipeline_stages' => [
                    'categories' => [
                        '1. PRF & Proposal',
                        '2. Preformulasi & QbD',
                        '3. Formulasi RM 100%',
                        '4. Trial Lab & Auto-Clone',
                        '5. Trial PM 4-Dept',
                        '6. Uji Sensori',
                        '7. Stabilitas',
                        '8. BPOM & NIE',
                    ],
                    'data' => [
                        $totalPrf,
                        PreformulationStudy::count(),
                        $totalFormula,
                        $totalTrialRm,
                        $totalTrialPm,
                        SampleEvaluation::count(),
                        StabilityTest::count(),
                        $totalNie,
                    ],
                ],
                'formula_types' => [
                    'labels' => ['Produk Baru', 'Revisi Resep', 'Substitusi Bahan'],
                    'series' => [
                        Formula::where('formula_type', 'new_product')->count() ?: 5,
                        Formula::where('formula_type', 'existing')->count() ?: 3,
                        Formula::where('formula_type', 'substitution')->count() ?: 2,
                    ],
                ],
            ],
        ];
    }
}
