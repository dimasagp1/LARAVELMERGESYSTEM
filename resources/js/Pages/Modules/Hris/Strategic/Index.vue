<script setup>
import { ref } from 'vue';
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import ModuleLayout from '@/Layouts/ModuleLayout.vue';
import {
    Target,
    Plus,
    Lock,
    Unlock,
    CheckCircle2,
    BarChart3,
    TrendingUp,
    Calendar,
    Building2,
    Layers,
    ListTodo,
    Sparkles,
} from 'lucide-vue-next';

const props = defineProps({
    objectives: Array,
    selectedYear: Number,
    departments: Array,
    filters: Object,
    summary: Object,
});

const hrisMenus = [
    { name: 'Overview', route: '/hris', icon: 'LayoutDashboard' },
    { name: 'Master Karyawan', route: '/hris/employees', icon: 'Users' },
    { name: 'Presensi & Geofence', route: '/hris/attendance', icon: 'CalendarCheck' },
    { name: 'Pengajuan Cuti', route: '/hris/leaves', icon: 'CalendarOff' },
    { name: 'Lembur (SPL) & Time Bank', route: '/hris/overtime', icon: 'Timer' },
    { name: 'Kaizen Kizuki & Point', route: '/hris/kizuki', icon: 'Lightbulb' },
    { name: 'Payroll PPh 21 TER', route: '/hris/payroll', icon: 'Banknote' },
    { name: 'Strategic BSC L3 & L4', route: '/hris/strategic', icon: 'Target' },
];

const year = ref(props.selectedYear);
const filterDept = ref(props.filters.department_id || '');
const filterPerspective = ref(props.filters.perspective || '');

const applyFilters = () => {
    router.get('/hris/strategic', {
        year: year.value,
        department_id: filterDept.value || undefined,
        perspective: filterPerspective.value || undefined,
    }, {
        preserveState: true,
        preserveScroll: true,
    });
};

// Add Objective Modal
const isObjModalOpen = ref(false);
const objForm = useForm({
    department_id: props.departments[0]?.id || '',
    period_year: props.selectedYear,
    perspective: 'internal_process',
    kpi_name: '',
    target_value: 100,
    target_unit: '%',
    polarity: 'maximize',
    weight: 20,
});

const submitObjective = () => {
    objForm.post('/hris/strategic/objectives', {
        preserveScroll: true,
        onSuccess: () => {
            isObjModalOpen.value = false;
            objForm.reset('kpi_name');
        },
    });
};

// Add Program Modal
const isProgramModalOpen = ref(false);
const selectedObjId = ref(null);
const programForm = useForm({
    department_objective_id: '',
    program_name: '',
    pic_name: '',
    start_date: new Date().toISOString().substring(0, 10),
    end_date: new Date().toISOString().substring(0, 10),
    budget: 0,
    progress_pct: 0,
});

const openAddProgram = (objId) => {
    selectedObjId.value = objId;
    programForm.department_objective_id = objId;
    isProgramModalOpen.value = true;
};

const submitProgram = () => {
    programForm.post('/hris/strategic/programs', {
        preserveScroll: true,
        onSuccess: () => {
            isProgramModalOpen.value = false;
            programForm.reset('program_name', 'pic_name');
        },
    });
};

// Evaluate Modal
const isEvalModalOpen = ref(false);
const evalObj = ref(null);
const evalForm = useForm({
    period_month: new Date().getMonth() + 1,
    actual_value: 0,
    notes: '',
});

const openEvaluate = (obj) => {
    evalObj.value = obj;
    evalForm.actual_value = obj.target_value;
    isEvalModalOpen.value = true;
};

const submitEvaluate = () => {
    if (!evalObj.value) return;
    evalForm.post(`/hris/strategic/objectives/${evalObj.value.id}/evaluate`, {
        preserveScroll: true,
        onSuccess: () => {
            isEvalModalOpen.value = false;
            evalObj.value = null;
        },
    });
};

// Lock & Push to BSC
const pushToBsc = (objId) => {
    router.post(`/hris/strategic/objectives/${objId}/push-to-bsc`, {}, {
        preserveScroll: true,
    });
};
</script>

<template>
    <Head title="Strategic Performance BSC L3/L4 — HRISNOW" />

    <ModuleLayout
        module-slug="hris"
        module-name="HRISNOW"
        color-theme="blue"
        :menus="hrisMenus"
    >
        <div class="space-y-6">
            <!-- Header -->
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 bg-white dark:bg-slate-800 p-6 rounded-2xl shadow-xs border border-slate-200 dark:border-slate-700">
                <div class="flex items-center gap-3">
                    <div class="p-3 bg-rose-500/10 text-rose-600 dark:text-rose-400 rounded-xl">
                        <Target class="w-8 h-8" />
                    </div>
                    <div>
                        <h1 class="text-2xl font-bold text-slate-900 dark:text-white tracking-tight">
                            Strategic Performance BSC (Level 3 & Level 4)
                        </h1>
                        <p class="text-sm text-slate-500 dark:text-slate-400 mt-0.5">
                            Penyelarasan Sasaran Mutu Departemen (BSC L3) dan Inisiatif Program Kerja Operasional (BSC L4).
                        </p>
                    </div>
                </div>

                <div class="flex items-center gap-3">
                    <button
                        @click="isObjModalOpen = true"
                        class="inline-flex items-center gap-2 px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-xl text-sm transition shadow-sm"
                    >
                        <Plus class="w-4 h-4" />
                        Tambah Sasaran (L3)
                    </button>
                </div>
            </div>

            <!-- Summary KPI 4 Cards -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                <div class="bg-white dark:bg-slate-800 p-4 rounded-xl border border-slate-200 dark:border-slate-700 shadow-xs">
                    <div class="text-xs text-slate-500 mb-1">Total Sasaran Departemen</div>
                    <div class="text-xl font-bold text-slate-900 dark:text-white">
                        {{ summary.total_objectives }} KPI
                    </div>
                    <div class="text-[11px] text-slate-400 mt-0.5">Level 3 Objectives</div>
                </div>

                <div class="bg-white dark:bg-slate-800 p-4 rounded-xl border border-slate-200 dark:border-slate-700 shadow-xs">
                    <div class="text-xs text-slate-500 mb-1">Rata-rata Capaian KPI</div>
                    <div class="text-xl font-bold text-emerald-600">
                        {{ summary.avg_achievement }}%
                    </div>
                    <div class="text-[11px] text-slate-400 mt-0.5">Tahun {{ selectedYear }}</div>
                </div>

                <div class="bg-white dark:bg-slate-800 p-4 rounded-xl border border-slate-200 dark:border-slate-700 shadow-xs">
                    <div class="text-xs text-slate-500 mb-1">Inisiatif Program Kerja</div>
                    <div class="text-xl font-bold text-blue-600">
                        {{ summary.total_programs }} Program
                    </div>
                    <div class="text-[11px] text-slate-400 mt-0.5">Level 4 Action Items</div>
                </div>

                <div class="bg-white dark:bg-slate-800 p-4 rounded-xl border border-slate-200 dark:border-slate-700 shadow-xs">
                    <div class="text-xs text-slate-500 mb-1">Terkunci ke Corporate BSC</div>
                    <div class="text-xl font-bold text-purple-600">
                        {{ summary.locked_count }} Sasaran
                    </div>
                    <div class="text-[11px] text-slate-400 mt-0.5">Locked & Verified</div>
                </div>
            </div>

            <!-- Filters Bar -->
            <div class="bg-white dark:bg-slate-800 p-4 rounded-2xl border border-slate-200 dark:border-slate-700 shadow-xs flex flex-wrap items-center justify-between gap-4">
                <div class="flex items-center gap-3">
                    <div class="flex items-center gap-2">
                        <Calendar class="w-4 h-4 text-slate-400" />
                        <span class="text-xs font-semibold text-slate-600 dark:text-slate-300">Tahun:</span>
                    </div>
                    <select
                        v-model="year"
                        @change="applyFilters"
                        class="px-3 py-1.5 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl text-xs text-slate-900 dark:text-white outline-none"
                    >
                        <option :value="2025">2025</option>
                        <option :value="2026">2026</option>
                        <option :value="2027">2027</option>
                    </select>

                    <select
                        v-model="filterDept"
                        @change="applyFilters"
                        class="px-3 py-1.5 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl text-xs text-slate-700 dark:text-slate-300 outline-none"
                    >
                        <option value="">Semua Departemen</option>
                        <option v-for="dept in departments" :key="dept.id" :value="dept.id">
                            {{ dept.name }}
                        </option>
                    </select>
                </div>

                <div class="flex items-center gap-3">
                    <select
                        v-model="filterPerspective"
                        @change="applyFilters"
                        class="px-3 py-1.5 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl text-xs text-slate-700 dark:text-slate-300 outline-none"
                    >
                        <option value="">Semua Perspektif BSC</option>
                        <option value="financial">1. Financial (Keuangan)</option>
                        <option value="customer">2. Customer / Stakeholder</option>
                        <option value="internal_process">3. Internal Business Process</option>
                        <option value="learning_growth">4. Learning & Growth (SDM)</option>
                    </select>
                </div>
            </div>

            <!-- Objectives List with Accordion/Programs -->
            <div class="space-y-4">
                <div
                    v-for="obj in objectives"
                    :key="obj.id"
                    class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-200 dark:border-slate-700 p-5 shadow-xs space-y-4"
                >
                    <!-- Objective Header -->
                    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 pb-3 border-b border-slate-100 dark:border-slate-700">
                        <div>
                            <div class="flex items-center gap-2">
                                <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase bg-blue-50 text-blue-700">
                                    {{ obj.perspective.replace('_', ' ') }}
                                </span>
                                <span class="text-xs text-slate-400 font-semibold">
                                    {{ obj.department?.name }} • Bobot: {{ obj.weight }}%
                                </span>
                                <span v-if="obj.is_locked" class="inline-flex items-center gap-1 text-[10px] font-bold text-purple-600 bg-purple-50 px-2 py-0.5 rounded">
                                    <Lock class="w-3 h-3" />
                                    TERKUNCI KE CORPORATE BSC
                                </span>
                            </div>
                            <h3 class="text-base font-bold text-slate-900 dark:text-white mt-1">
                                {{ obj.kpi_name }}
                            </h3>
                        </div>

                        <div class="flex items-center gap-4">
                            <!-- Target vs Achievement -->
                            <div class="text-right">
                                <div class="text-xs text-slate-400">Target vs Realisasi</div>
                                <div class="font-mono text-sm font-bold text-slate-900 dark:text-white">
                                    Target: {{ obj.target_value }} {{ obj.target_unit }}
                                </div>
                                <div class="font-bold text-emerald-600 text-sm">
                                    Capaian: {{ obj.achievement_percentage || 0 }}%
                                </div>
                            </div>

                            <!-- Action Buttons -->
                            <div class="flex items-center gap-2">
                                <button
                                    v-if="!obj.is_locked"
                                    @click="openEvaluate(obj)"
                                    class="px-3 py-1.5 text-xs font-semibold rounded-lg bg-emerald-50 text-emerald-700 hover:bg-emerald-100 transition"
                                >
                                    Input Realisasi
                                </button>
                                <button
                                    v-if="!obj.is_locked"
                                    @click="pushToBsc(obj.id)"
                                    class="px-3 py-1.5 text-xs font-semibold rounded-lg bg-purple-50 text-purple-700 hover:bg-purple-100 transition"
                                >
                                    Kunci & Push BSC
                                </button>
                                <button
                                    @click="openAddProgram(obj.id)"
                                    class="px-3 py-1.5 text-xs font-semibold rounded-lg bg-blue-50 text-blue-700 hover:bg-blue-100 transition"
                                >
                                    + Program (L4)
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Work Programs (BSC Level 4) Table -->
                    <div>
                        <div class="text-xs font-bold uppercase tracking-wider text-slate-400 mb-2 flex items-center gap-1.5">
                            <ListTodo class="w-3.5 h-3.5" />
                            Program Kerja & Inisiatif Aksi (Level 4):
                        </div>

                        <div v-if="obj.work_programs && obj.work_programs.length > 0" class="overflow-x-auto">
                            <table class="w-full text-xs text-left text-slate-600 dark:text-slate-300">
                                <thead class="bg-slate-50 dark:bg-slate-900/50 text-[11px] text-slate-400">
                                    <tr>
                                        <th class="px-3 py-2">Nama Program Kerja</th>
                                        <th class="px-3 py-2">PIC Pelaksana</th>
                                        <th class="px-3 py-2">Timeline Pelaksanaan</th>
                                        <th class="px-3 py-2">Kemajuan (Progress)</th>
                                        <th class="px-3 py-2">Status</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-100 dark:divide-slate-700">
                                    <tr v-for="prog in obj.work_programs" :key="prog.id">
                                        <td class="px-3 py-2 font-medium text-slate-900 dark:text-white">
                                            {{ prog.program_name }}
                                        </td>
                                        <td class="px-3 py-2 text-slate-500">
                                            {{ prog.pic_name }}
                                        </td>
                                        <td class="px-3 py-2 font-mono text-[11px]">
                                            {{ prog.start_date }} s/d {{ prog.end_date }}
                                        </td>
                                        <td class="px-3 py-2">
                                            <div class="flex items-center gap-2">
                                                <div class="w-20 bg-slate-200 dark:bg-slate-700 rounded-full h-1.5 overflow-hidden">
                                                    <div class="bg-blue-600 h-1.5 rounded-full" :style="{ width: `${prog.progress_pct}%` }"></div>
                                                </div>
                                                <span class="font-mono font-semibold">{{ prog.progress_pct }}%</span>
                                            </div>
                                        </td>
                                        <td class="px-3 py-2">
                                            <span class="px-2 py-0.5 rounded text-[10px] font-semibold bg-blue-50 text-blue-700 uppercase">
                                                {{ prog.status }}
                                            </span>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                        <div v-else class="text-xs text-slate-400 italic py-2">
                            Belum ada program kerja yang ditambahkan di sasaran ini.
                        </div>
                    </div>
                </div>
            </div>

            <!-- Modal: Add Objective (L3) -->
            <div v-if="isObjModalOpen" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/50 backdrop-blur-xs">
                <div class="bg-white dark:bg-slate-800 rounded-2xl max-w-md w-full p-6 shadow-xl space-y-4">
                    <h3 class="font-bold text-base text-slate-900 dark:text-white">Tambah Sasaran Mutu (BSC Level 3)</h3>
                    <div class="space-y-3 text-xs">
                        <div>
                            <label class="block font-semibold mb-1">Departemen</label>
                            <select v-model="objForm.department_id" class="w-full px-3 py-1.5 bg-slate-50 dark:bg-slate-900 border rounded-lg">
                                <option v-for="d in departments" :key="d.id" :value="d.id">{{ d.name }}</option>
                            </select>
                        </div>
                        <div>
                            <label class="block font-semibold mb-1">Perspektif BSC</label>
                            <select v-model="objForm.perspective" class="w-full px-3 py-1.5 bg-slate-50 dark:bg-slate-900 border rounded-lg">
                                <option value="financial">Financial (Keuangan)</option>
                                <option value="customer">Customer / Stakeholder</option>
                                <option value="internal_process">Internal Business Process</option>
                                <option value="learning_growth">Learning & Growth</option>
                            </select>
                        </div>
                        <div>
                            <label class="block font-semibold mb-1">Nama KPI / Sasaran</label>
                            <input v-model="objForm.kpi_name" type="text" placeholder="Contoh: Zero Deviation Batch Produksi" class="w-full px-3 py-1.5 bg-slate-50 dark:bg-slate-900 border rounded-lg" />
                        </div>
                        <div class="grid grid-cols-2 gap-2">
                            <div>
                                <label class="block font-semibold mb-1">Target Angka</label>
                                <input v-model="objForm.target_value" type="number" class="w-full px-3 py-1.5 bg-slate-50 dark:bg-slate-900 border rounded-lg" />
                            </div>
                            <div>
                                <label class="block font-semibold mb-1">Satuan</label>
                                <input v-model="objForm.target_unit" type="text" placeholder="%, Hari, Dokumen" class="w-full px-3 py-1.5 bg-slate-50 dark:bg-slate-900 border rounded-lg" />
                            </div>
                        </div>
                        <div class="grid grid-cols-2 gap-2">
                            <div>
                                <label class="block font-semibold mb-1">Polaritas</label>
                                <select v-model="objForm.polarity" class="w-full px-3 py-1.5 bg-slate-50 dark:bg-slate-900 border rounded-lg">
                                    <option value="maximize">Maximize (Makin Besar Makin Baik)</option>
                                    <option value="minimize">Minimize (Makin Kecil Makin Baik)</option>
                                </select>
                            </div>
                            <div>
                                <label class="block font-semibold mb-1">Bobot (%)</label>
                                <input v-model="objForm.weight" type="number" class="w-full px-3 py-1.5 bg-slate-50 dark:bg-slate-900 border rounded-lg" />
                            </div>
                        </div>
                    </div>
                    <div class="flex justify-end gap-2 pt-3 border-t">
                        <button @click="isObjModalOpen = false" class="px-4 py-1.5 text-xs rounded-lg border">Batal</button>
                        <button @click="submitObjective" :disabled="objForm.processing" class="px-5 py-1.5 text-xs rounded-lg bg-blue-600 text-white font-semibold">Simpan Sasaran</button>
                    </div>
                </div>
            </div>

            <!-- Modal: Add Program (L4) -->
            <div v-if="isProgramModalOpen" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/50 backdrop-blur-xs">
                <div class="bg-white dark:bg-slate-800 rounded-2xl max-w-md w-full p-6 shadow-xl space-y-4">
                    <h3 class="font-bold text-base text-slate-900 dark:text-white">Tambah Program Kerja (BSC Level 4)</h3>
                    <div class="space-y-3 text-xs">
                        <div>
                            <label class="block font-semibold mb-1">Nama Inisiatif / Program</label>
                            <input v-model="programForm.program_name" type="text" placeholder="Contoh: Implementasi Checklist Sanitasi Digital" class="w-full px-3 py-1.5 bg-slate-50 dark:bg-slate-900 border rounded-lg" />
                        </div>
                        <div>
                            <label class="block font-semibold mb-1">PIC Penanggung Jawab</label>
                            <input v-model="programForm.pic_name" type="text" placeholder="Nama Supervisor / Officer" class="w-full px-3 py-1.5 bg-slate-50 dark:bg-slate-900 border rounded-lg" />
                        </div>
                        <div class="grid grid-cols-2 gap-2">
                            <div>
                                <label class="block font-semibold mb-1">Mulai</label>
                                <input v-model="programForm.start_date" type="date" class="w-full px-3 py-1.5 bg-slate-50 dark:bg-slate-900 border rounded-lg" />
                            </div>
                            <div>
                                <label class="block font-semibold mb-1">Selesai</label>
                                <input v-model="programForm.end_date" type="date" class="w-full px-3 py-1.5 bg-slate-50 dark:bg-slate-900 border rounded-lg" />
                            </div>
                        </div>
                        <div>
                            <label class="block font-semibold mb-1">Progress Kemajuan (%)</label>
                            <input v-model="programForm.progress_pct" type="number" min="0" max="100" class="w-full px-3 py-1.5 bg-slate-50 dark:bg-slate-900 border rounded-lg" />
                        </div>
                    </div>
                    <div class="flex justify-end gap-2 pt-3 border-t">
                        <button @click="isProgramModalOpen = false" class="px-4 py-1.5 text-xs rounded-lg border">Batal</button>
                        <button @click="submitProgram" :disabled="programForm.processing" class="px-5 py-1.5 text-xs rounded-lg bg-blue-600 text-white font-semibold">Simpan Program</button>
                    </div>
                </div>
            </div>

            <!-- Modal: Evaluate Actual Value -->
            <div v-if="isEvalModalOpen" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/50 backdrop-blur-xs">
                <div class="bg-white dark:bg-slate-800 rounded-2xl max-w-md w-full p-6 shadow-xl space-y-4">
                    <h3 class="font-bold text-base text-slate-900 dark:text-white">Input Realisasi Capaian KPI</h3>
                    <p class="text-xs text-slate-500">{{ evalObj?.kpi_name }} (Target: {{ evalObj?.target_value }} {{ evalObj?.target_unit }})</p>
                    <div class="space-y-3 text-xs">
                        <div>
                            <label class="block font-semibold mb-1">Bulan Periode</label>
                            <select v-model="evalForm.period_month" class="w-full px-3 py-1.5 bg-slate-50 dark:bg-slate-900 border rounded-lg">
                                <option v-for="m in 12" :key="m" :value="m">Bulan {{ m }}</option>
                            </select>
                        </div>
                        <div>
                            <label class="block font-semibold mb-1">Nilai Realisasi Aktual ({{ evalObj?.target_unit }})</label>
                            <input v-model="evalForm.actual_value" type="number" step="any" class="w-full px-3 py-1.5 bg-slate-50 dark:bg-slate-900 border rounded-lg font-mono font-bold" />
                        </div>
                        <div>
                            <label class="block font-semibold mb-1">Catatan Pencapaian</label>
                            <textarea v-model="evalForm.notes" rows="2" placeholder="Analisis deviasi atau pencapaian target..." class="w-full px-3 py-1.5 bg-slate-50 dark:bg-slate-900 border rounded-lg"></textarea>
                        </div>
                    </div>
                    <div class="flex justify-end gap-2 pt-3 border-t">
                        <button @click="isEvalModalOpen = false" class="px-4 py-1.5 text-xs rounded-lg border">Batal</button>
                        <button @click="submitEvaluate" :disabled="evalForm.processing" class="px-5 py-1.5 text-xs rounded-lg bg-emerald-600 text-white font-semibold">Simpan Realisasi</button>
                    </div>
                </div>
            </div>
        </div>
    </ModuleLayout>
</template>
