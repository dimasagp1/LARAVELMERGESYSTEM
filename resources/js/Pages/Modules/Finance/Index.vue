<script setup>
import { Head, Link } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import {
    Landmark,
    TrendingUp,
    DollarSign,
    PieChart,
    ShieldAlert,
    FileSpreadsheet,
    Scale,
    AlertCircle,
    ArrowRight,
    CheckCircle,
} from 'lucide-vue-next';

const props = defineProps({
    module: Object,
    kpis: Object,
});

const chartOptions = {
    chart: {
        type: 'area',
        height: 280,
        toolbar: { show: false },
        fontFamily: 'inherit',
    },
    colors: ['#059669', '#3b82f6'],
    dataLabels: { enabled: false },
    stroke: { curve: 'smooth', width: 2 },
    xaxis: {
        categories: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'],
    },
    tooltip: { theme: 'light' },
};

const chartSeries = [
    {
        name: 'Actual P&L (Juta Rp)',
        data: [120, 140, 135, 180, 210, 240, 230, 260, 290, 310, 385, 410],
    },
    {
        name: 'Budget Plan (Juta Rp)',
        data: [130, 130, 150, 160, 200, 220, 220, 250, 270, 300, 350, 380],
    },
];
</script>

<template>
    <AppLayout>
        <Head :title="module?.name" />

        <div class="space-y-6">
            <!-- Header -->
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 pb-4 border-b border-slate-200">
                <div class="flex items-center gap-3">
                    <div class="w-12 h-12 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center border border-emerald-200">
                        <Landmark class="w-6 h-6" />
                    </div>
                    <div>
                        <div class="flex items-center gap-2">
                            <h1 class="text-xl font-bold text-slate-900">{{ module?.name }}</h1>
                            <span class="text-xs px-2 py-0.5 rounded bg-emerald-100 text-emerald-800 font-medium">Modul 1</span>
                        </div>
                        <p class="text-xs text-slate-500 mt-0.5">{{ module?.description }}</p>
                    </div>
                </div>

                <div class="flex items-center gap-2 text-xs">
                    <button class="px-3 py-1.5 rounded-lg bg-emerald-600 text-white font-medium hover:bg-emerald-700 transition">
                        + Ajukan AJE
                    </button>
                    <button class="px-3 py-1.5 rounded-lg border border-slate-300 text-slate-700 font-medium hover:bg-slate-50 transition">
                        Export Trial Balance
                    </button>
                </div>
            </div>

            <!-- KPI Cards -->
            <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
                <div class="bg-white p-4 rounded-xl border border-slate-200 shadow-xs">
                    <div class="text-xs text-slate-500 font-medium">Omset MTD</div>
                    <div class="text-xl font-bold text-slate-900 mt-1">{{ kpis?.omset_mtd }}</div>
                    <div class="text-[11px] text-emerald-600 font-medium mt-1 flex items-center gap-1">
                        <TrendingUp class="w-3 h-3" /> +12.4% vs target
                    </div>
                </div>

                <div class="bg-white p-4 rounded-xl border border-slate-200 shadow-xs">
                    <div class="text-xs text-slate-500 font-medium">Laba Bersih MTD</div>
                    <div class="text-xl font-bold text-slate-900 mt-1">{{ kpis?.net_profit_mtd }}</div>
                    <div class="text-[11px] text-emerald-600 font-medium mt-1">Margin: 26.5%</div>
                </div>

                <div class="bg-white p-4 rounded-xl border border-slate-200 shadow-xs">
                    <div class="text-xs text-slate-500 font-medium">Penyerapan Budget Perusahaan</div>
                    <div class="text-xl font-bold text-slate-900 mt-1">{{ kpis?.budget_absorption_pct }}%</div>
                    <div class="text-[11px] text-blue-600 font-medium mt-1">Safe Threshold (&lt; 85%)</div>
                </div>

                <div class="bg-white p-4 rounded-xl border border-slate-200 shadow-xs">
                    <div class="text-xs text-slate-500 font-medium">Skor 19 Rasio BSC (Keuangan)</div>
                    <div class="text-xl font-bold text-slate-900 mt-1">{{ kpis?.bsc_finance_score }} / 100</div>
                    <div class="text-[11px] text-emerald-600 font-medium mt-1">Status: Excellent (A)</div>
                </div>
            </div>

            <!-- Chart & Widgets -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- Main Chart -->
                <div class="lg:col-span-2 bg-white p-5 rounded-xl border border-slate-200 shadow-xs">
                    <div class="flex items-center justify-between mb-4">
                        <div>
                            <h3 class="font-bold text-slate-900 text-sm">Budget vs Actual P&L (12 Bulan)</h3>
                            <p class="text-xs text-slate-500">Drill-down per Cost Center & Real-Time Performance</p>
                        </div>
                        <span class="text-xs font-mono bg-emerald-50 text-emerald-700 px-2 py-0.5 rounded border border-emerald-200">
                            YTD 2026
                        </span>
                    </div>
                    <apexchart type="area" height="280" :options="chartOptions" :series="chartSeries" />
                </div>

                <!-- Gatekeeper & Interconnection Widget -->
                <div class="bg-white p-5 rounded-xl border border-slate-200 shadow-xs flex flex-col justify-between">
                    <div>
                        <div class="flex items-center gap-2 mb-3">
                            <ShieldAlert class="w-4 h-4 text-emerald-600" />
                            <h3 class="font-bold text-slate-900 text-sm">Budget Gatekeeper API Status</h3>
                        </div>
                        <p class="text-xs text-slate-500 mb-4">
                            Melindungi seluruh transaksi Procurement dan GA agar tidak melebihi pagu anggaran secara otomatis.
                        </p>

                        <div class="space-y-2 text-xs">
                            <div class="flex items-center justify-between p-2.5 rounded-lg bg-emerald-50/60 border border-emerald-100">
                                <span class="font-medium text-emerald-900">Procurement PR Check</span>
                                <span class="text-emerald-700 font-bold">Active / Gated</span>
                            </div>
                            <div class="flex items-center justify-between p-2.5 rounded-lg bg-emerald-50/60 border border-emerald-100">
                                <span class="font-medium text-emerald-900">GA Facility WO Expense</span>
                                <span class="text-emerald-700 font-bold">Active / Gated</span>
                            </div>
                            <div class="flex items-center justify-between p-2.5 rounded-lg bg-slate-50 border border-slate-200">
                                <span class="font-medium text-slate-700">3-Way Matching AP</span>
                                <span class="text-blue-700 font-bold">Auto-Sync</span>
                            </div>
                        </div>
                    </div>

                    <div class="pt-4 border-t border-slate-100 text-xs">
                        <Link href="/" class="text-emerald-600 font-semibold hover:underline flex items-center justify-between">
                            <span>Kembali ke App Launcher</span>
                            <ArrowRight class="w-4 h-4" />
                        </Link>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
