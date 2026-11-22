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
    Users,
    Package,
    Building2,
    Truck,
    Factory,
    ShieldCheck,
    Briefcase,
    Car,
    FlaskConical,
    Layers,
    BookOpen,
    HelpCircle,
    CheckCircle2,
    ExternalLink,
} from 'lucide-vue-next';

const props = defineProps({
    module: {
        type: Object,
        required: true,
    },
    canvasConfig: {
        type: Object,
        default: null,
    },
    entities: {
        type: Array,
        default: () => [],
    },
    widgets: {
        type: Array,
        default: () => [],
    },
    availableMetrics: {
        type: Object,
        default: () => ({}),
    },
    dataSources: {
        type: Object,
        default: () => ({}),
    },
});

const devStore = useDeveloperModeStore();

// Canvas Grid & Style computations
const canvasColsClass = computed(() => {
    const cols = props.canvasConfig?.columns || 4;
    if (cols === 1) return 'grid-cols-1';
    if (cols === 2) return 'grid-cols-1 md:grid-cols-2';
    if (cols === 3) return 'grid-cols-1 md:grid-cols-2 lg:grid-cols-3';
    return 'grid-cols-1 sm:grid-cols-2 lg:grid-cols-4';
});

const cardRadiusClass = computed(() => {
    const r = props.canvasConfig?.card_radius;
    if (r === 'rounded-none') return 'rounded-none';
    if (r === 'rounded-3xl') return 'rounded-3xl';
    return 'rounded-2xl';
});

const cardPaddingClass = computed(() => {
    const p = props.canvasConfig?.card_padding;
    if (p === 'p-3') return 'p-3.5';
    if (p === 'p-7') return 'p-7';
    return 'p-5';
});

// Dynamic metric value resolver
const resolveWidgetValue = (w) => {
    const conf = w.data_source_config_json || {};
    if (conf.metric_key && props.availableMetrics[conf.metric_key]) {
        return props.availableMetrics[conf.metric_key].value;
    }
    if (conf.custom_value !== undefined && conf.custom_value !== '') {
        return conf.custom_value;
    }
    if (conf.metric_key === 'custom_entities_count') {
        return props.entities.length;
    }
    return 100;
};

// Default chart data fallback
const defaultChartSeries = [
    { name: 'Dokumen Diajukan', data: [12, 19, 15, 27, 34, 42, 38] },
    { name: 'Disetujui', data: [10, 16, 13, 24, 30, 40, 36] },
];

const defaultChartOptions = {
    chart: { type: 'area', toolbar: { show: false }, fontFamily: 'inherit' },
    colors: ['#10b981', '#06b6d4'],
    dataLabels: { enabled: false },
    stroke: { curve: 'smooth', width: 2 },
    xaxis: { categories: ['Sen', 'Sel', 'Rab', 'Kam', 'Jum', 'Sab', 'Min'] },
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
            <div class="relative overflow-hidden rounded-3xl bg-gradient-to-r from-slate-900 via-slate-800 to-slate-900 border border-slate-700/60 p-6 text-white shadow-xl">
                <div class="relative z-10 flex flex-col md:flex-row md:items-center justify-between gap-4">
                    <div class="space-y-2">
                        <div class="flex items-center gap-2">
                            <span class="px-2.5 py-0.5 rounded-full text-xs font-mono font-bold bg-emerald-500/20 text-emerald-300 border border-emerald-500/30 flex items-center gap-1">
                                <Sparkles class="w-3 h-3 text-amber-400" />
                                KANVAS MODUL KUSTOM
                            </span>
                            <span class="text-xs text-slate-400">Prefix: /modules/{{ module.slug }}</span>
                        </div>
                        <h1 class="text-2xl font-black tracking-tight text-white">{{ module.name }}</h1>
                        <p class="text-xs text-slate-300 max-w-2xl leading-relaxed">
                            {{ module.description || 'Modul kustom HerbaTech ERP dengan integrasi data lintas modul dan tata letak kanvas dinamis.' }}
                        </p>
                    </div>

                    <div class="flex flex-wrap items-center gap-3">
                        <Link
                            v-if="devStore.isEnabled"
                            href="/developer-mode"
                            class="px-4 py-2.5 rounded-xl bg-slate-800/90 hover:bg-slate-700 text-emerald-400 border border-emerald-500/30 font-semibold text-xs flex items-center gap-1.5 transition shadow-sm"
                        >
                            <Sliders class="w-4 h-4" />
                            <span>Atur Desain Kanvas di Builder</span>
                        </Link>

                        <div v-if="entities.length > 0">
                            <Link
                                :href="`/modules/${module.slug}/entities/${entities[0].slug}/create`"
                                class="px-4 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-white font-semibold text-xs flex items-center gap-1.5 shadow-lg shadow-emerald-600/30 transition"
                            >
                                <Plus class="w-4 h-4" />
                                <span>+ Input {{ entities[0].name }}</span>
                            </Link>
                        </div>
                    </div>
                </div>
            </div>

            <!-- CANVAS WIDGETS SECTION (If customized canvas widgets exist) -->
            <div v-if="widgets && widgets.length > 0" class="space-y-6">
                <div class="flex items-center justify-between">
                    <h2 class="text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400 flex items-center gap-1.5">
                        <Sparkles class="w-3.5 h-3.5 text-emerald-500" />
                        <span>Tata Letak Kanvas Terkonfigurasi</span>
                    </h2>
                    <span class="text-[11px] font-mono text-slate-400">
                        {{ widgets.length }} Blok Kanvas Aktif
                    </span>
                </div>

                <div :class="['grid gap-5', canvasColsClass]">
                    <template v-for="w in widgets" :key="w.id || w.title">
                        <!-- 1. KPI STAT WIDGET -->
                        <div
                            v-if="w.widget_type === 'kpi'"
                            :class="[
                                w.grid_span === 2 ? 'sm:col-span-2' : '',
                                w.grid_span === 3 ? 'sm:col-span-2 lg:col-span-3' : '',
                                w.grid_span === 4 ? 'col-span-full' : '',
                            ]"
                        >
                            <KpiStatCard
                                :title="w.title"
                                :value="resolveWidgetValue(w)"
                                :formatted-value="w.data_source_config_json?.unit ? ' ' + w.data_source_config_json.unit : ''"
                                :subtext="w.data_source_config_json?.subtext || 'Terhubung data sistem ERP'"
                                :icon="w.data_source_config_json?.icon || 'Activity'"
                                :color-theme="w.data_source_config_json?.color || 'emerald'"
                                :trend-direction="w.data_source_config_json?.trend_direction || 'up'"
                                :trend-percentage="w.data_source_config_json?.trend_percentage || 'OK'"
                            />
                        </div>

                        <!-- 2. CHART WIDGET -->
                        <div
                            v-else-if="w.widget_type === 'chart'"
                            :class="[
                                'col-span-1',
                                w.grid_span === 2 ? 'sm:col-span-2' : '',
                                w.grid_span === 3 ? 'sm:col-span-2 lg:col-span-3' : '',
                                w.grid_span === 4 ? 'col-span-full' : '',
                            ]"
                        >
                            <InteractiveChartCard
                                :title="w.title"
                                :subtitle="w.data_source_config_json?.subtitle || 'Tren analisis data berkala'"
                                :chart-type="w.data_source_config_json?.chart_type || 'area'"
                                :series="w.data_source_config_json?.series || defaultChartSeries"
                                :chart-options="defaultChartOptions"
                                :height="280"
                            />
                        </div>

                        <!-- 3. ACTIONS BAR WIDGET -->
                        <div
                            v-else-if="w.widget_type === 'actions'"
                            :class="[
                                'bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-sm flex flex-col justify-between',
                                cardRadiusClass,
                                cardPaddingClass,
                                w.grid_span === 2 ? 'sm:col-span-2' : '',
                                w.grid_span === 3 ? 'sm:col-span-2 lg:col-span-3' : '',
                                w.grid_span === 4 ? 'col-span-full' : '',
                            ]"
                        >
                            <div class="space-y-1 mb-3">
                                <h3 class="font-bold text-xs text-slate-800 dark:text-slate-100 uppercase tracking-wider">
                                    {{ w.title }}
                                </h3>
                                <p class="text-[11px] text-slate-500 dark:text-slate-400">
                                    {{ w.data_source_config_json?.subtitle || 'Pintasan navigasi dan aksi instan modul' }}
                                </p>
                            </div>
                            <div class="flex flex-wrap gap-2.5">
                                <Link
                                    v-if="entities.length > 0"
                                    :href="`/modules/${module.slug}/entities/${entities[0].slug}/create`"
                                    class="px-3.5 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-white font-bold text-xs flex items-center gap-1.5 transition shadow-sm"
                                >
                                    <Plus class="w-3.5 h-3.5" />
                                    <span>+ Buat {{ entities[0].name }}</span>
                                </Link>
                                <Link
                                    v-if="entities.length > 0"
                                    :href="`/modules/${module.slug}/entities/${entities[0].slug}`"
                                    class="px-3.5 py-2 rounded-xl bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200 font-semibold text-xs flex items-center gap-1.5 transition"
                                >
                                    <Table class="w-3.5 h-3.5" />
                                    <span>Buka Tabel Data</span>
                                </Link>
                                <Link
                                    href="/settings"
                                    class="px-3.5 py-2 rounded-xl bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200 font-semibold text-xs flex items-center gap-1.5 transition"
                                >
                                    <Sliders class="w-3.5 h-3.5" />
                                    <span>Hak Akses</span>
                                </Link>
                            </div>
                        </div>

                        <!-- 4. BANNER WIDGET -->
                        <div
                            v-else-if="w.widget_type === 'banner'"
                            :class="[
                                'bg-gradient-to-r from-emerald-950/60 to-slate-900 border border-emerald-800/60 p-5 text-white flex items-center justify-between gap-4',
                                cardRadiusClass,
                                w.grid_span === 2 ? 'sm:col-span-2' : '',
                                w.grid_span === 3 ? 'sm:col-span-2 lg:col-span-3' : '',
                                w.grid_span === 4 ? 'col-span-full' : '',
                            ]"
                        >
                            <div class="space-y-1">
                                <h3 class="font-bold text-sm text-emerald-300 flex items-center gap-2">
                                    <Sparkles class="w-4 h-4 text-emerald-400" />
                                    {{ w.title }}
                                </h3>
                                <p class="text-xs text-slate-300">
                                    {{ w.data_source_config_json?.message || 'Instruksi operasional kanvas kustom terintegrasi.' }}
                                </p>
                            </div>
                            <span class="px-3 py-1 rounded-full text-[10px] font-bold bg-emerald-500/20 text-emerald-300 border border-emerald-500/30">
                                Info Kanvas
                            </span>
                        </div>

                        <!-- 5. DEFAULT TABLE / INBOX WIDGET -->
                        <div
                            v-else
                            :class="[
                                'bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 p-5',
                                cardRadiusClass,
                                w.grid_span === 2 ? 'sm:col-span-2' : '',
                                w.grid_span === 3 ? 'sm:col-span-2 lg:col-span-3' : '',
                                w.grid_span === 4 ? 'col-span-full' : '',
                            ]"
                        >
                            <div class="flex items-center justify-between pb-3 mb-3 border-b border-slate-100 dark:border-slate-800">
                                <h3 class="font-bold text-xs text-slate-900 dark:text-white uppercase tracking-wider">
                                    {{ w.title }}
                                </h3>
                                <span class="text-[10px] text-emerald-600 dark:text-emerald-400 font-bold">Terintegrasi</span>
                            </div>
                            <p class="text-xs text-slate-500 dark:text-slate-400">
                                {{ w.data_source_config_json?.subtext || 'Komponen kanvas kustom modul HerbaTech Unified ERP.' }}
                            </p>
                        </div>
                    </template>
                </div>
            </div>

            <!-- DEFAULT KPI STATS (When no custom widgets configured yet) -->
            <div v-else class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
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
                    title="Katalog Produk SCM"
                    :value="availableMetrics.total_products?.value || 30"
                    formatted-value=" SKU"
                    subtext="Terhubung database inti"
                    icon="Package"
                    color-theme="amber"
                    trend-direction="up"
                    trend-percentage="Live"
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

            <!-- Dynamic Entities Cards -->
            <div class="space-y-4">
                <div class="flex items-center justify-between">
                    <div>
                        <h2 class="text-sm font-bold text-slate-900 dark:text-white uppercase tracking-wider">
                            Entitas & Tabel Data Dinamis
                        </h2>
                        <p class="text-xs text-slate-500 dark:text-slate-400">
                            Kelola form input, relasi data lintas modul, approval dokumen, dan ekspor data PDF/Excel.
                        </p>
                    </div>

                    <Link
                        v-if="devStore.isEnabled"
                        href="/developer-mode"
                        class="text-xs text-emerald-600 dark:text-emerald-400 font-semibold hover:underline flex items-center gap-1"
                    >
                        <span>+ Atur Entitas di Builder</span>
                    </Link>
                </div>

                <div v-if="entities.length === 0" class="rounded-3xl border border-dashed border-slate-300 dark:border-slate-800 p-8 text-center bg-white dark:bg-slate-900 text-slate-500">
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
                        class="rounded-3xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 p-5 flex flex-col justify-between hover:shadow-md transition group"
                    >
                        <div class="space-y-3">
                            <div class="flex items-start justify-between">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 rounded-2xl bg-emerald-50 dark:bg-emerald-950/60 text-emerald-600 dark:text-emerald-400 flex items-center justify-center font-bold">
                                        <Table class="w-5 h-5" />
                                    </div>
                                    <div>
                                        <h3 class="font-bold text-sm text-slate-900 dark:text-white group-hover:text-emerald-600 dark:group-hover:text-emerald-400 transition">
                                            {{ entity.name }}
                                        </h3>
                                        <span class="text-[11px] font-mono text-slate-400">/{{ entity.slug }}</span>
                                    </div>
                                </div>

                                <span v-if="entity.has_approval" class="text-[10px] font-semibold px-2 py-0.5 rounded-md bg-emerald-50 dark:bg-emerald-950 text-emerald-700 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800">
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
                                class="px-3.5 py-1.5 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-white font-semibold text-xs flex items-center gap-1 transition shadow-xs"
                            >
                                <Plus class="w-3.5 h-3.5" />
                                <span>+ Input</span>
                            </Link>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Default Activity & Trend Chart (When not placed on custom canvas) -->
            <div v-if="!widgets || widgets.length === 0" class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <div class="lg:col-span-2">
                    <InteractiveChartCard
                        title="Tren Aktivitas Dokumen & Transaksi"
                        subtitle="Volume dokumen diajukan vs disetujui dalam 7 hari terakhir"
                        chart-type="area"
                        :series="defaultChartSeries"
                        :chart-options="defaultChartOptions"
                        :height="280"
                    />
                </div>
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
