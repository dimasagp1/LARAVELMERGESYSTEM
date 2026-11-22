<script setup>
import { Head, Link } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import { useDeveloperModeStore } from '@/Stores/developerModeStore';
import {
    Landmark,
    Building2,
    Users,
    ShoppingCart,
    Factory,
    ShieldCheck,
    FlaskConical,
    Truck,
    Folder,
    ArrowUpRight,
    CheckCircle2,
    Layers,
    Cpu,
    GitBranch,
    Shield,
    Sparkles,
} from 'lucide-vue-next';

const props = defineProps({
    modules: Array,
    stats: Object,
});

const devStore = useDeveloperModeStore();

const iconMap = {
    Landmark,
    Building2,
    Users,
    ShoppingCart,
    Factory,
    ShieldCheck,
    FlaskConical,
    Truck,
};

function getIcon(iconName) {
    return iconMap[iconName] || Folder;
}

function getCardTheme(theme) {
    const map = {
        emerald: {
            border: 'hover:border-emerald-500',
            iconBg: 'bg-emerald-50 text-emerald-600 group-hover:bg-emerald-600 group-hover:text-white',
            badge: 'bg-emerald-100/70 text-emerald-800',
        },
        amber: {
            border: 'hover:border-amber-500',
            iconBg: 'bg-amber-50 text-amber-600 group-hover:bg-amber-600 group-hover:text-white',
            badge: 'bg-amber-100/70 text-amber-800',
        },
        blue: {
            border: 'hover:border-blue-500',
            iconBg: 'bg-blue-50 text-blue-600 group-hover:bg-blue-600 group-hover:text-white',
            badge: 'bg-blue-100/70 text-blue-800',
        },
        violet: {
            border: 'hover:border-violet-500',
            iconBg: 'bg-violet-50 text-violet-600 group-hover:bg-violet-600 group-hover:text-white',
            badge: 'bg-violet-100/70 text-violet-800',
        },
        rose: {
            border: 'hover:border-rose-500',
            iconBg: 'bg-rose-50 text-rose-600 group-hover:bg-rose-600 group-hover:text-white',
            badge: 'bg-rose-100/70 text-rose-800',
        },
        teal: {
            border: 'hover:border-teal-500',
            iconBg: 'bg-teal-50 text-teal-600 group-hover:bg-teal-600 group-hover:text-white',
            badge: 'bg-teal-100/70 text-teal-800',
        },
        cyan: {
            border: 'hover:border-cyan-500',
            iconBg: 'bg-cyan-50 text-cyan-600 group-hover:bg-cyan-600 group-hover:text-white',
            badge: 'bg-cyan-100/70 text-cyan-800',
        },
        indigo: {
            border: 'hover:border-indigo-500',
            iconBg: 'bg-indigo-50 text-indigo-600 group-hover:bg-indigo-600 group-hover:text-white',
            badge: 'bg-indigo-100/70 text-indigo-800',
        },
    };
    return map[theme] || {
        border: 'hover:border-slate-400',
        iconBg: 'bg-slate-100 text-slate-700 group-hover:bg-slate-700 group-hover:text-white',
        badge: 'bg-slate-100 text-slate-800',
    };
}
</script>

<template>
    <AppLayout>
        <Head title="Executive Overview" />

        <div class="space-y-6">
            <!-- Hero Banner -->
            <div class="relative overflow-hidden rounded-2xl bg-gradient-to-r from-emerald-900 via-teal-900 to-slate-900 p-6 sm:p-8 text-white shadow-xl">
                <div class="relative z-10 max-w-3xl">
                    <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-emerald-500/20 border border-emerald-400/30 text-emerald-300 text-xs font-medium mb-3 backdrop-blur-xs">
                        <Sparkles class="w-3.5 h-3.5" />
                        <span>Arsitektur Modular Monolith (Laravel 12 + Inertia.js v2 + Vue 3)</span>
                    </div>
                    <h1 class="text-2xl sm:text-3xl font-extrabold tracking-tight">
                        HerbaTech Unified Modular ERP
                    </h1>
                    <p class="mt-2 text-sm text-slate-300 leading-relaxed">
                        Platform ERP enterprise terpadu manufaktur ekstrak herbal dan farmasi (CPOB/CPOTB). Menyatukan 8 domain bisnis inti di atas <em>Single Source of Truth</em> dengan integrasi lintas-modul real-time dan ekstensi no-code.
                    </p>

                    <div class="mt-5 flex flex-wrap items-center gap-4 text-xs text-slate-300">
                        <div class="flex items-center gap-1.5">
                            <CheckCircle2 class="w-4 h-4 text-emerald-400" />
                            <span>Zero-Silo Data Kernel</span>
                        </div>
                        <div class="flex items-center gap-1.5">
                            <CheckCircle2 class="w-4 h-4 text-emerald-400" />
                            <span>Dual-Control Maker-Checker</span>
                        </div>
                        <div class="flex items-center gap-1.5">
                            <CheckCircle2 class="w-4 h-4 text-emerald-400" />
                            <span>Real-Time Budget Gatekeeper</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Core Kernel KPI Cards -->
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
                <div class="bg-white p-4 rounded-xl border border-slate-200 shadow-xs">
                    <div class="flex items-center justify-between text-slate-400 mb-1">
                        <span class="text-xs font-medium uppercase tracking-wider">Default Modules</span>
                        <Layers class="w-4 h-4 text-emerald-600" />
                    </div>
                    <div class="text-2xl font-bold text-slate-900">{{ stats?.total_modules ?? 8 }}</div>
                    <span class="text-[11px] text-emerald-600 font-medium">100% Active & Loaded</span>
                </div>

                <div class="bg-white p-4 rounded-xl border border-slate-200 shadow-xs">
                    <div class="flex items-center justify-between text-slate-400 mb-1">
                        <span class="text-xs font-medium uppercase tracking-wider">Multi-Plant Facilities</span>
                        <Factory class="w-4 h-4 text-blue-600" />
                    </div>
                    <div class="text-2xl font-bold text-slate-900">{{ stats?.active_plants ?? 2 }}</div>
                    <span class="text-[11px] text-slate-500 font-medium">Sukoharjo & Boyolali</span>
                </div>

                <div class="bg-white p-4 rounded-xl border border-slate-200 shadow-xs">
                    <div class="flex items-center justify-between text-slate-400 mb-1">
                        <span class="text-xs font-medium uppercase tracking-wider">Core Departments</span>
                        <Building2 class="w-4 h-4 text-amber-600" />
                    </div>
                    <div class="text-2xl font-bold text-slate-900">{{ stats?.total_departments ?? 9 }}</div>
                    <span class="text-[11px] text-slate-500 font-medium">Single Org Structure</span>
                </div>

                <div class="bg-white p-4 rounded-xl border border-slate-200 shadow-xs">
                    <div class="flex items-center justify-between text-slate-400 mb-1">
                        <span class="text-xs font-medium uppercase tracking-wider">Unified Product Hub</span>
                        <Cpu class="w-4 h-4 text-violet-600" />
                    </div>
                    <div class="text-2xl font-bold text-slate-900">{{ stats?.total_products ?? 0 }}</div>
                    <span class="text-[11px] text-slate-500 font-medium">FG / RM / PM / WIP / ATK</span>
                </div>
            </div>

            <!-- 8 Business Modules Section -->
            <div>
                <div class="flex items-center justify-between mb-4">
                    <div>
                        <h2 class="text-lg font-bold text-slate-900">8 Modul Bisnis Default</h2>
                        <p class="text-xs text-slate-500">Klik modul untuk mengakses dashboard operasional dan indikator KPI</p>
                    </div>
                    <div class="flex items-center gap-2">
                        <span class="text-xs font-mono px-2 py-1 bg-slate-100 rounded text-slate-600">
                            app/Modules/*
                        </span>
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                    <Link
                        v-for="module in modules"
                        :key="module.id"
                        :href="module.route_prefix"
                        :class="[
                            'group relative flex flex-col p-5 bg-white rounded-xl border border-slate-200 shadow-xs transition duration-200',
                            getCardTheme(module.color_theme).border,
                        ]"
                    >
                        <div class="flex items-start justify-between mb-3">
                            <div
                                :class="[
                                    'w-12 h-12 rounded-xl flex items-center justify-center transition duration-200',
                                    getCardTheme(module.color_theme).iconBg,
                                ]"
                            >
                                <component :is="getIcon(module.icon)" class="w-6 h-6" />
                            </div>

                            <span class="inline-flex items-center text-slate-400 group-hover:text-slate-700 transition">
                                <ArrowUpRight class="w-5 h-5 group-hover:translate-x-0.5 group-hover:-translate-y-0.5 transition-transform" />
                            </span>
                        </div>

                        <div class="mb-2">
                            <div class="flex items-center gap-2 mb-1">
                                <h3 class="font-bold text-slate-900 text-sm group-hover:text-emerald-700 transition">
                                    {{ module.name }}
                                </h3>
                            </div>
                            <span :class="['text-[10px] font-mono px-2 py-0.5 rounded font-medium', getCardTheme(module.color_theme).badge]">
                                {{ module.route_prefix }}
                            </span>
                        </div>

                        <p class="text-xs text-slate-500 line-clamp-3 leading-relaxed mt-1">
                            {{ module.description }}
                        </p>

                        <div class="mt-4 pt-3 border-t border-slate-100 flex items-center justify-between text-[11px] text-slate-400">
                            <span>Status: <strong class="text-emerald-600 font-semibold">Active</strong></span>
                            <span class="font-mono">#{{ module.sort_order }}</span>
                        </div>
                    </Link>
                </div>
            </div>

            <!-- PRD Section 5: Matrix Architecture Overview -->
            <div class="bg-white rounded-xl border border-slate-200 p-6 shadow-xs">
                <div class="flex items-center gap-2.5 mb-3">
                    <GitBranch class="w-5 h-5 text-emerald-600" />
                    <h3 class="font-bold text-slate-900 text-sm">Arsitektur Skenario Terpadu Lintas-Modul (PRD Section 5)</h3>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mt-4 text-xs">
                    <div class="p-3.5 rounded-lg bg-slate-50 border border-slate-200/80">
                        <div class="font-bold text-slate-800 mb-1">Skenario 1: NPD to Market</div>
                        <p class="text-slate-500 leading-relaxed">
                            [R&D] Formula 100% Valid & NIE BPOM &rarr; Auto-Create BOM [SCM] &rarr; Update Price Catalog [Procurement] &rarr; SOP Mutu [QMS] &rarr; Sales Attribution 365 Hari [Finance].
                        </p>
                    </div>

                    <div class="p-3.5 rounded-lg bg-slate-50 border border-slate-200/80">
                        <div class="font-bold text-slate-800 mb-1">Skenario 2: Quality Gate Inflow</div>
                        <p class="text-slate-500 leading-relaxed">
                            [Procurement] PO Approved &rarr; [SCM] GR Gudang Karantina &rarr; [QMS] QC Sample & Release &rarr; [Finance] 3-Way Matching PO vs GR vs Invoice.
                        </p>
                    </div>

                    <div class="p-3.5 rounded-lg bg-slate-50 border border-slate-200/80">
                        <div class="font-bold text-slate-800 mb-1">Skenario 3: Eksekusi Batch & HPP</div>
                        <p class="text-slate-500 leading-relaxed">
                            [Produksi] Active Batch Timer (Mixing-Filling-Packing) &rarr; [HRIS] Jam Kerja TKL &rarr; [QMS] In-Process QC &rarr; [Finance] HPP Riil Batch.
                        </p>
                    </div>

                    <div class="p-3.5 rounded-lg bg-slate-50 border border-slate-200/80">
                        <div class="font-bold text-slate-800 mb-1">Skenario 4: Facility Maintenance</div>
                        <p class="text-slate-500 leading-relaxed">
                            [GA] Work Order Mesin Kritis &rarr; [Procurement] PR Sparepart &rarr; [Finance] Gatekeeper & Aset Depresiasi &rarr; [QMS] Change Request CRA jika CPOB.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
