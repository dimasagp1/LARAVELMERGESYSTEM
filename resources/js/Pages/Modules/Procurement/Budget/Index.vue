<script setup>
import { ref } from 'vue';
import { Head, router } from '@inertiajs/vue3';
import ModuleLayout from '@/Layouts/ModuleLayout.vue';
import {
    Wallet,
    Plus,
    RefreshCw,
    TrendingUp,
    ShieldAlert,
    CheckCircle2,
    Calendar,
} from 'lucide-vue-next';

const props = defineProps({
    module: Object,
    budgets: Array,
    expenseStagings: Array,
    departments: Array,
    currentYear: Number,
    currentMonth: Number,
});

const fiscalYear = ref(props.currentYear);
const month = ref(props.currentMonth);

const applyFilter = () => {
    router.get('/procurement/budgets', {
        fiscal_year: fiscalYear.value,
        month: month.value,
    }, { preserveState: true, replace: true });
};

// Modal Budget Setup
const budgetModalOpen = ref(false);
const formDepartmentId = ref(props.departments[0]?.id || '');
const formPurpose = ref('Biaya Operasional Rutin');
const formInitialBudget = ref(50000000);
const modalLoading = ref(false);

const submitBudget = () => {
    modalLoading.value = true;
    router.post('/procurement/budgets', {
        department_id: formDepartmentId.value,
        fiscal_year: fiscalYear.value,
        month: month.value,
        purpose: formPurpose.value,
        initial_budget: formInitialBudget.value,
    }, {
        onSuccess: () => {
            budgetModalOpen.value = false;
            modalLoading.value = false;
        },
        onError: () => { modalLoading.value = false; },
    });
};
</script>

<template>
    <Head title="Pagu Anggaran FAT - ERP" />

    <ModuleLayout :module="module">
        <div class="space-y-6">
            <!-- Header -->
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-6 shadow-xs">
                <div>
                    <h1 class="text-xl font-bold text-slate-900 dark:text-white">
                        Manajemen Pagu Anggaran (FAT Budget Control)
                    </h1>
                    <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">
                        Kendali plafon anggaran pengadaan per departemen dan sinkronisasi komitmen belanja
                    </p>
                </div>
                <div class="flex items-center gap-3">
                    <select
                        v-model="month"
                        @change="applyFilter"
                        class="text-xs rounded-xl border border-slate-300 dark:border-slate-700 dark:bg-slate-800 p-2 font-semibold"
                    >
                        <option v-for="m in 12" :key="m" :value="m">Bulan {{ m }}</option>
                    </select>
                    <select
                        v-model="fiscalYear"
                        @change="applyFilter"
                        class="text-xs rounded-xl border border-slate-300 dark:border-slate-700 dark:bg-slate-800 p-2 font-semibold"
                    >
                        <option :value="2026">Tahun 2026</option>
                        <option :value="2027">Tahun 2027</option>
                    </select>
                    <button
                        @click="budgetModalOpen = true"
                        class="px-4 py-2 rounded-xl bg-violet-600 hover:bg-violet-700 text-white text-xs font-semibold inline-flex items-center gap-1.5 shadow-xs"
                    >
                        <Plus class="w-4 h-4" />
                        Set Pagu Baru
                    </button>
                </div>
            </div>

            <!-- Budget Table -->
            <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-6 shadow-xs space-y-4">
                <h2 class="text-sm font-bold text-slate-900 dark:text-white uppercase tracking-wider">
                    Plafon Anggaran Departemen (Bulan {{ month }}/{{ fiscalYear }})
                </h2>

                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="border-b border-slate-200 dark:border-slate-800 text-[11px] font-semibold text-slate-500 uppercase tracking-wider bg-slate-50/50 dark:bg-slate-800/40">
                                <th class="py-3 px-4">Departemen</th>
                                <th class="py-3 px-4">Tujuan (Purpose)</th>
                                <th class="py-3 px-4 text-right">Pagu Awal (Rp)</th>
                                <th class="py-3 px-4 text-right">Realisasi (Rp)</th>
                                <th class="py-3 px-4 text-right">Booked (Rp)</th>
                                <th class="py-3 px-4 text-right">Sisa Pagu (Rp)</th>
                                <th class="py-3 px-4">Status Serapan</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 dark:divide-slate-800 text-xs">
                            <tr v-for="b in budgets" :key="b.id" class="hover:bg-slate-50/50 dark:hover:bg-slate-800/30">
                                <td class="py-3 px-4 font-bold text-slate-900 dark:text-white">
                                    {{ b.department?.name }}
                                </td>
                                <td class="py-3 px-4 font-medium text-slate-700 dark:text-slate-300">
                                    {{ b.purpose }}
                                </td>
                                <td class="py-3 px-4 text-right font-semibold">
                                    Rp {{ Number(b.initial_budget).toLocaleString('id-ID') }}
                                </td>
                                <td class="py-3 px-4 text-right font-bold text-emerald-600">
                                    Rp {{ Number(b.realized_amount).toLocaleString('id-ID') }}
                                </td>
                                <td class="py-3 px-4 text-right font-bold text-amber-600">
                                    Rp {{ Number(b.booked_amount).toLocaleString('id-ID') }}
                                </td>
                                <td class="py-3 px-4 text-right font-extrabold text-violet-600 dark:text-violet-400">
                                    Rp {{ Number(b.initial_budget - (b.realized_amount + b.booked_amount)).toLocaleString('id-ID') }}
                                </td>
                                <td class="py-3 px-4">
                                    <div class="w-full bg-slate-100 dark:bg-slate-800 rounded-full h-2 max-w-[100px] overflow-hidden mb-1">
                                        <div
                                            class="bg-violet-600 h-2 rounded-full"
                                            :style="{ width: `${Math.min(100, (((b.realized_amount + b.booked_amount) / (b.initial_budget || 1)) * 100))}%` }"
                                        ></div>
                                    </div>
                                    <span class="text-[10px] text-slate-500 font-semibold">
                                        {{ Math.round(((b.realized_amount + b.booked_amount) / (b.initial_budget || 1)) * 100) }}% Terpakai
                                    </span>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div v-if="budgets.length === 0" class="text-center py-10 text-slate-400 text-xs">
                    Belum ada alokasi pagu anggaran untuk periode ini.
                </div>
            </div>

            <!-- Expense Staging Logs -->
            <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-6 shadow-xs space-y-4">
                <h2 class="text-sm font-bold text-slate-900 dark:text-white uppercase tracking-wider">
                    Log Sinkronisasi Komitmen Belanja (Expense Staging FAT)
                </h2>

                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse text-xs">
                        <thead>
                            <tr class="border-b border-slate-200 dark:border-slate-800 text-[11px] font-semibold text-slate-500 uppercase tracking-wider">
                                <th class="py-2.5 px-4">No PR</th>
                                <th class="py-2.5 px-4">Departemen & Tujuan</th>
                                <th class="py-2.5 px-4 text-right">Nominal (Rp)</th>
                                <th class="py-2.5 px-4">Status Sync</th>
                                <th class="py-2.5 px-4">Waktu Staged</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                            <tr v-for="stg in expenseStagings" :key="stg.id">
                                <td class="py-2.5 px-4 font-mono font-bold text-violet-600">
                                    {{ stg.purchase_request?.pr_number }}
                                </td>
                                <td class="py-2.5 px-4">
                                    {{ stg.department?.name }} &bull; {{ stg.purpose }}
                                </td>
                                <td class="py-2.5 px-4 text-right font-bold">
                                    Rp {{ Number(stg.amount).toLocaleString('id-ID') }}
                                </td>
                                <td class="py-2.5 px-4">
                                    <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-emerald-100 text-emerald-800">
                                        {{ stg.status }}
                                    </span>
                                </td>
                                <td class="py-2.5 px-4 text-slate-400">
                                    {{ stg.staged_at }}
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Modal Set Budget -->
        <div v-if="budgetModalOpen" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-xs">
            <div class="bg-white dark:bg-slate-900 rounded-2xl max-w-md w-full p-6 shadow-2xl border border-slate-200 dark:border-slate-800 space-y-4">
                <h3 class="text-sm font-bold text-slate-900 dark:text-white">
                    Tetapkan Pagu Anggaran Departemen
                </h3>
                <div>
                    <label class="block text-xs font-semibold mb-1">Departemen:</label>
                    <select v-model="formDepartmentId" class="w-full text-xs rounded-xl border border-slate-300 dark:border-slate-700 dark:bg-slate-800 p-2.5">
                        <option v-for="d in departments" :key="d.id" :value="d.id">{{ d.name }}</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-semibold mb-1">Tujuan Belanja (Purpose):</label>
                    <input v-model="formPurpose" type="text" class="w-full text-xs rounded-xl border border-slate-300 dark:border-slate-700 dark:bg-slate-800 p-2.5" required />
                </div>
                <div>
                    <label class="block text-xs font-semibold mb-1">Plafon Pagu Bulanan (Rp):</label>
                    <input v-model.number="formInitialBudget" type="number" step="1000000" class="w-full text-xs rounded-xl border border-slate-300 dark:border-slate-700 dark:bg-slate-800 p-2.5 font-bold" required />
                </div>
                <div class="flex justify-end gap-2 pt-3">
                    <button @click="budgetModalOpen = false" class="px-4 py-2 rounded-xl text-xs font-semibold text-slate-600">Batal</button>
                    <button @click="submitBudget" :disabled="modalLoading" class="px-4 py-2 rounded-xl text-xs font-semibold bg-violet-600 hover:bg-violet-700 text-white">Simpan Pagu</button>
                </div>
            </div>
        </div>
    </ModuleLayout>
</template>
