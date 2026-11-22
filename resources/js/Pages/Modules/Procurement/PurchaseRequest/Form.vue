<script setup>
import { ref, computed } from 'vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import ModuleLayout from '@/Layouts/ModuleLayout.vue';
import {
    ArrowLeft,
    Plus,
    Trash2,
    Save,
    Send,
    AlertTriangle,
    ShoppingCart,
} from 'lucide-vue-next';

const props = defineProps({
    module: Object,
    departments: Array,
    products: Array,
    uoms: Array,
    purposes: Array,
});

const form = useForm({
    department_id: props.departments[0]?.id || '',
    pr_type: 'operational',
    request_date: new Date().toISOString().split('T')[0],
    purpose: props.purposes[0] || 'Bahan Baku Rutin Produksi (Raw Material)',
    custom_purpose: '',
    bypass_budget_check: false,
    bypass_budget_reason: '',
    notes: '',
    as_draft: false,
    items: [
        {
            product_id: null,
            item_name: '',
            item_code: '',
            quantity: 1,
            uom: 'pcs',
            estimated_price: 0,
            is_bon: false,
            need_holding_quotation: false,
            need_holding_delivery: false,
        },
    ],
});

const addItem = () => {
    form.items.push({
        product_id: null,
        item_name: '',
        item_code: '',
        quantity: 1,
        uom: 'pcs',
        estimated_price: 0,
        is_bon: false,
        need_holding_quotation: false,
        need_holding_delivery: false,
    });
};

const removeItem = (idx) => {
    if (form.items.length > 1) {
        form.items.splice(idx, 1);
    }
};

const onProductSelect = (idx, event) => {
    const prodId = event.target.value;
    const prod = props.products.find((p) => p.id == prodId);
    if (prod) {
        form.items[idx].product_id = prod.id;
        form.items[idx].item_name = prod.name;
        form.items[idx].item_code = prod.code;
        form.items[idx].uom = prod.uom || 'pcs';
    }
};

const totalEstimated = computed(() => {
    return form.items.reduce((acc, it) => {
        const qty = parseFloat(it.quantity) || 0;
        const price = parseFloat(it.estimated_price) || 0;
        return acc + (qty * price);
    }, 0);
});

const submitForm = (draftMode = false) => {
    form.as_draft = draftMode;
    if (form.purpose === 'Lainnya' && form.custom_purpose) {
        form.purpose = form.custom_purpose;
    }
    form.post('/procurement/purchase-requests');
};
</script>

<template>
    <Head title="Buat Purchase Request - ERP" />

    <ModuleLayout :module="module">
        <div class="space-y-6 max-w-5xl mx-auto">
            <!-- Header -->
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <Link
                        href="/procurement/purchase-requests"
                        class="p-2 rounded-xl bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 hover:bg-slate-100 transition-colors"
                    >
                        <ArrowLeft class="w-4 h-4 text-slate-600 dark:text-slate-300" />
                    </Link>
                    <div>
                        <h1 class="text-xl font-bold text-slate-900 dark:text-white">
                            Formulir Purchase Request Baru
                        </h1>
                        <p class="text-xs text-slate-500">
                            Pengajuan kebutuhan barang/jasa departemen
                        </p>
                    </div>
                </div>

                <div class="flex items-center gap-2">
                    <button
                        type="button"
                        @click="submitForm(true)"
                        :disabled="form.processing"
                        class="px-4 py-2.5 rounded-xl border border-slate-300 dark:border-slate-700 hover:bg-slate-100 dark:hover:bg-slate-800 text-xs font-semibold text-slate-700 dark:text-slate-200 transition-colors inline-flex items-center gap-1.5"
                    >
                        <Save class="w-4 h-4" />
                        Simpan Draft
                    </button>
                    <button
                        type="button"
                        @click="submitForm(false)"
                        :disabled="form.processing"
                        class="px-5 py-2.5 rounded-xl bg-violet-600 hover:bg-violet-700 text-white text-xs font-semibold shadow-xs transition-colors inline-flex items-center gap-1.5"
                    >
                        <Send class="w-4 h-4" />
                        Submit PR
                    </button>
                </div>
            </div>

            <!-- Error Banner -->
            <div v-if="Object.keys(form.errors).length > 0" class="p-4 rounded-xl bg-rose-50 dark:bg-rose-950/40 border border-rose-200 dark:border-rose-800 text-xs text-rose-700 dark:text-rose-300">
                <div class="font-bold flex items-center gap-1.5 mb-1">
                    <AlertTriangle class="w-4 h-4 text-rose-600" />
                    Terdapat kesalahan input:
                </div>
                <ul class="list-disc list-inside space-y-0.5">
                    <li v-for="(err, field) in form.errors" :key="field">{{ err }}</li>
                </ul>
            </div>

            <!-- PR Header Information Card -->
            <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-6 shadow-xs space-y-4">
                <h2 class="text-sm font-bold text-slate-900 dark:text-white border-b border-slate-100 dark:border-slate-800 pb-2">
                    1. Informasi Umum Pengadaan
                </h2>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">
                            Departemen Pemohon <span class="text-rose-500">*</span>
                        </label>
                        <select
                            v-model="form.department_id"
                            class="w-full text-xs rounded-xl border border-slate-300 dark:border-slate-700 dark:bg-slate-800 p-2.5"
                        >
                            <option v-for="d in departments" :key="d.id" :value="d.id">{{ d.name }}</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">
                            Tipe Pengadaan <span class="text-rose-500">*</span>
                        </label>
                        <select
                            v-model="form.pr_type"
                            class="w-full text-xs rounded-xl border border-slate-300 dark:border-slate-700 dark:bg-slate-800 p-2.5"
                        >
                            <option value="operational">Operasional (Pabrik & Warehouse - OM)</option>
                            <option value="non_operational">Non-Operasional (Umum & Kantor - FAT)</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">
                            Tanggal Pengajuan
                        </label>
                        <input
                            v-model="form.request_date"
                            type="date"
                            class="w-full text-xs rounded-xl border border-slate-300 dark:border-slate-700 dark:bg-slate-800 p-2.5"
                        />
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">
                            Tujuan Belanja (Purpose FAT) <span class="text-rose-500">*</span>
                        </label>
                        <select
                            v-model="form.purpose"
                            class="w-full text-xs rounded-xl border border-slate-300 dark:border-slate-700 dark:bg-slate-800 p-2.5"
                        >
                            <option v-for="p in purposes" :key="p" :value="p">{{ p }}</option>
                            <option value="Lainnya">Lainnya (Tulis Manual)</option>
                        </select>
                        <input
                            v-if="form.purpose === 'Lainnya'"
                            v-model="form.custom_purpose"
                            type="text"
                            placeholder="Tulis tujuan belanja spesifik..."
                            class="mt-2 w-full text-xs rounded-xl border border-slate-300 dark:border-slate-700 dark:bg-slate-800 p-2.5"
                        />
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">
                            Catatan Tambahan
                        </label>
                        <input
                            v-model="form.notes"
                            type="text"
                            placeholder="Contoh: Pengadaan darurat untuk perbaikan mesin line 2"
                            class="w-full text-xs rounded-xl border border-slate-300 dark:border-slate-700 dark:bg-slate-800 p-2.5"
                        />
                    </div>
                </div>

                <!-- Budget Bypass Option -->
                <div class="p-4 rounded-xl border border-amber-200 dark:border-amber-900 bg-amber-50/50 dark:bg-amber-950/20">
                    <div class="flex items-center gap-2">
                        <input
                            id="bypass-toggle"
                            v-model="form.bypass_budget_check"
                            type="checkbox"
                            class="rounded border-slate-300 text-violet-600 focus:ring-violet-500"
                        />
                        <label for="bypass-toggle" class="text-xs font-bold text-amber-900 dark:text-amber-200 cursor-pointer">
                            Ajukan Budget Bypass (Pengadaan Mendesak / Force Majeure Melebihi Pagu)
                        </label>
                    </div>
                    <div v-if="form.bypass_budget_check" class="mt-2">
                        <input
                            v-model="form.bypass_budget_reason"
                            type="text"
                            placeholder="Jelaskan alasan darurat untuk persetujuan GM..."
                            class="w-full text-xs rounded-xl border border-amber-300 dark:border-amber-700 dark:bg-slate-800 p-2.5 text-slate-800 dark:text-slate-200"
                        />
                    </div>
                </div>
            </div>

            <!-- Granular Items List Card -->
            <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-6 shadow-xs space-y-4">
                <div class="flex items-center justify-between border-b border-slate-100 dark:border-slate-800 pb-2">
                    <div>
                        <h2 class="text-sm font-bold text-slate-900 dark:text-white">
                            2. Rincian Item Permintaan Pengadaan
                        </h2>
                        <p class="text-[11px] text-slate-500">
                            Setiap baris item akan diverifikasi secara granular sesuai kebutuhan
                        </p>
                    </div>
                    <button
                        type="button"
                        @click="addItem"
                        class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-violet-50 text-violet-700 dark:bg-violet-950/60 dark:text-violet-300 hover:bg-violet-100 text-xs font-semibold transition-colors"
                    >
                        <Plus class="w-3.5 h-3.5" />
                        Tambah Item
                    </button>
                </div>

                <div class="space-y-4">
                    <div
                        v-for="(item, idx) in form.items"
                        :key="idx"
                        class="p-4 rounded-xl border border-slate-200 dark:border-slate-800 bg-slate-50/40 dark:bg-slate-800/30 space-y-3"
                    >
                        <div class="flex items-center justify-between">
                            <span class="text-xs font-bold text-slate-700 dark:text-slate-300">
                                Baris Item #{{ idx + 1 }}
                            </span>
                            <button
                                v-if="form.items.length > 1"
                                type="button"
                                @click="removeItem(idx)"
                                class="text-rose-500 hover:text-rose-700 p-1 rounded transition-colors"
                                title="Hapus baris item ini"
                            >
                                <Trash2 class="w-4 h-4" />
                            </button>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-12 gap-3">
                            <div class="lg:col-span-3">
                                <label class="block text-[11px] font-semibold text-slate-600 dark:text-slate-400 mb-1">
                                    Pilih Master Produk (Opsional)
                                </label>
                                <select
                                    @change="onProductSelect(idx, $event)"
                                    class="w-full text-xs rounded-xl border border-slate-300 dark:border-slate-700 dark:bg-slate-800 p-2"
                                >
                                    <option value="">-- Input Manual --</option>
                                    <option v-for="p in products" :key="p.id" :value="p.id">
                                        [{{ p.code }}] {{ p.name }}
                                    </option>
                                </select>
                            </div>

                            <div class="lg:col-span-4">
                                <label class="block text-[11px] font-semibold text-slate-600 dark:text-slate-400 mb-1">
                                    Nama Barang / Jasa <span class="text-rose-500">*</span>
                                </label>
                                <input
                                    v-model="item.item_name"
                                    type="text"
                                    placeholder="Contoh: Ekstrak Curcuma Xanthorrhiza"
                                    class="w-full text-xs rounded-xl border border-slate-300 dark:border-slate-700 dark:bg-slate-800 p-2 font-medium"
                                    required
                                />
                            </div>

                            <div class="lg:col-span-2">
                                <label class="block text-[11px] font-semibold text-slate-600 dark:text-slate-400 mb-1">
                                    Jumlah <span class="text-rose-500">*</span>
                                </label>
                                <input
                                    v-model.number="item.quantity"
                                    type="number"
                                    step="0.01"
                                    min="0.01"
                                    class="w-full text-xs rounded-xl border border-slate-300 dark:border-slate-700 dark:bg-slate-800 p-2 font-semibold"
                                    required
                                />
                            </div>

                            <div class="lg:col-span-1">
                                <label class="block text-[11px] font-semibold text-slate-600 dark:text-slate-400 mb-1">
                                    Satuan
                                </label>
                                <input
                                    v-model="item.uom"
                                    type="text"
                                    placeholder="kg, pcs, set"
                                    class="w-full text-xs rounded-xl border border-slate-300 dark:border-slate-700 dark:bg-slate-800 p-2"
                                />
                            </div>

                            <div class="lg:col-span-2">
                                <label class="block text-[11px] font-semibold text-slate-600 dark:text-slate-400 mb-1">
                                    Estimasi / Unit (Rp)
                                </label>
                                <input
                                    v-model.number="item.estimated_price"
                                    type="number"
                                    step="1000"
                                    placeholder="0"
                                    class="w-full text-xs rounded-xl border border-slate-300 dark:border-slate-700 dark:bg-slate-800 p-2 text-right font-semibold"
                                />
                            </div>
                        </div>

                        <!-- Special Procurement Flags -->
                        <div class="flex flex-wrap items-center gap-4 pt-2 border-t border-slate-200/60 dark:border-slate-700/60 text-xs">
                            <label class="flex items-center gap-1.5 cursor-pointer text-amber-700 dark:text-amber-400 font-semibold">
                                <input
                                    v-model="item.is_bon"
                                    type="checkbox"
                                    class="rounded border-slate-300 text-amber-600 focus:ring-amber-500"
                                />
                                Jalur Bon Sementara (Kas Kecil / Pembelian Offline)
                            </label>
                            <label class="flex items-center gap-1.5 cursor-pointer text-indigo-700 dark:text-indigo-400 font-semibold">
                                <input
                                    v-model="item.need_holding_quotation"
                                    type="checkbox"
                                    class="rounded border-slate-300 text-indigo-600 focus:ring-indigo-500"
                                />
                                Perlu Penawaran Holding
                            </label>
                            <label class="flex items-center gap-1.5 cursor-pointer text-teal-700 dark:text-teal-400 font-semibold">
                                <input
                                    v-model="item.need_holding_delivery"
                                    type="checkbox"
                                    class="rounded border-slate-300 text-teal-600 focus:ring-teal-500"
                                />
                                Verifikasi Logistik Holding
                            </label>
                        </div>
                    </div>
                </div>

                <!-- Total Estimation Bar -->
                <div class="p-4 rounded-xl bg-violet-50/60 dark:bg-violet-950/30 border border-violet-100 dark:border-violet-900 flex items-center justify-between">
                    <span class="text-xs font-bold text-violet-900 dark:text-violet-200">
                        Total Estimasi Pengadaan ({{ form.items.length }} Item):
                    </span>
                    <span class="text-base font-extrabold text-violet-700 dark:text-violet-300">
                        Rp {{ totalEstimated.toLocaleString('id-ID') }}
                    </span>
                </div>
            </div>
        </div>
    </ModuleLayout>
</template>
