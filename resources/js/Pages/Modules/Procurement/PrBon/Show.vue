<script setup>
import { ref } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import ModuleLayout from '@/Layouts/ModuleLayout.vue';
import {
    ArrowLeft,
    Receipt,
    FileText,
    CheckCircle2,
    XCircle,
    RotateCcw,
    AlertCircle,
    Store,
    Calendar,
    ShieldCheck,
} from 'lucide-vue-next';

const props = defineProps({
    module: Object,
    bon: Object,
});

const decisionAction = ref('approve_all'); // 'approve_all' | 'partial' | 'revision' | 'reject_all'
const approvedItemIds = ref(props.bon.items?.map((it) => it.id) || []);
const decisionNote = ref('');
const isSubmitting = ref(false);

const handleDecision = () => {
    isSubmitting.value = true;
    router.post(`/procurement/pr-bons/${props.bon.id}/decision`, {
        action: decisionAction.value,
        approved_item_ids: approvedItemIds.value,
        note: decisionNote.value,
    }, {
        onFinish: () => { isSubmitting.value = false; },
    });
};

const getStatusBadge = (st) => {
    switch (st) {
        case 'waiting_gm': return { label: 'Menunggu Keputusan GM', class: 'bg-amber-100 text-amber-800 font-bold' };
        case 'revision': return { label: 'Perlu Revisi Procurement', class: 'bg-rose-100 text-rose-800 font-bold' };
        case 'approved_gm': return { label: 'Disetujui Penuh GM', class: 'bg-emerald-100 text-emerald-800 font-bold' };
        case 'partial_approved': return { label: 'Disetujui Sebagian GM', class: 'bg-teal-100 text-teal-800 font-bold' };
        case 'rejected_gm': return { label: 'Ditolak GM', class: 'bg-rose-100 text-rose-800 font-bold' };
        default: return { label: st, class: 'bg-slate-100 text-slate-700' };
    }
};
</script>

<template>
    <Head :title="`Berkas Bon ${bon.bon_number} - ERP`" />

    <ModuleLayout :module="module">
        <div class="space-y-6 max-w-5xl mx-auto">
            <!-- Header Card -->
            <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-6 shadow-xs">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                    <div class="flex items-center gap-3">
                        <Link
                            href="/procurement/pr-bons"
                            class="p-2.5 rounded-xl bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-600 dark:text-slate-300 transition-colors"
                        >
                            <ArrowLeft class="w-4 h-4" />
                        </Link>
                        <div>
                            <div class="flex items-center gap-2">
                                <h1 class="text-xl font-bold font-mono text-slate-900 dark:text-white">
                                    {{ bon.bon_number }}
                                </h1>
                                <span :class="['px-2.5 py-1 rounded-full text-xs font-bold', getStatusBadge(bon.status).class]">
                                    {{ getStatusBadge(bon.status).label }}
                                </span>
                            </div>
                            <p class="text-xs text-slate-500 mt-1">
                                Toko / Vendor: <strong>{{ bon.vendor_name }}</strong> &bull; Dibuat oleh {{ bon.creator?.name }}
                            </p>
                        </div>
                    </div>

                    <a
                        :href="`/procurement/pr-bons/${bon.id}/print`"
                        target="_blank"
                        class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl bg-slate-900 text-white dark:bg-white dark:text-slate-900 text-xs font-semibold hover:opacity-90 transition-opacity"
                    >
                        <FileText class="w-4 h-4" />
                        Cetak Form Bon PDF
                    </a>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mt-6 pt-5 border-t border-slate-100 dark:border-slate-800 text-xs">
                    <div>
                        <span class="text-slate-400 block mb-0.5">Tanggal Berkas Bon:</span>
                        <span class="font-bold text-slate-800 dark:text-slate-200">{{ bon.bon_date }}</span>
                    </div>
                    <div>
                        <span class="text-slate-400 block mb-0.5">Total Nominal Belanja Bon:</span>
                        <span class="text-base font-extrabold text-amber-600 dark:text-amber-400">
                            Rp {{ Number(bon.total_amount).toLocaleString('id-ID') }}
                        </span>
                    </div>
                    <div>
                        <span class="text-slate-400 block mb-0.5">Pengesah (General Manager):</span>
                        <span class="font-semibold text-slate-700 dark:text-slate-300">
                            {{ bon.approver?.name || 'Menunggu Tanda Tangan GM' }}
                        </span>
                    </div>
                </div>

                <div v-if="bon.revision_note" class="mt-4 p-3 rounded-xl bg-rose-50 dark:bg-rose-950/40 border border-rose-200 dark:border-rose-900 text-xs text-rose-700 dark:text-rose-300">
                    <strong>Catatan Revisi GM:</strong> {{ bon.revision_note }}
                </div>
            </div>

            <!-- Items in this Bon -->
            <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-6 shadow-xs space-y-4">
                <h2 class="text-sm font-bold text-slate-900 dark:text-white uppercase tracking-wider">
                    Daftar Item dalam Berkas Bon Ini ({{ bon.items?.length || 0 }})
                </h2>

                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="border-b border-slate-200 dark:border-slate-800 text-[11px] font-semibold text-slate-500 uppercase tracking-wider bg-slate-50/50 dark:bg-slate-800/40">
                                <th v-if="decisionAction === 'partial'" class="py-3 px-3 text-center">Setujui?</th>
                                <th class="py-3 px-3">Nomor PR Asal</th>
                                <th class="py-3 px-3">Nama Item & Spesifikasi</th>
                                <th class="py-3 px-3 text-center">Kuantitas</th>
                                <th class="py-3 px-3 text-right">Nominal Bon (Rp)</th>
                                <th class="py-3 px-3">Status Item</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 dark:divide-slate-800 text-xs">
                            <tr v-for="it in bon.items" :key="it.id" class="hover:bg-slate-50/50 dark:hover:bg-slate-800/30">
                                <td v-if="decisionAction === 'partial'" class="py-3 px-3 text-center">
                                    <input
                                        type="checkbox"
                                        :value="it.id"
                                        v-model="approvedItemIds"
                                        class="rounded text-amber-500 focus:ring-amber-500"
                                    />
                                </td>
                                <td class="py-3 px-3 font-semibold text-slate-900 dark:text-white">
                                    {{ it.purchase_request?.pr_number }}
                                    <div class="text-[10px] text-slate-400">{{ it.purchase_request?.department?.name }}</div>
                                </td>
                                <td class="py-3 px-3 font-medium">
                                    {{ it.item_name }}
                                </td>
                                <td class="py-3 px-3 text-center font-bold">
                                    {{ Number(it.quantity) }} {{ it.uom }}
                                </td>
                                <td class="py-3 px-3 text-right font-extrabold text-amber-600">
                                    Rp {{ Number(it.bon_unit_price || it.actual_price).toLocaleString('id-ID') }}
                                </td>
                                <td class="py-3 px-3">
                                    <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-slate-100 text-slate-700 dark:bg-slate-800 dark:text-slate-300">
                                        {{ it.status }}
                                    </span>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- GM Decision Action Panel -->
            <div v-if="['waiting_gm', 'revision'].includes(bon.status)" class="bg-white dark:bg-slate-900 border border-amber-200 dark:border-amber-950/60 rounded-2xl p-6 shadow-xs space-y-4">
                <div class="flex items-center gap-2 text-amber-800 dark:text-amber-400">
                    <ShieldCheck class="w-5 h-5" />
                    <h2 class="text-sm font-bold uppercase tracking-wider">
                        Keputusan Penelaahan General Manager
                    </h2>
                </div>

                <div class="grid grid-cols-2 sm:grid-cols-4 gap-2">
                    <button
                        type="button"
                        @click="decisionAction = 'approve_all'"
                        :class="['p-3 rounded-xl border text-xs font-bold text-center transition-all', decisionAction === 'approve_all' ? 'border-emerald-600 bg-emerald-50 text-emerald-800 dark:bg-emerald-950/40 dark:text-emerald-300' : 'border-slate-200 hover:bg-slate-50 text-slate-700 dark:border-slate-700 dark:text-slate-300']"
                    >
                        Approve Seluruh Item
                    </button>
                    <button
                        type="button"
                        @click="decisionAction = 'partial'"
                        :class="['p-3 rounded-xl border text-xs font-bold text-center transition-all', decisionAction === 'partial' ? 'border-teal-600 bg-teal-50 text-teal-800 dark:bg-teal-950/40 dark:text-teal-300' : 'border-slate-200 hover:bg-slate-50 text-slate-700 dark:border-slate-700 dark:text-slate-300']"
                    >
                        Approve Sebagian
                    </button>
                    <button
                        type="button"
                        @click="decisionAction = 'revision'"
                        :class="['p-3 rounded-xl border text-xs font-bold text-center transition-all', decisionAction === 'revision' ? 'border-amber-600 bg-amber-50 text-amber-800 dark:bg-amber-950/40 dark:text-amber-300' : 'border-slate-200 hover:bg-slate-50 text-slate-700 dark:border-slate-700 dark:text-slate-300']"
                    >
                        Minta Revisi Berkas
                    </button>
                    <button
                        type="button"
                        @click="decisionAction = 'reject_all'"
                        :class="['p-3 rounded-xl border text-xs font-bold text-center transition-all', decisionAction === 'reject_all' ? 'border-rose-600 bg-rose-50 text-rose-800 dark:bg-rose-950/40 dark:text-rose-300' : 'border-slate-200 hover:bg-slate-50 text-slate-700 dark:border-slate-700 dark:text-slate-300']"
                    >
                        Tolak Seluruh Berkas
                    </button>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">
                        Catatan / Instruksi General Manager:
                    </label>
                    <textarea
                        v-model="decisionNote"
                        rows="2"
                        placeholder="Masukkan catatan spesifikasi, negosiasi harga, atau alasan penolakan..."
                        class="w-full text-xs rounded-xl border border-slate-300 dark:border-slate-700 dark:bg-slate-800 p-2.5"
                    ></textarea>
                </div>

                <div class="flex justify-end pt-2">
                    <button
                        type="button"
                        @click="handleDecision"
                        :disabled="isSubmitting"
                        class="px-6 py-2.5 rounded-xl bg-violet-600 hover:bg-violet-700 text-white text-xs font-bold shadow-xs transition-colors"
                    >
                        Eksekusi Keputusan GM
                    </button>
                </div>
            </div>
        </div>
    </ModuleLayout>
</template>
