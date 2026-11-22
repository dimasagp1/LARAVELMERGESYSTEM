<script setup>
import { ref, computed } from 'vue';
import { Head, Link } from '@inertiajs/vue3';
import ModuleLayout from '@/Layouts/ModuleLayout.vue';
import KpiStatCard from '@/Components/Dashboard/KpiStatCard.vue';
import InteractiveChartCard from '@/Components/Dashboard/InteractiveChartCard.vue';
import {
    FlaskConical,
    Calculator,
    Boxes,
    FileCheck,
    Layers,
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
    Wrench,
    Search,
} from 'lucide-vue-next';

const props = defineProps({
    module: Object,
    kpis: Object,
    pendingQueue: {
        type: Array,
        default: () => [],
    },
    productCards: {
        type: Array,
        default: () => [],
    },
    recentActivities: {
        type: Array,
        default: () => [],
    },
    charts: {
        type: Object,
        default: () => ({}),
    },
});

const activeTab = ref('products'); // 'products' | 'approvals' | 'charts' | 'activities'
const productSearch = ref('');

const filteredProducts = computed(() => {
    if (!productSearch.value) return props.productCards;
    const q = productSearch.value.toLowerCase();
    return props.productCards.filter((p) =>
        p.name.toLowerCase().includes(q) || p.sku.toLowerCase().includes(q)
    );
});

// Chart 1: Pipeline Stages
const pipelineChartOptions = {
    chart: { type: 'bar', toolbar: { show: false }, fontFamily: 'inherit' },
    colors: ['#0891b2'],
    plotOptions: {
        bar: { horizontal: true, borderRadius: 4, barHeight: '50%' },
    },
    dataLabels: { enabled: true, style: { fontSize: '11px', colors: ['#fff'] } },
    xaxis: {
        categories: props.charts?.pipeline_stages?.categories || [],
    },
};

const pipelineChartSeries = [
    {
        name: 'Jumlah Dokumen',
        data: props.charts?.pipeline_stages?.data || [],
    },
];

// Chart 2: Formula Types Donut
const donutOptions = {
    chart: { type: 'donut', fontFamily: 'inherit' },
    colors: ['#06b6d4', '#10b981', '#f59e0b'],
    labels: props.charts?.formula_types?.labels || ['Produk Baru', 'Revisi', 'Substitusi'],
    legend: { position: 'bottom' },
    dataLabels: { enabled: true },
};

const donutSeries = props.charts?.formula_types?.series || [5, 3, 2];
</script>

<template>
    <ModuleLayout
        :title="module?.name || 'R&D Management'"
        :module-slug="module?.slug || 'rnd'"
        :module-name="module?.name || 'R&D (NPD Management)'"
        :color-theme="module?.color_theme || 'cyan'"
        :menus="module?.menus || []"
        :breadcrumbs="['Overview']"
    >
        <Head title="R&D Management System - HerbaTech ERP" />

        <div class="space-y-6">
            <!-- Top Hero Banner with Quick Actions -->
            <div class="rounded-2xl bg-gradient-to-r from-slate-900 via-cyan-950 to-slate-900 border border-cyan-800/50 p-6 text-white shadow-md relative overflow-hidden">
                <div class="relative z-10 flex flex-col md:flex-row md:items-center justify-between gap-5">
                    <div class="space-y-2">
                        <div class="flex items-center gap-2">
                            <span class="px-2.5 py-0.5 rounded-md text-xs font-mono font-bold bg-cyan-500/20 text-cyan-300 border border-cyan-500/30">
                                SIKLUS HIDUP NPD & FORMULASI RESEP
                            </span>
                            <span class="text-xs text-slate-400">BPOM & cGMP Compliant</span>
                        </div>
                        <h1 class="text-2xl font-bold tracking-tight text-white flex items-center gap-3">
                            <span>R&D Management System</span>
                            <span class="text-xs font-normal text-cyan-400 font-mono">v2.4 Unified</span>
                        </h1>
                        <p class="text-xs text-slate-300 max-w-2xl leading-relaxed">
                            Pusat kendali riset produk herbal: formulasi presisi 100.00%, auto-clone reformulasi, uji kemasan 4 departemen, hingga registrasi izin edar BPOM.
                        </p>
                    </div>

                    <!-- Quick Action Buttons -->
                    <div class="flex flex-wrap items-center gap-2.5 self-start md:self-auto">
                        <Link
                            href="/rnd/formulas/create"
                            class="px-4 py-2 rounded-xl bg-cyan-600 hover:bg-cyan-500 text-white font-semibold text-xs flex items-center gap-2 shadow-lg shadow-cyan-600/30 transition"
                        >
                            <Calculator class="w-4 h-4" />
                            <span>+ Formulasi RM (100%)</span>
                        </Link>
                        <Link
                            href="/rnd/trial-rms/create"
                            class="px-3.5 py-2 rounded-xl bg-slate-800 hover:bg-slate-700 text-cyan-300 border border-cyan-500/30 font-semibold text-xs flex items-center gap-1.5 transition"
                        >
                            <FlaskConical class="w-4 h-4" />
                            <span>+ Trial RM (Lab)</span>
                        </Link>
                        <Link
                            href="/rnd/trial-pms/create"
                            class="px-3.5 py-2 rounded-xl bg-slate-800 hover:bg-slate-700 text-cyan-300 border border-cyan-500/30 font-semibold text-xs flex items-center gap-1.5 transition"
                        >
                            <Boxes class="w-4 h-4" />
                            <span>+ Trial PM (Mesin)</span>
                        </Link>
                        <Link
                            href="/rnd/approval-center"
                            class="px-3.5 py-2 rounded-xl bg-amber-500/20 hover:bg-amber-500/30 text-amber-300 border border-amber-500/40 font-semibold text-xs flex items-center gap-1.5 transition"
                        >
                            <FileCheck class="w-4 h-4" />
                            <span>Approval Center ({{ kpis?.pending_approval || 0 }})</span>
                        </Link>
                    </div>
                </div>
            </div>

            <!-- KPI Metric Cards Grid -->
            <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
                <KpiStatCard
                    title="Pipeline Completion"
                    :value="kpis?.pipeline_progress_pct"
                    formatted-value="%"
                    :subtext="`${kpis?.approved_completed || 0} dari ${kpis?.total_items || 0} item selesai`"
                    icon="TrendingUp"
                    color-theme="cyan"
                    trend-direction="up"
                    trend-percentage="+8.5%"
                />
                <KpiStatCard
                    title="Menunggu Persetujuan"
                    :value="kpis?.pending_approval"
                    formatted-value=" Dokumen"
                    subtext="Antrean aktif OM & GM"
                    icon="Clock"
                    color-theme="amber"
                    trend-direction="neutral"
                    trend-percentage="Perlu Tindakan"
                />
                <KpiStatCard
                    title="Active NPD Projects"
                    :value="kpis?.active_npd_projects"
                    formatted-value=" Proyek"
                    subtext="Dalam tahap lab & pengujian"
                    icon="Layers"
                    color-theme="blue"
                    trend-direction="up"
                    trend-percentage="On Track"
                />
                <KpiStatCard
                    title="Izin Edar Terbit (NIE)"
                    :value="kpis?.nie_issued_ytd"
                    formatted-value=" Produk"
                    subtext="Badan POM RI resmi"
                    icon="ShieldCheck"
                    color-theme="emerald"
                    trend-direction="up"
                    trend-percentage="100% Legal"
                />
            </div>

            <!-- Interactive Navigation Tabs -->
            <div class="border-b border-slate-200 dark:border-slate-800 flex items-center gap-2">
                <button
                    @click="activeTab = 'products'"
                    :class="[
                        'flex items-center gap-2 px-4 py-3 text-xs font-semibold border-b-2 transition',
                        activeTab === 'products'
                            ? 'border-cyan-600 text-cyan-600 dark:text-cyan-400'
                            : 'border-transparent text-slate-500 hover:text-slate-800 dark:hover:text-slate-200',
                    ]"
                >
                    <Boxes class="w-4 h-4" />
                    <span>Progres Produk (Central Product Hub)</span>
                    <span class="text-[10px] font-mono px-1.5 py-0.5 rounded bg-cyan-100 dark:bg-cyan-950 text-cyan-700 dark:text-cyan-300">
                        {{ productCards.length }}
                    </span>
                </button>

                <button
                    @click="activeTab = 'approvals'"
                    :class="[
                        'flex items-center gap-2 px-4 py-3 text-xs font-semibold border-b-2 transition',
                        activeTab === 'approvals'
                            ? 'border-cyan-600 text-cyan-600 dark:text-cyan-400'
                            : 'border-transparent text-slate-500 hover:text-slate-800 dark:hover:text-slate-200',
                    ]"
                >
                    <Clock class="w-4 h-4" />
                    <span>Antrean Approval Saya</span>
                    <span
                        v-if="pendingQueue.length > 0"
                        class="text-[10px] font-mono px-1.5 py-0.5 rounded bg-amber-100 dark:bg-amber-950 text-amber-700 dark:text-amber-300 font-bold"
                    >
                        {{ pendingQueue.length }}
                    </span>
                </button>

                <button
                    @click="activeTab = 'charts'"
                    :class="[
                        'flex items-center gap-2 px-4 py-3 text-xs font-semibold border-b-2 transition',
                        activeTab === 'charts'
                            ? 'border-cyan-600 text-cyan-600 dark:text-cyan-400'
                            : 'border-transparent text-slate-500 hover:text-slate-800 dark:hover:text-slate-200',
                    ]"
                >
                    <TrendingUp class="w-4 h-4" />
                    <span>Grafik Pipeline & Analisis</span>
                </button>

                <button
                    @click="activeTab = 'activities'"
                    :class="[
                        'flex items-center gap-2 px-4 py-3 text-xs font-semibold border-b-2 transition',
                        activeTab === 'activities'
                            ? 'border-cyan-600 text-cyan-600 dark:text-cyan-400'
                            : 'border-transparent text-slate-500 hover:text-slate-800 dark:hover:text-slate-200',
                    ]"
                >
                    <Activity class="w-4 h-4" />
                    <span>Audit Feed Log</span>
                </button>
            </div>

            <!-- TAB 1: CENTRAL PRODUCT HUB (Product Progress Cards) -->
            <div v-if="activeTab === 'products'" class="space-y-4">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                    <div>
                        <h2 class="text-sm font-bold text-slate-900 dark:text-white uppercase tracking-wider">
                            Pusat Monitoring Siklus Hidup Produk (Central Hub)
                        </h2>
                        <p class="text-xs text-slate-500 dark:text-slate-400">
                            Setiap produk jadi terhubung secara utuh melintasi seluruh 8 tahapan pengujian R&D.
                        </p>
                    </div>

                    <div class="relative max-w-xs w-full">
                        <Search class="w-3.5 h-3.5 absolute left-3 top-1/2 -translate-y-1/2 text-slate-400" />
                        <input
                            v-model="productSearch"
                            type="text"
                            placeholder="Cari nama produk atau SKU..."
                            class="w-full pl-9 pr-3 py-1.5 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl text-xs placeholder-slate-400 focus:ring-cyan-500 focus:border-cyan-500"
                        />
                    </div>
                </div>

                <!-- Product Cards Grid -->
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
                    <div
                        v-for="p in filteredProducts"
                        :key="p.id"
                        class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-5 flex flex-col justify-between hover:shadow-md transition space-y-4"
                    >
                        <div class="space-y-3">
                            <div class="flex items-start justify-between">
                                <div>
                                    <span class="text-[10px] font-mono font-bold text-slate-400 uppercase tracking-wider">
                                        {{ p.sku }} &bull; {{ p.category || 'Herbal' }}
                                    </span>
                                    <h3 class="font-bold text-sm text-slate-900 dark:text-white mt-0.5 line-clamp-1">
                                        {{ p.name }}
                                    </h3>
                                </div>
                                <span class="px-2 py-0.5 rounded text-[11px] font-bold bg-cyan-50 dark:bg-cyan-950 text-cyan-700 dark:text-cyan-300 border border-cyan-200 dark:border-cyan-800">
                                    {{ p.progress_pct }}%
                                </span>
                            </div>

                            <!-- Progress Bar -->
                            <div class="w-full bg-slate-100 dark:bg-slate-800 rounded-full h-1.5 overflow-hidden">
                                <div
                                    class="bg-gradient-to-r from-cyan-500 to-emerald-500 h-1.5 rounded-full transition-all duration-300"
                                    :style="{ width: `${p.progress_pct}%` }"
                                />
                            </div>

                            <!-- 8-Stage Pill Grid -->
                            <div class="grid grid-cols-4 gap-1.5 pt-1 text-[10px] font-mono">
                                <!-- PRF -->
                                <div
                                    :class="[
                                        'p-1.5 rounded border text-center flex flex-col items-center justify-center',
                                        p.stages.prf
                                            ? 'bg-emerald-50 dark:bg-emerald-950/40 text-emerald-700 dark:text-emerald-300 border-emerald-200 dark:border-emerald-800'
                                            : 'bg-slate-50 dark:bg-slate-800 text-slate-400 border-slate-200 dark:border-slate-700',
                                    ]"
                                    title="Product Request Form"
                                >
                                    <span class="font-bold">PRF</span>
                                    <span class="text-[9px]">{{ p.stages.prf ? 'Done' : '-' }}</span>
                                </div>

                                <!-- NPD Proposal -->
                                <div
                                    :class="[
                                        'p-1.5 rounded border text-center flex flex-col items-center justify-center',
                                        p.stages.npd
                                            ? 'bg-emerald-50 dark:bg-emerald-950/40 text-emerald-700 dark:text-emerald-300 border-emerald-200 dark:border-emerald-800'
                                            : 'bg-slate-50 dark:bg-slate-800 text-slate-400 border-slate-200 dark:border-slate-700',
                                    ]"
                                    title="NPD Proposal"
                                >
                                    <span class="font-bold">NPD</span>
                                    <span class="text-[9px]">{{ p.stages.npd ? 'Done' : '-' }}</span>
                                </div>

                                <!-- Formula RM -->
                                <div
                                    :class="[
                                        'p-1.5 rounded border text-center flex flex-col items-center justify-center',
                                        p.stages.formula === 'approved'
                                            ? 'bg-emerald-50 dark:bg-emerald-950/40 text-emerald-700 dark:text-emerald-300 border-emerald-200 dark:border-emerald-800'
                                            : p.stages.formula.startsWith('pending')
                                            ? 'bg-amber-50 dark:bg-amber-950/40 text-amber-700 dark:text-amber-300 border-amber-200 dark:border-amber-800'
                                            : 'bg-slate-50 dark:bg-slate-800 text-slate-400 border-slate-200 dark:border-slate-700',
                                    ]"
                                    title="Formulasi Raw Material (100% Presisi)"
                                >
                                    <span class="font-bold">FRM</span>
                                    <span class="text-[9px]">V{{ p.stages.formula_version }}</span>
                                </div>

                                <!-- Trial RM -->
                                <div
                                    :class="[
                                        'p-1.5 rounded border text-center flex flex-col items-center justify-center',
                                        p.stages.trial_rm === 'lulus'
                                            ? 'bg-emerald-50 dark:bg-emerald-950/40 text-emerald-700 dark:text-emerald-300 border-emerald-200 dark:border-emerald-800'
                                            : p.stages.trial_rm === 'reformulasi'
                                            ? 'bg-rose-50 dark:bg-rose-950/40 text-rose-700 dark:text-rose-300 border-rose-200 dark:border-rose-800'
                                            : 'bg-slate-50 dark:bg-slate-800 text-slate-400 border-slate-200 dark:border-slate-700',
                                    ]"
                                    title="Trial Lab Raw Material"
                                >
                                    <span class="font-bold">TRM</span>
                                    <span class="text-[9px]">{{ p.stages.trial_rm }}</span>
                                </div>

                                <!-- Trial PM -->
                                <div
                                    :class="[
                                        'p-1.5 rounded border text-center flex flex-col items-center justify-center',
                                        p.stages.trial_pm === 'approved'
                                            ? 'bg-emerald-50 dark:bg-emerald-950/40 text-emerald-700 dark:text-emerald-300 border-emerald-200 dark:border-emerald-800'
                                            : 'bg-slate-50 dark:bg-slate-800 text-slate-400 border-slate-200 dark:border-slate-700',
                                    ]"
                                    title="Trial Packaging Material"
                                >
                                    <span class="font-bold">TPM</span>
                                    <span class="text-[9px]">{{ p.stages.trial_pm }}</span>
                                </div>

                                <!-- Sensory Score -->
                                <div
                                    :class="[
                                        'p-1.5 rounded border text-center flex flex-col items-center justify-center',
                                        p.stages.sensory_score
                                            ? 'bg-emerald-50 dark:bg-emerald-950/40 text-emerald-700 dark:text-emerald-300 border-emerald-200 dark:border-emerald-800'
                                            : 'bg-slate-50 dark:bg-slate-800 text-slate-400 border-slate-200 dark:border-slate-700',
                                    ]"
                                    title="Uji Sensori Hedonik"
                                >
                                    <span class="font-bold">SNS</span>
                                    <span class="text-[9px]">{{ p.stages.sensory_score ? `${p.stages.sensory_score}/5` : '-' }}</span>
                                </div>

                                <!-- Stability -->
                                <div
                                    :class="[
                                        'p-1.5 rounded border text-center flex flex-col items-center justify-center',
                                        p.stages.stability === 'ongoing' || p.stages.stability === 'compliant'
                                            ? 'bg-emerald-50 dark:bg-emerald-950/40 text-emerald-700 dark:text-emerald-300 border-emerald-200 dark:border-emerald-800'
                                            : 'bg-slate-50 dark:bg-slate-800 text-slate-400 border-slate-200 dark:border-slate-700',
                                    ]"
                                    title="Uji Stabilitas"
                                >
                                    <span class="font-bold">STB</span>
                                    <span class="text-[9px]">{{ p.stages.stability }}</span>
                                </div>

                                <!-- NIE BPOM -->
                                <div
                                    :class="[
                                        'p-1.5 rounded border text-center flex flex-col items-center justify-center',
                                        p.stages.nie
                                            ? 'bg-cyan-50 dark:bg-cyan-950/40 text-cyan-700 dark:text-cyan-300 border-cyan-200 dark:border-cyan-800 font-bold'
                                            : 'bg-slate-50 dark:bg-slate-800 text-slate-400 border-slate-200 dark:border-slate-700',
                                    ]"
                                    title="Nomor Izin Edar BPOM"
                                >
                                    <span class="font-bold">NIE</span>
                                    <span class="text-[9px]">{{ p.stages.nie ? 'BPOM' : '-' }}</span>
                                </div>
                            </div>
                        </div>

                        <!-- Card Footer Links -->
                        <div class="pt-3 border-t border-slate-100 dark:border-slate-800 flex items-center justify-between text-xs">
                            <span class="text-slate-500 font-mono">
                                Hrg Jual: Rp {{ Number(p.selling_price).toLocaleString('id-ID') }}
                            </span>
                            <Link
                                href="/rnd/formulas/create"
                                class="text-cyan-600 dark:text-cyan-400 font-semibold hover:underline flex items-center gap-1"
                            >
                                <span>Buat Formula</span>
                                <ChevronRight class="w-3.5 h-3.5" />
                            </Link>
                        </div>
                    </div>
                </div>
            </div>

            <!-- TAB 2: APPROVAL QUEUE -->
            <div v-if="activeTab === 'approvals'" class="space-y-4">
                <div class="flex items-center justify-between">
                    <div>
                        <h2 class="text-sm font-bold text-slate-900 dark:text-white uppercase tracking-wider">
                            Antrean Persetujuan Berjenjang (Gatekeeping)
                        </h2>
                        <p class="text-xs text-slate-500 dark:text-slate-400">
                            Telaah Tahap 1 oleh Operational Manager & Persetujuan Final Tahap 2 oleh General Manager.
                        </p>
                    </div>

                    <Link
                        href="/rnd/approval-center"
                        class="px-3.5 py-1.5 rounded-xl bg-cyan-600 hover:bg-cyan-500 text-white font-semibold text-xs transition"
                    >
                        Buka Approval Center Lengkap &rarr;
                    </Link>
                </div>

                <div v-if="pendingQueue.length === 0" class="rounded-2xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 p-8 text-center text-slate-400 text-xs">
                    <CheckCircle2 class="w-8 h-8 mx-auto text-emerald-500 mb-2" />
                    <p class="font-bold text-slate-700 dark:text-slate-200">Semua dokumen telah diproses!</p>
                    <p class="mt-1">Tidak ada dokumen formulasi atau trial yang sedang menunggu persetujuan Anda.</p>
                </div>

                <div v-else class="space-y-3">
                    <div
                        v-for="item in pendingQueue"
                        :key="item.id"
                        class="rounded-xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 p-4 flex flex-col md:flex-row md:items-center justify-between gap-4 hover:border-cyan-500/50 transition"
                    >
                        <div class="space-y-1">
                            <div class="flex items-center gap-2">
                                <span class="text-[10px] font-mono font-bold px-2 py-0.5 rounded bg-cyan-50 dark:bg-cyan-950 text-cyan-700 dark:text-cyan-300 border border-cyan-200 dark:border-cyan-800">
                                    {{ item.type }}
                                </span>
                                <span class="text-xs font-mono font-semibold text-slate-500">{{ item.code }}</span>
                                <span class="text-[11px] text-amber-600 dark:text-amber-400 font-semibold font-mono">
                                    &bull; Menunggu {{ item.stage }}
                                </span>
                            </div>
                            <h4 class="font-bold text-sm text-slate-900 dark:text-white">{{ item.title }}</h4>
                            <p class="text-xs text-slate-500 dark:text-slate-400">{{ item.description }}</p>
                        </div>

                        <div class="flex items-center gap-2">
                            <Link
                                :href="item.type.includes('Formula') ? `/rnd/formulas/${item.id}` : `/rnd/trial-pms/${item.id}`"
                                class="px-3.5 py-1.5 rounded-xl bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200 text-xs font-semibold transition"
                            >
                                Periksa Detail
                            </Link>
                            <Link
                                href="/rnd/approval-center"
                                class="px-3.5 py-1.5 rounded-xl bg-cyan-600 hover:bg-cyan-500 text-white text-xs font-semibold shadow-sm transition"
                            >
                                Proses Approval
                            </Link>
                        </div>
                    </div>
                </div>
            </div>

            <!-- TAB 3: CHARTS & ANALYTICS -->
            <div v-if="activeTab === 'charts'" class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <div class="lg:col-span-2">
                    <InteractiveChartCard
                        title="Status Siklus Hidup Dokumen R&D (8 Tahap)"
                        subtitle="Akumulasi volume dokumen di setiap tahapan pipeline NPD"
                        chart-type="bar"
                        :series="pipelineChartSeries"
                        :chart-options="pipelineChartOptions"
                        :height="320"
                    />
                </div>

                <div class="lg:col-span-1">
                    <div class="rounded-2xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 p-5 space-y-4">
                        <div>
                            <h3 class="font-bold text-sm text-slate-900 dark:text-white">Komposisi Tipe Formulasi</h3>
                            <p class="text-xs text-slate-500 dark:text-slate-400">Distribusi riset resep baru vs substitusi</p>
                        </div>
                        <apexchart
                            type="donut"
                            height="280"
                            :options="donutOptions"
                            :series="donutSeries"
                        />
                    </div>
                </div>
            </div>

            <!-- TAB 4: AUDIT FEED -->
            <div v-if="activeTab === 'activities'" class="space-y-4">
                <div class="rounded-2xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 p-5 space-y-4">
                    <div class="flex items-center justify-between border-b border-slate-100 dark:border-slate-800 pb-3">
                        <h3 class="font-bold text-sm text-slate-900 dark:text-white">Jejak Audit Real-Time (Spatie Activitylog)</h3>
                        <span class="text-xs font-mono text-cyan-600 dark:text-cyan-400">Audit Trail Terenkripsi</span>
                    </div>

                    <div class="divide-y divide-slate-100 dark:divide-slate-800">
                        <div
                            v-for="act in recentActivities"
                            :key="act.id"
                            class="py-3 flex items-start justify-between text-xs"
                        >
                            <div class="space-y-0.5">
                                <div class="flex items-center gap-2">
                                    <span class="font-bold text-slate-900 dark:text-white">{{ act.user }}</span>
                                    <span class="text-[10px] font-mono px-1.5 py-0.2 rounded bg-slate-100 dark:bg-slate-800 text-slate-500">
                                        {{ act.module }}
                                    </span>
                                </div>
                                <p class="text-slate-600 dark:text-slate-300">{{ act.action }}</p>
                            </div>
                            <span class="text-[11px] font-mono text-slate-400 shrink-0">{{ act.time }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </ModuleLayout>
</template>
