<script setup>
import { ref } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import ModuleLayout from '@/Layouts/ModuleLayout.vue';
import InteractiveChartCard from '@/Components/Dashboard/InteractiveChartCard.vue';
import {
    BarChart3,
    Download,
    TrendingUp,
    TrendingDown,
    Building2,
    Calendar,
    ArrowUpRight,
    ArrowDownRight,
    AlertTriangle,
    CheckCircle2
} from 'lucide-vue-next';

const props = defineProps({
    module: Object,
    budgetData: Object,
    periods: Array,
    selectedPeriodId: Number,
});

const currentPeriod = ref(props.selectedPeriodId || props.periods?.[0]?.id || 1);
const activeView = ref('summary'); // 'summary' | 'departments' | 'trend'

const changePeriod = () => {
    router.get('/finance/budget-pnl', {
        period_id: currentPeriod.value,
    }, { preserveState: true });
};

const formatCurrency = (val) => {
    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        maximumFractionDigits: 0,
    }).format(val || 0);
};

const formatPct = (val) => {
    return (val > 0 ? '+' : '') + (val || 0).toFixed(1) + '%';
};
</script>

<template>
    <Head title="Budget vs Actual P&L Monitoring" />

    <ModuleLayout :module="module">
        <div class="space-y-6">
            <!-- Header & Period Filter -->
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div>
                    <h1 class="text-2xl font-bold tracking-tight text-slate-900 dark:text-white flex items-center gap-2">
                        <BarChart3 class="w-6 h-6 text-emerald-500" />
                        <span>Monitoring Anggaran vs Realisasi (Budget vs Actual P&L)</span>
                    </h1>
                    <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">
                        Komparasi Target Rencana Pendapatan, HPP Standar, dan Pagu OPEX 11 Departemen terhadap Realisasi GL Aktual.
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

                    <a
                        :href="`/finance/budget-pnl/pdf?period_id=${currentPeriod}`"
                        class="inline-flex items-center gap-1.5 px-3.5 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-semibold rounded-xl shadow-sm transition-colors"
                    >
                        <Download class="w-4 h-4" />
                        <span>Ekspor PDF Komparatif</span>
                    </a>
                </div>
            </div>

            <!-- View Toggles -->
            <div class="border-b border-slate-200 dark:border-slate-800 flex gap-2">
                <button
                    @click="activeView = 'summary'"
                    :class="[
                        'pb-3 px-3 text-xs sm:text-sm font-semibold flex items-center gap-2 border-b-2 transition-all',
                        activeView === 'summary'
                            ? 'border-emerald-500 text-emerald-600 dark:text-emerald-400'
                            : 'border-transparent text-slate-500 hover:text-slate-800 dark:hover:text-slate-200'
                    ]"
                >
                    <TrendingUp class="w-4 h-4" />
                    <span>View 1: Ringkasan Laba Rugi Eksekutif</span>
                </button>
                <button
                    @click="activeView = 'departments'"
                    :class="[
                        'pb-3 px-3 text-xs sm:text-sm font-semibold flex items-center gap-2 border-b-2 transition-all',
                        activeView === 'departments'
                            ? 'border-emerald-500 text-emerald-600 dark:text-emerald-400'
                            : 'border-transparent text-slate-500 hover:text-slate-800 dark:hover:text-slate-200'
                    ]"
                >
                    <Building2 class="w-4 h-4" />
                    <span>View 2: Breakdown OPEX 11 Departemen</span>
                </button>
                <button
                    @click="activeView = 'trend'"
                    :class="[
                        'pb-3 px-3 text-xs sm:text-sm font-semibold flex items-center gap-2 border-b-2 transition-all',
                        activeView === 'trend'
                            ? 'border-emerald-500 text-emerald-600 dark:text-emerald-400'
                            : 'border-transparent text-slate-500 hover:text-slate-800 dark:hover:text-slate-200'
                    ]"
                >
                    <Calendar class="w-4 h-4" />
                    <span>View 3: Grafik Tren Run-Rate</span>
                </button>
            </div>

            <!-- VIEW 1: Summary Comparative P&L -->
            <div v-show="activeView === 'summary'" class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl shadow-sm overflow-hidden">
                <div class="p-4 border-b border-slate-200 dark:border-slate-800">
                    <h3 class="font-bold text-slate-900 dark:text-white text-sm">
                        Ikhtisar Komparatif Laba Rugi: Target Anggaran vs Realisasi Aktual
                    </h3>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs">
                        <thead class="bg-slate-50 dark:bg-slate-800/60 font-semibold text-slate-500 uppercase border-b border-slate-200 dark:border-slate-800">
                            <tr>
                                <th class="p-3 w-64">Pos Finansial</th>
                                <th class="p-3 text-right w-44">Target Anggaran (Budget)</th>
                                <th class="p-3 text-right w-44">Realisasi Aktual (Actual)</th>
                                <th class="p-3 text-right w-36">Varians (Rp)</th>
                                <th class="p-3 text-right w-28">Varians (%)</th>
                                <th class="p-3 text-center w-36">Status Kinerja</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-200 dark:divide-slate-800 font-mono">
                            <!-- Revenue -->
                            <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/40">
                                <td class="p-3 font-sans font-bold text-slate-900 dark:text-white">1. Pendapatan Penjualan</td>
                                <td class="p-3 text-right">{{ formatCurrency(budgetData.pnl_summary?.revenue?.budget) }}</td>
                                <td class="p-3 text-right font-bold">{{ formatCurrency(budgetData.pnl_summary?.revenue?.actual) }}</td>
                                <td class="p-3 text-right" :class="budgetData.pnl_summary?.revenue?.variance_rp >= 0 ? 'text-emerald-600' : 'text-rose-600'">
                                    {{ formatCurrency(budgetData.pnl_summary?.revenue?.variance_rp) }}
                                </td>
                                <td class="p-3 text-right font-bold" :class="budgetData.pnl_summary?.revenue?.is_favorable ? 'text-emerald-600' : 'text-rose-600'">
                                    {{ formatPct(budgetData.pnl_summary?.revenue?.variance_pct) }}
                                </td>
                                <td class="p-3 text-center">
                                    <span :class="['px-2 py-0.5 rounded text-[11px] font-bold uppercase', budgetData.pnl_summary?.revenue?.is_favorable ? 'bg-emerald-100 text-emerald-800 dark:bg-emerald-900/40 dark:text-emerald-300' : 'bg-rose-100 text-rose-800']">
                                        {{ budgetData.pnl_summary?.revenue?.is_favorable ? 'FAVORABLE (MELEBIHI TARGET)' : 'UNFAVORABLE' }}
                                    </span>
                                </td>
                            </tr>

                            <!-- COGS -->
                            <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/40">
                                <td class="p-3 font-sans text-slate-700 dark:text-slate-300">2. Beban Pokok Penjualan (COGS)</td>
                                <td class="p-3 text-right text-slate-500">({{ formatCurrency(budgetData.pnl_summary?.cogs?.budget) }})</td>
                                <td class="p-3 text-right text-rose-600">({{ formatCurrency(budgetData.pnl_summary?.cogs?.actual) }})</td>
                                <td class="p-3 text-right">{{ formatCurrency(budgetData.pnl_summary?.cogs?.variance_rp) }}</td>
                                <td class="p-3 text-right font-bold" :class="budgetData.pnl_summary?.cogs?.is_favorable ? 'text-emerald-600' : 'text-amber-600'">
                                    {{ formatPct(budgetData.pnl_summary?.cogs?.variance_pct) }}
                                </td>
                                <td class="p-3 text-center">
                                    <span :class="['px-2 py-0.5 rounded text-[11px] font-bold uppercase', budgetData.pnl_summary?.cogs?.is_favorable ? 'bg-emerald-100 text-emerald-800 dark:bg-emerald-900/40 dark:text-emerald-300' : 'bg-amber-100 text-amber-800']">
                                        {{ budgetData.pnl_summary?.cogs?.is_favorable ? 'EFISIEN' : 'BIAYA LEBIH TINGGI' }}
                                    </span>
                                </td>
                            </tr>

                            <!-- Gross Profit -->
                            <tr class="bg-slate-50 dark:bg-slate-800/50 font-bold">
                                <td class="p-3 font-sans text-slate-900 dark:text-white">3. Laba Kotor (Gross Profit)</td>
                                <td class="p-3 text-right">{{ formatCurrency(budgetData.pnl_summary?.gross_profit?.budget) }}</td>
                                <td class="p-3 text-right text-emerald-600">{{ formatCurrency(budgetData.pnl_summary?.gross_profit?.actual) }}</td>
                                <td class="p-3 text-right" :class="budgetData.pnl_summary?.gross_profit?.variance_rp >= 0 ? 'text-emerald-600' : 'text-rose-600'">
                                    {{ formatCurrency(budgetData.pnl_summary?.gross_profit?.variance_rp) }}
                                </td>
                                <td class="p-3 text-right" :class="budgetData.pnl_summary?.gross_profit?.is_favorable ? 'text-emerald-600' : 'text-rose-600'">
                                    {{ formatPct(budgetData.pnl_summary?.gross_profit?.variance_pct) }}
                                </td>
                                <td class="p-3 text-center">
                                    <span :class="['px-2 py-0.5 rounded text-[11px] font-bold uppercase', budgetData.pnl_summary?.gross_profit?.is_favorable ? 'bg-emerald-100 text-emerald-800' : 'bg-rose-100 text-rose-800']">
                                        {{ budgetData.pnl_summary?.gross_profit?.is_favorable ? 'FAVORABLE' : 'UNFAVORABLE' }}
                                    </span>
                                </td>
                            </tr>

                            <!-- OPEX -->
                            <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/40">
                                <td class="p-3 font-sans text-slate-700 dark:text-slate-300">4. Beban Operasional 11 Dept (OPEX)</td>
                                <td class="p-3 text-right text-slate-500">({{ formatCurrency(budgetData.pnl_summary?.opex?.budget) }})</td>
                                <td class="p-3 text-right text-rose-600">({{ formatCurrency(budgetData.pnl_summary?.opex?.actual) }})</td>
                                <td class="p-3 text-right">{{ formatCurrency(budgetData.pnl_summary?.opex?.variance_rp) }}</td>
                                <td class="p-3 text-right font-bold" :class="budgetData.pnl_summary?.opex?.is_favorable ? 'text-emerald-600' : 'text-rose-600'">
                                    {{ formatPct(budgetData.pnl_summary?.opex?.variance_pct) }}
                                </td>
                                <td class="p-3 text-center">
                                    <span :class="['px-2 py-0.5 rounded text-[11px] font-bold uppercase', budgetData.pnl_summary?.opex?.is_favorable ? 'bg-emerald-100 text-emerald-800 dark:bg-emerald-900/40 dark:text-emerald-300' : 'bg-rose-100 text-rose-800']">
                                        {{ budgetData.pnl_summary?.opex?.is_favorable ? 'HEMAT (DI BAWAH PAGU)' : 'OVERSPENT' }}
                                    </span>
                                </td>
                            </tr>

                            <!-- Net Income -->
                            <tr class="bg-emerald-600 text-white font-bold text-sm">
                                <td class="p-3 font-sans">5. LABA BERSIH (NET INCOME)</td>
                                <td class="p-3 text-right">{{ formatCurrency(budgetData.pnl_summary?.net_income?.budget) }}</td>
                                <td class="p-3 text-right">{{ formatCurrency(budgetData.pnl_summary?.net_income?.actual) }}</td>
                                <td class="p-3 text-right">{{ formatCurrency(budgetData.pnl_summary?.net_income?.variance_rp) }}</td>
                                <td class="p-3 text-right">{{ formatPct(budgetData.pnl_summary?.net_income?.variance_pct) }}</td>
                                <td class="p-3 text-center">
                                    <span class="px-2 py-0.5 rounded text-[11px] font-bold bg-white/20 text-white uppercase">
                                        {{ budgetData.pnl_summary?.net_income?.is_favorable ? 'TERCAPAI' : 'DI BAWAH TARGET' }}
                                    </span>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- VIEW 2: Department OPEX Breakdown -->
            <div v-show="activeView === 'departments'" class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl shadow-sm overflow-hidden">
                <div class="p-4 border-b border-slate-200 dark:border-slate-800 flex items-center justify-between">
                    <div>
                        <h3 class="font-bold text-slate-900 dark:text-white text-sm">
                            Pagu Anggaran & Realisasi Beban Operasional per 11 Departemen
                        </h3>
                        <p class="text-xs text-slate-500 mt-0.5">
                            Burn rate % dipantau secara real-time oleh Budget Gatekeeper Service untuk mencegah overspending pengadaan.
                        </p>
                    </div>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs">
                        <thead class="bg-slate-50 dark:bg-slate-800/60 font-semibold text-slate-500 uppercase border-b border-slate-200 dark:border-slate-800">
                            <tr>
                                <th class="p-3">Departemen</th>
                                <th class="p-3 text-right">Pagu Anggaran (Budget)</th>
                                <th class="p-3 text-right">Realisasi (Actual)</th>
                                <th class="p-3 text-center w-48">Penyerapan (Burn Rate %)</th>
                                <th class="p-3 text-right">Sisa Pagu / Varians (Rp)</th>
                                <th class="p-3 text-center">Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-200 dark:divide-slate-800">
                            <tr
                                v-for="dept in budgetData.department_opex"
                                :key="dept.department_id"
                                class="hover:bg-slate-50 dark:hover:bg-slate-800/40"
                            >
                                <td class="p-3 font-semibold text-slate-900 dark:text-white">
                                    {{ dept.department_name }}
                                    <span class="text-[10px] text-slate-400 font-mono block">{{ dept.department_code }}</span>
                                </td>
                                <td class="p-3 text-right font-mono">{{ formatCurrency(dept.budget) }}</td>
                                <td class="p-3 text-right font-mono font-bold text-slate-800 dark:text-slate-100">
                                    {{ formatCurrency(dept.actual) }}
                                </td>
                                <td class="p-3">
                                    <div class="flex items-center gap-2">
                                        <div class="w-full bg-slate-100 dark:bg-slate-800 rounded-full h-2 overflow-hidden">
                                            <div
                                                class="h-2 rounded-full transition-all"
                                                :class="dept.is_overbudget ? 'bg-rose-500' : (dept.burn_rate_pct > 80 ? 'bg-amber-500' : 'bg-emerald-500')"
                                                :style="{ width: `${Math.min(100, dept.burn_rate_pct)}%` }"
                                            ></div>
                                        </div>
                                        <span class="font-mono text-xs font-bold w-12 text-right" :class="dept.is_overbudget ? 'text-rose-600' : 'text-slate-700 dark:text-slate-300'">
                                            {{ dept.burn_rate_pct }}%
                                        </span>
                                    </div>
                                </td>
                                <td class="p-3 text-right font-mono" :class="dept.variance_rp > 0 ? 'text-rose-600 font-bold' : 'text-emerald-600'">
                                    {{ formatCurrency(dept.budget - dept.actual) }}
                                </td>
                                <td class="p-3 text-center">
                                    <span
                                        v-if="dept.is_overbudget"
                                        class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-[10px] font-bold bg-rose-100 text-rose-800 dark:bg-rose-900/40 dark:text-rose-300"
                                    >
                                        <AlertTriangle class="w-3 h-3" />
                                        <span>OVERBUDGET</span>
                                    </span>
                                    <span
                                        v-else
                                        class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-[10px] font-bold bg-emerald-100 text-emerald-800 dark:bg-emerald-900/40 dark:text-emerald-300"
                                    >
                                        <CheckCircle2 class="w-3 h-3" />
                                        <span>AMAN</span>
                                    </span>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- VIEW 3: Run-Rate Trend Chart -->
            <div v-show="activeView === 'trend'" class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl p-5 shadow-sm">
                <InteractiveChartCard
                    title="Tren Run-Rate Finansial (Pendapatan vs Laba Bersih MTD/YTD)"
                    type="line"
                    :series="budgetData.run_rate_trend?.series || []"
                    :categories="budgetData.run_rate_trend?.categories || []"
                    :colors="['#059669', '#0284c7']"
                    height="360"
                />
            </div>
        </div>
    </ModuleLayout>
</template>
