<script setup>
import { Head, Link } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import {
    FlaskConical,
    Sparkles,
    CheckCircle2,
    Layers,
    FileCheck2,
    ArrowRight,
    Calculator,
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
    colors: ['#0891b2'],
    plotOptions: {
        bar: { horizontal: true, borderRadius: 4, barHeight: '55%' },
    },
    xaxis: {
        categories: [
            '1. PRF & Proposal',
            '2. Preformulasi',
            '3. Formulasi 100%',
            '4. Trial Lab & V1-V3',
            '5. Uji Sensori',
            '6. Stabilitas Dipercepat',
            '7. Dossier BPOM',
            '8. Master BOM Transfer',
        ],
    },
    tooltip: { theme: 'light' },
};

const chartSeries = [
    {
        name: 'Tingkat Kelengkapan Proyek (%)',
        data: [100, 100, 95, 90, 85, 75, 60, 45],
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
                    <div class="w-12 h-12 rounded-xl bg-cyan-50 text-cyan-600 flex items-center justify-center border border-cyan-200">
                        <FlaskConical class="w-6 h-6" />
                    </div>
                    <div>
                        <div class="flex items-center gap-2">
                            <h1 class="text-xl font-bold text-slate-900">{{ module?.name }}</h1>
                            <span class="text-xs px-2 py-0.5 rounded bg-cyan-100 text-cyan-800 font-medium">Modul 7</span>
                        </div>
                        <p class="text-xs text-slate-500 mt-0.5">{{ module?.description }}</p>
                    </div>
                </div>

                <div class="flex items-center gap-2 text-xs">
                    <button class="px-3 py-1.5 rounded-lg bg-cyan-600 text-white font-medium hover:bg-cyan-700 transition">
                        + Buat Proyek NPD Baru
                    </button>
                    <button class="px-3 py-1.5 rounded-lg border border-slate-300 text-slate-700 font-medium hover:bg-slate-50 transition">
                        Simulasi HPP Formulasi
                    </button>
                </div>
            </div>

            <!-- KPI Cards -->
            <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
                <div class="bg-white p-4 rounded-xl border border-slate-200 shadow-xs">
                    <div class="text-xs text-slate-500 font-medium">Total Active NPD Projects</div>
                    <div class="text-xl font-bold text-slate-900 mt-1">{{ kpis?.active_npd_projects }}</div>
                    <div class="text-[11px] text-cyan-600 font-medium mt-1">3-Fase Siklus Pengembangan</div>
                </div>

                <div class="bg-white p-4 rounded-xl border border-slate-200 shadow-xs">
                    <div class="text-xs text-slate-500 font-medium">Pipeline Progress NPD</div>
                    <div class="text-xl font-bold text-slate-900 mt-1">{{ kpis?.pipeline_progress_pct }}%</div>
                    <div class="text-[11px] text-emerald-600 font-medium mt-1">Faster NPD Cycle Target</div>
                </div>

                <div class="bg-white p-4 rounded-xl border border-slate-200 shadow-xs">
                    <div class="text-xs text-slate-500 font-medium">Pending Formula Approvals</div>
                    <div class="text-xl font-bold text-slate-900 mt-1">{{ kpis?.pending_formula_approvals }}</div>
                    <div class="text-[11px] text-amber-600 font-medium mt-1">Validasi Presisi 100.00%</div>
                </div>

                <div class="bg-white p-4 rounded-xl border border-slate-200 shadow-xs">
                    <div class="text-xs text-slate-500 font-medium">NIE BPOM Terbit YTD</div>
                    <div class="text-xl font-bold text-slate-900 mt-1">{{ kpis?.nie_issued_ytd }} Produk</div>
                    <div class="text-[11px] text-emerald-600 font-medium mt-1">Dossier e-Registrasi Ready</div>
                </div>
            </div>

            <!-- Chart & Widgets -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <div class="lg:col-span-2 bg-white p-5 rounded-xl border border-slate-200 shadow-xs">
                    <div class="flex items-center justify-between mb-4">
                        <div>
                            <h3 class="font-bold text-slate-900 text-sm">Status Kelengkapan Pipeline Tahapan NPD</h3>
                            <p class="text-xs text-slate-500">Dari Ide Produk & Formulasi hingga Master BOM Komersial</p>
                        </div>
                    </div>
                    <apexchart type="bar" height="280" :options="chartOptions" :series="chartSeries" />
                </div>

                <div class="bg-white p-5 rounded-xl border border-slate-200 shadow-xs flex flex-col justify-between">
                    <div>
                        <div class="flex items-center gap-2 mb-3">
                            <Calculator class="w-4 h-4 text-cyan-600" />
                            <h3 class="font-bold text-slate-900 text-sm">Simulasi HPP & Uji Stabilitas</h3>
                        </div>
                        <p class="text-xs text-slate-500 mb-4">
                            Kalkulator instan HPP per unit berbasis katalog harga terkini dan validasi bobot 100%.
                        </p>
                        <div class="space-y-2 text-xs">
                            <div class="p-2.5 rounded-lg bg-cyan-50/60 border border-cyan-100 flex items-center justify-between">
                                <div>
                                    <div class="font-semibold text-cyan-900">Sirup Ekstrak Pegagan Plus</div>
                                    <div class="text-slate-500 text-[11px]">HPP Proyeksi: Rp 14.850 / botol</div>
                                </div>
                                <span class="text-emerald-700 font-bold">100.00%</span>
                            </div>
                            <div class="p-2.5 rounded-lg bg-cyan-50/60 border border-cyan-100 flex items-center justify-between">
                                <div>
                                    <div class="font-semibold text-cyan-900">Uji Stabilitas Dipercepat (40°C)</div>
                                    <div class="text-slate-500 text-[11px]">Bulan ke-3: Kadar Kurkuminoid Lulus</div>
                                </div>
                                <span class="text-blue-700 font-bold">PASS</span>
                            </div>
                        </div>
                    </div>

                    <div class="pt-4 border-t border-slate-100 text-xs">
                        <Link href="/" class="text-cyan-600 font-semibold hover:underline flex items-center justify-between">
                            <span>Kembali ke App Launcher</span>
                            <ArrowRight class="w-4 h-4" />
                        </Link>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
