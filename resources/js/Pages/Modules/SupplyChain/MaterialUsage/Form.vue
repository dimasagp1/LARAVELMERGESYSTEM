<script setup>
import { ref } from 'vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import ModuleLayout from '@/Layouts/ModuleLayout.vue';
import {
    ArrowRightCircle,
    ArrowLeft,
    Plus,
    Trash2,
    Save,
    AlertCircle,
    Boxes
} from 'lucide-vue-next';

const props = defineProps({
    plants: Array,
    departments: Array,
    availableStocks: Array,
});

const form = useForm({
    work_order_number: 'SPK-2026' + Math.floor(1000 + Math.random() * 9000),
    plant_id: props.plants?.[0]?.id || '',
    department_id: props.departments?.[0]?.id || '',
    usage_date: new Date().toISOString().split('T')[0],
    notes: 'Penyerahan bahan baku sesuai formula SPK batch manufaktur',
    items: [
        {
            inventory_stock_id: props.availableStocks?.[0]?.id || '',
            product_id: props.availableStocks?.[0]?.product_id || '',
            batch_number: props.availableStocks?.[0]?.batch_number || '',
            quantity: 10,
            uom: props.availableStocks?.[0]?.product?.uom?.code || 'KG',
            unit_cost: props.availableStocks?.[0]?.unit_cost || 0,
            notes: '',
        },
    ],
});

const onStockSelect = (index) => {
    const item = form.items[index];
    const stock = props.availableStocks.find(s => s.id === item.inventory_stock_id);
    if (stock) {
        item.product_id = stock.product_id;
        item.batch_number = stock.batch_number;
        item.uom = stock.product?.uom?.code || 'PCS';
        item.unit_cost = stock.unit_cost || 0;
    }
};

const addItem = () => {
    const defaultStock = props.availableStocks?.[0];
    form.items.push({
        inventory_stock_id: defaultStock?.id || '',
        product_id: defaultStock?.product_id || '',
        batch_number: defaultStock?.batch_number || '',
        quantity: 5,
        uom: defaultStock?.product?.uom?.code || 'KG',
        unit_cost: defaultStock?.unit_cost || 0,
        notes: '',
    });
};

const removeItem = (index) => {
    if (form.items.length > 1) {
        form.items.splice(index, 1);
    }
};

const submit = () => {
    form.post('/supply-chain/material-usage');
};
</script>

<template>
    <Head title="Terbitkan BSTB Baru - LogiFlow SCM" />

    <ModuleLayout current-module="supply-chain">
        <div class="space-y-6 max-w-5xl mx-auto">
            <!-- Header -->
            <div class="flex items-center justify-between bg-white dark:bg-slate-800 p-6 rounded-2xl shadow-sm border border-slate-200 dark:border-slate-700">
                <div class="flex items-center gap-3">
                    <Link
                        href="/supply-chain/material-usage"
                        class="p-2 text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 hover:bg-slate-100 dark:hover:bg-slate-700 rounded-xl transition-all"
                    >
                        <ArrowLeft class="w-5 h-5" />
                    </Link>
                    <div>
                        <h1 class="text-2xl font-bold text-slate-900 dark:text-white tracking-tight">
                            Terbitkan Bukti Serah Terima Bahan (BSTB)
                        </h1>
                        <p class="text-sm text-slate-500 dark:text-slate-400 mt-0.5">
                            Pengeluaran material dari gudang akan memotong stok aktif secara otomatis dan mengunci alokasi SPK.
                        </p>
                    </div>
                </div>
            </div>

            <!-- Form -->
            <form @submit.prevent="submit" class="space-y-6">
                <!-- Metadata Card -->
                <div class="bg-white dark:bg-slate-800 p-6 rounded-2xl border border-slate-200 dark:border-slate-700 shadow-sm space-y-4">
                    <h2 class="text-base font-bold text-slate-900 dark:text-white pb-3 border-b border-slate-100 dark:border-slate-700">
                        Informasi Penugasan Produksi
                    </h2>

                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 text-xs">
                        <div>
                            <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1">No. SPK / Work Order *</label>
                            <input
                                type="text"
                                v-model="form.work_order_number"
                                required
                                placeholder="Contoh: SPK-202611-002"
                                class="w-full rounded-xl border border-slate-200 dark:border-slate-700 px-3 py-2 text-xs dark:bg-slate-900"
                            />
                        </div>

                        <div>
                            <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1">Pabrik Fasilitas *</label>
                            <select v-model="form.plant_id" class="w-full rounded-xl border border-slate-200 dark:border-slate-700 px-3 py-2 text-xs dark:bg-slate-900">
                                <option v-for="p in plants" :key="p.id" :value="p.id">{{ p.name }} ({{ p.code }})</option>
                            </select>
                        </div>

                        <div>
                            <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1">Departemen Pemohon *</label>
                            <select v-model="form.department_id" class="w-full rounded-xl border border-slate-200 dark:border-slate-700 px-3 py-2 text-xs dark:bg-slate-900">
                                <option v-for="d in departments" :key="d.id" :value="d.id">{{ d.name }}</option>
                            </select>
                        </div>

                        <div>
                            <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1">Tanggal Penyerahan *</label>
                            <input
                                type="date"
                                v-model="form.usage_date"
                                required
                                class="w-full rounded-xl border border-slate-200 dark:border-slate-700 px-3 py-2 text-xs dark:bg-slate-900"
                            />
                        </div>

                        <div class="lg:col-span-4">
                            <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1">Catatan Serah Terima</label>
                            <input
                                type="text"
                                v-model="form.notes"
                                class="w-full rounded-xl border border-slate-200 dark:border-slate-700 px-3 py-2 text-xs dark:bg-slate-900"
                            />
                        </div>
                    </div>
                </div>

                <!-- Items Card -->
                <div class="bg-white dark:bg-slate-800 p-6 rounded-2xl border border-slate-200 dark:border-slate-700 shadow-sm space-y-4">
                    <div class="flex items-center justify-between pb-3 border-b border-slate-100 dark:border-slate-700">
                        <div>
                            <h2 class="text-base font-bold text-slate-900 dark:text-white">Daftar Bahan Yang Dikeluarkan</h2>
                            <p class="text-xs text-slate-500">Pilih dari stok gudang yang telah lulus inspeksi QC (Status Available).</p>
                        </div>
                        <button
                            type="button"
                            @click="addItem"
                            class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-blue-50 text-blue-700 dark:bg-blue-950/40 dark:text-blue-400 hover:bg-blue-100 font-semibold text-xs rounded-lg transition-colors"
                        >
                            <Plus class="w-3.5 h-3.5" />
                            <span>Tambah Baris</span>
                        </button>
                    </div>

                    <div class="space-y-4">
                        <div
                            v-for="(item, idx) in form.items"
                            :key="idx"
                            class="p-4 bg-slate-50 dark:bg-slate-900/50 rounded-xl border border-slate-200 dark:border-slate-700/80 space-y-3"
                        >
                            <div class="flex items-center justify-between text-xs">
                                <span class="font-bold text-slate-700 dark:text-slate-300">Bahan #{{ idx + 1 }}</span>
                                <button
                                    v-if="form.items.length > 1"
                                    type="button"
                                    @click="removeItem(idx)"
                                    class="text-rose-500 hover:text-rose-700 flex items-center gap-1 text-[11px]"
                                >
                                    <Trash2 class="w-3.5 h-3.5" />
                                    <span>Hapus</span>
                                </button>
                            </div>

                            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-3 text-xs">
                                <div class="lg:col-span-2">
                                    <label class="block font-medium text-slate-600 dark:text-slate-400 mb-1">Pilih Stok Fisik Gudang *</label>
                                    <select
                                        v-model="item.inventory_stock_id"
                                        @change="onStockSelect(idx)"
                                        class="w-full rounded-lg border border-slate-200 dark:border-slate-700 px-2.5 py-1.5 text-xs dark:bg-slate-800"
                                    >
                                        <option v-for="stk in availableStocks" :key="stk.id" :value="stk.id">
                                            {{ stk.product?.code }} - {{ stk.product?.name }} [Batch: {{ stk.batch_number }} | Bebas: {{ stk.quantity_available }} {{ stk.product?.uom?.code }}]
                                        </option>
                                    </select>
                                </div>

                                <div>
                                    <label class="block font-medium text-slate-600 dark:text-slate-400 mb-1">Jumlah Diserahkan *</label>
                                    <input
                                        type="number"
                                        step="0.01"
                                        v-model="item.quantity"
                                        required
                                        class="w-full rounded-lg border border-slate-200 dark:border-slate-700 px-2.5 py-1.5 text-xs dark:bg-slate-800 text-right"
                                    />
                                </div>

                                <div>
                                    <label class="block font-medium text-slate-600 dark:text-slate-400 mb-1">Satuan</label>
                                    <input
                                        type="text"
                                        v-model="item.uom"
                                        readonly
                                        class="w-full rounded-lg border border-slate-200 dark:border-slate-700 px-2.5 py-1.5 text-xs bg-slate-100 dark:bg-slate-800 text-center"
                                    />
                                </div>

                                <div>
                                    <label class="block font-medium text-slate-600 dark:text-slate-400 mb-1">Keterangan Mesin/Alur</label>
                                    <input
                                        type="text"
                                        v-model="item.notes"
                                        placeholder="Ruang mixing / granulasi"
                                        class="w-full rounded-lg border border-slate-200 dark:border-slate-700 px-2.5 py-1.5 text-xs dark:bg-slate-800"
                                    />
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Submit Bar -->
                <div class="flex items-center justify-end gap-3">
                    <Link
                        href="/supply-chain/material-usage"
                        class="px-5 py-2.5 border border-slate-200 dark:border-slate-700 text-slate-600 dark:text-slate-300 font-semibold text-xs rounded-xl hover:bg-slate-50 transition-colors"
                    >
                        Batal
                    </Link>
                    <button
                        type="submit"
                        :disabled="form.processing"
                        class="inline-flex items-center gap-2 px-6 py-2.5 bg-blue-600 hover:bg-blue-700 text-white font-semibold text-xs rounded-xl shadow-sm transition-all"
                    >
                        <Save class="w-4 h-4" />
                        <span>{{ form.processing ? 'Memproses...' : 'Terbitkan BSTB & Potong Stok' }}</span>
                    </button>
                </div>
            </form>
        </div>
    </ModuleLayout>
</template>
