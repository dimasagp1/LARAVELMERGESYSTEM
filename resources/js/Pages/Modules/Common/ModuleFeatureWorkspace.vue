<script setup>
import { ref, computed } from 'vue';
import { Head, router, useForm, usePage } from '@inertiajs/vue3';
import ModuleLayout from '@/Layouts/ModuleLayout.vue';
import {
    Layers,
    Search,
    Filter,
    Download,
    Plus,
    RefreshCw,
    CheckCircle2,
    Clock,
    AlertCircle,
    ArrowUpRight,
    ChevronRight,
    FileSpreadsheet,
    FileText,
    Calendar,
    Eye,
    Edit3,
    Trash2,
    X,
    Building2,
    User,
    DollarSign,
    Check,
    AlertTriangle,
    Tag,
} from 'lucide-vue-next';

const props = defineProps({
    module: {
        type: Object,
        default: () => ({ name: 'Modul ERP', slug: '', menus: [] }),
    },
    feature: {
        type: Object,
        default: () => ({ name: 'Fitur Operasional', route: '', category: 'Operasional', description: '' }),
    },
    category: {
        type: String,
        default: 'Operasional',
    },
    records: {
        type: Object,
        default: () => ({ data: [], links: [], total: 0 }),
    },
    summary: {
        type: Object,
        default: () => ({
            total_records: 0,
            approved_count: 0,
            pending_count: 0,
            in_progress_count: 0,
            draft_count: 0,
            compliance_rate: 100,
        }),
    },
    nextCode: {
        type: String,
        default: 'REC-2026-0001',
    },
    filters: {
        type: Object,
        default: () => ({ q: '', status: 'all' }),
    },
    breadcrumbs: {
        type: Array,
        default: () => [],
    },
});

const page = usePage();
const searchQuery = ref(props.filters.q || '');
const selectedStatus = ref(props.filters.status || 'all');
const isRefreshing = ref(false);

// Modals state
const showCreateModal = ref(false);
const showEditModal = ref(false);
const showDetailModal = ref(false);
const showDeleteModal = ref(false);

const activeRecord = ref(null);

// Forms
const createForm = useForm({
    feature_route: props.feature.route || '',
    record_code: props.nextCode || '',
    title: '',
    category: props.category || 'Operasional',
    pic_name: page.props.auth?.user?.name || 'Administrator',
    entry_date: new Date().toISOString().split('T')[0],
    status: 'In Progress',
    amount: null,
    description: '',
    custom_attributes: {},
});

const editForm = useForm({
    title: '',
    category: '',
    pic_name: '',
    entry_date: '',
    status: 'In Progress',
    amount: null,
    description: '',
    custom_attributes: {},
});

function handleFilter() {
    router.get(
        props.feature.route,
        {
            q: searchQuery.value,
            status: selectedStatus.value,
        },
        { preserveState: true, replace: true }
    );
}

function refreshData() {
    isRefreshing.value = true;
    router.reload({
        onFinish: () => {
            isRefreshing.value = false;
        },
    });
}

function openCreateModal() {
    createForm.reset();
    createForm.feature_route = props.feature.route;
    createForm.record_code = props.nextCode;
    createForm.category = props.category;
    createForm.pic_name = page.props.auth?.user?.name || 'Administrator';
    createForm.entry_date = new Date().toISOString().split('T')[0];
    createForm.status = 'In Progress';
    createForm.amount = null;
    createForm.description = '';
    showCreateModal.value = true;
}

function submitCreate() {
    createForm.post('/modules-feature/records', {
        preserveScroll: true,
        onSuccess: () => {
            showCreateModal.value = false;
            createForm.reset();
        },
    });
}

function openEditModal(record) {
    activeRecord.value = record;
    editForm.title = record.title;
    editForm.category = record.category;
    editForm.pic_name = record.pic_name;
    editForm.entry_date = record.entry_date ? record.entry_date.split('T')[0] : '';
    editForm.status = record.status;
    editForm.amount = record.amount ? Number(record.amount) : null;
    editForm.description = record.description || '';
    editForm.custom_attributes = record.custom_attributes || {};
    showEditModal.value = true;
}

function submitEdit() {
    if (!activeRecord.value) return;
    editForm.put(`/modules-feature/records/${activeRecord.value.id}`, {
        preserveScroll: true,
        onSuccess: () => {
            showEditModal.value = false;
            activeRecord.value = null;
        },
    });
}

function openDetailModal(record) {
    activeRecord.value = record;
    showDetailModal.value = true;
}

function confirmDelete(record) {
    activeRecord.value = record;
    showDeleteModal.value = true;
}

function executeDelete() {
    if (!activeRecord.value) return;
    router.delete(`/modules-feature/records/${activeRecord.value.id}`, {
        preserveScroll: true,
        onSuccess: () => {
            showDeleteModal.value = false;
            activeRecord.value = null;
        },
    });
}

function exportCsv() {
    const params = new URLSearchParams({
        route: props.feature.route,
        q: searchQuery.value || '',
        status: selectedStatus.value || 'all',
    });
    window.location.href = `/modules-feature/records/export?${params.toString()}`;
}

function formatCurrency(val) {
    if (val === null || val === undefined || isNaN(val)) return '-';
    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        maximumFractionDigits: 0,
    }).format(val);
}

function formatDate(dateStr) {
    if (!dateStr) return '-';
    try {
        const d = new Date(dateStr);
        return d.toLocaleDateString('id-ID', { day: '2-digit', month: 'short', year: 'numeric' });
    } catch {
        return dateStr;
    }
}

function getStatusBadge(status) {
    switch (status) {
        case 'Approved':
            return 'bg-emerald-50 dark:bg-emerald-950/60 text-emerald-700 dark:text-emerald-300 border-emerald-200 dark:border-emerald-800';
        case 'In Progress':
            return 'bg-blue-50 dark:bg-blue-950/60 text-blue-700 dark:text-blue-300 border-blue-200 dark:border-blue-800';
        case 'Pending Review':
            return 'bg-amber-50 dark:bg-amber-950/60 text-amber-700 dark:text-amber-300 border-amber-200 dark:border-amber-800';
        case 'Rejected':
            return 'bg-rose-50 dark:bg-rose-950/60 text-rose-700 dark:text-rose-300 border-rose-200 dark:border-rose-800';
        default:
            return 'bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 border-slate-200 dark:border-slate-700';
    }
}
</script>

<template>
    <Head :title="`${feature.name} - ${module.name}`" />

    <ModuleLayout
        :module-slug="module.slug"
        :module-name="module.name"
        :breadcrumbs="breadcrumbs"
        :menus="module.menus"
    >
        <div class="space-y-6">
            <!-- Feature Header & Summary Card -->
            <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 p-6 shadow-xs">
                <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
                    <div class="flex items-start gap-4">
                        <div class="w-12 h-12 rounded-2xl bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-200 flex items-center justify-center shrink-0 border border-slate-200 dark:border-slate-700 shadow-xs">
                            <Layers class="w-6 h-6 text-emerald-600 dark:text-emerald-400" />
                        </div>
                        <div>
                            <div class="flex items-center gap-2">
                                <span class="px-2.5 py-0.5 rounded-full text-[11px] font-semibold bg-emerald-50 dark:bg-emerald-950/50 text-emerald-700 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800">
                                    {{ category }}
                                </span>
                                <span class="text-xs text-slate-400 font-mono">{{ feature.route }}</span>
                            </div>
                            <h1 class="text-xl font-bold text-slate-900 dark:text-white mt-1">
                                {{ feature.name }}
                            </h1>
                            <p class="text-xs text-slate-500 dark:text-slate-400 mt-1 max-w-2xl">
                                {{ feature.description || 'Pusat operasional dan tata kelola data terpadu untuk kepatuhan standar industri.' }}
                            </p>
                        </div>
                    </div>

                    <!-- Action Buttons -->
                    <div class="flex flex-wrap items-center gap-2">
                        <button
                            type="button"
                            @click="refreshData"
                            class="p-2 rounded-xl text-slate-500 hover:text-slate-700 dark:hover:text-slate-200 hover:bg-slate-100 dark:hover:bg-slate-800 border border-slate-200 dark:border-slate-700 transition"
                            title="Segarkan Data"
                        >
                            <RefreshCw :class="['w-4 h-4', isRefreshing ? 'animate-spin text-emerald-600' : '']" />
                        </button>
                        <button
                            type="button"
                            @click="exportCsv"
                            class="inline-flex items-center gap-1.5 px-3 py-2 rounded-xl text-xs font-semibold text-slate-700 dark:text-slate-200 bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 border border-slate-200 dark:border-slate-700 transition cursor-pointer"
                        >
                            <Download class="w-3.5 h-3.5" />
                            <span>Ekspor CSV</span>
                        </button>
                        <button
                            type="button"
                            @click="openCreateModal"
                            class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl text-xs font-semibold text-white bg-emerald-600 hover:bg-emerald-700 shadow-xs transition cursor-pointer"
                        >
                            <Plus class="w-3.5 h-3.5" />
                            <span>Entri / Buat Baru</span>
                        </button>
                    </div>
                </div>

                <!-- KPI Metric Mini Strip -->
                <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 mt-6 pt-5 border-t border-slate-100 dark:border-slate-800">
                    <div class="p-3 rounded-xl bg-slate-50 dark:bg-slate-800/40 border border-slate-100 dark:border-slate-800">
                        <div class="text-[11px] font-medium text-slate-400 uppercase tracking-wider">Total Entri</div>
                        <div class="text-lg font-bold text-slate-900 dark:text-white mt-0.5">
                            {{ summary.total_records || records.total || 0 }}
                        </div>
                    </div>
                    <div class="p-3 rounded-xl bg-emerald-50/50 dark:bg-emerald-950/20 border border-emerald-100 dark:border-emerald-900/40">
                        <div class="text-[11px] font-medium text-emerald-700 dark:text-emerald-400 uppercase tracking-wider">Approved / Selesai</div>
                        <div class="text-lg font-bold text-emerald-700 dark:text-emerald-300 mt-0.5">
                            {{ summary.approved_count || 0 }}
                        </div>
                    </div>
                    <div class="p-3 rounded-xl bg-amber-50/50 dark:bg-amber-950/20 border border-amber-100 dark:border-amber-900/40">
                        <div class="text-[11px] font-medium text-amber-700 dark:text-amber-400 uppercase tracking-wider">Pending Review</div>
                        <div class="text-lg font-bold text-amber-700 dark:text-amber-300 mt-0.5">
                            {{ summary.pending_count || 0 }}
                        </div>
                    </div>
                    <div class="p-3 rounded-xl bg-blue-50/50 dark:bg-blue-950/20 border border-blue-100 dark:border-blue-900/40">
                        <div class="text-[11px] font-medium text-blue-700 dark:text-blue-400 uppercase tracking-wider">Tingkat Kepatuhan</div>
                        <div class="text-lg font-bold text-blue-700 dark:text-blue-300 mt-0.5">
                            {{ summary.compliance_rate || 100 }}%
                        </div>
                    </div>
                </div>
            </div>

            <!-- Workspace Table & Filter Section -->
            <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 overflow-hidden shadow-xs">
                <!-- Toolbar Filter -->
                <div class="p-4 border-b border-slate-100 dark:border-slate-800 flex flex-col sm:flex-row items-stretch sm:items-center justify-between gap-3 bg-slate-50/40 dark:bg-slate-800/30">
                    <form @submit.prevent="handleFilter" class="relative flex-1 max-w-sm">
                        <Search class="w-4 h-4 absolute left-3 top-1/2 -translate-y-1/2 text-slate-400" />
                        <input
                            v-model="searchQuery"
                            type="text"
                            placeholder="Cari nomor kode, nama entri, atau PIC... (Enter)"
                            class="w-full pl-9 pr-3 py-1.5 text-xs rounded-xl bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-slate-800 dark:text-slate-100 placeholder-slate-400 focus:outline-none focus:ring-1 focus:ring-emerald-500"
                        />
                    </form>

                    <div class="flex items-center gap-2">
                        <div class="flex items-center gap-1.5 text-xs text-slate-500">
                            <Filter class="w-3.5 h-3.5 text-slate-400" />
                            <span>Status:</span>
                        </div>
                        <select
                            v-model="selectedStatus"
                            @change="handleFilter"
                            class="text-xs px-2.5 py-1.5 rounded-xl bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-slate-700 dark:text-slate-200 focus:outline-none focus:ring-1 focus:ring-emerald-500 cursor-pointer"
                        >
                            <option value="all">Semua Status</option>
                            <option value="Approved">Approved</option>
                            <option value="In Progress">In Progress</option>
                            <option value="Pending Review">Pending Review</option>
                            <option value="Draft">Draft</option>
                            <option value="Rejected">Rejected</option>
                        </select>
                    </div>
                </div>

                <!-- Table Content -->
                <div class="overflow-x-auto custom-scrollbar">
                    <table class="w-full text-left text-xs">
                        <thead class="bg-slate-50 dark:bg-slate-800/60 text-slate-500 dark:text-slate-400 uppercase text-[10px] tracking-wider border-b border-slate-100 dark:border-slate-800">
                            <tr>
                                <th class="px-4 py-3 font-semibold">No</th>
                                <th class="px-4 py-3 font-semibold">Nomor Kode</th>
                                <th class="px-4 py-3 font-semibold">Judul / Entri</th>
                                <th class="px-4 py-3 font-semibold">PIC & Tanggal</th>
                                <th class="px-4 py-3 font-semibold text-right">Nilai / Anggaran</th>
                                <th class="px-4 py-3 font-semibold">Status</th>
                                <th class="px-4 py-3 font-semibold text-center">Aksi (CRUD)</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 dark:divide-slate-800 text-slate-700 dark:text-slate-300">
                            <tr
                                v-for="(record, idx) in records.data"
                                :key="record.id"
                                class="hover:bg-slate-50/80 dark:hover:bg-slate-800/40 transition"
                            >
                                <td class="px-4 py-3 text-slate-400 font-mono text-[11px]">
                                    {{ (records.from || 1) + idx }}
                                </td>
                                <td class="px-4 py-3">
                                    <span class="font-mono text-[11px] font-semibold text-slate-800 dark:text-slate-200 bg-slate-100 dark:bg-slate-800 px-2 py-0.5 rounded-md border border-slate-200 dark:border-slate-700">
                                        {{ record.record_code }}
                                    </span>
                                </td>
                                <td class="px-4 py-3">
                                    <div class="font-semibold text-slate-900 dark:text-white">
                                        {{ record.title }}
                                    </div>
                                    <div class="text-[11px] text-slate-400 truncate max-w-xs mt-0.5">
                                        {{ record.description || record.category }}
                                    </div>
                                </td>
                                <td class="px-4 py-3">
                                    <div class="font-medium text-slate-800 dark:text-slate-200">
                                        {{ record.pic_name || '-' }}
                                    </div>
                                    <div class="text-[11px] text-slate-400 mt-0.5">
                                        {{ formatDate(record.entry_date) }}
                                    </div>
                                </td>
                                <td class="px-4 py-3 text-right font-mono font-medium text-slate-800 dark:text-slate-200">
                                    {{ formatCurrency(record.amount) }}
                                </td>
                                <td class="px-4 py-3">
                                    <span :class="['px-2 py-0.5 rounded-full text-[10px] font-bold border inline-block', getStatusBadge(record.status)]">
                                        {{ record.status }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-center">
                                    <div class="flex items-center justify-center gap-1.5">
                                        <button
                                            type="button"
                                            @click="openDetailModal(record)"
                                            class="p-1.5 rounded-lg text-slate-500 hover:text-emerald-600 dark:hover:text-emerald-400 hover:bg-slate-100 dark:hover:bg-slate-800 transition cursor-pointer"
                                            title="Lihat Detail (Read)"
                                        >
                                            <Eye class="w-4 h-4" />
                                        </button>
                                        <button
                                            type="button"
                                            @click="openEditModal(record)"
                                            class="p-1.5 rounded-lg text-slate-500 hover:text-blue-600 dark:hover:text-blue-400 hover:bg-slate-100 dark:hover:bg-slate-800 transition cursor-pointer"
                                            title="Edit Data (Update)"
                                        >
                                            <Edit3 class="w-4 h-4" />
                                        </button>
                                        <button
                                            type="button"
                                            @click="confirmDelete(record)"
                                            class="p-1.5 rounded-lg text-slate-500 hover:text-rose-600 dark:hover:text-rose-400 hover:bg-slate-100 dark:hover:bg-slate-800 transition cursor-pointer"
                                            title="Hapus Data (Delete)"
                                        >
                                            <Trash2 class="w-4 h-4" />
                                        </button>
                                    </div>
                                </td>
                            </tr>

                            <tr v-if="!records.data || records.data.length === 0">
                                <td colspan="7" class="px-4 py-12 text-center text-slate-400">
                                    <div class="flex flex-col items-center justify-center gap-2">
                                        <FileSpreadsheet class="w-10 h-10 text-slate-300 dark:text-slate-600" />
                                        <p class="text-sm font-medium">Belum ada data untuk fitur ini.</p>
                                        <button
                                            type="button"
                                            @click="openCreateModal"
                                            class="mt-2 inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl text-xs font-semibold text-white bg-emerald-600 hover:bg-emerald-700 transition cursor-pointer"
                                        >
                                            <Plus class="w-3.5 h-3.5" />
                                            <span>Tambah Data Pertama</span>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Pagination Footer -->
                <div class="p-4 border-t border-slate-100 dark:border-slate-800 flex flex-col sm:flex-row items-center justify-between gap-3 text-xs text-slate-500 bg-slate-50/20 dark:bg-slate-800/20">
                    <div>
                        Menampilkan <span class="font-semibold text-slate-700 dark:text-slate-300">{{ records.from || 0 }}</span>
                        - <span class="font-semibold text-slate-700 dark:text-slate-300">{{ records.to || 0 }}</span>
                        dari <span class="font-semibold text-slate-700 dark:text-slate-300">{{ records.total || 0 }}</span> entri
                    </div>
                    <div v-if="records.links && records.links.length > 3" class="flex items-center gap-1 flex-wrap">
                        <template v-for="(link, i) in records.links" :key="i">
                            <button
                                v-if="link.url"
                                type="button"
                                @click="router.get(link.url, {}, { preserveState: true })"
                                :class="[
                                    'px-2.5 py-1 rounded-lg text-xs transition cursor-pointer',
                                    link.active
                                        ? 'bg-emerald-600 text-white font-semibold'
                                        : 'border border-slate-200 dark:border-slate-700 text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800',
                                ]"
                                v-html="link.label"
                            />
                            <span
                                v-else
                                class="px-2.5 py-1 text-xs text-slate-400 opacity-50 border border-transparent"
                                v-html="link.label"
                            />
                        </template>
                    </div>
                </div>
            </div>
        </div>

        <!-- CREATE MODAL -->
        <div v-if="showCreateModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-xs">
            <div class="bg-white dark:bg-slate-900 rounded-2xl max-w-lg w-full p-6 shadow-2xl border border-slate-200 dark:border-slate-800 max-h-[90vh] overflow-y-auto custom-scrollbar">
                <div class="flex items-center justify-between pb-3 border-b border-slate-100 dark:border-slate-800">
                    <h3 class="font-bold text-slate-900 dark:text-white text-base flex items-center gap-2">
                        <Plus class="w-5 h-5 text-emerald-600" />
                        Tambah Entri Baru (Create)
                    </h3>
                    <button @click="showCreateModal = false" class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200">
                        <X class="w-5 h-5" />
                    </button>
                </div>

                <form @submit.prevent="submitCreate" class="space-y-4 mt-4">
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Nomor Kode</label>
                            <input
                                v-model="createForm.record_code"
                                type="text"
                                class="w-full px-3 py-2 text-xs rounded-xl bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-slate-800 dark:text-slate-100 font-mono"
                                required
                            />
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Kategori</label>
                            <input
                                v-model="createForm.category"
                                type="text"
                                class="w-full px-3 py-2 text-xs rounded-xl bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-slate-800 dark:text-slate-100"
                                required
                            />
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Judul / Nama Entri *</label>
                        <input
                            v-model="createForm.title"
                            type="text"
                            placeholder="Contoh: Pengajuan Shift / Pemeliharaan Mesin"
                            class="w-full px-3 py-2 text-xs rounded-xl bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-slate-800 dark:text-slate-100 focus:outline-none focus:ring-1 focus:ring-emerald-500"
                            required
                        />
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">PIC / Pemohon</label>
                            <input
                                v-model="createForm.pic_name"
                                type="text"
                                class="w-full px-3 py-2 text-xs rounded-xl bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-slate-800 dark:text-slate-100"
                            />
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Tanggal Entri</label>
                            <input
                                v-model="createForm.entry_date"
                                type="date"
                                class="w-full px-3 py-2 text-xs rounded-xl bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-slate-800 dark:text-slate-100"
                            />
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Status Alur Kerja</label>
                            <select
                                v-model="createForm.status"
                                class="w-full px-3 py-2 text-xs rounded-xl bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-slate-800 dark:text-slate-100"
                            >
                                <option value="Draft">Draft</option>
                                <option value="In Progress">In Progress</option>
                                <option value="Pending Review">Pending Review</option>
                                <option value="Approved">Approved</option>
                                <option value="Rejected">Rejected</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Nilai Nominal / Budget (Rp)</label>
                            <input
                                v-model="createForm.amount"
                                type="number"
                                placeholder="0"
                                class="w-full px-3 py-2 text-xs rounded-xl bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-slate-800 dark:text-slate-100"
                            />
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Deskripsi / Catatan Rinci</label>
                        <textarea
                            v-model="createForm.description"
                            rows="3"
                            placeholder="Tuliskan spesifikasi, catatan teknis, atau justifikasi..."
                            class="w-full px-3 py-2 text-xs rounded-xl bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-slate-800 dark:text-slate-100 focus:outline-none focus:ring-1 focus:ring-emerald-500"
                        />
                    </div>

                    <div class="flex items-center justify-end gap-2 pt-3 border-t border-slate-100 dark:border-slate-800">
                        <button
                            type="button"
                            @click="showCreateModal = false"
                            class="px-4 py-2 text-xs font-semibold rounded-xl text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800"
                        >
                            Batal
                        </button>
                        <button
                            type="submit"
                            :disabled="createForm.processing"
                            class="px-4 py-2 text-xs font-semibold rounded-xl text-white bg-emerald-600 hover:bg-emerald-700 shadow-xs disabled:opacity-50 cursor-pointer"
                        >
                            {{ createForm.processing ? 'Menyimpan...' : 'Simpan Entri' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- EDIT MODAL -->
        <div v-if="showEditModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-xs">
            <div class="bg-white dark:bg-slate-900 rounded-2xl max-w-lg w-full p-6 shadow-2xl border border-slate-200 dark:border-slate-800 max-h-[90vh] overflow-y-auto custom-scrollbar">
                <div class="flex items-center justify-between pb-3 border-b border-slate-100 dark:border-slate-800">
                    <h3 class="font-bold text-slate-900 dark:text-white text-base flex items-center gap-2">
                        <Edit3 class="w-5 h-5 text-blue-600" />
                        Edit Data (Update) - {{ activeRecord?.record_code }}
                    </h3>
                    <button @click="showEditModal = false" class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200">
                        <X class="w-5 h-5" />
                    </button>
                </div>

                <form @submit.prevent="submitEdit" class="space-y-4 mt-4">
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Judul / Nama Entri *</label>
                        <input
                            v-model="editForm.title"
                            type="text"
                            class="w-full px-3 py-2 text-xs rounded-xl bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-slate-800 dark:text-slate-100 focus:outline-none focus:ring-1 focus:ring-emerald-500"
                            required
                        />
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Kategori</label>
                            <input
                                v-model="editForm.category"
                                type="text"
                                class="w-full px-3 py-2 text-xs rounded-xl bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-slate-800 dark:text-slate-100"
                            />
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">PIC Pelaksana</label>
                            <input
                                v-model="editForm.pic_name"
                                type="text"
                                class="w-full px-3 py-2 text-xs rounded-xl bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-slate-800 dark:text-slate-100"
                            />
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Tanggal Entri</label>
                            <input
                                v-model="editForm.entry_date"
                                type="date"
                                class="w-full px-3 py-2 text-xs rounded-xl bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-slate-800 dark:text-slate-100"
                            />
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Status Alur Kerja</label>
                            <select
                                v-model="editForm.status"
                                class="w-full px-3 py-2 text-xs rounded-xl bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-slate-800 dark:text-slate-100"
                            >
                                <option value="Draft">Draft</option>
                                <option value="In Progress">In Progress</option>
                                <option value="Pending Review">Pending Review</option>
                                <option value="Approved">Approved</option>
                                <option value="Rejected">Rejected</option>
                            </select>
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Nilai Nominal / Budget (Rp)</label>
                        <input
                            v-model="editForm.amount"
                            type="number"
                            class="w-full px-3 py-2 text-xs rounded-xl bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-slate-800 dark:text-slate-100"
                        />
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Deskripsi / Catatan Rinci</label>
                        <textarea
                            v-model="editForm.description"
                            rows="3"
                            class="w-full px-3 py-2 text-xs rounded-xl bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-slate-800 dark:text-slate-100 focus:outline-none focus:ring-1 focus:ring-emerald-500"
                        />
                    </div>

                    <div class="flex items-center justify-end gap-2 pt-3 border-t border-slate-100 dark:border-slate-800">
                        <button
                            type="button"
                            @click="showEditModal = false"
                            class="px-4 py-2 text-xs font-semibold rounded-xl text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800"
                        >
                            Batal
                        </button>
                        <button
                            type="submit"
                            :disabled="editForm.processing"
                            class="px-4 py-2 text-xs font-semibold rounded-xl text-white bg-blue-600 hover:bg-blue-700 shadow-xs disabled:opacity-50 cursor-pointer"
                        >
                            {{ editForm.processing ? 'Menyimpan...' : 'Perbarui Data' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- DETAIL MODAL -->
        <div v-if="showDetailModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-xs">
            <div class="bg-white dark:bg-slate-900 rounded-2xl max-w-lg w-full p-6 shadow-2xl border border-slate-200 dark:border-slate-800 max-h-[90vh] overflow-y-auto custom-scrollbar">
                <div class="flex items-center justify-between pb-3 border-b border-slate-100 dark:border-slate-800">
                    <div class="flex items-center gap-2">
                        <span class="font-mono text-xs font-bold px-2 py-0.5 rounded bg-slate-100 dark:bg-slate-800 text-slate-800 dark:text-slate-200 border border-slate-200 dark:border-slate-700">
                            {{ activeRecord?.record_code }}
                        </span>
                        <span :class="['px-2 py-0.5 rounded-full text-[10px] font-bold border', getStatusBadge(activeRecord?.status)]">
                            {{ activeRecord?.status }}
                        </span>
                    </div>
                    <button @click="showDetailModal = false" class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200">
                        <X class="w-5 h-5" />
                    </button>
                </div>

                <div class="mt-4 space-y-4 text-xs">
                    <div>
                        <div class="text-[11px] font-medium text-slate-400 uppercase tracking-wider">Judul / Nama Entri</div>
                        <div class="text-base font-bold text-slate-900 dark:text-white mt-0.5">
                            {{ activeRecord?.title }}
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-4 p-3 rounded-xl bg-slate-50 dark:bg-slate-800/40 border border-slate-100 dark:border-slate-800">
                        <div>
                            <div class="text-[10px] text-slate-400 uppercase font-semibold">Kategori</div>
                            <div class="font-medium text-slate-800 dark:text-slate-200 mt-0.5">{{ activeRecord?.category }}</div>
                        </div>
                        <div>
                            <div class="text-[10px] text-slate-400 uppercase font-semibold">PIC Pelaksana</div>
                            <div class="font-medium text-slate-800 dark:text-slate-200 mt-0.5">{{ activeRecord?.pic_name || '-' }}</div>
                        </div>
                        <div>
                            <div class="text-[10px] text-slate-400 uppercase font-semibold">Tanggal Entri</div>
                            <div class="font-medium text-slate-800 dark:text-slate-200 mt-0.5">{{ formatDate(activeRecord?.entry_date) }}</div>
                        </div>
                        <div>
                            <div class="text-[10px] text-slate-400 uppercase font-semibold">Nilai Nominal</div>
                            <div class="font-mono font-bold text-slate-900 dark:text-white mt-0.5">{{ formatCurrency(activeRecord?.amount) }}</div>
                        </div>
                    </div>

                    <div>
                        <div class="text-[11px] font-medium text-slate-400 uppercase tracking-wider mb-1">Deskripsi & Catatan</div>
                        <div class="p-3 rounded-xl bg-slate-50 dark:bg-slate-800/30 border border-slate-100 dark:border-slate-800 text-slate-700 dark:text-slate-300 leading-relaxed whitespace-pre-wrap">
                            {{ activeRecord?.description || 'Tidak ada catatan tambahan untuk entri ini.' }}
                        </div>
                    </div>

                    <div v-if="activeRecord?.custom_attributes && Object.keys(activeRecord.custom_attributes).length > 0">
                        <div class="text-[11px] font-medium text-slate-400 uppercase tracking-wider mb-1">Custom Attributes (Developer Mode)</div>
                        <pre class="p-3 rounded-xl bg-slate-950 text-slate-300 font-mono text-[10px] overflow-x-auto">{{ JSON.stringify(activeRecord.custom_attributes, null, 2) }}</pre>
                    </div>

                    <div class="text-[11px] text-slate-400 pt-3 border-t border-slate-100 dark:border-slate-800 flex items-center justify-between">
                        <span>Dibuat: {{ formatDate(activeRecord?.created_at) }}</span>
                        <span>Diperbarui: {{ formatDate(activeRecord?.updated_at) }}</span>
                    </div>
                </div>

                <div class="flex items-center justify-end gap-2 pt-4 border-t border-slate-100 dark:border-slate-800 mt-4">
                    <button
                        type="button"
                        @click="showDetailModal = false; openEditModal(activeRecord)"
                        class="px-3.5 py-1.5 text-xs font-semibold rounded-xl text-white bg-blue-600 hover:bg-blue-700 cursor-pointer"
                    >
                        Edit Data
                    </button>
                    <button
                        type="button"
                        @click="showDetailModal = false"
                        class="px-3.5 py-1.5 text-xs font-semibold rounded-xl text-slate-700 dark:text-slate-300 bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 cursor-pointer"
                    >
                        Tutup
                    </button>
                </div>
            </div>
        </div>

        <!-- DELETE MODAL -->
        <div v-if="showDeleteModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-xs">
            <div class="bg-white dark:bg-slate-900 rounded-2xl max-w-sm w-full p-6 shadow-2xl border border-slate-200 dark:border-slate-800 text-center">
                <div class="w-12 h-12 rounded-full bg-rose-100 dark:bg-rose-950 text-rose-600 dark:text-rose-400 flex items-center justify-center mx-auto mb-3">
                    <AlertTriangle class="w-6 h-6" />
                </div>
                <h3 class="font-bold text-slate-900 dark:text-white text-base">Konfirmasi Hapus Data</h3>
                <p class="text-xs text-slate-500 dark:text-slate-400 mt-2">
                    Apakah Anda yakin ingin menghapus data dengan kode <span class="font-semibold text-slate-900 dark:text-white font-mono">{{ activeRecord?.record_code }}</span>? Tindakan ini dapat dipulihkan melalui audit log.
                </p>
                <div class="flex items-center justify-center gap-2 mt-5">
                    <button
                        type="button"
                        @click="showDeleteModal = false"
                        class="px-4 py-2 text-xs font-semibold rounded-xl text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800"
                    >
                        Batal
                    </button>
                    <button
                        type="button"
                        @click="executeDelete"
                        class="px-4 py-2 text-xs font-semibold rounded-xl text-white bg-rose-600 hover:bg-rose-700 shadow-xs cursor-pointer"
                    >
                        Ya, Hapus Data
                    </button>
                </div>
            </div>
        </div>
    </ModuleLayout>
</template>
