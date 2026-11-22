<?php

namespace App\Core\Integrations\Services;

use App\Core\Integrations\Contracts\UnifiedApprovalContract;
use App\Core\Models\CustomRecord;
use App\Models\User;
use App\Modules\Finance\Models\FinPeriodAdjustingEntry;
use App\Modules\GeneralAffair\Models\AtkRequest;
use App\Modules\GeneralAffair\Models\FpuRequest;
use App\Modules\GeneralAffair\Models\WorkOrder;
use App\Modules\Hris\Models\HrisLeave;
use App\Modules\Hris\Models\HrisOvertime;
use App\Modules\Procurement\Models\PrBon;
use App\Modules\Procurement\Models\PrItem;
use App\Modules\Production\Models\LaporanHarian;
use App\Modules\Quality\Models\ChangeRequest;
use App\Modules\Quality\Models\Deviation;
use App\Modules\Rnd\Models\Formula;
use App\Modules\SupplyChain\Models\ScmQcInspection;
use App\Modules\SupplyChain\Models\ScmThreeWayMatching;
use Illuminate\Support\Facades\Schema;

class UnifiedApprovalInboxService implements UnifiedApprovalContract
{
    /**
     * Get aggregated pending approvals from all 8 core modules and custom modules.
     *
     * @return list<array<string, mixed>>
     */
    public function getPendingApprovalsForUser(?User $user = null): array
    {
        $approvals = [];

        // 1. Procurement: PR Items & PR Bons
        if (Schema::hasTable('pr_items')) {
            $prItems = PrItem::with(['purchaseRequest', 'product'])
                ->whereIn('status', ['pending', 'pending_estimate', 'waiting_quotation_holding', 'waiting_actual_price', 'waiting_gm'])
                ->latest('id')
                ->take(3)
                ->get();

            foreach ($prItems as $item) {
                $approvals[] = [
                    'id' => 'PR-ITEM-'.$item->id,
                    'title' => ($item->purchaseRequest?->pr_number ?? 'PR').' - '.$item->item_name,
                    'description' => "Pengadaan {$item->quantity} {$item->uom} (Status: {$item->status})",
                    'module_slug' => 'procurement',
                    'module_name' => 'Procurement',
                    'module_theme' => 'violet',
                    'type' => 'Purchase Request Item',
                    'amount' => $item->estimated_price > 0 ? 'Rp '.number_format($item->estimated_price, 0, ',', '.') : 'Estimasi Diperlukan',
                    'requester' => $item->purchaseRequest?->user?->name ?? 'Pemohon Procurement',
                    'urgency' => in_array($item->status, ['waiting_gm', 'pending']) ? 'high' : 'normal',
                    'action_url' => '/procurement/purchase-requests',
                    'date' => $item->created_at ? $item->created_at->diffForHumans() : 'Baru saja',
                ];
            }
        }

        if (Schema::hasTable('pr_bons')) {
            $bons = PrBon::with('department')
                ->whereIn('status', ['submitted', 'waiting_gm'])
                ->latest('id')
                ->take(2)
                ->get();

            foreach ($bons as $bon) {
                $approvals[] = [
                    'id' => 'BON-'.$bon->id,
                    'title' => $bon->bon_number.' (Kas Kecil)',
                    'description' => 'Pengadaan Darurat: '.$bon->purpose,
                    'module_slug' => 'procurement',
                    'module_name' => 'Procurement',
                    'module_theme' => 'violet',
                    'type' => 'PR Bon Sementara',
                    'amount' => 'Rp '.number_format((float) $bon->total_amount, 0, ',', '.'),
                    'requester' => $bon->creator?->name ?? 'Pemohon Bon',
                    'urgency' => 'high',
                    'action_url' => '/procurement/pr-bons',
                    'date' => $bon->created_at ? $bon->created_at->diffForHumans() : 'Baru saja',
                ];
            }
        }

        // 2. R&D: Formulasi yang belum di-approve
        if (Schema::hasTable('rnd_formulas')) {
            $formulas = Formula::with('product')
                ->where(function ($q) {
                    $q->whereNull('approved_by_om')->orWhereNull('approved_by_gm');
                })
                ->whereNull('deleted_at')
                ->latest('id')
                ->take(2)
                ->get();

            foreach ($formulas as $f) {
                $approvals[] = [
                    'id' => 'FORMULA-'.$f->id,
                    'title' => ($f->formula_code ?: 'FORMULA').' (V'.$f->version.')',
                    'description' => 'Resep Formulasi: '.($f->product?->name ?? 'Produk R&D'),
                    'module_slug' => 'rnd',
                    'module_name' => 'R&D',
                    'module_theme' => 'cyan',
                    'type' => 'NPD Formulation',
                    'amount' => 'HPP: Rp '.number_format((float) $f->total_cost_per_pack, 0, ',', '.'),
                    'requester' => $f->creator?->name ?? 'Formulator R&D',
                    'urgency' => 'normal',
                    'action_url' => '/rnd/formulas',
                    'date' => $f->created_at ? $f->created_at->diffForHumans() : 'Baru saja',
                ];
            }
        }

        // 3. Quality: Deviations & Change Requests
        if (Schema::hasTable('quality_deviations')) {
            $deviations = Deviation::whereIn('status', ['DRAFT', 'OPEN', 'INVESTIGATION'])
                ->latest('id')
                ->take(2)
                ->get();

            foreach ($deviations as $d) {
                $approvals[] = [
                    'id' => 'DEV-'.$d->id,
                    'title' => $d->deviation_number.' - '.$d->topik,
                    'description' => "Investigasi Deviasi {$d->department} (Bets: {$d->nomor_bets})",
                    'module_slug' => 'quality',
                    'module_name' => 'Quality (QMS)',
                    'module_theme' => 'teal',
                    'type' => 'Deviation Report',
                    'amount' => null,
                    'requester' => $d->pic ?: 'Tim Mutu',
                    'urgency' => $d->identifikasi_penyimpangan === 'Kritis' ? 'critical' : 'high',
                    'action_url' => '/quality/deviations',
                    'date' => $d->created_at ? $d->created_at->diffForHumans() : 'Baru saja',
                ];
            }
        }

        if (Schema::hasTable('quality_change_requests')) {
            $crs = ChangeRequest::whereIn('status', ['SUBMITTED', 'PENDING_EVALUATION', 'PENDING_APPROVAL'])
                ->latest('id')
                ->take(2)
                ->get();

            foreach ($crs as $cr) {
                $approvals[] = [
                    'id' => 'CR-'.$cr->id,
                    'title' => $cr->cr_number.' - '.$cr->judul_perubahan,
                    'description' => "Usulan Perubahan {$cr->kategori_perubahan} oleh {$cr->departemen}",
                    'module_slug' => 'quality',
                    'module_name' => 'Quality (QMS)',
                    'module_theme' => 'teal',
                    'type' => 'Change Request',
                    'amount' => null,
                    'requester' => $cr->initiator?->name ?? 'Initiator QA',
                    'urgency' => $cr->kategori_perubahan === 'CRA' ? 'critical' : 'normal',
                    'action_url' => '/quality/change-requests',
                    'date' => $cr->created_at ? $cr->created_at->diffForHumans() : 'Baru saja',
                ];
            }
        }

        // 4. Finance: AJE Adjusting Entries
        if (Schema::hasTable('fin_period_adjusting_entries')) {
            $ajes = FinPeriodAdjustingEntry::where('status', 'draft')
                ->latest('id')
                ->take(2)
                ->get();

            foreach ($ajes as $aje) {
                $approvals[] = [
                    'id' => 'AJE-'.$aje->id,
                    'title' => ($aje->entry_number ?: 'AJE').' - '.$aje->description,
                    'description' => 'Jurnal Penyesuaian Akuntansi Akhir Periode',
                    'module_slug' => 'finance',
                    'module_name' => 'Finance',
                    'module_theme' => 'emerald',
                    'type' => 'Journal Entry',
                    'amount' => 'Rp '.number_format((float) $aje->total_debit, 0, ',', '.'),
                    'requester' => $aje->creator?->name ?? 'Staf Akuntansi',
                    'urgency' => 'normal',
                    'action_url' => '/finance/aje',
                    'date' => $aje->created_at ? $aje->created_at->diffForHumans() : 'Baru saja',
                ];
            }
        }

        // 5. Supply Chain: 3-Way Matching Pending & Material Returns
        if (Schema::hasTable('scm_three_way_matchings')) {
            $matchings = ScmThreeWayMatching::where('status', 'discrepant')
                ->orWhereNull('approved_at')
                ->latest('id')
                ->take(2)
                ->get();

            foreach ($matchings as $m) {
                $approvals[] = [
                    'id' => 'TWM-'.$m->id,
                    'title' => $m->matching_number.' (PO: '.$m->po_number.')',
                    'description' => "Audit 3-Way Matching Tagihan Vendor: {$m->vendor_invoice_number}",
                    'module_slug' => 'supply-chain',
                    'module_name' => 'Supply Chain',
                    'module_theme' => 'indigo',
                    'type' => '3-Way Matching AP',
                    'amount' => 'Rp '.number_format((float) $m->invoice_amount, 0, ',', '.'),
                    'requester' => $m->auditor?->name ?? 'Auditor SCM',
                    'urgency' => $m->status === 'discrepant' ? 'high' : 'normal',
                    'action_url' => '/supply-chain/matching',
                    'date' => $m->created_at ? $m->created_at->diffForHumans() : 'Baru saja',
                ];
            }
        }

        // 6. HRIS: Leaves & Overtimes
        if (Schema::hasTable('hris_leaves')) {
            $leaves = HrisLeave::with('employee')
                ->where('status', 'pending')
                ->latest('id')
                ->take(2)
                ->get();

            foreach ($leaves as $leave) {
                $approvals[] = [
                    'id' => 'LEAVE-'.$leave->id,
                    'title' => 'Pengajuan Cuti: '.($leave->employee?->full_name ?? 'Karyawan'),
                    'description' => "Cuti {$leave->total_days} hari ({$leave->start_date} s/d {$leave->end_date}): {$leave->reason}",
                    'module_slug' => 'hris',
                    'module_name' => 'HRIS',
                    'module_theme' => 'blue',
                    'type' => 'Pengajuan Cuti',
                    'amount' => "{$leave->total_days} Hari",
                    'requester' => $leave->employee?->full_name ?? 'Karyawan',
                    'urgency' => 'normal',
                    'action_url' => '/hris/leaves',
                    'date' => $leave->created_at ? $leave->created_at->diffForHumans() : 'Baru saja',
                ];
            }
        }

        // 7. General Affair: Work Orders, FPU & ATK Requests
        if (Schema::hasTable('ga_work_orders')) {
            $wos = WorkOrder::whereIn('status', ['DRAFT', 'OPEN', 'PENDING_FPU'])
                ->latest('id')
                ->take(2)
                ->get();

            foreach ($wos as $wo) {
                $approvals[] = [
                    'id' => 'WO-'.$wo->id,
                    'title' => $wo->wo_number.' - '.$wo->title,
                    'description' => "Perintah Kerja Fasilitas/Mesin: {$wo->facility_name}",
                    'module_slug' => 'general-affair',
                    'module_name' => 'General Affair',
                    'module_theme' => 'amber',
                    'type' => 'Work Order (Form A-D)',
                    'amount' => null,
                    'requester' => $wo->requester?->name ?? 'Pemohon GA',
                    'urgency' => $wo->priority === 'EMERGENCY' ? 'critical' : ($wo->priority === 'HIGH' ? 'high' : 'normal'),
                    'action_url' => '/general-affair/work-orders/'.$wo->id,
                    'date' => $wo->created_at ? $wo->created_at->diffForHumans() : 'Baru saja',
                ];
            }
        }

        if (Schema::hasTable('ga_fpu_requests')) {
            $fpus = FpuRequest::where('status', 'PENDING_APPROVAL')
                ->latest('id')
                ->take(2)
                ->get();

            foreach ($fpus as $fpu) {
                $approvals[] = [
                    'id' => 'FPU-'.$fpu->id,
                    'title' => $fpu->fpu_number.' (Form C FPU)',
                    'description' => 'Pengadaan Material Suku Cadang WO',
                    'module_slug' => 'general-affair',
                    'module_name' => 'General Affair',
                    'module_theme' => 'amber',
                    'type' => 'Form C FPU',
                    'amount' => 'Rp '.number_format((float) $fpu->total_estimated_cost, 0, ',', '.'),
                    'requester' => 'Admin GA',
                    'urgency' => 'high',
                    'action_url' => '/general-affair/work-orders',
                    'date' => $fpu->created_at ? $fpu->created_at->diffForHumans() : 'Baru saja',
                ];
            }
        }

        // 8. Custom Records (Developer Mode No-Code Builder)
        if (Schema::hasTable('custom_records')) {
            $records = CustomRecord::with('entity')
                ->where('status', 'PENDING_APPROVAL')
                ->latest('id')
                ->take(2)
                ->get();

            foreach ($records as $rec) {
                $approvals[] = [
                    'id' => 'CUSTOM-'.$rec->id,
                    'title' => ($rec->record_number ?: 'REC').' - '.($rec->entity?->name ?? 'Entitas Kustom'),
                    'description' => 'Transaksi modul kustom menunggu verifikasi',
                    'module_slug' => 'developer-mode',
                    'module_name' => 'Custom Module',
                    'module_theme' => 'slate',
                    'type' => 'Custom Record',
                    'amount' => null,
                    'requester' => 'Operator Modul',
                    'urgency' => 'normal',
                    'action_url' => '/developer-mode/entities',
                    'date' => $rec->created_at ? $rec->created_at->diffForHumans() : 'Baru saja',
                ];
            }
        }

        return $approvals;
    }

    /**
     * Get live badge counts per module slug.
     *
     * @return array<string, int>
     */
    public function getLiveModuleBadgeCounts(): array
    {
        $counts = [
            'procurement' => 0,
            'supply-chain' => 0,
            'hris' => 0,
            'quality' => 0,
            'general-affair' => 0,
            'finance' => 0,
            'production' => 0,
            'rnd' => 0,
        ];

        // Procurement
        if (Schema::hasTable('pr_items')) {
            $counts['procurement'] += PrItem::whereIn('status', ['pending', 'pending_estimate', 'waiting_gm'])->count();
        }
        if (Schema::hasTable('pr_bons')) {
            $counts['procurement'] += PrBon::whereIn('status', ['submitted', 'waiting_gm'])->count();
        }

        // Supply Chain
        if (Schema::hasTable('scm_qc_inspections')) {
            $counts['supply-chain'] += ScmQcInspection::where('status', 'pending')->count();
        }
        if (Schema::hasTable('scm_three_way_matchings')) {
            $counts['supply-chain'] += ScmThreeWayMatching::where('status', 'discrepant')->count();
        }

        // HRIS
        if (Schema::hasTable('hris_leaves')) {
            $counts['hris'] += HrisLeave::where('status', 'pending')->count();
        }
        if (Schema::hasTable('hris_overtimes')) {
            $counts['hris'] += HrisOvertime::where('status', 'submitted')->count();
        }

        // Quality
        if (Schema::hasTable('quality_deviations')) {
            $counts['quality'] += Deviation::whereIn('status', ['OPEN', 'DRAFT'])->count();
        }
        if (Schema::hasTable('quality_change_requests')) {
            $counts['quality'] += ChangeRequest::whereIn('status', ['SUBMITTED', 'PENDING_APPROVAL'])->count();
        }

        // General Affair
        if (Schema::hasTable('ga_work_orders')) {
            $counts['general-affair'] += WorkOrder::whereIn('status', ['DRAFT', 'OPEN', 'PENDING_FPU'])->count();
        }
        if (Schema::hasTable('ga_fpu_requests')) {
            $counts['general-affair'] += FpuRequest::where('status', 'PENDING_APPROVAL')->count();
        }
        if (Schema::hasTable('ga_atk_requests')) {
            $counts['general-affair'] += AtkRequest::whereIn('status', ['PENDING_MANAGER', 'PENDING_ADMIN_GA'])->count();
        }

        // Finance
        if (Schema::hasTable('fin_period_adjusting_entries')) {
            $counts['finance'] += FinPeriodAdjustingEntry::where('status', 'draft')->count();
        }

        // Production
        if (Schema::hasTable('laporan_harians')) {
            $counts['production'] += LaporanHarian::where('status', 'draft')->count();
        }

        // R&D
        if (Schema::hasTable('rnd_formulas')) {
            $counts['rnd'] += Formula::where(function ($q) {
                $q->whereNull('approved_by_om')->orWhereNull('approved_by_gm');
            })->whereNull('deleted_at')->count();
        }

        return $counts;
    }
}
