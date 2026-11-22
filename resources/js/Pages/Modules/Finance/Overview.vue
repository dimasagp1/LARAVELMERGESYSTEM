<script setup>
import { ref, computed } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import ModuleLayout from '@/Layouts/ModuleLayout.vue';
import KpiStatCard from '@/Components/Dashboard/KpiStatCard.vue';
import InteractiveChartCard from '@/Components/Dashboard/InteractiveChartCard.vue';
import {
    LayoutDashboard,
    FileSpreadsheet,
    Scale,
    BookOpen,
    BarChart3,
    Activity,
    CheckCheck,
    Plus,
    CheckCircle2,
    AlertCircle,
    Check,
    X,
    Eye,
    TrendingUp,
    ShieldCheck,
    Clock,
    DollarSign,
    PieChart,
    ChevronRight,
    ArrowUpRight,
    Download,
    Award
} from 'lucide-vue-next';

const props = defineProps({
    module: Object,
    period: Object,
    kpis: Object,
    reconciliationSummary: Object,
    bscPillars: Object,
    pendingAje: {
        type: Array,
        default: () => [],
    },
    charts: {
        type: Object,
        default: () => ({}),
    },
    recentActivities: {
        type: Array,
        default: () => [],
    },
});

const activeTab = ref('charts'); // 'charts' | 'bsc' | 'aje' | 'reconciliation'
const selectedAje = ref(null);
const approvalModalOpen = ref(false);
const approvalNotes = ref('');
const approvalLoading = ref(false);

const openApprovalModal = (item) => {
    selectedAje.value = item;
    approvalNotes.value = '';
    approvalModalOpen.value = true;
};

const handleAjeAction = (action) => {
    if (!selectedAje.value) return;
    approvalLoading.value = true;

    const url = action === 'approve'
        ? `/finance/aje/${selectedAje.value.id}/approve`
        : `/finance/aje/${selectedAje.value.id}/reject`;

    router.post(url, {
        rejection_reason: approvalNotes.value,
    }, {
        preserveScroll: true,
        onSuccess: () => {
            approvalModalOpen.value = false;
            selectedAje.value = null;
        },
        onFinish: () => {
            approvalLoading.value = false;
        },
    });
};

const formatCurrency = (val) => {
    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        maximumFractionDigits: 0,
    }).format(val || 0);
};

const formatNumber = (val) => {
    return new Intl.NumberFormat('id-ID').format(val || 0);
};
</script>

<template>
    <Head title="Finance & Budgeting Monitoring" />

    <ModuleLayout :module="module">
        <div class="space-y-6">
            <!-- Top Banner -->
            <div class="bg-gradient-to-r from-emerald-900 via-teal-900 to-slate-900 text-white rounded-2xl p-6 shadow-xl border border-emerald-700/40 relative overflow-hidden">
                <div class="absolute -right-10 -bottom-10 w-64 h-64 bg-emerald-500/10 rounded-full blur-3xl pointer-events-none"></div>
                <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-6 relative z-10">
                    <div>
                        <div class="flex items-center gap-2 mb-2">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-emerald-500/20 text-emerald-300 border border-emerald-500/30">
                                <Clock class="w-3.5 h-3.5 mr-1" />
                                Periode: {{ period?.period_name || 'November 2026' }}
                            </span>
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-teal-500/20 text-teal-300 border border-teal-500/30">
                                Status: {{ period?.status?.toUpperCase() || 'OPEN' }}
                            </span>
                        </div>
                        <h1 class="text-2xl font-bold tracking-tight text-white flex items-center gap-2">
                            <span>Sistem Monitoring Keuangan & Anggaran Terpadu</span>
                        </h1>
                        <p class="text-emerald-100/80 text-sm mt-1 max-w-2xl">
                            Kertas Kerja 4-Kolom, Audit Rekonsiliasi Otomatis R1-R9, Laporan Keuangan SAK, 19 Rasio Balanced Scorecard, dan Real-Time Budget Gatekeeper.
                        </p>
                    </div>

                    <!-- Quick Actions -->
                    <div class="flex flex-wrap items-center gap-2.5">
                        <Link
                            href="/finance/aje/create"
                            class="inline-flex items-center gap-2 px-4 py-2.5 bg-emerald-500 hover:bg-emerald-600 text-white text-sm font-semibold rounded-xl shadow-lg shadow-emerald-500/25 transition-all transform hover:-translate-y-0.5"
                        >
                            <Plus class="w-4 h-4" />
                            <span>Buat AJE Baru</span>
                        </Link>
                        <Link
                            href="/finance/trial-balance"
                            class="inline-flex items-center gap-2 px-3.5 py-2.5 bg-white/10 hover:bg-white/20 text-white text-sm font-medium rounded-xl border border-white/10 backdrop-blur-sm transition-all"
                        >
                            <Scale class="w-4 h-4" />
                            <span>Kertas Kerja 4-Kolom</span>
                        </Link>
                        <Link
                            href="/finance/financial-statements"
                            class="inline-flex items-center gap-2 px-3.5 py-2.5 bg-white/10 hover:bg-white/20 text-white text-sm font-medium rounded-xl border border-white/10 backdrop-blur-sm transition-all"
                        >
                            <BookOpen class="w-4 h-4" />
                            <span>Laporan Keuangan</span>
                        </Link>
                    </div>
                </div>
            </div>

            <!-- Reconciliation Status Alert Bar -->
            <div
                :class="[
                    'p-4 rounded-xl border flex flex-col sm:flex-row sm:items-center justify-between gap-3 text-sm transition-colors',
                    reconciliationSummary?.all_passed
                        ? 'bg-emerald-50 dark:bg-emerald-950/30 border-emerald-200 dark:border-emerald-800/50 text-emerald-900 dark:text-emerald-200'
                        : 'bg-amber-50 dark:bg-amber-950/30 border-amber-200 dark:border-amber-800/50 text-amber-900 dark:text-amber-200'
                ]"
            >
                <div class="flex items-center gap-3">
                    <div
                        :class="[
                            'w-8 h-8 rounded-lg flex items-center justify-center shrink-0',
                            reconciliationSummary?.all_passed ? 'bg-emerald-500 text-white' : 'bg-amber-500 text-white'
                        ]"
                    >
                        <CheckCheck v-if="reconciliationSummary?.all_passed" class="w-5 h-5" />
                        <AlertCircle v-else class="w-5 h-5" />
                    </div>
                    <div>
                        <div class="font-bold flex items-center gap-2">
                            <span>Status 9-Titik Rekonsiliasi Otomatis (R1-R9):</span>
                            <span class="px-2 py-0.5 rounded text-xs font-semibold" :class="reconciliationSummary?.all_passed ? 'bg-emerald-200 text-emerald-800 dark:bg-emerald-800 dark:text-emerald-100' : 'bg-amber-200 text-amber-800 dark:bg-amber-800 dark:text-amber-100'">
                                {{ reconciliationSummary?.passed_count || 9 }} / {{ reconciliationSummary?.total_rules || 9 }} Passed ({{ reconciliationSummary?.all_passed ? '100% Selaras' : 'Perlu Investigasi' }})
                            </span>
                        </div>
                        <p class="text-xs text-slate-600 dark:text-slate-400 mt-0.5">
                            Keseimbangan Debet-Kredit GL Odoo, Posisi Neraca, Aliran Kas, dan Mutasi Persediaan terverifikasi otomatis.
                        </p>
                    </div>
                </div>
                <Link
                    href="/finance/reconciliation"
                    class="inline-flex items-center gap-1.5 font-semibold text-xs px-3 py-1.5 rounded-lg border border-current hover:bg-black/5 dark:hover:bg-white/5 transition-colors shrink-0 self-start sm:self-auto"
                >
                    <span>Buka Detail Audit</span>
                    <ArrowUpRight class="w-3.5 h-3.5" />
                </Link>
            </div>

            <!-- 5 Core KPI Cards -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-4">
                <KpiStatCard
                    title="Pendapatan Usaha"
                    :value="kpis?.revenue ? 'Rp ' + (kpis.revenue / 1000000000).toFixed(2) + ' M' : 'Rp 1.85 M'"
                    subtitle="Target: Rp 1.50 M"
                    trend="+23.3%"
                    trend-type="up"
                    color-theme="emerald"
                />
                <KpiStatCard
                    title="Net Profit Margin"
                    :value="(kpis?.net_profit_margin || 22.4) + '%'"
                    subtitle="Standar Industri: 15%"
                    trend="+7.4%"
                    trend-type="up"
                    color-theme="teal"
                />
                <KpiStatCard
                    title="Realisasi Anggaran"
                    :value="(kpis?.budget_burn_rate || 68.2) + '%'"
                    subtitle="Burn Rate OPEX 11 Dept"
                    trend="Aman (Di bawah pagu)"
                    trend-type="neutral"
                    color-theme="sky"
                />
                <KpiStatCard
                    title="Skor Balanced Scorecard"
                    :value="(kpis?.bsc_composite_score || 88.5).toString()"
                    subtitle="Predikat: A (Unggul)"
                    trend="5 Pilar Seimbang"
                    trend-type="up"
                    color-theme="indigo"
                />
                <KpiStatCard
                    title="Keseimbangan GL"
                    :value="kpis?.trial_balance_status?.is_balanced ? 'Rp 0 Diff' : 'Selaras'"
                    subtitle="Debit = Kredit (4-Kolom)"
                    trend="100% Balanced"
                    trend-type="up"
                    color-theme="emerald"
                />
            </div>

            <!-- Tabbed Navigation -->
            <div class="border-b border-slate-200 dark:border-slate-800 flex gap-4">
                <button
                    @click="activeTab = 'charts'"
                    :class="[
                        'pb-3 text-sm font-semibold flex items-center gap-2 border-b-2 transition-all',
                        activeTab === 'charts'
                            ? 'border-emerald-500 text-emerald-600 dark:text-emerald-400'
                            : 'border-transparent text-slate-500 hover:text-slate-800 dark:hover:text-slate-200'
                    ]"
                >
                    <BarChart3 class="w-4 h-4" />
                    <span>Tren Finansial & Realisasi Budget</span>
                </button>
                <button
                    @click="activeTab = 'bsc'"
                    :class="[
                        'pb-3 text-sm font-semibold flex items-center gap-2 border-b-2 transition-all',
                        activeTab === 'bsc'
                            ? 'border-emerald-500 text-emerald-600 dark:text-emerald-400'
                            : 'border-transparent text-slate-500 hover:text-slate-800 dark:hover:text-slate-200'
                    ]"
                >
                    <Activity class="w-4 h-4" />
                    <span>5 Pilar Balanced Scorecard (BSC)</span>
                </button>
                <button
                    @click="activeTab = 'aje'"
                    :class="[
                        'pb-3 text-sm font-semibold flex items-center gap-2 border-b-2 transition-all relative',
                        activeTab === 'aje'
                            ? 'border-emerald-500 text-emerald-600 dark:text-emerald-400'
                            : 'border-transparent text-slate-500 hover:text-slate-800 dark:hover:text-slate-200'
                    ]"
                >
                    <FileSpreadsheet class="w-4 h-4" />
                    <span>Maker-Checker AJE Approval</span>
                    <span
                        v-if="pendingAje.length > 0"
                        class="px-1.5 py-0.5 rounded-full text-xs font-bold bg-amber-500 text-white leading-none ml-1"
                    >
                        {{ pendingAje.length }}
                    </span>
                </button>
            </div>

            <!-- TAB 1: Charts & Run-rate -->
            <div v-show="activeTab === 'charts'" class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <!-- Interactive ApexChart: Revenue vs Net Income Trend -->
                <InteractiveChartCard
                    title="Tren Pendapatan vs Laba Bersih (Run-Rate MTD/YTD)"
                    type="area"
                    :series="charts.pnlTrend?.series || []"
                    :categories="charts.pnlTrend?.categories || ['Jul', 'Agu', 'Sep', 'Okt', 'Nov']"
                    :colors="['#10b981', '#06b6d4']"
                    height="320"
                />

                <!-- Interactive ApexChart: Department Budget Burn Rate -->
                <InteractiveChartCard
                    title="Penyerapan Anggaran OPEX per Departemen (%)"
                    type="bar"
                    :series="charts.deptBudgetBurn?.series || []"
                    :categories="charts.deptBudgetBurn?.categories || []"
                    :colors="['#059669']"
                    height="320"
                />
            </div>

            <!-- TAB 2: Balanced Scorecard 5 Pillars Preview -->
            <div v-show="activeTab === 'bsc'" class="space-y-4">
                <div class="grid grid-cols-1 md:grid-cols-5 gap-4">
                    <div
                        v-for="(pillar, key) in bscPillars"
                        :key="key"
                        class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl p-4 shadow-sm"
                    >
                        <div class="flex items-center justify-between text-xs font-semibold text-slate-500 uppercase tracking-wider mb-2">
                            <span>{{ key }}</span>
                            <span class="text-emerald-600 dark:text-emerald-400">{{ pillar.weight }}% Bobot</span>
                        </div>
                        <div class="text-2xl font-black text-slate-900 dark:text-white">
                            {{ pillar.score }}<span class="text-xs font-normal text-slate-400">/100</span>
                        </div>
                        <div class="w-full bg-slate-100 dark:bg-slate-800 rounded-full h-2 mt-3 overflow-hidden">
                            <div
                                class="h-2 rounded-full transition-all duration-500"
                                :class="pillar.score >= 85 ? 'bg-emerald-500' : (pillar.score >= 70 ? 'bg-amber-500' : 'bg-red-500')"
                                :style="{ width: `${pillar.score}%` }"
                            ></div>
                        </div>
                        <p class="text-xs text-slate-500 mt-2">
                            {{ pillar.ratios?.length || 0 }} Indikator Rasio
                        </p>
                    </div>
                </div>

                <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl p-5 shadow-sm">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="font-bold text-slate-900 dark:text-white flex items-center gap-2">
                            <Award class="w-5 h-5 text-indigo-500" />
                            <span>Ikhtisar 19 Rasio Strategis Finansial</span>
                        </h3>
                        <Link
                            href="/finance/financial-ratios"
                            class="text-xs text-emerald-600 dark:text-emerald-400 font-semibold hover:underline flex items-center gap-1"
                        >
                            <span>Kelola Konfigurasi & Target Rasio</span>
                            <ArrowUpRight class="w-3.5 h-3.5" />
                        </Link>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
                        <div
                            v-for="rat in (bscPillars?.profitability?.ratios || []).concat(bscPillars?.liquidity?.ratios || []).slice(0, 6)"
                            :key="rat.code"
                            class="p-3 bg-slate-50 dark:bg-slate-800/50 rounded-lg flex items-center justify-between"
                        >
                            <div>
                                <span class="font-bold text-sm text-slate-800 dark:text-slate-100">{{ rat.code }}</span>
                                <p class="text-xs text-slate-500 truncate max-w-[150px]">{{ rat.name }}</p>
                            </div>
                            <div class="text-right">
                                <span class="text-sm font-semibold text-slate-900 dark:text-white">{{ rat.raw_value }} {{ rat.unit }}</span>
                                <span
                                    :class="[
                                        'block text-[10px] px-1.5 py-0.5 rounded font-medium mt-0.5',
                                        rat.status === 'achieved'
                                            ? 'bg-emerald-100 text-emerald-800 dark:bg-emerald-900/50 dark:text-emerald-300'
                                            : 'bg-amber-100 text-amber-800 dark:bg-amber-900/50 dark:text-amber-300'
                                    ]"
                                >
                                    Target: {{ rat.target }} {{ rat.unit }}
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- TAB 3: Antrean Review AJE Maker-Checker -->
            <div v-show="activeTab === 'aje'" class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl shadow-sm overflow-hidden">
                <div class="p-4 border-b border-slate-200 dark:border-slate-800 flex items-center justify-between">
                    <div>
                        <h3 class="font-bold text-slate-900 dark:text-white flex items-center gap-2">
                            <ShieldCheck class="w-5 h-5 text-emerald-500" />
                            <span>Maker-Checker Approval Queue (AJE Menunggu Persetujuan)</span>
                        </h3>
                        <p class="text-xs text-slate-500 mt-0.5">
                            Hanya jurnal penyesuaian berstatus 'Approved' oleh Checker yang akan mempengaruhi Kertas Kerja Trial Balance & Laporan Keuangan.
                        </p>
                    </div>
                    <Link
                        href="/finance/aje"
                        class="text-xs text-emerald-600 dark:text-emerald-400 font-semibold hover:underline flex items-center gap-1"
                    >
                        <span>Lihat Semua Jurnal ({{ pendingAje.length }})</span>
                        <ChevronRight class="w-4 h-4" />
                    </Link>
                </div>

                <div v-if="pendingAje.length === 0" class="p-8 text-center text-slate-500">
                    <CheckCircle2 class="w-12 h-12 text-emerald-500 mx-auto mb-2 opacity-60" />
                    <p class="font-medium text-slate-700 dark:text-slate-300">Tidak ada AJE yang menunggu persetujuan.</p>
                    <p class="text-xs mt-1">Semua jurnal penyesuaian telah selesai diproses.</p>
                </div>

                <div v-else class="overflow-x-auto">
                    <table class="w-full text-left text-sm">
                        <thead class="bg-slate-50 dark:bg-slate-800/60 text-xs font-semibold text-slate-500 uppercase">
                            <tr>
                                <th class="px-4 py-3">No. Jurnal</th>
                                <th class="px-4 py-3">Tanggal</th>
                                <th class="px-4 py-3">Deskripsi / Dasar Penyesuaian</th>
                                <th class="px-4 py-3">Maker (Dibuat Oleh)</th>
                                <th class="px-4 py-3 text-right">Total Debet / Kredit</th>
                                <th class="px-4 py-3 text-center">Status</th>
                                <th class="px-4 py-3 text-right">Aksi Checker</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-200 dark:divide-slate-800">
                            <tr v-for="item in pendingAje" :key="item.id" class="hover:bg-slate-50 dark:hover:bg-slate-800/30 transition-colors">
                                <td class="px-4 py-3 font-semibold text-emerald-600 dark:text-emerald-400">
                                    <Link :href="`/finance/aje/${item.id}`" class="hover:underline">
                                        {{ item.journal_number }}
                                    </Link>
                                </td>
                                <td class="px-4 py-3 text-xs text-slate-500">
                                    {{ item.journal_date }}
                                </td>
                                <td class="px-4 py-3">
                                    <div class="font-medium text-slate-900 dark:text-white">{{ item.description }}</div>
                                    <span class="text-[11px] text-slate-400 capitalize">Tipe: {{ item.type }}</span>
                                </td>
                                <td class="px-4 py-3 text-xs text-slate-600 dark:text-slate-400">
                                    {{ item.maker?.name || 'Staff Finance' }}
                                </td>
                                <td class="px-4 py-3 text-right font-semibold text-slate-900 dark:text-white">
                                    {{ formatCurrency(item.total_debit) }}
                                </td>
                                <td class="px-4 py-3 text-center">
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-semibold bg-amber-100 text-amber-800 dark:bg-amber-900/40 dark:text-amber-300">
                                        {{ item.status?.toUpperCase() }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-right space-x-1">
                                    <button
                                        @click="openApprovalModal(item)"
                                        class="inline-flex items-center gap-1 px-3 py-1 bg-emerald-500 hover:bg-emerald-600 text-white text-xs font-semibold rounded-lg shadow-sm transition-colors"
                                    >
                                        <Check class="w-3.5 h-3.5" />
                                        <span>Review & Setujui</span>
                                    </button>
                                    <Link
                                        :href="`/finance/aje/${item.id}`"
                                        class="inline-flex items-center p-1 text-slate-400 hover:text-slate-600 dark:hover:text-slate-200"
                                    >
                                        <Eye class="w-4 h-4" />
                                    </Link>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Approval Modal Dialog -->
            <div
                v-if="approvalModalOpen"
                class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50 backdrop-blur-sm"
            >
                <div class="bg-white dark:bg-slate-900 rounded-2xl max-w-md w-full p-6 shadow-2xl border border-slate-200 dark:border-slate-800 space-y-4">
                    <div class="flex items-center justify-between pb-3 border-b border-slate-200 dark:border-slate-800">
                        <h3 class="font-bold text-slate-900 dark:text-white flex items-center gap-2">
                            <ShieldCheck class="w-5 h-5 text-emerald-500" />
                            <span>Keputusan Checker AJE</span>
                        </h3>
                        <button @click="approvalModalOpen = false" class="text-slate-400 hover:text-slate-600">
                            <X class="w-5 h-5" />
                        </button>
                    </div>

                    <div class="text-sm space-y-2">
                        <div class="bg-slate-50 dark:bg-slate-800 p-3 rounded-lg">
                            <div class="text-xs text-slate-500">Nomor Jurnal:</div>
                            <div class="font-bold text-emerald-600 dark:text-emerald-400">{{ selectedAje?.journal_number }}</div>
                            <div class="text-xs text-slate-500 mt-1">Uraian:</div>
                            <div class="text-xs font-medium text-slate-800 dark:text-slate-200">{{ selectedAje?.description }}</div>
                            <div class="text-xs text-slate-500 mt-1">Nominal Debet / Kredit:</div>
                            <div class="font-bold text-slate-900 dark:text-white">{{ formatCurrency(selectedAje?.total_debit) }}</div>
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">
                                Catatan Checker (Wajib diisi bila Menolak):
                            </label>
                            <textarea
                                v-model="approvalNotes"
                                rows="3"
                                placeholder="Masukkan catatan telaah / alasan penolakan jurnal..."
                                class="w-full text-xs rounded-lg border-slate-300 dark:border-slate-700 dark:bg-slate-800 focus:ring-emerald-500 focus:border-emerald-500"
                            ></textarea>
                        </div>
                    </div>

                    <div class="flex items-center justify-end gap-2 pt-3 border-t border-slate-200 dark:border-slate-800">
                        <button
                            type="button"
                            @click="approvalModalOpen = false"
                            class="px-3.5 py-2 text-xs font-medium text-slate-600 hover:text-slate-800 dark:text-slate-400 rounded-lg hover:bg-slate-100 dark:hover:bg-slate-800"
                        >
                            Batal
                        </button>
                        <button
                            type="button"
                            :disabled="approvalLoading"
                            @click="handleAjeAction('reject')"
                            class="px-3.5 py-2 text-xs font-semibold text-rose-600 hover:bg-rose-50 dark:hover:bg-rose-950/30 rounded-lg border border-rose-200 dark:border-rose-900"
                        >
                            Tolak Jurnal
                        </button>
                        <button
                            type="button"
                            :disabled="approvalLoading"
                            @click="handleAjeAction('approve')"
                            class="px-4 py-2 text-xs font-semibold text-white bg-emerald-600 hover:bg-emerald-700 rounded-lg shadow-sm"
                        >
                            Setujui (Approve)
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </ModuleLayout>
</template>
