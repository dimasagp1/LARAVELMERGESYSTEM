<script setup>
import { ref, computed } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import ModuleLayout from '@/Layouts/ModuleLayout.vue';
import {
    ArrowLeft,
    FileText,
    CheckCircle2,
    Clock,
    AlertCircle,
    RotateCcw,
    DollarSign,
    Check,
    X,
    Send,
    Truck,
    ShoppingCart,
    Layers,
    UserCheck,
    Building2,
    Calendar,
    AlertTriangle,
} from 'lucide-vue-next';

const props = defineProps({
    module: Object,
    pr: Object,
});

// Modal states
const activeModal = ref(null); // 'estimate' | 'approve_l1' | 'holding_quote' | 'actual_price' | 'approve_l2' | 'reject' | 'po' | 'note'
const activeItem = ref(null);
const modalLoading = ref(false);

// Form fields for item actions
const estimatePrice = ref(0);
const estimateFlags = ref({
    rekap_po_odoo: true,
    is_incoming: true,
    need_holding_quotation: false,
    need_holding_delivery: false,
    is_bon: false,
});

const approvalNotes = ref('');
const rejectReason = ref('');
const rejectStep = ref('om');

const actualUnitPrice = ref(0);
const actualVendorName = ref('');
const actualDeliveryPlans = ref([{ plan_date: '', plan_quantity: 0, notes: '' }]);

const poNumber = ref('');
const poManualOdoo = ref(false);
const poManualReason = ref('');

const validationNoteText = ref('');

const openModal = (type, item) => {
    activeItem.value = item;
    activeModal.value = type;
    approvalNotes.value = '';
    rejectReason.value = '';

    if (type === 'estimate') {
        estimatePrice.value = Number(item.estimated_price) || 0;
        estimateFlags.value = {
            rekap_po_odoo: item.rekap_po_odoo,
            is_incoming: item.is_incoming,
            need_holding_quotation: item.need_holding_quotation,
            need_holding_delivery: item.need_holding_delivery,
            is_bon: item.is_bon,
        };
    } else if (type === 'actual_price') {
        actualUnitPrice.value = Number(item.actual_price) ? (Number(item.actual_price) / (Number(item.quantity) || 1)) : (Number(item.estimated_price) / (Number(item.quantity) || 1));
        actualVendorName.value = item.vendor_name || '';
        actualDeliveryPlans.value = item.delivery_plans?.length
            ? item.delivery_plans.map((p) => ({ plan_date: p.plan_date, plan_quantity: p.plan_quantity, notes: p.notes }))
            : [{ plan_date: new Date().toISOString().split('T')[0], plan_quantity: item.quantity, notes: 'Pengiriman utama' }];
    } else if (type === 'po') {
        poNumber.value = item.po_number || '';
        poManualOdoo.value = false;
        poManualReason.value = '';
    }
};

const closeModal = () => {
    activeModal.value = null;
    activeItem.value = null;
    modalLoading.value = false;
};

// Actions execution
const submitEstimate = () => {
    modalLoading.value = true;
    router.post(`/procurement/items/${activeItem.value.id}/estimate`, {
        estimated_price: estimatePrice.value,
        ...estimateFlags.value,
    }, {
        onSuccess: () => closeModal(),
        onError: () => { modalLoading.value = false; },
    });
};

const submitApproveL1 = () => {
    modalLoading.value = true;
    router.post(`/procurement/items/${activeItem.value.id}/approve-l1`, {
        notes: approvalNotes.value,
    }, {
        onSuccess: () => closeModal(),
        onError: () => { modalLoading.value = false; },
    });
};

const submitHoldingQuote = () => {
    modalLoading.value = true;
    router.post(`/procurement/items/${activeItem.value.id}/holding-quote`, {
        notes: approvalNotes.value || 'Quotation Holding disetujui',
    }, {
        onSuccess: () => closeModal(),
        onError: () => { modalLoading.value = false; },
    });
};

const submitActualPrice = () => {
    modalLoading.value = true;
    router.post(`/procurement/items/${activeItem.value.id}/actual-price`, {
        actual_unit_price: actualUnitPrice.value,
        vendor_name: actualVendorName.value,
        delivery_plans: actualDeliveryPlans.value,
    }, {
        onSuccess: () => closeModal(),
        onError: () => { modalLoading.value = false; },
    });
};

const submitApproveL2 = () => {
    modalLoading.value = true;
    router.post(`/procurement/items/${activeItem.value.id}/approve-l2`, {
        notes: approvalNotes.value,
    }, {
        onSuccess: () => closeModal(),
        onError: () => { modalLoading.value = false; },
    });
};

const submitReject = () => {
    modalLoading.value = true;
    router.post(`/procurement/items/${activeItem.value.id}/reject`, {
        reason: rejectReason.value,
        step: rejectStep.value,
    }, {
        onSuccess: () => closeModal(),
        onError: () => { modalLoading.value = false; },
    });
};

const submitPo = () => {
    modalLoading.value = true;
    router.post(`/procurement/items/${activeItem.value.id}/process-po`, {
        po_number: poNumber.value,
        po_manual_odoo: poManualOdoo.value,
        po_manual_reason: poManualReason.value,
    }, {
        onSuccess: () => closeModal(),
        onError: () => { modalLoading.value = false; },
    });
};

const submitNote = () => {
    modalLoading.value = true;
    router.post(`/procurement/items/${activeItem.value.id}/send-note`, {
        note: validationNoteText.value,
    }, {
        onSuccess: () => closeModal(),
        onError: () => { modalLoading.value = false; },
    });
};

// Bulk Revision
const rejectedItems = computed(() => {
    return props.pr.items?.filter((it) => ['rejected_om', 'rejected_gm', 'rejected_proc'].includes(it.status)) || [];
});

const submitBulkRevise = () => {
    if (!confirm(`Buat PR draft revisi baru untuk ${rejectedItems.value.length} item yang ditolak?`)) return;
    router.post(`/procurement/purchase-requests/${props.pr.id}/bulk-revise`, {
        rejected_item_ids: rejectedItems.value.map((i) => i.id),
    });
};

const getItemBadge = (status) => {
    switch (status) {
        case 'draft': return { label: '1. Draft', class: 'bg-slate-100 text-slate-700' };
        case 'pending_estimate': return { label: '2. Estimasi Harga', class: 'bg-amber-100 text-amber-800' };
        case 'pending': return { label: '3. Approval L1', class: 'bg-blue-100 text-blue-800' };
        case 'waiting_quotation_holding': return { label: '4. Holding Quote', class: 'bg-indigo-100 text-indigo-800' };
        case 'waiting_actual_price': return { label: '5. Input Actual', class: 'bg-purple-100 text-purple-800' };
        case 'waiting_gm': return { label: '6. Approval GM', class: 'bg-violet-100 text-violet-800 font-bold' };
        case 'waiting_bon': return { label: 'Staging Bon', class: 'bg-amber-100 text-amber-900 font-bold' };
        case 'waiting_gm_bon': return { label: 'Berkas Bon GM', class: 'bg-amber-200 text-amber-900 font-bold' };
        case 'ready_to_po': return { label: '7. Ready to PO', class: 'bg-teal-100 text-teal-800 font-bold' };
        case 'ordered': return { label: '8. PO Terbit', class: 'bg-emerald-100 text-emerald-800' };
        case 'delivered': return { label: 'Tiba di Pabrik', class: 'bg-emerald-200 text-emerald-900 font-extrabold' };
        case 'completed': return { label: 'Selesai', class: 'bg-slate-200 text-slate-800' };
        case 'rejected_om':
        case 'rejected_gm':
        case 'rejected_proc':
            return { label: 'Ditolak', class: 'bg-rose-100 text-rose-800 font-bold' };
        default: return { label: status, class: 'bg-slate-100 text-slate-700' };
    }
};
</script>

<template>
    <Head :title="`Detail PR ${pr.pr_number} - ERP`" />

    <ModuleLayout :module="module">
        <div class="space-y-6">
            <!-- Header Card -->
            <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-6 shadow-xs">
                <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
                    <div class="flex items-start gap-3">
                        <Link
                            href="/procurement/purchase-requests"
                            class="p-2.5 rounded-xl bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-600 dark:text-slate-300 transition-colors mt-0.5"
                        >
                            <ArrowLeft class="w-4 h-4" />
                        </Link>
                        <div>
                            <div class="flex items-center gap-2">
                                <h1 class="text-xl font-extrabold text-slate-900 dark:text-white">
                                    {{ pr.pr_number }}
                                </h1>
                                <span v-if="pr.revision_count > 0" class="px-2 py-0.5 rounded text-xs font-bold bg-amber-100 text-amber-800">
                                    Revisi #{{ pr.revision_count }}
                                </span>
                                <span class="px-2.5 py-1 rounded-full text-xs font-bold uppercase tracking-wider bg-violet-50 text-violet-700 dark:bg-violet-950/60 dark:text-violet-300">
                                    {{ pr.pr_type }}
                                </span>
                            </div>
                            <p class="text-xs text-slate-500 mt-1">
                                Diajukan oleh <strong>{{ pr.user?.name }}</strong> ({{ pr.department?.name }}) &bull; Tanggal: {{ pr.request_date }}
                            </p>
                        </div>
                    </div>

                    <div class="flex flex-wrap items-center gap-2">
                        <!-- Bulk Revision Action if any item is rejected -->
                        <button
                            v-if="rejectedItems.length > 0"
                            @click="submitBulkRevise"
                            class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl bg-amber-500 hover:bg-amber-600 text-white text-xs font-bold shadow-xs transition-colors"
                        >
                            <RotateCcw class="w-4 h-4" />
                            Revisi {{ rejectedItems.length }} Item Ditolak (Bulk Revision)
                        </button>
                        <a
                            :href="`/procurement/purchase-requests/${pr.id}/pdf`"
                            target="_blank"
                            class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl bg-slate-900 dark:bg-white text-white dark:text-slate-900 text-xs font-semibold hover:opacity-90 transition-opacity"
                        >
                            <FileText class="w-4 h-4" />
                            Cetak PDF
                        </a>
                    </div>
                </div>

                <!-- Meta Details -->
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mt-6 pt-5 border-t border-slate-100 dark:border-slate-800 text-xs">
                    <div>
                        <span class="text-slate-400 block mb-0.5">Tujuan Pengeluaran (FAT Purpose):</span>
                        <span class="font-bold text-slate-800 dark:text-slate-200">{{ pr.purpose }}</span>
                    </div>
                    <div>
                        <span class="text-slate-400 block mb-0.5">Total Nilai Pengadaan:</span>
                        <span class="text-sm font-extrabold text-violet-600 dark:text-violet-400">
                            Rp {{ Number(pr.total_amount).toLocaleString('id-ID') }}
                        </span>
                    </div>
                    <div>
                        <span class="text-slate-400 block mb-0.5">Status Pagu Anggaran:</span>
                        <span v-if="pr.bypass_budget_check" class="font-bold text-rose-600">
                            BYPASS AKTIF (Alasan: {{ pr.bypass_budget_reason }})
                        </span>
                        <span v-else class="font-semibold text-emerald-600">
                            Validasi Pagu FAT Berjalan
                        </span>
                    </div>
                </div>
            </div>

            <!-- Granular Items Table -->
            <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-6 shadow-xs space-y-4">
                <div class="flex items-center justify-between">
                    <div>
                        <h2 class="text-sm font-bold text-slate-900 dark:text-white uppercase tracking-wider">
                            Rincian Item & Granular 8-Step Workflow
                        </h2>
                        <p class="text-xs text-slate-500">
                            Setiap baris item dapat diproses secara independen tanpa menahan item lain
                        </p>
                    </div>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="border-b border-slate-200 dark:border-slate-800 text-[11px] font-semibold text-slate-500 uppercase tracking-wider bg-slate-50/50 dark:bg-slate-800/40">
                                <th class="py-3 px-3">Item & Spek</th>
                                <th class="py-3 px-3 text-center">Qty / UOM</th>
                                <th class="py-3 px-3 text-right">Estimasi</th>
                                <th class="py-3 px-3 text-right">Harga Aktual / Bon</th>
                                <th class="py-3 px-3">Vendor / PO</th>
                                <th class="py-3 px-3">Status Tahapan</th>
                                <th class="py-3 px-3 text-right">Aksi Workflow</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 dark:divide-slate-800 text-xs">
                            <tr
                                v-for="item in pr.items"
                                :key="item.id"
                                class="hover:bg-slate-50/60 dark:hover:bg-slate-800/30 transition-colors"
                            >
                                <td class="py-3 px-3">
                                    <div class="font-bold text-slate-900 dark:text-white">{{ item.item_name }}</div>
                                    <div v-if="item.item_code" class="text-[10px] text-slate-400">Kode: {{ item.item_code }}</div>
                                    <div v-if="item.validation_notes" class="text-[10px] text-amber-600 font-semibold mt-0.5">
                                        Catatan: {{ item.validation_notes }}
                                    </div>
                                    <div v-if="item.rejection_reason" class="text-[10px] text-rose-600 font-bold mt-0.5">
                                        Alasan Tolak: {{ item.rejection_reason }}
                                    </div>
                                </td>
                                <td class="py-3 px-3 text-center font-bold">
                                    {{ Number(item.quantity) }} {{ item.uom }}
                                    <div v-if="item.ready_quantity > 0" class="text-[10px] text-emerald-600 font-normal">
                                        Tiba: {{ Number(item.ready_quantity) }}
                                    </div>
                                </td>
                                <td class="py-3 px-3 text-right text-slate-600 dark:text-slate-400">
                                    Rp {{ Number(item.estimated_price).toLocaleString('id-ID') }}
                                </td>
                                <td class="py-3 px-3 text-right font-bold text-slate-900 dark:text-white">
                                    Rp {{ Number(item.actual_price || item.bon_unit_price || item.estimated_price).toLocaleString('id-ID') }}
                                    <span v-if="item.is_bon" class="block text-[9px] text-amber-600 font-bold uppercase">
                                        (Jalur Bon)
                                    </span>
                                </td>
                                <td class="py-3 px-3">
                                    <div class="font-medium text-slate-800 dark:text-slate-200">
                                        {{ item.vendor_name || '-' }}
                                    </div>
                                    <div v-if="item.po_number" class="text-[11px] font-mono text-violet-600 dark:text-violet-400 font-bold">
                                        {{ item.po_number }}
                                    </div>
                                </td>
                                <td class="py-3 px-3">
                                    <span :class="['px-2.5 py-1 rounded-full text-[10px] font-bold inline-block', getItemBadge(item.status).class]">
                                        {{ getItemBadge(item.status).label }}
                                    </span>
                                </td>
                                <td class="py-3 px-3 text-right">
                                    <div class="flex items-center justify-end gap-1.5 flex-wrap">
                                        <!-- Step 2: Procurement Estimasi -->
                                        <button
                                            v-if="['draft', 'pending_estimate'].includes(item.status)"
                                            @click="openModal('estimate', item)"
                                            class="px-2.5 py-1 rounded-lg bg-amber-500 hover:bg-amber-600 text-white text-[11px] font-semibold"
                                        >
                                            Input Estimasi
                                        </button>

                                        <!-- Step 3: Approval L1 (OM / FAT) -->
                                        <template v-if="item.status === 'pending'">
                                            <button
                                                @click="openModal('approve_l1', item)"
                                                class="px-2.5 py-1 rounded-lg bg-emerald-600 hover:bg-emerald-700 text-white text-[11px] font-semibold"
                                            >
                                                Approve L1
                                            </button>
                                            <button
                                                @click="openModal('reject', item); rejectStep = 'om';"
                                                class="px-2 py-1 rounded-lg bg-rose-50 text-rose-600 hover:bg-rose-100 text-[11px] font-semibold"
                                            >
                                                Tolak
                                            </button>
                                        </template>

                                        <!-- Step 4: Holding Quotation -->
                                        <button
                                            v-if="item.status === 'waiting_quotation_holding'"
                                            @click="openModal('holding_quote', item)"
                                            class="px-2.5 py-1 rounded-lg bg-indigo-600 hover:bg-indigo-700 text-white text-[11px] font-semibold"
                                        >
                                            Lampirkan Quote
                                        </button>

                                        <!-- Step 5: Input Actual Price & Plan -->
                                        <button
                                            v-if="item.status === 'waiting_actual_price'"
                                            @click="openModal('actual_price', item)"
                                            class="px-2.5 py-1 rounded-lg bg-purple-600 hover:bg-purple-700 text-white text-[11px] font-semibold"
                                        >
                                            Input Actual & Plan
                                        </button>

                                        <!-- Step 6: Approval L2 (GM) -->
                                        <template v-if="item.status === 'waiting_gm'">
                                            <button
                                                @click="openModal('approve_l2', item)"
                                                class="px-2.5 py-1 rounded-lg bg-violet-600 hover:bg-violet-700 text-white text-[11px] font-semibold"
                                            >
                                                Approve GM
                                            </button>
                                            <button
                                                @click="openModal('reject', item); rejectStep = 'gm';"
                                                class="px-2 py-1 rounded-lg bg-rose-50 text-rose-600 hover:bg-rose-100 text-[11px] font-semibold"
                                            >
                                                Tolak
                                            </button>
                                        </template>

                                        <!-- Waiting Bon -->
                                        <Link
                                            v-if="item.status === 'waiting_bon'"
                                            href="/procurement/pr-bons/staging"
                                            class="px-2.5 py-1 rounded-lg bg-amber-500 hover:bg-amber-600 text-white text-[11px] font-semibold"
                                        >
                                            Ke Staging Bon
                                        </Link>

                                        <!-- Step 7: Process PO -->
                                        <button
                                            v-if="item.status === 'ready_to_po'"
                                            @click="openModal('po', item)"
                                            class="px-2.5 py-1 rounded-lg bg-teal-600 hover:bg-teal-700 text-white text-[11px] font-semibold"
                                        >
                                            Proses PO
                                        </button>

                                        <!-- Step 8: Logistik Delivery Link -->
                                        <Link
                                            v-if="['ordered', 'delivered'].includes(item.status)"
                                            href="/procurement/deliveries"
                                            class="px-2.5 py-1 rounded-lg bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 text-slate-700 dark:text-slate-300 text-[11px] font-semibold"
                                        >
                                            Cek Delivery
                                        </Link>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Dynamic Action Modals -->
        <div v-if="activeModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-xs">
            <div class="bg-white dark:bg-slate-900 rounded-2xl max-w-lg w-full p-6 shadow-2xl border border-slate-200 dark:border-slate-800">
                <!-- Modal Estimasi -->
                <div v-if="activeModal === 'estimate'" class="space-y-4">
                    <h3 class="text-sm font-bold text-slate-900 dark:text-white">
                        Input Estimasi & Flag Alur Kerja: {{ activeItem?.item_name }}
                    </h3>
                    <div>
                        <label class="block text-xs font-semibold mb-1">Total Estimasi Harga (Rp):</label>
                        <input
                            v-model.number="estimatePrice"
                            type="number"
                            class="w-full text-xs rounded-xl border border-slate-300 dark:border-slate-700 dark:bg-slate-800 p-2.5 font-bold"
                        />
                    </div>
                    <div class="space-y-2 pt-2 border-t border-slate-100 dark:border-slate-800 text-xs">
                        <label class="flex items-center gap-2 cursor-pointer font-medium">
                            <input v-model="estimateFlags.is_bon" type="checkbox" class="rounded text-amber-600" />
                            <span>Jalur Pengadaan Bon Sementara (Kas Kecil / Offline)</span>
                        </label>
                        <label class="flex items-center gap-2 cursor-pointer font-medium">
                            <input v-model="estimateFlags.rekap_po_odoo" type="checkbox" class="rounded text-violet-600" />
                            <span>Sinkronisasi Rekap PO ke Odoo ERP</span>
                        </label>
                        <label class="flex items-center gap-2 cursor-pointer font-medium">
                            <input v-model="estimateFlags.need_holding_quotation" type="checkbox" class="rounded text-indigo-600" />
                            <span>Perlu Lampiran Penawaran Holding</span>
                        </label>
                    </div>
                    <div class="flex justify-end gap-2 pt-3">
                        <button @click="closeModal" class="px-4 py-2 rounded-xl text-xs font-semibold text-slate-600">Batal</button>
                        <button @click="submitEstimate" :disabled="modalLoading" class="px-4 py-2 rounded-xl text-xs font-semibold bg-amber-500 hover:bg-amber-600 text-white">Simpan Estimasi</button>
                    </div>
                </div>

                <!-- Modal Approve L1 -->
                <div v-else-if="activeModal === 'approve_l1'" class="space-y-4">
                    <h3 class="text-sm font-bold text-slate-900 dark:text-white">
                        Persetujuan Level 1 (OM / FAT): {{ activeItem?.item_name }}
                    </h3>
                    <p class="text-xs text-slate-500">
                        Kuantitas: {{ activeItem?.quantity }} {{ activeItem?.uom }} &bull; Estimasi: Rp {{ Number(activeItem?.estimated_price).toLocaleString('id-ID') }}
                    </p>
                    <div>
                        <label class="block text-xs font-semibold mb-1">Catatan Persetujuan (Opsional):</label>
                        <textarea v-model="approvalNotes" rows="3" class="w-full text-xs rounded-xl border border-slate-300 dark:border-slate-700 dark:bg-slate-800 p-2.5"></textarea>
                    </div>
                    <div class="flex justify-end gap-2 pt-3">
                        <button @click="closeModal" class="px-4 py-2 rounded-xl text-xs font-semibold text-slate-600">Batal</button>
                        <button @click="submitApproveL1" :disabled="modalLoading" class="px-4 py-2 rounded-xl text-xs font-semibold bg-emerald-600 hover:bg-emerald-700 text-white">Setujui Level 1</button>
                    </div>
                </div>

                <!-- Modal Actual Price & Plans -->
                <div v-else-if="activeModal === 'actual_price'" class="space-y-4">
                    <h3 class="text-sm font-bold text-slate-900 dark:text-white">
                        Input Harga Aktual & Rencana Kedatangan
                    </h3>
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-semibold mb-1">Harga Satuan Aktual (Rp):</label>
                            <input v-model.number="actualUnitPrice" type="number" class="w-full text-xs rounded-xl border border-slate-300 dark:border-slate-700 dark:bg-slate-800 p-2 font-bold" />
                        </div>
                        <div>
                            <label class="block text-xs font-semibold mb-1">Nama Vendor Pemenang:</label>
                            <input v-model="actualVendorName" type="text" placeholder="PT Supplier Kimia..." class="w-full text-xs rounded-xl border border-slate-300 dark:border-slate-700 dark:bg-slate-800 p-2" />
                        </div>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold mb-1">Rencana Tanggal Kedatangan:</label>
                        <input v-model="actualDeliveryPlans[0].plan_date" type="date" class="w-full text-xs rounded-xl border border-slate-300 dark:border-slate-700 dark:bg-slate-800 p-2" />
                    </div>
                    <div class="flex justify-end gap-2 pt-3">
                        <button @click="closeModal" class="px-4 py-2 rounded-xl text-xs font-semibold text-slate-600">Batal</button>
                        <button @click="submitActualPrice" :disabled="modalLoading" class="px-4 py-2 rounded-xl text-xs font-semibold bg-purple-600 hover:bg-purple-700 text-white">Simpan Harga & Rencana</button>
                    </div>
                </div>

                <!-- Modal Approve L2 (GM) -->
                <div v-else-if="activeModal === 'approve_l2'" class="space-y-4">
                    <h3 class="text-sm font-bold text-slate-900 dark:text-white">
                        Persetujuan Level 2 (General Manager)
                    </h3>
                    <p class="text-xs text-slate-500">
                        Item: {{ activeItem?.item_name }} &bull; Nilai Aktual: Rp {{ Number(activeItem?.actual_price).toLocaleString('id-ID') }}
                    </p>
                    <div>
                        <label class="block text-xs font-semibold mb-1">Instruksi GM:</label>
                        <textarea v-model="approvalNotes" rows="3" placeholder="Persetujuan final penerbitan PO..." class="w-full text-xs rounded-xl border border-slate-300 dark:border-slate-700 dark:bg-slate-800 p-2.5"></textarea>
                    </div>
                    <div class="flex justify-end gap-2 pt-3">
                        <button @click="closeModal" class="px-4 py-2 rounded-xl text-xs font-semibold text-slate-600">Batal</button>
                        <button @click="submitApproveL2" :disabled="modalLoading" class="px-4 py-2 rounded-xl text-xs font-semibold bg-violet-600 hover:bg-violet-700 text-white">Setujui GM (Ready to PO)</button>
                    </div>
                </div>

                <!-- Modal Rejection -->
                <div v-else-if="activeModal === 'reject'" class="space-y-4">
                    <h3 class="text-sm font-bold text-rose-600">
                        Tolak Item: {{ activeItem?.item_name }}
                    </h3>
                    <div>
                        <label class="block text-xs font-semibold mb-1">Alasan Penolakan (Wajib):</label>
                        <textarea v-model="rejectReason" rows="3" placeholder="Jelaskan alasan penolakan atau instruksi perbaikan..." class="w-full text-xs rounded-xl border border-rose-300 dark:border-rose-800 dark:bg-slate-800 p-2.5 text-xs" required></textarea>
                    </div>
                    <div class="flex justify-end gap-2 pt-3">
                        <button @click="closeModal" class="px-4 py-2 rounded-xl text-xs font-semibold text-slate-600">Batal</button>
                        <button @click="submitReject" :disabled="modalLoading || !rejectReason" class="px-4 py-2 rounded-xl text-xs font-semibold bg-rose-600 hover:bg-rose-700 text-white">Konfirmasi Penolakan</button>
                    </div>
                </div>

                <!-- Modal Process PO -->
                <div v-else-if="activeModal === 'po'" class="space-y-4">
                    <h3 class="text-sm font-bold text-slate-900 dark:text-white">
                        Penerbitan Purchase Order: {{ activeItem?.item_name }}
                    </h3>
                    <div>
                        <label class="block text-xs font-semibold mb-1">Nomor PO:</label>
                        <input v-model="poNumber" type="text" placeholder="Kosongkan untuk auto-generate internal PO" class="w-full text-xs rounded-xl border border-slate-300 dark:border-slate-700 dark:bg-slate-800 p-2.5 font-mono" />
                    </div>
                    <div class="p-3 rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 text-xs space-y-2">
                        <label class="flex items-center gap-2 cursor-pointer font-semibold">
                            <input v-model="poManualOdoo" type="checkbox" class="rounded text-teal-600" />
                            <span>Input PO Manual Odoo (Bypass Offline Odoo)</span>
                        </label>
                        <input v-if="poManualOdoo" v-model="poManualReason" type="text" placeholder="Alasan input manual..." class="w-full text-xs rounded-lg border border-slate-300 dark:border-slate-700 p-2" />
                    </div>
                    <div class="flex justify-end gap-2 pt-3">
                        <button @click="closeModal" class="px-4 py-2 rounded-xl text-xs font-semibold text-slate-600">Batal</button>
                        <button @click="submitPo" :disabled="modalLoading" class="px-4 py-2 rounded-xl text-xs font-semibold bg-teal-600 hover:bg-teal-700 text-white">Terbitkan PO</button>
                    </div>
                </div>
            </div>
        </div>
    </ModuleLayout>
</template>
