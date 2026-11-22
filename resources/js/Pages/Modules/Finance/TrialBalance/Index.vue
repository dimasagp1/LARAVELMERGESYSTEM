<script setup>
import { ref } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import ModuleLayout from '@/Layouts/ModuleLayout.vue';
import {
    Scale,
    Download,
    CheckCircle2,
    AlertCircle,
    ChevronDown,
    ChevronRight,
    Eye,
    X,
    Layers,
    FileSpreadsheet,
    ListTree
} from 'lucide-vue-next';

const props = defineProps({
    module: Object,
    trialBalance: Object,
    periods: Array,
    selectedPeriodId: Number,
});

const currentPeriod = ref(props.selectedPeriodId || props.periods?.[0]?.id || 1);
const selectedFsliDrilldown = ref(null);
const drilldownLoading = ref(false);

const changePeriod = () => {
    router.get('/finance/trial-balance', {
        period_id: currentPeriod.value,
    }, { preserveState: true });
};

const openDrilldown = async (fsliId) => {
    drilldownLoading.value = true;
    try {
        const response = await fetch(`/finance/trial-balance/drilldown/${fsliId}?period_id=${currentPeriod.value}`);
        const data = await response.json();
        selectedFsliDrilldown.value = data;
    } catch (e) {
        console.error('Error loading drilldown:', e);
    } finally {
        drilldownLoading.value = false;
    }
};

const closeDrilldown = () => {
    selectedFsliDrilldown.value = null;
};

const formatCurrency = (val) => {
    if (val === 0 || !val) return '-';
    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        maximumFractionDigits: 0,
    }).format(val);
};

const formatCurrencyNonZero = (val) => {
    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        maximumFractionDigits: 0,
    }).format(val || 0);
};
</script>

<template>
    <Head title="Kertas Kerja 4-Kolom (Trial Balance)" />

    <ModuleLayout :module="module">
        <div class="space-y-6">
            <!-- Header & Period Filter -->
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div>
                    <h1 class="text-2xl font-bold tracking-tight text-slate-900 dark:text-white flex items-center gap-2">
                        <Scale class="w-6 h-6 text-emerald-500" />
                        <span>Kertas Kerja 4-Kolom (Trial Balance Working Sheet)</span>
                    </h1>
                    <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">
                        Konsolidasi Saldo GL Odoo + Jurnal Penyesuaian (AJE Approved) menghasilkan Saldo Disesuaikan dengan drilldown 3-level hingga voucher move.
                    </p>
                </div>

                <div class="flex items-center gap-3">
                    <select
                        v-model="currentPeriod"
                        @change="changePeriod"
                        class="text-xs font-semibold rounded-xl border border-slate-300 dark:border-slate-700 dark:bg-slate-800 text-slate-800 dark:text-slate-200 py-2.5 px-3"
                    >
                        <option v-for="p in periods" :key="p.id" :value="p.id">
                            Periode: {{ p.period_name }} ({{ p.status }})
                        </option>
                    </select>

                    <a
                        :href="`/finance/trial-balance/export?period_id=${currentPeriod}`"
                        class="inline-flex items-center gap-1.5 px-3.5 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-semibold rounded-xl shadow-sm transition-colors"
                    >
                        <Download class="w-4 h-4" />
                        <span>Ekspor CSV</span>
                    </a>
                </div>
            </div>

            <!-- Balancing Status Card -->
            <div
                :class="[
                    'p-4 rounded-xl border flex flex-col sm:flex-row sm:items-center justify-between gap-3 text-xs',
                    trialBalance?.is_balanced
                        ? 'bg-emerald-50 dark:bg-emerald-950/30 border-emerald-200 dark:border-emerald-800/50 text-emerald-900 dark:text-emerald-200'
                        : 'bg-rose-50 dark:bg-rose-950/30 border-rose-200 dark:border-rose-800/50 text-rose-900 dark:text-rose-200'
                ]"
            >
                <div class="flex items-center gap-3">
                    <div
                        :class="[
                            'w-7 h-7 rounded-lg flex items-center justify-center shrink-0',
                            trialBalance?.is_balanced ? 'bg-emerald-500 text-white' : 'bg-rose-500 text-white'
                        ]"
                    >
                        <CheckCircle2 v-if="trialBalance?.is_balanced" class="w-4 h-4" />
                        <AlertCircle v-else class="w-4 h-4" />
                    </div>
                    <div>
                        <span class="font-bold">
                            {{ trialBalance?.is_balanced ? 'Kertas Kerja 100% Berimbang (Debit = Kredit)' : 'PERINGATAN: Kertas Kerja Belum Berimbang!' }}
                        </span>
                        <p class="text-slate-600 dark:text-slate-400 mt-0.5">
                            Saldo Disesuaikan: Debet {{ formatCurrencyNonZero(trialBalance?.total_adjusted_debit) }} vs Kredit {{ formatCurrencyNonZero(trialBalance?.total_adjusted_credit) }}.
                        </p>
                    </div>
                </div>
                <div class="font-mono font-bold text-sm">
                    Selisih: {{ formatCurrencyNonZero(Math.abs((trialBalance?.total_adjusted_debit || 0) - (trialBalance?.total_adjusted_credit || 0))) }}
                </div>
            </div>

            <!-- 4-Column Table -->
            <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl shadow-sm overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs">
                        <thead class="bg-slate-100 dark:bg-slate-800 font-bold text-slate-700 dark:text-slate-200 uppercase border-b border-slate-300 dark:border-slate-700">
                            <!-- Multi-tier header -->
                            <tr>
                                <th rowspan="2" class="p-3 border-r border-slate-200 dark:border-slate-700 w-64">
                                    Pos Laporan Keuangan (FSLI)
                                </th>
                                <th colspan="2" class="p-2 text-center border-r border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800/40">
                                    1. Saldo Belum Disesuaikan (GL)
                                </th>
                                <th colspan="2" class="p-2 text-center border-r border-slate-200 dark:border-slate-700 bg-emerald-50/50 dark:bg-emerald-950/20 text-emerald-800 dark:text-emerald-300">
                                    2. Penyesuaian (AJE)
                                </th>
                                <th colspan="2" class="p-2 text-center border-r border-slate-200 dark:border-slate-700 bg-teal-50/50 dark:bg-teal-950/20 text-teal-800 dark:text-teal-300">
                                    3. Saldo Disesuaikan (Adjusted)
                                </th>
                                <th rowspan="2" class="p-3 text-center w-20">Drilldown</th>
                            </tr>
                            <tr class="text-[11px] border-t border-slate-200 dark:border-slate-700">
                                <th class="p-2 text-right w-32 border-r border-slate-200 dark:border-slate-700">Debet</th>
                                <th class="p-2 text-right w-32 border-r border-slate-200 dark:border-slate-700">Kredit</th>
                                <th class="p-2 text-right w-32 border-r border-slate-200 dark:border-slate-700 text-emerald-700 dark:text-emerald-400">Debet</th>
                                <th class="p-2 text-right w-32 border-r border-slate-200 dark:border-slate-700 text-emerald-700 dark:text-emerald-400">Kredit</th>
                                <th class="p-2 text-right w-32 border-r border-slate-200 dark:border-slate-700 text-teal-700 dark:text-teal-400">Debet</th>
                                <th class="p-2 text-right w-32 border-r border-slate-200 dark:border-slate-700 text-teal-700 dark:text-teal-400">Kredit</th>
                            </tr>
                        </thead>

                        <tbody class="divide-y divide-slate-200 dark:divide-slate-800 font-mono">
                            <tr
                                v-for="line in trialBalance?.lines"
                                :key="line.fsli_id"
                                class="hover:bg-slate-50 dark:hover:bg-slate-800/40 transition-colors"
                            >
                                <td class="p-3 font-sans border-r border-slate-200 dark:border-slate-800">
                                    <div class="font-bold text-slate-900 dark:text-white flex items-center justify-between">
                                        <span>{{ line.name }}</span>
                                        <span class="text-[10px] text-slate-400 font-mono">{{ line.code }}</span>
                                    </div>
                                    <span class="text-[10px] text-slate-500 uppercase">{{ line.statement_type?.replace('_', ' ') }}</span>
                                </td>

                                <!-- Column 1: Unadjusted -->
                                <td class="p-2.5 text-right border-r border-slate-200 dark:border-slate-800 text-slate-800 dark:text-slate-200">
                                    {{ formatCurrency(line.unadjusted?.debit) }}
                                </td>
                                <td class="p-2.5 text-right border-r border-slate-200 dark:border-slate-800 text-slate-800 dark:text-slate-200">
                                    {{ formatCurrency(line.unadjusted?.credit) }}
                                </td>

                                <!-- Column 2: Adjustments -->
                                <td class="p-2.5 text-right border-r border-slate-200 dark:border-slate-800 text-emerald-600 dark:text-emerald-400 font-semibold bg-emerald-50/20 dark:bg-emerald-950/10">
                                    {{ formatCurrency(line.adjustments?.debit) }}
                                </td>
                                <td class="p-2.5 text-right border-r border-slate-200 dark:border-slate-800 text-emerald-600 dark:text-emerald-400 font-semibold bg-emerald-50/20 dark:bg-emerald-950/10">
                                    {{ formatCurrency(line.adjustments?.credit) }}
                                </td>

                                <!-- Column 3: Adjusted -->
                                <td class="p-2.5 text-right border-r border-slate-200 dark:border-slate-800 text-teal-700 dark:text-teal-300 font-bold bg-teal-50/20 dark:bg-teal-950/10">
                                    {{ formatCurrency(line.adjusted?.debit) }}
                                </td>
                                <td class="p-2.5 text-right border-r border-slate-200 dark:border-slate-800 text-teal-700 dark:text-teal-300 font-bold bg-teal-50/20 dark:bg-teal-950/10">
                                    {{ formatCurrency(line.adjusted?.credit) }}
                                </td>

                                <!-- Drilldown Button -->
                                <td class="p-2 text-center">
                                    <button
                                        @click="openDrilldown(line.fsli_id)"
                                        class="p-1 text-slate-400 hover:text-emerald-600 dark:hover:text-emerald-400 rounded hover:bg-slate-100 dark:hover:bg-slate-800"
                                        title="Buka Drilldown Akun & Jurnal"
                                    >
                                        <Layers class="w-4 h-4" />
                                    </button>
                                </td>
                            </tr>
                        </tbody>

                        <!-- Grand Total Footer -->
                        <tfoot class="bg-slate-100 dark:bg-slate-800 border-t-2 border-slate-300 dark:border-slate-700 font-mono font-bold text-xs">
                            <tr>
                                <td class="p-3 uppercase font-sans border-r border-slate-200 dark:border-slate-700 text-slate-900 dark:text-white">
                                    TOTAL KERTAS KERJA
                                </td>
                                <td class="p-3 text-right border-r border-slate-200 dark:border-slate-700 text-slate-900 dark:text-white">
                                    {{ formatCurrencyNonZero(trialBalance?.total_unadjusted_debit) }}
                                </td>
                                <td class="p-3 text-right border-r border-slate-200 dark:border-slate-700 text-slate-900 dark:text-white">
                                    {{ formatCurrencyNonZero(trialBalance?.total_unadjusted_credit) }}
                                </td>
                                <td class="p-3 text-right border-r border-slate-200 dark:border-slate-700 text-emerald-600 dark:text-emerald-400">
                                    {{ formatCurrencyNonZero(trialBalance?.total_adjustment_debit) }}
                                </td>
                                <td class="p-3 text-right border-r border-slate-200 dark:border-slate-700 text-emerald-600 dark:text-emerald-400">
                                    {{ formatCurrencyNonZero(trialBalance?.total_adjustment_credit) }}
                                </td>
                                <td class="p-3 text-right border-r border-slate-200 dark:border-slate-700 text-teal-600 dark:text-teal-400">
                                    {{ formatCurrencyNonZero(trialBalance?.total_adjusted_debit) }}
                                </td>
                                <td class="p-3 text-right border-r border-slate-200 dark:border-slate-700 text-teal-600 dark:text-teal-400">
                                    {{ formatCurrencyNonZero(trialBalance?.total_adjusted_credit) }}
                                </td>
                                <td></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>

            <!-- Drilldown Modal / Drawer (3-Level Drilldown) -->
            <div
                v-if="selectedFsliDrilldown"
                class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50 backdrop-blur-sm"
            >
                <div class="bg-white dark:bg-slate-900 rounded-2xl max-w-4xl w-full max-h-[85vh] flex flex-col shadow-2xl border border-slate-200 dark:border-slate-800">
                    <div class="p-5 border-b border-slate-200 dark:border-slate-800 flex items-center justify-between">
                        <div>
                            <div class="flex items-center gap-2">
                                <span class="px-2 py-0.5 rounded text-xs font-bold bg-emerald-100 text-emerald-800 dark:bg-emerald-900/50 dark:text-emerald-300">
                                    Level 2 & 3 Drilldown
                                </span>
                                <h3 class="font-bold text-slate-900 dark:text-white text-base">
                                    {{ selectedFsliDrilldown.fsli?.name }} ({{ selectedFsliDrilldown.fsli?.code }})
                                </h3>
                            </div>
                            <p class="text-xs text-slate-500 mt-1">
                                Rincian Akun Terpetakan & Entri Jurnal Transaksi di Balik Pos Keuangan Ini.
                            </p>
                        </div>
                        <button @click="closeDrilldown" class="text-slate-400 hover:text-slate-600">
                            <X class="w-5 h-5" />
                        </button>
                    </div>

                    <div class="p-5 overflow-y-auto space-y-6">
                        <!-- Level 2: Underlying COA Accounts -->
                        <div>
                            <h4 class="font-bold text-xs uppercase text-slate-500 tracking-wider mb-2 flex items-center gap-1.5">
                                <ListTree class="w-4 h-4 text-emerald-500" />
                                <span>Level 2: Akun Chart of Accounts Terpetakan (COA)</span>
                            </h4>
                            <div class="bg-slate-50 dark:bg-slate-800/50 rounded-xl overflow-hidden border border-slate-200 dark:border-slate-700">
                                <table class="w-full text-xs text-left">
                                    <thead class="bg-slate-100 dark:bg-slate-800 font-semibold text-slate-600 border-b">
                                        <tr>
                                            <th class="p-2.5">Kode Akun</th>
                                            <th class="p-2.5">Nama Akun</th>
                                            <th class="p-2.5 text-right">Debet</th>
                                            <th class="p-2.5 text-right">Kredit</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-slate-200 dark:divide-slate-700 font-mono">
                                        <tr v-for="acc in selectedFsliDrilldown.accounts" :key="acc.id">
                                            <td class="p-2.5 font-bold text-slate-900 dark:text-white">{{ acc.code }}</td>
                                            <td class="p-2.5 font-sans">{{ acc.name }}</td>
                                            <td class="p-2.5 text-right">{{ formatCurrency(acc.debit) }}</td>
                                            <td class="p-2.5 text-right">{{ formatCurrency(acc.credit) }}</td>
                                        </tr>
                                        <tr v-if="!selectedFsliDrilldown.accounts?.length">
                                            <td colspan="4" class="p-4 text-center text-slate-400 font-sans">Tidak ada akun langsung terpetakan.</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <!-- Level 3: Individual GL & AJE Entries -->
                        <div>
                            <h4 class="font-bold text-xs uppercase text-slate-500 tracking-wider mb-2 flex items-center gap-1.5">
                                <FileSpreadsheet class="w-4 h-4 text-teal-500" />
                                <span>Level 3: Baris Jurnal / Voucher Move Individual</span>
                            </h4>
                            <div class="bg-slate-50 dark:bg-slate-800/50 rounded-xl overflow-hidden border border-slate-200 dark:border-slate-700">
                                <table class="w-full text-xs text-left">
                                    <thead class="bg-slate-100 dark:bg-slate-800 font-semibold text-slate-600 border-b">
                                        <tr>
                                            <th class="p-2.5">No. Move / Referensi</th>
                                            <th class="p-2.5">Tanggal</th>
                                            <th class="p-2.5">Akun</th>
                                            <th class="p-2.5">Departemen</th>
                                            <th class="p-2.5 text-right">Debet</th>
                                            <th class="p-2.5 text-right">Kredit</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-slate-200 dark:divide-slate-700 font-mono">
                                        <tr v-for="entry in selectedFsliDrilldown.entries" :key="entry.id">
                                            <td class="p-2.5 font-bold text-emerald-600 dark:text-emerald-400">{{ entry.move_number || entry.reference || '-' }}</td>
                                            <td class="p-2.5 font-sans">{{ entry.date || '-' }}</td>
                                            <td class="p-2.5 font-sans">{{ entry.account_name || entry.account_code }}</td>
                                            <td class="p-2.5 font-sans">{{ entry.department?.name || '-' }}</td>
                                            <td class="p-2.5 text-right">{{ formatCurrency(entry.debit) }}</td>
                                            <td class="p-2.5 text-right">{{ formatCurrency(entry.credit) }}</td>
                                        </tr>
                                        <tr v-if="!selectedFsliDrilldown.entries?.length">
                                            <td colspan="6" class="p-4 text-center text-slate-400 font-sans">Tidak ada mutasi voucher move pada periode ini.</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <div class="p-4 border-t border-slate-200 dark:border-slate-800 flex justify-end">
                        <button
                            type="button"
                            @click="closeDrilldown"
                            class="px-4 py-2 bg-slate-200 dark:bg-slate-700 hover:bg-slate-300 text-xs font-semibold rounded-lg"
                        >
                            Tutup Drilldown
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </ModuleLayout>
</template>
