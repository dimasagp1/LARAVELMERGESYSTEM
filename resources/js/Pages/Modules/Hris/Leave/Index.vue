<script setup>
import { ref } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import ModuleLayout from '@/Layouts/ModuleLayout.vue';
import {
    CalendarOff,
    Plus,
    Search,
    Filter,
    CheckCircle2,
    XCircle,
    Download,
    Eye,
    Clock,
    FileText,
} from 'lucide-vue-next';

const props = defineProps({
    leaves: Object,
    categories: Array,
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
const categoryId = ref(props.filters.leave_category_id || '');

const applyFilters = () => {
    router.get('/hris/leaves', {
        search: search.value || undefined,
        status: status.value || undefined,
        leave_category_id: categoryId.value || undefined,
    }, {
        preserveState: true,
        preserveScroll: true,
    });
};

const handleApproval = (leaveId, action) => {
    router.post(`/hris/leaves/${leaveId}/approve`, {
        action: action,
    }, {
        preserveScroll: true,
    });
};
</script>

<template>
    <Head title="Manajemen Cuti & Izin — HRISNOW" />

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
                    <div class="p-3 bg-teal-500/10 text-teal-600 dark:text-teal-400 rounded-xl">
                        <CalendarOff class="w-8 h-8" />
                    </div>
                    <div>
                        <h1 class="text-2xl font-bold text-slate-900 dark:text-white tracking-tight">
                            Manajemen Permohonan Cuti & Izin
                        </h1>
                        <p class="text-sm text-slate-500 dark:text-slate-400 mt-0.5">
                            Alur persetujuan bertingkat Atasan Langsung & HRD, pemotongan otomatis saldo cuti tahunan.
                        </p>
                    </div>
                </div>

                <div class="flex items-center gap-3">
                    <div class="flex items-center gap-2 text-xs">
                        <span class="px-3 py-1.5 rounded-xl bg-amber-50 text-amber-700 border border-amber-200 font-semibold">
                            Pending: {{ summary.pending }}
                        </span>
                        <span class="px-3 py-1.5 rounded-xl bg-emerald-50 text-emerald-700 border border-emerald-200 font-semibold">
                            Disetujui: {{ summary.approved }}
                        </span>
                    </div>

                    <Link
                        href="/hris/leaves/create"
                        class="inline-flex items-center gap-2 px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-xl text-sm transition shadow-sm"
                    >
                        <Plus class="w-4 h-4" />
                        Ajukan Cuti Baru
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
                        placeholder="Cari nomor cuti, nama karyawan, atau alasan..."
                        class="w-full pl-10 pr-4 py-2 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl text-sm focus:ring-2 focus:ring-blue-500 outline-none text-slate-900 dark:text-white"
                    />
                </div>

                <div class="flex items-center gap-3 w-full md:w-auto">
                    <select
                        v-model="categoryId"
                        @change="applyFilters"
                        class="px-3 py-2 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl text-xs text-slate-700 dark:text-slate-300 outline-none"
                    >
                        <option value="">Semua Kategori Cuti</option>
                        <option v-for="cat in categories" :key="cat.id" :value="cat.id">
                            {{ cat.name }}
                        </option>
                    </select>

                    <select
                        v-model="status"
                        @change="applyFilters"
                        class="px-3 py-2 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl text-xs text-slate-700 dark:text-slate-300 outline-none"
                    >
                        <option value="">Semua Status</option>
                        <option value="pending_spv">Pending Supervisor</option>
                        <option value="pending_hr">Pending HRD</option>
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
                                <th class="px-4 py-3">No. Pengajuan</th>
                                <th class="px-4 py-3">Karyawan</th>
                                <th class="px-4 py-3">Kategori</th>
                                <th class="px-4 py-3">Rentang Tanggal</th>
                                <th class="px-4 py-3">Total Hari</th>
                                <th class="px-4 py-3">Alasan Cuti</th>
                                <th class="px-4 py-3">Status</th>
                                <th class="px-4 py-3 text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 dark:divide-slate-700">
                            <tr
                                v-for="leave in leaves.data"
                                :key="leave.id"
                                class="hover:bg-slate-50/80 dark:hover:bg-slate-900/40 transition"
                            >
                                <td class="px-4 py-3.5 font-mono font-semibold text-blue-600">
                                    {{ leave.leave_number }}
                                </td>
                                <td class="px-4 py-3.5">
                                    <div class="font-semibold text-slate-900 dark:text-white">
                                        {{ leave.employee?.user?.name }}
                                    </div>
                                    <div class="text-[10px] text-slate-400">
                                        {{ leave.employee?.nik }} • {{ leave.employee?.department?.name }}
                                    </div>
                                </td>
                                <td class="px-4 py-3.5">
                                    <span class="font-medium text-slate-800 dark:text-slate-200">
                                        {{ leave.category?.name }}
                                    </span>
                                </td>
                                <td class="px-4 py-3.5">
                                    <div class="font-mono text-slate-900 dark:text-white">
                                        {{ leave.start_date }} s/d {{ leave.end_date }}
                                    </div>
                                </td>
                                <td class="px-4 py-3.5 font-bold text-teal-600 font-mono">
                                    {{ leave.total_days }} Hari
                                </td>
                                <td class="px-4 py-3.5 max-w-xs truncate text-slate-500">
                                    {{ leave.reason }}
                                </td>
                                <td class="px-4 py-3.5">
                                    <span
                                        :class="[
                                            'px-2 py-0.5 rounded text-[10px] font-semibold',
                                            leave.status === 'approved' ? 'bg-emerald-50 text-emerald-700 dark:bg-emerald-950 dark:text-emerald-300' :
                                            leave.status === 'pending_spv' ? 'bg-amber-50 text-amber-700 dark:bg-amber-950 dark:text-amber-300' :
                                            leave.status === 'pending_hr' ? 'bg-blue-50 text-blue-700 dark:bg-blue-950 dark:text-blue-300' :
                                            'bg-rose-50 text-rose-700 dark:bg-rose-950 dark:text-rose-300',
                                        ]"
                                    >
                                        {{ leave.status.replace('_', ' ').toUpperCase() }}
                                    </span>
                                </td>
                                <td class="px-4 py-3.5 text-right">
                                    <div class="flex items-center justify-end gap-1.5">
                                        <button
                                            v-if="leave.status === 'pending_spv'"
                                            @click="handleApproval(leave.id, 'approve_spv')"
                                            class="px-2 py-1 text-[11px] font-medium bg-emerald-50 text-emerald-700 hover:bg-emerald-100 rounded-lg transition"
                                            title="Approve Atasan Langsung"
                                        >
                                            Approve SPV
                                        </button>
                                        <button
                                            v-else-if="leave.status === 'pending_hr'"
                                            @click="handleApproval(leave.id, 'approve_hr')"
                                            class="px-2 py-1 text-[11px] font-medium bg-blue-50 text-blue-700 hover:bg-blue-100 rounded-lg transition"
                                            title="Approve HRD"
                                        >
                                            Approve HR
                                        </button>
                                        <button
                                            v-if="leave.status.startsWith('pending')"
                                            @click="handleApproval(leave.id, 'reject')"
                                            class="px-2 py-1 text-[11px] font-medium bg-rose-50 text-rose-700 hover:bg-rose-100 rounded-lg transition"
                                            title="Tolak Pengajuan"
                                        >
                                            Tolak
                                        </button>

                                        <a
                                            :href="`/hris/leaves/${leave.id}/pdf`"
                                            target="_blank"
                                            class="p-1.5 rounded-lg text-slate-500 hover:text-blue-600 hover:bg-slate-100 transition"
                                            title="Cetak Form Permohonan Cuti PDF"
                                        >
                                            <Download class="w-4 h-4" />
                                        </a>
                                    </div>
                                </td>
                            </tr>
                            <tr v-if="leaves.data.length === 0">
                                <td colspan="8" class="text-center py-10 text-slate-400">
                                    Tidak ada pengajuan cuti yang ditemukan.
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </ModuleLayout>
</template>
