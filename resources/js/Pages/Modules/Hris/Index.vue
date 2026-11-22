<script setup>
import { Head, Link } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import {
    Users,
    Clock,
    Award,
    Banknote,
    CheckCircle2,
    Calendar,
    ArrowRight,
} from 'lucide-vue-next';

const props = defineProps({
    module: Object,
    kpis: Object,
});

const chartOptions = {
    chart: {
        type: 'line',
        height: 280,
        toolbar: { show: false },
        fontFamily: 'inherit',
    },
    colors: ['#2563eb', '#10b981'],
    stroke: { curve: 'smooth', width: [3, 2] },
    xaxis: {
        categories: ['Sen', 'Sel', 'Rab', 'Kam', 'Jum', 'Sab', 'Min'],
    },
    tooltip: { theme: 'light' },
};

const chartSeries = [
    {
        name: 'Presensi Karyawan (%)',
        data: [98.2, 97.4, 96.8, 97.1, 95.8, 94.0, 92.5],
    },
    {
        name: 'Target Kehadiran (%)',
        data: [95, 95, 95, 95, 95, 95, 95],
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
                    <div class="w-12 h-12 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center border border-blue-200">
                        <Users class="w-6 h-6" />
                    </div>
                    <div>
                        <div class="flex items-center gap-2">
                            <h1 class="text-xl font-bold text-slate-900">{{ module?.name }}</h1>
                            <span class="text-xs px-2 py-0.5 rounded bg-blue-100 text-blue-800 font-medium">Modul 3</span>
                        </div>
                        <p class="text-xs text-slate-500 mt-0.5">{{ module?.description }}</p>
                    </div>
                </div>

                <div class="flex items-center gap-2 text-xs">
                    <button class="px-3 py-1.5 rounded-lg bg-blue-600 text-white font-medium hover:bg-blue-700 transition">
                        + Input Kaizen Kizuki
                    </button>
                    <button class="px-3 py-1.5 rounded-lg border border-slate-300 text-slate-700 font-medium hover:bg-slate-50 transition">
                        Run Payroll TER
                    </button>
                </div>
            </div>

            <!-- KPI Cards -->
            <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
                <div class="bg-white p-4 rounded-xl border border-slate-200 shadow-xs">
                    <div class="text-xs text-slate-500 font-medium">Total Headcount</div>
                    <div class="text-xl font-bold text-slate-900 mt-1">{{ kpis?.total_headcount }}</div>
                    <div class="text-[11px] text-blue-600 font-medium mt-1">160 TKL • 88 TKTL</div>
                </div>

                <div class="bg-white p-4 rounded-xl border border-slate-200 shadow-xs">
                    <div class="text-xs text-slate-500 font-medium">Presensi Hari Ini</div>
                    <div class="text-xl font-bold text-slate-900 mt-1">{{ kpis?.attendance_today_pct }}%</div>
                    <div class="text-[11px] text-emerald-600 font-medium mt-1">Biometrik & Geofence Valid</div>
                </div>

                <div class="bg-white p-4 rounded-xl border border-slate-200 shadow-xs">
                    <div class="text-xs text-slate-500 font-medium">Total Jam Lembur MTD</div>
                    <div class="text-xl font-bold text-slate-900 mt-1">{{ kpis?.overtime_hours_mtd }} Jam</div>
                    <div class="text-[11px] text-slate-500 font-medium mt-1">Time Bank Balance: +48 Jam</div>
                </div>

                <div class="bg-white p-4 rounded-xl border border-slate-200 shadow-xs">
                    <div class="text-xs text-slate-500 font-medium">Rata-rata Skor BSC L3/L4</div>
                    <div class="text-xl font-bold text-slate-900 mt-1">{{ kpis?.performance_bsc_score }} / 100</div>
                    <div class="text-[11px] text-emerald-600 font-medium mt-1">35 Sasaran Mutu Terkait</div>
                </div>
            </div>

            <!-- Chart & Widgets -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <div class="lg:col-span-2 bg-white p-5 rounded-xl border border-slate-200 shadow-xs">
                    <div class="flex items-center justify-between mb-4">
                        <div>
                            <h3 class="font-bold text-slate-900 text-sm">Tren Presensi Mingguan</h3>
                            <p class="text-xs text-slate-500">Verifikasi Face Matching & Anti-Fake GPS Geofencing</p>
                        </div>
                    </div>
                    <apexchart type="line" height="280" :options="chartOptions" :series="chartSeries" />
                </div>

                <div class="bg-white p-5 rounded-xl border border-slate-200 shadow-xs flex flex-col justify-between">
                    <div>
                        <div class="flex items-center gap-2 mb-3">
                            <Award class="w-4 h-4 text-blue-600" />
                            <h3 class="font-bold text-slate-900 text-sm">Kaizen Kizuki Feed</h3>
                        </div>
                        <p class="text-xs text-slate-500 mb-4">
                            Sistem usul perbaikan berkelanjutan operator lantai pabrik dengan akumulasi reward point.
                        </p>
                        <div class="space-y-2 text-xs">
                            <div class="p-2.5 rounded-lg bg-blue-50/60 border border-blue-100">
                                <div class="font-semibold text-blue-900">Optimalisasi Jig Filling Line 2</div>
                                <div class="text-slate-500 text-[11px] mt-0.5">Oleh: Joko (Prod) • +25 Poin</div>
                            </div>
                            <div class="p-2.5 rounded-lg bg-blue-50/60 border border-blue-100">
                                <div class="font-semibold text-blue-900">Reduksi Waste Botol Kaca 100ml</div>
                                <div class="text-slate-500 text-[11px] mt-0.5">Oleh: Siti (QC) • +30 Poin</div>
                            </div>
                        </div>
                    </div>

                    <div class="pt-4 border-t border-slate-100 text-xs">
                        <Link href="/" class="text-blue-600 font-semibold hover:underline flex items-center justify-between">
                            <span>Kembali ke App Launcher</span>
                            <ArrowRight class="w-4 h-4" />
                        </Link>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
