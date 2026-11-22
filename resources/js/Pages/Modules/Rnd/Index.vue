<script setup>
import { Head } from '@inertiajs/vue3';
import ModuleLayout from '@/Layouts/ModuleLayout.vue';
import KpiStatCard from '@/Components/Dashboard/KpiStatCard.vue';
import InteractiveChartCard from '@/Components/Dashboard/InteractiveChartCard.vue';
import ApprovalInboxWidget from '@/Components/Dashboard/ApprovalInboxWidget.vue';
import QuickActionsBar from '@/Components/Dashboard/QuickActionsBar.vue';
import ActivityFeedWidget from '@/Components/Dashboard/ActivityFeedWidget.vue';
import { FlaskConical, Calculator } from 'lucide-vue-next';

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
    { label: '+ Buat Proyek NPD Baru', icon: 'Plus', action: 'create_npd', primary: true },
    { label: 'Simulasi HPP Formulasi', icon: 'Calculator', action: 'calc_hpp' },
    { label: 'Download Dossier BPOM', icon: 'Download', action: 'dossier' },
];

const chartOptions = {
    chart: {
        type: 'bar',
        height: 290,
        toolbar: { show: false },
    },
    colors: ['#0891b2'],
    plotOptions: {
        bar: { horizontal: true, borderRadius: 4, barHeight: '55%' },
    },
    xaxis: {
        categories: [
            '1. PRF & Proposal',
            '2. Preformulasi',
            '3. Formulasi 100%',
            '4. Trial Lab & V1-V3',
            '5. Uji Sensori',
            '6. Stabilitas Dipercepat',
            '7. Dossier BPOM',
            '8. Master BOM Transfer',
        ],
    },
};

const chartSeries = [
    {
        name: 'Kelengkapan Proyek (%)',
        data: [100, 100, 95, 90, 85, 75, 60, 45],
    },
];
</script>

<template>
    <ModuleLayout
        :title="module?.name"
        :module-slug="module?.slug || 'rnd'"
        :module-name="module?.name || 'R&D (NPD Management)'"
        :color-theme="module?.color_theme || 'cyan'"
        :menus="module?.menus || []"
        :breadcrumbs="['Dashboard Riset & Formulasi']"
    >
        <Head :title="module?.name" />

        <div class="space-y-6">
            <!-- Header -->
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 pb-4 border-b border-slate-200 dark:border-slate-800">
                <div class="flex items-center gap-3">
                    <div class="w-12 h-12 rounded-xl bg-cyan-50 dark:bg-cyan-950/60 text-cyan-600 dark:text-cyan-400 flex items-center justify-center border border-cyan-200 dark:border-cyan-800 shrink-0">
                        <FlaskConical class="w-6 h-6" />
                    </div>
                    <div>
                        <div class="flex items-center gap-2">
                            <h1 class="text-xl font-bold text-slate-900 dark:text-white">{{ module?.name }}</h1>
                            <span class="text-xs px-2 py-0.5 rounded bg-cyan-100 dark:bg-cyan-950 text-cyan-800 dark:text-cyan-300 font-medium">Modul 7</span>
                        </div>
                        <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">{{ module?.description }}</p>
                    </div>
                </div>

                <QuickActionsBar :actions="quickActions" />
            </div>

            <!-- KPI Cards -->
            <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
                <KpiStatCard
                    title="Active NPD Projects"
                    :value="kpis?.active_npd_projects"
                    :trend="14.2"
                    subtitle="3-Fase Siklus Pengembangan"
                    status="cyan"
                    :sparkline-data="[5, 6, 6, 7, 7, 8, 8]"
                />

                <KpiStatCard
                    title="Pipeline Progress"
                    :value="`${kpis?.pipeline_progress_pct}%`"
                    :trend="5.8"
                    subtitle="Akselerasi Time-to-Market"
                    status="emerald"
                    :sparkline-data="[62, 65, 68, 70, 71, 73, 74.2]"
                />

                <KpiStatCard
                    title="Pending Formula Review"
                    :value="kpis?.pending_formula_approvals"
                    :trend="0"
                    subtitle="Validasi Presisi 100.00%"
                    status="amber"
                    :sparkline-data="[3, 3, 2, 2, 2, 2, 2]"
                />

                <KpiStatCard
                    title="NIE BPOM Terbit YTD"
                    :value="`${kpis?.nie_issued_ytd} NIE`"
                    :trend="50.0"
                    subtitle="Dossier e-Registrasi Valid"
                    status="emerald"
                    :sparkline-data="[2, 3, 3, 4, 5, 5, 6]"
                />
            </div>

            <!-- Chart & Approval Inbox -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <div class="lg:col-span-2">
                    <InteractiveChartCard
                        title="Status Kelengkapan Pipeline Tahapan NPD (1-8)"
                        subtitle="Dari Ide Produk & Formulasi hingga Master BOM Komersial"
                        type="bar"
                        :height="300"
                        :options="chartOptions"
                        :series="chartSeries"
                        selected-period="YTD"
                    />
                </div>

                <div>
                    <ApprovalInboxWidget
                        title="Approval Formulasi & Dossier"
                        :items="pendingApprovals"
                        module-slug="rnd"
                    />
                </div>
            </div>

            <!-- Bottom Row -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <div class="bg-white dark:bg-slate-900 rounded-xl border border-slate-200 dark:border-slate-800 p-5 shadow-xs">
                    <div class="flex items-center gap-2 mb-3">
                        <Calculator class="w-4 h-4 text-cyan-600 dark:text-cyan-400" />
                        <h3 class="font-bold text-slate-900 dark:text-white text-sm">Simulasi HPP & Uji Stabilitas</h3>
                    </div>
                    <p class="text-xs text-slate-500 dark:text-slate-400 mb-4">
                        Kalkulator instan HPP per unit berbasis harga pengadaan dan validasi bobot 100%.
                    </p>
                    <div class="space-y-2 text-xs">
                        <div class="p-2.5 rounded-lg bg-cyan-50/60 dark:bg-cyan-950/40 border border-cyan-100 dark:border-cyan-800 flex items-center justify-between">
                            <div>
                                <div class="font-semibold text-cyan-900 dark:text-cyan-200">Sirup Ekstrak Pegagan Plus</div>
                                <div class="text-slate-500 dark:text-slate-400 text-[11px]">HPP Proyeksi: Rp 14.850 / botol</div>
                            </div>
                            <span class="text-emerald-700 dark:text-emerald-400 font-bold font-mono">100.00%</span>
                        </div>
                        <div class="p-2.5 rounded-lg bg-cyan-50/60 dark:bg-cyan-950/40 border border-cyan-100 dark:border-cyan-800 flex items-center justify-between">
                            <div>
                                <div class="font-semibold text-cyan-900 dark:text-cyan-200">Uji Stabilitas Dipercepat (40°C)</div>
                                <div class="text-slate-500 dark:text-slate-400 text-[11px]">Bulan ke-3: Kadar Kurkuminoid Lulus</div>
                            </div>
                            <span class="text-blue-600 dark:text-blue-400 font-bold font-mono">PASS</span>
                        </div>
                    </div>
                </div>

                <div class="lg:col-span-2">
                    <ActivityFeedWidget
                        title="Audit Log Formulasi & Eksperimen R&D"
                        :activities="recentActivities"
                    />
                </div>
            </div>
        </div>
    </ModuleLayout>
</template>
