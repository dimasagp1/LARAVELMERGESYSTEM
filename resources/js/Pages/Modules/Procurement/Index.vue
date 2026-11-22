<script setup>
import { Head, Link } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import {
    ShoppingCart,
    Clock,
    DollarSign,
    CheckCircle,
    FileCheck,
    ArrowRight,
    Building,
} from 'lucide-vue-next';

const props = defineProps({
    module: Object,
    kpis: Object,
});

const chartOptions = {
    chart: {
        type: 'donut',
        height: 280,
        fontFamily: 'inherit',
    },
    colors: ['#8b5cf6', '#a78bfa', '#c4b5fd', '#10b981'],
    labels: ['Raw Material (RM)', 'Packaging Material (PM)', 'Fasilitas & Mesin', 'Umum & ATK'],
    legend: { position: 'bottom' },
    tooltip: { theme: 'light' },
};

const chartSeries = [450, 280, 120, 70];
</script>

<template>
    <AppLayout>
        <Head :title="module?.name" />

        <div class="space-y-6">
            <!-- Header -->
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 pb-4 border-b border-slate-200">
                <div class="flex items-center gap-3">
                    <div class="w-12 h-12 rounded-xl bg-violet-50 text-violet-600 flex items-center justify-center border border-violet-200">
                        <ShoppingCart class="w-6 h-6" />
                    </div>
                    <div>
                        <div class="flex items-center gap-2">
                            <h1 class="text-xl font-bold text-slate-900">{{ module?.name }}</h1>
                            <span class="text-xs px-2 py-0.5 rounded bg-violet-100 text-violet-800 font-medium">Modul 4</span>
                        </div>
                        <p class="text-xs text-slate-500 mt-0.5">{{ module?.description }}</p>
                    </div>
                </div>

                <div class="flex items-center gap-2 text-xs">
                    <button class="px-3 py-1.5 rounded-lg bg-violet-600 text-white font-medium hover:bg-violet-700 transition">
                        + Buat Purchase Request (PR)
                    </button>
                    <button class="px-3 py-1.5 rounded-lg border border-slate-300 text-slate-700 font-medium hover:bg-slate-50 transition">
                        Quotation Matrix Bidding
                    </button>
                </div>
            </div>

            <!-- KPI Cards -->
            <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
                <div class="bg-white p-4 rounded-xl border border-slate-200 shadow-xs">
                    <div class="text-xs text-slate-500 font-medium">Total Active PR</div>
                    <div class="text-xl font-bold text-slate-900 mt-1">{{ kpis?.active_pr_count }}</div>
                    <div class="text-[11px] text-violet-600 font-medium mt-1">Granular 8-Step Approval</div>
                </div>

                <div class="bg-white p-4 rounded-xl border border-slate-200 shadow-xs">
                    <div class="text-xs text-slate-500 font-medium">Nilai Total PO Bulan Ini</div>
                    <div class="text-xl font-bold text-slate-900 mt-1">{{ kpis?.po_value_mtd }}</div>
                    <div class="text-[11px] text-emerald-600 font-medium mt-1">Committed Budget Active</div>
                </div>

                <div class="bg-white p-4 rounded-xl border border-slate-200 shadow-xs">
                    <div class="text-xs text-slate-500 font-medium">PR-to-PO Lead Time</div>
                    <div class="text-xl font-bold text-slate-900 mt-1">{{ kpis?.avg_lead_time_days }} Hari</div>
                    <div class="text-[11px] text-emerald-600 font-medium mt-1">Target: &lt; 7 Hari</div>
                </div>

                <div class="bg-white p-4 rounded-xl border border-slate-200 shadow-xs">
                    <div class="text-xs text-slate-500 font-medium">Saving Pengadaan MTD</div>
                    <div class="text-xl font-bold text-slate-900 mt-1">{{ kpis?.cost_saving_pct }}%</div>
                    <div class="text-[11px] text-emerald-600 font-medium mt-1">Hasil Bidding 3 Supplier</div>
                </div>
            </div>

            <!-- Chart & Widgets -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <div class="lg:col-span-2 bg-white p-5 rounded-xl border border-slate-200 shadow-xs">
                    <div class="flex items-center justify-between mb-4">
                        <div>
                            <h3 class="font-bold text-slate-900 text-sm">Distribusi Nilai PO per Kategori (Juta Rp)</h3>
                            <p class="text-xs text-slate-500">Integrasi Odoo PO (JSON-RPC) & Sinkronisasi ERP</p>
                        </div>
                    </div>
                    <apexchart type="donut" height="280" :options="chartOptions" :series="chartSeries" />
                </div>

                <div class="bg-white p-5 rounded-xl border border-slate-200 shadow-xs flex flex-col justify-between">
                    <div>
                        <div class="flex items-center gap-2 mb-3">
                            <Building class="w-4 h-4 text-violet-600" />
                            <h3 class="font-bold text-slate-900 text-sm">Evaluasi Pemasok (AVL)</h3>
                        </div>
                        <p class="text-xs text-slate-500 mb-4">
                            4 Pilar Mutu Pengadaan: Ketepatan Waktu, Kuantitas, Kualitas, dan Kestabilan Harga.
                        </p>
                        <div class="space-y-2 text-xs">
                            <div class="p-2.5 rounded-lg bg-violet-50/60 border border-violet-100 flex items-center justify-between">
                                <div>
                                    <div class="font-semibold text-violet-900">PT Herbalindo Ekstrak Mandiri</div>
                                    <div class="text-slate-500 text-[11px]">RM Herba • AVL Grade A</div>
                                </div>
                                <span class="font-bold text-emerald-600">96.5</span>
                            </div>
                            <div class="p-2.5 rounded-lg bg-violet-50/60 border border-violet-100 flex items-center justify-between">
                                <div>
                                    <div class="font-semibold text-violet-900">CV Packindo Sejahtera</div>
                                    <div class="text-slate-500 text-[11px]">Botol & Blister • AVL Grade A</div>
                                </div>
                                <span class="font-bold text-emerald-600">94.0</span>
                            </div>
                        </div>
                    </div>

                    <div class="pt-4 border-t border-slate-100 text-xs">
                        <Link href="/" class="text-violet-600 font-semibold hover:underline flex items-center justify-between">
                            <span>Kembali ke App Launcher</span>
                            <ArrowRight class="w-4 h-4" />
                        </Link>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
