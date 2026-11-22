<?php

namespace App\Core\Services;

use App\Core\Integrations\Contracts\UnifiedApprovalContract;
use App\Core\Models\ErpModule;
use App\Core\Models\UserModulePreference;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Cache;

class ModuleManagerService
{
    public function __construct(
        protected ?UnifiedApprovalContract $approvalService = null
    ) {}

    /**
     * @return Collection<int, ErpModule>
     */
    public function getActiveModules(): Collection
    {
        $cached = Cache::remember('erp_active_modules', 3600, function (): Collection {
            return ErpModule::query()
                ->where('is_active', true)
                ->orderBy('sort_order')
                ->get();
        });

        if (! $cached instanceof Collection) {
            Cache::forget('erp_active_modules');

            $cached = ErpModule::query()
                ->where('is_active', true)
                ->orderBy('sort_order')
                ->get();
        }

        return $cached->map(function (ErpModule $module) {
            $module->menus = $this->getModuleMenus($module->slug, $module->schema_config_json);

            return $module;
        });
    }

    /**
     * @return Collection<int, ErpModule>
     */
    public function getModulesForUser(?User $user): Collection
    {
        $activeModules = $this->getActiveModules();

        $preferences = $user ? UserModulePreference::query()
            ->where('user_id', $user->id)
            ->get()
            ->keyBy('module_id') : collect();

        $sorted = $activeModules->map(function (ErpModule $module) use ($preferences) {
            $pref = $preferences->get($module->id);
            $module->is_pinned = $pref ? (bool) $pref->is_pinned : false;
            $module->custom_order = $pref ? (int) $pref->custom_order : (int) $module->sort_order;
            $module->badge_count = $this->getLiveBadgeCount($module->slug);
            $module->menus = $this->getModuleMenus($module->slug, $module->schema_config_json);

            return $module;
        })->sort(function ($a, $b) {
            if ($a->is_pinned !== $b->is_pinned) {
                return $a->is_pinned ? -1 : 1;
            }

            return $a->custom_order <=> $b->custom_order;
        })->values();

        // Filter modules & menus for restricted non-superadmin users
        if ($user && ! $user->isSuperAdmin() && $user->accessPermissions()->exists()) {
            return $sorted->filter(function (ErpModule $module) use ($user) {
                return $user->hasModuleAccess($module->slug);
            })->map(function (ErpModule $module) use ($user) {
                $menus = $module->menus ?? $this->getModuleMenus($module->slug, $module->schema_config_json);
                $module->menus = array_values(array_filter($menus, function ($m) use ($user, $module) {
                    return $user->hasMenuAccess($module->slug, $m['route']);
                }));

                return $module;
            })->values();
        }

        return $sorted;
    }

    /**
     * Returns contextual menus for a module.
     *
     * @param  array<string, mixed>|null  $schemaConfig
     * @return list<array<string, mixed>>
     */
    public function getModuleMenus(string $slug, ?array $schemaConfig = null): array
    {
        if (! empty($schemaConfig['menus'])) {
            return $schemaConfig['menus'];
        }

        return match ($slug) {
            'finance' => [
                // 1. Ringkasan
                ['name' => 'Overview Dashboard', 'route' => '/finance', 'icon' => 'LayoutDashboard', 'category' => 'Ringkasan', 'description' => 'Ringkasan KPI eksekutif, status periode & grafik realisasi'],
                ['name' => 'Dashboard Manager', 'route' => '/finance/manager-dashboard', 'icon' => 'PieChart', 'category' => 'Ringkasan', 'description' => 'Pantauan manajerial 11 departemen'],
                ['name' => 'Dashboard Departemen', 'route' => '/finance/dept-dashboard', 'icon' => 'Building2', 'category' => 'Ringkasan', 'description' => 'Pantauan pengeluaran khusus departemen login'],

                // 2. Anggaran
                ['name' => 'Monitoring Budget P&L', 'route' => '/finance/budget-pnl', 'icon' => 'BarChart3', 'category' => 'Anggaran', 'description' => 'Komparasi berdampingan Target vs Actual P&L'],
                ['name' => 'Budget Dinamis & Variansi', 'route' => '/finance/dynamic-budget', 'icon' => 'Calculator', 'category' => 'Anggaran', 'description' => 'Flexible budget engine & simulasi volume'],
                ['name' => 'Realisasi Belanja Pagu', 'route' => '/finance/monitoring', 'icon' => 'Wallet', 'category' => 'Anggaran', 'description' => 'Kontrol pagu belanja operasional per departemen'],
                ['name' => 'Forecast Belanja', 'route' => '/finance/forecasts', 'icon' => 'TrendingUp', 'category' => 'Anggaran', 'description' => 'Proyeksi kebutuhan belanja bulanan'],
                ['name' => 'Laporan Anggaran Proyeksi', 'route' => '/finance/budgeted-statements', 'icon' => 'FileSpreadsheet', 'category' => 'Anggaran', 'description' => 'Laporan laba rugi & neraca versi proyeksi murni'],
                ['name' => 'Analisa Varians Biaya', 'route' => '/finance/variance-analysis', 'icon' => 'Scale', 'category' => 'Anggaran', 'description' => 'Dekomposisi varians volume & harga'],
                ['name' => 'Rekap Realisasi Belanja', 'route' => '/finance/laporan', 'icon' => 'FileText', 'category' => 'Anggaran', 'description' => 'Ekspor rekapitulasi realisasi belanja'],
                ['name' => 'Standar Budget Departemen', 'route' => '/finance/departments', 'icon' => 'Landmark', 'category' => 'Anggaran', 'description' => 'Penetapan pagu bulanan 11 departemen'],
                ['name' => 'Rencana Produksi Pabrik', 'route' => '/finance/production-plans', 'icon' => 'Factory', 'category' => 'Anggaran', 'description' => 'Target kapasitas mesin, yield & biaya BOM'],
                ['name' => 'Rencana Pendapatan Omset', 'route' => '/finance/revenue-plans', 'icon' => 'DollarSign', 'category' => 'Anggaran', 'description' => 'Target volume unit dan harga jual produk'],
                ['name' => 'Pagu PR (Staging Verifikasi)', 'route' => '/finance/staging', 'icon' => 'Receipt', 'category' => 'Anggaran', 'description' => 'Verifikasi status pengeluaran bon belanja'],

                // 3. Akuntansi
                ['name' => 'Master Chart of Accounts (COA)', 'route' => '/finance/coa', 'icon' => 'ListTree', 'category' => 'Akuntansi', 'description' => 'Bagan akun mandiri, saldo awal & pemetaan FSLI'],
                ['name' => 'Jurnal Penyesuaian (AJE)', 'route' => '/finance/aje', 'icon' => 'FileSpreadsheet', 'category' => 'Akuntansi', 'description' => 'Maker-checker jurnal akrual, depresiasi & amortisasi'],
                ['name' => 'Trial Balance 4-Kolom', 'route' => '/finance/trial-balance', 'icon' => 'Scale', 'category' => 'Akuntansi', 'description' => 'Kertas kerja neraca saldo seimbang & drilldown'],
                ['name' => '4 Laporan Keuangan Standar', 'route' => '/finance/financial-statements', 'icon' => 'BookOpen', 'category' => 'Akuntansi', 'description' => 'Laba Rugi, Neraca, Ekuitas, Arus Kas'],
                ['name' => '19 Rasio Finansial BSC', 'route' => '/finance/financial-ratios', 'icon' => 'Activity', 'category' => 'Akuntansi', 'description' => '19 Rasio Finansial 5 Pilar BSC'],
                ['name' => 'Modal Disetor (Paid-in)', 'route' => '/finance/paid-in-capital', 'icon' => 'Coins', 'category' => 'Akuntansi', 'description' => 'Pencatatan modal disetor resmi untuk rasio CPR'],
                ['name' => 'Produk NPD (Omset Inovasi)', 'route' => '/finance/npd', 'icon' => 'Lightbulb', 'category' => 'Akuntansi', 'description' => 'Omset produk inovasi baru vs eksisting'],

                // 4. Data & Audit
                ['name' => 'Kroscek Odoo Live', 'route' => '/finance/odoo-croscheck', 'icon' => 'RefreshCw', 'category' => 'Data', 'description' => 'Verifikasi keseimbangan transaksi Odoo live'],
                ['name' => 'Pemetaan COA & Cost Center', 'route' => '/finance/coa-mapping', 'icon' => 'ListTree', 'category' => 'Data', 'description' => 'Aturan distribusi akun Odoo ke FSLI & Departemen'],
                ['name' => 'Audit Rekonsiliasi (R1-R9)', 'route' => '/finance/reconciliation', 'icon' => 'CheckCheck', 'category' => 'Data', 'description' => '9-Point automatic reconciliation checks'],
                ['name' => 'Audit Trail & Activity Logs', 'route' => '/finance/activity-logs', 'icon' => 'Clock', 'category' => 'Data', 'description' => 'Catatan histori aktivitas seluruh pengguna & sistem'],

                // 5. Integrasi
                ['name' => 'Publikasi Data Olahan', 'route' => '/finance/publications', 'icon' => 'Share2', 'category' => 'Integrasi', 'description' => 'Staging snapshot publikasi finansial ke HRIS & BSC'],
                ['name' => 'Pipeline Sasaran Mutu', 'route' => '/finance/kpi-pipeline', 'icon' => 'Target', 'category' => 'Integrasi', 'description' => 'Ekstraksi 64 Sasaran Mutu Odoo untuk HRIS'],
                ['name' => 'Data Ketenagakerjaan HRIS', 'route' => '/finance/headcounts', 'icon' => 'Users', 'category' => 'Integrasi', 'description' => 'Master headcount, karyawan langsung & jam kerja'],
                ['name' => 'Integrasi SuperApps BSC', 'route' => '/finance/bsc-integration', 'icon' => 'Cpu', 'category' => 'Integrasi', 'description' => 'Konfigurasi koneksi ke SuperApps BSC Hub'],
                ['name' => 'Konfigurasi Bobot Rasio BSC', 'route' => '/finance/ratios-configurations', 'icon' => 'Sliders', 'category' => 'Integrasi', 'description' => 'Kustomisasi bobot & target rasio ke BSC'],
                ['name' => 'Feed Sistem Produksi', 'route' => '/finance/production-feeds', 'icon' => 'Boxes', 'category' => 'Integrasi', 'description' => 'Penerimaan data realisasi pabrik & reject rate'],

                // 6. Administrasi
                ['name' => 'Tahun Anggaran & Tutup Buku', 'route' => '/finance/fiscal-years', 'icon' => 'Calendar', 'category' => 'Admin', 'description' => 'Penetapan tahun fiskal aktif & penutupan buku'],
                ['name' => 'Manajemen Pengguna FAT', 'route' => '/finance/users', 'icon' => 'ShieldCheck', 'category' => 'Admin', 'description' => 'Pengelolaan hak akses pengguna & peran RBAC'],
                ['name' => 'Pengaturan Sistem FAT', 'route' => '/finance/settings', 'icon' => 'Settings', 'category' => 'Admin', 'description' => 'Pengujian API koneksi Odoo, HRIS, BSC, Produksi'],
            ],
            'general-affair' => [
                // 1. Work Order GA & FPU
                ['name' => 'Overview FPUGA Dashboard', 'route' => '/general-affair', 'icon' => 'LayoutDashboard', 'category' => 'Work Order GA & FPU', 'description' => 'Pusat Kendali Fasilitas Penunjang Umum & GA'],
                ['name' => 'Daftar Work Order GA', 'route' => '/general-affair/work-orders', 'icon' => 'Wrench', 'category' => 'Work Order GA & FPU', 'description' => 'Monitoring tiket perbaikan fasilitas & utilitas gedung'],
                ['name' => 'Form A: Permohonan Perbaikan', 'route' => '/general-affair/work-orders/create', 'icon' => 'FilePlus', 'category' => 'Work Order GA & FPU', 'description' => 'Pengajuan keluhan/kebutuhan perbaikan pemohon'],
                ['name' => 'Form B: Pengecekan Teknisi', 'route' => '/general-affair/work-orders/inspection', 'icon' => 'ClipboardCheck', 'category' => 'Work Order GA & FPU', 'description' => 'Inspeksi lapangan & identifikasi kebutuhan material'],
                ['name' => 'Form C: Pengadaan Material FPU', 'route' => '/general-affair/work-orders/fpu-materials', 'icon' => 'PackagePlus', 'category' => 'Work Order GA & FPU', 'description' => 'Pengadaan material verifikasi HRGA, Mgr, GM'],
                ['name' => 'Timeline Progres Pengerjaan', 'route' => '/general-affair/work-orders/progress', 'icon' => 'Hourglass', 'category' => 'Work Order GA & FPU', 'description' => 'Laporan progres harian teknisi & dokumentasi foto'],
                ['name' => 'Form D: Berita Acara & Rating', 'route' => '/general-affair/work-orders/handover', 'icon' => 'CheckCircle2', 'category' => 'Work Order GA & FPU', 'description' => 'Serah terima hasil pekerjaan & rating 5-bintang'],
                ['name' => 'Cetak Form Resmi A, C, dan D', 'route' => '/general-affair/work-orders/print', 'icon' => 'Printer', 'category' => 'Work Order GA & FPU', 'description' => 'Dokumen cetak legal standar operasional FPUGA'],

                // 2. Permintaan ATK & RT
                ['name' => 'Daftar Permintaan ATK & RT', 'route' => '/general-affair/atk', 'icon' => 'Package', 'category' => 'Permintaan ATK & RT', 'description' => 'Logistik ATK & operasional rumah tangga kantor/pabrik'],
                ['name' => 'Pengajuan Katalog & Custom Item', 'route' => '/general-affair/atk/create', 'icon' => 'ShoppingCart', 'category' => 'Permintaan ATK & RT', 'description' => 'Katalog standar ATK/RT & custom item'],
                ['name' => 'Persetujuan Manager & Admin GA', 'route' => '/general-affair/atk/approvals', 'icon' => 'UserCheck', 'category' => 'Permintaan ATK & RT', 'description' => 'Alur approval Manager Divisi -> Admin GA'],
                ['name' => 'Approval Granular & Revisi (-R1)', 'route' => '/general-affair/atk/revisions', 'icon' => 'RefreshCw', 'category' => 'Permintaan ATK & RT', 'description' => 'Revisi terstruktur item ditolak tanpa mengulang form'],
                ['name' => 'Serah Terima Fisik Barang', 'route' => '/general-affair/atk/distribution', 'icon' => 'ArrowRightCircle', 'category' => 'Permintaan ATK & RT', 'description' => 'Pencatatan tanggal distribusi & nama penerima'],
                ['name' => 'Cetak & Ekspor Rekap Bulanan', 'route' => '/general-affair/atk/reports', 'icon' => 'FileSpreadsheet', 'category' => 'Permintaan ATK & RT', 'description' => 'Ekspor PDF dan Excel untuk audit pembukuan bulanan'],

                // 3. Manajemen Aset Tetap Multi-Plant
                ['name' => 'Master Aset Multi-Plant', 'route' => '/general-affair/assets', 'icon' => 'Building2', 'category' => 'Aset Tetap Multi-Plant', 'description' => 'Penatausahaan aset Plant A, Plant B & Head Office'],
                ['name' => 'Depresiasi Garis Lurus', 'route' => '/general-affair/assets/depreciation', 'icon' => 'Calculator', 'category' => 'Aset Tetap Multi-Plant', 'description' => 'Penyusutan bulanan, akumulasi & nilai buku real-time'],
                ['name' => 'Mutasi Aset Lintas Lokasi', 'route' => '/general-affair/assets/mutations', 'icon' => 'ArrowLeftRight', 'category' => 'Aset Tetap Multi-Plant', 'description' => 'Transaksi pemindahan aset antar plant, dept & ruangan'],
                ['name' => 'Kondisi Fisik & Disposal Aset', 'route' => '/general-affair/assets/disposals', 'icon' => 'Trash2', 'category' => 'Aset Tetap Multi-Plant', 'description' => 'Monitoring kondisi baik/rusak & penghapusan aset'],
                ['name' => 'Daftar Inventaris Ruangan (DIR)', 'route' => '/general-affair/assets/dir-rooms', 'icon' => 'ClipboardList', 'category' => 'Aset Tetap Multi-Plant', 'description' => 'Form CM-03/GA/002.C.01 cetak lembar inventaris pintu'],
                ['name' => 'Label QR Code & Mobile Scanner', 'route' => '/general-affair/assets/qr-scanner', 'icon' => 'QrCode', 'category' => 'Aset Tetap Multi-Plant', 'description' => 'Cetak label QR & scanner kamera inspeksi opname'],
                ['name' => 'Asisten AI Pengendali Aset', 'route' => '/general-affair/assets/ai-assistant', 'icon' => 'Sparkles', 'category' => 'Aset Tetap Multi-Plant', 'description' => 'GaAiAssetAgentService asisten cerdas bahasa alami'],

                // 4. Security Patrol & Pos Keamanan
                ['name' => 'Dashboard Pos Keamanan', 'route' => '/general-affair/patrols/security', 'icon' => 'ShieldCheck', 'category' => 'Security Patrol', 'description' => 'Monitoring kepatuhan patroli satpam & pos perimeter'],
                ['name' => 'Master Pos & Checkpoints QR', 'route' => '/general-affair/patrols/security/checkpoints', 'icon' => 'MapPin', 'category' => 'Security Patrol', 'description' => 'Pemetaan gerbang, loading dock, trafo & barcode pos'],
                ['name' => 'Penjadwalan Shift & Import Excel', 'route' => '/general-affair/patrols/security/schedules', 'icon' => 'Calendar', 'category' => 'Security Patrol', 'description' => 'Shift pagi, siang, malam & regu tetap satpam'],
                ['name' => 'Mobile QR Scanner Patroli + Timer', 'route' => '/general-affair/patrols/security/scan', 'icon' => 'ScanLine', 'category' => 'Security Patrol', 'description' => 'Scan pos HP, validasi durasi patroli & status pos'],
                ['name' => 'Laporan Anomali (Evident Report)', 'route' => '/general-affair/patrols/security/incidents', 'icon' => 'Camera', 'category' => 'Security Patrol', 'description' => 'Pencatatan kejadian mencurigakan & bukti foto'],
                ['name' => 'Ekspor Rekapitulasi Patroli', 'route' => '/general-affair/patrols/security/reports', 'icon' => 'FileText', 'category' => 'Security Patrol', 'description' => 'Laporan bulanan persentase pos terinspeksi'],

                // 5. Cleaning Service & Sanitasi 5R
                ['name' => 'Dashboard Kebersihan Ruangan', 'route' => '/general-affair/patrols/cleaning', 'icon' => 'Sparkles', 'category' => 'Cleaning Service 5R', 'description' => 'Pemantauan sanitasi toilet & fasilitas kerja'],
                ['name' => 'Master Titik Sanitasi & QR', 'route' => '/general-affair/patrols/cleaning/checkpoints', 'icon' => 'MapPin', 'category' => 'Cleaning Service 5R', 'description' => 'Titik pembersihan toilet, koridor, locker, kantin'],
                ['name' => 'Penjadwalan Petugas Kebersihan', 'route' => '/general-affair/patrols/cleaning/schedules', 'icon' => 'Calendar', 'category' => 'Cleaning Service 5R', 'description' => 'Penugasan shift & area kerja cleaning service'],
                ['name' => 'Mobile QR Checklist Pembersihan', 'route' => '/general-affair/patrols/cleaning/scan', 'icon' => 'CheckSquare', 'category' => 'Cleaning Service 5R', 'description' => 'Scan kode ruangan & checklist sanitasi selesai'],
                ['name' => 'Rekap Kepatuhan Sanitasi', 'route' => '/general-affair/patrols/cleaning/reports', 'icon' => 'FileSpreadsheet', 'category' => 'Cleaning Service 5R', 'description' => 'Monitoring kedisiplinan pembersihan per area'],

                // 6. IT HelpDesk Pro & SLA Ticketing
                ['name' => 'HelpDesk Dashboard & SLA Hub', 'route' => '/general-affair/helpdesk', 'icon' => 'LifeBuoy', 'category' => 'IT HelpDesk Pro', 'description' => 'Monitoring tiket FRT, MTTR & SLA compliance %'],
                ['name' => 'Mode Proyek (9-Stage Workflow)', 'route' => '/general-affair/helpdesk/projects', 'icon' => 'Layers', 'category' => 'IT HelpDesk Pro', 'description' => 'Requirements -> UI/UX -> DB -> Code -> UAT -> Deploy'],
                ['name' => 'Tiket Masalah Teknis Harian', 'route' => '/general-affair/helpdesk/tickets', 'icon' => 'HelpCircle', 'category' => 'IT HelpDesk Pro', 'description' => 'Wifi, printer, PC, jaringan matriks prioritas'],
                ['name' => 'Papan Antrean Teknisi IT', 'route' => '/general-affair/helpdesk/queues', 'icon' => 'Kanban', 'category' => 'IT HelpDesk Pro', 'description' => 'Papan antrean penugasan teknisi IT HelpDesk'],
                ['name' => 'Pengawasan SLA & Pause/Play', 'route' => '/general-affair/helpdesk/sla-monitoring', 'icon' => 'Clock', 'category' => 'IT HelpDesk Pro', 'description' => 'Timer tiket dan jeda saat tunggu data pemohon'],
                ['name' => 'Laporan Analitik SLA & Teknisi', 'route' => '/general-affair/helpdesk/reports', 'icon' => 'BarChart', 'category' => 'IT HelpDesk Pro', 'description' => 'Rekapitulasi performa teknisi & durasi penyelesaian'],

                // 7. Daily Log, Chat & Administrasi
                ['name' => 'Daily Log & Timesheet Teknisi', 'route' => '/general-affair/daily-logs', 'icon' => 'FileEdit', 'category' => 'Administrasi & Utilitas', 'description' => 'Rekapitulasi durasi kerja harian staf & teknisi'],
                ['name' => 'Floating Chat Tiket Real-Time', 'route' => '/general-affair/chat', 'icon' => 'MessageSquare', 'category' => 'Administrasi & Utilitas', 'description' => 'Sistem pesan internal antar pemohon & teknisi'],
                ['name' => 'Master Barang ATK/RT & Satuan', 'route' => '/general-affair/master-items', 'icon' => 'Boxes', 'category' => 'Administrasi & Utilitas', 'description' => 'Katalog resmi barang kantor & satuan unit UOM'],
                ['name' => 'Master Pengguna & Tanda Tangan', 'route' => '/general-affair/admin/users', 'icon' => 'Users', 'category' => 'Administrasi & Utilitas', 'description' => 'Pendaftaran pengguna, role & spesimen tanda tangan'],
                ['name' => 'Audit Log Aktivitas Sistem GA', 'route' => '/general-affair/admin/activity-logs', 'icon' => 'History', 'category' => 'Administrasi & Utilitas', 'description' => 'Jejak audit seluruh perubahan operasional GA'],
            ],
            'hris' => [
                // 1. Master Organisasi & Karyawan
                ['name' => 'Overview HRIS Dashboard', 'route' => '/hris', 'icon' => 'LayoutDashboard', 'category' => 'Master Karyawan & Organisasi', 'description' => 'Dashboard Utama & Operational Control Tower'],
                ['name' => 'Master Karyawan', 'route' => '/hris/employees', 'icon' => 'Users', 'category' => 'Master Karyawan & Organisasi', 'description' => 'Biodata lengkap, NIK, golongan grade, mutasi'],
                ['name' => 'Master 9 Departemen Inti', 'route' => '/hris/departments', 'icon' => 'Building2', 'category' => 'Master Karyawan & Organisasi', 'description' => 'Struktur hierarki departemen, Manager & pagu target'],
                ['name' => 'Verifikasi Wajah Biometrik', 'route' => '/hris/face-enrollment', 'icon' => 'ScanFace', 'category' => 'Master Karyawan & Organisasi', 'description' => 'Registrasi 128-D descriptor wajah anti-titip absen'],
                ['name' => 'Pengajuan Profil Mandiri', 'route' => '/hris/profile-requests', 'icon' => 'UserCheck', 'category' => 'Master Karyawan & Organisasi', 'description' => 'Pembaruan data pribadi mandiri oleh karyawan'],

                // 2. Jadwal Kerja & Shift
                ['name' => 'Master Shift Kerja', 'route' => '/hris/shifts', 'icon' => 'Clock', 'category' => 'Jadwal Kerja & Roster', 'description' => 'Definisi jam masuk, pulang, toleransi & istirahat'],
                ['name' => 'Roster Penjadwalan Bulanan', 'route' => '/hris/schedules', 'icon' => 'Calendar', 'category' => 'Jadwal Kerja & Roster', 'description' => 'Pemetaan jadwal kerja individu atau kelompok'],
                ['name' => 'Pengajuan Tukar Shift', 'route' => '/hris/shift-requests', 'icon' => 'ArrowLeftRight', 'category' => 'Jadwal Kerja & Roster', 'description' => 'Alur tukar jadwal & validasi tabrakan jadwal'],
                ['name' => 'Kalender Kerja & Hari Libur', 'route' => '/hris/holidays', 'icon' => 'CalendarDays', 'category' => 'Jadwal Kerja & Roster', 'description' => 'Hari libur nasional & hari kerja efektif bulanan'],
                ['name' => 'Geofencing GPS Lokasi Kantor', 'route' => '/hris/office-locations', 'icon' => 'MapPin', 'category' => 'Jadwal Kerja & Roster', 'description' => 'Koordinat GPS & radius toleransi kantor/pabrik'],

                // 3. Presensi & Disiplin Waktu
                ['name' => 'Presensi GPS & Wajah', 'route' => '/hris/attendance', 'icon' => 'CalendarCheck', 'category' => 'Presensi & Disiplin', 'description' => 'Smart check-in/out, anti mock GPS & verifikasi wajah'],
                ['name' => 'Presensi Ibadah Spiritual', 'route' => '/hris/prayer-attendances', 'icon' => 'HeartHandshake', 'category' => 'Presensi & Disiplin', 'description' => 'Pencatatan kehadiran sholat berjamaah harian'],
                ['name' => 'Izin Terlambat Datang', 'route' => '/hris/late-arrival-requests', 'icon' => 'AlertCircle', 'category' => 'Presensi & Disiplin', 'description' => 'Dispensasi datang terlambat dengan bukti'],
                ['name' => 'Izin Pulang Cepat', 'route' => '/hris/early-leave-requests', 'icon' => 'LogOut', 'category' => 'Presensi & Disiplin', 'description' => 'Izin meninggalkan tempat kerja sebelum jam shift usai'],
                ['name' => 'Sanksi, Pelanggaran & SP', 'route' => '/hris/sanctions', 'icon' => 'AlertOctagon', 'category' => 'Presensi & Disiplin', 'description' => 'Penerbitan SP 1, 2, 3, teguran & penalti remunerasi'],
                ['name' => 'Audit Log & Rekap Presensi', 'route' => '/hris/attendance-audit-logs', 'icon' => 'FileSpreadsheet', 'category' => 'Presensi & Disiplin', 'description' => 'Jejak audit perubahan absensi & rekap harian/bulanan'],

                // 4. Cuti & Kompensasi Waktu
                ['name' => 'Pengajuan Cuti Online', 'route' => '/hris/leaves', 'icon' => 'CalendarOff', 'category' => 'Cuti & Waktu Kerja', 'description' => 'Permohonan cuti tahunan, sakit, melahirkan berjenjang'],
                ['name' => 'Kategori & Saldo Kuota Cuti', 'route' => '/hris/leave-categories', 'icon' => 'Tag', 'category' => 'Cuti & Waktu Kerja', 'description' => 'Pengurangan saldo otomatis & pencegahan overdraft'],
                ['name' => 'Cuti Bersama Perusahaan', 'route' => '/hris/bulk-day-off-events', 'icon' => 'Users2', 'category' => 'Cuti & Waktu Kerja', 'description' => 'Penjadwalan libur massal pemotong cuti tahunan'],
                ['name' => 'Day Bank System', 'route' => '/hris/day-banks', 'icon' => 'Landmark', 'category' => 'Cuti & Waktu Kerja', 'description' => 'Konversi kerja lembur libur menjadi saldo hari'],
                ['name' => 'Surat Perintah Lembur (SPL)', 'route' => '/hris/overtime', 'icon' => 'Timer', 'category' => 'Cuti & Waktu Kerja', 'description' => 'SPL formula Depnaker RI & multi-tier approval'],
                ['name' => 'Time Bank Engine', 'route' => '/hris/time-banks', 'icon' => 'Hourglass', 'category' => 'Cuti & Waktu Kerja', 'description' => 'Deposit, Debt, Debt Payment, Withdraw jam kerja'],

                // 5. Kaizen, Pelatihan & MCU
                ['name' => 'Timesheet Aktivitas Harian', 'route' => '/hris/activities', 'icon' => 'ClipboardList', 'category' => 'Kaizen, Training & MCU', 'description' => 'Pelaporan tugas harian mandiri & validasi atasan'],
                ['name' => 'Kaizen Kizuki & Ide Perbaikan', 'route' => '/hris/kizuki', 'icon' => 'Lightbulb', 'category' => 'Kaizen, Training & MCU', 'description' => 'Continuous improvement & laporan anomali area'],
                ['name' => 'Sistem Poin & Reward Voucher', 'route' => '/hris/points', 'icon' => 'Award', 'category' => 'Kaizen, Training & MCU', 'description' => 'Poin keaktifan, presensi sempurna & klaim hadiah'],
                ['name' => 'Manajemen Pelatihan', 'route' => '/hris/trainings', 'icon' => 'GraduationCap', 'category' => 'Kaizen, Training & MCU', 'description' => 'Program pelatihan internal/eksternal & peserta'],
                ['name' => 'Evaluasi Kirkpatrick 3-Level', 'route' => '/hris/training-evaluations', 'icon' => 'CheckSquare', 'category' => 'Kaizen, Training & MCU', 'description' => 'L1 Reaksi, L2 Pembelajaran, L3 Perilaku kerja'],
                ['name' => 'Rencana Aksi Pasca Pelatihan', 'route' => '/hris/training-action-plans', 'icon' => 'ListChecks', 'category' => 'Kaizen, Training & MCU', 'description' => 'Monitoring implementasi ilmu di lapangan 1-3 bulan'],
                ['name' => 'Kesehatan Kerja & MCU', 'route' => '/hris/medical-checkups', 'icon' => 'Stethoscope', 'category' => 'Kaizen, Training & MCU', 'description' => 'Pemeriksaan kesehatan berkala & status kelaikan'],
                ['name' => 'Portal Mandiri My-MCU', 'route' => '/hris/my-mcu', 'icon' => 'FileHeart', 'category' => 'Kaizen, Training & MCU', 'description' => 'Resume privat rekam medis karyawan'],

                // 6. Kinerja & Penggajian
                ['name' => 'Template & Indikator Kinerja', 'route' => '/hris/kpi-templates', 'icon' => 'Sliders', 'category' => 'Kinerja & Kompensasi', 'description' => 'Standarisasi indikator KPI per jabatan & bobot %'],
                ['name' => 'Evaluasi & Scoring KPI', 'route' => '/hris/kpis', 'icon' => 'Target', 'category' => 'Kinerja & Kompensasi', 'description' => 'Penilaian berkala atasan-bawahan & nilai akhir'],
                ['name' => 'Kalkulator Payroll (PPh 21 TER)', 'route' => '/hris/payroll', 'icon' => 'Banknote', 'category' => 'Kinerja & Kompensasi', 'description' => 'Perhitungan gaji pokok, BPJS, lembur & TER'],
                ['name' => 'Slip Gaji Elektronik (PDF)', 'route' => '/hris/payslips', 'icon' => 'FileText', 'category' => 'Kinerja & Kompensasi', 'description' => 'Slip gaji digital ber-PIN per karyawan'],
                ['name' => 'Master Golongan & Grading', 'route' => '/hris/master-golongans', 'icon' => 'Layers', 'category' => 'Kinerja & Kompensasi', 'description' => 'Standarisasi rentang upah & jalur kenaikan'],
                ['name' => 'Evaluasi Remunerasi', 'route' => '/hris/remunerations', 'icon' => 'TrendingUp', 'category' => 'Kinerja & Kompensasi', 'description' => 'Tinjauan kenaikan upah berbasis performa & masa kerja'],
                ['name' => 'Matriks Kelayakan Promosi', 'route' => '/hris/promotions', 'icon' => 'BadgePercent', 'category' => 'Kinerja & Kompensasi', 'description' => 'Algoritma kesiapan kenaikan jabatan objektif'],

                // 7. Strategic BSC L3 & L4 Engine
                ['name' => '35 Sasaran Mutu Departemen', 'route' => '/hris/strategic/objectives', 'icon' => 'Compass', 'category' => 'Strategic BSC L3 & L4', 'description' => 'Sasaran mutu operasional 9 departemen & rumus dinamis'],
                ['name' => 'Program Kerja & Action Plan (L4)', 'route' => '/hris/strategic/work-programs', 'icon' => 'Briefcase', 'category' => 'Strategic BSC L3 & L4', 'description' => '86 Program Kerja & 258 Action Plan Lead Measure'],
                ['name' => 'Bank Data Staging Finance', 'route' => '/hris/strategic/finance-variables', 'icon' => 'Database', 'category' => 'Strategic BSC L3 & L4', 'description' => 'Penampung data metrik moneter dari financea'],
                ['name' => 'Feed Produktivitas Headcount', 'route' => '/hris/strategic/productivity-feeds', 'icon' => 'BarChart', 'category' => 'Strategic BSC L3 & L4', 'description' => 'Perhitungan otomatis TKL, TKTL & Jam Efektif'],

                // 8. Fasilitas, SOP & Asisten AI
                ['name' => 'Work Order Pemeliharaan', 'route' => '/hris/work-orders', 'icon' => 'Wrench', 'category' => 'Fasilitas, SOP & AI', 'description' => 'Tiket perbaikan fasilitas & mesin ke teknik/GA'],
                ['name' => 'Form Permintaan Uang/Bahan (FPU)', 'route' => '/hris/fpu-requests', 'icon' => 'Coins', 'category' => 'Fasilitas, SOP & AI', 'description' => 'Kebutuhan anggaran/material perbaikan fasilitas'],
                ['name' => 'Handover Serah Terima Pekerjaan', 'route' => '/hris/wo-handovers', 'icon' => 'CheckCircle', 'category' => 'Fasilitas, SOP & AI', 'description' => 'Berita acara serah terima pekerjaan tuntas'],
                ['name' => 'Pusat Kebijakan Perusahaan (PP)', 'route' => '/hris/company-policies', 'icon' => 'Shield', 'category' => 'Fasilitas, SOP & AI', 'description' => 'Visi-misi, kebijakan mutu halal & aturan kerja'],
                ['name' => 'Buku Panduan Digital (SOP)', 'route' => '/hris/manual-books', 'icon' => 'BookOpen', 'category' => 'Fasilitas, SOP & AI', 'description' => 'Repositori panduan kerja SOP PDF terpusat'],
                ['name' => 'AI HR Assistant (Gemini)', 'route' => '/hris/ai-assistant', 'icon' => 'Sparkles', 'category' => 'Fasilitas, SOP & AI', 'description' => 'Asisten cerdas tanya-jawab regulasi HR & SOP'],
            ],
            'procurement' => [
                // 1. Purchase Request & Workflow
                ['name' => 'Dashboard Overview Pengadaan', 'route' => '/procurement', 'icon' => 'LayoutDashboard', 'category' => 'Purchase Request & Workflow', 'description' => 'Dashboard Analytics & Status Pengadaan'],
                ['name' => 'Purchase Requests (PR)', 'route' => '/procurement/purchase-requests', 'icon' => 'FileCheck', 'category' => 'Purchase Request & Workflow', 'description' => 'PR Operasional & Non-Operasional'],
                ['name' => 'Granular 8-Step Item Workflow', 'route' => '/procurement/item-workflow', 'icon' => 'ListTree', 'category' => 'Purchase Request & Workflow', 'description' => 'Pelacakan siklus 8 tahap independen per item'],
                ['name' => 'Input Estimasi Harga Pasar', 'route' => '/procurement/estimates', 'icon' => 'Tag', 'category' => 'Purchase Request & Workflow', 'description' => 'Input harga estimasi pasar, quotation & vendor'],
                ['name' => 'Riwayat Penolakan & Bulk Revisi', 'route' => '/procurement/revisions', 'icon' => 'RefreshCw', 'category' => 'Purchase Request & Workflow', 'description' => 'Auto-generate PR baru untuk item ditolak'],

                // 2. Pengadaan Khusus (PR Bon)
                ['name' => 'Daftar Berkas PR Bon', 'route' => '/procurement/pr-bons', 'icon' => 'Receipt', 'category' => 'Pengadaan Khusus (PR Bon)', 'description' => 'Pengadaan kas kecil, toko lokal & darurat'],
                ['name' => 'Staging Bon Lintas PR', 'route' => '/procurement/pr-bons/staging', 'icon' => 'Layers', 'category' => 'Pengadaan Khusus (PR Bon)', 'description' => 'Pengelompokan item is_bon lintas nomor PR'],
                ['name' => 'Portal Keputusan GM PR Bon', 'route' => '/procurement/pr-bons/approval', 'icon' => 'CheckCircle2', 'category' => 'Pengadaan Khusus (PR Bon)', 'description' => 'Halaman terdedikasi telaah & otorisasi GM'],
                ['name' => 'Cetak Berkas Form Bon Resmi', 'route' => '/procurement/pr-bons/print', 'icon' => 'Printer', 'category' => 'Pengadaan Khusus (PR Bon)', 'description' => 'Format cetak resmi tanda tangan Procurement & GM'],

                // 3. Purchase Order & Integrasi Odoo
                ['name' => 'Daftar PO Internal', 'route' => '/procurement/purchase-orders', 'icon' => 'ShoppingCart', 'category' => 'Purchase Order & Odoo', 'description' => 'PO non-Odoo & rekaman pesanan internal'],
                ['name' => 'Sinkronisasi PO Odoo (JSON-RPC)', 'route' => '/procurement/odoo-sync', 'icon' => 'Cpu', 'category' => 'Purchase Order & Odoo', 'description' => 'Pembuatan PO langsung ke modul purchase Odoo'],
                ['name' => 'Manual Odoo PO Bypass', 'route' => '/procurement/odoo-manual', 'icon' => 'ShieldAlert', 'category' => 'Purchase Order & Odoo', 'description' => 'Pencatatan nomor PO manual & audit trail'],

                // 4. Logistik & Penerimaan Fisik
                ['name' => 'Rencana Pengiriman (Delivery Plans)', 'route' => '/procurement/deliveries', 'icon' => 'Truck', 'category' => 'Logistik & Penerimaan Fisik', 'description' => 'Jadwal kedatangan parsial & pelacakan reschedule'],
                ['name' => 'Penerimaan Fisik & Surat Jalan', 'route' => '/procurement/deliveries/receipts', 'icon' => 'ClipboardCheck', 'category' => 'Logistik & Penerimaan Fisik', 'description' => 'Pencatatan kedatangan riil & unggah surat jalan'],
                ['name' => 'Verifikasi Holding Delivery', 'route' => '/procurement/deliveries/holding', 'icon' => 'Building', 'category' => 'Logistik & Penerimaan Fisik', 'description' => 'Konfirmasi fisik khusus pengadaan holding'],
                ['name' => 'Retur Barang Rusak & Penggantian', 'route' => '/procurement/deliveries/retur', 'icon' => 'CornerUpLeft', 'category' => 'Logistik & Penerimaan Fisik', 'description' => 'Barang ditolak QC & tanda terima pengganti vendor'],

                // 5. Kontrol Pagu Anggaran
                ['name' => 'Cek Pagu Real-Time (FAT API)', 'route' => '/procurement/budgets', 'icon' => 'Wallet', 'category' => 'Kontrol Pagu Anggaran', 'description' => 'Validasi pagu sebelum PR disetujui (FAT API)'],
                ['name' => 'Staging Pagu & Komitmen Belanja', 'route' => '/procurement/staging-pagu', 'icon' => 'FileSpreadsheet', 'category' => 'Kontrol Pagu Anggaran', 'description' => 'Pemantauan komitmen expense staging ke database FAT'],
                ['name' => 'Panel Manajemen Pagu Departemen', 'route' => '/procurement/settings/finance-budget', 'icon' => 'Landmark', 'category' => 'Kontrol Pagu Anggaran', 'description' => 'Konfigurasi pagu tahunan & ekspor anggaran'],
                ['name' => 'Otorisasi Budget Bypass', 'route' => '/procurement/budgets/bypass', 'icon' => 'ShieldCheck', 'category' => 'Kontrol Pagu Anggaran', 'description' => 'Otorisasi belanja mendesak melebihi pagu aktif'],

                // 6. Standar Harga & AVL
                ['name' => 'Katalog Standar Harga Barang', 'route' => '/procurement/standard-prices', 'icon' => 'DollarSign', 'category' => 'Standar Harga & Rekanan', 'description' => 'Standarisasi harga acuan Material & Supporting'],
                ['name' => 'Approved Vendor List (AVL)', 'route' => '/procurement/vendors', 'icon' => 'Building2', 'category' => 'Standar Harga & Rekanan', 'description' => 'Daftar vendor resmi terdaftar & performa kirim'],
                ['name' => 'Sinkronisasi Harga Master & Odoo', 'route' => '/procurement/standard-prices/sync', 'icon' => 'RefreshCw', 'category' => 'Standar Harga & Rekanan', 'description' => 'Update harga pasar dari riwayat PO Odoo terakhir'],

                // 7. Sasaran Mutu & Integrasi HRIS
                ['name' => 'Sasaran Mutu Procurement 2026', 'route' => '/procurement/sasaran-mutu', 'icon' => 'Target', 'category' => 'Sasaran Mutu & Integrasi', 'description' => '4 Sasaran Mutu bobot 25% ISO 9001 korporasi'],
                ['name' => 'Webhook & Log Loss Time Mesin', 'route' => '/procurement/loss-time-logs', 'icon' => 'Timer', 'category' => 'Sasaran Mutu & Integrasi', 'description' => 'Catatan kejadian downtime akibat pasokan bahan'],
                ['name' => 'Sinkronisasi Metrik ke HRIS API', 'route' => '/procurement/hris-sync', 'icon' => 'Users', 'category' => 'Sasaran Mutu & Integrasi', 'description' => 'Pengiriman data SLA & kinerja ke tim HRIS'],

                // 8. Master Data & Laporan
                ['name' => 'Laporan Terpadu & Ekspor Data', 'route' => '/procurement/reports', 'icon' => 'FileText', 'category' => 'Master & Laporan', 'description' => 'Laporan berfilter multi-kriteria & ekspor Excel'],
                ['name' => 'Master Barang & Satuan (UOM)', 'route' => '/procurement/master-items', 'icon' => 'Package', 'category' => 'Master & Laporan', 'description' => 'Katalog barang terstandar & sinkronisasi Odoo'],
                ['name' => 'Pengaturan Kredensial & Tanda Tangan', 'route' => '/procurement/settings', 'icon' => 'Settings', 'category' => 'Master & Laporan', 'description' => 'Kredensial Odoo, Finance, HRIS & tanda tangan'],
            ],
            'production', 'produksi' => [
                // 1. Dashboard & Analisis
                ['name' => 'Dashboard Analytics & KPI OEE', 'route' => '/production', 'icon' => 'LayoutDashboard', 'category' => 'Dashboard & Analisis', 'description' => 'Rata-rata OEE, Availability, Performance & Yield'],
                ['name' => 'Line Performance Heat Grid', 'route' => '/production/analytics/heat-grid', 'icon' => 'Grid', 'category' => 'Dashboard & Analisis', 'description' => 'Volume output per Line pada Mixing, Filling, Packing'],
                ['name' => 'Grafik Pareto Downtime Mesin', 'route' => '/production/analytics/pareto', 'icon' => 'BarChart2', 'category' => 'Dashboard & Analisis', 'description' => 'Pemeringkatan alasan gangguan & durasi henti'],
                ['name' => 'Analisis Reject & Cacat Produk', 'route' => '/production/analytics/rejects', 'icon' => 'PieChart', 'category' => 'Dashboard & Analisis', 'description' => 'Reject rate %, sebaran varian produk & tren'],

                // 2. Master Data & Konfigurasi
                ['name' => 'Master Produk (FG, RM, PM, WIP)', 'route' => '/production/master/products', 'icon' => 'Package', 'category' => 'Master Data Pabrik', 'description' => 'Batas safety, minimum, maximum & id produk Odoo'],
                ['name' => 'Master Mesin Pabrik', 'route' => '/production/master/machines', 'icon' => 'Cpu', 'category' => 'Master Data Pabrik', 'description' => 'Daftar mesin & Cycle Time standar per pcs'],
                ['name' => 'Master Jalur / Line Produksi', 'route' => '/production/master/lines', 'icon' => 'GitCommit', 'category' => 'Master Data Pabrik', 'description' => 'Penomoran dan nama jalur perakitan lantai pabrik'],
                ['name' => 'Master Alasan Downtime', 'route' => '/production/master/downtime-reasons', 'icon' => 'AlertTriangle', 'category' => 'Master Data Pabrik', 'description' => 'Kamus henti kerja, input manual & durasi patokan'],
                ['name' => 'Parameter & Multiplier Manpower', 'route' => '/production/master/settings', 'icon' => 'Sliders', 'category' => 'Master Data Pabrik', 'description' => 'Target unit per MP, target IRA & toleransi usage'],
                ['name' => 'Audit Log Riwayat Pabrik', 'route' => '/production/master/history', 'icon' => 'Clock', 'category' => 'Master Data Pabrik', 'description' => 'Rekam jejak perubahan data kritikal manufaktur'],

                // 3. Perencanaan PPIC (WPS)
                ['name' => 'Weekly Production Schedule (WPS)', 'route' => '/production/schedules', 'icon' => 'Calendar', 'category' => 'Perencanaan PPIC (WPS)', 'description' => 'Jadwal mingguan List View & Board Kanban'],
                ['name' => 'Tarik MO Terkonfirmasi Odoo ERP', 'route' => '/production/schedules/odoo-sync', 'icon' => 'RefreshCw', 'category' => 'Perencanaan PPIC (WPS)', 'description' => 'Sinkronisasi Manufacturing Order status confirmed'],
                ['name' => 'Manajemen Packing Hold', 'route' => '/production/schedules/packing-hold', 'icon' => 'PauseCircle', 'category' => 'Perencanaan PPIC (WPS)', 'description' => 'Penundaan pengemasan sekunder tanpa batalkan batch'],

                // 4. Eksekusi Lantai Pabrik & Timer
                ['name' => 'Laporan Harian Produksi (LHP)', 'route' => '/production/daily-reports', 'icon' => 'ClipboardList', 'category' => 'Eksekusi & Timer', 'description' => 'Rekam realitas output fisik & efisiensi kerja'],
                ['name' => 'Panel Kontrol Timer Produksi', 'route' => '/production/timer-console', 'icon' => 'Timer', 'category' => 'Eksekusi & Timer', 'description' => 'Start, Pause, Resume dengan dialog alasan & End'],
                ['name' => 'Estafet Batch Tracking Linier', 'route' => '/production/batches', 'icon' => 'Layers', 'category' => 'Eksekusi & Timer', 'description' => 'Pelacakan linier Mixing -> Filling -> Packing'],
                ['name' => 'Validasi & Kunci Supervisor', 'route' => '/production/daily-reports/approvals', 'icon' => 'Lock', 'category' => 'Eksekusi & Timer', 'description' => 'Penguncian LHP agar tidak dapat diubah operator'],

                // 5. Reject, Scrap & Pemborosan
                ['name' => 'Pencatatan Detail Reject & Cacat', 'route' => '/production/scrap', 'icon' => 'AlertOctagon', 'category' => 'Reject & Scrap', 'description' => 'Volume unit reject, jenis defect & material'],
                ['name' => 'Recipe Component Breakdown Reject', 'route' => '/production/scrap/breakdown', 'icon' => 'ListTree', 'category' => 'Reject & Scrap', 'description' => 'Komponen bahan resep BOM yang ikut terbuang'],
                ['name' => 'Sinkronisasi Odoo Stock Scrap', 'route' => '/production/scrap/odoo-sync', 'icon' => 'Cpu', 'category' => 'Reject & Scrap', 'description' => 'Koreksi saldo persediaan modul stock.scrap Odoo'],
                ['name' => 'Scrap Material Gudang Pabrik', 'route' => '/production/scrap/warehouse', 'icon' => 'Trash2', 'category' => 'Reject & Scrap', 'description' => 'Kerusakan penanganan & kedaluwarsa di gudang'],

                // 6. SCM & Persediaan Pabrik
                ['name' => 'Struktur Bill of Materials (BOM)', 'route' => '/production/boms', 'icon' => 'FileSpreadsheet', 'category' => 'SCM & Persediaan Pabrik', 'description' => 'Explosion resep & bahan kemas primer/sekunder'],
                ['name' => 'Saldo Stok & Visualisasi Safe Level', 'route' => '/production/stocks', 'icon' => 'Warehouse', 'category' => 'SCM & Persediaan Pabrik', 'description' => 'Stok per produk, nomor batch, expired & lokasi'],
                ['name' => 'Material Usage (Konsumsi Riil)', 'route' => '/production/material-usage', 'icon' => 'ArrowRightCircle', 'category' => 'SCM & Persediaan Pabrik', 'description' => 'Analisis variansi kuantitas aktual vs standar BOM'],
                ['name' => 'Stock Opname Persediaan (IRA %)', 'route' => '/production/stock-opname', 'icon' => 'Scale', 'category' => 'SCM & Persediaan Pabrik', 'description' => 'Cycle count gudang & akurasi pencatatan persediaan'],
                ['name' => 'Cetak Kartu Stelling Fisik Gudang', 'route' => '/production/stock-opname/stelling', 'icon' => 'Printer', 'category' => 'SCM & Persediaan Pabrik', 'description' => 'Cetak formulir kartu stelling fisik ke PDF'],

                // 7. Pengiriman & Logistik Fleet
                ['name' => 'Rencana Pengiriman (Delivery Plans)', 'route' => '/production/deliveries', 'icon' => 'Truck', 'category' => 'Pengiriman & Logistik Fleet', 'description' => 'Penjadwalan ekspedisi barang jadi ke pelanggan'],
                ['name' => 'Pre-Flight Stock Check Barang Jadi', 'route' => '/production/deliveries/stock-check', 'icon' => 'ShieldCheck', 'category' => 'Pengiriman & Logistik Fleet', 'description' => 'Validasi saldo fisik FG sebelum cetak dokumen'],
                ['name' => 'Manajemen Kendaraan & Driver (Fleet)', 'route' => '/production/deliveries/fleet', 'icon' => 'Car', 'category' => 'Pengiriman & Logistik Fleet', 'description' => 'Nomor polisi kendaraan, kapasitas & kontak supir'],
                ['name' => 'Generator Surat Jalan, Faktur & COA', 'route' => '/production/deliveries/documents', 'icon' => 'FileText', 'category' => 'Pengiriman & Logistik Fleet', 'description' => 'Berkas legal distribusi PDF siap pakai'],
                ['name' => 'Pengukuran Kinerja Delivery (OTIF)', 'route' => '/production/deliveries/kpi', 'icon' => 'Award', 'category' => 'Pengiriman & Logistik Fleet', 'description' => 'On-time, in-full, damage-free & complaint rate'],

                // 8. Integrasi Eksternal
                ['name' => 'Konektor Odoo ERP (XML-RPC)', 'route' => '/production/integrations/odoo', 'icon' => 'Cpu', 'category' => 'Integrasi Eksternal', 'description' => 'Penyelarasan MO, BOM, stok, scrap & pesanan'],
                ['name' => 'Konektor Kinerja ke HRIS', 'route' => '/production/integrations/hris', 'icon' => 'Users', 'category' => 'Integrasi Eksternal', 'description' => 'Feed OEE, efisiensi & jam henti ke HRIS'],
                ['name' => 'Konektor Biaya ke Finance App', 'route' => '/production/integrations/finance', 'icon' => 'DollarSign', 'category' => 'Integrasi Eksternal', 'description' => 'Feed unit terproduksi & rugi downtime ke Finance'],
            ],
            'quality' => [
                // 1. Master Induk Dokumen Mutu
                ['name' => 'Overview Mutu QMS', 'route' => '/quality', 'icon' => 'LayoutDashboard', 'category' => 'Master Dokumen Mutu', 'description' => 'Pusat Kendali Mutu Farmasi & ISO 9001/CPOB'],
                ['name' => 'Piramida 4-Tingkat Mutu', 'route' => '/quality/documents', 'icon' => 'FileCheck', 'category' => 'Master Dokumen Mutu', 'description' => 'Manual Mutu, SOP, IK, dan Formulir Rekaman'],
                ['name' => 'Struktur Folder Bersarang', 'route' => '/quality/documents/folders', 'icon' => 'FolderTree', 'category' => 'Master Dokumen Mutu', 'description' => 'Parent-child tree, warna badge & ikon folder'],
                ['name' => 'Riwayat Revisi Dokumen Mutu', 'route' => '/quality/documents/revisions', 'icon' => 'History', 'category' => 'Master Dokumen Mutu', 'description' => 'Pencatatan riwayat revisi nomor & change notes'],
                ['name' => 'Tautan Dokumen Terstruktur', 'route' => '/quality/documents/links', 'icon' => 'Link', 'category' => 'Master Dokumen Mutu', 'description' => 'Penyimpanan multi-link Google Drive & Cloud'],
                ['name' => 'Kustomisasi Master Options', 'route' => '/quality/documents/master-options', 'icon' => 'Sliders', 'category' => 'Master Dokumen Mutu', 'description' => '12 Ruang Lingkup SOP, Departemen & Rak Arsip'],
                ['name' => 'Impor & Sinkronisasi Sheets', 'route' => '/quality/documents/sync', 'icon' => 'RefreshCw', 'category' => 'Master Dokumen Mutu', 'description' => 'Impor massal Excel & Google Sheets API sync'],

                // 2. Change Request (CRA & CRB)
                ['name' => 'Daftar Usulan Perubahan', 'route' => '/quality/change-requests', 'icon' => 'GitCommit', 'category' => 'Change Request (CR)', 'description' => 'Pengendalian alur perubahan formula & fasilitas'],
                ['name' => 'Pengajuan CRA (Mayor dengan FMEA)', 'route' => '/quality/change-requests/cra', 'icon' => 'AlertTriangle', 'category' => 'Change Request (CR)', 'description' => 'Analisis risiko Severity, Occurrence, Detection RPN'],
                ['name' => 'Pengajuan CRB (Minor tanpa FMEA)', 'route' => '/quality/change-requests/crb', 'icon' => 'FileEdit', 'category' => 'Change Request (CR)', 'description' => 'Perubahan minor alur approval hingga Head QA'],
                ['name' => 'Kajian Risiko Multi-PIC Dept', 'route' => '/quality/change-requests/pic-assessments', 'icon' => 'Users', 'category' => 'Change Request (CR)', 'description' => 'Kajian teknis PIC R&D, Produksi, QC, Teknik & SCM'],
                ['name' => 'Verifikasi Stage 1 & Stage 2', 'route' => '/quality/change-requests/stages', 'icon' => 'CheckSquare', 'category' => 'Change Request (CR)', 'description' => 'Stage 1 Evaluasi & Stage 2 Bukti Pelaksanaan'],

                // 3. Pelaporan Deviasi (DR)
                ['name' => 'Daftar Laporan Deviasi', 'route' => '/quality/deviations', 'icon' => 'AlertOctagon', 'category' => 'Pelaporan Deviasi (DR)', 'description' => 'Temuan ketidaksesuaian operasional & insiden teknis'],
                ['name' => 'Form Pelaporan Awal Deviasi', 'route' => '/quality/deviations/create', 'icon' => 'FilePlus', 'category' => 'Pelaporan Deviasi (DR)', 'description' => 'Kategori penyimpangan, domino effect & immediate action'],
                ['name' => 'Penomoran Ganda Otomatis (DR & PK)', 'route' => '/quality/deviations/numbering', 'icon' => 'Tag', 'category' => 'Pelaporan Deviasi (DR)', 'description' => 'Penomoran resmi DR/xxx dan PK/xxx per tahun'],
                ['name' => 'Penunjukan Atasan Mengetahui', 'route' => '/quality/deviations/superiors', 'icon' => 'UserCheck', 'category' => 'Pelaporan Deviasi (DR)', 'description' => 'Tanggapan atasan terkait & tanda tangan digital'],

                // 4. Penyelidikan Ketidaksesuaian (Investigation)
                ['name' => 'Hub Monitoring Penyelidikan', 'route' => '/quality/deviations-investigations', 'icon' => 'Search', 'category' => 'Penyelidikan Ketidaksesuaian', 'description' => 'Monitoring investigasi mendalam akar penyebab'],
                ['name' => 'Diagram Tulang Ikan (6M Fishbone)', 'route' => '/quality/deviations-investigations/fishbone', 'icon' => 'GitFork', 'category' => 'Penyelidikan Ketidaksesuaian', 'description' => 'Man, Machine, Method, Material, Milieu, Measurement SVG'],
                ['name' => 'Analisis Akar Masalah 5-Why', 'route' => '/quality/deviations-investigations/five-whys', 'icon' => 'HelpCircle', 'category' => 'Penyelidikan Ketidaksesuaian', 'description' => 'Penelusuran bertahap hingga menemukan root cause'],
                ['name' => 'Evaluasi Tindakan & Scoring FMEA', 'route' => '/quality/deviations-investigations/fmea', 'icon' => 'Activity', 'category' => 'Penyelidikan Ketidaksesuaian', 'description' => 'Matriks evaluasi dengan skoring 1, 3, 9'],
                ['name' => 'Keputusan QA (Auto-CAPA / Reject)', 'route' => '/quality/deviations-investigations/decisions', 'icon' => 'CheckCircle2', 'category' => 'Penyelidikan Ketidaksesuaian', 'description' => 'Approve memicu record CAPA / Reject kembali inisiator'],

                // 5. CAPA & Penutupan Masalah
                ['name' => 'Daftar Program CAPA', 'route' => '/quality/capa', 'icon' => 'ShieldCheck', 'category' => 'CAPA & Tindakan Korektif', 'description' => 'Corrective and Preventive Action terintegrasi deviasi'],
                ['name' => 'Sub-CAPA Multi-Tindakan', 'route' => '/quality/capa/actions', 'icon' => 'ListTree', 'category' => 'CAPA & Tindakan Korektif', 'description' => 'Tindakan korektif vs preventif multi-PIC & deadline'],
                ['name' => 'Pelaksanaan & Upload Bukti Lapangan', 'route' => '/quality/capa/evidences', 'icon' => 'UploadCloud', 'category' => 'CAPA & Tindakan Korektif', 'description' => 'PIC unggah berkas/foto bukti tindakan di lapangan'],
                ['name' => 'Verifikasi Efektivitas & Close CAPA', 'route' => '/quality/capa/verifications', 'icon' => 'Award', 'category' => 'CAPA & Tindakan Korektif', 'description' => 'Verifikasi bukti oleh QA & penutupan permanen'],

                // 6. Tanda Tangan & Cetak Form A4
                ['name' => 'Canvas Signature Pad', 'route' => '/quality/signatures', 'icon' => 'PenTool', 'category' => 'Tanda Tangan & Cetak A4', 'description' => 'Tanda tangan touchscreen & auto-trim whitespace'],
                ['name' => 'Cetak Form Standar CRA & CRB', 'route' => '/quality/print/change-requests', 'icon' => 'Printer', 'category' => 'Tanda Tangan & Cetak A4', 'description' => 'Form usulan perubahan resmi dengan matriks approval'],
                ['name' => 'Cetak Formulir Laporan Deviasi (DR)', 'route' => '/quality/print/deviations', 'icon' => 'FileText', 'category' => 'Tanda Tangan & Cetak A4', 'description' => 'Dokumen cetak DR siap audit CPOB'],
                ['name' => 'Cetak Penyelidikan Ketidaksesuaian', 'route' => '/quality/print/investigations', 'icon' => 'BookOpen', 'category' => 'Tanda Tangan & Cetak A4', 'description' => 'Formulir resmi PK CM-09/QA/003D.01 dengan diagram 6M'],

                // 7. Super Admin & Konfigurasi
                ['name' => 'Manajemen Pengguna Mutu', 'route' => '/quality/admin/users', 'icon' => 'Users', 'category' => 'Admin Mutu & Konfigurasi', 'description' => 'Hak akses Initiator, QA, Head QA, OM, GM, Superadmin'],
                ['name' => 'Kop Surat & Logo Dokumen Cetak', 'route' => '/quality/admin/branding', 'icon' => 'Image', 'category' => 'Admin Mutu & Konfigurasi', 'description' => 'Kustomisasi identitas perusahaan dokumen cetak'],
                ['name' => 'Master Nomor Dokumen Mutu', 'route' => '/quality/admin/settings', 'icon' => 'Settings', 'category' => 'Admin Mutu & Konfigurasi', 'description' => 'Konfigurasi penomoran dokumen penyelidikan default'],
            ],
            'rnd' => [
                // 1. Dashboard & Pipeline Control
                ['name' => 'Timeline Control Center', 'route' => '/rnd', 'icon' => 'LayoutDashboard', 'category' => 'Dashboard & Pipeline', 'description' => 'Sentral kendali aktivitas seluruh siklus hidup riset NPD'],
                ['name' => 'Pipeline & Dokumen Terdaftar', 'route' => '/rnd/timeline/pipeline', 'icon' => 'ListTree', 'category' => 'Dashboard & Pipeline', 'description' => 'Tabel interaktif seluruh dokumen R&D lintas modul'],
                ['name' => 'Product Progress Cards Grid', 'route' => '/rnd/timeline/product-cards', 'icon' => 'Grid', 'category' => 'Dashboard & Pipeline', 'description' => 'Status kelengkapan 13 modul produk secara visual'],
                ['name' => 'Antrean Pending & Beban Analis', 'route' => '/rnd/timeline/my-pending', 'icon' => 'Inbox', 'category' => 'Dashboard & Pipeline', 'description' => 'Filter antrean approval staff, Manager OM & Direksi GM'],
                ['name' => 'Audit Feed Aktivitas Tim R&D', 'route' => '/rnd/timeline/audit-feed', 'icon' => 'History', 'category' => 'Dashboard & Pipeline', 'description' => 'Log mutasi data real-time aktivitas tim riset'],

                // 2. Fase 1: Insight & Concept
                ['name' => 'Product Request Form (PRF)', 'route' => '/rnd/prfs', 'icon' => 'FileText', 'category' => 'Fase 1: Insight & Concept', 'description' => 'Formulir permintaan produk baru dari divisi Marketing'],
                ['name' => 'NPD Proposals & Target COGS', 'route' => '/rnd/npd-proposals', 'icon' => 'Layers', 'category' => 'Fase 1: Insight & Concept', 'description' => 'Proposal inisiasi riset, estimasi COGS & target launch'],

                // 3. Fase 2: Development — Kajian & Resep
                ['name' => 'Kajian Preformulasi', 'route' => '/rnd/preformulation-studies', 'icon' => 'Microscope', 'category' => 'Fase 2: Kajian & Formulasi', 'description' => 'Karakterisasi fisika-kimia, kelarutan & kompatibilitas'],
                ['name' => 'Quality by Design (QbD)', 'route' => '/rnd/qbds', 'icon' => 'Compass', 'category' => 'Fase 2: Kajian & Formulasi', 'description' => 'Penerapan ICH Q8, QTPP, CQA, CMA & analisis risiko'],
                ['name' => 'Formulasi Bahan Baku (RM)', 'route' => '/rnd/formulas', 'icon' => 'Calculator', 'category' => 'Fase 2: Kajian & Formulasi', 'description' => 'Resep bahan baku presisi total wajib tepat 100%'],
                ['name' => 'Eksekusi Trial RM (Uji Lab)', 'route' => '/rnd/trial-rms', 'icon' => 'FlaskConical', 'category' => 'Fase 2: Kajian & Formulasi', 'description' => 'Pencampuran & verifikasi fisika-kimia organoleptik'],
                ['name' => 'Loop Reformulasi Otomatis', 'route' => '/rnd/trial-rms/reformula', 'icon' => 'RefreshCw', 'category' => 'Fase 2: Kajian & Formulasi', 'description' => 'Auto-clone duplikasi formula V1 -> V2 menjaga silsilah'],
                ['name' => 'Trial PM Kemasan Mesin', 'route' => '/rnd/trial-pms', 'icon' => 'Boxes', 'category' => 'Fase 2: Kajian & Formulasi', 'description' => 'Uji kemasan mesin konsensus 4 departemen'],
                ['name' => 'Log Book PM (Penerimaan Sampel)', 'route' => '/rnd/logbook-pm', 'icon' => 'BookOpen', 'category' => 'Fase 2: Kajian & Formulasi', 'description' => 'Buku besar penerimaan fisik sampel kemasan supplier'],

                // 4. Fase 2: Evaluation & Testing
                ['name' => 'Evaluasi Sensori & Panelis', 'route' => '/rnd/sample-evaluations', 'icon' => 'Award', 'category' => 'Fase 2: Evaluation & Testing', 'description' => 'Uji kesukaan konsumen 5 parameter organoleptik'],
                ['name' => 'Uji Stabilitas Sediaan', 'route' => '/rnd/stability-tests', 'icon' => 'Thermometer', 'category' => 'Fase 2: Evaluation & Testing', 'description' => 'Accelerated 40°C & Real-Time 30°C interval berkala'],

                // 5. Fase 2: Pengesahan & Desain
                ['name' => 'Pengesahan Formula Resmi', 'route' => '/rnd/formula-approvals', 'icon' => 'FileCheck', 'category' => 'Fase 2: Pengesahan & Desain', 'description' => 'Pengesahan legal formula dengan matriks tanda tangan direksi'],
                ['name' => 'Desain Artwork Kemasan', 'route' => '/rnd/design-approvals', 'icon' => 'Image', 'category' => 'Fase 2: Pengesahan & Desain', 'description' => 'Persetujuan materi grafis kemasan multi-stakeholder'],

                // 6. Fase 2 & 3: Regulasi, Alih Teknologi & Komersial
                ['name' => 'Regulatory Dossier (DMS BPOM)', 'route' => '/rnd/regulatory-dossier', 'icon' => 'FolderArchive', 'category' => 'Regulasi & Alih Teknologi', 'description' => 'DMS folder bersarang registrasi izin edar & ekspor ZIP'],
                ['name' => 'Technology Transfer ke Pabrik', 'route' => '/rnd/technology-transfers', 'icon' => 'ArrowRightLeft', 'category' => 'Regulasi & Alih Teknologi', 'description' => 'Protokol transfer parameter proses lab ke komersial'],
                ['name' => 'NIE Approved (Nomor Izin Edar)', 'route' => '/rnd/nie-approvals', 'icon' => 'CheckCircle2', 'category' => 'Regulasi & Alih Teknologi', 'description' => 'Perekaman nomor izin edar resmi Badan POM RI'],
                ['name' => 'Commercial Production DMS', 'route' => '/rnd/commercial-productions', 'icon' => 'Factory', 'category' => 'Regulasi & Alih Teknologi', 'description' => 'Pengarsipan catatan CPB, CPK & rilis batch komersial'],

                // 7. Pusat Persetujuan Tunggal
                ['name' => 'Approval Center (Single Inbox)', 'route' => '/rnd/approval-center', 'icon' => 'CheckSquare', 'category' => 'Approval Center Tunggal', 'description' => 'Kotak masuk tunggal telaah OM & persetujuan GM'],

                // 8. Master Data & Integrasi Pengadaan
                ['name' => 'Master Produk & Kategori', 'route' => '/rnd/products', 'icon' => 'Package', 'category' => 'Master Data & Integrasi', 'description' => 'Klasifikasi herbal, suplemen, kosmetik & pangan'],
                ['name' => 'Master Bahan Baku & Supplier', 'route' => '/rnd/materials', 'icon' => 'Boxes', 'category' => 'Master Data & Integrasi', 'description' => 'Bahan aktif, eksipien, status trial vs commercial & CoA'],
                ['name' => 'Katalog Harga Bahan Pemasok', 'route' => '/rnd/material-suppliers', 'icon' => 'DollarSign', 'category' => 'Master Data & Integrasi', 'description' => 'Multi-supplier per bahan, fluktuasi & harga aktif'],
                ['name' => 'Integrasi Feed Harga Pengadaan', 'route' => '/rnd/procurement-prices', 'icon' => 'Cpu', 'category' => 'Master Data & Integrasi', 'description' => 'Inbound webhook PO pengadaan & manual pull API'],
                ['name' => 'Mapping Alias Bahan Pengadaan', 'route' => '/rnd/procurement-prices/mapping', 'icon' => 'Tag', 'category' => 'Master Data & Integrasi', 'description' => 'Pencocokan nama bahan feed ke master R&D & apply ke HPP'],
                ['name' => 'Administrasi & Pengaturan R&D', 'route' => '/rnd/admin/settings', 'icon' => 'Settings', 'category' => 'Master Data & Integrasi', 'description' => 'Manajemen tim analis, koneksi API sandbox & parameter'],
            ],
            'supply-chain' => [
                // 1. Master Data & Konfigurasi Dasar
                ['name' => 'Overview LogiFlow SCM', 'route' => '/supply-chain', 'icon' => 'LayoutDashboard', 'category' => 'Master Data SCM', 'description' => 'Closed-Loop Supply Chain & Manufacturing Hub'],
                ['name' => 'Master Produk (RM, PM, Bulk, FG)', 'route' => '/supply-chain/products', 'icon' => 'Package', 'category' => 'Master Data SCM', 'description' => 'Parameter safety stock, lead time & reorder point'],
                ['name' => 'Master BOM / Resep Produk', 'route' => '/supply-chain/boms', 'icon' => 'FileSpreadsheet', 'category' => 'Master Data SCM', 'description' => 'Komposisi bahan presisi 4 desimal & versioning'],
                ['name' => 'Pusat Kerja (Work Center)', 'route' => '/supply-chain/work-centers', 'icon' => 'Factory', 'category' => 'Master Data SCM', 'description' => 'Stasiun mesin pabrik, kapasitas & tarif overhead/jam'],
                ['name' => 'Master Rekanan (Vendor)', 'route' => '/supply-chain/vendors', 'icon' => 'Building2', 'category' => 'Master Data SCM', 'description' => 'Pemasok resmi, NPWP, termin TOP & performa kirim'],
                ['name' => 'Master Satuan & Parameter', 'route' => '/supply-chain/parameters', 'icon' => 'Sliders', 'category' => 'Master Data SCM', 'description' => 'UOM internasional, konversi & hierarki departemen'],

                // 2. Perencanaan & PPIC
                ['name' => 'Riwayat Data Penjualan', 'route' => '/supply-chain/sales-data', 'icon' => 'BarChart3', 'category' => 'Perencanaan & PPIC', 'description' => 'Volume riil penjualan per SKU per bulan'],
                ['name' => 'Peramalan Penjualan (Forecast)', 'route' => '/supply-chain/forecasts', 'icon' => 'TrendingUp', 'category' => 'Perencanaan & PPIC', 'description' => 'Annual & 3-6 bulan Rolling Forecast'],
                ['name' => 'Master Production Schedule (MPS)', 'route' => '/supply-chain/mps', 'icon' => 'CalendarCheck', 'category' => 'Perencanaan & PPIC', 'description' => 'Jadwal induk mingguan/bulanan SKU produk jadi'],
                ['name' => 'Kalkulator Kebutuhan Bersih (MRP)', 'route' => '/supply-chain/mrp', 'icon' => 'Cpu', 'category' => 'Perencanaan & PPIC', 'description' => 'Ledakan kebutuhan bersih material just-in-time'],
                ['name' => 'Peringatan Cerdas Persediaan', 'route' => '/supply-chain/alerts', 'icon' => 'AlertTriangle', 'category' => 'Perencanaan & PPIC', 'description' => 'Under-stock, expired <90 hari & overdue PO'],

                // 3. Kontrol Anggaran (Budget Layer)
                ['name' => 'Target Omset (Revenue Budget)', 'route' => '/supply-chain/budgets/revenue', 'icon' => 'DollarSign', 'category' => 'Kontrol Anggaran (Budget)', 'description' => 'Target penerimaan tahunan/bulanan korporasi'],
                ['name' => 'Alokasi Anggaran Departemen', 'route' => '/supply-chain/budgets/departments', 'icon' => 'Landmark', 'category' => 'Kontrol Anggaran (Budget)', 'description' => 'Distribusi persentase pagu departemen disetujui'],
                ['name' => 'Matriks Cost Center & Purpose', 'route' => '/supply-chain/cost-centers', 'icon' => 'Layers', 'category' => 'Kontrol Anggaran (Budget)', 'description' => 'Pemetaan pusat biaya & shared budget lintas divisi'],
                ['name' => 'Monitoring Komitmen Anggaran', 'route' => '/supply-chain/budgets/monitoring', 'icon' => 'Wallet', 'category' => 'Kontrol Anggaran (Budget)', 'description' => 'Real-time budget commit, release & warning tiers'],

                // 4. Pengadaan (Procurement & Purchasing)
                ['name' => 'PR Operasional (BOM-Based)', 'route' => '/supply-chain/procurement/operational-pr', 'icon' => 'ClipboardCheck', 'category' => 'Pengadaan Terpadu', 'description' => 'Otomatis ledakan kebutuhan bahan dari target batch'],
                ['name' => 'PR Non-Operasional', 'route' => '/supply-chain/procurement/non-operational-pr', 'icon' => 'FileText', 'category' => 'Pengadaan Terpadu', 'description' => 'Pengadaan umum, ATK & suku cadang mesin'],
                ['name' => 'Form Estimasi Harga Pasar', 'route' => '/supply-chain/procurement/estimates', 'icon' => 'Tag', 'category' => 'Pengadaan Terpadu', 'description' => 'Estimasi harga satuan riil sebelum ditinjau FAT'],
                ['name' => 'Penerbitan PO Multi-Vendor', 'route' => '/supply-chain/procurement/purchase-orders', 'icon' => 'ShoppingCart', 'category' => 'Pengadaan Terpadu', 'description' => 'Penerbitan PO rekanan berurutan resmi'],

                // 5. Pergudangan & Inventori
                ['name' => 'Inbound Goods Receipt (GRN)', 'route' => '/supply-chain/goods-receipt', 'icon' => 'Truck', 'category' => 'Pergudangan & Inventori', 'description' => 'Penerimaan fisik barang & pembentukan nomor GRN'],
                ['name' => 'Karantina QC (Uji 4-Parameter)', 'route' => '/supply-chain/quarantine', 'icon' => 'ShieldCheck', 'category' => 'Pergudangan & Inventori', 'description' => 'Fisik, Kimia, Mikrobiologi, Penandaan kemasan'],
                ['name' => 'Saldo Stok Multi-Gudang', 'route' => '/supply-chain/stock', 'icon' => 'Warehouse', 'category' => 'Pergudangan & Inventori', 'description' => 'Ledger batch, exp date & lokasi penyimpanan fisik'],
                ['name' => 'Permintaan Pemakaian Bahan (BSTB)', 'route' => '/supply-chain/material-usage', 'icon' => 'ArrowRightCircle', 'category' => 'Pergudangan & Inventori', 'description' => 'Bukti serah terima bahan ke lini perakitan pabrik'],
                ['name' => 'Pengembalian Bahan ke Vendor', 'route' => '/supply-chain/material-return', 'icon' => 'CornerUpLeft', 'category' => 'Pergudangan & Inventori', 'description' => 'Pengembalian barang reject atau cacat kirim vendor'],
                ['name' => 'Stock Opname & Akurasi IRA', 'route' => '/supply-chain/stock-opname', 'icon' => 'Scale', 'category' => 'Pergudangan & Inventori', 'description' => 'Snapshot saldo, hitung fisik & persentase IRA %'],
                ['name' => 'Manajemen Scrap Gudang', 'route' => '/supply-chain/warehouse-scrap', 'icon' => 'AlertOctagon', 'category' => 'Pergudangan & Inventori', 'description' => 'Pencatatan kerusakan & penurunan mutu di gudang'],

                // 6. Manufaktur & Mutu
                ['name' => 'Work Order Produksi (SPK)', 'route' => '/supply-chain/production/work-orders', 'icon' => 'Wrench', 'category' => 'Manufaktur & Mutu', 'description' => 'Mandat resmi lantai kerja mengikat batch & BOM'],
                ['name' => 'Pipeline Inspeksi Mutu', 'route' => '/supply-chain/quality/inspections', 'icon' => 'CheckCircle2', 'category' => 'Manufaktur & Mutu', 'description' => 'Pengujian mutu tahapan pengolahan'],
                ['name' => 'Rilis Sertifikat CoA & CPB', 'route' => '/supply-chain/quality/releases', 'icon' => 'Award', 'category' => 'Manufaktur & Mutu', 'description' => 'Persetujuan rilis batch produk oleh QA'],

                // 7. Keuangan & Settlement Hutang
                ['name' => '3-Way Matching Bill', 'route' => '/supply-chain/matching', 'icon' => 'FileCheck2', 'category' => 'Keuangan & Hutang (AP)', 'description' => 'Pencocokan PO vs GRN Penerimaan vs Invoice Vendor'],
                ['name' => 'Payment Voucher & Hutang Usaha', 'route' => '/supply-chain/finance/vouchers', 'icon' => 'Receipt', 'category' => 'Keuangan & Hutang (AP)', 'description' => 'Penerbitan voucher pembayaran hutang dagang'],
                ['name' => 'Akuntansi Biaya (HPP Produk)', 'route' => '/supply-chain/finance/cogs', 'icon' => 'Calculator', 'category' => 'Keuangan & Hutang (AP)', 'description' => 'Kalkulasi HPP gabungan bahan, upah & overhead'],
            ],
            default => [
                ['name' => 'Overview', 'route' => "/modules/{$slug}", 'icon' => 'LayoutDashboard'],
            ],
        };
    }

    /**
     * Return live alerts or pending approval count per module.
     */
    public function getLiveBadgeCount(string $slug): int
    {
        if ($this->approvalService) {
            $badges = $this->approvalService->getLiveModuleBadgeCounts();
            if (isset($badges[$slug])) {
                return $badges[$slug];
            }
        }

        return match ($slug) {
            'procurement' => 5,
            'supply-chain' => 4,
            'hris' => 4,
            'quality' => 3,
            'general-affair' => 3,
            'finance' => 2,
            'production' => 2,
            'rnd' => 1,
            default => 0,
        };
    }

    /**
     * Cross-module pending approval list for the active user.
     *
     * @return list<array<string, mixed>>
     */
    public function getPendingApprovalsForUser(?User $user): array
    {
        if ($this->approvalService) {
            $realApprovals = $this->approvalService->getPendingApprovalsForUser($user);
            if (! empty($realApprovals)) {
                return $realApprovals;
            }
        }

        return [
            [
                'id' => 101,
                'title' => 'Purchase Request PR-2026-0089',
                'description' => 'Pengadaan Ekstrak Curcuma Xanthorrhiza 500 Kg',
                'module_slug' => 'procurement',
                'module_name' => 'Procurement',
                'module_theme' => 'violet',
                'type' => 'Purchase Request',
                'amount' => 'Rp 67.500.000',
                'requester' => 'Budi Santoso (R&D)',
                'urgency' => 'high',
                'date' => '2 jam lalu',
            ],
            [
                'id' => 102,
                'title' => 'Change Request CRA-2026-012',
                'description' => 'Penyesuaian Suhu HVAC Cleanroom Area Mixing Ekstrak',
                'module_slug' => 'quality',
                'module_name' => 'Quality (QMS)',
                'module_theme' => 'teal',
                'type' => 'Change Request Major',
                'amount' => null,
                'requester' => 'Dr. Rina Wati (QA Spv)',
                'urgency' => 'high',
                'date' => '3 jam lalu',
            ],
            [
                'id' => 103,
                'title' => 'Formulasi R&D V3 (100.00%)',
                'description' => 'Kapsul Ekstrak Meniran & Jahe Merah Skala Komersial',
                'module_slug' => 'rnd',
                'module_name' => 'R&D',
                'module_theme' => 'cyan',
                'type' => 'NPD Formulation',
                'amount' => 'HPP: Rp 16.200/btl',
                'requester' => 'Formulator HerbaTech',
                'urgency' => 'medium',
                'date' => '5 jam lalu',
            ],
            [
                'id' => 104,
                'title' => 'Jurnal Penyesuaian AJE-2026-044',
                'description' => 'Akrual Biaya Pemeliharaan Mesin Granulasi Plant 1',
                'module_slug' => 'finance',
                'module_name' => 'Finance',
                'module_theme' => 'emerald',
                'type' => 'Journal Entry',
                'amount' => 'Rp 14.800.000',
                'requester' => 'Staf Akuntansi FAT',
                'urgency' => 'normal',
                'date' => '1 hari lalu',
            ],
        ];
    }

    /**
     * Search index for Global Command Palette (Ctrl + K).
     *
     * @return list<array<string, mixed>>
     */
    public function getGlobalSearchItems(): array
    {
        $items = [];

        // 1. All Active Modules
        foreach ($this->getActiveModules() as $module) {
            $items[] = [
                'type' => 'module',
                'category' => 'Modul ERP',
                'title' => $module->name,
                'subtitle' => $module->description,
                'url' => $module->route_prefix,
                'icon' => $module->icon,
                'theme' => $module->color_theme,
            ];

            // Sub-menus
            foreach ($this->getModuleMenus($module->slug, $module->schema_config_json) as $menu) {
                $items[] = [
                    'type' => 'menu',
                    'category' => "Menu: {$module->name}",
                    'title' => $menu['name'],
                    'subtitle' => "Buka submenu {$menu['name']} di {$module->name}",
                    'url' => $menu['route'],
                    'icon' => $menu['icon'] ?? 'Folder',
                    'theme' => $module->color_theme,
                ];
            }
        }

        // 2. Documents & Sample Entities (PR, PO, Batch, Deviation, Employee, Product)
        $sampleDocuments = [
            ['title' => 'PR-2026-0089', 'category' => 'Purchase Request', 'subtitle' => 'Pengadaan Ekstrak Curcuma Xanthorrhiza (Menunggu Review)', 'url' => '/procurement', 'icon' => 'FileText', 'theme' => 'violet'],
            ['title' => 'PO-2026-0042', 'category' => 'Purchase Order', 'subtitle' => 'PO Kemasan Botol Kaca Amber CV Packindo (Approved)', 'url' => '/procurement', 'icon' => 'ShoppingCart', 'theme' => 'violet'],
            ['title' => 'BATCH-EXT-2026-088', 'category' => 'Batch Produksi', 'subtitle' => 'Ekstrak Temulawak 500 Kg - Line Mixing Cleanroom', 'url' => '/production', 'icon' => 'Factory', 'theme' => 'rose'],
            ['title' => 'DEV-2026-003', 'category' => 'Deviasi Mutu CPOB', 'subtitle' => 'Deviasi Penurunan Suhu Ruang Kering (Fishbone Investigated)', 'url' => '/quality', 'icon' => 'AlertOctagon', 'theme' => 'teal'],
            ['title' => 'NPD-HERBA-PEGAGAN', 'category' => 'Proyek R&D', 'subtitle' => 'Sirup Ekstrak Pegagan - Formulasi 100% Valid', 'url' => '/rnd', 'icon' => 'FlaskConical', 'theme' => 'cyan'],
            ['title' => 'EMP-0142: Joko Susilo', 'category' => 'Karyawan', 'subtitle' => 'Operator Lini 2 Ekstraksi (Hadir - Presensi Valid)', 'url' => '/hris', 'icon' => 'Users', 'theme' => 'blue'],
            ['title' => 'FG-EXT-CURCUMA-100', 'category' => 'Produk Jadi', 'subtitle' => 'Curcuma Extract 100ml Bottle (Stok: 4.800 Pcs)', 'url' => '/supply-chain', 'icon' => 'Boxes', 'theme' => 'indigo'],
            ['title' => 'WO-2026-0019', 'category' => 'Work Order GA', 'subtitle' => 'Perbaikan Kompresor HVAC Cleanroom Boyolali', 'url' => '/general-affair', 'icon' => 'Wrench', 'theme' => 'amber'],
        ];

        return array_merge($items, $sampleDocuments);
    }

    public function togglePin(User $user, int $moduleId): bool
    {
        $preference = UserModulePreference::firstOrNew([
            'user_id' => $user->id,
            'module_id' => $moduleId,
        ]);

        $preference->is_pinned = ! $preference->is_pinned;
        $preference->save();

        return $preference->is_pinned;
    }

    /**
     * @param  list<int>  $moduleIds
     */
    public function reorderModules(User $user, array $moduleIds): void
    {
        foreach ($moduleIds as $order => $moduleId) {
            UserModulePreference::updateOrCreate(
                [
                    'user_id' => $user->id,
                    'module_id' => (int) $moduleId,
                ],
                [
                    'custom_order' => $order,
                ]
            );
        }
    }

    public function clearCache(): void
    {
        Cache::forget('erp_active_modules');
    }
}
