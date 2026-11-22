<script setup>
import { ref } from 'vue';
import { Head, Link } from '@inertiajs/vue3';
import ModuleLayout from '@/Layouts/ModuleLayout.vue';
import KpiStatCard from '@/Components/Dashboard/KpiStatCard.vue';
import InteractiveChartCard from '@/Components/Dashboard/InteractiveChartCard.vue';
import {
    LayoutDashboard,
    Cpu,
    Truck,
    ShieldCheck,
    ArrowRightCircle,
    CornerUpLeft,
    FileCheck2,
    Warehouse,
    AlertTriangle,
    Clock,
    Plus,
    Boxes,
    Eye,
    TrendingUp,
    CheckCircle2,
    FileText,
    ArrowUpRight,
    RotateCcw
} from 'lucide-vue-next';

const props = defineProps({
    module: {
        type: Object,
        default: () => ({
            name: 'Supply Chain & Inventory',
            slug: 'supply-chain',
            description: 'LogiFlow SCM — MRP, Goods Receipt, QC 4-Parameter, BSTB, 3-Way Matching, Multi-Warehouse.',
            color_theme: 'emerald',
        }),
    },
    metrics: {
        type: Object,
        required: true,
    },
});

const formatCurrency = (val) => {
    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        maximumFractionDigits: 0,
    }).format(val || 0);
};

const formatNumber = (val) => {
    return new Intl.NumberFormat('id-ID').format(val || 0);
};

// Quick action shortcuts
const quickActions = [
    { title: 'Simulasi MRP', desc: 'Hitung kebutuhan bersih bahan baku', href: '/supply-chain/mrp', icon: Cpu, color: 'bg-emerald-50 text-emerald-700 hover:border-emerald-300' },
    { title: 'Catat GRN Masuk', desc: 'Input surat jalan vendor & auto-karantina', href: '/supply-chain/goods-receipt/create', icon: Truck, color: 'bg-teal-50 text-teal-700 hover:border-teal-300' },
    { title: 'Pemeriksaan QC 4-Param', desc: 'Validasi visual, lab, kuantitas & CoA', href: '/supply-chain/quarantine', icon: ShieldCheck, color: 'bg-amber-50 text-amber-700 hover:border-amber-300' },
    { title: 'Keluarkan BSTB', desc: 'Serah terima bahan ke lantai produksi', href: '/supply-chain/material-usage/create', icon: ArrowRightCircle, color: 'bg-blue-50 text-blue-700 hover:border-blue-300' },
    { title: 'Audit 3-Way Matching', desc: 'Pencocokan PO vs GR Lulus vs Faktur', href: '/supply-chain/matching', icon: FileCheck2, color: 'bg-purple-50 text-purple-700 hover:border-purple-300' },
    { title: 'Matriks Stok Multi-WH', desc: 'Lacak lot, kedaluwarsa & mutasi gudang', href: '/supply-chain/stock', icon: Warehouse, color: 'bg-indigo-50 text-indigo-700 hover:border-indigo-300' },
];
</script>

<template>
    <Head title="LogiFlow SCM - Supply Chain & Inventory Overview" />

    <ModuleLayout :module="module">
        <div class="space-y-6">
            <!-- Header Section -->
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 bg-white dark:bg-slate-800 p-6 rounded-2xl shadow-sm border border-slate-200 dark:border-slate-700">
                <div>
                    <div class="flex items-center gap-3">
                        <div class="p-2.5 bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 rounded-xl">
                            <Boxes class="w-7 h-7" />
                        </div>
                        <div>
                            <h1 class="text-2xl font-bold text-slate-900 dark:text-white tracking-tight">
                                LogiFlow SCM — Supply Chain & Inventory
                            </h1>
                            <p class="text-sm text-slate-500 dark:text-slate-400 mt-0.5">
                                Kendali rantai pasok tertutup, simulasi MRP, karantina mutu CPOTB 4-parameter, dan audit 3-way matching.
                            </p>
                        </div>
                    </div>
                </div>

                <div class="flex items-center gap-3">
                    <Link
                        href="/supply-chain/goods-receipt/create"
                        class="inline-flex items-center gap-2 px-4 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-semibold rounded-xl shadow-sm transition-all shadow-emerald-500/20"
                    >
                        <Plus class="w-4 h-4" />
                        <span>Penerimaan Barang Baru</span>
                    </Link>
                    <Link
                        href="/supply-chain/mrp"
                        class="inline-flex items-center gap-2 px-4 py-2.5 bg-slate-100 dark:bg-slate-700 hover:bg-slate-200 dark:hover:bg-slate-600 text-slate-700 dark:text-slate-200 text-sm font-semibold rounded-xl transition-all"
                    >
                        <Cpu class="w-4 h-4 text-emerald-600 dark:text-emerald-400" />
                        <span>Kalkulasi MRP</span>
                    </Link>
                </div>
            </div>

            <!-- KPI Summary Cards -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-6 gap-4">
                <KpiStatCard
                    title="Valuasi Persediaan"
                    :value="formatCurrency(metrics.kpis.total_valuation)"
                    trend="+4.2%"
                    trend-type="up"
                    status="success"
                    subtitle="Saldo fisik aktif"
                    icon="DollarSign"
                />

                <KpiStatCard
                    title="Stok Kritis Rendah"
                    :value="metrics.kpis.low_stock_count"
                    trend="Perlu Reorder Segera"
                    trend-type="down"
                    :status="metrics.kpis.low_stock_count > 0 ? 'danger' : 'success'"
                    subtitle="Di bawah safety stock"
                    icon="AlertTriangle"
                />

                <KpiStatCard
                    title="Mendekati ED (<90h)"
                    :value="metrics.kpis.near_expiry_count"
                    trend="Prioritas FEFO"
                    trend-type="down"
                    :status="metrics.kpis.near_expiry_count > 0 ? 'warning' : 'success'"
                    subtitle="Perlu release / disposal"
                    icon="Clock"
                />

                <KpiStatCard
                    title="Antrean Karantina QC"
                    :value="metrics.kpis.pending_qc_count"
                    trend="4-Parameter Check"
                    trend-type="neutral"
                    status="warning"
                    subtitle="Lot menunggu rilis"
                    icon="ShieldCheck"
                />

                <KpiStatCard
                    title="Selisih 3-Way Match"
                    :value="metrics.kpis.pending_matching_count"
                    trend="Audit PO vs GR vs Bill"
                    trend-type="neutral"
                    :status="metrics.kpis.pending_matching_count > 0 ? 'danger' : 'success'"
                    subtitle="Invoice berselisih"
                    icon="FileCheck2"
                />

                <KpiStatCard
                    title="Inbound Karantina"
                    :value="metrics.kpis.quarantine_receipts_count"
                    trend="Gudang WH-QRN"
                    trend-type="neutral"
                    status="info"
                    subtitle="GRN belum tuntas"
                    icon="Truck"
                />
            </div>

            <!-- Quick Action Cards Grid -->
            <div>
                <h2 class="text-sm font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400 mb-3">
                    Aksi Cepat & Navigasi Operasional SCM
                </h2>
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-6 gap-3">
                    <Link
                        v-for="act in quickActions"
                        :key="act.title"
                        :href="act.href"
                        class="p-4 bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 hover:shadow-md transition-all group flex flex-col justify-between"
                    >
                        <div class="flex items-center justify-between mb-3">
                            <div :class="['p-2.5 rounded-lg border border-transparent transition-colors', act.color]">
                                <component :is="act.icon" class="w-5 h-5" />
                            </div>
                            <ArrowUpRight class="w-4 h-4 text-slate-400 group-hover:text-emerald-600 transition-colors" />
                        </div>
                        <div>
                            <div class="font-semibold text-sm text-slate-800 dark:text-slate-100 group-hover:text-emerald-600 dark:group-hover:text-emerald-400">
                                {{ act.title }}
                            </div>
                            <div class="text-xs text-slate-500 dark:text-slate-400 mt-0.5 line-clamp-1">
                                {{ act.desc }}
                            </div>
                        </div>
                    </Link>
                </div>
            </div>

            <!-- Interactive Charts Grid -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- Valuasi Berdasarkan Kategori Material -->
                <div class="bg-white dark:bg-slate-800 p-5 rounded-2xl border border-slate-200 dark:border-slate-700 shadow-sm flex flex-col justify-between">
                    <div>
                        <div class="flex items-center justify-between mb-1">
                            <h3 class="font-bold text-slate-900 dark:text-white text-base">Valuasi Persediaan (Juta IDR)</h3>
                            <span class="text-xs font-medium px-2 py-0.5 bg-emerald-50 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-400 rounded-full">Kategori Material</span>
                        </div>
                        <p class="text-xs text-slate-500 dark:text-slate-400 mb-4">Distribusi aset inventori antara Bahan Baku, Kemas & Produk Jadi.</p>
                    </div>

                    <InteractiveChartCard
                        title=""
                        type="donut"
                        :series="metrics.charts.valuation_by_type.series"
                        :categories="metrics.charts.valuation_by_type.labels"
                        :colors="['#10b981', '#3b82f6', '#8b5cf6', '#f59e0b', '#64748b']"
                        height="260"
                    />
                </div>

                <!-- Distribusi Status Gudang -->
                <div class="bg-white dark:bg-slate-800 p-5 rounded-2xl border border-slate-200 dark:border-slate-700 shadow-sm flex flex-col justify-between">
                    <div>
                        <div class="flex items-center justify-between mb-1">
                            <h3 class="font-bold text-slate-900 dark:text-white text-base">Status Fisik Persediaan</h3>
                            <span class="text-xs font-medium px-2 py-0.5 bg-blue-50 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400 rounded-full">Total Qty (Kg/Pcs)</span>
                        </div>
                        <p class="text-xs text-slate-500 dark:text-slate-400 mb-4">Perbandingan material Lulus (Tersedia), Karantina QC, dan Ditolak.</p>
                    </div>

                    <InteractiveChartCard
                        title=""
                        type="bar"
                        :series="[{ name: 'Kuantitas Stok', data: metrics.charts.status_distribution.series }]"
                        :categories="metrics.charts.status_distribution.labels"
                        :colors="['#0d9488']"
                        height="260"
                    />
                </div>

                <!-- Tren Arus Masuk vs Keluar -->
                <div class="bg-white dark:bg-slate-800 p-5 rounded-2xl border border-slate-200 dark:border-slate-700 shadow-sm flex flex-col justify-between">
                    <div>
                        <div class="flex items-center justify-between mb-1">
                            <h3 class="font-bold text-slate-900 dark:text-white text-base">Arus Barang Masuk vs Keluar</h3>
                            <span class="text-xs font-medium px-2 py-0.5 bg-purple-50 text-purple-700 dark:bg-purple-900/30 dark:text-purple-400 rounded-full">6 Bulan Terakhir</span>
                        </div>
                        <p class="text-xs text-slate-500 dark:text-slate-400 mb-4">Volume Penerimaan Inbound (GRN) vs Pengeluaran Pabrik (BSTB).</p>
                    </div>

                    <InteractiveChartCard
                        title=""
                        type="line"
                        :series="[
                            { name: 'Inbound GRN', data: metrics.charts.movement_trend.inbound },
                            { name: 'Outbound BSTB', data: metrics.charts.movement_trend.outbound }
                        ]"
                        :categories="metrics.charts.movement_trend.months"
                        :colors="['#10b981', '#f43f5e']"
                        height="260"
                    />
                </div>
            </div>

            <!-- Two-Column Feed: Critical Alerts & Recent Inbound Goods Receipts -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <!-- Critical Low Stock Alerts -->
                <div class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-200 dark:border-slate-700 shadow-sm p-6">
                    <div class="flex items-center justify-between mb-4">
                        <div class="flex items-center gap-2">
                            <AlertTriangle class="w-5 h-5 text-amber-500" />
                            <h3 class="font-bold text-slate-900 dark:text-white text-base">Peringatan Material Kritis (< Safety Stock)</h3>
                        </div>
                        <Link href="/supply-chain/mrp" class="text-xs text-emerald-600 hover:text-emerald-700 font-semibold flex items-center gap-1">
                            <span>Jalankan MRP</span>
                            <ArrowUpRight class="w-3.5 h-3.5" />
                        </Link>
                    </div>

                    <div v-if="metrics.critical_stocks && metrics.critical_stocks.length > 0" class="overflow-x-auto">
                        <table class="w-full text-left text-xs">
                            <thead>
                                <tr class="border-b border-slate-200 dark:border-slate-700 text-slate-400 font-semibold uppercase">
                                    <th class="pb-2.5">Kode & Nama Material</th>
                                    <th class="pb-2.5">Gudang</th>
                                    <th class="pb-2.5 text-right">Stok Bebas</th>
                                    <th class="pb-2.5 text-right">Safety Stock</th>
                                    <th class="pb-2.5 text-center">Status</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100 dark:divide-slate-700/50">
                                <tr v-for="stock in metrics.critical_stocks" :key="stock.id" class="hover:bg-slate-50 dark:hover:bg-slate-700/30">
                                    <td class="py-3">
                                        <div class="font-semibold text-slate-800 dark:text-slate-200">{{ stock.product?.code }}</div>
                                        <div class="text-slate-500 line-clamp-1">{{ stock.product?.name }}</div>
                                    </td>
                                    <td class="py-3 text-slate-600 dark:text-slate-300">
                                        {{ stock.location?.code ?? '-' }}
                                    </td>
                                    <td class="py-3 text-right font-bold text-rose-600">
                                        {{ formatNumber(stock.quantity_available) }} {{ stock.product?.uom?.code }}
                                    </td>
                                    <td class="py-3 text-right text-slate-500">
                                        {{ formatNumber(stock.product?.min_stock_alert) }} {{ stock.product?.uom?.code }}
                                    </td>
                                    <td class="py-3 text-center">
                                        <span class="inline-flex px-2 py-0.5 rounded-full text-[10px] font-bold bg-rose-50 text-rose-700 dark:bg-rose-950/40 dark:text-rose-400">
                                            UNDER-STOCK
                                        </span>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <div v-else class="py-8 text-center text-slate-500 text-xs">
                        <CheckCircle2 class="w-8 h-8 text-emerald-500 mx-auto mb-2" />
                        Seluruh saldo material dalam batas aman di atas safety stock.
                    </div>
                </div>

                <!-- Recent Inbound Goods Receipts -->
                <div class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-200 dark:border-slate-700 shadow-sm p-6">
                    <div class="flex items-center justify-between mb-4">
                        <div class="flex items-center gap-2">
                            <Truck class="w-5 h-5 text-teal-600" />
                            <h3 class="font-bold text-slate-900 dark:text-white text-base">Penerimaan Barang Terbaru (GRN)</h3>
                        </div>
                        <Link href="/supply-chain/goods-receipt" class="text-xs text-emerald-600 hover:text-emerald-700 font-semibold flex items-center gap-1">
                            <span>Lihat Semua</span>
                            <ArrowUpRight class="w-3.5 h-3.5" />
                        </Link>
                    </div>

                    <div v-if="metrics.recent_goods_receipts && metrics.recent_goods_receipts.length > 0" class="overflow-x-auto">
                        <table class="w-full text-left text-xs">
                            <thead>
                                <tr class="border-b border-slate-200 dark:border-slate-700 text-slate-400 font-semibold uppercase">
                                    <th class="pb-2.5">No. GRN</th>
                                    <th class="pb-2.5">Vendor</th>
                                    <th class="pb-2.5">Surat Jalan</th>
                                    <th class="pb-2.5 text-center">Status</th>
                                    <th class="pb-2.5 text-right">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100 dark:divide-slate-700/50">
                                <tr v-for="grn in metrics.recent_goods_receipts" :key="grn.id" class="hover:bg-slate-50 dark:hover:bg-slate-700/30">
                                    <td class="py-3">
                                        <div class="font-semibold text-emerald-600">{{ grn.grn_number }}</div>
                                        <div class="text-slate-400 text-[10px]">PO: {{ grn.po_number ?? '-' }}</div>
                                    </td>
                                    <td class="py-3 font-medium text-slate-800 dark:text-slate-200">
                                        {{ grn.vendor?.name ?? '-' }}
                                    </td>
                                    <td class="py-3 text-slate-600 dark:text-slate-400">
                                        {{ grn.delivery_order_number }}
                                    </td>
                                    <td class="py-3 text-center">
                                        <span
                                            :class="[
                                                'inline-flex px-2 py-0.5 rounded-full text-[10px] font-bold uppercase',
                                                grn.status === 'completed'
                                                    ? 'bg-emerald-50 text-emerald-700 dark:bg-emerald-950/40 dark:text-emerald-400'
                                                    : 'bg-amber-50 text-amber-700 dark:bg-amber-950/40 dark:text-amber-400'
                                            ]"
                                        >
                                            {{ grn.status }}
                                        </span>
                                    </td>
                                    <td class="py-3 text-right">
                                        <Link
                                            :href="`/supply-chain/goods-receipt/${grn.id}`"
                                            class="p-1.5 text-slate-400 hover:text-emerald-600 transition-colors inline-block"
                                            title="Buka Detail GRN"
                                        >
                                            <Eye class="w-4 h-4" />
                                        </Link>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <div v-else class="py-8 text-center text-slate-500 text-xs">
                        Belum ada data penerimaan barang masuk.
                    </div>
                </div>
            </div>
        </div>
    </ModuleLayout>
</template>
