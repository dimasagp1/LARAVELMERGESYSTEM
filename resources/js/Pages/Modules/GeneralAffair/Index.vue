<script setup>
import { Head, Link } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import {
    Building2,
    Wrench,
    QrCode,
    Shield,
    FileText,
    ArrowRight,
    Clock,
} from 'lucide-vue-next';

const props = defineProps({
    module: Object,
    kpis: Object,
});

const chartOptions = {
    chart: {
        type: 'bar',
        height: 280,
        toolbar: { show: false },
        fontFamily: 'inherit',
    },
    colors: ['#d97706'],
    plotOptions: {
        bar: { borderRadius: 4, columnWidth: '45%' },
    },
    dataLabels: { enabled: false },
    xaxis: {
        categories: ['FAT', 'HR', 'PROC', 'PROD', 'QA', 'QC', 'RND', 'SCM'],
    },
    tooltip: { theme: 'light' },
};

const chartSeries = [
    {
        name: 'Work Orders Diselesaikan',
        data: [4, 6, 3, 14, 5, 4, 7, 9],
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
                    <div class="w-12 h-12 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center border border-amber-200">
                        <Building2 class="w-6 h-6" />
                    </div>
                    <div>
                        <div class="flex items-center gap-2">
                            <h1 class="text-xl font-bold text-slate-900">{{ module?.name }}</h1>
                            <span class="text-xs px-2 py-0.5 rounded bg-amber-100 text-amber-800 font-medium">Modul 2</span>
                        </div>
                        <p class="text-xs text-slate-500 mt-0.5">{{ module?.description }}</p>
                    </div>
                </div>

                <div class="flex items-center gap-2 text-xs">
                    <button class="px-3 py-1.5 rounded-lg bg-amber-600 text-white font-medium hover:bg-amber-700 transition">
                        + Buat Work Order (Form A)
                    </button>
                    <button class="px-3 py-1.5 rounded-lg border border-slate-300 text-slate-700 font-medium hover:bg-slate-50 transition">
                        Permintaan ATK
                    </button>
                </div>
            </div>

            <!-- KPI Cards -->
            <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
                <div class="bg-white p-4 rounded-xl border border-slate-200 shadow-xs">
                    <div class="text-xs text-slate-500 font-medium">Open Work Orders</div>
                    <div class="text-xl font-bold text-slate-900 mt-1">{{ kpis?.open_work_orders }}</div>
                    <div class="text-[11px] text-amber-600 font-medium mt-1">SLA Rata-rata: 3.4 Jam</div>
                </div>

                <div class="bg-white p-4 rounded-xl border border-slate-200 shadow-xs">
                    <div class="text-xs text-slate-500 font-medium">Permintaan ATK Pending</div>
                    <div class="text-xl font-bold text-slate-900 mt-1">{{ kpis?.pending_atk_requests }}</div>
                    <div class="text-[11px] text-slate-500 font-medium mt-1">Siklus Revisi Otomatis</div>
                </div>

                <div class="bg-white p-4 rounded-xl border border-slate-200 shadow-xs">
                    <div class="text-xs text-slate-500 font-medium">Total Nilai Buku Aset Tetap</div>
                    <div class="text-xl font-bold text-slate-900 mt-1">{{ kpis?.total_asset_value }}</div>
                    <div class="text-[11px] text-emerald-600 font-medium mt-1">Depresiasi Garis Lurus</div>
                </div>

                <div class="bg-white p-4 rounded-xl border border-slate-200 shadow-xs">
                    <div class="text-xs text-slate-500 font-medium">Anomali Patroli QR</div>
                    <div class="text-xl font-bold text-slate-900 mt-1">{{ kpis?.security_anomalies }}</div>
                    <div class="text-[11px] text-emerald-600 font-medium mt-1">Semua Checkpoint Bersih</div>
                </div>
            </div>

            <!-- Chart & Widgets -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <div class="lg:col-span-2 bg-white p-5 rounded-xl border border-slate-200 shadow-xs">
                    <div class="flex items-center justify-between mb-4">
                        <div>
                            <h3 class="font-bold text-slate-900 text-sm">Penyelesaian Work Order per Departemen</h3>
                            <p class="text-xs text-slate-500">Siklus 4-Form FPU (Request &rarr; Check &rarr; Material &rarr; Handover)</p>
                        </div>
                    </div>
                    <apexchart type="bar" height="280" :options="chartOptions" :series="chartSeries" />
                </div>

                <div class="bg-white p-5 rounded-xl border border-slate-200 shadow-xs flex flex-col justify-between">
                    <div>
                        <div class="flex items-center gap-2 mb-3">
                            <QrCode class="w-4 h-4 text-amber-600" />
                            <h3 class="font-bold text-slate-900 text-sm">Security & Cleaning Patrol</h3>
                        </div>
                        <p class="text-xs text-slate-500 mb-4">
                            Patroli mobile scan QR code titik lokasi dengan unggah foto evident real-time.
                        </p>
                        <div class="space-y-2 text-xs">
                            <div class="flex items-center justify-between p-2.5 rounded-lg bg-amber-50/60 border border-amber-100">
                                <span>Plant 1 Sukoharjo</span>
                                <span class="font-bold text-amber-800">12/12 Checkpoint</span>
                            </div>
                            <div class="flex items-center justify-between p-2.5 rounded-lg bg-amber-50/60 border border-amber-100">
                                <span>Plant 2 Boyolali</span>
                                <span class="font-bold text-amber-800">8/8 Checkpoint</span>
                            </div>
                        </div>
                    </div>

                    <div class="pt-4 border-t border-slate-100 text-xs">
                        <Link href="/" class="text-amber-600 font-semibold hover:underline flex items-center justify-between">
                            <span>Kembali ke App Launcher</span>
                            <ArrowRight class="w-4 h-4" />
                        </Link>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
