<script setup>
import { Head, Link } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import {
    ShieldCheck,
    FileCheck,
    AlertOctagon,
    GitCommit,
    CheckCircle2,
    ArrowRight,
    FileSignature,
} from 'lucide-vue-next';

const props = defineProps({
    module: Object,
    kpis: Object,
});

const chartOptions = {
    chart: {
        type: 'bar',
        height: 280,
        stacked: true,
        toolbar: { show: false },
        fontFamily: 'inherit',
    },
    colors: ['#0d9488', '#f59e0b', '#ef4444'],
    plotOptions: {
        bar: { horizontal: false, borderRadius: 4, columnWidth: '50%' },
    },
    xaxis: {
        categories: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun'],
    },
    tooltip: { theme: 'light' },
};

const chartSeries = [
    { name: 'Closed CAPA', data: [12, 15, 18, 14, 20, 16] },
    { name: 'In Progress', data: [2, 3, 1, 4, 2, 2] },
    { name: 'Overdue', data: [0, 0, 0, 0, 0, 0] },
];
</script>

<template>
    <AppLayout>
        <Head :title="module?.name" />

        <div class="space-y-6">
            <!-- Header -->
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 pb-4 border-b border-slate-200">
                <div class="flex items-center gap-3">
                    <div class="w-12 h-12 rounded-xl bg-teal-50 text-teal-600 flex items-center justify-center border border-teal-200">
                        <ShieldCheck class="w-6 h-6" />
                    </div>
                    <div>
                        <div class="flex items-center gap-2">
                            <h1 class="text-xl font-bold text-slate-900">{{ module?.name }}</h1>
                            <span class="text-xs px-2 py-0.5 rounded bg-teal-100 text-teal-800 font-medium">Modul 6</span>
                        </div>
                        <p class="text-xs text-slate-500 mt-0.5">{{ module?.description }}</p>
                    </div>
                </div>

                <div class="flex items-center gap-2 text-xs">
                    <button class="px-3 py-1.5 rounded-lg bg-teal-600 text-white font-medium hover:bg-teal-700 transition">
                        + Laporkan Deviasi (DR)
                    </button>
                    <button class="px-3 py-1.5 rounded-lg border border-slate-300 text-slate-700 font-medium hover:bg-slate-50 transition">
                        Ajukan Change Request
                    </button>
                </div>
            </div>

            <!-- KPI Cards -->
            <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
                <div class="bg-white p-4 rounded-xl border border-slate-200 shadow-xs">
                    <div class="text-xs text-slate-500 font-medium">Dokumen Mutu Aktif</div>
                    <div class="text-xl font-bold text-slate-900 mt-1">{{ kpis?.active_qms_docs }}</div>
                    <div class="text-[11px] text-teal-600 font-medium mt-1">Piramida CPOB 4-Tingkat</div>
                </div>

                <div class="bg-white p-4 rounded-xl border border-slate-200 shadow-xs">
                    <div class="text-xs text-slate-500 font-medium">Open Change Requests</div>
                    <div class="text-xl font-bold text-slate-900 mt-1">{{ kpis?.open_change_requests }}</div>
                    <div class="text-[11px] text-amber-600 font-medium mt-1">1 CRA Major • 2 CRB Minor</div>
                </div>

                <div class="bg-white p-4 rounded-xl border border-slate-200 shadow-xs">
                    <div class="text-xs text-slate-500 font-medium">Open Deviation Reports</div>
                    <div class="text-xl font-bold text-slate-900 mt-1">{{ kpis?.open_deviations }}</div>
                    <div class="text-[11px] text-slate-500 font-medium mt-1">Investigasi Fishbone 6M</div>
                </div>

                <div class="bg-white p-4 rounded-xl border border-slate-200 shadow-xs">
                    <div class="text-xs text-slate-500 font-medium">Overdue CAPA Actions</div>
                    <div class="text-xl font-bold text-slate-900 mt-1">{{ kpis?.overdue_capa }}</div>
                    <div class="text-[11px] text-emerald-600 font-medium mt-1">100% Kepatuhan Tenggat</div>
                </div>
            </div>

            <!-- Chart & Widgets -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <div class="lg:col-span-2 bg-white p-5 rounded-xl border border-slate-200 shadow-xs">
                    <div class="flex items-center justify-between mb-4">
                        <div>
                            <h3 class="font-bold text-slate-900 text-sm">Status Penutupan Tindakan Perbaikan (CAPA)</h3>
                            <p class="text-xs text-slate-500">Kepatuhan Target & Verifikasi Efektivitas QA</p>
                        </div>
                    </div>
                    <apexchart type="bar" height="280" :options="chartOptions" :series="chartSeries" />
                </div>

                <div class="bg-white p-5 rounded-xl border border-slate-200 shadow-xs flex flex-col justify-between">
                    <div>
                        <div class="flex items-center gap-2 mb-3">
                            <FileSignature class="w-4 h-4 text-teal-600" />
                            <h3 class="font-bold text-slate-900 text-sm">21 CFR Part 11 Digital Signatures</h3>
                        </div>
                        <p class="text-xs text-slate-500 mb-4">
                            Dual-Control Signature (Maker-Checker) dengan enkripsi snapshot & unalterable audit trail.
                        </p>
                        <div class="space-y-2 text-xs">
                            <div class="p-2.5 rounded-lg bg-teal-50/60 border border-teal-100 flex items-center justify-between">
                                <div>
                                    <div class="font-semibold text-teal-900">Release Bahan Baku #RM-091</div>
                                    <div class="text-slate-500 text-[11px]">Signed: QA Supervisor & QA Head</div>
                                </div>
                                <span class="text-emerald-700 font-bold">RELEASED</span>
                            </div>
                            <div class="p-2.5 rounded-lg bg-teal-50/60 border border-teal-100 flex items-center justify-between">
                                <div>
                                    <div class="font-semibold text-teal-900">Change Request CRA-2026-03</div>
                                    <div class="text-slate-500 text-[11px]">FMEA RPN Score: 72 (Low Risk)</div>
                                </div>
                                <span class="text-blue-700 font-bold">APPROVED</span>
                            </div>
                        </div>
                    </div>

                    <div class="pt-4 border-t border-slate-100 text-xs">
                        <Link href="/" class="text-teal-600 font-semibold hover:underline flex items-center justify-between">
                            <span>Kembali ke App Launcher</span>
                            <ArrowRight class="w-4 h-4" />
                        </Link>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
