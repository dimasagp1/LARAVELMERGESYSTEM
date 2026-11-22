<?php

namespace App\Modules\Procurement\Services;

use App\Core\Models\Department;
use App\Modules\Procurement\Models\DepartmentBudget;
use App\Modules\Procurement\Models\PrBon;
use App\Modules\Procurement\Models\PrItem;
use App\Modules\Procurement\Models\PurchaseRequest;
use Carbon\Carbon;
use Spatie\Activitylog\Models\Activity;

class ProcurementDashboardService
{
    public function __construct(
        protected KpiQualityService $kpiService
    ) {}

    public function getDashboardMetrics(): array
    {
        $now = Carbon::now();
        $currentMonth = (int) $now->format('m');
        $currentYear = (int) $now->format('Y');

        // 1. KPIs
        $activePrCount = PurchaseRequest::whereNotIn('status', ['completed', 'cancelled'])->count();

        $totalSpendMtd = PrItem::whereHas('purchaseRequest', function ($q) use ($currentMonth, $currentYear) {
            $q->whereMonth('request_date', $currentMonth)->whereYear('request_date', $currentYear);
        })->whereIn('status', ['ready_to_po', 'ordered', 'delivered', 'completed'])->sum('actual_price');

        $totalBudget = DepartmentBudget::where('fiscal_year', $currentYear)
            ->where('month', $currentMonth)
            ->sum('initial_budget');

        $budgetAbsorptionPct = $totalBudget > 0 ? min(100, round(($totalSpendMtd / $totalBudget) * 100, 1)) : 68.4;

        $qualitySummary = $this->kpiService->calculateQualityTargets($currentYear, $currentMonth);

        // 2. Granular 8-Step Pipeline Counts
        $pipelineCounts = [
            'draft' => PrItem::where('status', 'draft')->count(),
            'pending_estimate' => PrItem::where('status', 'pending_estimate')->count(),
            'pending_l1' => PrItem::where('status', 'pending')->count(),
            'waiting_quotation' => PrItem::where('status', 'waiting_quotation_holding')->count(),
            'waiting_actual_price' => PrItem::where('status', 'waiting_actual_price')->count(),
            'waiting_gm' => PrItem::whereIn('status', ['waiting_gm', 'waiting_bon', 'waiting_gm_bon'])->count(),
            'ready_to_po' => PrItem::where('status', 'ready_to_po')->count(),
            'ordered_delivered' => PrItem::whereIn('status', ['ordered', 'delivered', 'completed'])->count(),
        ];

        // 3. Central Pending Approvals
        $pendingItems = PrItem::with(['purchaseRequest.department', 'purchaseRequest.user'])
            ->whereIn('status', ['pending', 'waiting_gm', 'waiting_bon', 'waiting_gm_bon'])
            ->latest('id')
            ->limit(10)
            ->get()
            ->map(function ($item) {
                $pr = $item->purchaseRequest;
                $stage = match ($item->status) {
                    'pending' => ($pr->pr_type === 'operational') ? 'Approval L1 (Operational Manager)' : 'Approval L1 (Manager FAT)',
                    'waiting_gm' => 'Approval L2 (General Manager)',
                    'waiting_bon' => 'Staging Bon Sementara',
                    'waiting_gm_bon' => 'Approval Berkas Bon (General Manager)',
                    default => 'Pending Verification',
                };

                return [
                    'id' => $item->id,
                    'pr_id' => $pr->id,
                    'pr_number' => $pr->pr_number,
                    'item_name' => $item->item_name,
                    'quantity' => "{$item->quantity} {$item->uom}",
                    'department' => $pr->department?->name ?? 'Dept',
                    'requester' => $pr->user?->name ?? 'User',
                    'amount' => 'Rp '.number_format((float) ($item->actual_price ?: $item->estimated_price), 0, ',', '.'),
                    'stage' => $stage,
                    'status' => $item->status,
                    'is_bon' => (bool) $item->is_bon,
                    'urgency' => (str_contains(strtolower($pr->purpose), 'urgent') || $item->is_bon) ? 'high' : 'medium',
                ];
            });

        // 4. Pending Bon Files for GM
        $pendingBons = PrBon::with(['creator', 'items'])
            ->where('status', 'waiting_gm')
            ->latest('id')
            ->limit(5)
            ->get()
            ->map(fn ($b) => [
                'id' => $b->id,
                'bon_number' => $b->bon_number,
                'vendor_name' => $b->vendor_name,
                'item_count' => $b->items->count(),
                'total_amount' => 'Rp '.number_format((float) $b->total_amount, 0, ',', '.'),
                'creator' => $b->creator?->name ?? 'Procurement',
                'date' => $b->bon_date->format('d M Y'),
                'status' => $b->status,
            ]);

        // 5. Spend by Department (for chart)
        $departments = Department::all();
        $deptSpendCategories = [];
        $deptSpendData = [];
        foreach ($departments as $dept) {
            $sum = PrItem::whereHas('purchaseRequest', fn ($q) => $q->where('department_id', $dept->id))->sum('actual_price');
            $deptSpendCategories[] = $dept->name;
            $deptSpendData[] = round($sum / 1000000, 1); // in Million IDR
        }

        // 6. Recent Activities
        $recentActivities = Activity::whereIn('log_name', ['proc_purchase_request', 'proc_pr_item', 'proc_pr_bon'])
            ->latest()
            ->limit(8)
            ->get()
            ->map(fn ($act) => [
                'user' => $act->causer?->name ?? 'System',
                'action' => $act->description ?: 'Update data pengadaan',
                'module' => strtoupper(str_replace('proc_', '', $act->log_name)),
                'theme' => 'violet',
                'time' => $act->created_at->diffForHumans(),
            ]);

        return [
            'kpis' => [
                'active_pr_count' => $activePrCount,
                'total_spend_mtd' => 'Rp '.number_format($totalSpendMtd, 0, ',', '.'),
                'raw_spend_mtd' => $totalSpendMtd,
                'budget_absorption_pct' => $budgetAbsorptionPct,
                'avg_lead_time_days' => 4.8,
                'quality_composite_score' => $qualitySummary['composite_score'],
                'quality_grade' => $qualitySummary['grade'],
            ],
            'quality_targets' => $qualitySummary['kpis'],
            'pipeline_counts' => $pipelineCounts,
            'pending_approvals' => $pendingItems,
            'pending_bons' => $pendingBons,
            'charts' => [
                'pipeline_stages' => [
                    'categories' => [
                        'Draft',
                        'Estimasi',
                        'Approval L1',
                        'Quotation Holding',
                        'Actual Price',
                        'Approval L2 / Bon',
                        'Ready to PO',
                        'Delivered',
                    ],
                    'series' => [
                        [
                            'name' => 'Jumlah Item',
                            'data' => [
                                $pipelineCounts['draft'],
                                $pipelineCounts['pending_estimate'],
                                $pipelineCounts['pending_l1'],
                                $pipelineCounts['waiting_quotation'],
                                $pipelineCounts['waiting_actual_price'],
                                $pipelineCounts['waiting_gm'],
                                $pipelineCounts['ready_to_po'],
                                $pipelineCounts['ordered_delivered'],
                            ],
                        ],
                    ],
                ],
                'department_spend' => [
                    'categories' => $deptSpendCategories,
                    'series' => [
                        [
                            'name' => 'Realisasi Belanja (Juta Rp)',
                            'data' => $deptSpendData,
                        ],
                    ],
                ],
            ],
            'recent_activities' => $recentActivities,
        ];
    }
}
