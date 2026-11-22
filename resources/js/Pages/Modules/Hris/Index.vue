<script setup>
import { Head } from '@inertiajs/vue3';
import ModuleLayout from '@/Layouts/ModuleLayout.vue';
import KpiStatCard from '@/Components/Dashboard/KpiStatCard.vue';
import InteractiveChartCard from '@/Components/Dashboard/InteractiveChartCard.vue';
import ApprovalInboxWidget from '@/Components/Dashboard/ApprovalInboxWidget.vue';
import QuickActionsBar from '@/Components/Dashboard/QuickActionsBar.vue';
import ActivityFeedWidget from '@/Components/Dashboard/ActivityFeedWidget.vue';
import { Users, Award } from 'lucide-vue-next';

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
    { label: '+ Input Kaizen Kizuki', icon: 'Award', action: 'create_kaizen', primary: true },
    { label: 'Run Payroll TER', icon: 'Calculator', action: 'run_payroll' },
    { label: 'Roster & Time Bank', icon: 'Plus', action: 'roster' },
];

const chartOptions = {
    chart: {
        type: 'line',
        height: 290,
        toolbar: { show: false },
    },
    colors: ['#2563eb', '#10b981'],
    stroke: { curve: 'smooth', width: [3, 2] },
    xaxis: {
        categories: ['Sen', 'Sel', 'Rab', 'Kam', 'Jum', 'Sab', 'Min'],
    },
};

const chartSeries = [
    {
        name: 'Presensi Karyawan (%)',
        data: [98.2, 97.4, 96.8, 97.1, 95.8, 94.0, 92.5],
    },
    {
        name: 'Target Sasaran (%)',
        data: [95, 95, 95, 95, 95, 95, 95],
    },
];
</script>

<template>
    <ModuleLayout
        :title="module?.name"
        :module-slug="module?.slug || 'hris'"
        :module-name="module?.name || 'HRIS (HRISNOW)'"
        :color-theme="module?.color_theme || 'blue'"
        :menus="module?.menus || []"
        :breadcrumbs="['Dashboard SDM & Presensi']"
    >
        <Head :title="module?.name" />

        <div class="space-y-6">
            <!-- Header -->
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 pb-4 border-b border-slate-200 dark:border-slate-800">
                <div class="flex items-center gap-3">
                    <div class="w-12 h-12 rounded-xl bg-blue-50 dark:bg-blue-950/60 text-blue-600 dark:text-blue-400 flex items-center justify-center border border-blue-200 dark:border-blue-800 shrink-0">
                        <Users class="w-6 h-6" />
                    </div>
                    <div>
                        <div class="flex items-center gap-2">
                            <h1 class="text-xl font-bold text-slate-900 dark:text-white">{{ module?.name }}</h1>
                            <span class="text-xs px-2 py-0.5 rounded bg-blue-100 dark:bg-blue-950 text-blue-800 dark:text-blue-300 font-medium">Modul 3</span>
                        </div>
                        <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">{{ module?.description }}</p>
                    </div>
                </div>

                <QuickActionsBar :actions="quickActions" />
            </div>

            <!-- KPI Cards -->
            <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
                <KpiStatCard
                    title="Total Headcount"
                    :value="kpis?.total_headcount"
                    :trend="3.5"
                    subtitle="160 TKL • 88 TKTL"
                    status="blue"
                    :sparkline-data="[230, 235, 238, 240, 242, 245, 248]"
                />

                <KpiStatCard
                    title="Presensi Hari Ini"
                    :value="`${kpis?.attendance_today_pct}%`"
                    :trend="1.8"
                    subtitle="Face Matching & Geofence"
                    status="emerald"
                    :sparkline-data="[95, 96, 95.5, 96.2, 96.5, 96.0, 96.8]"
                />

                <KpiStatCard
                    title="Total Lembur MTD"
                    :value="`${kpis?.overtime_hours_mtd} Jam`"
                    :trend="-4.2"
                    subtitle="Time Bank: +48 Jam"
                    status="blue"
                    :sparkline-data="[340, 335, 330, 325, 320, 315, 312]"
                />

                <KpiStatCard
                    title="Rata-rata Skor BSC L3/L4"
                    :value="`${kpis?.performance_bsc_score} / 100`"
                    :trend="2.0"
                    subtitle="35 Sasaran Mutu Terkait"
                    status="emerald"
                    :sparkline-data="[88, 89, 89.5, 90, 90.5, 91, 91.2]"
                />
            </div>

            <!-- Chart & Approval Inbox -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <div class="lg:col-span-2">
                    <InteractiveChartCard
                        title="Tren Presensi Karyawan Mingguan vs Target"
                        subtitle="Verifikasi Face Matching & Anti-Fake GPS Geofencing (Sukoharjo & Boyolali)"
                        type="line"
                        :height="300"
                        :options="chartOptions"
                        :series="chartSeries"
                        selected-period="Hari Ini"
                    />
                </div>

                <div>
                    <ApprovalInboxWidget
                        title="Antrean Approval SDM"
                        :items="pendingApprovals"
                        module-slug="hris"
                    />
                </div>
            </div>

            <!-- Bottom Row -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <div class="bg-white dark:bg-slate-900 rounded-xl border border-slate-200 dark:border-slate-800 p-5 shadow-xs">
                    <div class="flex items-center gap-2 mb-3">
                        <Award class="w-4 h-4 text-blue-600 dark:text-blue-400" />
                        <h3 class="font-bold text-slate-900 dark:text-white text-sm">Kaizen Kizuki Feed</h3>
                    </div>
                    <p class="text-xs text-slate-500 dark:text-slate-400 mb-4">
                        Sistem ide perbaikan lantai pabrik operator dengan akumulasi reward point.
                    </p>
                    <div class="space-y-2 text-xs">
                        <div class="p-2.5 rounded-lg bg-blue-50/60 dark:bg-blue-950/40 border border-blue-100 dark:border-blue-800">
                            <div class="font-semibold text-blue-900 dark:text-blue-200">Optimalisasi Jig Filling Line 2</div>
                            <div class="text-slate-500 dark:text-slate-400 text-[11px] mt-0.5">Oleh: Joko (Prod) • +25 Poin</div>
                        </div>
                        <div class="p-2.5 rounded-lg bg-blue-50/60 dark:bg-blue-950/40 border border-blue-100 dark:border-blue-800">
                            <div class="font-semibold text-blue-900 dark:text-blue-200">Reduksi Waste Botol Kaca 100ml</div>
                            <div class="text-slate-500 dark:text-slate-400 text-[11px] mt-0.5">Oleh: Siti (QC) • +30 Poin</div>
                        </div>
                    </div>
                </div>

                <div class="lg:col-span-2">
                    <ActivityFeedWidget
                        title="Log Presensi & Payroll Karyawan"
                        :activities="recentActivities"
                    />
                </div>
            </div>
        </div>
    </ModuleLayout>
</template>
