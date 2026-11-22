<script setup>
import { Head, Link } from '@inertiajs/vue3';
import ModuleLayout from '@/Layouts/ModuleLayout.vue';
import {
    Banknote,
    ArrowLeft,
    Download,
    Building2,
    Calendar,
    User,
    CheckCircle2,
    Shield,
} from 'lucide-vue-next';

const props = defineProps({
    salary: Object,
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
    <Head :title="`Slip Gaji — ${salary.salary_number}`" />

    <ModuleLayout
        module-slug="hris"
        module-name="HRISNOW"
        color-theme="blue"
        :menus="hrisMenus"
    >
        <div class="max-w-4xl mx-auto space-y-6">
            <!-- Header -->
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <Link
                        href="/hris/payroll"
                        class="p-2 rounded-xl bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-slate-500 hover:text-slate-800 transition"
                    >
                        <ArrowLeft class="w-5 h-5" />
                    </Link>
                    <div>
                        <h1 class="text-2xl font-bold text-slate-900 dark:text-white tracking-tight">
                            Slip Gaji Karyawan (Payslip)
                        </h1>
                        <p class="text-sm text-slate-500 dark:text-slate-400 font-mono">
                            {{ salary.salary_number }} • Periode {{ salary.period_month }}/{{ salary.period_year }}
                        </p>
                    </div>
                </div>

                <a
                    :href="`/hris/payroll/${salary.id}/pdf`"
                    target="_blank"
                    class="inline-flex items-center gap-2 px-5 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white font-semibold rounded-xl text-sm transition shadow-sm"
                >
                    <Download class="w-4 h-4" />
                    Unduh Slip Gaji PDF
                </a>
            </div>

            <!-- Payslip Paper Layout -->
            <div class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-200 dark:border-slate-700 p-8 shadow-sm space-y-6">
                <!-- Company Header -->
                <div class="flex items-start justify-between pb-6 border-b border-slate-200 dark:border-slate-700">
                    <div>
                        <h2 class="text-xl font-black text-slate-900 dark:text-white tracking-tight">
                            PT. HERBATECH INNOPHARMA INDUSTRY
                        </h2>
                        <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">
                            Kawasan Industri Herbal Modern, Sukoharjo & Boyolali, Jawa Tengah
                        </p>
                        <p class="text-[11px] text-slate-400 font-mono mt-0.5">
                            Dokumen Resmi Penggajian Terintegrasi Core Shared Kernel ERP
                        </p>
                    </div>
                    <div class="text-right">
                        <div class="inline-block px-3 py-1 bg-blue-50 text-blue-700 font-mono font-bold text-xs rounded-lg">
                            STATUS: PAID / APPROVED
                        </div>
                        <div class="text-[11px] text-slate-400 mt-1 font-mono">
                            Generated: {{ salary.created_at ? salary.created_at.substring(0, 10) : '2026-11-22' }}
                        </div>
                    </div>
                </div>

                <!-- Employee Meta Grid -->
                <div class="grid grid-cols-2 md:grid-cols-4 gap-4 p-4 bg-slate-50 dark:bg-slate-900/60 rounded-xl text-xs">
                    <div>
                        <span class="text-slate-400 block">Nama Karyawan:</span>
                        <span class="font-bold text-slate-900 dark:text-white">{{ salary.employee?.user?.name }}</span>
                    </div>
                    <div>
                        <span class="text-slate-400 block">NIK & Golongan:</span>
                        <span class="font-semibold text-slate-800 dark:text-slate-200 font-mono">
                            {{ salary.employee?.nik }} (Gol. {{ salary.employee?.golongan?.code }})
                        </span>
                    </div>
                    <div>
                        <span class="text-slate-400 block">Departemen & Jabatan:</span>
                        <span class="font-semibold text-slate-800 dark:text-slate-200">
                            {{ salary.employee?.department?.name }} - {{ salary.employee?.position }}
                        </span>
                    </div>
                    <div>
                        <span class="text-slate-400 block">Status PTKP & Bank:</span>
                        <span class="font-semibold text-slate-800 dark:text-slate-200 font-mono">
                            {{ salary.employee?.tax_ptkp_status }} • {{ salary.employee?.bank_name }}
                        </span>
                    </div>
                </div>

                <!-- Earnings and Deductions Table -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Column 1: Penghasilan (Earnings) -->
                    <div class="space-y-3">
                        <div class="font-bold text-xs uppercase tracking-wider text-emerald-700 dark:text-emerald-400 pb-2 border-b border-emerald-100 dark:border-emerald-900/40">
                            A. Komponen Penghasilan (Earnings)
                        </div>

                        <div class="space-y-2 text-xs">
                            <div class="flex justify-between py-1 border-b border-slate-100 dark:border-slate-700/50">
                                <span class="text-slate-600 dark:text-slate-400">Gaji Pokok:</span>
                                <span class="font-mono font-medium text-slate-900 dark:text-white">{{ formatCurrency(salary.basic_salary) }}</span>
                            </div>
                            <div class="flex justify-between py-1 border-b border-slate-100 dark:border-slate-700/50">
                                <span class="text-slate-600 dark:text-slate-400">Tunjangan Tetap:</span>
                                <span class="font-mono font-medium text-slate-900 dark:text-white">{{ formatCurrency(salary.allowance_fixed) }}</span>
                            </div>
                            <div class="flex justify-between py-1 border-b border-slate-100 dark:border-slate-700/50">
                                <span class="text-slate-600 dark:text-slate-400">Tunjangan Kehadiran:</span>
                                <span class="font-mono font-medium text-slate-900 dark:text-white">{{ formatCurrency(salary.allowance_attendance) }}</span>
                            </div>
                            <div class="flex justify-between py-1 border-b border-slate-100 dark:border-slate-700/50">
                                <span class="text-slate-600 dark:text-slate-400">Upah Lembur Resmi (SPL):</span>
                                <span class="font-mono font-medium text-amber-600">{{ formatCurrency(salary.overtime_pay) }}</span>
                            </div>
                            <div class="flex justify-between py-2 font-bold text-slate-900 dark:text-white bg-slate-50 dark:bg-slate-900 px-3 rounded-lg">
                                <span>Total Penghasilan Bruto:</span>
                                <span class="font-mono text-emerald-600">{{ formatCurrency(salary.gross_salary) }}</span>
                            </div>
                        </div>
                    </div>

                    <!-- Column 2: Potongan (Deductions) -->
                    <div class="space-y-3">
                        <div class="font-bold text-xs uppercase tracking-wider text-rose-700 dark:text-rose-400 pb-2 border-b border-rose-100 dark:border-rose-900/40">
                            B. Komponen Potongan (Deductions)
                        </div>

                        <div class="space-y-2 text-xs">
                            <div class="flex justify-between py-1 border-b border-slate-100 dark:border-slate-700/50">
                                <span class="text-slate-600 dark:text-slate-400">BPJS Ketenagakerjaan (3% JHT+JP):</span>
                                <span class="font-mono font-medium text-rose-600">-{{ formatCurrency(salary.bpjs_tk_employee) }}</span>
                            </div>
                            <div class="flex justify-between py-1 border-b border-slate-100 dark:border-slate-700/50">
                                <span class="text-slate-600 dark:text-slate-400">BPJS Kesehatan (1%):</span>
                                <span class="font-mono font-medium text-rose-600">-{{ formatCurrency(salary.bpjs_kes_employee) }}</span>
                            </div>
                            <div class="flex justify-between py-1 border-b border-slate-100 dark:border-slate-700/50">
                                <span class="text-slate-600 dark:text-slate-400">Pajak PPh 21 TER (Tarif Efektif):</span>
                                <span class="font-mono font-medium text-rose-600">-{{ formatCurrency(salary.pph21_amount) }}</span>
                            </div>
                            <div class="flex justify-between py-1 border-b border-slate-100 dark:border-slate-700/50">
                                <span class="text-slate-600 dark:text-slate-400">Denda Keterlambatan Presensi:</span>
                                <span class="font-mono font-medium text-rose-600">-{{ formatCurrency(salary.deduction_late) }}</span>
                            </div>
                            <div class="flex justify-between py-2 font-bold text-slate-900 dark:text-white bg-slate-50 dark:bg-slate-900 px-3 rounded-lg">
                                <span>Total Potongan:</span>
                                <span class="font-mono text-rose-600">-{{ formatCurrency(salary.total_deductions) }}</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Net Take Home Pay Highlight -->
                <div class="p-6 bg-gradient-to-r from-emerald-500/10 via-teal-500/10 to-blue-500/10 rounded-2xl border border-emerald-500/20 flex items-center justify-between">
                    <div>
                        <div class="text-xs uppercase font-bold tracking-wider text-slate-500">
                            Gaji Bersih Diterima (Take Home Pay)
                        </div>
                        <div class="text-xs text-slate-400 mt-0.5">
                            Ditransfer ke Rekening {{ salary.employee?.bank_name }} No. {{ salary.employee?.bank_account_no || 'xxxx-xxxx' }}
                        </div>
                    </div>
                    <div class="text-2xl font-black font-mono text-emerald-600">
                        {{ formatCurrency(salary.net_salary) }}
                    </div>
                </div>
            </div>
        </div>
    </ModuleLayout>
</template>
