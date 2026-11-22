<script setup>
import { ref } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import ModuleLayout from '@/Layouts/ModuleLayout.vue';
import {
    Timer,
    Plus,
    Search,
    Filter,
    CheckCircle2,
    XCircle,
    Download,
    Eye,
    Clock,
    Banknote,
    Layers,
} from 'lucide-vue-next';

const props = defineProps({
    overtimes: Object,
    filters: Object,
    summary: Object,
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

const search = ref(props.filters.search || '');
const status = ref(props.filters.status || '');
const compensationType = ref(props.filters.compensation_type || '');

const applyFilters = () => {
    router.get('/hris/overtime', {
        search: search.value || undefined,
        status: status.value || undefined,
        compensation_type: compensationType.value || undefined,
    }, {
        preserveState: true,
        preserveScroll: true,
    });
};

const handleApproval = (overtimeId, action) => {
    router.post(`/hris/overtime/${overtimeId}/approve`, {
        action: action,
    }, {
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
    <Head title="Lembur (SPL) & Time Bank — HRISNOW" />

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
                    <div class="p-3 bg-amber-500/10 text-amber-600 dark:text-amber-400 rounded-xl">
                        <Timer class="w-8 h-8" />
                    </div>
                    <div>
                        <h1 class="text-2xl font-bold text-slate-900 dark:text-white tracking-tight">
                            Surat Perintah Lembur (SPL) & Time Bank
                        </h1>
                        <p class="text-sm text-slate-500 dark:text-slate-400 mt-0.5">
                            Kalkulasi Depnaker RI otomatis (1.5x / 2.0x / 3.0x / 4.0x) dan opsi konversi Time Bank.
                        </p>
                    </div>
                </div>

                <div class="flex items-center gap-3">
                    <div class="flex items-center gap-2 text-xs">
                        <span class="px-3 py-1.5 rounded-xl bg-amber-50 text-amber-700 border border-amber-200 font-semibold">
                            Pending: {{ summary.pending }}
                        </span>
                        <span class="px-3 py-1.5 rounded-xl bg-emerald-50 text-emerald-700 border border-emerald-200 font-semibold">
                            Disetujui: {{ summary.total_hours }} Jam
                        </span>
                    </div>

                    <Link
                        href="/hris/overtime/create"
                        class="inline-flex items-center gap-2 px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-xl text-sm transition shadow-sm"
                    >
                        <Plus class="w-4 h-4" />
                        Buat SPL Baru
                    </Link>
                </div>
            </div>

            <!-- Filters Bar -->
            <div class="bg-white dark:bg-slate-800 p-4 rounded-2xl border border-slate-200 dark:border-slate-700 shadow-xs flex flex-col md:flex-row items-center gap-4">
                <div class="relative flex-1 w-full">
                    <Search class="w-4 h-4 absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400" />
                    <input
                        v-model="search"
                        @keyup.enter="applyFilters"
                        type="text"
                        placeholder="Cari nomor SPL, nama karyawan, atau target kerja..."
                        class="w-full pl-10 pr-4 py-2 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl text-sm focus:ring-2 focus:ring-blue-500 outline-none text-slate-900 dark:text-white"
                    />
                </div>

                <div class="flex items-center gap-3 w-full md:w-auto">
                    <select
                        v-model="compensationType"
                        @change="applyFilters"
                        class="px-3 py-2 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl text-xs text-slate-700 dark:text-slate-300 outline-none"
                    >
                        <option value="">Semua Kompensasi</option>
                        <option value="cash">Uang Lembur (Payroll Cash)</option>
                        <option value="time_bank">Time Bank (Deposit Jam)</option>
                    </select>

                    <select
                        v-model="status"
                        @change="applyFilters"
                        class="px-3 py-2 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl text-xs text-slate-700 dark:text-slate-300 outline-none"
                    >
                        <option value="">Semua Status</option>
                        <option value="pending_spv">Pending Supervisor</option>
                        <option value="pending_om">Pending Operations Mgr</option>
                        <option value="pending_gm">Pending GM</option>
                        <option value="approved">Disetujui</option>
                        <option value="rejected">Ditolak</option>
                    </select>
                </div>
            </div>

            <!-- Table -->
            <div class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-200 dark:border-slate-700 shadow-xs overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-xs text-left text-slate-600 dark:text-slate-300">
                        <thead class="text-[11px] uppercase bg-slate-50 dark:bg-slate-900/50 text-slate-400 border-b border-slate-200 dark:border-slate-700">
                            <tr>
                                <th class="px-4 py-3">No. SPL</th>
                                <th class="px-4 py-3">Karyawan</th>
                                <th class="px-4 py-3">Tanggal & Jam</th>
                                <th class="px-4 py-3">Jam Fisik</th>
                                <th class="px-4 py-3">Jam Pengali Depnaker</th>
                                <th class="px-4 py-3">Kompensasi</th>
                                <th class="px-4 py-3">Target Pekerjaan</th>
                                <th class="px-4 py-3">Status</th>
                                <th class="px-4 py-3 text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 dark:divide-slate-700">
                            <tr
                                v-for="ot in overtimes.data"
                                :key="ot.id"
                                class="hover:bg-slate-50/80 dark:hover:bg-slate-900/40 transition"
                            >
                                <td class="px-4 py-3.5 font-mono font-semibold text-amber-600">
                                    {{ ot.spl_number }}
                                </td>
                                <td class="px-4 py-3.5">
                                    <div class="font-semibold text-slate-900 dark:text-white">
                                        {{ ot.employee?.user?.name }}
                                    </div>
                                    <div class="text-[10px] text-slate-400">
                                        {{ ot.employee?.nik }} • {{ ot.employee?.department?.name }}
                                    </div>
                                </td>
                                <td class="px-4 py-3.5">
                                    <div class="font-medium text-slate-800 dark:text-slate-200">
                                        {{ ot.date }}
                                    </div>
                                    <div class="text-[10px] text-slate-400 font-mono">
                                        {{ ot.start_time }} - {{ ot.end_time }}
                                        <span v-if="ot.is_holiday" class="text-rose-600 font-semibold ml-1">(Hari Libur)</span>
                                    </div>
                                </td>
                                <td class="px-4 py-3.5 font-mono">
                                    {{ ot.total_hours }} jam
                                </td>
                                <td class="px-4 py-3.5 font-mono font-bold text-amber-600">
                                    {{ ot.multiplier_hours }} jam bayar
                                </td>
                                <td class="px-4 py-3.5">
                                    <span v-if="ot.compensation_type === 'cash'" class="inline-flex items-center gap-1 font-mono font-semibold text-emerald-600">
                                        <Banknote class="w-3.5 h-3.5" />
                                        {{ formatCurrency(ot.total_overtime_pay) }}
                                    </span>
                                    <span v-else class="inline-flex items-center gap-1 font-mono font-semibold text-purple-600">
                                        <Layers class="w-3.5 h-3.5" />
                                        +{{ ot.multiplier_hours }}h Time Bank
                                    </span>
                                </td>
                                <td class="px-4 py-3.5 max-w-xs truncate text-slate-500">
                                    {{ ot.work_target || ot.reason }}
                                </td>
                                <td class="px-4 py-3.5">
                                    <span
                                        :class="[
                                            'px-2 py-0.5 rounded text-[10px] font-semibold',
                                            ot.status === 'approved' ? 'bg-emerald-50 text-emerald-700 dark:bg-emerald-950 dark:text-emerald-300' :
                                            ot.status === 'pending_spv' ? 'bg-amber-50 text-amber-700 dark:bg-amber-950 dark:text-amber-300' :
                                            ot.status === 'pending_om' ? 'bg-blue-50 text-blue-700 dark:bg-blue-950 dark:text-blue-300' :
                                            'bg-rose-50 text-rose-700 dark:bg-rose-950 dark:text-rose-300',
                                        ]"
                                    >
                                        {{ ot.status.replace('_', ' ').toUpperCase() }}
                                    </span>
                                </td>
                                <td class="px-4 py-3.5 text-right">
                                    <div class="flex items-center justify-end gap-1.5">
                                        <button
                                            v-if="ot.status === 'pending_spv'"
                                            @click="handleApproval(ot.id, 'approve_spv')"
                                            class="px-2 py-1 text-[11px] font-medium bg-emerald-50 text-emerald-700 hover:bg-emerald-100 rounded-lg transition"
                                            title="Approve Supervisor"
                                        >
                                            Approve SPV
                                        </button>
                                        <button
                                            v-else-if="ot.status === 'pending_om'"
                                            @click="handleApproval(ot.id, 'approve_om')"
                                            class="px-2 py-1 text-[11px] font-medium bg-blue-50 text-blue-700 hover:bg-blue-100 rounded-lg transition"
                                            title="Approve OM"
                                        >
                                            Approve OM
                                        </button>
                                        <button
                                            v-if="ot.status.startsWith('pending')"
                                            @click="handleApproval(ot.id, 'reject')"
                                            class="px-2 py-1 text-[11px] font-medium bg-rose-50 text-rose-700 hover:bg-rose-100 rounded-lg transition"
                                            title="Tolak SPL"
                                        >
                                            Tolak
                                        </button>

                                        <a
                                            :href="`/hris/overtime/${ot.id}/pdf`"
                                            target="_blank"
                                            class="p-1.5 rounded-lg text-slate-500 hover:text-blue-600 hover:bg-slate-100 transition"
                                            title="Cetak Surat Perintah Lembur PDF"
                                        >
                                            <Download class="w-4 h-4" />
                                        </a>
                                    </div>
                                </td>
                            </tr>
                            <tr v-if="overtimes.data.length === 0">
                                <td colspan="9" class="text-center py-10 text-slate-400">
                                    Tidak ada data Surat Perintah Lembur (SPL).
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </ModuleLayout>
</template>
