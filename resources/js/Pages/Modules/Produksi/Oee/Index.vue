<script setup>
import { ref, computed } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import ModuleLayout from '@/Layouts/ModuleLayout.vue';
import InteractiveChartCard from '@/Components/Dashboard/InteractiveChartCard.vue';
import {
    Gauge,
    Timer,
    Zap,
    CheckCircle2,
    Filter,
    ArrowLeft,
    TrendingUp,
    AlertTriangle,
    Clock,
} from 'lucide-vue-next';

const props = defineProps({
    metrics: {
        type: Object,
        required: true,
    },
    pareto: {
        type: Array,
        default: () => [],
    },
    dailyTrends: {
        type: Array,
        default: () => [],
    },
    lines: {
        type: Array,
        default: () => [],
    },
    filters: {
        type: Object,
        default: () => ({}),
    },
});

const selectedLine = ref(props.filters.line_id || '');
const selectedMonth = ref(props.filters.month || new Date().toISOString().substring(0, 7));

const applyFilters = () => {
    router.get('/production/oee', {
        line_id: selectedLine.value,
        month: selectedMonth.value,
    }, { preserveState: true, preserveScroll: true });
};

// Trend Chart
const trendChartOptions = computed(() => ({
    chart: { type: 'line', toolbar: { show: false } },
    stroke: { curve: 'smooth', width: 2 },
    colors: ['#be123c', '#0284c7', '#10b981'],
    xaxis: {
        categories: (props.dailyTrends || []).map(t => t.tanggal),
    },
    yaxis: {
        min: 60,
        max: 100,
        labels: { formatter: (val) => `${val}%` },
    },
    legend: { position: 'top' },
}));

const trendChartSeries = computed(() => [
    { name: 'OEE Rata-rata', data: (props.dailyTrends || []).map(t => t.avg_oee || 85.0) },
    { name: 'Availability', data: (props.dailyTrends || []).map(t => t.avg_avail || 90.0) },
    { name: 'Performance', data: (props.dailyTrends || []).map(t => t.avg_perf || 94.0) },
]);

// Pareto Chart
const paretoChartOptions = computed(() => ({
    chart: { type: 'bar', toolbar: { show: false } },
    plotOptions: {
        bar: { horizontal: true, borderRadius: 4, barHeight: '60%' },
    },
    colors: ['#f43f5e'],
    xaxis: {
        categories: (props.pareto || []).map(p => p.alasan_nama || p.kategori),
        title: { text: 'Durasi Henti (Menit)' },
    },
}));

const paretoChartSeries = computed(() => [
    {
        name: 'Downtime (Menit)',
        data: (props.pareto || []).map(p => parseFloat(p.total_duration || 0)),
    },
]);
</script>

<template>
    <Head title="Monitoring OEE & Six Big Losses - Manufaktur" />

    <ModuleLayout module-slug="production">
        <div class="space-y-6">
            <!-- Header Bar -->
            <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h1 class="text-xl font-bold text-slate-900 dark:text-white sm:text-2xl">
                        Monitoring OEE & Six Big Losses
                    </h1>
                    <p class="text-xs text-slate-500">
                        Evaluasi efektivitas menyeluruh peralatan pabrik berdasarkan standar World Class Manufacturing (WCM ≥ 85%).
                    </p>
                </div>

                <!-- Filters -->
                <div class="flex flex-wrap items-center gap-2">
                    <select
                        v-model="selectedLine"
                        class="rounded-lg border border-slate-200 bg-white px-2.5 py-1.5 text-xs text-slate-900 dark:border-slate-800 dark:bg-slate-900 dark:text-white"
                        @change="applyFilters"
                    >
                        <option value="">Semua Lini Produksi</option>
                        <option v-for="l in lines" :key="l.id" :value="l.id">{{ l.name }}</option>
                    </select>

                    <input
                        v-model="selectedMonth"
                        type="month"
                        class="rounded-lg border border-slate-200 bg-white px-2.5 py-1.5 text-xs text-slate-900 dark:border-slate-800 dark:bg-slate-900 dark:text-white"
                        @change="applyFilters"
                    />

                    <button
                        type="button"
                        @click="applyFilters"
                        class="rounded-lg bg-rose-600 px-3 py-1.5 text-xs font-semibold text-white hover:bg-rose-700"
                    >
                        Terapkan
                    </button>
                </div>
            </div>

            <!-- KPI Cards -->
            <div class="grid grid-cols-2 gap-4 sm:grid-cols-4">
                <div class="rounded-xl border border-rose-200 bg-rose-50/50 p-4 shadow-sm dark:border-rose-900 dark:bg-rose-950/20">
                    <span class="text-xs font-bold uppercase text-rose-700 dark:text-rose-400">Overall OEE</span>
                    <div class="mt-1 text-2xl font-bold text-rose-600 dark:text-rose-400">{{ metrics.overall_oee }}%</div>
                    <span class="text-[11px] text-slate-500">Benchmark: ≥ 85.0%</span>
                </div>
                <div class="rounded-xl border border-slate-200 bg-white p-4 shadow-sm dark:border-slate-800 dark:bg-slate-900">
                    <span class="text-xs font-bold uppercase text-slate-500">Availability</span>
                    <div class="mt-1 text-2xl font-bold text-slate-900 dark:text-white">{{ metrics.availability }}%</div>
                    <span class="text-[11px] text-slate-400">Total Downtime: {{ metrics.total_downtime }}m</span>
                </div>
                <div class="rounded-xl border border-slate-200 bg-white p-4 shadow-sm dark:border-slate-800 dark:bg-slate-900">
                    <span class="text-xs font-bold uppercase text-slate-500">Performance</span>
                    <div class="mt-1 text-2xl font-bold text-slate-900 dark:text-white">{{ metrics.performance }}%</div>
                    <span class="text-[11px] text-slate-400">Efisiensi Siklus CT</span>
                </div>
                <div class="rounded-xl border border-slate-200 bg-white p-4 shadow-sm dark:border-slate-800 dark:bg-slate-900">
                    <span class="text-xs font-bold uppercase text-slate-500">Quality / Yield</span>
                    <div class="mt-1 text-2xl font-bold text-slate-900 dark:text-white">{{ metrics.yield }}%</div>
                    <span class="text-[11px] text-slate-400">Output: {{ metrics.total_output.toLocaleString('id-ID') }} pcs</span>
                </div>
            </div>

            <!-- Charts -->
            <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
                <InteractiveChartCard
                    title="Tren Harian OEE"
                    subtitle="Pergerakan OEE dan Availability sepanjang bulan"
                    :chart-options="trendChartOptions"
                    :series="trendChartSeries"
                    type="line"
                    height="280"
                />

                <InteractiveChartCard
                    title="Pareto Downtime Duration"
                    subtitle="Akumulasi menit jeda berdasarkan alasan henti"
                    :chart-options="paretoChartOptions"
                    :series="paretoChartSeries"
                    type="bar"
                    height="280"
                />
            </div>

            <!-- Six Big Losses Framework Breakdown -->
            <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-900">
                <h2 class="text-base font-semibold text-slate-900 dark:text-white">Analisis Six Big Losses Mesin</h2>
                <p class="text-xs text-slate-500">Pemetaan kerugian waktu & efisiensi ke dalam 3 pilar OEE</p>

                <div class="mt-4 grid grid-cols-1 gap-4 sm:grid-cols-3">
                    <div class="rounded-xl border border-slate-100 bg-slate-50 p-4 dark:border-slate-800 dark:bg-slate-800/40">
                        <span class="text-xs font-bold uppercase text-blue-700 dark:text-blue-400">1. Availability Losses</span>
                        <ul class="mt-2 space-y-1.5 text-xs text-slate-600 dark:text-slate-300">
                            <li>&bull; <strong>Equipment Failures:</strong> Kerusakan pompa chiller & motor.</li>
                            <li>&bull; <strong>Setup & Adjustments:</strong> Line clearance & ganti ukuran mold.</li>
                        </ul>
                    </div>
                    <div class="rounded-xl border border-slate-100 bg-slate-50 p-4 dark:border-slate-800 dark:bg-slate-800/40">
                        <span class="text-xs font-bold uppercase text-purple-700 dark:text-purple-400">2. Performance Losses</span>
                        <ul class="mt-2 space-y-1.5 text-xs text-slate-600 dark:text-slate-300">
                            <li>&bull; <strong>Idling & Minor Stops:</strong> Sensor tersumbat (< 5 menit).</li>
                            <li>&bull; <strong>Reduced Speed:</strong> Pengurangan rpm karena viskositas kental.</li>
                        </ul>
                    </div>
                    <div class="rounded-xl border border-slate-100 bg-slate-50 p-4 dark:border-slate-800 dark:bg-slate-800/40">
                        <span class="text-xs font-bold uppercase text-emerald-700 dark:text-emerald-400">3. Quality Losses</span>
                        <ul class="mt-2 space-y-1.5 text-xs text-slate-600 dark:text-slate-300">
                            <li>&bull; <strong>Process Defects:</strong> Segel bocor, volume kurang standar.</li>
                            <li>&bull; <strong>Startup Yield Loss:</strong> Botol awal saat pemanasan induksi.</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </ModuleLayout>
</template>
