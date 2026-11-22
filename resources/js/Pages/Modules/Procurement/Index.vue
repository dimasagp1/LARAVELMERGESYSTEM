<script setup>
import { Head } from '@inertiajs/vue3';
import ModuleLayout from '@/Layouts/ModuleLayout.vue';
import KpiStatCard from '@/Components/Dashboard/KpiStatCard.vue';
import InteractiveChartCard from '@/Components/Dashboard/InteractiveChartCard.vue';
import ApprovalInboxWidget from '@/Components/Dashboard/ApprovalInboxWidget.vue';
import QuickActionsBar from '@/Components/Dashboard/QuickActionsBar.vue';
import ActivityFeedWidget from '@/Components/Dashboard/ActivityFeedWidget.vue';
import { ShoppingCart, Building } from 'lucide-vue-next';

const props = defineProps({
    module: Object,
    kpis: Object,
    pendingApprovals: {
        type: Array,
        default: () => [],
    },
    recentActivities: {
        type: Array,
        default: () => [],
    },
});

const quickActions = [
    { label: '+ Buat Purchase Request (PR)', icon: 'Plus', action: 'create_pr', primary: true },
    { label: 'Quotation Matrix Bidding', icon: 'FileSpreadsheet', action: 'bidding' },
    { label: 'PR Bon Sementara (Urgent)', icon: 'ShoppingCart', action: 'urgent_pr' },
];

const chartOptions = {
    chart: {
        type: 'donut',
        height: 290,
    },
    colors: ['#8b5cf6', '#a78bfa', '#c4b5fd', '#10b981'],
    labels: ['Raw Material (RM)', 'Packaging Material (PM)', 'Fasilitas & Mesin', 'Umum & ATK'],
    legend: { position: 'bottom' },
};

const chartSeries = [450, 280, 120, 70];
</script>

<template>
    <ModuleLayout
        :title="module?.name"
        :module-slug="module?.slug || 'procurement'"
        :module-name="module?.name || 'Procurement'"
        :color-theme="module?.color_theme || 'violet'"
        :menus="module?.menus || []"
        :breadcrumbs="['Dashboard Pengadaan']"
    >
        <Head :title="module?.name" />

        <div class="space-y-6">
            <!-- Header -->
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 pb-4 border-b border-slate-200 dark:border-slate-800">
                <div class="flex items-center gap-3">
                    <div class="w-12 h-12 rounded-xl bg-violet-50 dark:bg-violet-950/60 text-violet-600 dark:text-violet-400 flex items-center justify-center border border-violet-200 dark:border-violet-800 shrink-0">
                        <ShoppingCart class="w-6 h-6" />
                    </div>
                    <div>
                        <div class="flex items-center gap-2">
                            <h1 class="text-xl font-bold text-slate-900 dark:text-white">{{ module?.name }}</h1>
                            <span class="text-xs px-2 py-0.5 rounded bg-violet-100 dark:bg-violet-950 text-violet-800 dark:text-violet-300 font-medium">Modul 4</span>
                        </div>
                        <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">{{ module?.description }}</p>
                    </div>
                </div>

                <QuickActionsBar :actions="quickActions" />
            </div>

            <!-- KPI Cards -->
            <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
                <KpiStatCard
                    title="Total Active PR"
                    :value="kpis?.active_pr_count"
                    :trend="-5.5"
                    subtitle="Granular 8-Step Approval"
                    status="violet"
                    :sparkline-data="[24, 22, 21, 20, 19, 18, 18]"
                />

                <KpiStatCard
                    title="Nilai Total PO MTD"
                    :value="kpis?.po_value_mtd"
                    :trend="14.2"
                    subtitle="Committed Budget Active"
                    status="emerald"
                    :sparkline-data="[720, 750, 800, 840, 870, 900, 920]"
                />

                <KpiStatCard
                    title="PR-to-PO Lead Time"
                    :value="`${kpis?.avg_lead_time_days} Hari`"
                    :trend="-12.0"
                    subtitle="Target: < 7 Hari"
                    status="emerald"
                    :sparkline-data="[6.8, 6.4, 6.0, 5.8, 5.5, 5.3, 5.2]"
                />

                <KpiStatCard
                    title="Cost Saving Pengadaan"
                    :value="`${kpis?.cost_saving_pct}%`"
                    :trend="2.4"
                    subtitle="Hasil Bidding 3 Supplier"
                    status="violet"
                    :sparkline-data="[5.0, 5.5, 6.0, 6.5, 7.0, 7.5, 7.8]"
                />
            </div>

            <!-- Chart & Approval Inbox -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <div class="lg:col-span-2">
                    <InteractiveChartCard
                        title="Distribusi Nilai PO per Kategori Pengadaan (Juta Rp)"
                        subtitle="Integrasi Odoo PO (JSON-RPC) & Kontrol Anggaran Real-Time"
                        type="donut"
                        :height="300"
                        :options="chartOptions"
                        :series="chartSeries"
                        selected-period="MTD"
                    />
                </div>

                <div>
                    <ApprovalInboxWidget
                        title="Antrean Approval Pengadaan"
                        :items="pendingApprovals"
                        module-slug="procurement"
                    />
                </div>
            </div>

            <!-- Bottom Row -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <div class="bg-white dark:bg-slate-900 rounded-xl border border-slate-200 dark:border-slate-800 p-5 shadow-xs">
                    <div class="flex items-center gap-2 mb-3">
                        <Building class="w-4 h-4 text-violet-600 dark:text-violet-400" />
                        <h3 class="font-bold text-slate-900 dark:text-white text-sm">Evaluasi Pemasok (AVL)</h3>
                    </div>
                    <p class="text-xs text-slate-500 dark:text-slate-400 mb-4">
                        Penilaian kepatuhan 4 sasaran mutu: Waktu, Kuantitas, Kualitas, dan Harga.
                    </p>
                    <div class="space-y-2 text-xs">
                        <div class="p-2.5 rounded-lg bg-violet-50/60 dark:bg-violet-950/40 border border-violet-100 dark:border-violet-800 flex items-center justify-between">
                            <div>
                                <div class="font-semibold text-violet-900 dark:text-violet-200">PT Herbalindo Ekstrak Mandiri</div>
                                <div class="text-slate-500 dark:text-slate-400 text-[11px]">RM Ekstrak • AVL Grade A</div>
                            </div>
                            <span class="font-mono font-bold text-emerald-600 dark:text-emerald-400">96.5</span>
                        </div>
                        <div class="p-2.5 rounded-lg bg-violet-50/60 dark:bg-violet-950/40 border border-violet-100 dark:border-violet-800 flex items-center justify-between">
                            <div>
                                <div class="font-semibold text-violet-900 dark:text-violet-200">CV Packindo Sejahtera</div>
                                <div class="text-slate-500 dark:text-slate-400 text-[11px]">Botol Amber & Blister • Grade A</div>
                            </div>
                            <span class="font-mono font-bold text-emerald-600 dark:text-emerald-400">94.0</span>
                        </div>
                    </div>
                </div>

                <div class="lg:col-span-2">
                    <ActivityFeedWidget
                        title="Audit Log Transaksi Pengadaan & Odoo Sync"
                        :activities="recentActivities"
                    />
                </div>
            </div>
        </div>
    </ModuleLayout>
</template>
