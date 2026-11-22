<script setup>
import { ref } from 'vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import ModuleLayout from '@/Layouts/ModuleLayout.vue';
import {
    Truck,
    ArrowLeft,
    Plus,
    Trash2,
    Save,
    AlertCircle,
    Info,
    Calendar,
    Building2
} from 'lucide-vue-next';

const props = defineProps({
    vendors: Array,
    plants: Array,
    products: Array,
});

const form = useForm({
    po_number: '',
    vendor_id: props.vendors?.[0]?.id || '',
    plant_id: props.plants?.[0]?.id || '',
    delivery_order_number: '',
    receipt_date: new Date().toISOString().split('T')[0],
    notes: '',
    items: [
        {
            product_id: props.products?.[0]?.id || '',
            item_name: props.products?.[0]?.name || '',
            received_qty: 100,
            uom: props.products?.[0]?.uom?.code || 'KG',
            batch_number: 'LOT-' + Math.floor(100000 + Math.random() * 900000),
            expiry_date: new Date(Date.now() + 730 * 24 * 60 * 60 * 1000).toISOString().split('T')[0],
            unit_price: props.products?.[0]?.standard_cost || 0,
            notes: '',
        },
    ],
});

const addItem = () => {
    const defaultProd = props.products?.[0];
    form.items.push({
        product_id: defaultProd?.id || '',
        item_name: defaultProd?.name || '',
        received_qty: 10,
        uom: defaultProd?.uom?.code || 'KG',
        batch_number: 'LOT-' + Math.floor(100000 + Math.random() * 900000),
        expiry_date: new Date(Date.now() + 730 * 24 * 60 * 60 * 1000).toISOString().split('T')[0],
        unit_price: defaultProd?.standard_cost || 0,
        notes: '',
    });
};

const removeItem = (index) => {
    if (form.items.length > 1) {
        form.items.splice(index, 1);
    }
};

const onProductChange = (index) => {
    const item = form.items[index];
    const selected = props.products.find(p => p.id === item.product_id);
    if (selected) {
        item.item_name = selected.name;
        item.uom = selected.uom?.code || 'PCS';
        item.unit_price = selected.standard_cost || 0;
    }
};

const submit = () => {
    form.post('/supply-chain/goods-receipt');
};
</script>

<template>
    <Head title="Penerimaan Barang Baru - LogiFlow SCM" />

    <ModuleLayout current-module="supply-chain">
        <div class="space-y-6 max-w-5xl mx-auto">
            <!-- Header -->
            <div class="flex items-center justify-between bg-white dark:bg-slate-800 p-6 rounded-2xl shadow-sm border border-slate-200 dark:border-slate-700">
                <div class="flex items-center gap-3">
                    <Link
                        href="/supply-chain/goods-receipt"
                        class="p-2 text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 hover:bg-slate-100 dark:hover:bg-slate-700 rounded-xl transition-all"
                    >
                        <ArrowLeft class="w-5 h-5" />
                    </Link>
                    <div>
                        <h1 class="text-2xl font-bold text-slate-900 dark:text-white tracking-tight">
                            Catat Penerimaan Inbound (GRN)
                        </h1>
                        <p class="text-sm text-slate-500 dark:text-slate-400 mt-0.5">
                            Barang yang baru tiba akan otomatis masuk ke Gudang Karantina QC (WH-QRN) menunggu uji 4-parameter.
                        </p>
                    </div>
                </div>
            </div>

            <!-- Form -->
            <form @submit.prevent="submit" class="space-y-6">
                <!-- Metadata Card -->
                <div class="bg-white dark:bg-slate-800 p-6 rounded-2xl border border-slate-200 dark:border-slate-700 shadow-sm space-y-4">
                    <h2 class="text-base font-bold text-slate-900 dark:text-white pb-3 border-b border-slate-100 dark:border-slate-700">
                        Informasi Dokumen Pengiriman & Vendor
                    </h2>

                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 text-xs">
                        <div>
                            <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1">Pilih Rekanan / Vendor *</label>
                            <select v-model="form.vendor_id" class="w-full rounded-xl border border-slate-200 dark:border-slate-700 px-3 py-2 text-xs dark:bg-slate-900">
                                <option v-for="v in vendors" :key="v.id" :value="v.id">{{ v.name }} ({{ v.code }})</option>
                            </select>
                        </div>

                        <div>
                            <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1">Pabrik Tujuan *</label>
                            <select v-model="form.plant_id" class="w-full rounded-xl border border-slate-200 dark:border-slate-700 px-3 py-2 text-xs dark:bg-slate-900">
                                <option v-for="p in plants" :key="p.id" :value="p.id">{{ p.name }} ({{ p.code }})</option>
                            </select>
                        </div>

                        <div>
                            <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1">No. Surat Jalan Vendor (DO) *</label>
                            <input
                                type="text"
                                v-model="form.delivery_order_number"
                                required
                                placeholder="Contoh: SJ-2026/0543"
                                class="w-full rounded-xl border border-slate-200 dark:border-slate-700 px-3 py-2 text-xs dark:bg-slate-900"
                            />
                        </div>

                        <div>
                            <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1">Nomor PO (Opsional)</label>
                            <input
                                type="text"
                                v-model="form.po_number"
                                placeholder="Contoh: PO-202611-0001"
                                class="w-full rounded-xl border border-slate-200 dark:border-slate-700 px-3 py-2 text-xs dark:bg-slate-900"
                            />
                        </div>

                        <div class="sm:col-span-2">
                            <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1">Tanggal Penerimaan Fisik *</label>
                            <input
                                type="date"
                                v-model="form.receipt_date"
                                required
                                class="w-full rounded-xl border border-slate-200 dark:border-slate-700 px-3 py-2 text-xs dark:bg-slate-900"
                            />
                        </div>

                        <div class="sm:col-span-2">
                            <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1">Catatan Tambahan</label>
                            <input
                                type="text"
                                v-model="form.notes"
                                placeholder="Kondisi kemasan saat turun dari truk, nomor polisi kendaraan dsb."
                                class="w-full rounded-xl border border-slate-200 dark:border-slate-700 px-3 py-2 text-xs dark:bg-slate-900"
                            />
                        </div>
                    </div>
                </div>

                <!-- Items Card -->
                <div class="bg-white dark:bg-slate-800 p-6 rounded-2xl border border-slate-200 dark:border-slate-700 shadow-sm space-y-4">
                    <div class="flex items-center justify-between pb-3 border-b border-slate-100 dark:border-slate-700">
                        <div>
                            <h2 class="text-base font-bold text-slate-900 dark:text-white">Daftar Material Diterima</h2>
                            <p class="text-xs text-slate-500">Seluruh item otomatis didaftarkan ke antrean inspeksi QC incoming.</p>
                        </div>
                        <button
                            type="button"
                            @click="addItem"
                            class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-emerald-50 text-emerald-700 dark:bg-emerald-950/40 dark:text-emerald-400 hover:bg-emerald-100 font-semibold text-xs rounded-lg transition-colors"
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
                                <span class="font-bold text-slate-700 dark:text-slate-300">Item #{{ idx + 1 }}</span>
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

                            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-6 gap-3 text-xs">
                                <div class="lg:col-span-2">
                                    <label class="block font-medium text-slate-600 dark:text-slate-400 mb-1">Pilih Produk / Material *</label>
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
                                    <label class="block font-medium text-slate-600 dark:text-slate-400 mb-1">Qty Tiba *</label>
                                    <input
                                        type="number"
                                        step="0.01"
                                        v-model="item.received_qty"
                                        required
                                        class="w-full rounded-lg border border-slate-200 dark:border-slate-700 px-2.5 py-1.5 text-xs dark:bg-slate-800 text-right"
                                    />
                                </div>

                                <div>
                                    <label class="block font-medium text-slate-600 dark:text-slate-400 mb-1">Satuan (UOM)</label>
                                    <input
                                        type="text"
                                        v-model="item.uom"
                                        class="w-full rounded-lg border border-slate-200 dark:border-slate-700 px-2.5 py-1.5 text-xs dark:bg-slate-800 text-center"
                                    />
                                </div>

                                <div>
                                    <label class="block font-medium text-slate-600 dark:text-slate-400 mb-1">No. Batch Vendor *</label>
                                    <input
                                        type="text"
                                        v-model="item.batch_number"
                                        required
                                        class="w-full rounded-lg border border-slate-200 dark:border-slate-700 px-2.5 py-1.5 text-xs dark:bg-slate-800"
                                    />
                                </div>

                                <div>
                                    <label class="block font-medium text-slate-600 dark:text-slate-400 mb-1">Tgl Kedaluwarsa (ED) *</label>
                                    <input
                                        type="date"
                                        v-model="item.expiry_date"
                                        required
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
                        href="/supply-chain/goods-receipt"
                        class="px-5 py-2.5 border border-slate-200 dark:border-slate-700 text-slate-600 dark:text-slate-300 font-semibold text-xs rounded-xl hover:bg-slate-50 transition-colors"
                    >
                        Batal
                    </Link>
                    <button
                        type="submit"
                        :disabled="form.processing"
                        class="inline-flex items-center gap-2 px-6 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white font-semibold text-xs rounded-xl shadow-sm transition-all"
                    >
                        <Save class="w-4 h-4" />
                        <span>{{ form.processing ? 'Menyimpan...' : 'Simpan & Masukkan Karantina' }}</span>
                    </button>
                </div>
            </form>
        </div>
    </ModuleLayout>
</template>
