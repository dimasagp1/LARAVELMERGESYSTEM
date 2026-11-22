<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';
import ModuleLayout from '@/Layouts/ModuleLayout.vue';
import {
    CornerUpLeft,
    ArrowLeft,
    Plus,
    Trash2,
    Save,
    AlertCircle
} from 'lucide-vue-next';

const props = defineProps({
    vendors: Array,
    receipts: Array,
    products: Array,
});

const form = useForm({
    vendor_id: props.vendors?.[0]?.id || '',
    goods_receipt_id: props.receipts?.[0]?.id || null,
    return_date: new Date().toISOString().split('T')[0],
    reason: 'Material tidak memenuhi spesifikasi mutu CPOTB (Reject QC)',
    items: [
        {
            product_id: props.products?.[0]?.id || '',
            batch_number: 'LOT-REJECT',
            quantity: 50,
            uom: props.products?.[0]?.uom?.code || 'KG',
            unit_price: props.products?.[0]?.standard_cost || 0,
            reason: 'Kadar zat aktif di bawah batas minimal monografi',
        },
    ],
});

const addItem = () => {
    const p = props.products?.[0];
    form.items.push({
        product_id: p?.id || '',
        batch_number: '',
        quantity: 10,
        uom: p?.uom?.code || 'KG',
        unit_price: p?.standard_cost || 0,
        reason: 'Kemasan rusak / segel produsen terbuka',
    });
};

const removeItem = (idx) => {
    if (form.items.length > 1) {
        form.items.splice(idx, 1);
    }
};

const onProductChange = (idx) => {
    const item = form.items[idx];
    const p = props.products.find(prod => prod.id === item.product_id);
    if (p) {
        item.uom = p.uom?.code || 'PCS';
        item.unit_price = p.standard_cost || 0;
    }
};

const submit = () => {
    form.post('/supply-chain/material-return');
};
</script>

<template>
    <Head title="Buat Memo Retur Barang - LogiFlow SCM" />

    <ModuleLayout current-module="supply-chain">
        <div class="space-y-6 max-w-5xl mx-auto">
            <!-- Header -->
            <div class="flex items-center justify-between bg-white dark:bg-slate-800 p-6 rounded-2xl shadow-sm border border-slate-200 dark:border-slate-700">
                <div class="flex items-center gap-3">
                    <Link
                        href="/supply-chain/material-return"
                        class="p-2 text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 hover:bg-slate-100 dark:hover:bg-slate-700 rounded-xl transition-all"
                    >
                        <ArrowLeft class="w-5 h-5" />
                    </Link>
                    <div>
                        <h1 class="text-2xl font-bold text-slate-900 dark:text-white tracking-tight">
                            Buat Surat Jalan Retur Barang
                        </h1>
                        <p class="text-sm text-slate-500 dark:text-slate-400 mt-0.5">
                            Dokumen pengembalian material reject ke vendor untuk klaim penggantian lot atau nota kredit.
                        </p>
                    </div>
                </div>
            </div>

            <!-- Form -->
            <form @submit.prevent="submit" class="space-y-6">
                <!-- Info Card -->
                <div class="bg-white dark:bg-slate-800 p-6 rounded-2xl border border-slate-200 dark:border-slate-700 shadow-sm space-y-4 text-xs">
                    <h2 class="text-base font-bold text-slate-900 dark:text-white pb-3 border-b border-slate-100 dark:border-slate-700">
                        Informasi Vendor & Pengiriman Retur
                    </h2>

                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                        <div>
                            <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1">Vendor Rekanan *</label>
                            <select v-model="form.vendor_id" class="w-full rounded-xl border border-slate-200 dark:border-slate-700 px-3 py-2 text-xs dark:bg-slate-900">
                                <option v-for="v in vendors" :key="v.id" :value="v.id">{{ v.name }}</option>
                            </select>
                        </div>

                        <div>
                            <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1">Ref. Penerimaan GRN (Opsional)</label>
                            <select v-model="form.goods_receipt_id" class="w-full rounded-xl border border-slate-200 dark:border-slate-700 px-3 py-2 text-xs dark:bg-slate-900">
                                <option :value="null">-- Tanpa Ref GRN Langsung --</option>
                                <option v-for="r in receipts" :key="r.id" :value="r.id">{{ r.grn_number }} (PO: {{ r.po_number ?? '-' }})</option>
                            </select>
                        </div>

                        <div>
                            <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1">Tanggal Retur *</label>
                            <input
                                type="date"
                                v-model="form.return_date"
                                required
                                class="w-full rounded-xl border border-slate-200 dark:border-slate-700 px-3 py-2 text-xs dark:bg-slate-900"
                            />
                        </div>

                        <div class="sm:col-span-3">
                            <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1">Alasan Penolakan / Deviasi Mutu *</label>
                            <input
                                type="text"
                                v-model="form.reason"
                                required
                                class="w-full rounded-xl border border-slate-200 dark:border-slate-700 px-3 py-2 text-xs dark:bg-slate-900"
                            />
                        </div>
                    </div>
                </div>

                <!-- Items Card -->
                <div class="bg-white dark:bg-slate-800 p-6 rounded-2xl border border-slate-200 dark:border-slate-700 shadow-sm space-y-4 text-xs">
                    <div class="flex items-center justify-between pb-3 border-b border-slate-100 dark:border-slate-700">
                        <div>
                            <h2 class="text-base font-bold text-slate-900 dark:text-white">Daftar Item Yang Dikembalikan</h2>
                            <p class="text-slate-500">Rincian item beserta estimasi harga satuan untuk klaim finansial.</p>
                        </div>
                        <button
                            type="button"
                            @click="addItem"
                            class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-rose-50 text-rose-700 dark:bg-rose-950/40 dark:text-rose-400 hover:bg-rose-100 font-semibold rounded-lg transition-colors"
                        >
                            <Plus class="w-3.5 h-3.5" />
                            <span>Tambah Item</span>
                        </button>
                    </div>

                    <div class="space-y-4">
                        <div
                            v-for="(item, idx) in form.items"
                            :key="idx"
                            class="p-4 bg-slate-50 dark:bg-slate-900/50 rounded-xl border border-slate-200 dark:border-slate-700/80 space-y-3"
                        >
                            <div class="flex items-center justify-between text-xs">
                                <span class="font-bold text-slate-700 dark:text-slate-300">Barang #{{ idx + 1 }}</span>
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
                                    <label class="block font-medium text-slate-600 dark:text-slate-400 mb-1">Pilih Produk *</label>
                                    <select
                                        v-model="item.product_id"
                                        @change="onProductChange(idx)"
                                        class="w-full rounded-lg border border-slate-200 dark:border-slate-700 px-2.5 py-1.5 text-xs dark:bg-slate-800"
                                    >
                                        <option v-for="prod in products" :key="prod.id" :value="prod.id">
                                            {{ prod.code }} - {{ prod.name }}
                                        </option>
                                    </select>
                                </div>

                                <div>
                                    <label class="block font-medium text-slate-600 dark:text-slate-400 mb-1">No. Batch Reject</label>
                                    <input
                                        type="text"
                                        v-model="item.batch_number"
                                        class="w-full rounded-lg border border-slate-200 dark:border-slate-700 px-2.5 py-1.5 text-xs dark:bg-slate-800"
                                    />
                                </div>

                                <div>
                                    <label class="block font-medium text-slate-600 dark:text-slate-400 mb-1">Qty Retur *</label>
                                    <input
                                        type="number"
                                        step="0.01"
                                        v-model="item.quantity"
                                        required
                                        class="w-full rounded-lg border border-slate-200 dark:border-slate-700 px-2.5 py-1.5 text-xs dark:bg-slate-800 text-right text-rose-600 font-bold"
                                    />
                                </div>

                                <div>
                                    <label class="block font-medium text-slate-600 dark:text-slate-400 mb-1">Satuan</label>
                                    <input
                                        type="text"
                                        v-model="item.uom"
                                        class="w-full rounded-lg border border-slate-200 dark:border-slate-700 px-2.5 py-1.5 text-xs dark:bg-slate-800 text-center"
                                    />
                                </div>

                                <div class="lg:col-span-5">
                                    <label class="block font-medium text-slate-600 dark:text-slate-400 mb-1">Alasan Kerusakan Spesifik Item</label>
                                    <input
                                        type="text"
                                        v-model="item.reason"
                                        placeholder="Misal: Uji cemaran mikroba melewati ambang batas maksimal"
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
                        href="/supply-chain/material-return"
                        class="px-5 py-2.5 border border-slate-200 dark:border-slate-700 text-slate-600 dark:text-slate-300 font-semibold text-xs rounded-xl hover:bg-slate-50 transition-colors"
                    >
                        Batal
                    </Link>
                    <button
                        type="submit"
                        :disabled="form.processing"
                        class="inline-flex items-center gap-2 px-6 py-2.5 bg-rose-600 hover:bg-rose-700 text-white font-semibold text-xs rounded-xl shadow-sm transition-all"
                    >
                        <Save class="w-4 h-4" />
                        <span>{{ form.processing ? 'Menyimpan...' : 'Terbitkan Memo Retur' }}</span>
                    </button>
                </div>
            </form>
        </div>
    </ModuleLayout>
</template>
