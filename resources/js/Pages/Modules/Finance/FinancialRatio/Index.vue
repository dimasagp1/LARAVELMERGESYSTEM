<script setup>
import { ref } from 'vue';
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import ModuleLayout from '@/Layouts/ModuleLayout.vue';
import {
    Activity,
    Award,
    Settings2,
    CheckCircle2,
    AlertCircle,
    TrendingUp,
    Shield,
    Users,
    Clock,
    DollarSign,
    Sliders,
    X,
    Check,
    RefreshCw,
    Share2
} from 'lucide-vue-next';

const props = defineProps({
    module: Object,
    bscData: Object,
    configurations: Array,
    periods: Array,
    selectedPeriodId: Number,
});

const currentPeriod = ref(props.selectedPeriodId || props.periods?.[0]?.id || 1);
const activePillarTab = ref('all');
const editConfigModalOpen = ref(false);
const selectedConfig = ref(null);

const configForm = useForm({
    target_value: 0,
    weight: 0,
    is_active: true,
});

const changePeriod = () => {
    router.get('/finance/financial-ratios', {
        period_id: currentPeriod.value,
    }, { preserveState: true });
};

const syncHris = () => {
    if (confirm('Tarik data headcount riil (TKL/TKTL) dan jam kerja efektif dari modul HRIS ke periode ini?')) {
        router.post(`/finance/financial-ratios/${currentPeriod.value}/sync-hris`, {}, {
            preserveScroll: true,
        });
    }
};

const publishToHris = () => {
    if (confirm('Publikasikan variabel finansial periode ini ke Sasaran Mutu Level 3 di modul HRIS?')) {
        router.post(`/finance/financial-ratios/${currentPeriod.value}/publish-hris`, {}, {
            preserveScroll: true,
        });
    }
};

const openEditConfig = (config) => {
    selectedConfig.value = config;
    configForm.target_value = config.target_value;
    configForm.weight = config.weight;
    configForm.is_active = Boolean(config.is_active);
    editConfigModalOpen.value = true;
};

const saveConfig = () => {
    if (!selectedConfig.value) return;
    configForm.post(`/finance/financial-ratios/config/${selectedConfig.value.id}`, {
        preserveScroll: true,
        onSuccess: () => {
            editConfigModalOpen.value = false;
        },
    });
};

const getPillarColor = (pillar) => {
    switch (pillar) {
        case 'profitability': return 'emerald';
        case 'activity': return 'indigo';
        case 'productivity': return 'amber';
        case 'liquidity': return 'sky';
        case 'solvency': return 'teal';
        default: return 'slate';
    }
};

const formatRatioValue = (val, unit) => {
    if (unit === 'Hari') {
        return Math.round(val) + ' Hari';
    }
    if (unit === 'x' || unit === 'Kali') {
        return val.toFixed(2) + 'x';
    }
    if (unit === 'Rp') {
        return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', maximumFractionDigits: 0 }).format(val);
    }
    return val.toFixed(1) + ' ' + (unit || '%');
};
</script>

<template>
    <Head title="19 Rasio Balanced Scorecard (BSC)" />

    <ModuleLayout :module="module">
        <div class="space-y-6">
            <!-- Header & Period Filter -->
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div>
                    <h1 class="text-2xl font-bold tracking-tight text-slate-900 dark:text-white flex items-center gap-2">
                        <Activity class="w-6 h-6 text-emerald-500" />
                        <span>19 Rasio Finansial & Balanced Scorecard (BSC)</span>
                    </h1>
                    <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">
                        Monitoring kesehatan keuangan multi-dimensi (Profitabilitas 30%, Aktivitas 25%, Produktivitas 20%, Likuiditas 15%, Solvabilitas 10%).
                    </p>
                </div>

                <div class="flex flex-wrap items-center gap-2">
                    <select
                        v-model="currentPeriod"
                        @change="changePeriod"
                        class="text-xs font-semibold rounded-xl border border-slate-300 dark:border-slate-700 dark:bg-slate-800 text-slate-800 dark:text-slate-200 py-2.5 px-3"
                    >
                        <option v-for="p in periods" :key="p.id" :value="p.id">
                            Periode: {{ p.period_name }}
                        </option>
                    </select>

                    <!-- Cross-Module Sync Buttons -->
                    <button
                        type="button"
                        @click="syncHris"
                        class="px-3.5 py-2.5 rounded-xl bg-blue-600 hover:bg-blue-500 text-white font-semibold text-xs flex items-center gap-1.5 shadow-sm transition"
                        title="Tarik data Headcount TKL/TKTL & Jam Kerja dari HRIS"
                    >
                        <RefreshCw class="w-3.5 h-3.5" />
                        <span>Tarik dari HRIS</span>
                    </button>

                    <button
                        type="button"
                        @click="publishToHris"
                        class="px-3.5 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-white font-semibold text-xs flex items-center gap-1.5 shadow-sm transition"
                        title="Publikasikan variabel keuangan ke Sasaran Mutu Level 3 HRIS"
                    >
                        <Share2 class="w-3.5 h-3.5" />
                        <span>Publikasikan ke HRIS</span>
                    </button>
                </div>
            </div>

            <!-- Composite BSC Score Hero Banner -->
            <div class="bg-gradient-to-r from-slate-900 via-emerald-950 to-teal-900 text-white rounded-2xl p-6 shadow-xl border border-emerald-800/40 flex flex-col md:flex-row md:items-center justify-between gap-6">
                <div>
                    <span class="text-xs uppercase font-bold tracking-wider text-emerald-300 bg-emerald-500/20 px-2.5 py-1 rounded-full border border-emerald-500/30">
                        Evaluasi Kinerja Finansial Terpadu
                    </span>
                    <h2 class="text-xl font-bold mt-2">Indeks Komposit Balanced Scorecard</h2>
                    <p class="text-xs text-slate-300 mt-1 max-w-xl">
                        Skor tertimbang dihitung otomatis dari 19 indikator rasio terhadap target standar industri manufaktur herbal & farmasi.
                    </p>
                </div>

                <div class="flex items-center gap-4 bg-white/10 p-4 rounded-xl backdrop-blur-sm border border-white/10 shrink-0">
                    <div class="text-right">
                        <span class="text-xs text-slate-300 block">Predikat Kinerja:</span>
                        <span class="text-base font-black text-emerald-300">{{ bscData.grade || 'A (Unggul)' }}</span>
                    </div>
                    <div class="w-16 h-16 rounded-full bg-emerald-500/20 border-2 border-emerald-400 flex items-center justify-center">
                        <span class="text-2xl font-black font-mono text-white">{{ bscData.composite_score }}</span>
                    </div>
                </div>
            </div>

            <!-- 5 Pillar Cards -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-4">
                <div
                    v-for="(pillar, key) in bscData.pillars"
                    :key="key"
                    class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl p-4 shadow-sm"
                >
                    <div class="flex items-center justify-between text-xs font-semibold text-slate-500 uppercase tracking-wider mb-2">
                        <span>{{ key }}</span>
                        <span class="text-emerald-600 dark:text-emerald-400 font-bold">{{ pillar.weight }}%</span>
                    </div>
                    <div class="text-2xl font-black text-slate-900 dark:text-white">
                        {{ pillar.score }}<span class="text-xs font-normal text-slate-400">/100</span>
                    </div>
                    <div class="w-full bg-slate-100 dark:bg-slate-800 rounded-full h-2 mt-3 overflow-hidden">
                        <div
                            class="h-2 rounded-full transition-all duration-500"
                            :class="pillar.score >= 85 ? 'bg-emerald-500' : (pillar.score >= 70 ? 'bg-amber-500' : 'bg-red-500')"
                            :style="{ width: `${pillar.score}%` }"
                        ></div>
                    </div>
                    <div class="flex items-center justify-between text-[11px] text-slate-500 mt-2">
                        <span>{{ pillar.ratios?.length || 0 }} Indikator</span>
                        <span class="font-semibold" :class="pillar.score >= 85 ? 'text-emerald-600' : 'text-amber-600'">
                            {{ pillar.score >= 85 ? 'Sehat' : 'Perhatian' }}
                        </span>
                    </div>
                </div>
            </div>

            <!-- 19 Ratios Grid / Table by Pillar -->
            <div class="space-y-6">
                <div
                    v-for="(pillar, pillarKey) in bscData.pillars"
                    :key="pillarKey"
                    class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl shadow-sm overflow-hidden"
                >
                    <div class="p-4 bg-slate-50 dark:bg-slate-800/60 border-b border-slate-200 dark:border-slate-800 flex items-center justify-between">
                        <div>
                            <h3 class="font-bold text-sm text-slate-900 dark:text-white uppercase tracking-wider flex items-center gap-2">
                                <Award class="w-4 h-4 text-emerald-500" />
                                <span>Pilar: {{ pillarKey }} (Bobot {{ pillar.weight }}% &bull; Rata-rata Skor: {{ pillar.score }}/100)</span>
                            </h3>
                        </div>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="w-full text-left text-xs">
                            <thead class="text-slate-500 border-b border-slate-200 dark:border-slate-800 font-semibold">
                                <tr>
                                    <th class="p-3 w-28">Kode Rasio</th>
                                    <th class="p-3">Nama Indikator Finansial</th>
                                    <th class="p-3 text-right w-36">Nilai Realisasi</th>
                                    <th class="p-3 text-right w-32">Target Standar</th>
                                    <th class="p-3 text-center w-28">Skor (0-100)</th>
                                    <th class="p-3 text-center w-32">Status Pencapaian</th>
                                    <th class="p-3 text-center w-20">Konfig</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                                <tr v-for="rat in pillar.ratios" :key="rat.code" class="hover:bg-slate-50 dark:hover:bg-slate-800/30">
                                    <td class="p-3 font-bold font-mono text-emerald-600 dark:text-emerald-400">
                                        {{ rat.code }}
                                    </td>
                                    <td class="p-3">
                                        <div class="font-semibold text-slate-800 dark:text-slate-100">{{ rat.name }}</div>
                                    </td>
                                    <td class="p-3 text-right font-mono font-bold text-slate-900 dark:text-white">
                                        {{ formatRatioValue(rat.raw_value, rat.unit) }}
                                    </td>
                                    <td class="p-3 text-right font-mono text-slate-500">
                                        {{ formatRatioValue(rat.target, rat.unit) }}
                                    </td>
                                    <td class="p-3 text-center font-mono font-bold">
                                        <span :class="rat.score >= 85 ? 'text-emerald-600' : (rat.score >= 70 ? 'text-amber-600' : 'text-rose-600')">
                                            {{ rat.score }}
                                        </span>
                                    </td>
                                    <td class="p-3 text-center">
                                        <span
                                            :class="[
                                                'px-2 py-0.5 rounded text-[10px] font-bold uppercase',
                                                rat.status === 'achieved'
                                                    ? 'bg-emerald-100 text-emerald-800 dark:bg-emerald-900/40 dark:text-emerald-300'
                                                    : (rat.status === 'warning' ? 'bg-amber-100 text-amber-800 dark:bg-amber-900/40 dark:text-amber-300' : 'bg-rose-100 text-rose-800 dark:bg-rose-900/40 dark:text-rose-300')
                                            ]"
                                        >
                                            {{ rat.status === 'achieved' ? 'TERCAPAI' : (rat.status === 'warning' ? 'PERINGATAN' : 'KRITIS') }}
                                        </span>
                                    </td>
                                    <td class="p-3 text-center">
                                        <button
                                            @click="openEditConfig(configurations.find(c => c.ratio_code === rat.code) || { id: 1, ratio_code: rat.code, target_value: rat.target, weight: 1, is_active: true })"
                                            class="p-1 text-slate-400 hover:text-emerald-600 rounded hover:bg-slate-100 dark:hover:bg-slate-800"
                                            title="Ubah Target Rasio"
                                        >
                                            <Sliders class="w-3.5 h-3.5" />
                                        </button>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Edit Config Modal -->
            <div
                v-if="editConfigModalOpen"
                class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50 backdrop-blur-sm"
            >
                <div class="bg-white dark:bg-slate-900 rounded-2xl max-w-sm w-full p-6 shadow-2xl border border-slate-200 dark:border-slate-800 space-y-4">
                    <div class="flex items-center justify-between pb-3 border-b border-slate-200 dark:border-slate-800">
                        <h3 class="font-bold text-slate-900 dark:text-white text-sm flex items-center gap-2">
                            <Settings2 class="w-4 h-4 text-emerald-500" />
                            <span>Ubah Target: {{ selectedConfig?.ratio_code }}</span>
                        </h3>
                        <button @click="editConfigModalOpen = false" class="text-slate-400 hover:text-slate-600">
                            <X class="w-4 h-4" />
                        </button>
                    </div>

                    <form @submit.prevent="saveConfig" class="space-y-3 text-xs">
                        <div>
                            <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1">
                                Target Standar Nilai Rasio:
                            </label>
                            <input
                                v-model.number="configForm.target_value"
                                type="number"
                                step="any"
                                required
                                class="w-full rounded-lg border-slate-300 dark:border-slate-700 dark:bg-slate-800 text-xs py-2"
                            />
                        </div>

                        <div>
                            <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1">
                                Bobot Rasio (Opsional):
                            </label>
                            <input
                                v-model.number="configForm.weight"
                                type="number"
                                step="any"
                                class="w-full rounded-lg border-slate-300 dark:border-slate-700 dark:bg-slate-800 text-xs py-2"
                            />
                        </div>

                        <div class="flex items-center justify-end gap-2 pt-3 border-t">
                            <button
                                type="button"
                                @click="editConfigModalOpen = false"
                                class="px-3 py-1.5 text-slate-600 hover:bg-slate-100 rounded-lg text-xs"
                            >
                                Batal
                            </button>
                            <button
                                type="submit"
                                :disabled="configForm.processing"
                                class="px-4 py-1.5 bg-emerald-600 hover:bg-emerald-700 text-white font-semibold rounded-lg text-xs shadow-sm"
                            >
                                Simpan Target
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </ModuleLayout>
</template>
