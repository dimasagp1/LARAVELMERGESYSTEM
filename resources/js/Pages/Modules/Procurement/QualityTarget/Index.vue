<script setup>
import { ref } from 'vue';
import { Head, router } from '@inertiajs/vue3';
import ModuleLayout from '@/Layouts/ModuleLayout.vue';
import {
    Target,
    Plus,
    FileText,
    AlertTriangle,
    ShieldCheck,
    Clock,
    TrendingUp,
} from 'lucide-vue-next';

const props = defineProps({
    module: Object,
    summary: Object,
    lossLogs: Array,
    fiscalYear: Number,
});

const year = ref(props.fiscalYear);
const applyFilter = () => {
    router.get('/procurement/sasaran-mutu', { fiscal_year: year.value }, { preserveState: true, replace: true });
};

// Modal Loss Time
const lossModalOpen = ref(false);
const formLogDate = ref(new Date().toISOString().split('T')[0]);
const formMachineName = ref('Mesin Granulasi Line 1');
const formDowntimeHours = ref(2.5);
const formMaterialName = ref('Ekstrak Temulawak');
const formOutputPerHour = ref(500);
const formProductUnitValue = ref(25000);
const formTargetRevenue = ref(5000000000);
const modalLoading = ref(false);

const submitLossLog = () => {
    modalLoading.value = true;
    router.post('/procurement/sasaran-mutu/loss-time', {
        log_date: formLogDate.value,
        machine_name: formMachineName.value,
        downtime_hours: formDowntimeHours.value,
        material_name: formMaterialName.value,
        output_per_hour: formOutputPerHour.value,
        product_unit_value: formProductUnitValue.value,
        target_revenue: formTargetRevenue.value,
    }, {
        onSuccess: () => {
            lossModalOpen.value = false;
            modalLoading.value = false;
        },
        onError: () => { modalLoading.value = false; },
    });
};
</script>

<template>
    <Head title="Sasaran Mutu 2026 & KPI - ERP" />

    <ModuleLayout :module="module">
        <div class="space-y-6">
            <!-- Header -->
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-6 shadow-xs">
                <div>
                    <h1 class="text-xl font-bold text-slate-900 dark:text-white">
                        Sasaran Mutu Divisi Pengadaan 2026 (ISO 9001:2015)
                    </h1>
                    <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">
                        Monitoring pencapaian KPI mutu, deviasi harga pasar, dan mitigasi loss time produksi
                    </p>
                </div>
                <div class="flex items-center gap-2">
                    <select
                        v-model="year"
                        @change="applyFilter"
                        class="text-xs rounded-xl border border-slate-300 dark:border-slate-700 dark:bg-slate-800 p-2 font-semibold"
                    >
                        <option :value="2026">Tahun 2026</option>
                        <option :value="2027">Tahun 2027</option>
                    </select>
                    <button
                        @click="lossModalOpen = true"
                        class="px-3.5 py-2 rounded-xl bg-rose-600 hover:bg-rose-700 text-white text-xs font-semibold inline-flex items-center gap-1.5 shadow-xs transition-colors"
                    >
                        <AlertTriangle class="w-4 h-4" />
                        Catat Loss Time
                    </button>
                    <a
                        :href="`/procurement/sasaran-mutu/pdf?fiscal_year=${year}`"
                        target="_blank"
                        class="px-3.5 py-2 rounded-xl bg-slate-900 text-white dark:bg-white dark:text-slate-900 text-xs font-semibold inline-flex items-center gap-1.5 hover:opacity-90 transition-opacity"
                    >
                        <FileText class="w-4 h-4" />
                        Cetak Laporan PDF
                    </a>
                </div>
            </div>

            <!-- Composite Score Banner -->
            <div class="p-6 rounded-2xl bg-gradient-to-r from-violet-900 to-indigo-900 text-white shadow-sm flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div>
                    <span class="text-xs font-bold text-violet-300 uppercase tracking-wider block mb-1">
                        Pencapaian Sasaran Mutu Komposit 2026
                    </span>
                    <div class="text-3xl font-extrabold flex items-baseline gap-2">
                        {{ summary.composite_score }} <span class="text-base font-normal text-violet-200">/ 100</span>
                    </div>
                    <div class="text-xs text-violet-200 mt-1">
                        Predikat: <strong class="text-white">{{ summary.grade }}</strong> &bull; Total 4 Indikator Berbobot Seimbang (25%)
                    </div>
                </div>
                <div class="flex items-center gap-2">
                    <ShieldCheck class="w-12 h-12 text-emerald-400 opacity-90" />
                </div>
            </div>

            <!-- 4 Quality Pillars Cards -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div
                    v-for="kpi in summary.kpis"
                    :key="kpi.id"
                    class="p-5 rounded-2xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 shadow-xs flex flex-col justify-between"
                >
                    <div>
                        <div class="flex items-center justify-between mb-2">
                            <span class="text-[10px] font-bold text-violet-600 dark:text-violet-400 uppercase tracking-wider">
                                Pilar Mutu #{{ kpi.id }} &bull; Bobot {{ kpi.weight }}%
                            </span>
                            <span :class="['px-2.5 py-0.5 rounded-full text-xs font-extrabold', kpi.score >= 90 ? 'bg-emerald-100 text-emerald-800 dark:bg-emerald-950 dark:text-emerald-300' : 'bg-amber-100 text-amber-800 dark:bg-amber-950 dark:text-amber-300']">
                                Skor: {{ kpi.score }}
                            </span>
                        </div>
                        <h3 class="font-bold text-slate-900 dark:text-white text-sm mb-3">
                            {{ kpi.title }}
                        </h3>
                    </div>

                    <div class="pt-3 border-t border-slate-100 dark:border-slate-800 space-y-1.5 text-xs">
                        <div class="flex justify-between">
                            <span class="text-slate-400">Target Standard:</span>
                            <span class="font-semibold text-slate-800 dark:text-slate-200">{{ kpi.target }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-slate-400">Realisasi Aktual:</span>
                            <span class="font-bold text-violet-600 dark:text-violet-400">{{ kpi.actual }}</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Production Loss Time Logs Table -->
            <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-6 shadow-xs space-y-4">
                <div class="flex items-center justify-between">
                    <div>
                        <h2 class="text-sm font-bold text-slate-900 dark:text-white uppercase tracking-wider">
                            Insiden Downtime Mesin Akibat Keterlambatan Material (Loss Time)
                        </h2>
                        <p class="text-xs text-slate-500">
                            Data insiden langsung memengaruhi penilaian Sasaran Mutu 1
                        </p>
                    </div>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse text-xs">
                        <thead>
                            <tr class="border-b border-slate-200 dark:border-slate-800 text-[11px] font-semibold text-slate-500 uppercase tracking-wider bg-slate-50/50 dark:bg-slate-800/40">
                                <th class="py-3 px-3">Tanggal Kejadian</th>
                                <th class="py-3 px-3">Mesin Terhenti</th>
                                <th class="py-3 px-3">Material Terlambat</th>
                                <th class="py-3 px-3 text-center">Durasi (Jam)</th>
                                <th class="py-3 px-3 text-right">Estimasi Nilai Kerugian</th>
                                <th class="py-3 px-3">Dicatat Oleh</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                            <tr v-for="log in lossLogs" :key="log.id" class="hover:bg-slate-50/50 dark:hover:bg-slate-800/30">
                                <td class="py-3 px-3 font-semibold text-slate-900 dark:text-white">
                                    {{ log.log_date }}
                                </td>
                                <td class="py-3 px-3 font-bold text-rose-600">
                                    {{ log.machine_name }}
                                </td>
                                <td class="py-3 px-3 text-slate-700 dark:text-slate-300">
                                    {{ log.material_name }}
                                </td>
                                <td class="py-3 px-3 text-center font-extrabold">
                                    {{ Number(log.downtime_hours) }} Jam
                                </td>
                                <td class="py-3 px-3 text-right font-extrabold text-rose-600">
                                    Rp {{ Number(log.loss_value).toLocaleString('id-ID') }}
                                </td>
                                <td class="py-3 px-3 text-slate-400">
                                    {{ log.creator?.name || 'Sistem Produksi' }}
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div v-if="lossLogs.length === 0" class="text-center py-10 text-slate-400 text-xs">
                    Nir-Downtime! Tidak ada insiden keterlambatan material yang tercatat pada tahun ini.
                </div>
            </div>
        </div>

        <!-- Modal Tambah Loss Time -->
        <div v-if="lossModalOpen" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-xs">
            <div class="bg-white dark:bg-slate-900 rounded-2xl max-w-md w-full p-6 shadow-2xl border border-slate-200 dark:border-slate-800 space-y-4">
                <h3 class="text-sm font-bold text-rose-600">
                    Catat Kejadian Downtime Mesin Produksi
                </h3>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-semibold mb-1">Tanggal Insiden:</label>
                        <input v-model="formLogDate" type="date" class="w-full text-xs rounded-xl border border-slate-300 dark:border-slate-700 dark:bg-slate-800 p-2" required />
                    </div>
                    <div>
                        <label class="block text-xs font-semibold mb-1">Durasi Terhenti (Jam):</label>
                        <input v-model.number="formDowntimeHours" type="number" step="0.5" class="w-full text-xs rounded-xl border border-rose-300 dark:border-rose-800 dark:bg-slate-800 p-2 font-bold text-rose-600" required />
                    </div>
                </div>
                <div>
                    <label class="block text-xs font-semibold mb-1">Nama Mesin:</label>
                    <input v-model="formMachineName" type="text" class="w-full text-xs rounded-xl border border-slate-300 dark:border-slate-700 dark:bg-slate-800 p-2" required />
                </div>
                <div>
                    <label class="block text-xs font-semibold mb-1">Nama Material Terlambat:</label>
                    <input v-model="formMaterialName" type="text" class="w-full text-xs rounded-xl border border-slate-300 dark:border-slate-700 dark:bg-slate-800 p-2" required />
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-semibold mb-1">Output / Jam (Unit):</label>
                        <input v-model.number="formOutputPerHour" type="number" class="w-full text-xs rounded-xl border border-slate-300 dark:border-slate-700 dark:bg-slate-800 p-2" required />
                    </div>
                    <div>
                        <label class="block text-xs font-semibold mb-1">Nilai Produk / Unit (Rp):</label>
                        <input v-model.number="formProductUnitValue" type="number" class="w-full text-xs rounded-xl border border-slate-300 dark:border-slate-700 dark:bg-slate-800 p-2" required />
                    </div>
                </div>
                <div class="flex justify-end gap-2 pt-3">
                    <button @click="lossModalOpen = false" class="px-4 py-2 rounded-xl text-xs font-semibold text-slate-600">Batal</button>
                    <button @click="submitLossLog" :disabled="modalLoading" class="px-4 py-2 rounded-xl text-xs font-semibold bg-rose-600 hover:bg-rose-700 text-white">Simpan Log Loss Time</button>
                </div>
            </div>
        </div>
    </ModuleLayout>
</template>
