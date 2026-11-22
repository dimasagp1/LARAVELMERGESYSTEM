<script setup>
import { ref } from 'vue';
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import ModuleLayout from '@/Layouts/ModuleLayout.vue';
import {
    Banknote,
    Calculator,
    Download,
    Eye,
    Calendar,
    Search,
    Filter,
    CheckCircle2,
    Building2,
    DollarSign,
    Shield,
} from 'lucide-vue-next';

const props = defineProps({
    salaries: Object,
    periodMonth: Number,
    periodYear: Number,
    filters: Object,
    summary: Object,
    departments: Array,
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

const selectedMonth = ref(props.periodMonth);
const selectedYear = ref(props.periodYear);
const filterDept = ref(props.filters.department_id || '');

const applyFilters = () => {
    router.get('/hris/payroll', {
        period_month: selectedMonth.value,
        period_year: selectedYear.value,
        department_id: filterDept.value || undefined,
    }, {
        preserveState: true,
        preserveScroll: true,
    });
};

const calcForm = useForm({
    period_month: props.periodMonth,
    period_year: props.periodYear,
});

const runPayrollCalculation = () => {
    calcForm.period_month = selectedMonth.value;
    calcForm.period_year = selectedYear.value;
    calcForm.post('/hris/payroll/calculate', {
        preserveScroll: true,
    });
};

const formatCurrency = (val) => {
    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        maximumFractionDigits: 0,
    }).format(val || 0);
};
</script>

<template>
    <Head title="Payroll & Pajak PPh 21 TER — HRISNOW" />

    <ModuleLayout
        module-slug="hris"
        module-name="HRISNOW"
        color-theme="blue"
        :menus="hrisMenus"
    >
        <div class="space-y-6">
            <!-- Header -->
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 bg-white dark:bg-slate-800 p-6 rounded-2xl shadow-xs border border-slate-200 dark:border-slate-700">
                <div class="flex items-center gap-3">
                    <div class="p-3 bg-indigo-500/10 text-indigo-600 dark:text-indigo-400 rounded-xl">
                        <Banknote class="w-8 h-8" />
                    </div>
                    <div>
                        <h1 class="text-2xl font-bold text-slate-900 dark:text-white tracking-tight">
                            Penggajian (Payroll) & Pajak PPh 21 TER
                        </h1>
                        <p class="text-sm text-slate-500 dark:text-slate-400 mt-0.5">
                            Kalkulasi otomatis BPJS TK (3%), BPJS Kesehatan (1%), PPh 21 Tarif Efektif Rata-rata (TER), dan denda keterlambatan.
                        </p>
                    </div>
                </div>

                <!-- Run Calculation Button -->
                <button
                    @click="runPayrollCalculation"
                    :disabled="calcForm.processing"
                    class="inline-flex items-center gap-2 px-5 py-2.5 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-xl text-sm transition shadow-sm disabled:opacity-50"
                >
                    <Calculator class="w-4 h-4" />
                    Hitung Payroll Bulan Ini (Batch)
                </button>
            </div>

            <!-- Summary KPI Cards -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                <div class="bg-white dark:bg-slate-800 p-4 rounded-xl border border-slate-200 dark:border-slate-700 shadow-xs">
                    <div class="text-xs text-slate-500 mb-1">Total Penghasilan Bruto</div>
                    <div class="text-lg font-bold font-mono text-slate-900 dark:text-white">
                        {{ formatCurrency(summary.total_gross) }}
                    </div>
                    <div class="text-[11px] text-slate-400 mt-0.5">Gaji + Tunjangan + Lembur</div>
                </div>

                <div class="bg-white dark:bg-slate-800 p-4 rounded-xl border border-slate-200 dark:border-slate-700 shadow-xs">
                    <div class="text-xs text-slate-500 mb-1">Total Potongan Karyawan</div>
                    <div class="text-lg font-bold font-mono text-rose-600">
                        {{ formatCurrency(summary.total_deductions) }}
                    </div>
                    <div class="text-[11px] text-slate-400 mt-0.5">BPJS, PPh 21 TER & Terlambat</div>
                </div>

                <div class="bg-white dark:bg-slate-800 p-4 rounded-xl border border-slate-200 dark:border-slate-700 shadow-xs">
                    <div class="text-xs text-slate-500 mb-1">Total Netto (Take Home Pay)</div>
                    <div class="text-lg font-bold font-mono text-emerald-600">
                        {{ formatCurrency(summary.total_net) }}
                    </div>
                    <div class="text-[11px] text-slate-400 mt-0.5">Total Transfer Rekening</div>
                </div>

                <div class="bg-white dark:bg-slate-800 p-4 rounded-xl border border-slate-200 dark:border-slate-700 shadow-xs">
                    <div class="text-xs text-slate-500 mb-1">Karyawan Diproses</div>
                    <div class="text-lg font-bold text-blue-600">
                        {{ summary.processed_count }} Orang
                    </div>
                    <div class="text-[11px] text-slate-400 mt-0.5">Periode {{ periodMonth }}/{{ periodYear }}</div>
                </div>
            </div>

            <!-- Filters Bar -->
            <div class="bg-white dark:bg-slate-800 p-4 rounded-2xl border border-slate-200 dark:border-slate-700 shadow-xs flex flex-wrap items-center justify-between gap-4">
                <div class="flex items-center gap-3">
                    <div class="flex items-center gap-2">
                        <Calendar class="w-4 h-4 text-slate-400" />
                        <span class="text-xs font-semibold text-slate-600 dark:text-slate-300">Periode Bulan:</span>
                    </div>
                    <select
                        v-model="selectedMonth"
                        @change="applyFilters"
                        class="px-3 py-1.5 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl text-xs text-slate-900 dark:text-white outline-none"
                    >
                        <option v-for="m in 12" :key="m" :value="m">
                            Bulan {{ m }}
                        </option>
                    </select>

                    <select
                        v-model="selectedYear"
                        @change="applyFilters"
                        class="px-3 py-1.5 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl text-xs text-slate-900 dark:text-white outline-none"
                    >
                        <option :value="2025">2025</option>
                        <option :value="2026">2026</option>
                        <option :value="2027">2027</option>
                    </select>
                </div>

                <div class="flex items-center gap-3">
                    <select
                        v-model="filterDept"
                        @change="applyFilters"
                        class="px-3 py-1.5 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl text-xs text-slate-700 dark:text-slate-300 outline-none"
                    >
                        <option value="">Semua Departemen</option>
                        <option v-for="dept in departments" :key="dept.id" :value="dept.id">
                            {{ dept.name }}
                        </option>
                    </select>
                </div>
            </div>

            <!-- Table of Salaries -->
            <div class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-200 dark:border-slate-700 shadow-xs overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-xs text-left text-slate-600 dark:text-slate-300">
                        <thead class="text-[11px] uppercase bg-slate-50 dark:bg-slate-900/50 text-slate-400 border-b border-slate-200 dark:border-slate-700">
                            <tr>
                                <th class="px-4 py-3">No. Slip Gaji</th>
                                <th class="px-4 py-3">Karyawan</th>
                                <th class="px-4 py-3">Departemen</th>
                                <th class="px-4 py-3">Gaji Pokok</th>
                                <th class="px-4 py-3">Lembur</th>
                                <th class="px-4 py-3">Potongan BPJS & PPh</th>
                                <th class="px-4 py-3">Gaji Bersih (Netto)</th>
                                <th class="px-4 py-3">Status</th>
                                <th class="px-4 py-3 text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 dark:divide-slate-700">
                            <tr
                                v-for="sal in salaries.data"
                                :key="sal.id"
                                class="hover:bg-slate-50/80 dark:hover:bg-slate-900/40 transition"
                            >
                                <td class="px-4 py-3.5 font-mono font-semibold text-blue-600">
                                    {{ sal.salary_number }}
                                </td>
                                <td class="px-4 py-3.5">
                                    <div class="font-semibold text-slate-900 dark:text-white">
                                        {{ sal.employee?.user?.name }}
                                    </div>
                                    <div class="text-[10px] text-slate-400 font-mono">
                                        {{ sal.employee?.nik }} • PTKP: {{ sal.employee?.tax_ptkp_status }}
                                    </div>
                                </td>
                                <td class="px-4 py-3.5">
                                    <div class="font-medium text-slate-800 dark:text-slate-200">
                                        {{ sal.employee?.department?.name }}
                                    </div>
                                    <div class="text-[10px] text-slate-400">
                                        Gol. {{ sal.employee?.golongan?.code }}
                                    </div>
                                </td>
                                <td class="px-4 py-3.5 font-mono">
                                    {{ formatCurrency(sal.basic_salary) }}
                                </td>
                                <td class="px-4 py-3.5 font-mono text-amber-600 font-medium">
                                    {{ formatCurrency(sal.overtime_pay) }}
                                </td>
                                <td class="px-4 py-3.5 font-mono text-rose-600 font-medium">
                                    -{{ formatCurrency(sal.total_deductions) }}
                                    <span class="block text-[10px] text-slate-400">
                                        PPh TER: {{ formatCurrency(sal.pph21_amount) }}
                                    </span>
                                </td>
                                <td class="px-4 py-3.5 font-mono font-bold text-emerald-600 text-sm">
                                    {{ formatCurrency(sal.net_salary) }}
                                </td>
                                <td class="px-4 py-3.5">
                                    <span class="px-2 py-0.5 rounded text-[10px] font-semibold bg-emerald-50 text-emerald-700 uppercase">
                                        {{ sal.status }}
                                    </span>
                                </td>
                                <td class="px-4 py-3.5 text-right">
                                    <div class="flex items-center justify-end gap-1.5">
                                        <Link
                                            :href="`/hris/payroll/${sal.id}`"
                                            class="p-1.5 rounded-lg text-slate-500 hover:text-blue-600 hover:bg-slate-100 transition"
                                            title="Lihat Rincian Slip Gaji"
                                        >
                                            <Eye class="w-4 h-4" />
                                        </Link>
                                        <a
                                            :href="`/hris/payroll/${sal.id}/pdf`"
                                            target="_blank"
                                            class="p-1.5 rounded-lg text-slate-500 hover:text-emerald-600 hover:bg-slate-100 transition"
                                            title="Unduh Slip Gaji PDF"
                                        >
                                            <Download class="w-4 h-4" />
                                        </a>
                                    </div>
                                </td>
                            </tr>
                            <tr v-if="salaries.data.length === 0">
                                <td colspan="9" class="text-center py-10 text-slate-400">
                                    Belum ada data slip gaji untuk periode {{ periodMonth }}/{{ periodYear }}. Silakan klik tombol "Hitung Payroll Bulan Ini (Batch)".
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </ModuleLayout>
</template>
