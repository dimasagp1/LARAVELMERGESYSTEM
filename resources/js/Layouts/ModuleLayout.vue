<script setup>
import { ref, computed, watchEffect } from 'vue';
import { Link, usePage } from '@inertiajs/vue3';
import { useDeveloperModeStore } from '@/Stores/developerModeStore';
import { useThemeStore } from '@/Stores/themeStore';
import GlobalCommandPalette from '@/Components/GlobalCommandPalette.vue';
import AppLauncherModal from '@/Components/AppLauncherModal.vue';
import DeveloperModeOverlay from '@/Components/DeveloperModeOverlay.vue';
import NotificationCenterDropdown from '@/Components/NotificationCenterDropdown.vue';
import {
    LayoutGrid,
    Search,
    Bell,
    Sun,
    Moon,
    Terminal,
    Menu,
    X,
    ChevronRight,
    ChevronDown,
    LayoutDashboard,
    ListTree,
    PieChart,
    FileSpreadsheet,
    Scale,
    Wrench,
    FileText,
    Building2,
    QrCode,
    Users,
    Clock,
    Calendar,
    Banknote,
    Award,
    FileCheck,
    ShoppingCart,
    DollarSign,
    Building,
    Timer,
    Gauge,
    AlertTriangle,
    GitCommit,
    AlertOctagon,
    CheckCircle2,
    Layers,
    Calculator,
    FlaskConical,
    FileCheck2,
    RefreshCw,
    Boxes,
    ClipboardCheck,
    CheckCircle,
    Warehouse,
    Folder,
    Factory,
    ShieldCheck,
    Truck,
    Landmark,
    Target,
    CalendarOff,
    CalendarCheck,
    Lightbulb,
    BarChart3,
    Activity,
    CheckCheck,
    Package,
    Sparkles,
    Receipt,
    Wallet,
    Tag,
    Cpu,
    ArrowRightCircle,
    CornerUpLeft,
    BookOpen,
    Settings,
    TrendingUp,
    Coins,
    Share2,
    Sliders,
    ScanFace,
    UserCheck,
    ArrowLeftRight,
    CalendarDays,
    HeartHandshake,
    AlertCircle,
    LogOut,
    Users2,
    Hourglass,
    ClipboardList,
    GraduationCap,
    CheckSquare,
    ListChecks,
    Stethoscope,
    FileHeart,
    BadgePercent,
    Compass,
    Briefcase,
    Database,
    BarChart,
    Shield,
    Grid,
    BarChart2,
    PauseCircle,
    Lock,
    Trash2,
    Car,
    FolderTree,
    History,
    Link as LinkIcon,
    FileEdit,
    FilePlus,
    GitFork,
    HelpCircle,
    UploadCloud,
    PenTool,
    Image,
    PackagePlus,
    ScanLine,
    Camera,
    LifeBuoy,
    Kanban,
    MessageSquare,
    Microscope,
    Thermometer,
    FolderArchive,
    ArrowRightLeft,
    Inbox,
    ShieldAlert,
} from 'lucide-vue-next';

const defaultModuleMenus = {
    'finance': [
        { name: 'Overview Dashboard', route: '/finance', icon: 'LayoutDashboard', category: 'Ringkasan', description: 'Ringkasan KPI eksekutif, status periode & grafik realisasi' },
        { name: 'Dashboard Manager', route: '/finance/manager-dashboard', icon: 'PieChart', category: 'Ringkasan', description: 'Pantauan manajerial 11 departemen' },
        { name: 'Dashboard Departemen', route: '/finance/dept-dashboard', icon: 'Building2', category: 'Ringkasan', description: 'Pantauan pengeluaran khusus departemen login' },

        { name: 'Monitoring Budget P&L', route: '/finance/budget-pnl', icon: 'BarChart3', category: 'Anggaran', description: 'Komparasi berdampingan Target vs Actual P&L' },
        { name: 'Budget Dinamis & Variansi', route: '/finance/dynamic-budget', icon: 'Calculator', category: 'Anggaran', description: 'Flexible budget engine & simulasi volume' },
        { name: 'Realisasi Belanja Pagu', route: '/finance/monitoring', icon: 'Wallet', category: 'Anggaran', description: 'Kontrol pagu belanja operasional per departemen' },
        { name: 'Forecast Belanja', route: '/finance/forecasts', icon: 'TrendingUp', category: 'Anggaran', description: 'Proyeksi kebutuhan belanja bulanan' },
        { name: 'Laporan Anggaran Proyeksi', route: '/finance/budgeted-statements', icon: 'FileSpreadsheet', category: 'Anggaran', description: 'Laporan laba rugi & neraca versi proyeksi murni' },
        { name: 'Analisa Varians Biaya', route: '/finance/variance-analysis', icon: 'Scale', category: 'Anggaran', description: 'Dekomposisi varians volume & harga' },
        { name: 'Rekap Realisasi Belanja', route: '/finance/laporan', icon: 'FileText', category: 'Anggaran', description: 'Ekspor rekapitulasi realisasi belanja' },
        { name: 'Standar Budget Departemen', route: '/finance/departments', icon: 'Landmark', category: 'Anggaran', description: 'Penetapan pagu bulanan 11 departemen' },
        { name: 'Rencana Produksi Pabrik', route: '/finance/production-plans', icon: 'Factory', category: 'Anggaran', description: 'Target kapasitas mesin, yield & biaya BOM' },
        { name: 'Rencana Pendapatan Omset', route: '/finance/revenue-plans', icon: 'DollarSign', category: 'Anggaran', description: 'Target volume unit dan harga jual produk' },
        { name: 'Pagu PR (Staging Verifikasi)', route: '/finance/staging', icon: 'Receipt', category: 'Anggaran', description: 'Verifikasi status pengeluaran bon belanja' },

        { name: 'Master Chart of Accounts (COA)', route: '/finance/coa', icon: 'ListTree', category: 'Akuntansi', description: 'Bagan akun mandiri, saldo awal & pemetaan FSLI' },
        { name: 'Jurnal Penyesuaian (AJE)', route: '/finance/aje', icon: 'FileSpreadsheet', category: 'Akuntansi', description: 'Maker-checker jurnal akrual, depresiasi & amortisasi' },
        { name: 'Trial Balance 4-Kolom', route: '/finance/trial-balance', icon: 'Scale', category: 'Akuntansi', description: 'Kertas kerja neraca saldo seimbang & drilldown' },
        { name: '4 Laporan Keuangan Standar', route: '/finance/financial-statements', icon: 'BookOpen', category: 'Akuntansi', description: 'Laba Rugi, Neraca, Ekuitas, Arus Kas' },
        { name: '19 Rasio Finansial BSC', route: '/finance/financial-ratios', icon: 'Activity', category: 'Akuntansi', description: '19 Rasio Finansial 5 Pilar BSC' },
        { name: 'Modal Disetor (Paid-in)', route: '/finance/paid-in-capital', icon: 'Coins', category: 'Akuntansi', description: 'Pencatatan modal disetor resmi untuk rasio CPR' },
        { name: 'Produk NPD (Omset Inovasi)', route: '/finance/npd', icon: 'Lightbulb', category: 'Akuntansi', description: 'Omset produk inovasi baru vs eksisting' },

        { name: 'Kroscek Odoo Live', route: '/finance/odoo-croscheck', icon: 'RefreshCw', category: 'Data', description: 'Verifikasi keseimbangan transaksi Odoo live' },
        { name: 'Pemetaan COA & Cost Center', route: '/finance/coa-mapping', icon: 'ListTree', category: 'Data', description: 'Aturan distribusi akun Odoo ke FSLI & Departemen' },
        { name: 'Audit Rekonsiliasi (R1-R9)', route: '/finance/reconciliation', icon: 'CheckCheck', category: 'Data', description: '9-Point automatic reconciliation checks' },
        { name: 'Audit Trail & Activity Logs', route: '/finance/activity-logs', icon: 'Clock', category: 'Data', description: 'Catatan histori aktivitas seluruh pengguna & sistem' },

        { name: 'Publikasi Data Olahan', route: '/finance/publications', icon: 'Share2', category: 'Integrasi', description: 'Staging snapshot publikasi finansial ke HRIS & BSC' },
        { name: 'Pipeline Sasaran Mutu', route: '/finance/kpi-pipeline', icon: 'Target', category: 'Integrasi', description: 'Ekstraksi 64 Sasaran Mutu Odoo untuk HRIS' },
        { name: 'Data Ketenagakerjaan HRIS', route: '/finance/headcounts', icon: 'Users', category: 'Integrasi', description: 'Master headcount, karyawan langsung & jam kerja' },
        { name: 'Integrasi SuperApps BSC', route: '/finance/bsc-integration', icon: 'Cpu', category: 'Integrasi', description: 'Konfigurasi koneksi ke SuperApps BSC Hub' },
        { name: 'Konfigurasi Bobot Rasio BSC', route: '/finance/ratios-configurations', icon: 'Sliders', category: 'Integrasi', description: 'Kustomisasi bobot & target rasio ke BSC' },
        { name: 'Feed Sistem Produksi', route: '/finance/production-feeds', icon: 'Boxes', category: 'Integrasi', description: 'Penerimaan data realisasi pabrik & reject rate' },

        { name: 'Tahun Anggaran & Tutup Buku', route: '/finance/fiscal-years', icon: 'Calendar', category: 'Admin', description: 'Penetapan tahun fiskal aktif & penutupan buku' },
        { name: 'Manajemen Pengguna FAT', route: '/finance/users', icon: 'ShieldCheck', category: 'Admin', description: 'Pengelolaan hak akses pengguna & peran RBAC' },
        { name: 'Pengaturan Sistem FAT', route: '/finance/settings', icon: 'Settings', category: 'Admin', description: 'Pengujian API koneksi Odoo, HRIS, BSC, Produksi' },
    ],
    'general-affair': [
        { name: 'Overview FPUGA Dashboard', route: '/general-affair', icon: 'LayoutDashboard', category: 'Work Order GA & FPU', description: 'Pusat Kendali Fasilitas Penunjang Umum & GA' },
        { name: 'Daftar Work Order GA', route: '/general-affair/work-orders', icon: 'Wrench', category: 'Work Order GA & FPU', description: 'Monitoring tiket perbaikan fasilitas & utilitas gedung' },
        { name: 'Form A: Permohonan Perbaikan', route: '/general-affair/work-orders/create', icon: 'FilePlus', category: 'Work Order GA & FPU', description: 'Pengajuan keluhan/kebutuhan perbaikan pemohon' },
        { name: 'Form B: Pengecekan Teknisi', route: '/general-affair/work-orders/inspection', icon: 'ClipboardCheck', category: 'Work Order GA & FPU', description: 'Inspeksi lapangan & identifikasi kebutuhan material' },
        { name: 'Form C: Pengadaan Material FPU', route: '/general-affair/work-orders/fpu-materials', icon: 'PackagePlus', category: 'Work Order GA & FPU', description: 'Pengadaan material verifikasi HRGA, Mgr, GM' },
        { name: 'Timeline Progres Pengerjaan', route: '/general-affair/work-orders/progress', icon: 'Hourglass', category: 'Work Order GA & FPU', description: 'Laporan progres harian teknisi & dokumentasi foto' },
        { name: 'Form D: Berita Acara & Rating', route: '/general-affair/work-orders/handover', icon: 'CheckCircle2', category: 'Work Order GA & FPU', description: 'Serah terima hasil pekerjaan & rating 5-bintang' },
        { name: 'Cetak Form Resmi A, C, dan D', route: '/general-affair/work-orders/print', icon: 'Printer', category: 'Work Order GA & FPU', description: 'Dokumen cetak legal standar operasional FPUGA' },

        { name: 'Daftar Permintaan ATK & RT', route: '/general-affair/atk', icon: 'Package', category: 'Permintaan ATK & RT', description: 'Logistik ATK & operasional rumah tangga kantor/pabrik' },
        { name: 'Pengajuan Katalog & Custom Item', route: '/general-affair/atk/create', icon: 'ShoppingCart', category: 'Permintaan ATK & RT', description: 'Katalog standar ATK/RT & custom item' },
        { name: 'Persetujuan Manager & Admin GA', route: '/general-affair/atk/approvals', icon: 'UserCheck', category: 'Permintaan ATK & RT', description: 'Alur approval Manager Divisi -> Admin GA' },
        { name: 'Approval Granular & Revisi (-R1)', route: '/general-affair/atk/revisions', icon: 'RefreshCw', category: 'Permintaan ATK & RT', description: 'Revisi terstruktur item ditolak tanpa mengulang form' },
        { name: 'Serah Terima Fisik Barang', route: '/general-affair/atk/distribution', icon: 'ArrowRightCircle', category: 'Permintaan ATK & RT', description: 'Pencatatan tanggal distribusi & nama penerima' },
        { name: 'Cetak & Ekspor Rekap Bulanan', route: '/general-affair/atk/reports', icon: 'FileSpreadsheet', category: 'Permintaan ATK & RT', description: 'Ekspor PDF dan Excel untuk audit pembukuan bulanan' },

        { name: 'Master Aset Multi-Plant', route: '/general-affair/assets', icon: 'Building2', category: 'Aset Tetap Multi-Plant', description: 'Penatausahaan aset Plant A, Plant B & Head Office' },
        { name: 'Depresiasi Garis Lurus', route: '/general-affair/assets/depreciation', icon: 'Calculator', category: 'Aset Tetap Multi-Plant', description: 'Penyusutan bulanan, akumulasi & nilai buku real-time' },
        { name: 'Mutasi Aset Lintas Lokasi', route: '/general-affair/assets/mutations', icon: 'ArrowLeftRight', category: 'Aset Tetap Multi-Plant', description: 'Transaksi pemindahan aset antar plant, dept & ruangan' },
        { name: 'Kondisi Fisik & Disposal Aset', route: '/general-affair/assets/disposals', icon: 'Trash2', category: 'Aset Tetap Multi-Plant', description: 'Monitoring kondisi baik/rusak & penghapusan aset' },
        { name: 'Daftar Inventaris Ruangan (DIR)', route: '/general-affair/assets/dir-rooms', icon: 'ClipboardList', category: 'Aset Tetap Multi-Plant', description: 'Form CM-03/GA/002.C.01 cetak lembar inventaris pintu' },
        { name: 'Label QR Code & Mobile Scanner', route: '/general-affair/assets/qr-scanner', icon: 'QrCode', category: 'Aset Tetap Multi-Plant', description: 'Cetak label QR & scanner kamera inspeksi opname' },
        { name: 'Asisten AI Pengendali Aset', route: '/general-affair/assets/ai-assistant', icon: 'Sparkles', category: 'Aset Tetap Multi-Plant', description: 'GaAiAssetAgentService asisten cerdas bahasa alami' },

        { name: 'Dashboard Pos Keamanan', route: '/general-affair/patrols/security', icon: 'ShieldCheck', category: 'Security Patrol', description: 'Monitoring kepatuhan patroli satpam & pos perimeter' },
        { name: 'Master Pos & Checkpoints QR', route: '/general-affair/patrols/security/checkpoints', icon: 'MapPin', category: 'Security Patrol', description: 'Pemetaan gerbang, loading dock, trafo & barcode pos' },
        { name: 'Penjadwalan Shift & Import Excel', route: '/general-affair/patrols/security/schedules', icon: 'Calendar', category: 'Security Patrol', description: 'Shift pagi, siang, malam & regu tetap satpam' },
        { name: 'Mobile QR Scanner Patroli + Timer', route: '/general-affair/patrols/security/scan', icon: 'ScanLine', category: 'Security Patrol', description: 'Scan pos HP, validasi durasi patroli & status pos' },
        { name: 'Laporan Anomali (Evident Report)', route: '/general-affair/patrols/security/incidents', icon: 'Camera', category: 'Security Patrol', description: 'Pencatatan kejadian mencurigakan & bukti foto' },
        { name: 'Ekspor Rekapitulasi Patroli', route: '/general-affair/patrols/security/reports', icon: 'FileText', category: 'Security Patrol', description: 'Laporan bulanan persentase pos terinspeksi' },

        { name: 'Dashboard Kebersihan Ruangan', route: '/general-affair/patrols/cleaning', icon: 'Sparkles', category: 'Cleaning Service 5R', description: 'Pemantauan sanitasi toilet & fasilitas kerja' },
        { name: 'Master Titik Sanitasi & QR', route: '/general-affair/patrols/cleaning/checkpoints', icon: 'MapPin', category: 'Cleaning Service 5R', description: 'Titik pembersihan toilet, koridor, locker, kantin' },
        { name: 'Penjadwalan Petugas Kebersihan', route: '/general-affair/patrols/cleaning/schedules', icon: 'Calendar', category: 'Cleaning Service 5R', description: 'Penugasan shift & area kerja cleaning service' },
        { name: 'Mobile QR Checklist Pembersihan', route: '/general-affair/patrols/cleaning/scan', icon: 'CheckSquare', category: 'Cleaning Service 5R', description: 'Scan kode ruangan & checklist sanitasi selesai' },
        { name: 'Rekap Kepatuhan Sanitasi', route: '/general-affair/patrols/cleaning/reports', icon: 'FileSpreadsheet', category: 'Cleaning Service 5R', description: 'Monitoring kedisiplinan pembersihan per area' },

        { name: 'HelpDesk Dashboard & SLA Hub', route: '/general-affair/helpdesk', icon: 'LifeBuoy', category: 'IT HelpDesk Pro', description: 'Monitoring tiket FRT, MTTR & SLA compliance %' },
        { name: 'Mode Proyek (9-Stage Workflow)', route: '/general-affair/helpdesk/projects', icon: 'Layers', category: 'IT HelpDesk Pro', description: 'Requirements -> UI/UX -> DB -> Code -> UAT -> Deploy' },
        { name: 'Tiket Masalah Teknis Harian', route: '/general-affair/helpdesk/tickets', icon: 'HelpCircle', category: 'IT HelpDesk Pro', description: 'Wifi, printer, PC, jaringan matriks prioritas' },
        { name: 'Papan Antrean Teknisi IT', route: '/general-affair/helpdesk/queues', icon: 'Kanban', category: 'IT HelpDesk Pro', description: 'Papan antrean penugasan teknisi IT HelpDesk' },
        { name: 'Pengawasan SLA & Pause/Play', route: '/general-affair/helpdesk/sla-monitoring', icon: 'Clock', category: 'IT HelpDesk Pro', description: 'Timer tiket dan jeda saat tunggu data pemohon' },
        { name: 'Laporan Analitik SLA & Teknisi', route: '/general-affair/helpdesk/reports', icon: 'BarChart', category: 'IT HelpDesk Pro', description: 'Rekapitulasi performa teknisi & durasi penyelesaian' },

        { name: 'Daily Log & Timesheet Teknisi', route: '/general-affair/daily-logs', icon: 'FileEdit', category: 'Administrasi & Utilitas', description: 'Rekapitulasi durasi kerja harian staf & teknisi' },
        { name: 'Floating Chat Tiket Real-Time', route: '/general-affair/chat', icon: 'MessageSquare', category: 'Administrasi & Utilitas', description: 'Sistem pesan internal antar pemohon & teknisi' },
        { name: 'Master Barang ATK/RT & Satuan', route: '/general-affair/master-items', icon: 'Boxes', category: 'Administrasi & Utilitas', description: 'Katalog resmi barang kantor & satuan unit UOM' },
        { name: 'Master Pengguna & Tanda Tangan', route: '/general-affair/admin/users', icon: 'Users', category: 'Administrasi & Utilitas', description: 'Pendaftaran pengguna, role & spesimen tanda tangan' },
        { name: 'Audit Log Aktivitas Sistem GA', route: '/general-affair/admin/activity-logs', icon: 'History', category: 'Administrasi & Utilitas', description: 'Jejak audit seluruh perubahan operasional GA' },
    ],
    'hris': [
        { name: 'Overview HRIS Dashboard', route: '/hris', icon: 'LayoutDashboard', category: 'Master Karyawan & Organisasi', description: 'Dashboard Utama & Operational Control Tower' },
        { name: 'Master Karyawan', route: '/hris/employees', icon: 'Users', category: 'Master Karyawan & Organisasi', description: 'Biodata lengkap, NIK, golongan grade, mutasi' },
        { name: 'Master 9 Departemen Inti', route: '/hris/departments', icon: 'Building2', category: 'Master Karyawan & Organisasi', description: 'Struktur hierarki departemen, Manager & pagu target' },
        { name: 'Verifikasi Wajah Biometrik', route: '/hris/face-enrollment', icon: 'ScanFace', category: 'Master Karyawan & Organisasi', description: 'Registrasi 128-D descriptor wajah anti-titip absen' },
        { name: 'Pengajuan Profil Mandiri', route: '/hris/profile-requests', icon: 'UserCheck', category: 'Master Karyawan & Organisasi', description: 'Pembaruan data pribadi mandiri oleh karyawan' },

        { name: 'Master Shift Kerja', route: '/hris/shifts', icon: 'Clock', category: 'Jadwal Kerja & Roster', description: 'Definisi jam masuk, pulang, toleransi & istirahat' },
        { name: 'Roster Penjadwalan Bulanan', route: '/hris/schedules', icon: 'Calendar', category: 'Jadwal Kerja & Roster', description: 'Pemetaan jadwal kerja individu atau kelompok' },
        { name: 'Pengajuan Tukar Shift', route: '/hris/shift-requests', icon: 'ArrowLeftRight', category: 'Jadwal Kerja & Roster', description: 'Alur tukar jadwal & validasi tabrakan jadwal' },
        { name: 'Kalender Kerja & Hari Libur', route: '/hris/holidays', icon: 'CalendarDays', category: 'Jadwal Kerja & Roster', description: 'Hari libur nasional & hari kerja efektif bulanan' },
        { name: 'Geofencing GPS Lokasi Kantor', route: '/hris/office-locations', icon: 'MapPin', category: 'Jadwal Kerja & Roster', description: 'Koordinat GPS & radius toleransi kantor/pabrik' },

        { name: 'Presensi GPS & Wajah', route: '/hris/attendance', icon: 'CalendarCheck', category: 'Presensi & Disiplin', description: 'Smart check-in/out, anti mock GPS & verifikasi wajah' },
        { name: 'Presensi Ibadah Spiritual', route: '/hris/prayer-attendances', icon: 'HeartHandshake', category: 'Presensi & Disiplin', description: 'Pencatatan kehadiran sholat berjamaah harian' },
        { name: 'Izin Terlambat Datang', route: '/hris/late-arrival-requests', icon: 'AlertCircle', category: 'Presensi & Disiplin', description: 'Dispensasi datang terlambat dengan bukti' },
        { name: 'Izin Pulang Cepat', route: '/hris/early-leave-requests', icon: 'LogOut', category: 'Presensi & Disiplin', description: 'Izin meninggalkan tempat kerja sebelum jam shift usai' },
        { name: 'Sanksi, Pelanggaran & SP', route: '/hris/sanctions', icon: 'AlertOctagon', category: 'Presensi & Disiplin', description: 'Penerbitan SP 1, 2, 3, teguran & penalti remunerasi' },
        { name: 'Audit Log & Rekap Presensi', route: '/hris/attendance-audit-logs', icon: 'FileSpreadsheet', category: 'Presensi & Disiplin', description: 'Jejak audit perubahan absensi & rekap harian/bulanan' },

        { name: 'Pengajuan Cuti Online', route: '/hris/leaves', icon: 'CalendarOff', category: 'Cuti & Waktu Kerja', description: 'Permohonan cuti tahunan, sakit, melahirkan berjenjang' },
        { name: 'Kategori & Saldo Kuota Cuti', route: '/hris/leave-categories', icon: 'Tag', category: 'Cuti & Waktu Kerja', description: 'Pengurangan saldo otomatis & pencegahan overdraft' },
        { name: 'Cuti Bersama Perusahaan', route: '/hris/bulk-day-off-events', icon: 'Users2', category: 'Cuti & Waktu Kerja', description: 'Penjadwalan libur massal pemotong cuti tahunan' },
        { name: 'Day Bank System', route: '/hris/day-banks', icon: 'Landmark', category: 'Cuti & Waktu Kerja', description: 'Konversi kerja lembur libur menjadi saldo hari' },
        { name: 'Surat Perintah Lembur (SPL)', route: '/hris/overtime', icon: 'Timer', category: 'Cuti & Waktu Kerja', description: 'SPL formula Depnaker RI & multi-tier approval' },
        { name: 'Time Bank Engine', route: '/hris/time-banks', icon: 'Hourglass', category: 'Cuti & Waktu Kerja', description: 'Deposit, Debt, Debt Payment, Withdraw jam kerja' },

        { name: 'Timesheet Aktivitas Harian', route: '/hris/activities', icon: 'ClipboardList', category: 'Kaizen, Training & MCU', description: 'Pelaporan tugas harian mandiri & validasi atasan' },
        { name: 'Kaizen Kizuki & Ide Perbaikan', route: '/hris/kizuki', icon: 'Lightbulb', category: 'Kaizen, Training & MCU', description: 'Continuous improvement & laporan anomali area' },
        { name: 'Sistem Poin & Reward Voucher', route: '/hris/points', icon: 'Award', category: 'Kaizen, Training & MCU', description: 'Poin keaktifan, presensi sempurna & klaim hadiah' },
        { name: 'Manajemen Pelatihan', route: '/hris/trainings', icon: 'GraduationCap', category: 'Kaizen, Training & MCU', description: 'Program pelatihan internal/eksternal & peserta' },
        { name: 'Evaluasi Kirkpatrick 3-Level', route: '/hris/training-evaluations', icon: 'CheckSquare', category: 'Kaizen, Training & MCU', description: 'L1 Reaksi, L2 Pembelajaran, L3 Perilaku kerja' },
        { name: 'Rencana Aksi Pasca Pelatihan', route: '/hris/training-action-plans', icon: 'ListChecks', category: 'Kaizen, Training & MCU', description: 'Monitoring implementasi ilmu di lapangan 1-3 bulan' },
        { name: 'Kesehatan Kerja & MCU', route: '/hris/medical-checkups', icon: 'Stethoscope', category: 'Kaizen, Training & MCU', description: 'Pemeriksaan kesehatan berkala & status kelaikan' },
        { name: 'Portal Mandiri My-MCU', route: '/hris/my-mcu', icon: 'FileHeart', category: 'Kaizen, Training & MCU', description: 'Resume privat rekam medis karyawan' },

        { name: 'Template & Indikator Kinerja', route: '/hris/kpi-templates', icon: 'Sliders', category: 'Kinerja & Kompensasi', description: 'Standarisasi indikator KPI per jabatan & bobot %' },
        { name: 'Evaluasi & Scoring KPI', route: '/hris/kpis', icon: 'Target', category: 'Kinerja & Kompensasi', description: 'Penilaian berkala atasan-bawahan & nilai akhir' },
        { name: 'Kalkulator Payroll (PPh 21 TER)', route: '/hris/payroll', icon: 'Banknote', category: 'Kinerja & Kompensasi', description: 'Perhitungan gaji pokok, BPJS, lembur & TER' },
        { name: 'Slip Gaji Elektronik (PDF)', route: '/hris/payslips', icon: 'FileText', category: 'Kinerja & Kompensasi', description: 'Slip gaji digital ber-PIN per karyawan' },
        { name: 'Master Golongan & Grading', route: '/hris/master-golongans', icon: 'Layers', category: 'Kinerja & Kompensasi', description: 'Standarisasi rentang upah & jalur kenaikan' },
        { name: 'Evaluasi Remunerasi', route: '/hris/remunerations', icon: 'TrendingUp', category: 'Kinerja & Kompensasi', description: 'Tinjauan kenaikan upah berbasis performa & masa kerja' },
        { name: 'Matriks Kelayakan Promosi', route: '/hris/promotions', icon: 'BadgePercent', category: 'Kinerja & Kompensasi', description: 'Algoritma kesiapan kenaikan jabatan objektif' },

        { name: '35 Sasaran Mutu Departemen', route: '/hris/strategic/objectives', icon: 'Compass', category: 'Strategic BSC L3 & L4', description: 'Sasaran mutu operasional 9 departemen & rumus dinamis' },
        { name: 'Program Kerja & Action Plan (L4)', route: '/hris/strategic/work-programs', icon: 'Briefcase', category: 'Strategic BSC L3 & L4', description: '86 Program Kerja & 258 Action Plan Lead Measure' },
        { name: 'Bank Data Staging Finance', route: '/hris/strategic/finance-variables', icon: 'Database', category: 'Strategic BSC L3 & L4', description: 'Penampung data metrik moneter dari financea' },
        { name: 'Feed Produktivitas Headcount', route: '/hris/strategic/productivity-feeds', icon: 'BarChart', category: 'Strategic BSC L3 & L4', description: 'Perhitungan otomatis TKL, TKTL & Jam Efektif' },

        { name: 'Work Order Pemeliharaan', route: '/hris/work-orders', icon: 'Wrench', category: 'Fasilitas, SOP & AI', description: 'Tiket perbaikan fasilitas & mesin ke teknik/GA' },
        { name: 'Form Permintaan Uang/Bahan (FPU)', route: '/hris/fpu-requests', icon: 'Coins', category: 'Fasilitas, SOP & AI', description: 'Kebutuhan anggaran/material perbaikan fasilitas' },
        { name: 'Handover Serah Terima Pekerjaan', route: '/hris/wo-handovers', icon: 'CheckCircle', category: 'Fasilitas, SOP & AI', description: 'Berita acara serah terima pekerjaan tuntas' },
        { name: 'Pusat Kebijakan Perusahaan (PP)', route: '/hris/company-policies', icon: 'Shield', category: 'Fasilitas, SOP & AI', description: 'Visi-misi, kebijakan mutu halal & aturan kerja' },
        { name: 'Buku Panduan Digital (SOP)', route: '/hris/manual-books', icon: 'BookOpen', category: 'Fasilitas, SOP & AI', description: 'Repositori panduan kerja SOP PDF terpusat' },
        { name: 'AI HR Assistant (Gemini)', route: '/hris/ai-assistant', icon: 'Sparkles', category: 'Fasilitas, SOP & AI', description: 'Asisten cerdas tanya-jawab regulasi HR & SOP' },
    ],
    'procurement': [
        { name: 'Dashboard Overview Pengadaan', route: '/procurement', icon: 'LayoutDashboard', category: 'Purchase Request & Workflow', description: 'Dashboard Analytics & Status Pengadaan' },
        { name: 'Purchase Requests (PR)', route: '/procurement/purchase-requests', icon: 'FileCheck', category: 'Purchase Request & Workflow', description: 'PR Operasional & Non-Operasional' },
        { name: 'Granular 8-Step Item Workflow', route: '/procurement/item-workflow', icon: 'ListTree', category: 'Purchase Request & Workflow', description: 'Pelacakan siklus 8 tahap independen per item' },
        { name: 'Input Estimasi Harga Pasar', route: '/procurement/estimates', icon: 'Tag', category: 'Purchase Request & Workflow', description: 'Input harga estimasi pasar, quotation & vendor' },
        { name: 'Riwayat Penolakan & Bulk Revisi', route: '/procurement/revisions', icon: 'RefreshCw', category: 'Purchase Request & Workflow', description: 'Auto-generate PR baru untuk item ditolak' },

        { name: 'Daftar Berkas PR Bon', route: '/procurement/pr-bons', icon: 'Receipt', category: 'Pengadaan Khusus (PR Bon)', description: 'Pengadaan kas kecil, toko lokal & darurat' },
        { name: 'Staging Bon Lintas PR', route: '/procurement/pr-bons/staging', icon: 'Layers', category: 'Pengadaan Khusus (PR Bon)', description: 'Pengelompokan item is_bon lintas nomor PR' },
        { name: 'Portal Keputusan GM PR Bon', route: '/procurement/pr-bons/approval', icon: 'CheckCircle2', category: 'Pengadaan Khusus (PR Bon)', description: 'Halaman terdedikasi telaah & otorisasi GM' },
        { name: 'Cetak Berkas Form Bon Resmi', route: '/procurement/pr-bons/print', icon: 'Printer', category: 'Pengadaan Khusus (PR Bon)', description: 'Format cetak resmi tanda tangan Procurement & GM' },

        { name: 'Daftar PO Internal', route: '/procurement/purchase-orders', icon: 'ShoppingCart', category: 'Purchase Order & Odoo', description: 'PO non-Odoo & rekaman pesanan internal' },
        { name: 'Sinkronisasi PO Odoo (JSON-RPC)', route: '/procurement/odoo-sync', icon: 'Cpu', category: 'Purchase Order & Odoo', description: 'Pembuatan PO langsung ke modul purchase Odoo' },
        { name: 'Manual Odoo PO Bypass', route: '/procurement/odoo-manual', icon: 'ShieldAlert', category: 'Purchase Order & Odoo', description: 'Pencatatan nomor PO manual & audit trail' },

        { name: 'Rencana Pengiriman (Delivery Plans)', route: '/procurement/deliveries', icon: 'Truck', category: 'Logistik & Penerimaan Fisik', description: 'Jadwal kedatangan parsial & pelacakan reschedule' },
        { name: 'Penerimaan Fisik & Surat Jalan', route: '/procurement/deliveries/receipts', icon: 'ClipboardCheck', category: 'Logistik & Penerimaan Fisik', description: 'Pencatatan kedatangan riil & unggah surat jalan' },
        { name: 'Verifikasi Holding Delivery', route: '/procurement/deliveries/holding', icon: 'Building', category: 'Logistik & Penerimaan Fisik', description: 'Konfirmasi fisik khusus pengadaan holding' },
        { name: 'Retur Barang Rusak & Penggantian', route: '/procurement/deliveries/retur', icon: 'CornerUpLeft', category: 'Logistik & Penerimaan Fisik', description: 'Barang ditolak QC & tanda terima pengganti vendor' },

        { name: 'Cek Pagu Real-Time (FAT API)', route: '/procurement/budgets', icon: 'Wallet', category: 'Kontrol Pagu Anggaran', description: 'Validasi pagu sebelum PR disetujui (FAT API)' },
        { name: 'Staging Pagu & Komitmen Belanja', route: '/procurement/staging-pagu', icon: 'FileSpreadsheet', category: 'Kontrol Pagu Anggaran', description: 'Pemantauan komitmen expense staging ke database FAT' },
        { name: 'Panel Manajemen Pagu Departemen', route: '/procurement/settings/finance-budget', icon: 'Landmark', category: 'Kontrol Pagu Anggaran', description: 'Konfigurasi pagu tahunan & ekspor anggaran' },
        { name: 'Otorisasi Budget Bypass', route: '/procurement/budgets/bypass', icon: 'ShieldCheck', category: 'Kontrol Pagu Anggaran', description: 'Otorisasi belanja mendesak melebihi pagu aktif' },

        { name: 'Katalog Standar Harga Barang', route: '/procurement/standard-prices', icon: 'DollarSign', category: 'Standar Harga & Rekanan', description: 'Standarisasi harga acuan Material & Supporting' },
        { name: 'Approved Vendor List (AVL)', route: '/procurement/vendors', icon: 'Building2', category: 'Standar Harga & Rekanan', description: 'Daftar vendor resmi terdaftar & performa kirim' },
        { name: 'Sinkronisasi Harga Master & Odoo', route: '/procurement/standard-prices/sync', icon: 'RefreshCw', category: 'Standar Harga & Rekanan', description: 'Update harga pasar dari riwayat PO Odoo terakhir' },

        { name: 'Sasaran Mutu Procurement 2026', route: '/procurement/sasaran-mutu', icon: 'Target', category: 'Sasaran Mutu & Integrasi', description: '4 Sasaran Mutu bobot 25% ISO 9001 korporasi' },
        { name: 'Webhook & Log Loss Time Mesin', route: '/procurement/loss-time-logs', icon: 'Timer', category: 'Sasaran Mutu & Integrasi', description: 'Catatan kejadian downtime akibat pasokan bahan' },
        { name: 'Sinkronisasi Metrik ke HRIS API', route: '/procurement/hris-sync', icon: 'Users', category: 'Sasaran Mutu & Integrasi', description: 'Pengiriman data SLA & kinerja ke tim HRIS' },

        { name: 'Laporan Terpadu & Ekspor Data', route: '/procurement/reports', icon: 'FileText', category: 'Master & Laporan', description: 'Laporan berfilter multi-kriteria & ekspor Excel' },
        { name: 'Master Barang & Satuan (UOM)', route: '/procurement/master-items', icon: 'Package', category: 'Master & Laporan', description: 'Katalog barang terstandar & sinkronisasi Odoo' },
        { name: 'Pengaturan Kredensial & Tanda Tangan', route: '/procurement/settings', icon: 'Settings', category: 'Master & Laporan', description: 'Kredensial Odoo, Finance, HRIS & tanda tangan' },
    ],
    'production': [
        { name: 'Dashboard Analytics & KPI OEE', route: '/production', icon: 'LayoutDashboard', category: 'Dashboard & Analisis', description: 'Rata-rata OEE, Availability, Performance & Yield' },
        { name: 'Line Performance Heat Grid', route: '/production/analytics/heat-grid', icon: 'Grid', category: 'Dashboard & Analisis', description: 'Volume output per Line pada Mixing, Filling, Packing' },
        { name: 'Grafik Pareto Downtime Mesin', route: '/production/analytics/pareto', icon: 'BarChart2', category: 'Dashboard & Analisis', description: 'Pemeringkatan alasan gangguan & durasi henti' },
        { name: 'Analisis Reject & Cacat Produk', route: '/production/analytics/rejects', icon: 'PieChart', category: 'Dashboard & Analisis', description: 'Reject rate %, sebaran varian produk & tren' },

        { name: 'Master Produk (FG, RM, PM, WIP)', route: '/production/master/products', icon: 'Package', category: 'Master Data Pabrik', description: 'Batas safety, minimum, maximum & id produk Odoo' },
        { name: 'Master Mesin Pabrik', route: '/production/master/machines', icon: 'Cpu', category: 'Master Data Pabrik', description: 'Daftar mesin & Cycle Time standar per pcs' },
        { name: 'Master Jalur / Line Produksi', route: '/production/master/lines', icon: 'GitCommit', category: 'Master Data Pabrik', description: 'Penomoran dan nama jalur perakitan lantai pabrik' },
        { name: 'Master Alasan Downtime', route: '/production/master/downtime-reasons', icon: 'AlertTriangle', category: 'Master Data Pabrik', description: 'Kamus henti kerja, input manual & durasi patokan' },
        { name: 'Parameter & Multiplier Manpower', route: '/production/master/settings', icon: 'Sliders', category: 'Master Data Pabrik', description: 'Target unit per MP, target IRA & toleransi usage' },
        { name: 'Audit Log Riwayat Pabrik', route: '/production/master/history', icon: 'Clock', category: 'Master Data Pabrik', description: 'Rekam jejak perubahan data kritikal manufaktur' },

        { name: 'Weekly Production Schedule (WPS)', route: '/production/schedules', icon: 'Calendar', category: 'Perencanaan PPIC (WPS)', description: 'Jadwal mingguan List View & Board Kanban' },
        { name: 'Tarik MO Terkonfirmasi Odoo ERP', route: '/production/schedules/odoo-sync', icon: 'RefreshCw', category: 'Perencanaan PPIC (WPS)', description: 'Sinkronisasi Manufacturing Order status confirmed' },
        { name: 'Manajemen Packing Hold', route: '/production/schedules/packing-hold', icon: 'PauseCircle', category: 'Perencanaan PPIC (WPS)', description: 'Penundaan pengemasan sekunder tanpa batalkan batch' },

        { name: 'Laporan Harian Produksi (LHP)', route: '/production/daily-reports', icon: 'ClipboardList', category: 'Eksekusi & Timer', description: 'Rekam realitas output fisik & efisiensi kerja' },
        { name: 'Panel Kontrol Timer Produksi', route: '/production/timer-console', icon: 'Timer', category: 'Eksekusi & Timer', description: 'Start, Pause, Resume dengan dialog alasan & End' },
        { name: 'Estafet Batch Tracking Linier', route: '/production/batches', icon: 'Layers', category: 'Eksekusi & Timer', description: 'Pelacakan linier Mixing -> Filling -> Packing' },
        { name: 'Validasi & Kunci Supervisor', route: '/production/daily-reports/approvals', icon: 'Lock', category: 'Eksekusi & Timer', description: 'Penguncian LHP agar tidak dapat diubah operator' },

        { name: 'Pencatatan Detail Reject & Cacat', route: '/production/scrap', icon: 'AlertOctagon', category: 'Reject & Scrap', description: 'Volume unit reject, jenis defect & material' },
        { name: 'Recipe Component Breakdown Reject', route: '/production/scrap/breakdown', icon: 'ListTree', category: 'Reject & Scrap', description: 'Komponen bahan resep BOM yang ikut terbuang' },
        { name: 'Sinkronisasi Odoo Stock Scrap', route: '/production/scrap/odoo-sync', icon: 'Cpu', category: 'Reject & Scrap', description: 'Koreksi saldo persediaan modul stock.scrap Odoo' },
        { name: 'Scrap Material Gudang Pabrik', route: '/production/scrap/warehouse', icon: 'Trash2', category: 'Reject & Scrap', description: 'Kerusakan penanganan & kedaluwarsa di gudang' },

        { name: 'Struktur Bill of Materials (BOM)', route: '/production/boms', icon: 'FileSpreadsheet', category: 'SCM & Persediaan Pabrik', description: 'Explosion resep & bahan kemas primer/sekunder' },
        { name: 'Saldo Stok & Visualisasi Safe Level', route: '/production/stocks', icon: 'Warehouse', category: 'SCM & Persediaan Pabrik', description: 'Stok per produk, nomor batch, expired & lokasi' },
        { name: 'Material Usage (Konsumsi Riil)', route: '/production/material-usage', icon: 'ArrowRightCircle', category: 'SCM & Persediaan Pabrik', description: 'Analisis variansi kuantitas aktual vs standar BOM' },
        { name: 'Stock Opname Persediaan (IRA %)', route: '/production/stock-opname', icon: 'Scale', category: 'SCM & Persediaan Pabrik', description: 'Cycle count gudang & akurasi pencatatan persediaan' },
        { name: 'Cetak Kartu Stelling Fisik Gudang', route: '/production/stock-opname/stelling', icon: 'Printer', category: 'SCM & Persediaan Pabrik', description: 'Cetak formulir kartu stelling fisik ke PDF' },

        { name: 'Rencana Pengiriman (Delivery Plans)', route: '/production/deliveries', icon: 'Truck', category: 'Pengiriman & Logistik Fleet', description: 'Penjadwalan ekspedisi barang jadi ke pelanggan' },
        { name: 'Pre-Flight Stock Check Barang Jadi', route: '/production/deliveries/stock-check', icon: 'ShieldCheck', category: 'Pengiriman & Logistik Fleet', description: 'Validasi saldo fisik FG sebelum cetak dokumen' },
        { name: 'Manajemen Kendaraan & Driver (Fleet)', route: '/production/deliveries/fleet', icon: 'Car', category: 'Pengiriman & Logistik Fleet', description: 'Nomor polisi kendaraan, kapasitas & kontak supir' },
        { name: 'Generator Surat Jalan, Faktur & COA', route: '/production/deliveries/documents', icon: 'FileText', category: 'Pengiriman & Logistik Fleet', description: 'Berkas legal distribusi PDF siap pakai' },
        { name: 'Pengukuran Kinerja Delivery (OTIF)', route: '/production/deliveries/kpi', icon: 'Award', category: 'Pengiriman & Logistik Fleet', description: 'On-time, in-full, damage-free & complaint rate' },

        { name: 'Konektor Odoo ERP (XML-RPC)', route: '/production/integrations/odoo', icon: 'Cpu', category: 'Integrasi Eksternal', description: 'Penyelarasan MO, BOM, stok, scrap & pesanan' },
        { name: 'Konektor Kinerja ke HRIS', route: '/production/integrations/hris', icon: 'Users', category: 'Integrasi Eksternal', description: 'Feed OEE, efisiensi & jam henti ke HRIS' },
        { name: 'Konektor Biaya ke Finance App', route: '/production/integrations/finance', icon: 'DollarSign', category: 'Integrasi Eksternal', description: 'Feed unit terproduksi & rugi downtime ke Finance' },
    ],
    'produksi': [
        { name: 'Dashboard Analytics & KPI OEE', route: '/production', icon: 'LayoutDashboard', category: 'Dashboard & Analisis', description: 'Rata-rata OEE, Availability, Performance & Yield' },
        { name: 'Line Performance Heat Grid', route: '/production/analytics/heat-grid', icon: 'Grid', category: 'Dashboard & Analisis', description: 'Volume output per Line pada Mixing, Filling, Packing' },
        { name: 'Grafik Pareto Downtime Mesin', route: '/production/analytics/pareto', icon: 'BarChart2', category: 'Dashboard & Analisis', description: 'Pemeringkatan alasan gangguan & durasi henti' },
        { name: 'Analisis Reject & Cacat Produk', route: '/production/analytics/rejects', icon: 'PieChart', category: 'Dashboard & Analisis', description: 'Reject rate %, sebaran varian produk & tren' },

        { name: 'Master Produk (FG, RM, PM, WIP)', route: '/production/master/products', icon: 'Package', category: 'Master Data Pabrik', description: 'Batas safety, minimum, maximum & id produk Odoo' },
        { name: 'Master Mesin Pabrik', route: '/production/master/machines', icon: 'Cpu', category: 'Master Data Pabrik', description: 'Daftar mesin & Cycle Time standar per pcs' },
        { name: 'Master Jalur / Line Produksi', route: '/production/master/lines', icon: 'GitCommit', category: 'Master Data Pabrik', description: 'Penomoran dan nama jalur perakitan lantai pabrik' },
        { name: 'Master Alasan Downtime', route: '/production/master/downtime-reasons', icon: 'AlertTriangle', category: 'Master Data Pabrik', description: 'Kamus henti kerja, input manual & durasi patokan' },
        { name: 'Parameter & Multiplier Manpower', route: '/production/master/settings', icon: 'Sliders', category: 'Master Data Pabrik', description: 'Target unit per MP, target IRA & toleransi usage' },
        { name: 'Audit Log Riwayat Pabrik', route: '/production/master/history', icon: 'Clock', category: 'Master Data Pabrik', description: 'Rekam jejak perubahan data kritikal manufaktur' },

        { name: 'Weekly Production Schedule (WPS)', route: '/production/schedules', icon: 'Calendar', category: 'Perencanaan PPIC (WPS)', description: 'Jadwal mingguan List View & Board Kanban' },
        { name: 'Tarik MO Terkonfirmasi Odoo ERP', route: '/production/schedules/odoo-sync', icon: 'RefreshCw', category: 'Perencanaan PPIC (WPS)', description: 'Sinkronisasi Manufacturing Order status confirmed' },
        { name: 'Manajemen Packing Hold', route: '/production/schedules/packing-hold', icon: 'PauseCircle', category: 'Perencanaan PPIC (WPS)', description: 'Penundaan pengemasan sekunder tanpa batalkan batch' },

        { name: 'Laporan Harian Produksi (LHP)', route: '/production/daily-reports', icon: 'ClipboardList', category: 'Eksekusi & Timer', description: 'Rekam realitas output fisik & efisiensi kerja' },
        { name: 'Panel Kontrol Timer Produksi', route: '/production/timer-console', icon: 'Timer', category: 'Eksekusi & Timer', description: 'Start, Pause, Resume dengan dialog alasan & End' },
        { name: 'Estafet Batch Tracking Linier', route: '/production/batches', icon: 'Layers', category: 'Eksekusi & Timer', description: 'Pelacakan linier Mixing -> Filling -> Packing' },
        { name: 'Validasi & Kunci Supervisor', route: '/production/daily-reports/approvals', icon: 'Lock', category: 'Eksekusi & Timer', description: 'Penguncian LHP agar tidak dapat diubah operator' },

        { name: 'Pencatatan Detail Reject & Cacat', route: '/production/scrap', icon: 'AlertOctagon', category: 'Reject & Scrap', description: 'Volume unit reject, jenis defect & material' },
        { name: 'Recipe Component Breakdown Reject', route: '/production/scrap/breakdown', icon: 'ListTree', category: 'Reject & Scrap', description: 'Komponen bahan resep BOM yang ikut terbuang' },
        { name: 'Sinkronisasi Odoo Stock Scrap', route: '/production/scrap/odoo-sync', icon: 'Cpu', category: 'Reject & Scrap', description: 'Koreksi saldo persediaan modul stock.scrap Odoo' },
        { name: 'Scrap Material Gudang Pabrik', route: '/production/scrap/warehouse', icon: 'Trash2', category: 'Reject & Scrap', description: 'Kerusakan penanganan & kedaluwarsa di gudang' },

        { name: 'Struktur Bill of Materials (BOM)', route: '/production/boms', icon: 'FileSpreadsheet', category: 'SCM & Persediaan Pabrik', description: 'Explosion resep & bahan kemas primer/sekunder' },
        { name: 'Saldo Stok & Visualisasi Safe Level', route: '/production/stocks', icon: 'Warehouse', category: 'SCM & Persediaan Pabrik', description: 'Stok per produk, nomor batch, expired & lokasi' },
        { name: 'Material Usage (Konsumsi Riil)', route: '/production/material-usage', icon: 'ArrowRightCircle', category: 'SCM & Persediaan Pabrik', description: 'Analisis variansi kuantitas aktual vs standar BOM' },
        { name: 'Stock Opname Persediaan (IRA %)', route: '/production/stock-opname', icon: 'Scale', category: 'SCM & Persediaan Pabrik', description: 'Cycle count gudang & akurasi pencatatan persediaan' },
        { name: 'Cetak Kartu Stelling Fisik Gudang', route: '/production/stock-opname/stelling', icon: 'Printer', category: 'SCM & Persediaan Pabrik', description: 'Cetak formulir kartu stelling fisik ke PDF' },

        { name: 'Rencana Pengiriman (Delivery Plans)', route: '/production/deliveries', icon: 'Truck', category: 'Pengiriman & Logistik Fleet', description: 'Penjadwalan ekspedisi barang jadi ke pelanggan' },
        { name: 'Pre-Flight Stock Check Barang Jadi', route: '/production/deliveries/stock-check', icon: 'ShieldCheck', category: 'Pengiriman & Logistik Fleet', description: 'Validasi saldo fisik FG sebelum cetak dokumen' },
        { name: 'Manajemen Kendaraan & Driver (Fleet)', route: '/production/deliveries/fleet', icon: 'Car', category: 'Pengiriman & Logistik Fleet', description: 'Nomor polisi kendaraan, kapasitas & kontak supir' },
        { name: 'Generator Surat Jalan, Faktur & COA', route: '/production/deliveries/documents', icon: 'FileText', category: 'Pengiriman & Logistik Fleet', description: 'Berkas legal distribusi PDF siap pakai' },
        { name: 'Pengukuran Kinerja Delivery (OTIF)', route: '/production/deliveries/kpi', icon: 'Award', category: 'Pengiriman & Logistik Fleet', description: 'On-time, in-full, damage-free & complaint rate' },

        { name: 'Konektor Odoo ERP (XML-RPC)', route: '/production/integrations/odoo', icon: 'Cpu', category: 'Integrasi Eksternal', description: 'Penyelarasan MO, BOM, stok, scrap & pesanan' },
        { name: 'Konektor Kinerja ke HRIS', route: '/production/integrations/hris', icon: 'Users', category: 'Integrasi Eksternal', description: 'Feed OEE, efisiensi & jam henti ke HRIS' },
        { name: 'Konektor Biaya ke Finance App', route: '/production/integrations/finance', icon: 'DollarSign', category: 'Integrasi Eksternal', description: 'Feed unit terproduksi & rugi downtime ke Finance' },
    ],
    'quality': [
        { name: 'Overview Mutu QMS', route: '/quality', icon: 'LayoutDashboard', category: 'Master Dokumen Mutu', description: 'Pusat Kendali Mutu Farmasi & ISO 9001/CPOB' },
        { name: 'Piramida 4-Tingkat Mutu', route: '/quality/documents', icon: 'FileCheck', category: 'Master Dokumen Mutu', description: 'Manual Mutu, SOP, IK, dan Formulir Rekaman' },
        { name: 'Struktur Folder Bersarang', route: '/quality/documents/folders', icon: 'FolderTree', category: 'Master Dokumen Mutu', description: 'Parent-child tree, warna badge & ikon folder' },
        { name: 'Riwayat Revisi Dokumen Mutu', route: '/quality/documents/revisions', icon: 'History', category: 'Master Dokumen Mutu', description: 'Pencatatan riwayat revisi nomor & change notes' },
        { name: 'Tautan Dokumen Terstruktur', route: '/quality/documents/links', icon: 'Link', category: 'Master Dokumen Mutu', description: 'Penyimpanan multi-link Google Drive & Cloud' },
        { name: 'Kustomisasi Master Options', route: '/quality/documents/master-options', icon: 'Sliders', category: 'Master Dokumen Mutu', description: '12 Ruang Lingkup SOP, Departemen & Rak Arsip' },
        { name: 'Impor & Sinkronisasi Sheets', route: '/quality/documents/sync', icon: 'RefreshCw', category: 'Master Dokumen Mutu', description: 'Impor massal Excel & Google Sheets API sync' },

        { name: 'Daftar Usulan Perubahan', route: '/quality/change-requests', icon: 'GitCommit', category: 'Change Request (CR)', description: 'Pengendalian alur perubahan formula & fasilitas' },
        { name: 'Pengajuan CRA (Mayor dengan FMEA)', route: '/quality/change-requests/cra', icon: 'AlertTriangle', category: 'Change Request (CR)', description: 'Analisis risiko Severity, Occurrence, Detection RPN' },
        { name: 'Pengajuan CRB (Minor tanpa FMEA)', route: '/quality/change-requests/crb', icon: 'FileEdit', category: 'Change Request (CR)', description: 'Perubahan minor alur approval hingga Head QA' },
        { name: 'Kajian Risiko Multi-PIC Dept', route: '/quality/change-requests/pic-assessments', icon: 'Users', category: 'Change Request (CR)', description: 'Kajian teknis PIC R&D, Produksi, QC, Teknik & SCM' },
        { name: 'Verifikasi Stage 1 & Stage 2', route: '/quality/change-requests/stages', icon: 'CheckSquare', category: 'Change Request (CR)', description: 'Stage 1 Evaluasi & Stage 2 Bukti Pelaksanaan' },

        { name: 'Daftar Laporan Deviasi', route: '/quality/deviations', icon: 'AlertOctagon', category: 'Pelaporan Deviasi (DR)', description: 'Temuan ketidaksesuaian operasional & insiden teknis' },
        { name: 'Form Pelaporan Awal Deviasi', route: '/quality/deviations/create', icon: 'FilePlus', category: 'Pelaporan Deviasi (DR)', description: 'Kategori penyimpangan, domino effect & immediate action' },
        { name: 'Penomoran Ganda Otomatis (DR & PK)', route: '/quality/deviations/numbering', icon: 'Tag', category: 'Pelaporan Deviasi (DR)', description: 'Penomoran resmi DR/xxx dan PK/xxx per tahun' },
        { name: 'Penunjukan Atasan Mengetahui', route: '/quality/deviations/superiors', icon: 'UserCheck', category: 'Pelaporan Deviasi (DR)', description: 'Tanggapan atasan terkait & tanda tangan digital' },

        { name: 'Hub Monitoring Penyelidikan', route: '/quality/deviations-investigations', icon: 'Search', category: 'Penyelidikan Ketidaksesuaian', description: 'Monitoring investigasi mendalam akar penyebab' },
        { name: 'Diagram Tulang Ikan (6M Fishbone)', route: '/quality/deviations-investigations/fishbone', icon: 'GitFork', category: 'Penyelidikan Ketidaksesuaian', description: 'Man, Machine, Method, Material, Milieu, Measurement SVG' },
        { name: 'Analisis Akar Masalah 5-Why', route: '/quality/deviations-investigations/five-whys', icon: 'HelpCircle', category: 'Penyelidikan Ketidaksesuaian', description: 'Penelusuran bertahap hingga menemukan root cause' },
        { name: 'Evaluasi Tindakan & Scoring FMEA', route: '/quality/deviations-investigations/fmea', icon: 'Activity', category: 'Penyelidikan Ketidaksesuaian', description: 'Matriks evaluasi dengan skoring 1, 3, 9' },
        { name: 'Keputusan QA (Auto-CAPA / Reject)', route: '/quality/deviations-investigations/decisions', icon: 'CheckCircle2', category: 'Penyelidikan Ketidaksesuaian', description: 'Approve memicu record CAPA / Reject kembali inisiator' },

        { name: 'Daftar Program CAPA', route: '/quality/capa', icon: 'ShieldCheck', category: 'CAPA & Tindakan Korektif', description: 'Corrective and Preventive Action terintegrasi deviasi' },
        { name: 'Sub-CAPA Multi-Tindakan', route: '/quality/capa/actions', icon: 'ListTree', category: 'CAPA & Tindakan Korektif', description: 'Tindakan korektif vs preventif multi-PIC & deadline' },
        { name: 'Pelaksanaan & Upload Bukti Lapangan', route: '/quality/capa/evidences', icon: 'UploadCloud', category: 'CAPA & Tindakan Korektif', description: 'PIC unggah berkas/foto bukti tindakan di lapangan' },
        { name: 'Verifikasi Efektivitas & Close CAPA', route: '/quality/capa/verifications', icon: 'Award', category: 'CAPA & Tindakan Korektif', description: 'Verifikasi bukti oleh QA & penutupan permanen' },

        { name: 'Canvas Signature Pad', route: '/quality/signatures', icon: 'PenTool', category: 'Tanda Tangan & Cetak A4', description: 'Tanda tangan touchscreen & auto-trim whitespace' },
        { name: 'Cetak Form Standar CRA & CRB', route: '/quality/print/change-requests', icon: 'Printer', category: 'Tanda Tangan & Cetak A4', description: 'Form usulan perubahan resmi dengan matriks approval' },
        { name: 'Cetak Formulir Laporan Deviasi (DR)', route: '/quality/print/deviations', icon: 'FileText', category: 'Tanda Tangan & Cetak A4', description: 'Dokumen cetak DR siap audit CPOB' },
        { name: 'Cetak Penyelidikan Ketidaksesuaian', route: '/quality/print/investigations', icon: 'BookOpen', category: 'Tanda Tangan & Cetak A4', description: 'Formulir resmi PK CM-09/QA/003D.01 dengan diagram 6M' },

        { name: 'Manajemen Pengguna Mutu', route: '/quality/admin/users', icon: 'Users', category: 'Admin Mutu & Konfigurasi', description: 'Hak akses Initiator, QA, Head QA, OM, GM, Superadmin' },
        { name: 'Kop Surat & Logo Dokumen Cetak', route: '/quality/admin/branding', icon: 'Image', category: 'Admin Mutu & Konfigurasi', description: 'Kustomisasi identitas perusahaan dokumen cetak' },
        { name: 'Master Nomor Dokumen Mutu', route: '/quality/admin/settings', icon: 'Settings', category: 'Admin Mutu & Konfigurasi', description: 'Konfigurasi penomoran dokumen penyelidikan default' },
    ],
    'rnd': [
        { name: 'Timeline Control Center', route: '/rnd', icon: 'LayoutDashboard', category: 'Dashboard & Pipeline', description: 'Sentral kendali aktivitas seluruh siklus hidup riset NPD' },
        { name: 'Pipeline & Dokumen Terdaftar', route: '/rnd/timeline/pipeline', icon: 'ListTree', category: 'Dashboard & Pipeline', description: 'Tabel interaktif seluruh dokumen R&D lintas modul' },
        { name: 'Product Progress Cards Grid', route: '/rnd/timeline/product-cards', icon: 'Grid', category: 'Dashboard & Pipeline', description: 'Status kelengkapan 13 modul produk secara visual' },
        { name: 'Antrean Pending & Beban Analis', route: '/rnd/timeline/my-pending', icon: 'Inbox', category: 'Dashboard & Pipeline', description: 'Filter antrean approval staff, Manager OM & Direksi GM' },
        { name: 'Audit Feed Aktivitas Tim R&D', route: '/rnd/timeline/audit-feed', icon: 'History', category: 'Dashboard & Pipeline', description: 'Log mutasi data real-time aktivitas tim riset' },

        { name: 'Product Request Form (PRF)', route: '/rnd/prfs', icon: 'FileText', category: 'Fase 1: Insight & Concept', description: 'Formulir permintaan produk baru dari divisi Marketing' },
        { name: 'NPD Proposals & Target COGS', route: '/rnd/npd-proposals', icon: 'Layers', category: 'Fase 1: Insight & Concept', description: 'Proposal inisiasi riset, estimasi COGS & target launch' },

        { name: 'Kajian Preformulasi', route: '/rnd/preformulation-studies', icon: 'Microscope', category: 'Fase 2: Kajian & Formulasi', description: 'Karakterisasi fisika-kimia, kelarutan & kompatibilitas' },
        { name: 'Quality by Design (QbD)', route: '/rnd/qbds', icon: 'Compass', category: 'Fase 2: Kajian & Formulasi', description: 'Penerapan ICH Q8, QTPP, CQA, CMA & analisis risiko' },
        { name: 'Formulasi Bahan Baku (RM)', route: '/rnd/formulas', icon: 'Calculator', category: 'Fase 2: Kajian & Formulasi', description: 'Resep bahan baku presisi total wajib tepat 100%' },
        { name: 'Eksekusi Trial RM (Uji Lab)', route: '/rnd/trial-rms', icon: 'FlaskConical', category: 'Fase 2: Kajian & Formulasi', description: 'Pencampuran & verifikasi fisika-kimia organoleptik' },
        { name: 'Loop Reformulasi Otomatis', route: '/rnd/trial-rms/reformula', icon: 'RefreshCw', category: 'Fase 2: Kajian & Formulasi', description: 'Auto-clone duplikasi formula V1 -> V2 menjaga silsilah' },
        { name: 'Trial PM Kemasan Mesin', route: '/rnd/trial-pms', icon: 'Boxes', category: 'Fase 2: Kajian & Formulasi', description: 'Uji kemasan mesin konsensus 4 departemen' },
        { name: 'Log Book PM (Penerimaan Sampel)', route: '/rnd/logbook-pm', icon: 'BookOpen', category: 'Fase 2: Kajian & Formulasi', description: 'Buku besar penerimaan fisik sampel kemasan supplier' },

        { name: 'Evaluasi Sensori & Panelis', route: '/rnd/sample-evaluations', icon: 'Award', category: 'Fase 2: Evaluation & Testing', description: 'Uji kesukaan konsumen 5 parameter organoleptik' },
        { name: 'Uji Stabilitas Sediaan', route: '/rnd/stability-tests', icon: 'Thermometer', category: 'Fase 2: Evaluation & Testing', description: 'Accelerated 40°C & Real-Time 30°C interval berkala' },

        { name: 'Pengesahan Formula Resmi', route: '/rnd/formula-approvals', icon: 'FileCheck', category: 'Fase 2: Pengesahan & Desain', description: 'Pengesahan legal formula dengan matriks tanda tangan direksi' },
        { name: 'Desain Artwork Kemasan', route: '/rnd/design-approvals', icon: 'Image', category: 'Fase 2: Pengesahan & Desain', description: 'Persetujuan materi grafis kemasan multi-stakeholder' },

        { name: 'Regulatory Dossier (DMS BPOM)', route: '/rnd/regulatory-dossier', icon: 'FolderArchive', category: 'Regulasi & Alih Teknologi', description: 'DMS folder bersarang registrasi izin edar & ekspor ZIP' },
        { name: 'Technology Transfer ke Pabrik', route: '/rnd/technology-transfers', icon: 'ArrowRightLeft', category: 'Regulasi & Alih Teknologi', description: 'Protokol transfer parameter proses lab ke komersial' },
        { name: 'NIE Approved (Nomor Izin Edar)', route: '/rnd/nie-approvals', icon: 'CheckCircle2', category: 'Regulasi & Alih Teknologi', description: 'Perekaman nomor izin edar resmi Badan POM RI' },
        { name: 'Commercial Production DMS', route: '/rnd/commercial-productions', icon: 'Factory', category: 'Regulasi & Alih Teknologi', description: 'Pengarsipan catatan CPB, CPK & rilis batch komersial' },

        { name: 'Approval Center (Single Inbox)', route: '/rnd/approval-center', icon: 'CheckSquare', category: 'Approval Center Tunggal', description: 'Kotak masuk tunggal telaah OM & persetujuan GM' },

        { name: 'Master Produk & Kategori', route: '/rnd/products', icon: 'Package', category: 'Master Data & Integrasi', description: 'Klasifikasi herbal, suplemen, kosmetik & pangan' },
        { name: 'Master Bahan Baku & Supplier', route: '/rnd/materials', icon: 'Boxes', category: 'Master Data & Integrasi', description: 'Bahan aktif, eksipien, status trial vs commercial & CoA' },
        { name: 'Katalog Harga Bahan Pemasok', route: '/rnd/material-suppliers', icon: 'DollarSign', category: 'Master Data & Integrasi', description: 'Multi-supplier per bahan, fluktuasi & harga aktif' },
        { name: 'Integrasi Feed Harga Pengadaan', route: '/rnd/procurement-prices', icon: 'Cpu', category: 'Master Data & Integrasi', description: 'Inbound webhook PO pengadaan & manual pull API' },
        { name: 'Mapping Alias Bahan Pengadaan', route: '/rnd/procurement-prices/mapping', icon: 'Tag', category: 'Master Data & Integrasi', description: 'Pencocokan nama bahan feed ke master R&D & apply ke HPP' },
        { name: 'Administrasi & Pengaturan R&D', route: '/rnd/admin/settings', icon: 'Settings', category: 'Master Data & Integrasi', description: 'Manajemen tim analis, koneksi API sandbox & parameter' },
    ],
    'supply-chain': [
        { name: 'Overview LogiFlow SCM', route: '/supply-chain', icon: 'LayoutDashboard', category: 'Master Data SCM', description: 'Closed-Loop Supply Chain & Manufacturing Hub' },
        { name: 'Master Produk (RM, PM, Bulk, FG)', route: '/supply-chain/products', icon: 'Package', category: 'Master Data SCM', description: 'Parameter safety stock, lead time & reorder point' },
        { name: 'Master BOM / Resep Produk', route: '/supply-chain/boms', icon: 'FileSpreadsheet', category: 'Master Data SCM', description: 'Komposisi bahan presisi 4 desimal & versioning' },
        { name: 'Pusat Kerja (Work Center)', route: '/supply-chain/work-centers', icon: 'Factory', category: 'Master Data SCM', description: 'Stasiun mesin pabrik, kapasitas & tarif overhead/jam' },
        { name: 'Master Rekanan (Vendor)', route: '/supply-chain/vendors', icon: 'Building2', category: 'Master Data SCM', description: 'Pemasok resmi, NPWP, termin TOP & performa kirim' },
        { name: 'Master Satuan & Parameter', route: '/supply-chain/parameters', icon: 'Sliders', category: 'Master Data SCM', description: 'UOM internasional, konversi & hierarki departemen' },

        { name: 'Riwayat Data Penjualan', route: '/supply-chain/sales-data', icon: 'BarChart3', category: 'Perencanaan & PPIC', description: 'Volume riil penjualan per SKU per bulan' },
        { name: 'Peramalan Penjualan (Forecast)', route: '/supply-chain/forecasts', icon: 'TrendingUp', category: 'Perencanaan & PPIC', description: 'Annual & 3-6 bulan Rolling Forecast' },
        { name: 'Master Production Schedule (MPS)', route: '/supply-chain/mps', icon: 'CalendarCheck', category: 'Perencanaan & PPIC', description: 'Jadwal induk mingguan/bulanan SKU produk jadi' },
        { name: 'Kalkulator Kebutuhan Bersih (MRP)', route: '/supply-chain/mrp', icon: 'Cpu', category: 'Perencanaan & PPIC', description: 'Ledakan kebutuhan bersih material just-in-time' },
        { name: 'Peringatan Cerdas Persediaan', route: '/supply-chain/alerts', icon: 'AlertTriangle', category: 'Perencanaan & PPIC', description: 'Under-stock, expired <90 hari & overdue PO' },

        { name: 'Target Omset (Revenue Budget)', route: '/supply-chain/budgets/revenue', icon: 'DollarSign', category: 'Kontrol Anggaran (Budget)', description: 'Target penerimaan tahunan/bulanan korporasi' },
        { name: 'Alokasi Anggaran Departemen', route: '/supply-chain/budgets/departments', icon: 'Landmark', category: 'Kontrol Anggaran (Budget)', description: 'Distribusi persentase pagu departemen disetujui' },
        { name: 'Matriks Cost Center & Purpose', route: '/supply-chain/cost-centers', icon: 'Layers', category: 'Kontrol Anggaran (Budget)', description: 'Pemetaan pusat biaya & shared budget lintas divisi' },
        { name: 'Monitoring Komitmen Anggaran', route: '/supply-chain/budgets/monitoring', icon: 'Wallet', category: 'Kontrol Anggaran (Budget)', description: 'Real-time budget commit, release & warning tiers' },

        { name: 'PR Operasional (BOM-Based)', route: '/supply-chain/procurement/operational-pr', icon: 'ClipboardCheck', category: 'Pengadaan Terpadu', description: 'Otomatis ledakan kebutuhan bahan dari target batch' },
        { name: 'PR Non-Operasional', route: '/supply-chain/procurement/non-operational-pr', icon: 'FileText', category: 'Pengadaan Terpadu', description: 'Pengadaan umum, ATK & suku cadang mesin' },
        { name: 'Form Estimasi Harga Pasar', route: '/supply-chain/procurement/estimates', icon: 'Tag', category: 'Pengadaan Terpadu', description: 'Estimasi harga satuan riil sebelum ditinjau FAT' },
        { name: 'Penerbitan PO Multi-Vendor', route: '/supply-chain/procurement/purchase-orders', icon: 'ShoppingCart', category: 'Pengadaan Terpadu', description: 'Penerbitan PO rekanan berurutan resmi' },

        { name: 'Inbound Goods Receipt (GRN)', route: '/supply-chain/goods-receipt', icon: 'Truck', category: 'Pergudangan & Inventori', description: 'Penerimaan fisik barang & pembentukan nomor GRN' },
        { name: 'Karantina QC (Uji 4-Parameter)', route: '/supply-chain/quarantine', icon: 'ShieldCheck', category: 'Pergudangan & Inventori', description: 'Fisik, Kimia, Mikrobiologi, Penandaan kemasan' },
        { name: 'Saldo Stok Multi-Gudang', route: '/supply-chain/stock', icon: 'Warehouse', category: 'Pergudangan & Inventori', description: 'Ledger batch, exp date & lokasi penyimpanan fisik' },
        { name: 'Permintaan Pemakaian Bahan (BSTB)', route: '/supply-chain/material-usage', icon: 'ArrowRightCircle', category: 'Pergudangan & Inventori', description: 'Bukti serah terima bahan ke lini perakitan pabrik' },
        { name: 'Pengembalian Bahan ke Vendor', route: '/supply-chain/material-return', icon: 'CornerUpLeft', category: 'Pergudangan & Inventori', description: 'Pengembalian barang reject atau cacat kirim vendor' },
        { name: 'Stock Opname & Akurasi IRA', route: '/supply-chain/stock-opname', icon: 'Scale', category: 'Pergudangan & Inventori', description: 'Snapshot saldo, hitung fisik & persentase IRA %' },
        { name: 'Manajemen Scrap Gudang', route: '/supply-chain/warehouse-scrap', icon: 'AlertOctagon', category: 'Pergudangan & Inventori', description: 'Pencatatan kerusakan & penurunan mutu di gudang' },

        { name: 'Work Order Produksi (SPK)', route: '/supply-chain/production/work-orders', icon: 'Wrench', category: 'Manufaktur & Mutu', description: 'Mandat resmi lantai kerja mengikat batch & BOM' },
        { name: 'Pipeline Inspeksi Mutu', route: '/supply-chain/quality/inspections', icon: 'CheckCircle2', category: 'Manufaktur & Mutu', description: 'Pengujian mutu tahapan pengolahan' },
        { name: 'Rilis Sertifikat CoA & CPB', route: '/supply-chain/quality/releases', icon: 'Award', category: 'Manufaktur & Mutu', description: 'Persetujuan rilis batch produk oleh QA' },

        { name: '3-Way Matching Bill', route: '/supply-chain/matching', icon: 'FileCheck2', category: 'Keuangan & Hutang (AP)', description: 'Pencocokan PO vs GRN Penerimaan vs Invoice Vendor' },
        { name: 'Payment Voucher & Hutang Usaha', route: '/supply-chain/finance/vouchers', icon: 'Receipt', category: 'Keuangan & Hutang (AP)', description: 'Penerbitan voucher pembayaran hutang dagang' },
        { name: 'Akuntansi Biaya (HPP Produk)', route: '/supply-chain/finance/cogs', icon: 'Calculator', category: 'Keuangan & Hutang (AP)', description: 'Kalkulasi HPP gabungan bahan, upah & overhead' },
    ],
    'supply_chain': [
        { name: 'Overview LogiFlow SCM', route: '/supply-chain', icon: 'LayoutDashboard', category: 'Master Data SCM', description: 'Closed-Loop Supply Chain & Manufacturing Hub' },
        { name: 'Master Produk (RM, PM, Bulk, FG)', route: '/supply-chain/products', icon: 'Package', category: 'Master Data SCM', description: 'Parameter safety stock, lead time & reorder point' },
        { name: 'Master BOM / Resep Produk', route: '/supply-chain/boms', icon: 'FileSpreadsheet', category: 'Master Data SCM', description: 'Komposisi bahan presisi 4 desimal & versioning' },
        { name: 'Pusat Kerja (Work Center)', route: '/supply-chain/work-centers', icon: 'Factory', category: 'Master Data SCM', description: 'Stasiun mesin pabrik, kapasitas & tarif overhead/jam' },
        { name: 'Master Rekanan (Vendor)', route: '/supply-chain/vendors', icon: 'Building2', category: 'Master Data SCM', description: 'Pemasok resmi, NPWP, termin TOP & performa kirim' },
        { name: 'Master Satuan & Parameter', route: '/supply-chain/parameters', icon: 'Sliders', category: 'Master Data SCM', description: 'UOM internasional, konversi & hierarki departemen' },

        { name: 'Riwayat Data Penjualan', route: '/supply-chain/sales-data', icon: 'BarChart3', category: 'Perencanaan & PPIC', description: 'Volume riil penjualan per SKU per bulan' },
        { name: 'Peramalan Penjualan (Forecast)', route: '/supply-chain/forecasts', icon: 'TrendingUp', category: 'Perencanaan & PPIC', description: 'Annual & 3-6 bulan Rolling Forecast' },
        { name: 'Master Production Schedule (MPS)', route: '/supply-chain/mps', icon: 'CalendarCheck', category: 'Perencanaan & PPIC', description: 'Jadwal induk mingguan/bulanan SKU produk jadi' },
        { name: 'Kalkulator Kebutuhan Bersih (MRP)', route: '/supply-chain/mrp', icon: 'Cpu', category: 'Perencanaan & PPIC', description: 'Ledakan kebutuhan bersih material just-in-time' },
        { name: 'Peringatan Cerdas Persediaan', route: '/supply-chain/alerts', icon: 'AlertTriangle', category: 'Perencanaan & PPIC', description: 'Under-stock, expired <90 hari & overdue PO' },

        { name: 'Target Omset (Revenue Budget)', route: '/supply-chain/budgets/revenue', icon: 'DollarSign', category: 'Kontrol Anggaran (Budget)', description: 'Target penerimaan tahunan/bulanan korporasi' },
        { name: 'Alokasi Anggaran Departemen', route: '/supply-chain/budgets/departments', icon: 'Landmark', category: 'Kontrol Anggaran (Budget)', description: 'Distribusi persentase pagu departemen disetujui' },
        { name: 'Matriks Cost Center & Purpose', route: '/supply-chain/cost-centers', icon: 'Layers', category: 'Kontrol Anggaran (Budget)', description: 'Pemetaan pusat biaya & shared budget lintas divisi' },
        { name: 'Monitoring Komitmen Anggaran', route: '/supply-chain/budgets/monitoring', icon: 'Wallet', category: 'Kontrol Anggaran (Budget)', description: 'Real-time budget commit, release & warning tiers' },

        { name: 'PR Operasional (BOM-Based)', route: '/supply-chain/procurement/operational-pr', icon: 'ClipboardCheck', category: 'Pengadaan Terpadu', description: 'Otomatis ledakan kebutuhan bahan dari target batch' },
        { name: 'PR Non-Operasional', route: '/supply-chain/procurement/non-operational-pr', icon: 'FileText', category: 'Pengadaan Terpadu', description: 'Pengadaan umum, ATK & suku cadang mesin' },
        { name: 'Form Estimasi Harga Pasar', route: '/supply-chain/procurement/estimates', icon: 'Tag', category: 'Pengadaan Terpadu', description: 'Estimasi harga satuan riil sebelum ditinjau FAT' },
        { name: 'Penerbitan PO Multi-Vendor', route: '/supply-chain/procurement/purchase-orders', icon: 'ShoppingCart', category: 'Pengadaan Terpadu', description: 'Penerbitan PO rekanan berurutan resmi' },

        { name: 'Inbound Goods Receipt (GRN)', route: '/supply-chain/goods-receipt', icon: 'Truck', category: 'Pergudangan & Inventori', description: 'Penerimaan fisik barang & pembentukan nomor GRN' },
        { name: 'Karantina QC (Uji 4-Parameter)', route: '/supply-chain/quarantine', icon: 'ShieldCheck', category: 'Pergudangan & Inventori', description: 'Fisik, Kimia, Mikrobiologi, Penandaan kemasan' },
        { name: 'Saldo Stok Multi-Gudang', route: '/supply-chain/stock', icon: 'Warehouse', category: 'Pergudangan & Inventori', description: 'Ledger batch, exp date & lokasi penyimpanan fisik' },
        { name: 'Permintaan Pemakaian Bahan (BSTB)', route: '/supply-chain/material-usage', icon: 'ArrowRightCircle', category: 'Pergudangan & Inventori', description: 'Bukti serah terima bahan ke lini perakitan pabrik' },
        { name: 'Pengembalian Bahan ke Vendor', route: '/supply-chain/material-return', icon: 'CornerUpLeft', category: 'Pergudangan & Inventori', description: 'Pengembalian barang reject atau cacat kirim vendor' },
        { name: 'Stock Opname & Akurasi IRA', route: '/supply-chain/stock-opname', icon: 'Scale', category: 'Pergudangan & Inventori', description: 'Snapshot saldo, hitung fisik & persentase IRA %' },
        { name: 'Manajemen Scrap Gudang', route: '/supply-chain/warehouse-scrap', icon: 'AlertOctagon', category: 'Pergudangan & Inventori', description: 'Pencatatan kerusakan & penurunan mutu di gudang' },

        { name: 'Work Order Produksi (SPK)', route: '/supply-chain/production/work-orders', icon: 'Wrench', category: 'Manufaktur & Mutu', description: 'Mandat resmi lantai kerja mengikat batch & BOM' },
        { name: 'Pipeline Inspeksi Mutu', route: '/supply-chain/quality/inspections', icon: 'CheckCircle2', category: 'Manufaktur & Mutu', description: 'Pengujian mutu tahapan pengolahan' },
        { name: 'Rilis Sertifikat CoA & CPB', route: '/supply-chain/quality/releases', icon: 'Award', category: 'Manufaktur & Mutu', description: 'Persetujuan rilis batch produk oleh QA' },

        { name: '3-Way Matching Bill', route: '/supply-chain/matching', icon: 'FileCheck2', category: 'Keuangan & Hutang (AP)', description: 'Pencocokan PO vs GRN Penerimaan vs Invoice Vendor' },
        { name: 'Payment Voucher & Hutang Usaha', route: '/supply-chain/finance/vouchers', icon: 'Receipt', category: 'Keuangan & Hutang (AP)', description: 'Penerbitan voucher pembayaran hutang dagang' },
        { name: 'Akuntansi Biaya (HPP Produk)', route: '/supply-chain/finance/cogs', icon: 'Calculator', category: 'Keuangan & Hutang (AP)', description: 'Kalkulasi HPP gabungan bahan, upah & overhead' },
    ],
    'settings': [
        { name: 'Pengaturan Sistem', route: '/settings', icon: 'Settings', category: 'Admin', description: 'Konfigurasi branding, tema, nomor dokumen & API' },
        { name: 'Manajemen Pengguna', route: '/settings/users', icon: 'Users', category: 'Admin', description: 'Kelola hak akses modul & menu per user' },
    ],
};

const defaultModuleInfo = {
    'finance': { name: 'Finance & Budgeting', color_theme: 'emerald' },
    'general-affair': { name: 'Fasilitas & Umum (FPUGA)', color_theme: 'amber' },
    'general_affair': { name: 'Fasilitas & Umum (FPUGA)', color_theme: 'amber' },
    'hris': { name: 'Human Resource (HRIS)', color_theme: 'blue' },
    'procurement': { name: 'Procurement & Purchasing', color_theme: 'violet' },
    'production': { name: 'Produksi & Manufaktur', color_theme: 'rose' },
    'produksi': { name: 'Produksi & Manufaktur', color_theme: 'rose' },
    'quality': { name: 'Quality Assurance (QMS)', color_theme: 'teal' },
    'rnd': { name: 'R&D (NPD Management)', color_theme: 'cyan' },
    'supply-chain': { name: 'Supply Chain & Inventory', color_theme: 'emerald' },
    'supply_chain': { name: 'Supply Chain & Inventory', color_theme: 'emerald' },
    'settings': { name: 'Pengaturan Sistem & User', color_theme: 'slate' },
};

const props = defineProps({
    title: String,
    module: {
        type: Object,
        default: null,
    },
    moduleSlug: {
        type: String,
        default: '',
    },
    moduleName: {
        type: String,
        default: '',
    },
    colorTheme: {
        type: String,
        default: '',
    },
    menus: {
        type: Array,
        default: () => [],
    },
    breadcrumbs: {
        type: Array,
        default: () => [],
    },
    currentModule: {
        type: String,
        default: '',
    },
});

const page = usePage();
const devStore = useDeveloperModeStore();
const themeStore = useThemeStore();

const showLauncher = ref(false);
const showPalette = ref(false);
const isSidebarOpen = ref(true);
const isMobileSidebarOpen = ref(false);

const currentPathSlug = computed(() => {
    const url = page.url || '';
    const segments = url.replace(/^\//, '').split('?')[0].split('/').filter(Boolean);
    if (segments.length === 0) return '';
    if (segments[0] === 'modules' && segments.length > 1) {
        return segments[1];
    }
    return segments[0] || '';
});

// Resolve props: prefer explicit props, fall back to module object, then shared state, then path
const resolvedSlug = computed(() => {
    const raw = props.moduleSlug || props.currentModule || props.module?.slug || currentPathSlug.value || '';
    if (raw === 'produksi') return 'production';
    return raw;
});

const resolvedName = computed(() => {
    if (props.moduleName) return props.moduleName;
    if (props.module?.name) return props.module.name;
    if (props.module?.module_name) return props.module.module_name;

    const sharedModules = page.props.erp?.modules || [];
    const found = sharedModules.find(m => m.slug === resolvedSlug.value || (resolvedSlug.value === 'production' && m.slug === 'produksi'));
    if (found?.name) return found.name;

    return defaultModuleInfo[resolvedSlug.value]?.name || 'Modul ERP';
});

const resolvedTheme = computed(() => {
    if (props.colorTheme) return props.colorTheme;
    if (props.module?.color_theme) return props.module.color_theme;
    if (props.module?.colorTheme) return props.module.colorTheme;

    const sharedModules = page.props.erp?.modules || [];
    const found = sharedModules.find(m => m.slug === resolvedSlug.value || (resolvedSlug.value === 'production' && m.slug === 'produksi'));
    if (found?.color_theme) return found.color_theme;

    return defaultModuleInfo[resolvedSlug.value]?.color_theme || 'emerald';
});

const resolvedMenus = computed(() => {
    if (props.menus && props.menus.length > 0) return props.menus;
    if (props.module?.menus && props.module.menus.length > 0) return props.module.menus;

    const sharedModules = page.props.erp?.modules || [];
    const found = sharedModules.find(m => m.slug === resolvedSlug.value || (resolvedSlug.value === 'production' && m.slug === 'produksi'));
    if (found?.menus && found.menus.length > 0) return found.menus;

    const key = resolvedSlug.value.replace(/_/g, '-');
    if (defaultModuleMenus[key]) {
        return defaultModuleMenus[key];
    }
    if (defaultModuleMenus[resolvedSlug.value]) {
        return defaultModuleMenus[resolvedSlug.value];
    }

    return [];
});

function isMenuActive(routeUrl) {
    if (!routeUrl) return false;
    const currentPath = (page.url || '').split('?')[0];
    const targetPath = routeUrl.split('?')[0];
    if (currentPath === targetPath) return true;
    const targetSegments = targetPath.split('/').filter(Boolean);
    if (targetSegments.length > 1 && currentPath.startsWith(targetPath)) {
        return true;
    }
    return false;
}

const iconMap = {
    LayoutDashboard,
    ListTree,
    PieChart,
    FileSpreadsheet,
    Scale,
    Wrench,
    FileText,
    Building2,
    QrCode,
    Users,
    Clock,
    Calendar,
    Banknote,
    Award,
    FileCheck,
    ShoppingCart,
    DollarSign,
    Building,
    Timer,
    Gauge,
    AlertTriangle,
    GitCommit,
    AlertOctagon,
    CheckCircle2,
    Layers,
    Calculator,
    FlaskConical,
    FileCheck2,
    RefreshCw,
    Boxes,
    ClipboardCheck,
    CheckCircle,
    Warehouse,
    Folder,
    Factory,
    ShieldCheck,
    Truck,
    Landmark,
    Target,
    CalendarOff,
    CalendarCheck,
    Lightbulb,
    BarChart3,
    Activity,
    CheckCheck,
    Package,
    Sparkles,
    Receipt,
    Wallet,
    Tag,
    Cpu,
    ArrowRightCircle,
    CornerUpLeft,
    BookOpen,
    Settings,
    TrendingUp,
    Coins,
    Share2,
    Sliders,
    ScanFace,
    UserCheck,
    ArrowLeftRight,
    CalendarDays,
    HeartHandshake,
    AlertCircle,
    LogOut,
    Users2,
    Hourglass,
    ClipboardList,
    GraduationCap,
    CheckSquare,
    ListChecks,
    Stethoscope,
    FileHeart,
    BadgePercent,
    Compass,
    Briefcase,
    Database,
    BarChart,
    Shield,
    Grid,
    BarChart2,
    PauseCircle,
    Lock,
    Trash2,
    Car,
    FolderTree,
    History,
    Link: LinkIcon,
    FileEdit,
    FilePlus,
    GitFork,
    HelpCircle,
    UploadCloud,
    PenTool,
    Image,
    PackagePlus,
    ScanLine,
    Camera,
    LifeBuoy,
    Kanban,
    MessageSquare,
    Microscope,
    Thermometer,
    FolderArchive,
    ArrowRightLeft,
    Inbox,
    ShieldAlert,
};

function getMenuIcon(iconName) {
    return iconMap[iconName] || Folder;
}

function getBreadcrumbLabel(crumb) {
    if (!crumb) return '';
    if (typeof crumb === 'object') {
        return crumb.name || crumb.label || crumb.title || '';
    }
    return String(crumb);
}

const menuFilterQuery = ref('');
const collapsedCategories = ref({});

function toggleCategory(category) {
    collapsedCategories.value[category] = !collapsedCategories.value[category];
}

const groupedMenus = computed(() => {
    const list = resolvedMenus.value || [];
    const query = menuFilterQuery.value.trim().toLowerCase();

    const filtered = query
        ? list.filter(m => (m.name && m.name.toLowerCase().includes(query)) ||
                           (m.category && m.category.toLowerCase().includes(query)) ||
                           (m.description && m.description.toLowerCase().includes(query)))
        : list;

    const groups = {};
    for (const menu of filtered) {
        const cat = menu.category || 'Navigasi';
        if (!groups[cat]) {
            groups[cat] = [];
        }
        groups[cat].push(menu);
    }
    return Object.entries(groups).map(([category, items]) => ({
        category,
        items,
    }));
});

// Auto-expand category that contains active route
watchEffect(() => {
    for (const group of groupedMenus.value) {
        if (group.items.some(m => isMenuActive(m.route))) {
            collapsedCategories.value[group.category] = false;
        }
    }
});

const themeColorStyles = computed(() => {
    const map = {
        emerald: {
            accent: 'bg-emerald-600 text-white',
            lightBadge: 'bg-emerald-50 text-emerald-800 dark:bg-emerald-950/60 dark:text-emerald-300 border-emerald-200 dark:border-emerald-800',
            activeMenu: 'bg-emerald-50 dark:bg-emerald-950/50 text-emerald-800 dark:text-emerald-300 font-semibold border-l-4 border-emerald-600',
            iconColor: 'text-emerald-600 dark:text-emerald-400',
        },
        amber: {
            accent: 'bg-amber-600 text-white',
            lightBadge: 'bg-amber-50 text-amber-800 dark:bg-amber-950/60 dark:text-amber-300 border-amber-200 dark:border-amber-800',
            activeMenu: 'bg-amber-50 dark:bg-amber-950/50 text-amber-800 dark:text-amber-300 font-semibold border-l-4 border-amber-600',
            iconColor: 'text-amber-600 dark:text-amber-400',
        },
        blue: {
            accent: 'bg-blue-600 text-white',
            lightBadge: 'bg-blue-50 text-blue-800 dark:bg-blue-950/60 dark:text-blue-300 border-blue-200 dark:border-blue-800',
            activeMenu: 'bg-blue-50 dark:bg-blue-950/50 text-blue-800 dark:text-blue-300 font-semibold border-l-4 border-blue-600',
            iconColor: 'text-blue-600 dark:text-blue-400',
        },
        violet: {
            accent: 'bg-violet-600 text-white',
            lightBadge: 'bg-violet-50 text-violet-800 dark:bg-violet-950/60 dark:text-violet-300 border-violet-200 dark:border-violet-800',
            activeMenu: 'bg-violet-50 dark:bg-violet-950/50 text-violet-800 dark:text-violet-300 font-semibold border-l-4 border-violet-600',
            iconColor: 'text-violet-600 dark:text-violet-400',
        },
        rose: {
            accent: 'bg-rose-600 text-white',
            lightBadge: 'bg-rose-50 text-rose-800 dark:bg-rose-950/60 dark:text-rose-300 border-rose-200 dark:border-rose-800',
            activeMenu: 'bg-rose-50 dark:bg-rose-950/50 text-rose-800 dark:text-rose-300 font-semibold border-l-4 border-rose-600',
            iconColor: 'text-rose-600 dark:text-rose-400',
        },
        teal: {
            accent: 'bg-teal-600 text-white',
            lightBadge: 'bg-teal-50 text-teal-800 dark:bg-teal-950/60 dark:text-teal-300 border-teal-200 dark:border-teal-800',
            activeMenu: 'bg-teal-50 dark:bg-teal-950/50 text-teal-800 dark:text-teal-300 font-semibold border-l-4 border-teal-600',
            iconColor: 'text-teal-600 dark:text-teal-400',
        },
        cyan: {
            accent: 'bg-cyan-600 text-white',
            lightBadge: 'bg-cyan-50 text-cyan-800 dark:bg-cyan-950/60 dark:text-cyan-300 border-cyan-200 dark:border-cyan-800',
            activeMenu: 'bg-cyan-50 dark:bg-cyan-950/50 text-cyan-800 dark:text-cyan-300 font-semibold border-l-4 border-cyan-600',
            iconColor: 'text-cyan-600 dark:text-cyan-400',
        },
        indigo: {
            accent: 'bg-indigo-600 text-white',
            lightBadge: 'bg-indigo-50 text-indigo-800 dark:bg-indigo-950/60 dark:text-indigo-300 border-indigo-200 dark:border-indigo-800',
            activeMenu: 'bg-indigo-50 dark:bg-indigo-950/50 text-indigo-800 dark:text-indigo-300 font-semibold border-l-4 border-indigo-600',
            iconColor: 'text-indigo-600 dark:text-indigo-400',
        },
    };
    return map[resolvedTheme.value] || map.emerald;
});

// Global keyboard shortcut for Ctrl + K
if (typeof window !== 'undefined') {
    window.addEventListener('keydown', (e) => {
        if ((e.ctrlKey || e.metaKey) && e.key.toLowerCase() === 'k') {
            e.preventDefault();
            showPalette.value = !showPalette.value;
        }
    });
}
</script>

<template>
    <div class="min-h-screen bg-slate-50/80 dark:bg-slate-950 text-slate-800 dark:text-slate-100 flex flex-col font-sans transition-colors duration-200">
        <!-- Top Workspace Header -->
        <header class="sticky top-0 z-40 bg-white/95 dark:bg-slate-900/95 backdrop-blur border-b border-slate-200 dark:border-slate-800 shadow-xs">
            <div class="px-4 sm:px-6 lg:px-8">
                <div class="flex items-center justify-between h-16">
                    <!-- Left: 9-Dots App Launcher, Module Logo & Breadcrumbs -->
                    <div class="flex items-center gap-3">
                        <!-- 9-Dots Launcher Button to open App Launcher or go back -->
                        <button
                            @click="showLauncher = true"
                            class="p-2 rounded-xl text-slate-600 dark:text-slate-300 hover:text-emerald-700 dark:hover:text-emerald-400 hover:bg-slate-100 dark:hover:bg-slate-800 transition border border-transparent hover:border-slate-200 dark:hover:border-slate-700"
                            title="Buka App Launcher (Semua Modul)"
                        >
                            <LayoutGrid class="w-5 h-5" />
                        </button>

                        <div class="h-6 w-px bg-slate-200 dark:bg-slate-700" />

                        <!-- Module Brand & Breadcrumbs -->
                        <div class="flex items-center gap-2">
                            <Link href="/" class="hidden sm:flex items-center gap-1.5 text-xs font-semibold text-slate-500 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white">
                                <span>ERP</span>
                            </Link>

                            <ChevronRight class="hidden sm:block w-3.5 h-3.5 text-slate-400" />

                            <div class="flex items-center gap-2">
                                <span :class="['px-2 py-0.5 rounded text-xs font-bold border', themeColorStyles.lightBadge]">
                                    {{ resolvedName }}
                                </span>
                            </div>

                            <template v-if="breadcrumbs.length > 0">
                                <ChevronRight class="w-3.5 h-3.5 text-slate-400" />
                                <span class="text-xs text-slate-700 dark:text-slate-200 font-medium truncate max-w-[150px] sm:max-w-xs">
                                    {{ getBreadcrumbLabel(breadcrumbs[breadcrumbs.length - 1]) }}
                                </span>
                            </template>
                        </div>
                    </div>

                    <!-- Right Controls: Global Search (Ctrl + K), Dark Mode, Dev Mode, Notifications, User -->
                    <div class="flex items-center gap-2 sm:gap-3">
                        <!-- Global Search Trigger Button (Ctrl + K) -->
                        <button
                            @click="showPalette = true"
                            class="flex items-center gap-2 px-3 py-1.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 text-slate-500 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-700/60 transition text-xs shadow-2xs"
                        >
                            <Search class="w-3.5 h-3.5" />
                            <span class="hidden md:inline">Cari dokumen/menu...</span>
                            <span class="font-mono text-[10px] px-1.5 py-0.5 rounded bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700">
                                Ctrl + K
                            </span>
                        </button>

                        <!-- Dark / Light Mode Toggle -->
                        <button
                            @click="themeStore.toggleTheme"
                            class="p-2 rounded-xl text-slate-500 dark:text-slate-400 hover:text-slate-700 dark:hover:text-slate-200 hover:bg-slate-100 dark:hover:bg-slate-800 transition"
                            :title="themeStore.isDark ? 'Beralih ke Light Mode' : 'Beralih ke Dark Mode'"
                        >
                            <Sun v-if="themeStore.isDark" class="w-4 h-4 text-amber-400" />
                            <Moon v-else class="w-4 h-4 text-slate-600" />
                        </button>

                        <!-- Developer Mode Button -->
                        <button
                            @click="devStore.toggle"
                            :class="[
                                'hidden sm:flex items-center gap-1.5 px-2.5 py-1.5 rounded-lg text-xs font-mono font-medium border transition',
                                devStore.isEnabled
                                    ? 'bg-emerald-600 text-white border-emerald-600'
                                    : 'bg-slate-50 dark:bg-slate-800 text-slate-600 dark:text-slate-400 border-slate-200 dark:border-slate-700 hover:bg-slate-100 dark:hover:bg-slate-700',
                            ]"
                        >
                            <Terminal class="w-3.5 h-3.5" />
                            <span class="hidden lg:inline">Dev Mode</span>
                        </button>

                        <!-- Settings & Admin Links -->
                        <Link
                            href="/settings"
                            class="p-2 rounded-xl text-slate-500 dark:text-slate-400 hover:text-emerald-700 dark:hover:text-emerald-400 hover:bg-slate-100 dark:hover:bg-slate-800 transition"
                            title="Pengaturan Sistem & Branding"
                        >
                            <Settings class="w-4 h-4" />
                        </Link>

                        <Link
                            href="/settings/users"
                            class="p-2 rounded-xl text-slate-500 dark:text-slate-400 hover:text-emerald-700 dark:hover:text-emerald-400 hover:bg-slate-100 dark:hover:bg-slate-800 transition"
                            title="Manajemen Pengguna & Hak Akses"
                        >
                            <Users class="w-4 h-4" />
                        </Link>

                        <!-- Notification Center Lonceng -->
                        <NotificationCenterDropdown />

                        <!-- Mobile Sidebar Toggle -->
                        <button
                            @click="isMobileSidebarOpen = !isMobileSidebarOpen"
                            class="md:hidden p-2 rounded-xl text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800"
                        >
                            <Menu class="w-5 h-5" />
                        </button>

                        <!-- User Profile Menu -->
                        <div class="hidden sm:flex items-center gap-2 pl-2 border-l border-slate-200 dark:border-slate-800">
                            <div class="w-8 h-8 rounded-full bg-slate-800 dark:bg-emerald-800 text-white flex items-center justify-center font-semibold text-xs shadow-xs">
                                SA
                            </div>
                            <div class="hidden xl:block text-left text-xs leading-tight">
                                <div class="font-semibold text-slate-800 dark:text-slate-200">Super Administrator</div>
                                <div class="text-[10px] text-emerald-600 dark:text-emerald-400 font-medium">System Architect</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </header>

        <!-- Body Workspace with Sidebar & Main Content -->
        <div class="flex-1 flex max-w-7xl w-full mx-auto px-4 sm:px-6 lg:px-8 py-6 gap-6">
            <!-- Contextual Sidebar (Desktop) -->
            <aside
                :class="[
                    'hidden md:block transition-all duration-200 shrink-0',
                    isSidebarOpen ? 'w-64' : 'w-16',
                ]"
            >
                <div class="sticky top-24 bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 shadow-xs p-3">
                    <!-- Module Indicator -->
                    <div class="flex items-center justify-between pb-3 mb-2 border-b border-slate-100 dark:border-slate-800 px-2">
                        <div v-if="isSidebarOpen" class="min-w-0">
                            <h3 class="text-xs font-bold uppercase tracking-wider text-slate-400 dark:text-slate-500">
                                Navigasi Modul
                            </h3>
                            <div class="font-semibold text-slate-800 dark:text-slate-200 text-sm truncate mt-0.5">
                                {{ resolvedName }}
                            </div>
                        </div>
                        <button
                            @click="isSidebarOpen = !isSidebarOpen"
                            class="p-1 rounded-md text-slate-400 hover:text-slate-600 dark:hover:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800 text-xs"
                            :title="isSidebarOpen ? 'Persempit Sidebar' : 'Perluas Sidebar'"
                        >
                            <Menu class="w-4 h-4" />
                        </button>
                    </div>

                    <!-- Search Filter (visible when sidebar is open and has multiple items) -->
                    <div v-if="isSidebarOpen && resolvedMenus.length > 6" class="px-1 mb-2">
                        <div class="relative">
                            <Search class="w-3.5 h-3.5 absolute left-2.5 top-2.5 text-slate-400" />
                            <input
                                v-model="menuFilterQuery"
                                type="text"
                                placeholder="Cari menu & fitur..."
                                class="w-full pl-8 pr-2 py-1.5 text-[11px] rounded-xl bg-slate-50 dark:bg-slate-800/80 border border-slate-200 dark:border-slate-700 focus:outline-none focus:ring-1 focus:ring-emerald-500 text-slate-700 dark:text-slate-200 placeholder-slate-400"
                            />
                        </div>
                    </div>

                    <!-- Dynamic Menus (Categorized Accordion) -->
                    <div class="max-h-[calc(100vh-17rem)] overflow-y-auto space-y-3 pr-1 custom-scrollbar">
                        <div v-for="group in groupedMenus" :key="group.category" class="space-y-1">
                            <!-- Category Header (when sidebar open) -->
                            <button
                                v-if="isSidebarOpen && groupedMenus.length > 1"
                                type="button"
                                @click="toggleCategory(group.category)"
                                class="w-full flex items-center justify-between px-2 py-1 text-[10px] font-bold uppercase tracking-wider text-slate-400 dark:text-slate-500 hover:text-slate-700 dark:hover:text-slate-300 transition"
                            >
                                <span class="truncate">{{ group.category }}</span>
                                <div class="flex items-center gap-1">
                                    <span class="text-[9px] px-1 py-0.2 rounded-md bg-slate-100 dark:bg-slate-800 text-slate-400 font-medium">
                                        {{ group.items.length }}
                                    </span>
                                    <ChevronDown
                                        :class="['w-3 h-3 transition-transform duration-150', collapsedCategories[group.category] ? '-rotate-90' : '']"
                                    />
                                </div>
                            </button>

                            <!-- Items in Category -->
                            <div v-show="!isSidebarOpen || !collapsedCategories[group.category]" class="space-y-0.5">
                                <Link
                                    v-for="(menu, idx) in group.items"
                                    :key="idx"
                                    :href="menu.route"
                                    :class="[
                                        'flex items-center gap-2.5 px-2.5 py-1.5 rounded-xl text-xs transition duration-150 group',
                                        isMenuActive(menu.route)
                                            ? themeColorStyles.activeMenu
                                            : 'text-slate-600 dark:text-slate-400 hover:bg-slate-50 dark:hover:bg-slate-800 hover:text-slate-900 dark:hover:text-white',
                                    ]"
                                    :title="!isSidebarOpen ? (menu.name + (menu.description ? ' - ' + menu.description : '')) : (menu.description || '')"
                                >
                                    <component
                                        :is="getMenuIcon(menu.icon)"
                                        :class="['w-4 h-4 shrink-0 transition-colors', isMenuActive(menu.route) ? themeColorStyles.iconColor : 'text-slate-400 dark:text-slate-500 group-hover:text-slate-700 dark:group-hover:text-slate-300']"
                                    />
                                    <span v-if="isSidebarOpen" class="font-medium truncate text-xs">
                                        {{ menu.name }}
                                    </span>
                                </Link>
                            </div>
                        </div>
                    </div>

                    <!-- Back to App Launcher Link -->
                    <div class="mt-4 pt-3 border-t border-slate-100 dark:border-slate-800 space-y-1">
                        <Link
                            href="/"
                            class="flex items-center gap-2.5 px-3 py-2 rounded-xl text-xs font-medium text-slate-500 dark:text-slate-400 hover:text-emerald-700 dark:hover:text-emerald-400 hover:bg-slate-50 dark:hover:bg-slate-800 transition"
                        >
                            <LayoutGrid class="w-4 h-4 shrink-0" />
                            <span v-if="isSidebarOpen">Semua Modul (Launcher)</span>
                        </Link>
                        <Link
                            href="/settings"
                            class="flex items-center gap-2.5 px-3 py-2 rounded-xl text-xs font-medium text-slate-500 dark:text-slate-400 hover:text-emerald-700 dark:hover:text-emerald-400 hover:bg-slate-50 dark:hover:bg-slate-800 transition"
                        >
                            <Settings class="w-4 h-4 shrink-0" />
                            <span v-if="isSidebarOpen">Pengaturan Sistem</span>
                        </Link>
                    </div>
                </div>
            </aside>

            <!-- Contextual Sidebar Drawer (Mobile) -->
            <div
                v-if="isMobileSidebarOpen"
                class="fixed inset-0 z-50 bg-slate-900/60 backdrop-blur-xs flex md:hidden"
                @click.self="isMobileSidebarOpen = false"
            >
                <div class="w-72 bg-white dark:bg-slate-900 h-full p-4 border-r border-slate-200 dark:border-slate-800 shadow-2xl flex flex-col justify-between">
                    <div class="overflow-hidden flex flex-col flex-1">
                        <div class="flex items-center justify-between pb-3 mb-3 border-b border-slate-100 dark:border-slate-800 shrink-0">
                            <span class="font-bold text-slate-900 dark:text-white text-sm">{{ resolvedName }}</span>
                            <button @click="isMobileSidebarOpen = false" class="p-1 text-slate-400">
                                <X class="w-5 h-5" />
                            </button>
                        </div>
                        <div class="overflow-y-auto flex-1 space-y-3 pr-1 custom-scrollbar">
                            <div v-for="group in groupedMenus" :key="group.category" class="space-y-1">
                                <div class="px-2 text-[10px] font-bold uppercase tracking-wider text-slate-400">
                                    {{ group.category }}
                                </div>
                                <Link
                                    v-for="(menu, idx) in group.items"
                                    :key="idx"
                                    :href="menu.route"
                                    @click="isMobileSidebarOpen = false"
                                    :class="[
                                        'flex items-center gap-3 px-3 py-2 rounded-xl text-xs transition',
                                        isMenuActive(menu.route)
                                            ? themeColorStyles.activeMenu
                                            : 'text-slate-600 dark:text-slate-400 hover:bg-slate-50 dark:hover:bg-slate-800',
                                    ]"
                                >
                                    <component :is="getMenuIcon(menu.icon)" class="w-4 h-4 shrink-0" />
                                    <span class="truncate">{{ menu.name }}</span>
                                </Link>
                            </div>
                        </div>
                    </div>

                    <div class="pt-3 border-t border-slate-100 dark:border-slate-800 shrink-0">
                        <Link
                            href="/"
                            class="flex items-center gap-2 text-xs font-semibold text-emerald-600 dark:text-emerald-400"
                        >
                            <LayoutGrid class="w-4 h-4" />
                            <span>Kembali ke App Launcher</span>
                        </Link>
                    </div>
                </div>
            </div>

            <!-- Main Module Content Area -->
            <main class="flex-1 min-w-0">
                <slot />
            </main>
        </div>

        <!-- Global Command Palette Modal (Ctrl + K) -->
        <GlobalCommandPalette
            :show="showPalette"
            :items="$page.props.erp?.modules ? [] : []"
            @close="showPalette = false"
        />

        <!-- App Launcher Modal -->
        <AppLauncherModal :show="showLauncher" @close="showLauncher = false" />

        <!-- Developer Mode Overlay Bar (PRD 3.1) -->
        <DeveloperModeOverlay />
    </div>
</template>
