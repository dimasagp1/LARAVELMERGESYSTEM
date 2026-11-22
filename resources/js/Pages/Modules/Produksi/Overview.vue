<script setup>
import { ref, computed } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import ModuleLayout from '@/Layouts/ModuleLayout.vue';
import KpiStatCard from '@/Components/Dashboard/KpiStatCard.vue';
import InteractiveChartCard from '@/Components/Dashboard/InteractiveChartCard.vue';
import {
    Factory,
    Gauge,
    Calendar,
    Timer,
    Layers,
    AlertTriangle,
    Play,
    Pause,
    CheckCircle2,
    Clock,
    TrendingUp,
    ShieldCheck,
    Cpu,
    ArrowUpRight,
    Filter,
    Boxes,
    FileSpreadsheet,
    Zap,
} from 'lucide-vue-next';

const props = defineProps({
    module: {
        type: Object,
        default: () => ({
            name: 'Produksi & Manufaktur',
            slug: 'production',
            description: 'Perencanaan WPS, estafet batch tracking (Mixing-Filling-Packing), timer digital, OEE realtime, dan integrasi scrap limbah.',
            color_theme: 'rose',
        }),
    },
    metrics: {
        type: Object,
        required: true,
    },
    filters: {
        type: Object,
        default: () => ({
            date: '',
            shift: 'all',
        }),
    },
});

const selectedDate = ref(props.filters.date || new Date().toISOString().split('T')[0]);
const selectedShift = ref(props.filters.shift || 'all');

const applyFilters = () => {
    router.get('/production', {
        date: selectedDate.value,
        shift: selectedShift.value,
    }, { preserveState: true, preserveScroll: true });
};

// Charts Configurations
const oeeChartOptions = computed(() => ({
    chart: {
        type: 'area',
        toolbar: { show: false },
        zoom: { enabled: false },
    },
    stroke: { curve: 'smooth', width: 2 },
    colors: ['#be123c', '#0284c7', '#10b981'],
    fill: {
        type: 'gradient',
        gradient: {
            shadeIntensity: 1,
            opacityFrom: 0.45,
            opacityTo: 0.05,
            stops: [20, 100],
        },
    },
    xaxis: {
        categories: ['08:00', '10:00', '12:00', '14:00', '16:00', '18:00'],
    },
    yaxis: {
        min: 60,
        max: 100,
        labels: { formatter: (val) => `${val}%` },
    },
    dataLabels: { enabled: false },
    legend: { position: 'top' },
}));

const oeeChartSeries = computed(() => [
    { name: 'OEE Real-time', data: [82.5, 84.0, 86.2, 85.0, 87.5, props.metrics.kpis.overall_oee_pct] },
    { name: 'Availability', data: [88.0, 90.5, 92.0, 91.5, 93.0, props.metrics.kpis.line_availability_pct] },
    { name: 'Performance', data: [91.0, 93.0, 95.0, 94.0, 95.5, props.metrics.kpis.performance_rate_pct] },
]);

const paretoChartOptions = computed(() => ({
    chart: {
        type: 'bar',
        toolbar: { show: false },
    },
    plotOptions: {
        bar: {
            horizontal: true,
            borderRadius: 4,
            barHeight: '65%',
        },
    },
    colors: ['#f43f5e'],
    dataLabels: {
        enabled: true,
        formatter: (val) => `${val} m`,
    },
    xaxis: {
        categories: (props.metrics.paretoDowntime || []).map(p => p.alasan_nama || p.kategori),
        title: { text: 'Durasi Henti Mesin (Menit)' },
    },
}));

const paretoChartSeries = computed(() => [
    {
        name: 'Downtime (Menit)',
        data: (props.metrics.paretoDowntime || []).map(p => parseFloat(p.total_duration || 0)),
    },
]);

// Quick Actions
const quickActions = [
    { title: 'Jadwalkan WPS (PPIC)', desc: 'Buat rencana batch mingguan (Mixing/Filling/Packing)', href: '/production/schedules/create', icon: Calendar, color: 'bg-rose-50 text-rose-700 hover:border-rose-300' },
    { title: 'Konsol Timer LHP', desc: 'Buka stopwatch digital shop floor & log downtime', href: '/production/daily-reports/create', icon: Timer, color: 'bg-blue-50 text-blue-700 hover:border-blue-300' },
    { title: 'Monitoring OEE Detail', desc: 'Analisis Six Big Losses & perbandingan lini pabrik', href: '/production/oee', icon: Gauge, color: 'bg-purple-50 text-purple-700 hover:border-purple-300' },
    { title: 'Pencatatan Reject / Scrap', desc: 'Input produk cacat & sinkronisasi Odoo Scrap', href: '/production/scrap', icon: AlertTriangle, color: 'bg-amber-50 text-amber-700 hover:border-amber-300' },
    { title: 'Estafet Batch Tracking', desc: 'Pantau silsilah nomor batch mixing s/d packing', href: '/production/batches', icon: Layers, color: 'bg-emerald-50 text-emerald-700 hover:border-emerald-300' },
];

const handleApproveLhp = (reportId) => {
    if (confirm('Verifikasi dan kunci Laporan Harian ini secara resmi?')) {
        router.post(`/production/daily-reports/${reportId}/lock`, {}, {
            onSuccess: () => alert('Laporan Harian berhasil diverifikasi dan dikunci.'),
        });
    }
};
</script>

<template>
    <Head title="Overview Produksi & Manufaktur - ERP" />

    <ModuleLayout :module="module">
        <div class="space-y-6">
            <!-- Header Banner -->
            <div class="relative overflow-hidden rounded-2xl bg-gradient-to-r from-rose-900 via-rose-800 to-slate-900 p-6 text-white shadow-lg">
                <div class="relative z-10 flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
                    <div>
                        <div class="flex items-center gap-3">
                            <span class="inline-flex items-center rounded-lg bg-rose-500/20 px-3 py-1 text-xs font-semibold uppercase tracking-wider text-rose-200 backdrop-blur-sm">
                                <Factory class="mr-1.5 h-3.5 w-3.5" /> Shop Floor Execution
                            </span>
                            <span class="inline-flex items-center rounded-lg bg-emerald-500/20 px-2.5 py-0.5 text-xs font-medium text-emerald-300">
                                <ShieldCheck class="mr-1 h-3.5 w-3.5" /> CPOB Standard 2026
                            </span>
                        </div>
                        <h1 class="mt-2 text-2xl font-bold tracking-tight text-white md:text-3xl">
                            Monitoring Produksi & Manufaktur
                        </h1>
                        <p class="mt-1 text-sm text-rose-100/80">
                            Single Source of Truth pengendalian batch estafet, kalkulasi real-time OEE, dan kontrol jeda downtime.
                        </p>
                    </div>

                    <!-- Filter Bar -->
                    <div class="flex flex-wrap items-center gap-2 rounded-xl bg-white/10 p-2 backdrop-blur-md">
                        <div class="flex items-center gap-1.5 text-xs text-rose-100">
                            <Filter class="h-4 w-4" />
                            <span>Filter:</span>
                        </div>
                        <input
                            v-model="selectedDate"
                            type="date"
                            class="rounded-lg border-0 bg-white/20 px-2.5 py-1.5 text-xs text-white placeholder-rose-200 focus:ring-2 focus:ring-rose-400"
                            @change="applyFilters"
                        />
                        <select
                            v-model="selectedShift"
                            class="rounded-lg border-0 bg-white/20 px-2.5 py-1.5 text-xs text-white focus:ring-2 focus:ring-rose-400"
                            @change="applyFilters"
                        >
                            <option value="all" class="text-slate-900">Semua Shift</option>
                            <option value="shift1" class="text-slate-900">Shift 1 (Pagi)</option>
                            <option value="shift2" class="text-slate-900">Shift 2 (Siang)</option>
                            <option value="shift3" class="text-slate-900">Shift 3 (Malam)</option>
                        </select>
                    </div>
                </div>
            </div>

            <!-- 4 Primary KPI Stat Cards -->
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
                <KpiStatCard
                    title="Overall OEE"
                    :value="`${metrics.kpis.overall_oee_pct}%`"
                    subtitle="Target Sasaran Mutu: ≥ 85.0%"
                    :change="1.8"
                    change-type="increase"
                    color="rose"
                    icon="Gauge"
                />
                <KpiStatCard
                    title="Availability Rate"
                    :value="`${metrics.kpis.line_availability_pct}%`"
                    :subtitle="`Bersih: ${metrics.kpis.total_net_minutes}m / Gross: ${metrics.kpis.total_gross_minutes}m`"
                    :change="0.5"
                    change-type="increase"
                    color="blue"
                    icon="Timer"
                />
                <KpiStatCard
                    title="Performance Rate"
                    :value="`${metrics.kpis.performance_rate_pct}%`"
                    subtitle="Efisiensi Kecepatan vs CT Standar"
                    :change="1.2"
                    change-type="increase"
                    color="purple"
                    icon="Zap"
                />
                <KpiStatCard
                    title="Quality / Yield"
                    :value="`${metrics.kpis.batch_yield_pct}%`"
                    :subtitle="`Reject: ${metrics.kpis.total_reject_pcs} pcs (${metrics.kpis.total_output_pcs} lolos)`"
                    :change="-0.3"
                    change-type="decrease"
                    color="emerald"
                    icon="CheckCircle2"
                />
            </div>

            <!-- Secondary Metrics Strip -->
            <div class="grid grid-cols-2 gap-4 md:grid-cols-4">
                <div class="rounded-xl border border-slate-200 bg-white p-4 shadow-sm dark:border-slate-800 dark:bg-slate-900">
                    <div class="text-xs font-medium text-slate-500 dark:text-slate-400">Produktivitas Man Power</div>
                    <div class="mt-1 flex items-baseline gap-2">
                        <div class="text-xl font-bold text-slate-900 dark:text-white">{{ metrics.kpis.productivity_pct }}%</div>
                        <span class="text-xs text-emerald-600 dark:text-emerald-400">Standar 2.000 unit/MP</span>
                    </div>
                </div>
                <div class="rounded-xl border border-slate-200 bg-white p-4 shadow-sm dark:border-slate-800 dark:bg-slate-900">
                    <div class="text-xs font-medium text-slate-500 dark:text-slate-400">Total Output Selesai</div>
                    <div class="mt-1 flex items-baseline gap-2">
                        <div class="text-xl font-bold text-slate-900 dark:text-white">{{ Number(metrics.kpis.total_output_pcs).toLocaleString('id-ID') }}</div>
                        <span class="text-xs text-slate-500">Pcs Barang Jadi</span>
                    </div>
                </div>
                <div class="rounded-xl border border-slate-200 bg-white p-4 shadow-sm dark:border-slate-800 dark:bg-slate-900">
                    <div class="text-xs font-medium text-slate-500 dark:text-slate-400">Total Downtime (Jeda)</div>
                    <div class="mt-1 flex items-baseline gap-2">
                        <div class="text-xl font-bold text-rose-600 dark:text-rose-400">{{ metrics.kpis.total_downtime_minutes }} m</div>
                        <span class="text-xs text-slate-500">Tercatat di sistem</span>
                    </div>
                </div>
                <div class="rounded-xl border border-slate-200 bg-white p-4 shadow-sm dark:border-slate-800 dark:bg-slate-900">
                    <div class="text-xs font-medium text-slate-500 dark:text-slate-400">WPS Holding Packing</div>
                    <div class="mt-1 flex items-baseline gap-2">
                        <div class="text-xl font-bold text-amber-600 dark:text-amber-400">{{ metrics.wpsSummary.holding_packing }} Batch</div>
                        <span class="text-xs text-slate-500">Packing Hold Aktif</span>
                    </div>
                </div>
            </div>

            <!-- Line Performance Heat Grid -->
            <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-900">
                <div class="flex items-center justify-between pb-4">
                    <div>
                        <h2 class="text-base font-semibold text-slate-900 dark:text-white">Line Performance Heat Grid</h2>
                        <p class="text-xs text-slate-500 dark:text-slate-400">Pemetaan volume output fisik dan rata-rata OEE per tahapan proses</p>
                    </div>
                    <Link href="/production/schedules" class="text-xs font-medium text-rose-600 hover:text-rose-700 dark:text-rose-400">
                        Buka Jadwal WPS &rarr;
                    </Link>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm">
                        <thead class="border-b border-slate-200 bg-slate-50 text-xs font-semibold uppercase text-slate-600 dark:border-slate-800 dark:bg-slate-800/50 dark:text-slate-400">
                            <tr>
                                <th class="py-3 px-4">Lini Produksi</th>
                                <th class="py-3 px-4 text-right">Tahap 1: Mixing</th>
                                <th class="py-3 px-4 text-right">Tahap 2: Filling</th>
                                <th class="py-3 px-4 text-right">Tahap 3: Packing</th>
                                <th class="py-3 px-4 text-right">Total Akumulasi</th>
                                <th class="py-3 px-4 text-center">Rata-rata OEE</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                            <tr v-for="line in metrics.heatGrid" :key="line.line_id" class="hover:bg-slate-50 dark:hover:bg-slate-800/40">
                                <td class="py-3 px-4">
                                    <div class="font-medium text-slate-900 dark:text-white">{{ line.line_name }}</div>
                                    <div class="text-xs text-slate-500 font-mono">{{ line.line_number }}</div>
                                </td>
                                <td class="py-3 px-4 text-right font-medium text-slate-700 dark:text-slate-300">
                                    {{ line.mixing_output.toLocaleString('id-ID') }} pcs
                                </td>
                                <td class="py-3 px-4 text-right font-medium text-slate-700 dark:text-slate-300">
                                    {{ line.filling_output.toLocaleString('id-ID') }} pcs
                                </td>
                                <td class="py-3 px-4 text-right font-medium text-slate-700 dark:text-slate-300">
                                    {{ line.packing_output.toLocaleString('id-ID') }} pcs
                                </td>
                                <td class="py-3 px-4 text-right font-bold text-rose-600 dark:text-rose-400">
                                    {{ line.total_output.toLocaleString('id-ID') }} pcs
                                </td>
                                <td class="py-3 px-4 text-center">
                                    <span :class="[
                                        'inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-semibold',
                                        line.avg_oee >= 85 ? 'bg-emerald-100 text-emerald-800 dark:bg-emerald-950/40 dark:text-emerald-400' :
                                        line.avg_oee >= 75 ? 'bg-amber-100 text-amber-800 dark:bg-amber-950/40 dark:text-amber-400' :
                                        'bg-rose-100 text-rose-800 dark:bg-rose-950/40 dark:text-rose-400'
                                    ]">
                                        {{ line.avg_oee }}%
                                    </span>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Two Interactive Charts -->
            <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
                <InteractiveChartCard
                    title="Tren OEE & Efisiensi Real-time"
                    subtitle="Dinamika Availability, Performance vs Overall OEE"
                    :chart-options="oeeChartOptions"
                    :series="oeeChartSeries"
                    type="area"
                    height="280"
                />

                <InteractiveChartCard
                    title="Grafik Pareto Downtime (Penyebab Henti Mesin)"
                    subtitle="Peringkat akumulasi menit henti berdasarkan kategori"
                    :chart-options="paretoChartOptions"
                    :series="paretoChartSeries"
                    type="bar"
                    height="280"
                />
            </div>

            <!-- Batch Estafet Breakdown Table -->
            <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-900">
                <div class="flex items-center justify-between pb-4">
                    <div>
                        <h2 class="text-base font-semibold text-slate-900 dark:text-white">Batch Breakdown (Lantai Produksi)</h2>
                        <p class="text-xs text-slate-500 dark:text-slate-400">Pemeriksaan rinci status pengerjaan, timer, dan efisiensi OEE per batch</p>
                    </div>
                    <Link href="/production/daily-reports" class="text-xs font-medium text-rose-600 hover:text-rose-700 dark:text-rose-400">
                        Lihat Semua LHP &rarr;
                    </Link>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm">
                        <thead class="border-b border-slate-200 bg-slate-50 text-xs font-semibold uppercase text-slate-600 dark:border-slate-800 dark:bg-slate-800/50 dark:text-slate-400">
                            <tr>
                                <th class="py-3 px-4">No. Batch</th>
                                <th class="py-3 px-4">Produk</th>
                                <th class="py-3 px-4">Proses</th>
                                <th class="py-3 px-4">Lini</th>
                                <th class="py-3 px-4 text-right">Output</th>
                                <th class="py-3 px-4 text-right">OEE</th>
                                <th class="py-3 px-4 text-right">Yield</th>
                                <th class="py-3 px-4 text-center">Timer Status</th>
                                <th class="py-3 px-4 text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                            <tr v-for="b in metrics.batchBreakdown" :key="b.id" class="hover:bg-slate-50 dark:hover:bg-slate-800/40">
                                <td class="py-3 px-4 font-mono font-semibold text-rose-600 dark:text-rose-400">
                                    {{ b.batch_number }}
                                </td>
                                <td class="py-3 px-4 font-medium text-slate-900 dark:text-white">
                                    {{ b.product_name }}
                                </td>
                                <td class="py-3 px-4">
                                    <span class="inline-flex rounded-md bg-rose-50 px-2 py-0.5 text-xs font-semibold text-rose-700 dark:bg-rose-950/40 dark:text-rose-300">
                                        {{ b.proses }}
                                    </span>
                                </td>
                                <td class="py-3 px-4 text-slate-600 dark:text-slate-400">
                                    {{ b.line_name }}
                                </td>
                                <td class="py-3 px-4 text-right font-medium">
                                    {{ b.output_fisik.toLocaleString('id-ID') }}
                                </td>
                                <td class="py-3 px-4 text-right font-semibold" :class="b.oee_persen >= 85 ? 'text-emerald-600' : 'text-amber-600'">
                                    {{ b.oee_persen }}%
                                </td>
                                <td class="py-3 px-4 text-right font-semibold text-slate-700 dark:text-slate-300">
                                    {{ b.yield_persen }}%
                                </td>
                                <td class="py-3 px-4 text-center">
                                    <span :class="[
                                        'inline-flex items-center gap-1 rounded-full px-2.5 py-0.5 text-xs font-medium',
                                        b.timer_status === 'start' ? 'bg-emerald-100 text-emerald-700 animate-pulse' :
                                        b.timer_status === 'pause' ? 'bg-amber-100 text-amber-700' :
                                        b.timer_status === 'end' ? 'bg-blue-100 text-blue-700' :
                                        'bg-slate-100 text-slate-700'
                                    ]">
                                        <Play v-if="b.timer_status === 'start'" class="h-3 w-3 fill-current" />
                                        <Pause v-else-if="b.timer_status === 'pause'" class="h-3 w-3" />
                                        <Clock v-else class="h-3 w-3" />
                                        {{ b.timer_status.toUpperCase() }}
                                    </span>
                                </td>
                                <td class="py-3 px-4 text-center">
                                    <Link :href="`/production/daily-reports/${b.id}`" class="inline-flex items-center gap-1 rounded-lg bg-rose-50 px-2.5 py-1 text-xs font-semibold text-rose-700 hover:bg-rose-100 dark:bg-rose-950/40 dark:text-rose-300">
                                        Konsol Timer <ArrowUpRight class="h-3 w-3" />
                                    </Link>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Bottom Row: Supervisor Approval Inbox & Quick Actions -->
            <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
                <!-- Supervisor Lock Approval Inbox -->
                <div class="lg:col-span-2 rounded-xl border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-900">
                    <div class="flex items-center justify-between pb-3 border-b border-slate-100 dark:border-slate-800">
                        <div>
                            <h2 class="text-base font-semibold text-slate-900 dark:text-white">Antrean Verifikasi Supervisor (Lock LHP)</h2>
                            <p class="text-xs text-slate-500">Laporan harian yang sudah disubmit oleh operator dan menunggu verifikasi CPOB</p>
                        </div>
                        <span class="rounded-full bg-rose-100 px-2.5 py-0.5 text-xs font-bold text-rose-700 dark:bg-rose-950/40 dark:text-rose-400">
                            {{ metrics.pendingApprovals.length }} Antrean
                        </span>
                    </div>

                    <div class="mt-4 space-y-3">
                        <div
                            v-for="item in metrics.pendingApprovals"
                            :key="item.id"
                            class="flex flex-col gap-3 rounded-xl border border-slate-200/80 p-3.5 transition-all hover:border-rose-300 sm:flex-row sm:items-center sm:justify-between dark:border-slate-800"
                        >
                            <div class="space-y-1">
                                <div class="flex items-center gap-2">
                                    <span class="font-semibold text-slate-900 dark:text-white">{{ item.title }}</span>
                                    <span :class="[
                                        'rounded px-1.5 py-0.5 text-[10px] font-bold uppercase',
                                        item.urgency === 'high' ? 'bg-rose-100 text-rose-700' : 'bg-amber-100 text-amber-700'
                                    ]">
                                        {{ item.urgency }}
                                    </span>
                                </div>
                                <p class="text-xs text-slate-500">{{ item.description }}</p>
                                <div class="flex items-center gap-3 text-xs text-slate-400">
                                    <span>Diajukan: {{ item.requester }}</span>
                                    <span>&bull;</span>
                                    <span class="font-medium text-slate-700 dark:text-slate-300">{{ item.amount }}</span>
                                </div>
                            </div>

                            <div class="flex items-center gap-2">
                                <Link
                                    :href="`/production/daily-reports/${item.id}`"
                                    class="rounded-lg border border-slate-200 px-3 py-1.5 text-xs font-medium text-slate-700 hover:bg-slate-50 dark:border-slate-700 dark:text-slate-300"
                                >
                                    Review
                                </Link>
                                <button
                                    type="button"
                                    @click="handleApproveLhp(item.id)"
                                    class="rounded-lg bg-rose-600 px-3 py-1.5 text-xs font-medium text-white hover:bg-rose-700"
                                >
                                    Verifikasi & Lock
                                </button>
                            </div>
                        </div>

                        <div v-if="metrics.pendingApprovals.length === 0" class="py-8 text-center text-xs text-slate-400">
                            Tidak ada LHP yang menunggu verifikasi saat ini.
                        </div>
                    </div>
                </div>

                <!-- Quick Action Launcher -->
                <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-900">
                    <h2 class="text-base font-semibold text-slate-900 dark:text-white">Aksi Cepat Operasional</h2>
                    <p class="text-xs text-slate-500">Pintasan modul produksi harian</p>

                    <div class="mt-4 space-y-2.5">
                        <Link
                            v-for="action in quickActions"
                            :key="action.title"
                            :href="action.href"
                            :class="[
                                'flex items-start gap-3 rounded-xl border border-slate-200/80 p-3 transition-all hover:shadow-sm dark:border-slate-800',
                                action.color
                            ]"
                        >
                            <component :is="action.icon" class="h-5 w-5 shrink-0 mt-0.5" />
                            <div>
                                <div class="text-xs font-semibold">{{ action.title }}</div>
                                <div class="text-[11px] opacity-80">{{ action.desc }}</div>
                            </div>
                        </Link>
                    </div>
                </div>
            </div>
        </div>
    </ModuleLayout>
</template>
