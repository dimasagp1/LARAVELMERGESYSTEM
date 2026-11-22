<script setup>
import { ref } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import ModuleLayout from '@/Layouts/ModuleLayout.vue';
import {
    CheckCheck,
    Play,
    CheckCircle2,
    AlertCircle,
    RotateCcw,
    ShieldCheck,
    Scale,
    FileSpreadsheet,
    HelpCircle,
    ArrowUpRight
} from 'lucide-vue-next';

const props = defineProps({
    module: Object,
    auditData: Object,
    periods: Array,
    selectedPeriodId: Number,
});

const currentPeriod = ref(props.selectedPeriodId || props.periods?.[0]?.id || 1);
const runningAudit = ref(false);

const changePeriod = () => {
    router.get('/finance/reconciliation', {
        period_id: currentPeriod.value,
    }, { preserveState: true });
};

const triggerAudit = () => {
    runningAudit.value = true;
    router.post('/finance/reconciliation/run', {
        period_id: currentPeriod.value,
    }, {
        preserveScroll: true,
        onFinish: () => {
            runningAudit.value = false;
        },
    });
};

const formatCurrency = (val) => {
    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        maximumFractionDigits: 2,
    }).format(val || 0);
};
</script>

<template>
    <Head title="Audit Rekonsiliasi Otomatis (R1-R9)" />

    <ModuleLayout :module="module">
        <div class="space-y-6">
            <!-- Header & Action -->
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div>
                    <h1 class="text-2xl font-bold tracking-tight text-slate-900 dark:text-white flex items-center gap-2">
                        <CheckCheck class="w-6 h-6 text-emerald-500" />
                        <span>Sistem Audit & Rekonsiliasi Otomatis 9-Titik (R1 - R9)</span>
                    </h1>
                    <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">
                        Memvalidasi keselarasan matematis tanpa celah antara GL Mirror Odoo, Kertas Kerja Trial Balance, Neraca, Laba Rugi, dan Arus Kas.
                    </p>
                </div>

                <div class="flex items-center gap-3">
                    <select
                        v-model="currentPeriod"
                        @change="changePeriod"
                        class="text-xs font-semibold rounded-xl border border-slate-300 dark:border-slate-700 dark:bg-slate-800 text-slate-800 dark:text-slate-200 py-2.5 px-3"
                    >
                        <option v-for="p in periods" :key="p.id" :value="p.id">
                            Periode: {{ p.period_name }}
                        </option>
                    </select>

                    <button
                        type="button"
                        :disabled="runningAudit"
                        @click="triggerAudit"
                        class="inline-flex items-center gap-2 px-4 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-semibold rounded-xl shadow-md transition-all disabled:opacity-50"
                    >
                        <Play class="w-4 h-4 fill-current" />
                        <span>{{ runningAudit ? 'Menjalankan Audit...' : 'Jalankan Audit Otomatis' }}</span>
                    </button>
                </div>
            </div>

            <!-- Overall Status Banner -->
            <div
                :class="[
                    'p-6 rounded-2xl border shadow-lg flex flex-col md:flex-row md:items-center justify-between gap-6 transition-all',
                    auditData?.all_passed
                        ? 'bg-gradient-to-r from-emerald-950 via-teal-950 to-slate-900 text-white border-emerald-700/50'
                        : 'bg-gradient-to-r from-amber-950 via-rose-950 to-slate-900 text-white border-amber-700/50'
                ]"
            >
                <div class="flex items-center gap-4">
                    <div
                        :class="[
                            'w-14 h-14 rounded-2xl flex items-center justify-center shrink-0 shadow-inner',
                            auditData?.all_passed ? 'bg-emerald-500 text-white' : 'bg-amber-500 text-white'
                        ]"
                    >
                        <ShieldCheck v-if="auditData?.all_passed" class="w-8 h-8" />
                        <AlertCircle v-else class="w-8 h-8" />
                    </div>
                    <div>
                        <div class="flex items-center gap-2">
                            <span class="text-xs uppercase font-bold tracking-wider px-2.5 py-0.5 rounded-full" :class="auditData?.all_passed ? 'bg-emerald-500/20 text-emerald-300 border border-emerald-500/30' : 'bg-amber-500/20 text-amber-300 border border-amber-500/30'">
                                {{ auditData?.all_passed ? '100% Selaras (Zero Variance)' : 'Ada Varians Terdeteksi' }}
                            </span>
                        </div>
                        <h2 class="text-xl font-bold mt-1">
                            {{ auditData?.passed_count || 0 }} dari {{ auditData?.total_rules || 9 }} Titik Rekonsiliasi Terverifikasi Lolos
                        </h2>
                        <p class="text-xs text-slate-300 mt-0.5 max-w-xl">
                            Seluruh laporan keuangan terbukti matematis konsisten dengan jurnal umum Odoo dan penyesuaian AJE.
                        </p>
                    </div>
                </div>

                <div class="flex items-center gap-2 self-start md:self-auto">
                    <Link
                        href="/finance/trial-balance"
                        class="px-3.5 py-2 rounded-xl bg-white/10 hover:bg-white/20 text-xs font-semibold backdrop-blur-sm border border-white/10 transition-colors"
                    >
                        Buka Kertas Kerja 4-Kolom
                    </Link>
                </div>
            </div>

            <!-- Rules Detail Cards -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div
                    v-for="rule in auditData?.rules"
                    :key="rule.code"
                    class="bg-white dark:bg-slate-900 border rounded-xl p-5 shadow-sm space-y-3 transition-colors"
                    :class="rule.is_passed ? 'border-slate-200 dark:border-slate-800' : 'border-rose-300 dark:border-rose-800 bg-rose-50/20'"
                >
                    <div class="flex items-center justify-between">
                        <span class="font-bold font-mono text-xs px-2 py-0.5 rounded bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300">
                            {{ rule.code }}
                        </span>
                        <span
                            :class="[
                                'inline-flex items-center gap-1 text-[11px] font-bold px-2 py-0.5 rounded-full',
                                rule.is_passed
                                    ? 'bg-emerald-100 text-emerald-800 dark:bg-emerald-900/40 dark:text-emerald-300'
                                    : 'bg-rose-100 text-rose-800 dark:bg-rose-900/40 dark:text-rose-300'
                            ]"
                        >
                            <CheckCircle2 v-if="rule.is_passed" class="w-3.5 h-3.5" />
                            <AlertCircle v-else class="w-3.5 h-3.5" />
                            <span>{{ rule.is_passed ? 'PASSED' : 'FAILED' }}</span>
                        </span>
                    </div>

                    <div>
                        <h4 class="font-bold text-xs text-slate-900 dark:text-white">{{ rule.name }}</h4>
                        <p class="text-[11px] text-slate-500 mt-1 leading-relaxed">{{ rule.description }}</p>
                    </div>

                    <div class="bg-slate-50 dark:bg-slate-800/60 p-2.5 rounded-lg text-[11px] font-mono space-y-1">
                        <div class="flex justify-between text-slate-600 dark:text-slate-400">
                            <span>Nilai Teruji:</span>
                            <span class="font-bold text-slate-900 dark:text-white">{{ formatCurrency(rule.gl_value) }}</span>
                        </div>
                        <div class="flex justify-between text-slate-600 dark:text-slate-400">
                            <span>Nilai Target:</span>
                            <span class="font-bold text-slate-900 dark:text-white">{{ formatCurrency(rule.target_value) }}</span>
                        </div>
                        <div class="flex justify-between border-t border-slate-200 dark:border-slate-700 pt-1 font-bold">
                            <span>Varians / Selisih:</span>
                            <span :class="rule.variance == 0 ? 'text-emerald-600' : 'text-rose-600'">
                                {{ formatCurrency(rule.variance) }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </ModuleLayout>
</template>
