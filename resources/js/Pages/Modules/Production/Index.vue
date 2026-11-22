<script setup>
import { Head } from '@inertiajs/vue3';
import ModuleLayout from '@/Layouts/ModuleLayout.vue';
import KpiStatCard from '@/Components/Dashboard/KpiStatCard.vue';
import InteractiveChartCard from '@/Components/Dashboard/InteractiveChartCard.vue';
import ApprovalInboxWidget from '@/Components/Dashboard/ApprovalInboxWidget.vue';
import QuickActionsBar from '@/Components/Dashboard/QuickActionsBar.vue';
import ActivityFeedWidget from '@/Components/Dashboard/ActivityFeedWidget.vue';
import { Factory, Timer } from 'lucide-vue-next';

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
    { label: '+ Buka Batch Baru (WPS)', icon: 'Plus', action: 'create_batch', primary: true },
    { label: 'Live Line Heat Grid', icon: 'Play', action: 'heat_grid' },
    { label: 'Input Alasan Downtime', icon: 'AlertTriangle', action: 'downtime' },
];

const chartOptions = {
    chart: {
        type: 'radialBar',
        height: 290,
    },
    colors: ['#e11d48', '#f59e0b', '#10b981'],
    plotOptions: {
        radialBar: {
            dataLabels: {
                name: { fontSize: '12px' },
                value: { fontSize: '14px', fontWeight: 'bold' },
                total: {
                    show: true,
                    label: 'Overall OEE',
                    formatter: function () {
                        return '84.6%';
                    },
                },
            },
        },
    },
    labels: ['Availability', 'Performance', 'Quality Rate'],
};

const chartSeries = [91.2, 94.0, 98.4];
</script>

<template>
    <ModuleLayout
        :title="module?.name"
        :module-slug="module?.slug || 'production'"
        :module-name="module?.name || 'Produksi & Manufaktur'"
        :color-theme="module?.color_theme || 'rose'"
        :menus="module?.menus || []"
        :breadcrumbs="['Dashboard Lantai Pabrik']"
    >
        <Head :title="module?.name" />

        <div class="space-y-6">
            <!-- Header -->
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 pb-4 border-b border-slate-200 dark:border-slate-800">
                <div class="flex items-center gap-3">
                    <div class="w-12 h-12 rounded-xl bg-rose-50 dark:bg-rose-950/60 text-rose-600 dark:text-rose-400 flex items-center justify-center border border-rose-200 dark:border-rose-800 shrink-0">
                        <Factory class="w-6 h-6" />
                    </div>
                    <div>
                        <div class="flex items-center gap-2">
                            <h1 class="text-xl font-bold text-slate-900 dark:text-white">{{ module?.name }}</h1>
                            <span class="text-xs px-2 py-0.5 rounded bg-rose-100 dark:bg-rose-950 text-rose-800 dark:text-rose-300 font-medium">Modul 5</span>
                        </div>
                        <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">{{ module?.description }}</p>
                    </div>
                </div>

                <QuickActionsBar :actions="quickActions" />
            </div>

            <!-- KPI Cards -->
            <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
                <KpiStatCard
                    title="Overall OEE %"
                    :value="`${kpis?.overall_oee_pct}%`"
                    :trend="2.4"
                    subtitle="Standar Kelas Dunia: > 85%"
                    status="rose"
                    :sparkline-data="[80, 81, 82, 83, 83.5, 84, 84.6]"
                />

                <KpiStatCard
                    title="Line Availability"
                    :value="`${kpis?.line_availability_pct}%`"
                    :trend="1.5"
                    subtitle="Unplanned Downtime Rendah"
                    status="emerald"
                    :sparkline-data="[88, 89, 90, 90.5, 91, 91, 91.2]"
                />

                <KpiStatCard
                    title="Performance Rate"
                    :value="`${kpis?.performance_rate_pct}%`"
                    :trend="0.8"
                    subtitle="Kecepatan Mesin Stabil"
                    status="blue"
                    :sparkline-data="[92, 92.5, 93, 93.5, 93.8, 94, 94]"
                />

                <KpiStatCard
                    title="Batch Yield Realization"
                    :value="`${kpis?.batch_yield_pct}%`"
                    :trend="0.5"
                    subtitle="Reject < 1.6% (CPOB Valid)"
                    status="emerald"
                    :sparkline-data="[97.5, 97.8, 98, 98.1, 98.2, 98.3, 98.4]"
                />
            </div>

            <!-- Chart & Approval Inbox -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <div class="lg:col-span-2">
                    <InteractiveChartCard
                        title="Indikator 3 Pilar Efisiensi OEE Mesin"
                        subtitle="Kalkulasi Real-Time Line Mixing & Filling Ekstrak Herbal"
                        type="radialBar"
                        :height="300"
                        :options="chartOptions"
                        :series="chartSeries"
                        selected-period="Hari Ini"
                    />
                </div>

                <div>
                    <ApprovalInboxWidget
                        title="Approval SPV Produksi"
                        :items="pendingApprovals"
                        module-slug="production"
                    />
                </div>
            </div>

            <!-- Bottom Row -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <div class="bg-white dark:bg-slate-900 rounded-xl border border-slate-200 dark:border-slate-800 p-5 shadow-xs">
                    <div class="flex items-center gap-2 mb-3">
                        <Timer class="w-4 h-4 text-rose-600 dark:text-rose-400" />
                        <h3 class="font-bold text-slate-900 dark:text-white text-sm">Estafet Batch Tracking System</h3>
                    </div>
                    <p class="text-xs text-slate-500 dark:text-slate-400 mb-4">
                        Mixing &rarr; Filling &rarr; Packing &rarr; Packing Hold (Karantina QA).
                    </p>
                    <div class="space-y-2 text-xs">
                        <div class="p-2.5 rounded-lg bg-rose-50/60 dark:bg-rose-950/40 border border-rose-100 dark:border-rose-800">
                            <div class="font-semibold text-rose-900 dark:text-rose-200">Batch #EXT-2026-088</div>
                            <div class="text-slate-500 dark:text-slate-400 text-[11px] mt-0.5">Ekstrak Temulawak 500kg • Tahap Filling</div>
                        </div>
                        <div class="p-2.5 rounded-lg bg-emerald-50/60 dark:bg-emerald-950/40 border border-emerald-100 dark:border-emerald-800">
                            <div class="font-semibold text-emerald-900 dark:text-emerald-200">Batch #CAP-2026-042</div>
                            <div class="text-slate-500 dark:text-slate-400 text-[11px] mt-0.5">Kapsul Meniran • Packing Hold QA Lulus</div>
                        </div>
                    </div>
                </div>

                <div class="lg:col-span-2">
                    <ActivityFeedWidget
                        title="Audit Log Eksekusi Batch & Mesin"
                        :activities="recentActivities"
                    />
                </div>
            </div>
        </div>
    </ModuleLayout>
</template>
