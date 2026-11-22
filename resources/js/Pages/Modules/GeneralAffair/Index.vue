<script setup>
import { Head } from '@inertiajs/vue3';
import ModuleLayout from '@/Layouts/ModuleLayout.vue';
import KpiStatCard from '@/Components/Dashboard/KpiStatCard.vue';
import InteractiveChartCard from '@/Components/Dashboard/InteractiveChartCard.vue';
import ApprovalInboxWidget from '@/Components/Dashboard/ApprovalInboxWidget.vue';
import QuickActionsBar from '@/Components/Dashboard/QuickActionsBar.vue';
import ActivityFeedWidget from '@/Components/Dashboard/ActivityFeedWidget.vue';
import { Building2, QrCode } from 'lucide-vue-next';

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
    { label: '+ Buat Work Order (Form A)', icon: 'Plus', action: 'create_wo', primary: true },
    { label: 'Permintaan ATK & RT', icon: 'FileSpreadsheet', action: 'atk_request' },
    { label: 'Daftar Aset DIR', icon: 'Download', action: 'asset_dir' },
];

const chartOptions = {
    chart: {
        type: 'bar',
        height: 290,
        toolbar: { show: false },
    },
    colors: ['#d97706'],
    plotOptions: {
        bar: { borderRadius: 4, columnWidth: '45%' },
    },
    xaxis: {
        categories: ['FAT', 'HR', 'PROC', 'PROD', 'QA', 'QC', 'RND', 'SCM'],
    },
};

const chartSeries = [
    {
        name: 'Work Orders Selesai',
        data: [4, 6, 3, 14, 5, 4, 7, 9],
    },
];
</script>

<template>
    <ModuleLayout
        :title="module?.name"
        :module-slug="module?.slug || 'general-affair'"
        :module-name="module?.name || 'General Affair (FPUGA)'"
        :color-theme="module?.color_theme || 'amber'"
        :menus="module?.menus || []"
        :breadcrumbs="['Dashboard Fasilitas & GA']"
    >
        <Head :title="module?.name" />

        <div class="space-y-6">
            <!-- Header -->
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 pb-4 border-b border-slate-200 dark:border-slate-800">
                <div class="flex items-center gap-3">
                    <div class="w-12 h-12 rounded-xl bg-amber-50 dark:bg-amber-950/60 text-amber-600 dark:text-amber-400 flex items-center justify-center border border-amber-200 dark:border-amber-800 shrink-0">
                        <Building2 class="w-6 h-6" />
                    </div>
                    <div>
                        <div class="flex items-center gap-2">
                            <h1 class="text-xl font-bold text-slate-900 dark:text-white">{{ module?.name }}</h1>
                            <span class="text-xs px-2 py-0.5 rounded bg-amber-100 dark:bg-amber-950 text-amber-800 dark:text-amber-300 font-medium">Modul 2</span>
                        </div>
                        <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">{{ module?.description }}</p>
                    </div>
                </div>

                <QuickActionsBar :actions="quickActions" />
            </div>

            <!-- KPI Cards -->
            <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
                <KpiStatCard
                    title="Open Work Orders"
                    :value="kpis?.open_work_orders"
                    :trend="-15.4"
                    subtitle="SLA Rata-rata: 3.4 Jam"
                    status="amber"
                    :sparkline-data="[20, 18, 16, 15, 14, 13, 12]"
                />

                <KpiStatCard
                    title="Permintaan ATK Pending"
                    :value="kpis?.pending_atk_requests"
                    :trend="0"
                    subtitle="Siklus Revisi Otomatis"
                    status="amber"
                    :sparkline-data="[8, 7, 6, 6, 5, 5, 5]"
                />

                <KpiStatCard
                    title="Total Nilai Buku Aset"
                    :value="kpis?.total_asset_value"
                    :trend="2.1"
                    subtitle="Depresiasi Garis Lurus"
                    status="emerald"
                    :sparkline-data="[8.4, 8.5, 8.5, 8.6, 8.7, 8.7, 8.75]"
                />

                <KpiStatCard
                    title="Anomali Patroli QR"
                    :value="kpis?.security_anomalies"
                    :trend="0"
                    subtitle="100% Checkpoint Aman"
                    status="emerald"
                    :sparkline-data="[1, 0, 0, 0, 0, 0, 0]"
                />
            </div>

            <!-- Chart & Approval Inbox -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <div class="lg:col-span-2">
                    <InteractiveChartCard
                        title="Work Orders Diselesaikan per Departemen"
                        subtitle="Siklus 4-Form FPU (Request &rarr; Check &rarr; Material &rarr; Handover)"
                        type="bar"
                        :height="300"
                        :options="chartOptions"
                        :series="chartSeries"
                        selected-period="MTD"
                    />
                </div>

                <div>
                    <ApprovalInboxWidget
                        title="Antrean Approval GA"
                        :items="pendingApprovals"
                        module-slug="general-affair"
                    />
                </div>
            </div>

            <!-- Bottom Row -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- QR Security Widget -->
                <div class="bg-white dark:bg-slate-900 rounded-xl border border-slate-200 dark:border-slate-800 p-5 shadow-xs">
                    <div class="flex items-center gap-2 mb-3">
                        <QrCode class="w-4 h-4 text-amber-600 dark:text-amber-400" />
                        <h3 class="font-bold text-slate-900 dark:text-white text-sm">Security & Cleaning Patrol Status</h3>
                    </div>
                    <p class="text-xs text-slate-500 dark:text-slate-400 mb-4">
                        Pemindaian titik barcode/QR berbasis mobile dengan upload evident anomali foto real-time.
                    </p>
                    <div class="space-y-2 text-xs">
                        <div class="p-2.5 rounded-lg bg-amber-50/60 dark:bg-amber-950/40 border border-amber-100 dark:border-amber-800 flex items-center justify-between">
                            <span class="font-medium text-amber-950 dark:text-amber-200">Plant 1 Sukoharjo</span>
                            <span class="font-mono font-bold text-amber-800 dark:text-amber-300">12/12 Terverifikasi</span>
                        </div>
                        <div class="p-2.5 rounded-lg bg-amber-50/60 dark:bg-amber-950/40 border border-amber-100 dark:border-amber-800 flex items-center justify-between">
                            <span class="font-medium text-amber-950 dark:text-amber-200">Plant 2 Boyolali</span>
                            <span class="font-mono font-bold text-amber-800 dark:text-amber-300">8/8 Terverifikasi</span>
                        </div>
                    </div>
                </div>

                <div class="lg:col-span-2">
                    <ActivityFeedWidget
                        title="Audit Log Pemeliharaan Fasilitas & Patroli"
                        :activities="recentActivities"
                    />
                </div>
            </div>
        </div>
    </ModuleLayout>
</template>
