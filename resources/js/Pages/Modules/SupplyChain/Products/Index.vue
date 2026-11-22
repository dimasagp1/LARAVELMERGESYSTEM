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
    Package,
    Boxes,
    Layers,
    FileSpreadsheet,
    AlertCircle,
    X,
    CheckCircle2,
    XCircle,
    Tag,
} from 'lucide-vue-next';

const props = defineProps({
    module: {
        type: Object,
        required: true,
    },
    products: {
        type: Object,
        required: true,
    },
    uoms: {
        type: Array,
        default: () => [],
    },
    summary: {
        type: Object,
        default: () => ({
            total: 0,
            rm: 0,
            pm: 0,
            fg: 0,
            bulk: 0,
        }),
    },
    filters: {
        type: Object,
        default: () => ({
            search: '',
            type: 'all',
            status: 'all',
        }),
    },
});

const searchQuery = ref(props.filters.search || '');
const selectedType = ref(props.filters.type || 'all');
const selectedStatus = ref(props.filters.status || 'all');

const isModalOpen = ref(false);
const isEditing = ref(false);
const editingId = ref(null);

const form = useForm({
    sku: '',
    name: '',
    type: 'fg',
    category: '',
    uom_id: props.uoms[0]?.id || '',
    secondary_uom_id: '',
    conversion_rate: 1.0,
    min_stock: 0,
    safety_stock: 0,
    reorder_point: 0,
    standard_cost: 0,
    selling_price: 0,
    description: '',
    is_active: true,
});

const applyFilters = () => {
    router.get('/supply-chain/products', {
        search: searchQuery.value,
        type: selectedType.value,
        status: selectedStatus.value,
    }, {
        preserveState: true,
        preserveScroll: true,
    });
};

const openCreateModal = () => {
    isEditing.value = false;
    editingId.value = null;
    form.reset();
    form.clearErrors();
    form.type = 'fg';
    form.uom_id = props.uoms[0]?.id || '';
    form.conversion_rate = 1.0;
    form.is_active = true;
    isModalOpen.value = true;
};

const openEditModal = (product) => {
    isEditing.value = true;
    editingId.value = product.id;
    form.clearErrors();
    form.sku = product.sku;
    form.name = product.name;
    form.type = product.type;
    form.category = product.category || '';
    form.uom_id = product.uom_id;
    form.secondary_uom_id = product.secondary_uom_id || '';
    form.conversion_rate = product.conversion_rate;
    form.min_stock = product.min_stock;
    form.safety_stock = product.safety_stock;
    form.reorder_point = product.reorder_point;
    form.standard_cost = product.standard_cost;
    form.selling_price = product.selling_price;
    form.description = product.description || '';
    form.is_active = product.is_active;
    isModalOpen.value = true;
};

const closeModal = () => {
    isModalOpen.value = false;
    form.reset();
};

const submitForm = () => {
    if (isEditing.value) {
        form.put(`/supply-chain/products/${editingId.value}`, {
            onSuccess: () => closeModal(),
        });
    } else {
        form.post('/supply-chain/products', {
            onSuccess: () => closeModal(),
        });
    }
};

const deleteProduct = (product) => {
    if (confirm(`Yakin ingin menghapus produk ${product.sku} - ${product.name}?`)) {
        router.delete(`/supply-chain/products/${product.id}`, {
            preserveScroll: true,
        });
    }
};

const formatCurrency = (val) => {
    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        maximumFractionDigits: 0,
    }).format(val || 0);
};

const typeLabels = {
    fg: { label: 'Finished Goods (FG)', color: 'bg-emerald-50 text-emerald-700 dark:bg-emerald-950/50 dark:text-emerald-300 border-emerald-200 dark:border-emerald-800' },
    rm: { label: 'Raw Material (RM)', color: 'bg-blue-50 text-blue-700 dark:bg-blue-950/50 dark:text-blue-300 border-blue-200 dark:border-blue-800' },
    pm: { label: 'Packaging (PM)', color: 'bg-amber-50 text-amber-700 dark:bg-amber-950/50 dark:text-amber-300 border-amber-200 dark:border-amber-800' },
    bulk: { label: 'Massa Ruahan (Bulk)', color: 'bg-purple-50 text-purple-700 dark:bg-purple-950/50 dark:text-purple-300 border-purple-200 dark:border-purple-800' },
    wip: { label: 'WIP (Setengah Jadi)', color: 'bg-indigo-50 text-indigo-700 dark:bg-indigo-950/50 dark:text-indigo-300 border-indigo-200 dark:border-indigo-800' },
    sparepart: { label: 'Suku Cadang Mesin', color: 'bg-slate-100 text-slate-700 dark:bg-slate-800 dark:text-slate-300 border-slate-200 dark:border-slate-700' },
    atk: { label: 'ATK / Non-Produksi', color: 'bg-rose-50 text-rose-700 dark:bg-rose-950/50 dark:text-rose-300 border-rose-200 dark:border-rose-800' },
};
</script>

<template>
    <Head title="Master Produk (RM, PM, Bulk, FG) - SCM" />

    <ModuleLayout :module="module" :breadcrumbs="['Master Data', 'Master Produk']">
        <div class="space-y-6">
            <!-- Header Section -->
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 bg-white dark:bg-slate-900 p-6 rounded-2xl border border-slate-200 dark:border-slate-800 shadow-xs">
                <div>
                    <div class="flex items-center gap-2">
                        <span class="px-2.5 py-0.5 rounded-md text-xs font-bold uppercase tracking-wider bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 border border-emerald-500/20">
                            Shared Kernel Master Data
                        </span>
                        <span class="text-xs text-slate-400">| SCM, Manufaktur & R&D</span>
                    </div>
                    <h1 class="text-2xl font-bold text-slate-900 dark:text-white mt-1">
                        Master Produk & Material
                    </h1>
                    <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">
                        Basis data sentral Bahan Baku (RM), Bahan Kemas (PM), Produk Jadi (FG), Safety Stock, dan Parameter Reorder Point.
                    </p>
                </div>

                <div class="flex items-center gap-2.5">
                    <a
                        href="/supply-chain/products/export"
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
                        <span>Tambah Produk Baru</span>
                    </button>
                </div>
            </div>

            <!-- KPI Summary Cards -->
            <div class="grid grid-cols-2 lg:grid-cols-5 gap-4">
                <div class="bg-white dark:bg-slate-900 p-4 rounded-xl border border-slate-200 dark:border-slate-800 shadow-2xs">
                    <div class="flex items-center justify-between text-xs text-slate-500">
                        <span>Total SKU</span>
                        <Package class="w-4 h-4 text-emerald-500" />
                    </div>
                    <div class="mt-2 text-xl font-bold text-slate-900 dark:text-white">
                        {{ summary.total }}
                    </div>
                    <div class="text-[11px] text-emerald-600 font-medium mt-0.5">Seluruh Jenis Item</div>
                </div>

                <div class="bg-white dark:bg-slate-900 p-4 rounded-xl border border-slate-200 dark:border-slate-800 shadow-2xs">
                    <div class="flex items-center justify-between text-xs text-slate-500">
                        <span>Bahan Baku (RM)</span>
                        <Boxes class="w-4 h-4 text-blue-500" />
                    </div>
                    <div class="mt-2 text-xl font-bold text-blue-600 dark:text-blue-400">
                        {{ summary.rm }}
                    </div>
                    <div class="text-[11px] text-slate-400 mt-0.5">Ekstrak & Simplisia</div>
                </div>

                <div class="bg-white dark:bg-slate-900 p-4 rounded-xl border border-slate-200 dark:border-slate-800 shadow-2xs">
                    <div class="flex items-center justify-between text-xs text-slate-500">
                        <span>Bahan Kemas (PM)</span>
                        <Layers class="w-4 h-4 text-amber-500" />
                    </div>
                    <div class="mt-2 text-xl font-bold text-amber-600 dark:text-amber-400">
                        {{ summary.pm }}
                    </div>
                    <div class="text-[11px] text-slate-400 mt-0.5">Primer & Sekunder</div>
                </div>

                <div class="bg-white dark:bg-slate-900 p-4 rounded-xl border border-slate-200 dark:border-slate-800 shadow-2xs">
                    <div class="flex items-center justify-between text-xs text-slate-500">
                        <span>Produk Jadi (FG)</span>
                        <FileSpreadsheet class="w-4 h-4 text-emerald-500" />
                    </div>
                    <div class="mt-2 text-xl font-bold text-emerald-600 dark:text-emerald-400">
                        {{ summary.fg }}
                    </div>
                    <div class="text-[11px] text-slate-400 mt-0.5">Komersial Siap Kirim</div>
                </div>

                <div class="bg-white dark:bg-slate-900 p-4 rounded-xl border border-slate-200 dark:border-slate-800 shadow-2xs">
                    <div class="flex items-center justify-between text-xs text-slate-500">
                        <span>Massa Ruahan (Bulk)</span>
                        <Tag class="w-4 h-4 text-purple-500" />
                    </div>
                    <div class="mt-2 text-xl font-bold text-purple-600 dark:text-purple-400">
                        {{ summary.bulk }}
                    </div>
                    <div class="text-[11px] text-slate-400 mt-0.5">Campuran Pasca Mixing</div>
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
                            placeholder="Cari SKU, nama produk, kategori..."
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

                <div class="flex items-center gap-2 overflow-x-auto pb-1 sm:pb-0">
                    <button
                        v-for="t in [
                            { key: 'all', label: 'Semua' },
                            { key: 'fg', label: 'Finished Goods' },
                            { key: 'rm', label: 'Raw Material' },
                            { key: 'pm', label: 'Packaging' },
                            { key: 'bulk', label: 'Bulk / Massa' },
                        ]"
                        :key="t.key"
                        @click="selectedType = t.key; applyFilters()"
                        :class="[
                            'px-2.5 py-1.5 rounded-lg text-xs font-medium transition shrink-0',
                            selectedType === t.key
                                ? 'bg-emerald-600 text-white font-semibold'
                                : 'bg-slate-50 dark:bg-slate-800 text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-700',
                        ]"
                    >
                        {{ t.label }}
                    </button>
                </div>
            </div>

            <!-- Table of Products -->
            <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 shadow-xs overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse text-xs">
                        <thead>
                            <tr class="bg-slate-50 dark:bg-slate-800/80 border-b border-slate-200 dark:border-slate-700 text-slate-500 uppercase tracking-wider text-[10px]">
                                <th class="py-3 px-4 font-semibold">SKU / Kode</th>
                                <th class="py-3 px-4 font-semibold">Nama Produk</th>
                                <th class="py-3 px-3 font-semibold">Tipe</th>
                                <th class="py-3 px-3 font-semibold">Kategori</th>
                                <th class="py-3 px-3 font-semibold">Satuan (UOM)</th>
                                <th class="py-3 px-3 text-right font-semibold">Safety Stock</th>
                                <th class="py-3 px-3 text-right font-semibold">Reorder Point</th>
                                <th class="py-3 px-4 text-right font-semibold">Biaya Standar</th>
                                <th class="py-3 px-3 text-center font-semibold">Status</th>
                                <th class="py-3 px-4 text-center font-semibold">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                            <tr
                                v-for="p in products.data"
                                :key="p.id"
                                class="transition hover:bg-slate-50/60 dark:hover:bg-slate-800/40"
                            >
                                <td class="py-3 px-4 font-mono font-bold text-slate-900 dark:text-white whitespace-nowrap">
                                    {{ p.sku }}
                                </td>

                                <td class="py-3 px-4">
                                    <div class="font-semibold text-slate-800 dark:text-slate-200">
                                        {{ p.name }}
                                    </div>
                                    <div v-if="p.description" class="text-[10px] text-slate-400 truncate max-w-xs">
                                        {{ p.description }}
                                    </div>
                                </td>

                                <td class="py-3 px-3 whitespace-nowrap">
                                    <span :class="['px-2 py-0.5 rounded-md text-[10px] font-bold border', typeLabels[p.type]?.color || '']">
                                        {{ typeLabels[p.type]?.label || p.type }}
                                    </span>
                                </td>

                                <td class="py-3 px-3 text-slate-600 dark:text-slate-400 whitespace-nowrap">
                                    {{ p.category || '-' }}
                                </td>

                                <td class="py-3 px-3 text-slate-700 dark:text-slate-300 font-mono whitespace-nowrap">
                                    {{ p.uom?.name }} ({{ p.uom?.code }})
                                </td>

                                <td class="py-3 px-3 text-right font-mono text-slate-700 dark:text-slate-300 whitespace-nowrap">
                                    {{ Number(p.safety_stock).toLocaleString('id-ID') }}
                                </td>

                                <td class="py-3 px-3 text-right font-mono text-slate-700 dark:text-slate-300 whitespace-nowrap">
                                    {{ Number(p.reorder_point).toLocaleString('id-ID') }}
                                </td>

                                <td class="py-3 px-4 text-right font-mono font-semibold text-slate-900 dark:text-white whitespace-nowrap">
                                    {{ formatCurrency(p.standard_cost) }}
                                </td>

                                <td class="py-3 px-3 text-center whitespace-nowrap">
                                    <span
                                        class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-semibold"
                                        :class="p.is_active ? 'bg-emerald-50 text-emerald-700 dark:bg-emerald-950/60 dark:text-emerald-300' : 'bg-slate-100 text-slate-400 dark:bg-slate-800'"
                                    >
                                        <CheckCircle2 v-if="p.is_active" class="w-3 h-3 text-emerald-500" />
                                        <XCircle v-else class="w-3 h-3 text-slate-400" />
                                        <span>{{ p.is_active ? 'Aktif' : 'Nonaktif' }}</span>
                                    </span>
                                </td>

                                <td class="py-3 px-4 text-center whitespace-nowrap">
                                    <div class="flex items-center justify-center gap-1.5">
                                        <button
                                            @click="openEditModal(p)"
                                            class="p-1 rounded-md text-slate-500 hover:text-slate-700 dark:hover:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800"
                                            title="Ubah Produk"
                                        >
                                            <Edit2 class="w-3.5 h-3.5" />
                                        </button>

                                        <button
                                            @click="deleteProduct(p)"
                                            class="p-1 rounded-md text-rose-500 hover:bg-rose-50 dark:hover:bg-rose-950/50"
                                            title="Hapus Produk"
                                        >
                                            <Trash2 class="w-3.5 h-3.5" />
                                        </button>
                                    </div>
                                </td>
                            </tr>

                            <tr v-if="products.data.length === 0">
                                <td colspan="10" class="py-8 text-center text-slate-400">
                                    Tidak ada data produk ditemukan.
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div v-if="products.links && products.links.length > 3" class="px-4 py-3 border-t border-slate-100 dark:border-slate-800 flex items-center justify-between">
                    <div class="text-xs text-slate-500">
                        Menampilkan {{ products.from || 0 }} - {{ products.to || 0 }} dari {{ products.total }} item
                    </div>
                    <div class="flex items-center gap-1">
                        <template v-for="(link, idx) in products.links" :key="idx">
                            <button
                                v-if="link.url"
                                @click="router.get(link.url, {}, { preserveState: true, preserveScroll: true })"
                                :class="[
                                    'px-2.5 py-1 text-xs rounded-lg transition',
                                    link.active
                                        ? 'bg-emerald-600 text-white font-bold'
                                        : 'text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800',
                                ]"
                                v-html="link.label"
                            />
                        </template>
                    </div>
                </div>
            </div>
        </div>

        <!-- Create / Edit Modal -->
        <div
            v-if="isModalOpen"
            class="fixed inset-0 z-50 bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4 overflow-y-auto"
            @click.self="closeModal"
        >
            <div class="bg-white dark:bg-slate-900 w-full max-w-xl rounded-2xl border border-slate-200 dark:border-slate-800 shadow-2xl p-6 space-y-4">
                <div class="flex items-center justify-between pb-3 border-b border-slate-100 dark:border-slate-800">
                    <h3 class="font-bold text-slate-900 dark:text-white text-base">
                        {{ isEditing ? 'Ubah Data Produk' : 'Tambah Produk Baru' }}
                    </h3>
                    <button @click="closeModal" class="p-1 rounded-md text-slate-400 hover:text-slate-600">
                        <X class="w-4 h-4" />
                    </button>
                </div>

                <form @submit.prevent="submitForm" class="space-y-4">
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">
                                Kode SKU <span class="text-rose-500">*</span>
                            </label>
                            <input
                                v-model="form.sku"
                                type="text"
                                placeholder="Contoh: RM-EKST-TEMU-01"
                                class="w-full px-3 py-2 text-xs font-mono rounded-lg border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 text-slate-900 dark:text-white focus:outline-none focus:ring-1 focus:ring-emerald-500"
                                required
                            />
                            <div v-if="form.errors.sku" class="text-rose-500 text-[10px] mt-0.5">
                                {{ form.errors.sku }}
                            </div>
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">
                                Tipe Produk <span class="text-rose-500">*</span>
                            </label>
                            <select
                                v-model="form.type"
                                class="w-full px-3 py-2 text-xs rounded-lg border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 text-slate-900 dark:text-white focus:outline-none focus:ring-1 focus:ring-emerald-500"
                                required
                            >
                                <option value="fg">Finished Goods (FG)</option>
                                <option value="rm">Raw Material (RM)</option>
                                <option value="pm">Packaging Material (PM)</option>
                                <option value="bulk">Massa Ruahan (Bulk)</option>
                                <option value="wip">Work in Progress (WIP)</option>
                                <option value="sparepart">Suku Cadang Mesin</option>
                                <option value="atk">ATK Kantor</option>
                            </select>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">
                                Nama Produk <span class="text-rose-500">*</span>
                            </label>
                            <input
                                v-model="form.name"
                                type="text"
                                placeholder="Contoh: Ekstrak Temulawak Curcuma 500mg"
                                class="w-full px-3 py-2 text-xs rounded-lg border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 text-slate-900 dark:text-white focus:outline-none focus:ring-1 focus:ring-emerald-500"
                                required
                            />
                            <div v-if="form.errors.name" class="text-rose-500 text-[10px] mt-0.5">
                                {{ form.errors.name }}
                            </div>
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">
                                Kategori Produk
                            </label>
                            <input
                                v-model="form.category"
                                type="text"
                                placeholder="Contoh: Ekstrak Herbal / Kapsul"
                                class="w-full px-3 py-2 text-xs rounded-lg border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 text-slate-900 dark:text-white focus:outline-none focus:ring-1 focus:ring-emerald-500"
                            />
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">
                                Satuan Utama (UOM) <span class="text-rose-500">*</span>
                            </label>
                            <select
                                v-model="form.uom_id"
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
                                Biaya Standar HPP (IDR)
                            </label>
                            <input
                                v-model="form.standard_cost"
                                type="number"
                                step="any"
                                placeholder="0"
                                class="w-full px-3 py-2 text-xs font-mono rounded-lg border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 text-slate-900 dark:text-white focus:outline-none focus:ring-1 focus:ring-emerald-500"
                            />
                        </div>
                    </div>

                    <!-- Inventory Parameters: Safety Stock, Reorder Point -->
                    <div class="grid grid-cols-3 gap-3 p-3 bg-slate-50 dark:bg-slate-800/50 rounded-xl border border-slate-200 dark:border-slate-700">
                        <div>
                            <label class="block text-[11px] font-semibold text-slate-600 dark:text-slate-300 mb-1">
                                Minimum Stock
                            </label>
                            <input
                                v-model="form.min_stock"
                                type="number"
                                step="any"
                                class="w-full px-2.5 py-1.5 text-xs font-mono rounded border border-slate-200 dark:border-slate-600 bg-white dark:bg-slate-800 text-slate-900 dark:text-white"
                            />
                        </div>

                        <div>
                            <label class="block text-[11px] font-semibold text-slate-600 dark:text-slate-300 mb-1">
                                Safety Stock
                            </label>
                            <input
                                v-model="form.safety_stock"
                                type="number"
                                step="any"
                                class="w-full px-2.5 py-1.5 text-xs font-mono rounded border border-slate-200 dark:border-slate-600 bg-white dark:bg-slate-800 text-slate-900 dark:text-white"
                            />
                        </div>

                        <div>
                            <label class="block text-[11px] font-semibold text-slate-600 dark:text-slate-300 mb-1">
                                Reorder Point (ROP)
                            </label>
                            <input
                                v-model="form.reorder_point"
                                type="number"
                                step="any"
                                class="w-full px-2.5 py-1.5 text-xs font-mono rounded border border-slate-200 dark:border-slate-600 bg-white dark:bg-slate-800 text-slate-900 dark:text-white"
                            />
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">
                            Deskripsi & Spesifikasi
                        </label>
                        <textarea
                            v-model="form.description"
                            rows="2"
                            placeholder="Karakteristik fisik, penyimpanan suhu, atau kegunaan..."
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
                            {{ isEditing ? 'Simpan Perubahan' : 'Buat Produk' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </ModuleLayout>
</template>
