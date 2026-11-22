<?php

namespace App\Core\Services;

use App\Core\Models\Department;
use App\Core\Models\ErpFeatureRecord;
use App\Core\Models\Product;
use App\Core\Models\Vendor;
use App\Modules\Finance\Models\FinCoaAccount;
use App\Modules\GeneralAffair\Models\GaAsset;
use App\Modules\Hris\Models\HrisEmployee;
use App\Modules\Production\Models\Machine;
use App\Modules\Production\Models\WorkOrder;
use App\Modules\Quality\Models\QcInspection;
use App\Modules\Rnd\Models\RndFormula;
use App\Modules\SupplyChain\Models\Warehouse;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Schema;

class CrossModuleDataService
{
    /**
     * Get dictionary of all cross-module data sources available for custom modules & canvas widgets.
     *
     * @return array<string, array<string, mixed>>
     */
    public function getAvailableDataSources(): array
    {
        return [
            'hris_employees' => [
                'name' => 'Data Karyawan & Personil',
                'module' => 'HRIS',
                'icon' => 'Users',
                'badge' => 'hris',
                'description' => 'Daftar karyawan aktif, NIK, jabatan, dan departemen',
                'display_column' => 'name',
                'key_column' => 'id',
                'count' => class_exists(HrisEmployee::class) && Schema::hasTable('hris_employees') ? HrisEmployee::where('status', 'active')->count() : 0,
            ],
            'core_departments' => [
                'name' => 'Departemen & Divisi',
                'module' => 'Core',
                'icon' => 'Building2',
                'badge' => 'core',
                'description' => 'Struktur organisasi dan pusat biaya (cost center)',
                'display_column' => 'name',
                'key_column' => 'id',
                'count' => Schema::hasTable('departments') ? Department::where('is_active', true)->count() : 0,
            ],
            'fin_coa_accounts' => [
                'name' => 'Bagan Akun Keuangan (COA)',
                'module' => 'Finance',
                'icon' => 'BookOpen',
                'badge' => 'finance',
                'description' => 'Master akun buku besar (GL), saldo debit/kredit dan tipe akun',
                'display_column' => 'name',
                'key_column' => 'id',
                'count' => class_exists(FinCoaAccount::class) && Schema::hasTable('fin_coa_accounts') ? FinCoaAccount::where('is_active', true)->count() : 0,
            ],
            'core_products' => [
                'name' => 'Master Produk & Bahan Baku',
                'module' => 'Supply Chain',
                'icon' => 'Package',
                'badge' => 'scm',
                'description' => 'Katalog SKU herbal, simplisia, ekstrak, dan bahan kemas',
                'display_column' => 'name',
                'key_column' => 'id',
                'count' => Schema::hasTable('products') ? Product::count() : 0,
            ],
            'scm_warehouses' => [
                'name' => 'Gudang & Penyimpanan',
                'module' => 'Supply Chain',
                'icon' => 'Warehouse',
                'badge' => 'scm',
                'description' => 'Lokasi gudang bahan baku, karantina, dan produk jadi',
                'display_column' => 'name',
                'key_column' => 'id',
                'count' => class_exists(Warehouse::class) && Schema::hasTable('scm_warehouses') ? Warehouse::count() : 0,
            ],
            'core_vendors' => [
                'name' => 'Daftar Vendor & Mitra Pemasok',
                'module' => 'Procurement',
                'icon' => 'Truck',
                'badge' => 'procurement',
                'description' => 'Rekanan supplier bahan baku ekstrak dan logistik',
                'display_column' => 'name',
                'key_column' => 'id',
                'count' => Schema::hasTable('vendors') ? Vendor::where('is_active', true)->count() : 0,
            ],
            'prod_work_orders' => [
                'name' => 'Perintah Kerja SPK Produksi',
                'module' => 'Produksi',
                'icon' => 'Factory',
                'badge' => 'production',
                'description' => 'Work order batch manufaktur herbal dan status pengerjaan',
                'display_column' => 'wo_number',
                'key_column' => 'id',
                'count' => class_exists(WorkOrder::class) && Schema::hasTable('prod_work_orders') ? WorkOrder::count() : 0,
            ],
            'prod_machines' => [
                'name' => 'Mesin & Lini Produksi',
                'module' => 'Produksi',
                'icon' => 'Cpu',
                'badge' => 'production',
                'description' => 'Peralatan ekstraksi, mixer, dan mesin pengemasan',
                'display_column' => 'name',
                'key_column' => 'id',
                'count' => class_exists(Machine::class) && Schema::hasTable('prod_machines') ? Machine::where('is_active', true)->count() : 0,
            ],
            'qc_inspections' => [
                'name' => 'Log Inspeksi Mutu & Pengujian',
                'module' => 'Quality',
                'icon' => 'ShieldCheck',
                'badge' => 'quality',
                'description' => 'Laporan uji organoleptik, mikrobiologi, dan sertifikasi rilis',
                'display_column' => 'inspection_number',
                'key_column' => 'id',
                'count' => class_exists(QcInspection::class) && Schema::hasTable('qc_inspections') ? QcInspection::count() : 0,
            ],
            'ga_assets' => [
                'name' => 'Aset Fasilitas & Inventaris',
                'module' => 'General Affair',
                'icon' => 'Briefcase',
                'badge' => 'general_affair',
                'description' => 'Master inventaris kantor, peralatan, dan status kondisi',
                'display_column' => 'name',
                'key_column' => 'id',
                'count' => class_exists(GaAsset::class) && Schema::hasTable('ga_assets') ? GaAsset::count() : 0,
            ],
            'rnd_formulas' => [
                'name' => 'Master Formula & Trial R&D',
                'module' => 'R&D',
                'icon' => 'FlaskConical',
                'badge' => 'rnd',
                'description' => 'Resep formula herbal, standar dosis, dan status uji BPOM',
                'display_column' => 'name',
                'key_column' => 'id',
                'count' => class_exists(RndFormula::class) && Schema::hasTable('rnd_formulas') ? RndFormula::count() : 0,
            ],
            'erp_feature_records' => [
                'name' => 'Data Transaksi 215 Fitur Menu',
                'module' => 'Universal',
                'icon' => 'Layers',
                'badge' => 'universal',
                'description' => 'Entri data operasional dari 215 modul menu ERP tersimpan',
                'display_column' => 'title',
                'key_column' => 'id',
                'count' => Schema::hasTable('erp_feature_records') ? ErpFeatureRecord::count() : 0,
            ],
        ];
    }

    /**
     * Fetch live records for a given cross-module data source.
     *
     * @return Collection<int, array<string, mixed>>
     */
    public function fetchSourceRecords(string $sourceKey, ?string $search = null, int $limit = 25): Collection
    {
        $search = strtolower(trim((string) $search));

        return match ($sourceKey) {
            'hris_employees' => HrisEmployee::with('user')
                ->when($search !== '', function ($q) use ($search) {
                    $q->where('nik', 'like', "%{$search}%")
                        ->orWhere('position', 'like', "%{$search}%")
                        ->orWhereHas('user', fn ($uq) => $uq->where('name', 'like', "%{$search}%"));
                })
                ->limit($limit)
                ->get()
                ->map(fn ($e) => [
                    'id' => $e->id,
                    'title' => $e->user?->name ?? "Karyawan #{$e->nik}",
                    'code' => $e->nik,
                    'subtitle' => $e->position ?? 'Staff',
                    'badge' => $e->status,
                ]),

            'core_departments' => Department::when($search !== '', fn ($q) => $q->where('name', 'like', "%{$search}%")->orWhere('code', 'like', "%{$search}%"))
                ->limit($limit)
                ->get()
                ->map(fn ($d) => [
                    'id' => $d->id,
                    'title' => $d->name,
                    'code' => $d->code,
                    'subtitle' => $d->plant?->name ?? 'Head Office',
                    'badge' => $d->is_active ? 'Aktif' : 'Non-Aktif',
                ]),

            'fin_coa_accounts' => FinCoaAccount::when($search !== '', fn ($q) => $q->where('name', 'like', "%{$search}%")->orWhere('code', 'like', "%{$search}%"))
                ->limit($limit)
                ->get()
                ->map(fn ($a) => [
                    'id' => $a->id,
                    'title' => $a->name,
                    'code' => $a->code,
                    'subtitle' => strtoupper($a->account_type ?? 'GL').' | Saldo: Rp '.number_format((float) ($a->current_balance ?? 0), 0, ',', '.'),
                    'badge' => $a->classification ?? 'General',
                ]),

            'core_products' => Product::when($search !== '', fn ($q) => $q->where('name', 'like', "%{$search}%")->orWhere('code', 'like', "%{$search}%"))
                ->limit($limit)
                ->get()
                ->map(fn ($p) => [
                    'id' => $p->id,
                    'title' => $p->name,
                    'code' => $p->code,
                    'subtitle' => 'Kategori: '.($p->category ?? 'Herbal').' | UOM: '.($p->uom?->code ?? 'PCS'),
                    'badge' => $p->product_type ?? 'FG',
                ]),

            'scm_warehouses' => Warehouse::when($search !== '', fn ($q) => $q->where('name', 'like', "%{$search}%")->orWhere('code', 'like', "%{$search}%"))
                ->limit($limit)
                ->get()
                ->map(fn ($w) => [
                    'id' => $w->id,
                    'title' => $w->name,
                    'code' => $w->code,
                    'subtitle' => 'Tipe: '.($w->warehouse_type ?? 'Standard'),
                    'badge' => $w->is_active ? 'Operasional' : 'Tutup',
                ]),

            'core_vendors' => Vendor::when($search !== '', fn ($q) => $q->where('name', 'like', "%{$search}%")->orWhere('code', 'like', "%{$search}%"))
                ->limit($limit)
                ->get()
                ->map(fn ($v) => [
                    'id' => $v->id,
                    'title' => $v->name,
                    'code' => $v->code,
                    'subtitle' => 'PIC: '.($v->contact_person ?? '-').' ('.$v->phone.')',
                    'badge' => $v->is_active ? 'Mitra Terverifikasi' : 'Pending',
                ]),

            'prod_work_orders' => WorkOrder::when($search !== '', fn ($q) => $q->where('wo_number', 'like', "%{$search}%"))
                ->limit($limit)
                ->get()
                ->map(fn ($wo) => [
                    'id' => $wo->id,
                    'title' => 'SPK '.$wo->wo_number,
                    'code' => $wo->wo_number,
                    'subtitle' => 'Batch: '.($wo->batch_number ?? '-').' | Target: '.($wo->target_quantity ?? 0).' unit',
                    'badge' => $wo->status,
                ]),

            'prod_machines' => Machine::when($search !== '', fn ($q) => $q->where('name', 'like', "%{$search}%")->orWhere('code', 'like', "%{$search}%"))
                ->limit($limit)
                ->get()
                ->map(fn ($m) => [
                    'id' => $m->id,
                    'title' => $m->name,
                    'code' => $m->code,
                    'subtitle' => 'Kapasitas: '.($m->capacity_per_hour ?? 0).' unit/jam',
                    'badge' => $m->status,
                ]),

            'qc_inspections' => QcInspection::when($search !== '', fn ($q) => $q->where('inspection_number', 'like', "%{$search}%"))
                ->limit($limit)
                ->get()
                ->map(fn ($qc) => [
                    'id' => $qc->id,
                    'title' => 'QC #'.$qc->inspection_number,
                    'code' => $qc->inspection_number,
                    'subtitle' => 'Tipe: '.($qc->inspection_type ?? 'In-Process'),
                    'badge' => $qc->status,
                ]),

            'ga_assets' => GaAsset::when($search !== '', fn ($q) => $q->where('name', 'like', "%{$search}%")->orWhere('asset_code', 'like', "%{$search}%"))
                ->limit($limit)
                ->get()
                ->map(fn ($a) => [
                    'id' => $a->id,
                    'title' => $a->name,
                    'code' => $a->asset_code,
                    'subtitle' => 'Kategori: '.($a->category?->name ?? 'Aset GA').' | Lokasi: '.($a->location?->name ?? '-'),
                    'badge' => $a->condition ?? 'Baik',
                ]),

            'rnd_formulas' => RndFormula::when($search !== '', fn ($q) => $q->where('name', 'like', "%{$search}%")->orWhere('formula_code', 'like', "%{$search}%"))
                ->limit($limit)
                ->get()
                ->map(fn ($f) => [
                    'id' => $f->id,
                    'title' => $f->name,
                    'code' => $f->formula_code,
                    'subtitle' => 'Versi: '.($f->version ?? 'v1.0').' | Kategori: '.($f->category ?? 'Jamu Herbal'),
                    'badge' => $f->status ?? 'Draft',
                ]),

            'erp_feature_records' => ErpFeatureRecord::when($search !== '', fn ($q) => $q->where('title', 'like', "%{$search}%")->orWhere('code_number', 'like', "%{$search}%"))
                ->limit($limit)
                ->latest()
                ->get()
                ->map(fn ($r) => [
                    'id' => $r->id,
                    'title' => $r->title,
                    'code' => $r->code_number,
                    'subtitle' => 'Modul: '.strtoupper($r->module_slug).' ('.$r->feature_slug.') | PIC: '.($r->pic_name ?? '-'),
                    'badge' => $r->status,
                ]),

            default => collect(),
        };
    }

    /**
     * Get instant summary metrics for binding into KPI stat cards.
     *
     * @return array<string, array<string, mixed>>
     */
    public function getMetricsSummary(): array
    {
        return [
            'total_employees' => [
                'label' => 'Total Karyawan Aktif',
                'value' => Schema::hasTable('hris_employees') ? HrisEmployee::where('status', 'active')->count() : 0,
                'unit' => 'Orang',
                'icon' => 'Users',
                'color' => 'emerald',
            ],
            'total_departments' => [
                'label' => 'Departemen Operasional',
                'value' => Schema::hasTable('departments') ? Department::where('is_active', true)->count() : 0,
                'unit' => 'Divisi',
                'icon' => 'Building2',
                'color' => 'blue',
            ],
            'total_products' => [
                'label' => 'Total Katalog SKU Produk',
                'value' => Schema::hasTable('products') ? Product::count() : 0,
                'unit' => 'SKU',
                'icon' => 'Package',
                'color' => 'amber',
            ],
            'total_coa_accounts' => [
                'label' => 'Bagan Akun Aktif (COA)',
                'value' => Schema::hasTable('fin_coa_accounts') ? FinCoaAccount::where('is_active', true)->count() : 0,
                'unit' => 'Akun',
                'icon' => 'BookOpen',
                'color' => 'violet',
            ],
            'total_vendors' => [
                'label' => 'Vendor & Mitra Pemasok',
                'value' => Schema::hasTable('vendors') ? Vendor::where('is_active', true)->count() : 0,
                'unit' => 'Mitra',
                'icon' => 'Truck',
                'color' => 'teal',
            ],
            'total_work_orders' => [
                'label' => 'Perintah Kerja SPK',
                'value' => Schema::hasTable('prod_work_orders') ? WorkOrder::count() : 0,
                'unit' => 'SPK',
                'icon' => 'Factory',
                'color' => 'cyan',
            ],
            'total_feature_records' => [
                'label' => 'Entri 215 Fitur Menu ERP',
                'value' => Schema::hasTable('erp_feature_records') ? ErpFeatureRecord::count() : 0,
                'unit' => 'Entri',
                'icon' => 'Layers',
                'color' => 'indigo',
            ],
            'total_assets' => [
                'label' => 'Aset Fasilitas GA',
                'value' => Schema::hasTable('ga_assets') ? GaAsset::count() : 0,
                'unit' => 'Unit',
                'icon' => 'Briefcase',
                'color' => 'rose',
            ],
        ];
    }
}
