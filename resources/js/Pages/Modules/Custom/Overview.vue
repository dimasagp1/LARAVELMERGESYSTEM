<script setup>
import { computed } from 'vue';
import { Head, Link } from '@inertiajs/vue3';
import ModuleLayout from '@/Layouts/ModuleLayout.vue';
import KpiStatCard from '@/Components/Dashboard/KpiStatCard.vue';
import InteractiveChartCard from '@/Components/Dashboard/InteractiveChartCard.vue';
import ApprovalInboxWidget from '@/Components/Dashboard/ApprovalInboxWidget.vue';
import { useDeveloperModeStore } from '@/Stores/developerModeStore';
import {
    Boxes,
    Table,
    Plus,
    FileSpreadsheet,
    ArrowUpRight,
    FileCheck,
    Sliders,
    Sparkles,
    Eye,
    TrendingUp,
    Clock,
    Activity,
} from 'lucide-vue-next';

const props = defineProps({
    module: {
        type: Object,
        required: true,
    },
    entities: {
        type: Array,
        default: () => [],
    },
    widgets: {
        type: Array,
        default: () => [],
    },
});

const devStore = useDeveloperModeStore();

// Default trend data for apexchart
const chartSeries = [
    {
        name: 'Dokumen Dibuat',
        data: [12, 19, 15, 27, 34, 42, 38],
    },
    {
        name: 'Disetujui',
        data: [10, 16, 13, 24, 30, 40, 36],
    },
];

const chartOptions = {
    chart: {
        type: 'area',
        toolbar: { show: false },
        fontFamily: 'inherit',
    },
    colors: ['#10b981', '#06b6d4'],
    dataLabels: { enabled: false },
    stroke: { curve: 'smooth', width: 2 },
    xaxis: {
        categories: ['Sen', 'Sel', 'Rab', 'Kam', 'Jum', 'Sab', 'Min'],
    },
};
</script>

<template>
    <ModuleLayout
        :title="`${module.name} - Overview`"
        :module-slug="module.slug"
        :module-name="module.name"
        :color-theme="module.color_theme"
        :menus="module.menus"
        :breadcrumbs="['Overview']"
    >
        <Head :title="`${module.name} Overview`" />

        <div class="space-y-6">
            <!-- Welcome Module Banner -->
            <div class="relative overflow-hidden rounded-2xl bg-gradient-to-r from-slate-900 via-slate-800 to-slate-900 border border-slate-700/60 p-6 text-white shadow-md">
                <div class="relative z-10 flex flex-col md:flex-row md:items-center justify-between gap-4">
                    <div class="space-y-2">
                        <div class="flex items-center gap-2">
                            <span class="px-2.5 py-0.5 rounded-md text-xs font-mono font-bold bg-emerald-500/20 text-emerald-300 border border-emerald-500/30">
                                CUSTOM NO-CODE MODULE
                            </span>
                            <span class="text-xs text-slate-400">Prefix: /modules/{{ module.slug }}</span>
                        </div>
                        <h1 class="text-2xl font-bold tracking-tight text-white">{{ module.name }}</h1>
                        <p class="text-xs text-slate-300 max-w-2xl leading-relaxed">
                            {{ module.description || 'Modul terintegrasi HerbaTech Unified ERP dengan entitas transaksi dinamis.' }}
                        </p>
                    </div>

                    <div class="flex flex-wrap items-center gap-3">
                        <Link
                            v-if="devStore.isEnabled"
                            href="/developer-mode"
                            class="px-4 py-2 rounded-xl bg-slate-800 hover:bg-slate-700 text-emerald-400 border border-emerald-500/30 font-semibold text-xs flex items-center gap-1.5 transition"
                        >
                            <Sliders class="w-4 h-4" />
                            <span>Edit di No-Code Builder</span>
                        </Link>

                        <div v-if="entities.length > 0">
                            <Link
                                :href="`/modules/${module.slug}/entities/${entities[0].slug}/create`"
                                class="px-4 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-white font-semibold text-xs flex items-center gap-1.5 shadow-md shadow-emerald-600/20 transition"
                            >
                                <Plus class="w-4 h-4" />
                                <span>+ Input {{ entities[0].name }}</span>
                            </Link>
                        </div>
                    </div>
                </div>
            </div>

            <!-- KPI Metric Cards Grid -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                <KpiStatCard
                    title="Total Entitas Kustom"
                    :value="entities.length"
                    formatted-value=" Entitas"
                    subtext="Tabel tersimpan di database"
                    icon="Boxes"
                    color-theme="emerald"
                    trend-direction="up"
                    trend-percentage="100%"
                />
                <KpiStatCard
                    title="Workflows & Approvals"
                    :value="module.approval_workflows?.length || 1"
                    formatted-value=" Aktif"
                    subtext="Multi-stage approval pipeline"
                    icon="FileCheck"
                    color-theme="blue"
                    trend-direction="neutral"
                    trend-percentage="0%"
                />
                <KpiStatCard
                    title="SLA Compliance"
                    :value="98.5"
                    formatted-value="%"
                    subtext="Rata-rata persetujuan < 24 jam"
                    icon="Clock"
                    color-theme="amber"
                    trend-direction="up"
                    trend-percentage="+2.4%"
                />
                <KpiStatCard
                    title="Audit Log Integrity"
                    :value="100"
                    formatted-value="%"
                    subtext="Activitylog terenkripsi"
                    icon="Activity"
                    color-theme="violet"
                    trend-direction="up"
                    trend-percentage="OK"
                />
            </div>

            <!-- Entities Grid & Actions -->
            <div class="space-y-4">
                <div class="flex items-center justify-between">
                    <div>
                        <h2 class="text-sm font-bold text-slate-900 dark:text-white uppercase tracking-wider">
                            Entitas & Tabel Data Dinamis
                        </h2>
                        <p class="text-xs text-slate-500 dark:text-slate-400">
                            Kelola form input, approval dokumen, dan ekspor data PDF/Excel.
                        </p>
                    </div>

                    <Link
                        v-if="devStore.isEnabled"
                        href="/developer-mode"
                        class="text-xs text-emerald-600 dark:text-emerald-400 font-semibold hover:underline flex items-center gap-1"
                    >
                        <span>+ Tambah Entitas di Builder</span>
                    </Link>
                </div>

                <div v-if="entities.length === 0" class="rounded-2xl border border-dashed border-slate-300 dark:border-slate-800 p-8 text-center bg-white dark:bg-slate-900 text-slate-500">
                    <Table class="w-8 h-8 mx-auto text-slate-400 mb-2" />
                    <p class="text-xs font-semibold text-slate-700 dark:text-slate-300">Belum ada entitas data pada modul ini.</p>
                    <p class="text-xs text-slate-400 mt-1">Buka Developer Mode untuk menambahkan formulir dan tabel data kustom.</p>
                    <Link
                        href="/developer-mode"
                        class="inline-block mt-3 px-4 py-2 rounded-xl bg-emerald-600 text-white font-semibold text-xs shadow-sm hover:bg-emerald-500 transition"
                    >
                        Buka No-Code Builder
                    </Link>
                </div>

                <div v-else class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
                    <div
                        v-for="entity in entities"
                        :key="entity.id"
                        class="rounded-2xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 p-5 flex flex-col justify-between hover:shadow-md transition group"
                    >
                        <div class="space-y-3">
                            <div class="flex items-start justify-between">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 rounded-xl bg-emerald-50 dark:bg-emerald-950/60 text-emerald-600 dark:text-emerald-400 flex items-center justify-center font-bold">
                                        <Table class="w-5 h-5" />
                                    </div>
                                    <div>
                                        <h3 class="font-bold text-sm text-slate-900 dark:text-white group-hover:text-emerald-600 dark:group-hover:text-emerald-400 transition">
                                            {{ entity.name }}
                                        </h3>
                                        <span class="text-[11px] font-mono text-slate-400">/{{ entity.slug }}</span>
                                    </div>
                                </div>

                                <span v-if="entity.has_approval" class="text-[10px] font-semibold px-2 py-0.5 rounded bg-emerald-50 dark:bg-emerald-950 text-emerald-700 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800">
                                    Approval
                                </span>
                            </div>

                            <p class="text-xs text-slate-500 dark:text-slate-400 line-clamp-2">
                                {{ entity.description || 'Entitas transaksi kustom dengan field terkonfigurasi dinamis.' }}
                            </p>

                            <div class="text-xs font-mono text-slate-400 pt-2 border-t border-slate-100 dark:border-slate-800 flex items-center justify-between">
                                <span>{{ entity.fields?.length || 0 }} Field Terdaftar</span>
                                <span class="text-emerald-600 dark:text-emerald-400 font-semibold">Aktif</span>
                            </div>
                        </div>

                        <div class="pt-4 mt-4 border-t border-slate-100 dark:border-slate-800 flex items-center justify-between">
                            <Link
                                :href="`/modules/${module.slug}/entities/${entity.slug}`"
                                class="text-xs font-semibold text-slate-700 dark:text-slate-300 hover:text-emerald-600 dark:hover:text-emerald-400 flex items-center gap-1 transition"
                            >
                                <Eye class="w-3.5 h-3.5" />
                                <span>Lihat Data</span>
                            </Link>

                            <Link
                                :href="`/modules/${module.slug}/entities/${entity.slug}/create`"
                                class="px-3 py-1.5 rounded-lg bg-emerald-600 hover:bg-emerald-500 text-white font-semibold text-xs flex items-center gap-1 transition shadow-xs"
                            >
                                <Plus class="w-3.5 h-3.5" />
                                <span>+ Input</span>
                            </Link>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Charts & Approval Row -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- Activity & Trend Chart -->
                <div class="lg:col-span-2">
                    <InteractiveChartCard
                        title="Tren Aktivitas Dokumen & Transaksi"
                        subtitle="Volume dokumen diajukan vs disetujui dalam 7 hari terakhir"
                        chart-type="area"
                        :series="chartSeries"
                        :chart-options="chartOptions"
                        :height="280"
                    />
                </div>

                <!-- Approval Inbox Widget -->
                <div class="lg:col-span-1">
                    <ApprovalInboxWidget
                        title="Persetujuan Modul"
                        :module-slug="module.slug"
                        :pending-items="[]"
                    />
                </div>
            </div>
        </div>
    </ModuleLayout>
</template>
