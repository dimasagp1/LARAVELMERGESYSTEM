<script setup>
import { ref } from 'vue';
import { Head, router, useForm } from '@inertiajs/vue3';
import ModuleLayout from '@/Layouts/ModuleLayout.vue';
import {
    Plus,
    Search,
    Download,
    Edit2,
    Trash2,
    FileSpreadsheet,
    Layers,
    CheckCircle2,
    Clock,
    X,
    ChevronDown,
    ChevronUp,
    Boxes,
    Cpu,
    Sparkles,
} from 'lucide-vue-next';

const props = defineProps({
    module: {
        type: Object,
        required: true,
    },
    boms: {
        type: Object,
        required: true,
    },
    fgProducts: {
        type: Array,
        default: () => [],
    },
    materialProducts: {
        type: Array,
        default: () => [],
    },
    uoms: {
        type: Array,
        default: () => [],
    },
    summary: {
        type: Object,
        default: () => ({
            total: 0,
            approved: 0,
            draft: 0,
            active: 0,
        }),
    },
    filters: {
        type: Object,
        default: () => ({
            search: '',
            status: 'all',
        }),
    },
});

const searchQuery = ref(props.filters.search || '');
const selectedStatus = ref(props.filters.status || 'all');
const expandedBomId = ref(null);

const isModalOpen = ref(false);
const isEditing = ref(false);
const editingId = ref(null);

const form = useForm({
    product_id: props.fgProducts[0]?.id || '',
    version: '1.0',
    batch_size: 1000,
    batch_uom_id: props.uoms[0]?.id || '',
    status: 'approved',
    notes: '',
    items: [
        {
            product_id: props.materialProducts[0]?.id || '',
            quantity: 100,
            uom_id: props.uoms[0]?.id || '',
            percentage: 10,
            wastage_percentage: 1,
            stage: 'mixing',
            instructions: '',
        },
    ],
});

const toggleExpand = (id) => {
    expandedBomId.value = expandedBomId.value === id ? null : id;
};

const applyFilters = () => {
    router.get('/supply-chain/boms', {
        search: searchQuery.value,
        status: selectedStatus.value,
    }, {
        preserveState: true,
        preserveScroll: true,
    });
};

const addItemRow = () => {
    form.items.push({
        product_id: props.materialProducts[0]?.id || '',
        quantity: 10,
        uom_id: props.uoms[0]?.id || '',
        percentage: 0,
        wastage_percentage: 0,
        stage: 'mixing',
        instructions: '',
    });
};

const removeItemRow = (idx) => {
    if (form.items.length > 1) {
        form.items.splice(idx, 1);
    }
};

const openCreateModal = () => {
    isEditing.value = false;
    editingId.value = null;
    form.reset();
    form.clearErrors();
    form.product_id = props.fgProducts[0]?.id || '';
    form.version = '1.0';
    form.batch_size = 1000;
    form.batch_uom_id = props.uoms[0]?.id || '';
    form.status = 'approved';
    form.items = [
        {
            product_id: props.materialProducts[0]?.id || '',
            quantity: 100,
            uom_id: props.uoms[0]?.id || '',
            percentage: 10,
            wastage_percentage: 1,
            stage: 'mixing',
            instructions: '',
        },
    ];
    isModalOpen.value = true;
};

const openEditModal = (bom) => {
    isEditing.value = true;
    editingId.value = bom.id;
    form.clearErrors();
    form.product_id = bom.product_id;
    form.version = bom.version;
    form.batch_size = bom.batch_size;
    form.batch_uom_id = bom.batch_uom_id;
    form.status = bom.status;
    form.notes = bom.notes || '';
    form.items = (bom.items || []).map(i => ({
        product_id: i.product_id,
        quantity: i.quantity,
        uom_id: i.uom_id,
        percentage: i.percentage || 0,
        wastage_percentage: i.wastage_percentage || 0,
        stage: i.stage || 'mixing',
        instructions: i.instructions || '',
    }));
    isModalOpen.value = true;
};

const closeModal = () => {
    isModalOpen.value = false;
    form.reset();
};

const submitForm = () => {
    if (isEditing.value) {
        form.put(`/supply-chain/boms/${editingId.value}`, {
            onSuccess: () => closeModal(),
        });
    } else {
        form.post('/supply-chain/boms', {
            onSuccess: () => closeModal(),
        });
    }
};

const deleteBom = (bom) => {
    if (confirm(`Yakin ingin menghapus resep ${bom.bom_number}?`)) {
        router.delete(`/supply-chain/boms/${bom.id}`, {
            preserveScroll: true,
        });
    }
};

const statusBadges = {
    approved: { label: 'Disetujui QA', color: 'bg-emerald-50 text-emerald-700 dark:bg-emerald-950/60 dark:text-emerald-300 border-emerald-200 dark:border-emerald-800' },
    draft: { label: 'Draft Resep', color: 'bg-amber-50 text-amber-700 dark:bg-amber-950/60 dark:text-amber-300 border-amber-200 dark:border-amber-800' },
    pending_qa: { label: 'Tinjauan QA', color: 'bg-blue-50 text-blue-700 dark:bg-blue-950/60 dark:text-blue-300 border-blue-200 dark:border-blue-800' },
    obsolete: { label: 'Usang (Obsolete)', color: 'bg-slate-100 text-slate-500 dark:bg-slate-800 border-slate-200' },
};
</script>

<template>
    <Head title="Master Bill of Materials (BOM) - SCM" />

    <ModuleLayout :module="module" :breadcrumbs="['Master Data', 'Bill of Materials (BOM)']">
        <div class="space-y-6">
            <!-- Header Section -->
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 bg-white dark:bg-slate-900 p-6 rounded-2xl border border-slate-200 dark:border-slate-800 shadow-xs">
                <div>
                    <div class="flex items-center gap-2">
                        <span class="px-2.5 py-0.5 rounded-md text-xs font-bold uppercase tracking-wider bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 border border-emerald-500/20">
                            Resep & Formula Produksi
                        </span>
                        <span class="text-xs text-slate-400">| Terintegrasi MRP & Shop Floor</span>
                    </div>
                    <h1 class="text-2xl font-bold text-slate-900 dark:text-white mt-1">
                        Master Bill of Materials (BOM)
                    </h1>
                    <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">
                        Kelola formulasi multi-level komponen RM/PM, rasio batch size, toleransi susut (wastage), dan tahapan proses estafet.
                    </p>
                </div>

                <div class="flex items-center gap-2.5">
                    <a
                        href="/supply-chain/boms/export"
                        class="flex items-center gap-1.5 px-3 py-2 rounded-xl text-xs font-semibold text-slate-600 dark:text-slate-300 bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 transition"
                    >
                        <Download class="w-4 h-4" />
                        <span>Ekspor CSV</span>
                    </a>

                    <button
                        @click="openCreateModal()"
                        class="flex items-center gap-1.5 px-4 py-2 rounded-xl text-xs font-semibold text-white bg-emerald-600 hover:bg-emerald-700 shadow-xs transition"
                    >
                        <Plus class="w-4 h-4" />
                        <span>Buat BOM Baru</span>
                    </button>
                </div>
            </div>

            <!-- KPI Cards -->
            <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
                <div class="bg-white dark:bg-slate-900 p-4 rounded-xl border border-slate-200 dark:border-slate-800 shadow-2xs">
                    <div class="flex items-center justify-between text-xs text-slate-500">
                        <span>Total Master BOM</span>
                        <FileSpreadsheet class="w-4 h-4 text-emerald-500" />
                    </div>
                    <div class="mt-2 text-2xl font-bold text-slate-900 dark:text-white">
                        {{ summary.total }}
                    </div>
                    <div class="text-[11px] text-emerald-600 font-medium mt-0.5">Versi Aktif & Arsip</div>
                </div>

                <div class="bg-white dark:bg-slate-900 p-4 rounded-xl border border-slate-200 dark:border-slate-800 shadow-2xs">
                    <div class="flex items-center justify-between text-xs text-slate-500">
                        <span>BOM Disetujui (Approved)</span>
                        <CheckCircle2 class="w-4 h-4 text-emerald-500" />
                    </div>
                    <div class="mt-2 text-2xl font-bold text-emerald-600 dark:text-emerald-400">
                        {{ summary.approved }}
                    </div>
                    <div class="text-[11px] text-slate-400 mt-0.5">Siap Dieksekusi Pabrik</div>
                </div>

                <div class="bg-white dark:bg-slate-900 p-4 rounded-xl border border-slate-200 dark:border-slate-800 shadow-2xs">
                    <div class="flex items-center justify-between text-xs text-slate-500">
                        <span>Draf Resep</span>
                        <Clock class="w-4 h-4 text-amber-500" />
                    </div>
                    <div class="mt-2 text-2xl font-bold text-amber-600 dark:text-amber-400">
                        {{ summary.draft }}
                    </div>
                    <div class="text-[11px] text-slate-400 mt-0.5">Dalam Penyusunan</div>
                </div>

                <div class="bg-white dark:bg-slate-900 p-4 rounded-xl border border-slate-200 dark:border-slate-800 shadow-2xs">
                    <div class="flex items-center justify-between text-xs text-slate-500">
                        <span>Kategori Aktif</span>
                        <Boxes class="w-4 h-4 text-purple-500" />
                    </div>
                    <div class="mt-2 text-2xl font-bold text-purple-600 dark:text-purple-400">
                        {{ summary.active }}
                    </div>
                    <div class="text-[11px] text-slate-400 mt-0.5">Rilis Terkini</div>
                </div>
            </div>

            <!-- Toolbar & Filter -->
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 bg-white dark:bg-slate-900 p-3 rounded-xl border border-slate-200 dark:border-slate-800">
                <div class="flex items-center gap-2 flex-1 max-w-md">
                    <div class="relative w-full">
                        <Search class="w-4 h-4 absolute left-3 top-3 text-slate-400" />
                        <input
                            v-model="searchQuery"
                            type="text"
                            placeholder="Cari nomor BOM atau nama produk..."
                            class="w-full pl-9 pr-3 py-2 text-xs rounded-lg border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 text-slate-900 dark:text-white focus:outline-none focus:ring-1 focus:ring-emerald-500"
                            @keyup.enter="applyFilters"
                        />
                    </div>
                    <button
                        @click="applyFilters"
                        class="px-3 py-2 text-xs font-semibold rounded-lg bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 hover:bg-slate-200 transition"
                    >
                        Cari
                    </button>
                </div>

                <div class="flex items-center gap-2">
                    <button
                        v-for="s in [
                            { key: 'all', label: 'Semua Status' },
                            { key: 'approved', label: 'Approved' },
                            { key: 'draft', label: 'Draft' },
                        ]"
                        :key="s.key"
                        @click="selectedStatus = s.key; applyFilters()"
                        :class="[
                            'px-2.5 py-1.5 rounded-lg text-xs font-medium transition',
                            selectedStatus === s.key
                                ? 'bg-emerald-600 text-white font-semibold'
                                : 'bg-slate-50 dark:bg-slate-800 text-slate-600 dark:text-slate-300 hover:bg-slate-100',
                        ]"
                    >
                        {{ s.label }}
                    </button>
                </div>
            </div>

            <!-- BOM List Accordion Cards -->
            <div class="space-y-3">
                <div
                    v-for="b in boms.data"
                    :key="b.id"
                    class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 shadow-2xs overflow-hidden transition"
                >
                    <div class="p-4 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                        <div class="flex items-start gap-3">
                            <div class="p-2.5 rounded-xl bg-emerald-50 dark:bg-emerald-950/50 text-emerald-600 dark:text-emerald-400 mt-0.5">
                                <FileSpreadsheet class="w-5 h-5" />
                            </div>
                            <div>
                                <div class="flex items-center gap-2">
                                    <span class="font-mono font-bold text-slate-900 dark:text-white text-sm">
                                        {{ b.bom_number }}
                                    </span>
                                    <span class="px-2 py-0.2 rounded font-mono text-[10px] font-bold bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300">
                                        v{{ b.version }}
                                    </span>
                                    <span :class="['px-2 py-0.5 rounded-md text-[10px] font-bold border', statusBadges[b.status]?.color || '']">
                                        {{ statusBadges[b.status]?.label || b.status }}
                                    </span>
                                </div>
                                <div class="font-semibold text-slate-800 dark:text-slate-200 text-xs mt-0.5">
                                    {{ b.product?.name }} <span class="text-slate-400 font-mono">({{ b.product?.sku }})</span>
                                </div>
                                <div class="text-[11px] text-slate-500 mt-1 flex items-center gap-3">
                                    <span>Batch Size: <strong>{{ Number(b.batch_size).toLocaleString('id-ID') }} {{ b.batch_uom?.code }}</strong></span>
                                    <span>Total Komponen: <strong>{{ b.items?.length || 0 }} item</strong></span>
                                    <span v-if="b.notes" class="text-slate-400 italic">"{{ b.notes }}"</span>
                                </div>
                            </div>
                        </div>

                        <div class="flex items-center gap-2 self-end sm:self-center">
                            <button
                                @click="toggleExpand(b.id)"
                                class="flex items-center gap-1 px-3 py-1.5 rounded-xl text-xs font-semibold text-slate-600 dark:text-slate-300 bg-slate-50 dark:bg-slate-800 hover:bg-slate-100 transition"
                            >
                                <span>{{ expandedBomId === b.id ? 'Tutup Komponen' : 'Lihat Komponen' }}</span>
                                <ChevronUp v-if="expandedBomId === b.id" class="w-3.5 h-3.5" />
                                <ChevronDown v-else class="w-3.5 h-3.5" />
                            </button>

                            <button
                                @click="openEditModal(b)"
                                class="p-1.5 rounded-xl text-slate-500 hover:text-slate-700 dark:hover:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800"
                                title="Ubah BOM"
                            >
                                <Edit2 class="w-4 h-4" />
                            </button>

                            <button
                                @click="deleteBom(b)"
                                class="p-1.5 rounded-xl text-rose-500 hover:bg-rose-50 dark:hover:bg-rose-950/50"
                                title="Hapus BOM"
                            >
                                <Trash2 class="w-4 h-4" />
                            </button>
                        </div>
                    </div>

                    <!-- Expandable Recipe Component Table -->
                    <div v-if="expandedBomId === b.id" class="px-4 pb-4 pt-1 border-t border-slate-100 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-800/30">
                        <div class="text-[11px] font-bold uppercase tracking-wider text-slate-400 mb-2">
                            Rincian Resep Bahan (Bill of Materials)
                        </div>
                        <table class="w-full text-left text-xs border-collapse">
                            <thead>
                                <tr class="text-[10px] text-slate-400 uppercase border-b border-slate-200 dark:border-slate-700">
                                    <th class="py-1.5 px-2">No</th>
                                    <th class="py-1.5 px-2">Kode SKU</th>
                                    <th class="py-1.5 px-2">Nama Bahan Baku / Kemas</th>
                                    <th class="py-1.5 px-2">Tahap Proses</th>
                                    <th class="py-1.5 px-2 text-right">Kebutuhan</th>
                                    <th class="py-1.5 px-2 text-right">Persentase</th>
                                    <th class="py-1.5 px-2 text-right">Susut (Wastage)</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100 dark:divide-slate-800/60">
                                <tr v-for="(item, idx) in b.items" :key="item.id" class="hover:bg-white/60 dark:hover:bg-slate-800/60">
                                    <td class="py-2 px-2 text-slate-400 font-mono">{{ idx + 1 }}</td>
                                    <td class="py-2 px-2 font-mono font-bold text-slate-800 dark:text-slate-200">{{ item.product?.sku }}</td>
                                    <td class="py-2 px-2 font-medium text-slate-700 dark:text-slate-300">{{ item.product?.name }}</td>
                                    <td class="py-2 px-2 whitespace-nowrap">
                                        <span class="px-2 py-0.5 rounded text-[10px] uppercase font-bold bg-slate-200/80 dark:bg-slate-700 text-slate-700 dark:text-slate-300">
                                            {{ item.stage }}
                                        </span>
                                    </td>
                                    <td class="py-2 px-2 text-right font-mono font-bold text-slate-900 dark:text-white">
                                        {{ Number(item.quantity).toLocaleString('id-ID') }} {{ item.uom?.code }}
                                    </td>
                                    <td class="py-2 px-2 text-right font-mono text-slate-600 dark:text-slate-400">
                                        {{ item.percentage ? Number(item.percentage).toFixed(2) + '%' : '-' }}
                                    </td>
                                    <td class="py-2 px-2 text-right font-mono text-slate-500">
                                        {{ Number(item.wastage_percentage).toFixed(1) }}%
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <div v-if="boms.data.length === 0" class="p-8 text-center text-slate-400 bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800">
                    Belum ada Master BOM yang terdaftar. Silakan buat resep pertama Anda.
                </div>
            </div>
        </div>

        <!-- Create / Edit Modal -->
        <div
            v-if="isModalOpen"
            class="fixed inset-0 z-50 bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4 overflow-y-auto"
            @click.self="closeModal"
        >
            <div class="bg-white dark:bg-slate-900 w-full max-w-3xl rounded-2xl border border-slate-200 dark:border-slate-800 shadow-2xl p-6 space-y-4 max-h-[90vh] overflow-y-auto">
                <div class="flex items-center justify-between pb-3 border-b border-slate-100 dark:border-slate-800">
                    <h3 class="font-bold text-slate-900 dark:text-white text-base">
                        {{ isEditing ? 'Ubah Master BOM' : 'Buat Master BOM Baru' }}
                    </h3>
                    <button @click="closeModal" class="p-1 rounded-md text-slate-400 hover:text-slate-600">
                        <X class="w-4 h-4" />
                    </button>
                </div>

                <form @submit.prevent="submitForm" class="space-y-4">
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                        <div class="sm:col-span-2">
                            <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">
                                Target Produk Jadi (FG / Bulk) <span class="text-rose-500">*</span>
                            </label>
                            <select
                                v-model="form.product_id"
                                class="w-full px-3 py-2 text-xs rounded-lg border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 text-slate-900 dark:text-white focus:outline-none focus:ring-1 focus:ring-emerald-500"
                                required
                            >
                                <option v-for="p in fgProducts" :key="p.id" :value="p.id">
                                    {{ p.sku }} - {{ p.name }}
                                </option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">
                                Versi BOM <span class="text-rose-500">*</span>
                            </label>
                            <input
                                v-model="form.version"
                                type="text"
                                placeholder="Contoh: 1.0"
                                class="w-full px-3 py-2 text-xs font-mono rounded-lg border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 text-slate-900 dark:text-white focus:outline-none focus:ring-1 focus:ring-emerald-500"
                                required
                            />
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">
                                Batch Size Standar <span class="text-rose-500">*</span>
                            </label>
                            <input
                                v-model="form.batch_size"
                                type="number"
                                step="any"
                                class="w-full px-3 py-2 text-xs font-mono rounded-lg border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 text-slate-900 dark:text-white focus:outline-none focus:ring-1 focus:ring-emerald-500"
                                required
                            />
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">
                                Satuan Batch <span class="text-rose-500">*</span>
                            </label>
                            <select
                                v-model="form.batch_uom_id"
                                class="w-full px-3 py-2 text-xs rounded-lg border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 text-slate-900 dark:text-white focus:outline-none focus:ring-1 focus:ring-emerald-500"
                                required
                            >
                                <option v-for="u in uoms" :key="u.id" :value="u.id">
                                    {{ u.name }} ({{ u.code }})
                                </option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">
                                Status Resep <span class="text-rose-500">*</span>
                            </label>
                            <select
                                v-model="form.status"
                                class="w-full px-3 py-2 text-xs rounded-lg border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 text-slate-900 dark:text-white focus:outline-none focus:ring-1 focus:ring-emerald-500"
                                required
                            >
                                <option value="approved">Approved (Resmi)</option>
                                <option value="draft">Draft (Konsep)</option>
                                <option value="pending_qa">Tinjauan QA</option>
                                <option value="obsolete">Obsolete</option>
                            </select>
                        </div>
                    </div>

                    <!-- Dynamic Component Table -->
                    <div class="space-y-2 pt-2">
                        <div class="flex items-center justify-between">
                            <label class="text-xs font-bold text-slate-800 dark:text-slate-200 uppercase tracking-wider">
                                Komponen Resep Bahan (RM & PM) <span class="text-rose-500">*</span>
                            </label>
                            <button
                                type="button"
                                @click="addItemRow"
                                class="flex items-center gap-1 px-2.5 py-1 text-xs font-semibold rounded-lg bg-emerald-50 text-emerald-700 dark:bg-emerald-950/60 dark:text-emerald-300 hover:bg-emerald-100 transition"
                            >
                                <Plus class="w-3.5 h-3.5" />
                                <span>Tambah Baris</span>
                            </button>
                        </div>

                        <div class="space-y-2 max-h-60 overflow-y-auto pr-1">
                            <div
                                v-for="(item, idx) in form.items"
                                :key="idx"
                                class="grid grid-cols-12 gap-2 items-center p-2.5 bg-slate-50 dark:bg-slate-800/60 rounded-xl border border-slate-200 dark:border-slate-700 text-xs"
                            >
                                <div class="col-span-5">
                                    <label class="block text-[10px] text-slate-400 mb-0.5">Bahan Baku / Kemas</label>
                                    <select
                                        v-model="item.product_id"
                                        class="w-full p-1.5 text-xs rounded border border-slate-200 dark:border-slate-600 bg-white dark:bg-slate-800 text-slate-900 dark:text-white"
                                        required
                                    >
                                        <option v-for="m in materialProducts" :key="m.id" :value="m.id">
                                            [{{ m.type.toUpperCase() }}] {{ m.name }}
                                        </option>
                                    </select>
                                </div>

                                <div class="col-span-2">
                                    <label class="block text-[10px] text-slate-400 mb-0.5">Jumlah</label>
                                    <input
                                        v-model="item.quantity"
                                        type="number"
                                        step="any"
                                        placeholder="0"
                                        class="w-full p-1.5 text-xs font-mono rounded border border-slate-200 dark:border-slate-600 bg-white dark:bg-slate-800 text-slate-900 dark:text-white"
                                        required
                                    />
                                </div>

                                <div class="col-span-2">
                                    <label class="block text-[10px] text-slate-400 mb-0.5">Satuan</label>
                                    <select
                                        v-model="item.uom_id"
                                        class="w-full p-1.5 text-xs rounded border border-slate-200 dark:border-slate-600 bg-white dark:bg-slate-800 text-slate-900 dark:text-white"
                                        required
                                    >
                                        <option v-for="u in uoms" :key="u.id" :value="u.id">
                                            {{ u.code }}
                                        </option>
                                    </select>
                                </div>

                                <div class="col-span-2">
                                    <label class="block text-[10px] text-slate-400 mb-0.5">Tahap</label>
                                    <select
                                        v-model="item.stage"
                                        class="w-full p-1.5 text-xs rounded border border-slate-200 dark:border-slate-600 bg-white dark:bg-slate-800 text-slate-900 dark:text-white"
                                    >
                                        <option value="mixing">Mixing</option>
                                        <option value="filling">Filling</option>
                                        <option value="packing">Packing</option>
                                    </select>
                                </div>

                                <div class="col-span-1 text-center pt-3">
                                    <button
                                        type="button"
                                        @click="removeItemRow(idx)"
                                        class="p-1 rounded text-rose-500 hover:bg-rose-50"
                                        title="Hapus Baris"
                                    >
                                        <Trash2 class="w-3.5 h-3.5" />
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">
                            Catatan Resep
                        </label>
                        <textarea
                            v-model="form.notes"
                            rows="2"
                            placeholder="Catatan formulasi, instruksi khusus suhu, atau catatan QA..."
                            class="w-full px-3 py-2 text-xs rounded-lg border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 text-slate-900 dark:text-white focus:outline-none focus:ring-1 focus:ring-emerald-500"
                        ></textarea>
                    </div>

                    <div class="flex items-center justify-end gap-2 pt-3 border-t border-slate-100 dark:border-slate-800">
                        <button
                            type="button"
                            @click="closeModal"
                            class="px-4 py-2 text-xs font-semibold rounded-xl text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800 transition"
                        >
                            Batal
                        </button>
                        <button
                            type="submit"
                            :disabled="form.processing"
                            class="px-5 py-2 text-xs font-semibold rounded-xl text-white bg-emerald-600 hover:bg-emerald-700 shadow-xs transition disabled:opacity-50"
                        >
                            {{ isEditing ? 'Simpan Resep' : 'Simpan Master BOM' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </ModuleLayout>
</template>
