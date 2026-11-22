<script setup>
import { Head, Link } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import {
    Truck,
    Boxes,
    Warehouse,
    AlertCircle,
    CheckCircle,
    RefreshCw,
    ArrowRight,
    ClipboardCheck,
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
    colors: ['#4f46e5', '#06b6d4'],
    dataLabels: { enabled: false },
    stroke: { curve: 'smooth', width: 2 },
    xaxis: {
        categories: ['M1', 'M2', 'M3', 'M4', 'M5', 'M6', 'M7', 'M8'],
    },
    tooltip: { theme: 'light' },
};

const chartSeries = [
    {
        name: 'Forecast Permintaan (Kg)',
        data: [1200, 1450, 1300, 1700, 1900, 1850, 2100, 2300],
    },
    {
        name: 'Realisasi Stok Masuk GR (Kg)',
        data: [1150, 1400, 1350, 1680, 1850, 1900, 2050, 2250],
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
                    <div class="w-12 h-12 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center border border-indigo-200">
                        <Truck class="w-6 h-6" />
                    </div>
                    <div>
                        <div class="flex items-center gap-2">
                            <h1 class="text-xl font-bold text-slate-900">{{ module?.name }}</h1>
                            <span class="text-xs px-2 py-0.5 rounded bg-indigo-100 text-indigo-800 font-medium">Modul 8</span>
                        </div>
                        <p class="text-xs text-slate-500 mt-0.5">{{ module?.description }}</p>
                    </div>
                </div>

                <div class="flex items-center gap-2 text-xs">
                    <button class="px-3 py-1.5 rounded-lg bg-indigo-600 text-white font-medium hover:bg-indigo-700 transition flex items-center gap-1.5">
                        <RefreshCw class="w-3.5 h-3.5" />
                        Run MRP Engine
                    </button>
                    <button class="px-3 py-1.5 rounded-lg border border-slate-300 text-slate-700 font-medium hover:bg-slate-50 transition">
                        Penerimaan Goods Receipt
                    </button>
                </div>
            </div>

            <!-- KPI Cards -->
            <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
                <div class="bg-white p-4 rounded-xl border border-slate-200 shadow-xs">
                    <div class="text-xs text-slate-500 font-medium">Smart Alert Under-Stock</div>
                    <div class="text-xl font-bold text-slate-900 mt-1">{{ kpis?.under_stock_alerts }} Item</div>
                    <div class="text-[11px] text-rose-600 font-medium mt-1">Di bawah Reorder Point</div>
                </div>

                <div class="bg-white p-4 rounded-xl border border-slate-200 shadow-xs">
                    <div class="text-xs text-slate-500 font-medium">Near-Expiry (&lt; 90 Hari)</div>
                    <div class="text-xl font-bold text-slate-900 mt-1">{{ kpis?.near_expiry_items }} Lot</div>
                    <div class="text-[11px] text-amber-600 font-medium mt-1">Prioritas FEFO Aktif</div>
                </div>

                <div class="bg-white p-4 rounded-xl border border-slate-200 shadow-xs">
                    <div class="text-xs text-slate-500 font-medium">Akurasi MRP Net Requirement</div>
                    <div class="text-xl font-bold text-slate-900 mt-1">{{ kpis?.mrp_accuracy_pct }}%</div>
                    <div class="text-[11px] text-emerald-600 font-medium mt-1">Zero Stockout Produksi</div>
                </div>

                <div class="bg-white p-4 rounded-xl border border-slate-200 shadow-xs">
                    <div class="text-xs text-slate-500 font-medium">3-Way Matching AP Pending</div>
                    <div class="text-xl font-bold text-slate-900 mt-1">{{ kpis?.pending_3way_matching }} Transaksi</div>
                    <div class="text-[11px] text-blue-600 font-medium mt-1">PO vs GR vs Tagihan Vendor</div>
                </div>
            </div>

            <!-- Chart & Widgets -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <div class="lg:col-span-2 bg-white p-5 rounded-xl border border-slate-200 shadow-xs">
                    <div class="flex items-center justify-between mb-4">
                        <div>
                            <h3 class="font-bold text-slate-900 text-sm">Forecast vs Realisasi Penerimaan Bahan (8 Minggu)</h3>
                            <p class="text-xs text-slate-500">Kalkulasi Net Requirement MRP berbasis Lead Time Pengadaan</p>
                        </div>
                    </div>
                    <apexchart type="area" height="280" :options="chartOptions" :series="chartSeries" />
                </div>

                <div class="bg-white p-5 rounded-xl border border-slate-200 shadow-xs flex flex-col justify-between">
                    <div>
                        <div class="flex items-center gap-2 mb-3">
                            <ClipboardCheck class="w-4 h-4 text-indigo-600" />
                            <h3 class="font-bold text-slate-900 text-sm">Karantina Gudang QC</h3>
                        </div>
                        <p class="text-xs text-slate-500 mb-4">
                            Seluruh bahan masuk otomatis diisolasi berstatus Karantina dan terkunci dari konsumsi produksi.
                        </p>
                        <div class="space-y-2 text-xs">
                            <div class="p-2.5 rounded-lg bg-indigo-50/60 border border-indigo-100 flex items-center justify-between">
                                <div>
                                    <div class="font-semibold text-indigo-900">GR-2026-0034 (Ekstrak Jahe Merah)</div>
                                    <div class="text-slate-500 text-[11px]">Gudang Karantina • 400 Kg</div>
                                </div>
                                <span class="text-amber-700 font-bold">SAMPLING</span>
                            </div>
                            <div class="p-2.5 rounded-lg bg-emerald-50/60 border border-emerald-100 flex items-center justify-between">
                                <div>
                                    <div class="font-semibold text-emerald-900">GR-2026-0031 (Botol Kaca Amber 100ml)</div>
                                    <div class="text-slate-500 text-[11px]">Lulus Uji QC &rarr; Stok Aktif</div>
                                </div>
                                <span class="text-emerald-700 font-bold">RELEASED</span>
                            </div>
                        </div>
                    </div>

                    <div class="pt-4 border-t border-slate-100 text-xs">
                        <Link href="/" class="text-indigo-600 font-semibold hover:underline flex items-center justify-between">
                            <span>Kembali ke App Launcher</span>
                            <ArrowRight class="w-4 h-4" />
                        </Link>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
