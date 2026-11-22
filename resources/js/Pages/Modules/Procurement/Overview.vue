<script setup>
import { ref, computed } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import ModuleLayout from '@/Layouts/ModuleLayout.vue';
import KpiStatCard from '@/Components/Dashboard/KpiStatCard.vue';
import InteractiveChartCard from '@/Components/Dashboard/InteractiveChartCard.vue';
import {
    ShoppingCart,
    FileCheck,
    Receipt,
    Truck,
    Wallet,
    Tag,
    Target,
    Plus,
    Clock,
    CheckCircle2,
    Check,
    AlertCircle,
    FileText,
    TrendingUp,
    ShieldCheck,
    Activity,
    ArrowUpRight,
    ChevronRight,
    Award,
    Layers,
    Search,
    AlertTriangle,
    Eye,
} from 'lucide-vue-next';

const props = defineProps({
    module: Object,
    kpis: Object,
    qualityTargets: {
        type: Array,
        default: () => [],
    },
    pipelineCounts: {
        type: Object,
        default: () => ({}),
    },
    pendingApprovals: {
        type: Array,
        default: () => [],
    },
    pendingBons: {
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

const activeTab = ref('pipeline'); // 'pipeline' | 'approvals' | 'charts' | 'quality'
const selectedApproval = ref(null);
const approvalModalOpen = ref(false);
const approvalNotes = ref('');
const approvalLoading = ref(false);

const openApprovalModal = (item) => {
    selectedApproval.value = item;
    approvalNotes.value = '';
    approvalModalOpen.value = true;
};

const handleItemAction = (action) => {
    if (!selectedApproval.value) return;
    approvalLoading.value = true;

    const item = selectedApproval.value;
    let url = '';
    let payload = { notes: approvalNotes.value };

    if (item.status === 'pending') {
        url = `/procurement/items/${item.id}/${action === 'approve' ? 'approve-l1' : 'reject'}`;
        if (action === 'reject') payload.reason = approvalNotes.value || 'Ditolak pada tahap L1';
    } else if (item.status === 'waiting_gm') {
        url = `/procurement/items/${item.id}/${action === 'approve' ? 'approve-l2' : 'reject'}`;
        if (action === 'reject') {
            payload.reason = approvalNotes.value || 'Ditolak oleh GM';
            payload.step = 'gm';
        }
    }

    router.post(url, payload, {
        onSuccess: () => {
            approvalModalOpen.value = false;
            approvalLoading.value = false;
        },
        onError: () => {
            approvalLoading.value = false;
        },
    });
};

const pipelineSteps = computed(() => [
    { key: 'draft', label: '1. Draft', count: props.pipelineCounts?.draft || 0, color: 'bg-slate-500' },
    { key: 'pending_estimate', label: '2. Estimasi Harga', count: props.pipelineCounts?.pending_estimate || 0, color: 'bg-amber-500' },
    { key: 'pending_l1', label: '3. Approval L1 (OM/FAT)', count: props.pipelineCounts?.pending_l1 || 0, color: 'bg-blue-500' },
    { key: 'waiting_quotation', label: '4. Quote Holding', count: props.pipelineCounts?.waiting_quotation || 0, color: 'bg-indigo-500' },
    { key: 'waiting_actual_price', label: '5. Actual & Plan', count: props.pipelineCounts?.waiting_actual_price || 0, color: 'bg-purple-500' },
    { key: 'waiting_gm', label: '6. Approval L2 / Bon', count: props.pipelineCounts?.waiting_gm || 0, color: 'bg-violet-600' },
    { key: 'ready_to_po', label: '7. Ready to PO', count: props.pipelineCounts?.ready_to_po || 0, color: 'bg-teal-500' },
    { key: 'ordered_delivered', label: '8. Ordered / Delivered', count: props.pipelineCounts?.ordered_delivered || 0, color: 'bg-emerald-600' },
]);
</script>

<template>
    <Head title="Procurement Hub - ERP" />

    <ModuleLayout :module="module">
        <div class="space-y-6">
            <!-- Header Section with Quick Actions -->
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-6 shadow-xs">
                <div>
                    <div class="flex items-center gap-3">
                        <div class="w-12 h-12 rounded-xl bg-violet-50 dark:bg-violet-950/50 flex items-center justify-center text-violet-600 dark:text-violet-400">
                            <ShoppingCart class="w-6 h-6" />
                        </div>
                        <div>
                            <h1 class="text-2xl font-bold text-slate-900 dark:text-white tracking-tight">
                                Integrated Procurement Hub
                            </h1>
                            <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">
                                Granular 8-Step Item Workflow &bull; PR Bon Sementara &bull; FAT Budget Control &bull; Sasaran Mutu 2026
                            </p>
                        </div>
                    </div>
                </div>

                <div class="flex flex-wrap items-center gap-2">
                    <Link
                        href="/procurement/purchase-requests/create"
                        class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-violet-600 hover:bg-violet-700 text-white text-xs font-semibold shadow-sm transition-colors"
                    >
                        <Plus class="w-4 h-4" />
                        Buat PR Baru
                    </Link>
                    <Link
                        href="/procurement/pr-bons/staging"
                        class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-amber-500 hover:bg-amber-600 text-white text-xs font-semibold shadow-sm transition-colors"
                    >
                        <Receipt class="w-4 h-4" />
                        Staging Bon
                    </Link>
                    <Link
                        href="/procurement/deliveries"
                        class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200 text-xs font-semibold transition-colors"
                    >
                        <Truck class="w-4 h-4" />
                        Penerimaan Fisik
                    </Link>
                </div>
            </div>

            <!-- Top Row: KPI Stat Cards -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                <KpiStatCard
                    title="Active PRs"
                    :value="kpis.active_pr_count"
                    subtitle="Dokumen PR berjalan"
                    trend-direction="up"
                    trend-value="+3 PR minggu ini"
                    theme="violet"
                />
                <KpiStatCard
                    title="Realisasi Belanja MTD"
                    :value="kpis.total_spend_mtd"
                    subtitle="Total PO diterbitkan"
                    trend-direction="up"
                    trend-value="Sesuai Proyeksi"
                    theme="indigo"
                />
                <KpiStatCard
                    title="Serapan Pagu Anggaran"
                    :value="`${kpis.budget_absorption_pct}%`"
                    subtitle="Realisasi vs Target Bulanan"
                    trend-direction="up"
                    trend-value="Aman (FAT Budget)"
                    theme="emerald"
                />
                <KpiStatCard
                    title="Sasaran Mutu 2026"
                    :value="`${kpis.quality_composite_score} / 100`"
                    :subtitle="kpis.quality_grade"
                    trend-direction="up"
                    trend-value="Bobot 4 Pilar (25%)"
                    theme="sky"
                />
            </div>

            <!-- Granular 8-Step Item Workflow Tracker -->
            <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-6 shadow-xs">
                <div class="flex items-center justify-between mb-4">
                    <div>
                        <h2 class="text-sm font-bold text-slate-900 dark:text-white uppercase tracking-wider">
                            Granular 8-Step Item Workflow Pipeline
                        </h2>
                        <p class="text-xs text-slate-500 dark:text-slate-400">
                            Distribusi baris item pengadaan lintas tahap verifikasi independen
                        </p>
                    </div>
                    <Link
                        href="/procurement/purchase-requests"
                        class="text-xs font-semibold text-violet-600 dark:text-violet-400 hover:underline inline-flex items-center gap-1"
                    >
                        Lihat Seluruh PR
                        <ArrowUpRight class="w-3.5 h-3.5" />
                    </Link>
                </div>

                <div class="grid grid-cols-2 sm:grid-cols-4 lg:grid-cols-8 gap-3">
                    <div
                        v-for="step in pipelineSteps"
                        :key="step.key"
                        class="p-3.5 rounded-xl border border-slate-100 dark:border-slate-800 bg-slate-50/70 dark:bg-slate-800/40 flex flex-col justify-between"
                    >
                        <div class="flex items-center justify-between mb-2">
                            <span :class="['w-2.5 h-2.5 rounded-full', step.color]"></span>
                            <span class="text-lg font-bold text-slate-800 dark:text-slate-100">
                                {{ step.count }}
                            </span>
                        </div>
                        <span class="text-[11px] font-medium text-slate-600 dark:text-slate-300 leading-tight">
                            {{ step.label }}
                        </span>
                    </div>
                </div>
            </div>

            <!-- Tab Navigation -->
            <div class="flex items-center gap-2 border-b border-slate-200 dark:border-slate-800">
                <button
                    @click="activeTab = 'pipeline'"
                    :class="[
                        'px-4 py-3 text-xs font-bold transition-colors border-b-2 -mb-px flex items-center gap-2',
                        activeTab === 'pipeline'
                            ? 'border-violet-600 text-violet-600 dark:text-violet-400 dark:border-violet-400'
                            : 'border-transparent text-slate-500 hover:text-slate-700 dark:text-slate-400 dark:hover:text-slate-200'
                    ]"
                >
                    <FileCheck class="w-4 h-4" />
                    Antrean Persetujuan Item ({{ pendingApprovals.length }})
                </button>
                <button
                    @click="activeTab = 'bons'"
                    :class="[
                        'px-4 py-3 text-xs font-bold transition-colors border-b-2 -mb-px flex items-center gap-2',
                        activeTab === 'bons'
                            ? 'border-violet-600 text-violet-600 dark:text-violet-400 dark:border-violet-400'
                            : 'border-transparent text-slate-500 hover:text-slate-700 dark:text-slate-400 dark:hover:text-slate-200'
                    ]"
                >
                    <Receipt class="w-4 h-4" />
                    Berkas Bon Menunggu GM ({{ pendingBons.length }})
                </button>
                <button
                    @click="activeTab = 'quality'"
                    :class="[
                        'px-4 py-3 text-xs font-bold transition-colors border-b-2 -mb-px flex items-center gap-2',
                        activeTab === 'quality'
                            ? 'border-violet-600 text-violet-600 dark:text-violet-400 dark:border-violet-400'
                            : 'border-transparent text-slate-500 hover:text-slate-700 dark:text-slate-400 dark:hover:text-slate-200'
                    ]"
                >
                    <Target class="w-4 h-4" />
                    Scorecard Sasaran Mutu 2026
                </button>
                <button
                    @click="activeTab = 'charts'"
                    :class="[
                        'px-4 py-3 text-xs font-bold transition-colors border-b-2 -mb-px flex items-center gap-2',
                        activeTab === 'charts'
                            ? 'border-violet-600 text-violet-600 dark:text-violet-400 dark:border-violet-400'
                            : 'border-transparent text-slate-500 hover:text-slate-700 dark:text-slate-400 dark:hover:text-slate-200'
                    ]"
                >
                    <Activity class="w-4 h-4" />
                    Grafik Analitik & Pagu
                </button>
            </div>

            <!-- Tab Content 1: Pending Approvals Inbox -->
            <div v-if="activeTab === 'pipeline'" class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-6 shadow-xs">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-sm font-bold text-slate-900 dark:text-white">
                        Antrean Keputusan Pengadaan (Granular Approval)
                    </h3>
                    <span class="text-xs text-slate-500">1-Klik Review & Approval</span>
                </div>

                <div v-if="pendingApprovals.length === 0" class="text-center py-12 text-slate-400">
                    <CheckCircle2 class="w-10 h-10 mx-auto mb-2 text-emerald-500 opacity-80" />
                    <p class="text-sm font-medium">Seluruh item pengadaan telah diverifikasi.</p>
                </div>

                <div v-else class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="border-b border-slate-200 dark:border-slate-800 text-[11px] font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">
                                <th class="py-3 px-4">No PR / Item</th>
                                <th class="py-3 px-4">Departemen</th>
                                <th class="py-3 px-4">Kuantitas</th>
                                <th class="py-3 px-4">Estimasi / Harga</th>
                                <th class="py-3 px-4">Tahapan Approval</th>
                                <th class="py-3 px-4 text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 dark:divide-slate-800/60 text-xs text-slate-700 dark:text-slate-300">
                            <tr v-for="item in pendingApprovals" :key="item.id" class="hover:bg-slate-50/50 dark:hover:bg-slate-800/30 transition-colors">
                                <td class="py-3 px-4">
                                    <div class="font-bold text-slate-900 dark:text-white">{{ item.item_name }}</div>
                                    <div class="text-[11px] text-violet-600 dark:text-violet-400 font-medium">
                                        {{ item.pr_number }} &bull; Oleh {{ item.requester }}
                                    </div>
                                </td>
                                <td class="py-3 px-4 font-medium">{{ item.department }}</td>
                                <td class="py-3 px-4 font-semibold">{{ item.quantity }}</td>
                                <td class="py-3 px-4 font-bold text-slate-900 dark:text-white">{{ item.amount }}</td>
                                <td class="py-3 px-4">
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[10px] font-bold bg-violet-50 text-violet-700 dark:bg-violet-950/60 dark:text-violet-300">
                                        <Clock class="w-3 h-3" />
                                        {{ item.stage }}
                                    </span>
                                </td>
                                <td class="py-3 px-4 text-right">
                                    <div class="flex items-center justify-end gap-2">
                                        <Link
                                            :href="`/procurement/purchase-requests/${item.pr_id}`"
                                            class="px-2.5 py-1 rounded-lg bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 hover:bg-slate-200 text-xs font-semibold transition-colors"
                                        >
                                            Detail
                                        </Link>
                                        <button
                                            @click="openApprovalModal(item)"
                                            class="px-3 py-1 rounded-lg bg-violet-600 hover:bg-violet-700 text-white text-xs font-semibold shadow-xs transition-colors"
                                        >
                                            Review
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Tab Content 2: Pending Bon Dossiers -->
            <div v-if="activeTab === 'bons'" class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-6 shadow-xs">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-sm font-bold text-slate-900 dark:text-white">
                        Berkas Bon Menunggu Keputusan General Manager
                    </h3>
                    <Link
                        href="/procurement/pr-bons/staging"
                        class="text-xs font-semibold text-amber-600 hover:underline"
                    >
                        + Buka Staging Pengadaan Bon
                    </Link>
                </div>

                <div v-if="pendingBons.length === 0" class="text-center py-12 text-slate-400">
                    <CheckCircle2 class="w-10 h-10 mx-auto mb-2 text-emerald-500 opacity-80" />
                    <p class="text-sm font-medium">Tidak ada berkas bon yang menunggu keputusan GM saat ini.</p>
                </div>

                <div v-else class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                    <div
                        v-for="bon in pendingBons"
                        :key="bon.id"
                        class="p-5 rounded-xl border border-amber-200 dark:border-amber-950/60 bg-amber-50/40 dark:bg-amber-950/20 flex flex-col justify-between"
                    >
                        <div>
                            <div class="flex items-center justify-between mb-2">
                                <span class="text-xs font-mono font-bold text-amber-700 dark:text-amber-400">
                                    {{ bon.bon_number }}
                                </span>
                                <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-amber-100 text-amber-800 dark:bg-amber-900 dark:text-amber-200">
                                    Menunggu GM
                                </span>
                            </div>
                            <h4 class="font-bold text-slate-900 dark:text-white text-sm mb-1">
                                {{ bon.vendor_name }}
                            </h4>
                            <p class="text-xs text-slate-500 dark:text-slate-400 mb-3">
                                {{ bon.item_count }} Item Gabungan &bull; Diajukan: {{ bon.date }}
                            </p>
                            <div class="text-base font-extrabold text-amber-600 dark:text-amber-400 mb-4">
                                {{ bon.total_amount }}
                            </div>
                        </div>

                        <div class="flex items-center gap-2 pt-3 border-t border-amber-200/60 dark:border-amber-950/60">
                            <Link
                                :href="`/procurement/pr-bons/${bon.id}`"
                                class="flex-1 text-center py-1.5 rounded-lg bg-amber-600 hover:bg-amber-700 text-white text-xs font-semibold transition-colors"
                            >
                                Periksa Berkas & Keputusan GM
                            </Link>
                            <a
                                :href="`/procurement/pr-bons/${bon.id}/print`"
                                target="_blank"
                                class="p-1.5 rounded-lg bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-slate-600 hover:bg-slate-100"
                            >
                                <FileText class="w-4 h-4" />
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Tab Content 3: Sasaran Mutu 2026 Scorecard -->
            <div v-if="activeTab === 'quality'" class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-6 shadow-xs">
                <div class="flex items-center justify-between mb-4">
                    <div>
                        <h3 class="text-sm font-bold text-slate-900 dark:text-white">
                            Evaluasi Sasaran Mutu Procurement 2026 (ISO 9001:2015)
                        </h3>
                        <p class="text-xs text-slate-500">Bobot seimbang 4 pilar kunci (masing-masing 25%)</p>
                    </div>
                    <div class="flex items-center gap-3">
                        <span class="text-xs font-bold text-slate-600 dark:text-slate-300">
                            Skor Komposit: <strong class="text-emerald-600">{{ kpis.quality_composite_score }} / 100</strong> ({{ kpis.quality_grade }})
                        </span>
                        <a
                            href="/procurement/sasaran-mutu/pdf"
                            target="_blank"
                            class="px-3 py-1.5 rounded-lg bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 text-slate-700 dark:text-slate-300 text-xs font-semibold inline-flex items-center gap-1.5"
                        >
                            <FileText class="w-3.5 h-3.5" />
                            Cetak Laporan PDF
                        </a>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div
                        v-for="kpi in qualityTargets"
                        :key="kpi.id"
                        class="p-4 rounded-xl border border-slate-200 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-800/40"
                    >
                        <div class="flex items-start justify-between gap-3 mb-2">
                            <div>
                                <span class="text-[10px] font-bold text-violet-600 dark:text-violet-400 uppercase tracking-wider">
                                    Pilar {{ kpi.id }} &bull; Bobot {{ kpi.weight }}%
                                </span>
                                <h4 class="font-bold text-slate-900 dark:text-white text-xs mt-0.5">
                                    {{ kpi.title }}
                                </h4>
                            </div>
                            <span :class="['px-2.5 py-1 rounded-full text-xs font-extrabold', kpi.score >= 90 ? 'bg-emerald-100 text-emerald-800 dark:bg-emerald-950 dark:text-emerald-300' : 'bg-amber-100 text-amber-800 dark:bg-amber-950 dark:text-amber-300']">
                                Skor: {{ kpi.score }}
                            </span>
                        </div>
                        <div class="text-[11px] text-slate-600 dark:text-slate-400 space-y-1 mt-3 pt-2 border-t border-slate-200 dark:border-slate-700/60">
                            <div class="flex justify-between">
                                <span>Target Standar:</span>
                                <span class="font-semibold text-slate-800 dark:text-slate-200">{{ kpi.target }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span>Realisasi Aktual:</span>
                                <span class="font-bold text-violet-600 dark:text-violet-400">{{ kpi.actual }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Tab Content 4: Charts -->
            <div v-if="activeTab === 'charts'" class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <InteractiveChartCard
                    title="Distribusi Tahapan Granular 8-Step"
                    subtitle="Jumlah item PR berdasarkan posisi alur kerja saat ini"
                    type="bar"
                    :series="charts.pipeline_stages?.series || []"
                    :categories="charts.pipeline_stages?.categories || []"
                    height="320"
                />
                <InteractiveChartCard
                    title="Realisasi Pengadaan per Departemen"
                    subtitle="Akumulasi nominal belanja (Juta Rupiah)"
                    type="bar"
                    :series="charts.department_spend?.series || []"
                    :categories="charts.department_spend?.categories || []"
                    height="320"
                />
            </div>
        </div>

        <!-- Single Modal for 1-Click Review & Approval -->
        <div v-if="approvalModalOpen" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-xs">
            <div class="bg-white dark:bg-slate-900 rounded-2xl max-w-md w-full p-6 shadow-2xl border border-slate-200 dark:border-slate-800">
                <h3 class="text-base font-bold text-slate-900 dark:text-white mb-2">
                    Review Item: {{ selectedApproval?.item_name }}
                </h3>
                <p class="text-xs text-slate-500 mb-4">
                    {{ selectedApproval?.pr_number }} &bull; {{ selectedApproval?.department }} &bull; {{ selectedApproval?.amount }}
                </p>

                <div class="mb-4">
                    <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">
                        Catatan Persetujuan / Alasan Penolakan:
                    </label>
                    <textarea
                        v-model="approvalNotes"
                        rows="3"
                        class="w-full text-xs rounded-xl border border-slate-300 dark:border-slate-700 dark:bg-slate-800 p-2.5 focus:ring-2 focus:ring-violet-500"
                        placeholder="Masukkan catatan spesifikasi, validasi harga, atau justifikasi..."
                    ></textarea>
                </div>

                <div class="flex items-center justify-end gap-2">
                    <button
                        @click="approvalModalOpen = false"
                        class="px-4 py-2 rounded-xl text-xs font-semibold text-slate-600 hover:bg-slate-100 dark:text-slate-400 dark:hover:bg-slate-800"
                    >
                        Batal
                    </button>
                    <button
                        @click="handleItemAction('reject')"
                        :disabled="approvalLoading"
                        class="px-4 py-2 rounded-xl text-xs font-semibold bg-rose-600 hover:bg-rose-700 text-white"
                    >
                        Tolak Item
                    </button>
                    <button
                        @click="handleItemAction('approve')"
                        :disabled="approvalLoading"
                        class="px-4 py-2 rounded-xl text-xs font-semibold bg-emerald-600 hover:bg-emerald-700 text-white"
                    >
                        Setujui Item
                    </button>
                </div>
            </div>
        </div>
    </ModuleLayout>
</template>
