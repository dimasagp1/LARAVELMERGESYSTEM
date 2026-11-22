<script setup>
import { ref } from 'vue';
import { Head } from '@inertiajs/vue3';
import ModuleLayout from '@/Layouts/ModuleLayout.vue';
import KpiStatCard from '@/Components/Dashboard/KpiStatCard.vue';
import InteractiveChartCard from '@/Components/Dashboard/InteractiveChartCard.vue';
import ApprovalInboxWidget from '@/Components/Dashboard/ApprovalInboxWidget.vue';
import QuickActionsBar from '@/Components/Dashboard/QuickActionsBar.vue';
import ActivityFeedWidget from '@/Components/Dashboard/ActivityFeedWidget.vue';
import {
    Landmark,
    ShieldAlert,
    FileSpreadsheet,
    Scale,
    TrendingUp,
} from 'lucide-vue-next';

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
    { label: '+ Ajukan Jurnal AJE', icon: 'Plus', action: 'create_aje', primary: true },
    { label: 'Cek Pagu Gatekeeper', icon: 'Shield', action: 'check_budget' },
    { label: 'Trial Balance 4-Kolom', icon: 'FileSpreadsheet', action: 'trial_balance' },
    { label: 'Export Laporan Keuangan', icon: 'Download', action: 'export' },
];

const chartOptions = {
    chart: {
        type: 'area',
        height: 290,
        toolbar: { show: false },
    },
    colors: ['#059669', '#3b82f6'],
    dataLabels: { enabled: false },
    stroke: { curve: 'smooth', width: 2 },
    xaxis: {
        categories: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'],
    },
};

const chartSeries = [
    {
        name: 'Actual P&L (Juta Rp)',
        data: [120, 140, 135, 180, 210, 240, 230, 260, 290, 310, 385, 410],
    },
    {
        name: 'Budget Plan (Juta Rp)',
        data: [130, 130, 150, 160, 200, 220, 220, 250, 270, 300, 350, 380],
    },
];

function handleQuickAction(actionName) {
    alert(`Memicu aksi cepat: ${actionName}`);
}

function handleKpiFilter(kpiTitle) {
    console.log('Filter diterapkan untuk:', kpiTitle);
}
</script>

<template>
    <ModuleLayout
        :title="module?.name"
        :module-slug="module?.slug || 'finance'"
        :module-name="module?.name || 'Finance & Budgeting'"
        :color-theme="module?.color_theme || 'emerald'"
        :menus="module?.menus || []"
        :breadcrumbs="['Dashboard Keuangan']"
    >
        <Head :title="module?.name" />

        <div class="space-y-6">
            <!-- Header Section with Quick Actions Bar -->
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 pb-4 border-b border-slate-200 dark:border-slate-800">
                <div class="flex items-center gap-3">
                    <div class="w-12 h-12 rounded-xl bg-emerald-50 dark:bg-emerald-950/60 text-emerald-600 dark:text-emerald-400 flex items-center justify-center border border-emerald-200 dark:border-emerald-800 shrink-0">
                        <Landmark class="w-6 h-6" />
                    </div>
                    <div>
                        <div class="flex items-center gap-2">
                            <h1 class="text-xl font-bold text-slate-900 dark:text-white">{{ module?.name }}</h1>
                            <span class="text-xs px-2 py-0.5 rounded bg-emerald-100 dark:bg-emerald-950 text-emerald-800 dark:text-emerald-300 font-medium">Modul 1</span>
                        </div>
                        <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">{{ module?.description }}</p>
                    </div>
                </div>

                <!-- Quick Actions Bar Component -->
                <QuickActionsBar :actions="quickActions" @action="handleQuickAction" />
            </div>

            <!-- Metric KPI Stat Cards -->
            <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
                <KpiStatCard
                    title="Omset Penjualan MTD"
                    :value="kpis?.omset_mtd"
                    :trend="12.4"
                    trend-label="vs target bulanan"
                    status="emerald"
                    :sparkline-data="[110, 125, 120, 135, 140, 138, 145]"
                    @filter="handleKpiFilter"
                />

                <KpiStatCard
                    title="Laba Bersih MTD"
                    :value="kpis?.net_profit_mtd"
                    :trend="8.5"
                    subtitle="Margin Bersih: 26.5%"
                    status="emerald"
                    :sparkline-data="[28, 32, 30, 34, 36, 35, 38.5]"
                    @filter="handleKpiFilter"
                />

                <KpiStatCard
                    title="Penyerapan Budget Pagu"
                    :value="`${kpis?.budget_absorption_pct}%`"
                    :trend="-3.2"
                    subtitle="Threshold Aman (< 85%)"
                    status="blue"
                    :sparkline-data="[50, 55, 58, 62, 65, 67, 68.4]"
                    @filter="handleKpiFilter"
                />

                <KpiStatCard
                    title="Skor 19 Rasio BSC"
                    :value="`${kpis?.bsc_finance_score} / 100`"
                    :trend="4.0"
                    subtitle="Pilar Finansial Grade A"
                    status="emerald"
                    :sparkline-data="[80, 82, 84, 85, 86, 87, 88.5]"
                    @filter="handleKpiFilter"
                />
            </div>

            <!-- Center Row: Interactive ApexChart & Approval Inbox -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- Interactive Chart Card Component -->
                <div class="lg:col-span-2">
                    <InteractiveChartCard
                        title="Budget vs Actual P&L 12 Bulan (Juta Rp)"
                        subtitle="Drill-down per Cost Center & Real-Time Performance"
                        type="area"
                        :height="300"
                        :options="chartOptions"
                        :series="chartSeries"
                        :periods="['Hari Ini', 'MTD', 'QTD', 'YTD']"
                        selected-period="YTD"
                    />
                </div>

                <!-- Approval Inbox Widget Component -->
                <div>
                    <ApprovalInboxWidget
                        title="Antrean Approval FAT"
                        :items="pendingApprovals"
                        module-slug="finance"
                    />
                </div>
            </div>

            <!-- Bottom Row: Real-Time Gatekeeper Status & Unalterable Activity Feed -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- Budget Gatekeeper Panel -->
                <div class="bg-white dark:bg-slate-900 rounded-xl border border-slate-200 dark:border-slate-800 p-5 shadow-xs">
                    <div class="flex items-center gap-2 mb-3">
                        <ShieldAlert class="w-4 h-4 text-emerald-600 dark:text-emerald-400" />
                        <h3 class="font-bold text-slate-900 dark:text-white text-sm">Real-Time Budget Gatekeeper API</h3>
                    </div>
                    <p class="text-xs text-slate-500 dark:text-slate-400 mb-4">
                        API internal aktif yang memverifikasi pagu anggaran sebelum transaksi disetujui di Procurement dan GA.
                    </p>

                    <div class="space-y-2 text-xs">
                        <div class="flex items-center justify-between p-2.5 rounded-lg bg-emerald-50/60 dark:bg-emerald-950/40 border border-emerald-100 dark:border-emerald-800">
                            <span class="font-medium text-emerald-900 dark:text-emerald-300">Procurement PR Approval</span>
                            <span class="text-emerald-700 dark:text-emerald-400 font-bold font-mono">GATED / AUTO-BLOCK</span>
                        </div>
                        <div class="flex items-center justify-between p-2.5 rounded-lg bg-emerald-50/60 dark:bg-emerald-950/40 border border-emerald-100 dark:border-emerald-800">
                            <span class="font-medium text-emerald-900 dark:text-emerald-300">GA Facility WO Expenses</span>
                            <span class="text-emerald-700 dark:text-emerald-400 font-bold font-mono">GATED / AUTO-BLOCK</span>
                        </div>
                        <div class="flex items-center justify-between p-2.5 rounded-lg bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700">
                            <span class="font-medium text-slate-700 dark:text-slate-300">3-Way Matching AP Engine</span>
                            <span class="text-blue-600 dark:text-blue-400 font-bold font-mono">AUTO-RECONCILE</span>
                        </div>
                    </div>
                </div>

                <!-- Activity Feed Widget Component -->
                <div class="lg:col-span-2">
                    <ActivityFeedWidget
                        title="Audit Trail Keuangan & Jurnal Terkini"
                        :activities="recentActivities"
                    />
                </div>
            </div>
        </div>
    </ModuleLayout>
</template>
