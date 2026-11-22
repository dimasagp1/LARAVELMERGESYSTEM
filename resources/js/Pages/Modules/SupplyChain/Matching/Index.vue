<script setup>
import { ref } from 'vue';
import { Head, Link, useForm, router } from '@inertiajs/vue3';
import ModuleLayout from '@/Layouts/ModuleLayout.vue';
import {
    FileCheck2,
    CheckCircle2,
    AlertTriangle,
    Plus,
    X,
    FileText,
    DollarSign,
    Building2,
    Calendar,
    ArrowRight
} from 'lucide-vue-next';

const props = defineProps({
    matchings: Object,
    vendorBills: Array,
    completedReceipts: Array,
    stats: Object,
});

const isModalOpen = ref(false);

const form = useForm({
    goods_receipt_id: props.completedReceipts?.[0]?.id || '',
    vendor_invoice_number: 'INV-VEN-' + Math.floor(10000 + Math.random() * 90000),
    invoice_qty: 0,
    invoice_amount: 0,
    po_total_qty: 0,
    po_unit_price: 0,
    notes: 'Audit 3-way matching invoice vendor terhadap kuantitas lulus QC',
    auto_create_bill: true,
});

const onReceiptSelect = () => {
    const rc = props.completedReceipts.find(r => r.id === form.goods_receipt_id);
    if (rc) {
        const passedQty = rc.items?.reduce((acc, it) => acc + parseFloat(it.passed_qty || 0), 0) || 0;
        const unitPrice = parseFloat(rc.items?.[0]?.unit_price || 0);
        form.po_total_qty = rc.items?.reduce((acc, it) => acc + parseFloat(it.received_qty || 0), 0) || 0;
        form.po_unit_price = unitPrice;
        form.invoice_qty = passedQty;
        form.invoice_amount = Math.round(passedQty * unitPrice);
    }
};

const openAuditModal = () => {
    onReceiptSelect();
    isModalOpen.value = true;
};

const submitMatching = () => {
    form.post('/supply-chain/matching', {
        onSuccess: () => {
            isModalOpen.value = false;
        },
    });
};

const createBill = (matchingId) => {
    router.post(`/supply-chain/matching/${matchingId}/bill`);
};

const formatCurrency = (val) => new Intl.NumberFormat('id-ID', {
    style: 'currency',
    currency: 'IDR',
    maximumFractionDigits: 0,
}).format(val || 0);

const formatNumber = (val) => new Intl.NumberFormat('id-ID').format(val || 0);
</script>

<template>
    <Head title="3-Way Matching AP Audit - LogiFlow SCM" />

    <ModuleLayout current-module="supply-chain">
        <div class="space-y-6">
            <!-- Header -->
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 bg-white dark:bg-slate-800 p-6 rounded-2xl shadow-sm border border-slate-200 dark:border-slate-700">
                <div class="flex items-center gap-3">
                    <div class="p-2.5 bg-purple-500/10 text-purple-600 dark:text-purple-400 rounded-xl">
                        <FileCheck2 class="w-7 h-7" />
                    </div>
                    <div>
                        <h1 class="text-2xl font-bold text-slate-900 dark:text-white tracking-tight">
                            3-Way Matching & Hutang Usaha (AP)
                        </h1>
                        <p class="text-sm text-slate-500 dark:text-slate-400 mt-0.5">
                            Audit presisi 3 dokumen: Purchase Order (PO) vs Kuantitas Lulus QC (GRN) vs Faktur Tagihan Vendor.
                        </p>
                    </div>
                </div>

                <div>
                    <button
                        @click="openAuditModal"
                        class="inline-flex items-center gap-2 px-4 py-2.5 bg-purple-600 hover:bg-purple-700 text-white text-sm font-semibold rounded-xl shadow-sm transition-all shadow-purple-500/20"
                    >
                        <Plus class="w-4 h-4" />
                        <span>Audit Faktur Baru</span>
                    </button>
                </div>
            </div>

            <!-- Stats Bar -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                <div class="p-4 bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 shadow-sm flex items-center gap-3">
                    <div class="p-2.5 bg-emerald-50 text-emerald-600 dark:bg-emerald-950/40 rounded-lg">
                        <CheckCircle2 class="w-5 h-5" />
                    </div>
                    <div>
                        <span class="block text-xs font-medium text-slate-400">Audit Klop (Matched)</span>
                        <span class="text-lg font-bold text-slate-900 dark:text-white">{{ stats.total_matched }} Dokumen</span>
                    </div>
                </div>

                <div class="p-4 bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 shadow-sm flex items-center gap-3">
                    <div class="p-2.5 bg-rose-50 text-rose-600 dark:bg-rose-950/40 rounded-lg">
                        <AlertTriangle class="w-5 h-5" />
                    </div>
                    <div>
                        <span class="block text-xs font-medium text-slate-400">Selisih (Discrepant)</span>
                        <span class="text-lg font-bold text-rose-600">{{ stats.total_discrepant }} Kasus</span>
                    </div>
                </div>

                <div class="p-4 bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 shadow-sm flex items-center gap-3">
                    <div class="p-2.5 bg-blue-50 text-blue-600 dark:bg-blue-950/40 rounded-lg">
                        <FileText class="w-5 h-5" />
                    </div>
                    <div>
                        <span class="block text-xs font-medium text-slate-400">Tagihan Terbit (Bills)</span>
                        <span class="text-lg font-bold text-slate-900 dark:text-white">{{ stats.total_bills }} Tagihan</span>
                    </div>
                </div>

                <div class="p-4 bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 shadow-sm flex items-center gap-3">
                    <div class="p-2.5 bg-purple-50 text-purple-600 dark:bg-purple-950/40 rounded-lg">
                        <DollarSign class="w-5 h-5" />
                    </div>
                    <div>
                        <span class="block text-xs font-medium text-slate-400">Total Nilai Tagihan AP</span>
                        <span class="text-lg font-bold text-slate-900 dark:text-white">{{ formatCurrency(stats.total_bill_amount) }}</span>
                    </div>
                </div>
            </div>

            <!-- Matching Audit Table -->
            <div class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-200 dark:border-slate-700 shadow-sm p-6 space-y-4">
                <h2 class="text-base font-bold text-slate-900 dark:text-white">Riwayat Audit Pencocokan 3-Way</h2>

                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs">
                        <thead>
                            <tr class="border-b border-slate-200 dark:border-slate-700 text-slate-400 font-semibold uppercase">
                                <th class="pb-3">No. Audit</th>
                                <th class="pb-3">No. PO & GRN</th>
                                <th class="pb-3">Faktur Vendor</th>
                                <th class="pb-3 text-right">PO Qty</th>
                                <th class="pb-3 text-right text-emerald-600">Lulus QC Qty</th>
                                <th class="pb-3 text-right text-blue-600">Invoice Qty</th>
                                <th class="pb-3 text-right font-bold">Nominal Faktur</th>
                                <th class="pb-3 text-center">Status Audit</th>
                                <th class="pb-3 text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 dark:divide-slate-700/50">
                            <tr v-for="mat in matchings.data" :key="mat.id" class="hover:bg-slate-50 dark:hover:bg-slate-700/30">
                                <td class="py-3.5 font-bold text-purple-600">
                                    {{ mat.matching_number }}
                                </td>
                                <td class="py-3.5">
                                    <div class="font-semibold text-slate-800 dark:text-slate-200">{{ mat.po_number }}</div>
                                    <div class="text-[11px] text-slate-400">{{ mat.goods_receipt?.grn_number }}</div>
                                </td>
                                <td class="py-3.5">
                                    <div class="font-medium text-slate-800 dark:text-slate-200">{{ mat.vendor_invoice_number }}</div>
                                    <div class="text-[11px] text-slate-400">{{ mat.vendor?.name }}</div>
                                </td>
                                <td class="py-3.5 text-right">{{ formatNumber(mat.po_total_qty) }}</td>
                                <td class="py-3.5 text-right font-bold text-emerald-600">{{ formatNumber(mat.gr_passed_qty) }}</td>
                                <td class="py-3.5 text-right font-bold text-blue-600">{{ formatNumber(mat.invoice_qty) }}</td>
                                <td class="py-3.5 text-right font-bold text-slate-900 dark:text-white">
                                    {{ formatCurrency(mat.invoice_amount) }}
                                </td>
                                <td class="py-3.5 text-center">
                                    <span
                                        :class="[
                                            'inline-flex px-2 py-0.5 rounded-full text-[10px] font-bold uppercase',
                                            mat.status === 'matched' || mat.status === 'approved'
                                                ? 'bg-emerald-50 text-emerald-700 dark:bg-emerald-950/40 dark:text-emerald-400'
                                                : 'bg-rose-50 text-rose-700 dark:bg-rose-950/40 dark:text-rose-400'
                                        ]"
                                    >
                                        {{ mat.status === 'matched' ? 'KLOP (MATCH)' : (mat.status === 'approved' ? 'TAGIHAN TERBIT' : 'SELISIH') }}
                                    </span>
                                </td>
                                <td class="py-3.5 text-right">
                                    <button
                                        v-if="mat.status === 'matched'"
                                        @click="createBill(mat.id)"
                                        class="px-2.5 py-1 bg-emerald-600 hover:bg-emerald-700 text-white rounded text-[11px] font-medium"
                                    >
                                        Terbitkan Bill
                                    </button>
                                    <span v-else class="text-[11px] text-slate-400">Sudah Terbit</span>
                                </td>
                            </tr>
                            <tr v-if="!matchings.data || matchings.data.length === 0">
                                <td colspan="9" class="p-8 text-center text-slate-400">
                                    Belum ada audit 3-way matching.
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Vendor Bills List -->
            <div class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-200 dark:border-slate-700 shadow-sm p-6 space-y-4">
                <div class="flex items-center justify-between">
                    <h2 class="text-base font-bold text-slate-900 dark:text-white">Daftar Tagihan Pemasok (Vendor Bills AP)</h2>
                    <span class="text-xs text-slate-500">Siap dicairkan melalui modul Finance Payment Voucher</span>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs">
                        <thead>
                            <tr class="border-b border-slate-200 dark:border-slate-700 text-slate-400 font-semibold uppercase">
                                <th class="pb-3">No. Tagihan (Bill)</th>
                                <th class="pb-3">Vendor Rekanan</th>
                                <th class="pb-3">No. Faktur Vendor</th>
                                <th class="pb-3">Tgl Tagihan & Jatuh Tempo</th>
                                <th class="pb-3 text-right">Subtotal</th>
                                <th class="pb-3 text-right">PPN (11%)</th>
                                <th class="pb-3 text-right font-bold text-slate-900 dark:text-white">Total Tagihan</th>
                                <th class="pb-3 text-center">Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 dark:divide-slate-700/50">
                            <tr v-for="b in vendorBills" :key="b.id" class="hover:bg-slate-50 dark:hover:bg-slate-700/30">
                                <td class="py-3.5 font-bold text-blue-600">
                                    {{ b.bill_number }}
                                </td>
                                <td class="py-3.5 font-semibold text-slate-800 dark:text-slate-200">
                                    {{ b.vendor?.name }}
                                </td>
                                <td class="py-3.5 text-slate-600 dark:text-slate-300">
                                    {{ b.invoice_number }}
                                </td>
                                <td class="py-3.5">
                                    <div class="text-slate-800 dark:text-slate-200">{{ b.bill_date }}</div>
                                    <div class="text-[11px] text-rose-500 font-medium">Due: {{ b.due_date }}</div>
                                </td>
                                <td class="py-3.5 text-right">{{ formatCurrency(b.subtotal) }}</td>
                                <td class="py-3.5 text-right text-slate-500">{{ formatCurrency(b.tax_amount) }}</td>
                                <td class="py-3.5 text-right font-bold text-emerald-600">
                                    {{ formatCurrency(b.total_amount) }}
                                </td>
                                <td class="py-3.5 text-center">
                                    <span class="inline-flex px-2 py-0.5 rounded-full text-[10px] font-bold uppercase bg-emerald-50 text-emerald-700 dark:bg-emerald-950/40 dark:text-emerald-400">
                                        {{ b.status }}
                                    </span>
                                </td>
                            </tr>
                            <tr v-if="!vendorBills || vendorBills.length === 0">
                                <td colspan="8" class="p-8 text-center text-slate-400">
                                    Belum ada tagihan vendor yang disetujui.
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Audit Modal -->
        <div v-if="isModalOpen" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/50 backdrop-blur-sm">
            <div class="bg-white dark:bg-slate-800 rounded-2xl max-w-lg w-full p-6 shadow-xl border border-slate-200 dark:border-slate-700 space-y-4">
                <div class="flex items-center justify-between pb-3 border-b border-slate-100 dark:border-slate-700">
                    <h3 class="font-bold text-slate-900 dark:text-white text-base">Audit 3-Way Matching Faktur Vendor</h3>
                    <button @click="isModalOpen = false" class="text-slate-400 hover:text-slate-600">
                        <X class="w-5 h-5" />
                    </button>
                </div>

                <form @submit.prevent="submitMatching" class="space-y-4 text-xs">
                    <div>
                        <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1">Pilih Penerimaan GRN Lulus QC *</label>
                        <select
                            v-model="form.goods_receipt_id"
                            @change="onReceiptSelect"
                            class="w-full rounded-xl border border-slate-200 dark:border-slate-700 px-3 py-2 text-xs dark:bg-slate-900"
                        >
                            <option v-for="rc in completedReceipts" :key="rc.id" :value="rc.id">
                                {{ rc.grn_number }} - {{ rc.vendor?.name }} (PO: {{ rc.po_number ?? '-' }})
                            </option>
                        </select>
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1">No. Faktur Vendor *</label>
                            <input
                                type="text"
                                v-model="form.vendor_invoice_number"
                                required
                                class="w-full rounded-xl border border-slate-200 dark:border-slate-700 px-3 py-2 text-xs dark:bg-slate-900"
                            />
                        </div>

                        <div>
                            <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1">Kuantitas Ditagihkan *</label>
                            <input
                                type="number"
                                step="0.01"
                                v-model="form.invoice_qty"
                                required
                                class="w-full rounded-xl border border-slate-200 dark:border-slate-700 px-3 py-2 text-xs dark:bg-slate-900 text-right"
                            />
                        </div>
                    </div>

                    <div>
                        <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1">Total Nilai Faktur Tagihan (IDR) *</label>
                        <input
                            type="number"
                            step="0.01"
                            v-model="form.invoice_amount"
                            required
                            class="w-full rounded-xl border border-slate-200 dark:border-slate-700 px-3 py-2 text-xs dark:bg-slate-900 text-right font-bold text-emerald-600"
                        />
                    </div>

                    <div>
                        <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1">Catatan Auditor</label>
                        <input
                            type="text"
                            v-model="form.notes"
                            class="w-full rounded-xl border border-slate-200 dark:border-slate-700 px-3 py-2 text-xs dark:bg-slate-900"
                        />
                    </div>

                    <div class="flex items-center justify-end gap-3 pt-3 border-t border-slate-100 dark:border-slate-700">
                        <button
                            type="button"
                            @click="isModalOpen = false"
                            class="px-4 py-2 border border-slate-200 dark:border-slate-700 text-slate-600 dark:text-slate-300 rounded-xl"
                        >
                            Batal
                        </button>
                        <button
                            type="submit"
                            :disabled="form.processing"
                            class="px-5 py-2 bg-purple-600 hover:bg-purple-700 text-white font-semibold rounded-xl"
                        >
                            {{ form.processing ? 'Memproses Audit...' : 'Jalankan Audit & Buat Tagihan' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </ModuleLayout>
</template>
