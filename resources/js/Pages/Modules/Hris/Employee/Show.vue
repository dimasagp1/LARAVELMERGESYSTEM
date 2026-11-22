<script setup>
import { Head, Link } from '@inertiajs/vue3';
import ModuleLayout from '@/Layouts/ModuleLayout.vue';
import {
    Users,
    ArrowLeft,
    Edit,
    Building2,
    Calendar,
    Briefcase,
    Banknote,
    Clock,
    FileText,
    CalendarCheck,
    CalendarOff,
    Timer,
    MapPin,
    Download,
    Award,
} from 'lucide-vue-next';

const props = defineProps({
    employee: Object,
});

const hrisMenus = [
    { name: 'Overview', route: '/hris', icon: 'LayoutDashboard' },
    { name: 'Master Karyawan', route: '/hris/employees', icon: 'Users' },
    { name: 'Presensi & Geofence', route: '/hris/attendance', icon: 'CalendarCheck' },
    { name: 'Pengajuan Cuti', route: '/hris/leaves', icon: 'CalendarOff' },
    { name: 'Lembur (SPL) & Time Bank', route: '/hris/overtime', icon: 'Timer' },
    { name: 'Kaizen Kizuki & Point', route: '/hris/kizuki', icon: 'Lightbulb' },
    { name: 'Payroll PPh 21 TER', route: '/hris/payroll', icon: 'Banknote' },
    { name: 'Strategic BSC L3 & L4', route: '/hris/strategic', icon: 'Target' },
];

const formatCurrency = (val) => {
    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        maximumFractionDigits: 0,
    }).format(val || 0);
};
</script>

<template>
    <Head :title="`${employee.user?.name || 'Karyawan'} — Profil Lengkap`" />

    <ModuleLayout
        module-slug="hris"
        module-name="HRISNOW"
        color-theme="blue"
        :menus="hrisMenus"
    >
        <div class="max-w-6xl mx-auto space-y-6">
            <!-- Header Card -->
            <div class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-200 dark:border-slate-700 p-6 shadow-xs flex flex-col md:flex-row md:items-center justify-between gap-6">
                <div class="flex items-start gap-4">
                    <Link
                        href="/hris/employees"
                        class="p-2 rounded-xl border border-slate-200 dark:border-slate-700 text-slate-500 hover:text-slate-800 transition"
                    >
                        <ArrowLeft class="w-5 h-5" />
                    </Link>

                    <div class="w-16 h-16 rounded-2xl bg-blue-100 dark:bg-blue-900/40 text-blue-600 dark:text-blue-400 flex items-center justify-center font-bold text-2xl shrink-0">
                        {{ employee.user?.name ? employee.user.name.charAt(0) : 'E' }}
                    </div>

                    <div>
                        <div class="flex items-center gap-3">
                            <h1 class="text-2xl font-bold text-slate-900 dark:text-white">
                                {{ employee.user?.name }}
                            </h1>
                            <span class="px-2.5 py-0.5 rounded-full text-xs font-semibold bg-emerald-50 text-emerald-700 dark:bg-emerald-950 dark:text-emerald-300">
                                {{ employee.status.toUpperCase() }}
                            </span>
                        </div>
                        <div class="text-sm text-slate-500 dark:text-slate-400 font-mono mt-1">
                            NIK: {{ employee.nik }} • {{ employee.user?.email }}
                        </div>
                        <div class="text-xs text-blue-600 dark:text-blue-400 font-medium mt-1">
                            {{ employee.position }} — {{ employee.department?.name }} ({{ employee.plant?.name || 'Sukoharjo Plant' }})
                        </div>
                    </div>
                </div>

                <div class="flex items-center gap-3">
                    <Link
                        :href="`/hris/employees/${employee.id}/edit`"
                        class="inline-flex items-center gap-2 px-4 py-2 bg-amber-500 hover:bg-amber-600 text-white font-medium rounded-xl text-sm transition shadow-sm"
                    >
                        <Edit class="w-4 h-4" />
                        Edit Data Karyawan
                    </Link>
                </div>
            </div>

            <!-- Stats Overview 4 Cards -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                <div class="bg-white dark:bg-slate-800 p-4 rounded-xl border border-slate-200 dark:border-slate-700 shadow-xs">
                    <div class="text-xs text-slate-500 mb-1">Struktur Golongan</div>
                    <div class="text-lg font-bold text-slate-900 dark:text-white">
                        Golongan {{ employee.golongan?.code }}
                    </div>
                    <div class="text-xs text-slate-400 mt-0.5">{{ employee.golongan?.name }} (Level {{ employee.golongan?.level }})</div>
                </div>

                <div class="bg-white dark:bg-slate-800 p-4 rounded-xl border border-slate-200 dark:border-slate-700 shadow-xs">
                    <div class="text-xs text-slate-500 mb-1">Gaji Pokok & Tunjangan</div>
                    <div class="text-lg font-bold font-mono text-slate-900 dark:text-white">
                        {{ formatCurrency(employee.basic_salary) }}
                    </div>
                    <div class="text-xs text-slate-400 mt-0.5">PTKP: {{ employee.tax_ptkp_status }}</div>
                </div>

                <div class="bg-white dark:bg-slate-800 p-4 rounded-xl border border-slate-200 dark:border-slate-700 shadow-xs">
                    <div class="text-xs text-slate-500 mb-1">Saldo Cuti Tahunan</div>
                    <div class="text-lg font-bold text-teal-600">
                        {{ employee.leave_balance }} Hari
                    </div>
                    <div class="text-xs text-slate-400 mt-0.5">Tersedia untuk diambil</div>
                </div>

                <div class="bg-white dark:bg-slate-800 p-4 rounded-xl border border-slate-200 dark:border-slate-700 shadow-xs">
                    <div class="text-xs text-slate-500 mb-1">Saldo Time Bank Lembur</div>
                    <div class="text-lg font-bold text-amber-600 font-mono">
                        {{ employee.time_bank?.balance_hours || 0 }} Jam
                    </div>
                    <div class="text-xs text-slate-400 mt-0.5">Kompensasi Jam Lembur</div>
                </div>
            </div>

            <!-- Details Grid: Info Details & Recent History -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- Left: Detail Kepegawaian & Rekening -->
                <div class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-200 dark:border-slate-700 p-5 shadow-xs space-y-4">
                    <h3 class="font-bold text-sm text-slate-900 dark:text-white pb-2 border-b border-slate-100 dark:border-slate-700">
                        Detail Kepegawaian & Administrasi
                    </h3>

                    <div class="space-y-3 text-xs">
                        <div>
                            <span class="text-slate-400 block">Status Hubungan Kerja:</span>
                            <span class="font-semibold text-slate-800 dark:text-slate-200 uppercase">
                                {{ employee.employment_status }}
                            </span>
                        </div>

                        <div>
                            <span class="text-slate-400 block">Tanggal Bergabung:</span>
                            <span class="font-semibold text-slate-800 dark:text-slate-200">
                                {{ employee.join_date }}
                            </span>
                        </div>

                        <div v-if="employee.end_date">
                            <span class="text-slate-400 block">Akhir Kontrak:</span>
                            <span class="font-semibold text-slate-800 dark:text-slate-200">
                                {{ employee.end_date }}
                            </span>
                        </div>

                        <div>
                            <span class="text-slate-400 block">Shift Roster:</span>
                            <span class="font-semibold text-slate-800 dark:text-slate-200">
                                {{ employee.shift?.name || 'Shift Standar Pagi' }}
                            </span>
                        </div>

                        <div>
                            <span class="text-slate-400 block">Lokasi Kantor Geofence:</span>
                            <span class="font-semibold text-slate-800 dark:text-slate-200">
                                {{ employee.office_location?.name || 'Sukoharjo Plant' }}
                            </span>
                        </div>

                        <div>
                            <span class="text-slate-400 block">Rekening Payroll:</span>
                            <span class="font-semibold text-slate-800 dark:text-slate-200 font-mono">
                                {{ employee.bank_name || 'BCA' }} - {{ employee.bank_account_no || '-' }}
                            </span>
                        </div>

                        <div>
                            <span class="text-slate-400 block">Kontak Darurat:</span>
                            <span class="font-semibold text-slate-800 dark:text-slate-200">
                                {{ employee.emergency_contact_name || '-' }} ({{ employee.emergency_contact_phone || '-' }})
                            </span>
                        </div>
                    </div>
                </div>

                <!-- Right 2 Columns: Riwayat Presensi & Payroll Slip -->
                <div class="lg:col-span-2 space-y-6">
                    <!-- Riwayat Presensi Terakhir -->
                    <div class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-200 dark:border-slate-700 p-5 shadow-xs">
                        <h3 class="font-bold text-sm text-slate-900 dark:text-white pb-3 mb-3 border-b border-slate-100 dark:border-slate-700 flex items-center justify-between">
                            <span class="flex items-center gap-2">
                                <CalendarCheck class="w-4 h-4 text-emerald-600" />
                                10 Catatan Presensi Terakhir
                            </span>
                            <Link href="/hris/attendance" class="text-xs text-blue-600 hover:underline">
                                Lihat Semua
                            </Link>
                        </h3>

                        <div class="overflow-x-auto">
                            <table class="w-full text-xs text-left">
                                <thead class="text-slate-400 bg-slate-50 dark:bg-slate-900/50">
                                    <tr>
                                        <th class="px-3 py-2">Tanggal</th>
                                        <th class="px-3 py-2">Check In</th>
                                        <th class="px-3 py-2">Check Out</th>
                                        <th class="px-3 py-2">Jam Kerja</th>
                                        <th class="px-3 py-2">Status</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-100 dark:divide-slate-700">
                                    <tr v-for="att in employee.attendances" :key="att.id">
                                        <td class="px-3 py-2 font-medium">{{ att.date }}</td>
                                        <td class="px-3 py-2 font-mono text-emerald-600">
                                            {{ att.check_in_time ? att.check_in_time.substring(11, 16) : '-' }}
                                            <span v-if="att.is_late" class="ml-1 text-[10px] text-amber-600">(+{{ att.late_minutes }}m)</span>
                                        </td>
                                        <td class="px-3 py-2 font-mono text-blue-600">
                                            {{ att.check_out_time ? att.check_out_time.substring(11, 16) : '-' }}
                                        </td>
                                        <td class="px-3 py-2 font-mono">{{ att.work_hours || 0 }} jam</td>
                                        <td class="px-3 py-2">
                                            <span class="px-2 py-0.5 rounded text-[10px] font-semibold bg-emerald-50 text-emerald-700">
                                                {{ att.status.toUpperCase() }}
                                            </span>
                                        </td>
                                    </tr>
                                    <tr v-if="!employee.attendances || employee.attendances.length === 0">
                                        <td colspan="5" class="text-center py-4 text-slate-400">Belum ada riwayat presensi.</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Riwayat Slip Gaji Terakhir -->
                    <div class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-200 dark:border-slate-700 p-5 shadow-xs">
                        <h3 class="font-bold text-sm text-slate-900 dark:text-white pb-3 mb-3 border-b border-slate-100 dark:border-slate-700 flex items-center justify-between">
                            <span class="flex items-center gap-2">
                                <Banknote class="w-4 h-4 text-blue-600" />
                                Riwayat Slip Gaji (Payroll)
                            </span>
                            <Link href="/hris/payroll" class="text-xs text-blue-600 hover:underline">
                                Buka Modul Payroll
                            </Link>
                        </h3>

                        <div class="divide-y divide-slate-100 dark:divide-slate-700">
                            <div
                                v-for="sal in employee.salaries"
                                :key="sal.id"
                                class="py-3 flex items-center justify-between"
                            >
                                <div>
                                    <div class="font-semibold text-sm text-slate-900 dark:text-white">
                                        Periode {{ sal.period_month }}/{{ sal.period_year }} ({{ sal.salary_number }})
                                    </div>
                                    <div class="text-xs text-slate-400 mt-0.5">
                                        Bruto: {{ formatCurrency(sal.gross_salary) }} • Potongan: {{ formatCurrency(sal.total_deductions) }}
                                    </div>
                                </div>
                                <div class="flex items-center gap-3">
                                    <span class="font-mono font-bold text-sm text-emerald-600">
                                        {{ formatCurrency(sal.net_salary) }}
                                    </span>
                                    <a
                                        :href="`/hris/payroll/${sal.id}/pdf`"
                                        target="_blank"
                                        class="p-1.5 rounded-lg text-slate-500 hover:text-blue-600 hover:bg-slate-100 transition"
                                        title="Unduh Slip Gaji PDF"
                                    >
                                        <Download class="w-4 h-4" />
                                    </a>
                                </div>
                            </div>
                            <div v-if="!employee.salaries || employee.salaries.length === 0" class="text-center py-4 text-xs text-slate-400">
                                Belum ada riwayat penggajian yang diterbitkan.
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </ModuleLayout>
</template>
