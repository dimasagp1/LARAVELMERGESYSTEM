<script setup>
import { ref } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import ModuleLayout from '@/Layouts/ModuleLayout.vue';
import {
    FileSpreadsheet,
    ArrowLeft,
    CheckCircle2,
    AlertCircle,
    Download,
    Send,
    Check,
    X,
    RotateCcw,
    ShieldCheck,
    User,
    Calendar,
    Tag,
    Clock
} from 'lucide-vue-next';

const props = defineProps({
    module: Object,
    journal: Object,
});

const rejectModalOpen = ref(false);
const rejectionReason = ref('');
const loading = ref(false);

const submitToChecker = () => {
    if (confirm('Ajukan jurnal ini kepada Checker untuk diverifikasi?')) {
        router.post(`/finance/aje/${props.journal.id}/submit`);
    }
};

const approveJournal = () => {
    if (confirm('Setujui Jurnal Penyesuaian ini? Transaksi akan otomatis mempengaruhi Kertas Kerja Trial Balance & Laporan Keuangan.')) {
        router.post(`/finance/aje/${props.journal.id}/approve`);
    }
};

const rejectJournal = () => {
    if (!rejectionReason.value.trim()) {
        alert('Mohon masukkan alasan penolakan.');
        return;
    }
    loading.value = true;
    router.post(`/finance/aje/${props.journal.id}/reject`, {
        reason: rejectionReason.value,
    }, {
        onSuccess: () => {
            rejectModalOpen.value = false;
        },
        onFinish: () => {
            loading.value = false;
        },
    });
};

const reverseJournal = () => {
    if (confirm('Batalkan (Reverse) jurnal ini? Saldo penyesuaian akan dikeluarkan dari Kertas Kerja Trial Balance.')) {
        router.post(`/finance/aje/${props.journal.id}/reverse`);
    }
};

const formatCurrency = (val) => {
    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        maximumFractionDigits: 0,
    }).format(val || 0);
};

const getStatusBadge = (st) => {
    switch (st) {
        case 'draft':
            return { label: 'DRAFT', class: 'bg-slate-100 text-slate-700 dark:bg-slate-800 dark:text-slate-300' };
        case 'submitted':
            return { label: 'SUBMITTED TO CHECKER', class: 'bg-amber-100 text-amber-800 dark:bg-amber-900/40 dark:text-amber-300' };
        case 'approved':
            return { label: 'APPROVED (ACTIVE IN TRIAL BALANCE)', class: 'bg-emerald-100 text-emerald-800 dark:bg-emerald-900/40 dark:text-emerald-300' };
        case 'rejected':
            return { label: 'REJECTED', class: 'bg-rose-100 text-rose-800 dark:bg-rose-900/40 dark:text-rose-300' };
        case 'reversed':
            return { label: 'REVERSED', class: 'bg-purple-100 text-purple-800 dark:bg-purple-900/40 dark:text-purple-300' };
        default:
            return { label: st?.toUpperCase() || 'UNKNOWN', class: 'bg-slate-100 text-slate-600' };
    }
};
</script>

<template>
    <Head :title="`AJE ${journal.journal_number}`" />

    <ModuleLayout :module="module">
        <div class="space-y-6">
            <!-- Header -->
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div class="flex items-center gap-3">
                    <Link
                        href="/finance/aje"
                        class="p-2 rounded-xl border border-slate-300 dark:border-slate-700 hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors"
                    >
                        <ArrowLeft class="w-5 h-5 text-slate-600 dark:text-slate-400" />
                    </Link>
                    <div>
                        <div class="flex items-center gap-2">
                            <h1 class="text-2xl font-bold tracking-tight text-slate-900 dark:text-white">
                                {{ journal.journal_number }}
                            </h1>
                            <span :class="['px-2.5 py-0.5 rounded-full text-xs font-bold tracking-wide', getStatusBadge(journal.status).class]">
                                {{ getStatusBadge(journal.status).label }}
                            </span>
                        </div>
                        <p class="text-xs text-slate-500 mt-1">
                            Periode: {{ journal.period?.period_name }} &bull; Tanggal: {{ journal.journal_date }}
                        </p>
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="flex flex-wrap items-center gap-2">
                    <a
                        :href="`/finance/aje/${journal.id}/pdf`"
                        target="_blank"
                        class="inline-flex items-center gap-1.5 px-3.5 py-2 border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 hover:bg-slate-50 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200 text-xs font-semibold rounded-xl transition-colors"
                    >
                        <Download class="w-4 h-4" />
                        <span>Unduh PDF AJE</span>
                    </a>

                    <!-- Draft -> Submit -->
                    <button
                        v-if="journal.status === 'draft'"
                        type="button"
                        @click="submitToChecker"
                        class="inline-flex items-center gap-1.5 px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-semibold rounded-xl shadow-md transition-colors"
                    >
                        <Send class="w-4 h-4" />
                        <span>Ajukan ke Checker</span>
                    </button>

                    <!-- Submitted -> Checker Decision -->
                    <template v-if="journal.status === 'submitted'">
                        <button
                            type="button"
                            @click="rejectModalOpen = true"
                            class="inline-flex items-center gap-1.5 px-3.5 py-2 bg-rose-50 text-rose-700 dark:bg-rose-950/40 dark:text-rose-300 hover:bg-rose-100 border border-rose-200 dark:border-rose-800 text-xs font-semibold rounded-xl transition-colors"
                        >
                            <X class="w-4 h-4" />
                            <span>Tolak Jurnal</span>
                        </button>
                        <button
                            type="button"
                            @click="approveJournal"
                            class="inline-flex items-center gap-1.5 px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-semibold rounded-xl shadow-md transition-colors"
                        >
                            <Check class="w-4 h-4" />
                            <span>Setujui (Approve)</span>
                        </button>
                    </template>

                    <!-- Approved -> Reverse -->
                    <button
                        v-if="journal.status === 'approved'"
                        type="button"
                        @click="reverseJournal"
                        class="inline-flex items-center gap-1.5 px-3.5 py-2 bg-purple-50 text-purple-700 dark:bg-purple-950/40 dark:text-purple-300 hover:bg-purple-100 border border-purple-200 dark:border-purple-800 text-xs font-semibold rounded-xl transition-colors"
                    >
                        <RotateCcw class="w-4 h-4" />
                        <span>Batalkan (Reverse)</span>
                    </button>
                </div>
            </div>

            <!-- Rejection Warning Banner -->
            <div
                v-if="journal.status === 'rejected'"
                class="bg-rose-50 dark:bg-rose-950/40 border border-rose-200 dark:border-rose-900 rounded-xl p-4 text-xs text-rose-800 dark:text-rose-200 flex items-start gap-3"
            >
                <AlertCircle class="w-5 h-5 text-rose-500 shrink-0 mt-0.5" />
                <div>
                    <span class="font-bold">Catatan Penolakan oleh Checker:</span>
                    <p class="mt-0.5">{{ journal.rejection_reason || 'Tidak memenuhi kriteria kelengkapan dokumen pendukung.' }}</p>
                </div>
            </div>

            <!-- Summary Info Cards -->
            <div class="grid grid-cols-1 sm:grid-cols-4 gap-4">
                <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl p-4 shadow-sm">
                    <span class="text-xs text-slate-400 block">Kategori</span>
                    <div class="font-bold text-sm text-slate-800 dark:text-slate-100 capitalize mt-1">
                        {{ journal.category }}
                    </div>
                </div>

                <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl p-4 shadow-sm">
                    <span class="text-xs text-slate-400 block">Maker (Dibuat Oleh)</span>
                    <div class="font-bold text-sm text-slate-800 dark:text-slate-100 mt-1 flex items-center gap-1.5">
                        <User class="w-4 h-4 text-slate-400" />
                        <span>{{ journal.creator?.name || 'Staff Finance' }}</span>
                    </div>
                </div>

                <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl p-4 shadow-sm">
                    <span class="text-xs text-slate-400 block">Checker (Diverifikasi Oleh)</span>
                    <div class="font-bold text-sm text-slate-800 dark:text-slate-100 mt-1 flex items-center gap-1.5">
                        <ShieldCheck class="w-4 h-4" :class="journal.approver ? 'text-emerald-500' : 'text-slate-400'" />
                        <span>{{ journal.approver?.name || 'Belum Diverifikasi' }}</span>
                    </div>
                </div>

                <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl p-4 shadow-sm">
                    <span class="text-xs text-slate-400 block">Total Nilai Penyesuaian</span>
                    <div class="font-bold text-base text-emerald-600 dark:text-emerald-400 font-mono mt-1">
                        {{ formatCurrency(journal.total_debit) }}
                    </div>
                </div>
            </div>

            <!-- Description Card -->
            <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl p-5 shadow-sm space-y-1">
                <span class="text-xs font-semibold text-slate-500">Dasar / Uraian Penyesuaian:</span>
                <p class="text-sm text-slate-800 dark:text-slate-200 leading-relaxed">
                    {{ journal.description }}
                </p>
            </div>

            <!-- Double-Entry Lines Table -->
            <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl shadow-sm overflow-hidden">
                <div class="p-4 border-b border-slate-200 dark:border-slate-800 flex items-center justify-between">
                    <h3 class="font-bold text-slate-900 dark:text-white text-sm">
                        Rincian Baris Jurnal Penyesuaian ({{ journal.lines?.length || 0 }} Pos)
                    </h3>
                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-xs font-semibold bg-emerald-100 text-emerald-800 dark:bg-emerald-900/40 dark:text-emerald-300">
                        <CheckCircle2 class="w-3.5 h-3.5" />
                        <span>Debet = Kredit Seimbang</span>
                    </span>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs">
                        <thead class="bg-slate-50 dark:bg-slate-800/60 font-semibold text-slate-500 uppercase border-b border-slate-200 dark:border-slate-800">
                            <tr>
                                <th class="p-3 w-36">Kode Akun</th>
                                <th class="p-3">Nama Akun</th>
                                <th class="p-3 w-48">Pos Laporan (FSLI)</th>
                                <th class="p-3 w-40">Departemen</th>
                                <th class="p-3 text-right w-36">Debet (Rp)</th>
                                <th class="p-3 text-right w-36">Kredit (Rp)</th>
                                <th class="p-3">Keterangan</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-200 dark:divide-slate-800">
                            <tr v-for="line in journal.lines" :key="line.id" class="hover:bg-slate-50 dark:hover:bg-slate-800/40">
                                <td class="p-3 font-mono font-semibold text-slate-900 dark:text-white">
                                    {{ line.account_code }}
                                </td>
                                <td class="p-3 font-medium text-slate-800 dark:text-slate-200">
                                    {{ line.account_name }}
                                </td>
                                <td class="p-3 text-slate-600 dark:text-slate-400">
                                    {{ line.fsli?.name || '-' }}
                                </td>
                                <td class="p-3 text-slate-600 dark:text-slate-400">
                                    {{ line.department?.name || 'Shared / General' }}
                                </td>
                                <td class="p-3 text-right font-mono font-semibold text-slate-900 dark:text-white">
                                    {{ line.debit > 0 ? formatCurrency(line.debit) : '-' }}
                                </td>
                                <td class="p-3 text-right font-mono font-semibold text-slate-900 dark:text-white">
                                    {{ line.credit > 0 ? formatCurrency(line.credit) : '-' }}
                                </td>
                                <td class="p-3 text-slate-500">
                                    {{ line.description || '-' }}
                                </td>
                            </tr>
                        </tbody>
                        <tfoot class="bg-slate-50 dark:bg-slate-800/80 font-bold border-t-2 border-slate-300 dark:border-slate-700 text-xs">
                            <tr>
                                <td colspan="4" class="p-3 text-right uppercase text-slate-600 dark:text-slate-300">
                                    Total Penyesuaian (Balance):
                                </td>
                                <td class="p-3 text-right font-mono text-emerald-600 dark:text-emerald-400">
                                    {{ formatCurrency(journal.total_debit) }}
                                </td>
                                <td class="p-3 text-right font-mono text-emerald-600 dark:text-emerald-400">
                                    {{ formatCurrency(journal.total_credit) }}
                                </td>
                                <td></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>

            <!-- Reject Reason Modal -->
            <div
                v-if="rejectModalOpen"
                class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50 backdrop-blur-sm"
            >
                <div class="bg-white dark:bg-slate-900 rounded-2xl max-w-md w-full p-6 shadow-2xl border border-slate-200 dark:border-slate-800 space-y-4">
                    <div class="flex items-center justify-between pb-3 border-b border-slate-200 dark:border-slate-800">
                        <h3 class="font-bold text-slate-900 dark:text-white flex items-center gap-2">
                            <AlertCircle class="w-5 h-5 text-rose-500" />
                            <span>Tolak Jurnal Penyesuaian</span>
                        </h3>
                        <button @click="rejectModalOpen = false" class="text-slate-400 hover:text-slate-600">
                            <X class="w-5 h-5" />
                        </button>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">
                            Alasan Penolakan (Wajib Diisi):
                        </label>
                        <textarea
                            v-model="rejectionReason"
                            rows="3"
                            required
                            placeholder="Jelaskan alasan penolakan jurnal ini agar dapat diperbaiki oleh Maker..."
                            class="w-full text-xs rounded-lg border-slate-300 dark:border-slate-700 dark:bg-slate-800 focus:ring-rose-500 focus:border-rose-500"
                        ></textarea>
                    </div>

                    <div class="flex items-center justify-end gap-2 pt-3 border-t border-slate-200 dark:border-slate-800">
                        <button
                            type="button"
                            @click="rejectModalOpen = false"
                            class="px-3.5 py-2 text-xs text-slate-600 hover:bg-slate-100 rounded-lg"
                        >
                            Batal
                        </button>
                        <button
                            type="button"
                            :disabled="loading || !rejectionReason.trim()"
                            @click="rejectJournal"
                            class="px-4 py-2 text-xs font-semibold text-white bg-rose-600 hover:bg-rose-700 rounded-lg shadow-sm"
                        >
                            Konfirmasi Tolak Jurnal
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </ModuleLayout>
</template>
