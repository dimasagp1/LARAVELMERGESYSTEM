<script setup>
import { Head } from '@inertiajs/vue3';
import ModuleLayout from '@/Layouts/ModuleLayout.vue';
import KpiStatCard from '@/Components/Dashboard/KpiStatCard.vue';
import InteractiveChartCard from '@/Components/Dashboard/InteractiveChartCard.vue';
import ApprovalInboxWidget from '@/Components/Dashboard/ApprovalInboxWidget.vue';
import QuickActionsBar from '@/Components/Dashboard/QuickActionsBar.vue';
import ActivityFeedWidget from '@/Components/Dashboard/ActivityFeedWidget.vue';
import { Truck, ClipboardCheck } from 'lucide-vue-next';

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
    { label: '+ Run MRP Engine', icon: 'Play', action: 'run_mrp', primary: true },
    { label: 'Penerimaan Goods Receipt', icon: 'Plus', action: 'create_gr' },
    { label: 'Mutasi Antar Gudang', icon: 'UploadCloud', action: 'stock_transfer' },
];

const chartOptions = {
    chart: {
        type: 'area',
        height: 290,
        toolbar: { show: false },
    },
    colors: ['#4f46e5', '#06b6d4'],
    dataLabels: { enabled: false },
    stroke: { curve: 'smooth', width: 2 },
    xaxis: {
        categories: ['M1', 'M2', 'M3', 'M4', 'M5', 'M6', 'M7', 'M8'],
    },
};

const chartSeries = [
    {
        name: 'Forecast Permintaan (Kg)',
        data: [1200, 1450, 1300, 1700, 1900, 1850, 2100, 2300],
    },
    {
        name: 'Realisasi Masuk GR (Kg)',
        data: [1150, 1400, 1350, 1680, 1850, 1900, 2050, 2250],
    },
];
</script>

<template>
    <ModuleLayout
        :title="module?.name"
        :module-slug="module?.slug || 'supply-chain'"
        :module-name="module?.name || 'Supply Chain (LogiFlow SCM)'"
        :color-theme="module?.color_theme || 'indigo'"
        :menus="module?.menus || []"
        :breadcrumbs="['Dashboard Rantai Pasok & Gudang']"
    >
        <Head :title="module?.name" />

        <div class="space-y-6">
            <!-- Header -->
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 pb-4 border-b border-slate-200 dark:border-slate-800">
                <div class="flex items-center gap-3">
                    <div class="w-12 h-12 rounded-xl bg-indigo-50 dark:bg-indigo-950/60 text-indigo-600 dark:text-indigo-400 flex items-center justify-center border border-indigo-200 dark:border-indigo-800 shrink-0">
                        <Truck class="w-6 h-6" />
                    </div>
                    <div>
                        <div class="flex items-center gap-2">
                            <h1 class="text-xl font-bold text-slate-900 dark:text-white">{{ module?.name }}</h1>
                            <span class="text-xs px-2 py-0.5 rounded bg-indigo-100 dark:bg-indigo-950 text-indigo-800 dark:text-indigo-300 font-medium">Modul 8</span>
                        </div>
                        <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">{{ module?.description }}</p>
                    </div>
                </div>

                <QuickActionsBar :actions="quickActions" />
            </div>

            <!-- KPI Cards -->
            <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
                <KpiStatCard
                    title="Under-Stock Alerts"
                    :value="`${kpis?.under_stock_alerts} Item`"
                    :trend="0"
                    subtitle="Di bawah Reorder Point"
                    status="rose"
                    :sparkline-data="[5, 4, 4, 3, 3, 3, 3]"
                />

                <KpiStatCard
                    title="Near-Expiry (< 90 Hari)"
                    :value="`${kpis?.near_expiry_items} Lot`"
                    :trend="-20.0"
                    subtitle="Prioritas FEFO Aktif"
                    status="amber"
                    :sparkline-data="[8, 7, 6, 6, 5, 5, 5]"
                />

                <KpiStatCard
                    title="Akurasi Net MRP"
                    :value="`${kpis?.mrp_accuracy_pct}%`"
                    :trend="1.5"
                    subtitle="Zero Stockout Produksi"
                    status="indigo"
                    :sparkline-data="[95, 95.5, 96, 96.5, 97, 97.2, 97.4]"
                />

                <KpiStatCard
                    title="3-Way Matching AP"
                    :value="`${kpis?.pending_3way_matching} Transaksi`"
                    :trend="-15.0"
                    subtitle="PO vs GR vs Tagihan"
                    status="blue"
                    :sparkline-data="[7, 6, 6, 5, 5, 4, 4]"
                />
            </div>

            <!-- Chart & Approval Inbox -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <div class="lg:col-span-2">
                    <InteractiveChartCard
                        title="Forecast Kebutuhan vs Realisasi Penerimaan Bahan (8 Minggu)"
                        subtitle="Kalkulasi Net Requirement MRP berbasis Safety Stock & Lead Time PO"
                        type="area"
                        :height="300"
                        :options="chartOptions"
                        :series="chartSeries"
                        selected-period="MTD"
                    />
                </div>

                <div>
                    <ApprovalInboxWidget
                        title="Verifikasi Penerimaan & AP"
                        :items="pendingApprovals"
                        module-slug="supply-chain"
                    />
                </div>
            </div>

            <!-- Bottom Row -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <div class="bg-white dark:bg-slate-900 rounded-xl border border-slate-200 dark:border-slate-800 p-5 shadow-xs">
                    <div class="flex items-center gap-2 mb-3">
                        <ClipboardCheck class="w-4 h-4 text-indigo-600 dark:text-indigo-400" />
                        <h3 class="font-bold text-slate-900 dark:text-white text-sm">Gudang Karantina QC Status</h3>
                    </div>
                    <p class="text-xs text-slate-500 dark:text-slate-400 mb-4">
                        Seluruh material masuk otomatis diisolasi berstatus Karantina dan terkunci dari rilis produksi.
                    </p>
                    <div class="space-y-2 text-xs">
                        <div class="p-2.5 rounded-lg bg-indigo-50/60 dark:bg-indigo-950/40 border border-indigo-100 dark:border-indigo-800 flex items-center justify-between">
                            <div>
                                <div class="font-semibold text-indigo-900 dark:text-indigo-200">GR-2026-0034 (Ekstrak Jahe Merah)</div>
                                <div class="text-slate-500 dark:text-slate-400 text-[11px]">Gudang Karantina • 400 Kg</div>
                            </div>
                            <span class="text-amber-600 dark:text-amber-400 font-bold font-mono">SAMPLING</span>
                        </div>
                        <div class="p-2.5 rounded-lg bg-emerald-50/60 dark:bg-emerald-950/40 border border-emerald-100 dark:border-emerald-800 flex items-center justify-between">
                            <div>
                                <div class="font-semibold text-emerald-900 dark:text-emerald-200">GR-2026-0031 (Botol Kaca Amber 100ml)</div>
                                <div class="text-slate-500 dark:text-slate-400 text-[11px]">Lulus Uji QC &rarr; Stok Aktif</div>
                            </div>
                            <span class="text-emerald-600 dark:text-emerald-400 font-bold font-mono">RELEASED</span>
                        </div>
                    </div>
                </div>

                <div class="lg:col-span-2">
                    <ActivityFeedWidget
                        title="Audit Log Pergerakan Persediaan & Gudang"
                        :activities="recentActivities"
                    />
                </div>
            </div>
        </div>
    </ModuleLayout>
</template>
