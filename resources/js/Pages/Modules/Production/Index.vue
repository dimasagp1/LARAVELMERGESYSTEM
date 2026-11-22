<script setup>
import { Head, Link } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import {
    Factory,
    Gauge,
    Timer,
    Activity,
    AlertTriangle,
    CheckCircle,
    ArrowRight,
} from 'lucide-vue-next';

const props = defineProps({
    module: Object,
    kpis: Object,
});

const chartOptions = {
    chart: {
        type: 'radialBar',
        height: 280,
        fontFamily: 'inherit',
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
    tooltip: { theme: 'light' },
};

const chartSeries = [91.2, 94.0, 98.4];
</script>

<template>
    <AppLayout>
        <Head :title="module?.name" />

        <div class="space-y-6">
            <!-- Header -->
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 pb-4 border-b border-slate-200">
                <div class="flex items-center gap-3">
                    <div class="w-12 h-12 rounded-xl bg-rose-50 text-rose-600 flex items-center justify-center border border-rose-200">
                        <Factory class="w-6 h-6" />
                    </div>
                    <div>
                        <div class="flex items-center gap-2">
                            <h1 class="text-xl font-bold text-slate-900">{{ module?.name }}</h1>
                            <span class="text-xs px-2 py-0.5 rounded bg-rose-100 text-rose-800 font-medium">Modul 5</span>
                        </div>
                        <p class="text-xs text-slate-500 mt-0.5">{{ module?.description }}</p>
                    </div>
                </div>

                <div class="flex items-center gap-2 text-xs">
                    <button class="px-3 py-1.5 rounded-lg bg-rose-600 text-white font-medium hover:bg-rose-700 transition">
                        + Buka Batch Baru (WPS)
                    </button>
                    <button class="px-3 py-1.5 rounded-lg border border-slate-300 text-slate-700 font-medium hover:bg-slate-50 transition">
                        Live Line Heat Grid
                    </button>
                </div>
            </div>

            <!-- KPI Cards -->
            <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
                <div class="bg-white p-4 rounded-xl border border-slate-200 shadow-xs">
                    <div class="text-xs text-slate-500 font-medium">Overall OEE %</div>
                    <div class="text-xl font-bold text-slate-900 mt-1">{{ kpis?.overall_oee_pct }}%</div>
                    <div class="text-[11px] text-rose-600 font-medium mt-1">Standar World-Class: &gt; 85%</div>
                </div>

                <div class="bg-white p-4 rounded-xl border border-slate-200 shadow-xs">
                    <div class="text-xs text-slate-500 font-medium">Line Availability %</div>
                    <div class="text-xl font-bold text-slate-900 mt-1">{{ kpis?.line_availability_pct }}%</div>
                    <div class="text-[11px] text-emerald-600 font-medium mt-1">Unplanned Downtime Rendah</div>
                </div>

                <div class="bg-white p-4 rounded-xl border border-slate-200 shadow-xs">
                    <div class="text-xs text-slate-500 font-medium">Performance Rate %</div>
                    <div class="text-xl font-bold text-slate-900 mt-1">{{ kpis?.performance_rate_pct }}%</div>
                    <div class="text-[11px] text-blue-600 font-medium mt-1">Kecepatan Mesin Stabil</div>
                </div>

                <div class="bg-white p-4 rounded-xl border border-slate-200 shadow-xs">
                    <div class="text-xs text-slate-500 font-medium">Batch Yield Realization</div>
                    <div class="text-xl font-bold text-slate-900 mt-1">{{ kpis?.batch_yield_pct }}%</div>
                    <div class="text-[11px] text-emerald-600 font-medium mt-1">Reject &lt; 1.6% (CPOB Valid)</div>
                </div>
            </div>

            <!-- Chart & Widgets -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <div class="lg:col-span-2 bg-white p-5 rounded-xl border border-slate-200 shadow-xs">
                    <div class="flex items-center justify-between mb-4">
                        <div>
                            <h3 class="font-bold text-slate-900 text-sm">Indikator 3 Pilar Efisiensi OEE Mesin</h3>
                            <p class="text-xs text-slate-500">Live Kalkulasi Line Mixing & Filling</p>
                        </div>
                    </div>
                    <apexchart type="radialBar" height="280" :options="chartOptions" :series="chartSeries" />
                </div>

                <div class="bg-white p-5 rounded-xl border border-slate-200 shadow-xs flex flex-col justify-between">
                    <div>
                        <div class="flex items-center gap-2 mb-3">
                            <Timer class="w-4 h-4 text-rose-600" />
                            <h3 class="font-bold text-slate-900 text-sm">Estafet Batch Tracking</h3>
                        </div>
                        <p class="text-xs text-slate-500 mb-4">
                            Mixing &rarr; Filling &rarr; Packing &rarr; Packing Hold (Karantina QA).
                        </p>
                        <div class="space-y-2 text-xs">
                            <div class="p-2.5 rounded-lg bg-rose-50/60 border border-rose-100">
                                <div class="font-semibold text-rose-900">Batch #EXT-2026-088</div>
                                <div class="text-slate-500 text-[11px] mt-0.5">Ekstrak Temulawak 500kg • Tahap Filling</div>
                            </div>
                            <div class="p-2.5 rounded-lg bg-emerald-50/60 border border-emerald-100">
                                <div class="font-semibold text-emerald-900">Batch #CAP-2026-042</div>
                                <div class="text-slate-500 text-[11px] mt-0.5">Kapsul Meniran • Packing Hold QA Lulus</div>
                            </div>
                        </div>
                    </div>

                    <div class="pt-4 border-t border-slate-100 text-xs">
                        <Link href="/" class="text-rose-600 font-semibold hover:underline flex items-center justify-between">
                            <span>Kembali ke App Launcher</span>
                            <ArrowRight class="w-4 h-4" />
                        </Link>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
