<script setup>
import { ref } from 'vue';
import { Head, router } from '@inertiajs/vue3';
import ModuleLayout from '@/Layouts/ModuleLayout.vue';
import {
    Tag,
    Plus,
    RefreshCw,
    Search,
    Building2,
    CheckCircle2,
} from 'lucide-vue-next';

const props = defineProps({
    module: Object,
    prices: Object,
    vendors: Array,
    products: Array,
    effectiveYear: Number,
    filters: Object,
});

const search = ref(props.filters?.search || '');
const categoryType = ref(props.filters?.category_type || '');

const applyFilter = (cat) => {
    categoryType.value = cat;
    router.get('/procurement/standard-prices', {
        search: search.value || undefined,
        category_type: categoryType.value || undefined,
    }, { preserveState: true, replace: true });
};

// Modal Add Standard Price
const modalOpen = ref(false);
const formItemCode = ref('');
const formItemName = ref('');
const formCategory = ref('material');
const formUom = ref('kg');
const formStandardPrice = ref(50000);
const formVendorName = ref('');
const formEffectiveYear = ref(props.effectiveYear);
const modalLoading = ref(false);

const submitPrice = () => {
    modalLoading.value = true;
    router.post('/procurement/standard-prices', {
        item_code: formItemCode.value,
        item_name: formItemName.value,
        category_type: formCategory.value,
        uom: formUom.value,
        standard_price: formStandardPrice.value,
        vendor_name: formVendorName.value,
        effective_year: formEffectiveYear.value,
    }, {
        onSuccess: () => {
            modalOpen.value = false;
            modalLoading.value = false;
        },
        onError: () => { modalLoading.value = false; },
    });
};

const syncMaster = () => {
    router.post('/procurement/standard-prices/sync-master');
};
</script>

<template>
    <Head title="Standar Harga & AVL - ERP" />

    <ModuleLayout :module="module">
        <div class="space-y-6">
            <!-- Header -->
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-6 shadow-xs">
                <div>
                    <h1 class="text-xl font-bold text-slate-900 dark:text-white">
                        Katalog Standar Harga Acuan & AVL
                    </h1>
                    <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">
                        Plafon harga acuan pasar untuk evaluasi deviasi Sasaran Mutu 2026
                    </p>
                </div>
                <div class="flex items-center gap-2">
                    <button
                        @click="syncMaster"
                        class="px-3.5 py-2 rounded-xl border border-slate-300 dark:border-slate-700 hover:bg-slate-100 dark:hover:bg-slate-800 text-xs font-semibold text-slate-700 dark:text-slate-200 inline-flex items-center gap-1.5 transition-colors"
                    >
                        <RefreshCw class="w-3.5 h-3.5" />
                        Sync dari Master Produk
                    </button>
                    <button
                        @click="modalOpen = true"
                        class="px-4 py-2 rounded-xl bg-violet-600 hover:bg-violet-700 text-white text-xs font-semibold inline-flex items-center gap-1.5 shadow-xs transition-colors"
                    >
                        <Plus class="w-4 h-4" />
                        Tambah Standar Harga
                    </button>
                </div>
            </div>

            <!-- Categories Tabs & Filter -->
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div class="flex items-center gap-2 border-b border-slate-200 dark:border-slate-800 pb-1">
                    <button
                        @click="applyFilter('')"
                        :class="['px-3 py-1.5 rounded-lg text-xs font-bold transition-colors', !categoryType ? 'bg-violet-600 text-white' : 'text-slate-600 hover:bg-slate-100 dark:text-slate-400 dark:hover:bg-slate-800']"
                    >
                        Semua Kategori
                    </button>
                    <button
                        @click="applyFilter('material')"
                        :class="['px-3 py-1.5 rounded-lg text-xs font-bold transition-colors', categoryType === 'material' ? 'bg-violet-600 text-white' : 'text-slate-600 hover:bg-slate-100 dark:text-slate-400 dark:hover:bg-slate-800']"
                    >
                        Material (Bahan Baku & Kemas)
                    </button>
                    <button
                        @click="applyFilter('supporting')"
                        :class="['px-3 py-1.5 rounded-lg text-xs font-bold transition-colors', categoryType === 'supporting' ? 'bg-violet-600 text-white' : 'text-slate-600 hover:bg-slate-100 dark:text-slate-400 dark:hover:bg-slate-800']"
                    >
                        Supporting (Penunjang, Sparepart, Sanitasi)
                    </button>
                </div>

                <div class="relative w-72">
                    <Search class="w-4 h-4 text-slate-400 absolute left-3 top-3" />
                    <input
                        v-model="search"
                        @keyup.enter="applyFilter(categoryType)"
                        type="text"
                        placeholder="Cari kode / nama item / vendor..."
                        class="w-full pl-9 pr-3 py-2 text-xs rounded-xl border border-slate-300 dark:border-slate-700 dark:bg-slate-800"
                    />
                </div>
            </div>

            <!-- Prices Table -->
            <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl overflow-hidden shadow-xs">
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="border-b border-slate-200 dark:border-slate-800 text-[11px] font-semibold text-slate-500 uppercase tracking-wider bg-slate-50/50 dark:bg-slate-800/40">
                                <th class="py-3 px-4">Kode Item</th>
                                <th class="py-3 px-4">Nama Barang</th>
                                <th class="py-3 px-4">Kategori</th>
                                <th class="py-3 px-4 text-center">Satuan</th>
                                <th class="py-3 px-4 text-right">Harga Acuan Standar</th>
                                <th class="py-3 px-4">Vendor Terdaftar (AVL)</th>
                                <th class="py-3 px-4">Tahun Efektif</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 dark:divide-slate-800 text-xs">
                            <tr v-for="p in prices.data" :key="p.id" class="hover:bg-slate-50/50 dark:hover:bg-slate-800/30">
                                <td class="py-3 px-4 font-mono font-bold text-violet-600">
                                    {{ p.item_code }}
                                </td>
                                <td class="py-3 px-4 font-semibold text-slate-900 dark:text-white">
                                    {{ p.item_name }}
                                </td>
                                <td class="py-3 px-4">
                                    <span :class="['px-2 py-0.5 rounded text-[10px] font-bold uppercase', p.category_type === 'material' ? 'bg-violet-100 text-violet-800' : 'bg-sky-100 text-sky-800']">
                                        {{ p.category_type }}
                                    </span>
                                </td>
                                <td class="py-3 px-4 text-center font-medium">
                                    {{ p.uom }}
                                </td>
                                <td class="py-3 px-4 text-right font-extrabold text-slate-900 dark:text-white">
                                    Rp {{ Number(p.standard_price).toLocaleString('id-ID') }}
                                </td>
                                <td class="py-3 px-4 font-medium text-slate-700 dark:text-slate-300">
                                    {{ p.vendor?.name || p.vendor_name || 'Vendor Rekanan' }}
                                </td>
                                <td class="py-3 px-4 text-slate-500 font-semibold">
                                    {{ p.effective_year }}
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div v-if="prices.data?.length === 0" class="text-center py-12 text-slate-400 text-xs">
                    Belum ada data standar harga untuk filter ini.
                </div>
            </div>
        </div>

        <!-- Modal Tambah Harga Standar -->
        <div v-if="modalOpen" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-xs">
            <div class="bg-white dark:bg-slate-900 rounded-2xl max-w-md w-full p-6 shadow-2xl border border-slate-200 dark:border-slate-800 space-y-4">
                <h3 class="text-sm font-bold text-slate-900 dark:text-white">
                    Tambah Standar Harga Acuan Baru
                </h3>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-semibold mb-1">Kode Item:</label>
                        <input v-model="formItemCode" type="text" placeholder="RM-001" class="w-full text-xs rounded-xl border border-slate-300 dark:border-slate-700 dark:bg-slate-800 p-2 font-mono" required />
                    </div>
                    <div>
                        <label class="block text-xs font-semibold mb-1">Kategori:</label>
                        <select v-model="formCategory" class="w-full text-xs rounded-xl border border-slate-300 dark:border-slate-700 dark:bg-slate-800 p-2">
                            <option value="material">Material (Bahan Baku/Kemas)</option>
                            <option value="supporting">Supporting (Penunjang)</option>
                        </select>
                    </div>
                </div>
                <div>
                    <label class="block text-xs font-semibold mb-1">Nama Barang:</label>
                    <input v-model="formItemName" type="text" placeholder="Ekstrak Meniran..." class="w-full text-xs rounded-xl border border-slate-300 dark:border-slate-700 dark:bg-slate-800 p-2 font-semibold" required />
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-semibold mb-1">Harga Acuan (Rp):</label>
                        <input v-model.number="formStandardPrice" type="number" class="w-full text-xs rounded-xl border border-slate-300 dark:border-slate-700 dark:bg-slate-800 p-2 font-bold" required />
                    </div>
                    <div>
                        <label class="block text-xs font-semibold mb-1">Satuan:</label>
                        <input v-model="formUom" type="text" placeholder="kg, pcs" class="w-full text-xs rounded-xl border border-slate-300 dark:border-slate-700 dark:bg-slate-800 p-2" required />
                    </div>
                </div>
                <div>
                    <label class="block text-xs font-semibold mb-1">Vendor Acuan (AVL):</label>
                    <input v-model="formVendorName" type="text" placeholder="PT Bahan Alami Nusantara" class="w-full text-xs rounded-xl border border-slate-300 dark:border-slate-700 dark:bg-slate-800 p-2" />
                </div>
                <div class="flex justify-end gap-2 pt-3">
                    <button @click="modalOpen = false" class="px-4 py-2 rounded-xl text-xs font-semibold text-slate-600">Batal</button>
                    <button @click="submitPrice" :disabled="modalLoading" class="px-4 py-2 rounded-xl text-xs font-semibold bg-violet-600 hover:bg-violet-700 text-white">Simpan Standar</button>
                </div>
            </div>
        </div>
    </ModuleLayout>
</template>
