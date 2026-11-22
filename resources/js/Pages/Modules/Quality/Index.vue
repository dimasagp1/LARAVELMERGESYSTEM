<script setup>
import { Head } from '@inertiajs/vue3';
import ModuleLayout from '@/Layouts/ModuleLayout.vue';
import KpiStatCard from '@/Components/Dashboard/KpiStatCard.vue';
import InteractiveChartCard from '@/Components/Dashboard/InteractiveChartCard.vue';
import ApprovalInboxWidget from '@/Components/Dashboard/ApprovalInboxWidget.vue';
import QuickActionsBar from '@/Components/Dashboard/QuickActionsBar.vue';
import ActivityFeedWidget from '@/Components/Dashboard/ActivityFeedWidget.vue';
import { ShieldCheck, FileSignature } from 'lucide-vue-next';

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
    { label: '+ Laporkan Deviasi (DR)', icon: 'AlertTriangle', action: 'create_dr', primary: true },
    { label: 'Ajukan Change Request (CR)', icon: 'Plus', action: 'create_cr' },
    { label: 'Cetak Dokumen CPOB (A4)', icon: 'Download', action: 'print_cpob' },
];

const chartOptions = {
    chart: {
        type: 'bar',
        height: 290,
        stacked: true,
        toolbar: { show: false },
    },
    colors: ['#0d9488', '#f59e0b', '#ef4444'],
    plotOptions: {
        bar: { horizontal: false, borderRadius: 4, columnWidth: '50%' },
    },
    xaxis: {
        categories: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun'],
    },
};

const chartSeries = [
    { name: 'Closed CAPA', data: [12, 15, 18, 14, 20, 16] },
    { name: 'In Progress', data: [2, 3, 1, 4, 2, 2] },
    { name: 'Overdue', data: [0, 0, 0, 0, 0, 0] },
];
</script>

<template>
    <ModuleLayout
        :title="module?.name"
        :module-slug="module?.slug || 'quality'"
        :module-name="module?.name || 'Quality Management System (QMS)'"
        :color-theme="module?.color_theme || 'teal'"
        :menus="module?.menus || []"
        :breadcrumbs="['Dashboard Manajemen Mutu CPOB']"
    >
        <Head :title="module?.name" />

        <div class="space-y-6">
            <!-- Header -->
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 pb-4 border-b border-slate-200 dark:border-slate-800">
                <div class="flex items-center gap-3">
                    <div class="w-12 h-12 rounded-xl bg-teal-50 dark:bg-teal-950/60 text-teal-600 dark:text-teal-400 flex items-center justify-center border border-teal-200 dark:border-teal-800 shrink-0">
                        <ShieldCheck class="w-6 h-6" />
                    </div>
                    <div>
                        <div class="flex items-center gap-2">
                            <h1 class="text-xl font-bold text-slate-900 dark:text-white">{{ module?.name }}</h1>
                            <span class="text-xs px-2 py-0.5 rounded bg-teal-100 dark:bg-teal-950 text-teal-800 dark:text-teal-300 font-medium">Modul 6</span>
                        </div>
                        <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">{{ module?.description }}</p>
                    </div>
                </div>

                <QuickActionsBar :actions="quickActions" />
            </div>

            <!-- KPI Cards -->
            <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
                <KpiStatCard
                    title="Dokumen Mutu Aktif"
                    :value="kpis?.active_qms_docs"
                    :trend="4.2"
                    subtitle="Piramida CPOB 4-Tingkat"
                    status="teal"
                    :sparkline-data="[130, 132, 135, 138, 140, 141, 142]"
                />

                <KpiStatCard
                    title="Open Change Requests"
                    :value="kpis?.open_change_requests"
                    :trend="-25.0"
                    subtitle="1 CRA Major • 2 CRB Minor"
                    status="amber"
                    :sparkline-data="[6, 5, 5, 4, 4, 3, 3]"
                />

                <KpiStatCard
                    title="Open Deviation Reports"
                    :value="kpis?.open_deviations"
                    :trend="-33.3"
                    subtitle="Investigasi Fishbone 6M"
                    status="rose"
                    :sparkline-data="[5, 4, 4, 3, 3, 2, 2]"
                />

                <KpiStatCard
                    title="Overdue CAPA Actions"
                    :value="kpis?.overdue_capa"
                    :trend="0"
                    subtitle="100% Kepatuhan Tenggat"
                    status="emerald"
                    :sparkline-data="[0, 0, 0, 0, 0, 0, 0]"
                />
            </div>

            <!-- Chart & Approval Inbox -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <div class="lg:col-span-2">
                    <InteractiveChartCard
                        title="Status Penutupan Tindakan Perbaikan (CAPA)"
                        subtitle="Kepatuhan Target & Verifikasi Efektivitas QA (Bulanan)"
                        type="bar"
                        :height="300"
                        :options="chartOptions"
                        :series="chartSeries"
                        selected-period="YTD"
                    />
                </div>

                <div>
                    <ApprovalInboxWidget
                        title="Approval Dokumen & Rilis QA"
                        :items="pendingApprovals"
                        module-slug="quality"
                    />
                </div>
            </div>

            <!-- Bottom Row -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <div class="bg-white dark:bg-slate-900 rounded-xl border border-slate-200 dark:border-slate-800 p-5 shadow-xs">
                    <div class="flex items-center gap-2 mb-3">
                        <FileSignature class="w-4 h-4 text-teal-600 dark:text-teal-400" />
                        <h3 class="font-bold text-slate-900 dark:text-white text-sm">21 CFR Part 11 Digital Signatures</h3>
                    </div>
                    <p class="text-xs text-slate-500 dark:text-slate-400 mb-4">
                        Dual-Control Signature (Maker-Checker) dengan enkripsi snapshot & unalterable audit trail.
                    </p>
                    <div class="space-y-2 text-xs">
                        <div class="p-2.5 rounded-lg bg-teal-50/60 dark:bg-teal-950/40 border border-teal-100 dark:border-teal-800 flex items-center justify-between">
                            <div>
                                <div class="font-semibold text-teal-900 dark:text-teal-200">Release Bahan Baku #RM-091</div>
                                <div class="text-slate-500 dark:text-slate-400 text-[11px]">Signed: QA Supervisor & QA Head</div>
                            </div>
                            <span class="text-emerald-700 dark:text-emerald-400 font-bold font-mono">RELEASED</span>
                        </div>
                        <div class="p-2.5 rounded-lg bg-teal-50/60 dark:bg-teal-950/40 border border-teal-100 dark:border-teal-800 flex items-center justify-between">
                            <div>
                                <div class="font-semibold text-teal-900 dark:text-teal-200">Change Request CRA-2026-03</div>
                                <div class="text-slate-500 dark:text-slate-400 text-[11px]">FMEA RPN Score: 72 (Low Risk)</div>
                            </div>
                            <span class="text-blue-600 dark:text-blue-400 font-bold font-mono">APPROVED</span>
                        </div>
                    </div>
                </div>

                <div class="lg:col-span-2">
                    <ActivityFeedWidget
                        title="Audit Trail Kepatuhan CPOB & Mutu"
                        :activities="recentActivities"
                    />
                </div>
            </div>
        </div>
    </ModuleLayout>
</template>
