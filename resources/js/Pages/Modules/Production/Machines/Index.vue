<script setup>
import { ref } from 'vue';
import { Head, router, useForm } from '@inertiajs/vue3';
import ModuleLayout from '@/Layouts/ModuleLayout.vue';
import {
    Plus,
    Search,
    Download,
    Edit2,
    Trash2,
    Cpu,
    GitCommit,
    CheckCircle2,
    Activity,
    X,
    Timer,
    AlertTriangle,
    Factory,
} from 'lucide-vue-next';

const props = defineProps({
    module: {
        type: Object,
        required: true,
    },
    machines: {
        type: Object,
        required: true,
    },
    lines: {
        type: Array,
        default: () => [],
    },
    plants: {
        type: Array,
        default: () => [],
    },
    summary: {
        type: Object,
        default: () => ({
            total_machines: 0,
            active_machines: 0,
            total_lines: 0,
        }),
    },
    filters: {
        type: Object,
        default: () => ({
            search: '',
            line_id: 'all',
        }),
    },
});

const activeTab = ref('machines'); // 'machines' or 'lines'
const searchQuery = ref(props.filters.search || '');
const selectedLineFilter = ref(props.filters.line_id || 'all');

const isMachineModalOpen = ref(false);
const isEditingMachine = ref(false);
const editingMachineId = ref(null);

const machineForm = useForm({
    machine_code: '',
    name: '',
    production_line_id: props.lines[0]?.id || '',
    cycle_time_seconds: 1.20,
    status: 'running',
    is_active: true,
});

const isLineModalOpen = ref(false);
const isEditingLine = ref(false);
const editingLineId = ref(null);

const lineForm = useForm({
    plant_id: props.plants[0]?.id || '',
    line_number: '',
    name: '',
    design_capacity_hourly: 3000,
    is_active: true,
});

const applyFilters = () => {
    router.get('/production/master/machines', {
        search: searchQuery.value,
        line_id: selectedLineFilter.value,
    }, {
        preserveState: true,
        preserveScroll: true,
    });
};

const openCreateMachineModal = () => {
    isEditingMachine.value = false;
    editingMachineId.value = null;
    machineForm.reset();
    machineForm.clearErrors();
    machineForm.production_line_id = props.lines[0]?.id || '';
    machineForm.cycle_time_seconds = 1.20;
    machineForm.status = 'running';
    machineForm.is_active = true;
    isMachineModalOpen.value = true;
};

const openEditMachineModal = (m) => {
    isEditingMachine.value = true;
    editingMachineId.value = m.id;
    machineForm.clearErrors();
    machineForm.machine_code = m.machine_code;
    machineForm.name = m.name;
    machineForm.production_line_id = m.production_line_id;
    machineForm.cycle_time_seconds = m.cycle_time_seconds;
    machineForm.status = m.status;
    machineForm.is_active = m.is_active;
    isMachineModalOpen.value = true;
};

const submitMachineForm = () => {
    if (isEditingMachine.value) {
        machineForm.put(`/production/master/machines/${editingMachineId.value}`, {
            onSuccess: () => { isMachineModalOpen.value = false; machineForm.reset(); },
        });
    } else {
        machineForm.post('/production/master/machines', {
            onSuccess: () => { isMachineModalOpen.value = false; machineForm.reset(); },
        });
    }
};

const deleteMachine = (m) => {
    if (confirm(`Yakin ingin menghapus mesin ${m.machine_code} - ${m.name}?`)) {
        router.delete(`/production/master/machines/${m.id}`, { preserveScroll: true });
    }
};

const openCreateLineModal = () => {
    isEditingLine.value = false;
    editingLineId.value = null;
    lineForm.reset();
    lineForm.clearErrors();
    lineForm.plant_id = props.plants[0]?.id || '';
    lineForm.design_capacity_hourly = 3000;
    lineForm.is_active = true;
    isLineModalOpen.value = true;
};

const openEditLineModal = (l) => {
    isEditingLine.value = true;
    editingLineId.value = l.id;
    lineForm.clearErrors();
    lineForm.plant_id = l.plant_id;
    lineForm.line_number = l.line_number;
    lineForm.name = l.name;
    lineForm.design_capacity_hourly = l.design_capacity_hourly;
    lineForm.is_active = l.is_active;
    isLineModalOpen.value = true;
};

const submitLineForm = () => {
    if (isEditingLine.value) {
        lineForm.put(`/production/master/lines/${editingLineId.value}`, {
            onSuccess: () => { isLineModalOpen.value = false; lineForm.reset(); },
        });
    } else {
        lineForm.post('/production/master/lines', {
            onSuccess: () => { isLineModalOpen.value = false; lineForm.reset(); },
        });
    }
};

const deleteLine = (l) => {
    if (confirm(`Yakin ingin menghapus jalur produksi ${l.line_number}?`)) {
        router.delete(`/production/master/lines/${l.id}`, { preserveScroll: true });
    }
};

const machineStatusLabels = {
    running: { label: 'Beroperasi (Running)', color: 'bg-emerald-50 text-emerald-700 dark:bg-emerald-950/60 dark:text-emerald-300' },
    idle: { label: 'Siaga (Idle)', color: 'bg-blue-50 text-blue-700 dark:bg-blue-950/60 dark:text-blue-300' },
    breakdown: { label: 'Mogok (Breakdown)', color: 'bg-rose-50 text-rose-700 dark:bg-rose-950/60 dark:text-rose-300' },
    maintenance: { label: 'Perawatan (PM)', color: 'bg-amber-50 text-amber-700 dark:bg-amber-950/60 dark:text-amber-300' },
};
</script>

<template>
    <Head title="Master Mesin & Jalur Produksi - Manufaktur" />

    <ModuleLayout :module="module" :breadcrumbs="['Master Data', 'Mesin & Jalur Produksi']">
        <div class="space-y-6">
            <!-- Header Section -->
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 bg-white dark:bg-slate-900 p-6 rounded-2xl border border-slate-200 dark:border-slate-800 shadow-xs">
                <div>
                    <div class="flex items-center gap-2">
                        <span class="px-2.5 py-0.5 rounded-md text-xs font-bold uppercase tracking-wider bg-rose-500/10 text-rose-600 dark:text-rose-400 border border-rose-500/20">
                            Peralatan & Kapasitas Pabrik
                        </span>
                        <span class="text-xs text-slate-400">| World Class Manufacturing</span>
                    </div>
                    <h1 class="text-2xl font-bold text-slate-900 dark:text-white mt-1">
                        Master Mesin & Jalur Produksi
                    </h1>
                    <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">
                        Kelola inventaris mesin pabrik, Cycle Time standar per unit, pemetaan stasiun kerja, dan kapasitas desain per jam.
                    </p>
                </div>

                <div class="flex items-center gap-2.5">
                    <a
                        href="/production/master/machines/export"
                        class="flex items-center gap-1.5 px-3 py-2 rounded-xl text-xs font-semibold text-slate-600 dark:text-slate-300 bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 transition"
                    >
                        <Download class="w-4 h-4" />
                        <span>Ekspor CSV</span>
                    </a>

                    <button
                        v-if="activeTab === 'machines'"
                        @click="openCreateMachineModal()"
                        class="flex items-center gap-1.5 px-4 py-2 rounded-xl text-xs font-semibold text-white bg-rose-600 hover:bg-rose-700 shadow-xs transition"
                    >
                        <Plus class="w-4 h-4" />
                        <span>Tambah Mesin</span>
                    </button>

                    <button
                        v-else
                        @click="openCreateLineModal()"
                        class="flex items-center gap-1.5 px-4 py-2 rounded-xl text-xs font-semibold text-white bg-rose-600 hover:bg-rose-700 shadow-xs transition"
                    >
                        <Plus class="w-4 h-4" />
                        <span>Tambah Jalur Line</span>
                    </button>
                </div>
            </div>

            <!-- KPI Cards -->
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div class="bg-white dark:bg-slate-900 p-4 rounded-xl border border-slate-200 dark:border-slate-800 shadow-2xs">
                    <div class="flex items-center justify-between text-xs text-slate-500">
                        <span>Total Mesin Pabrik</span>
                        <Cpu class="w-4 h-4 text-rose-500" />
                    </div>
                    <div class="mt-2 text-2xl font-bold text-slate-900 dark:text-white">
                        {{ summary.total_machines }}
                    </div>
                    <div class="text-[11px] text-emerald-600 font-medium mt-0.5">
                        {{ summary.active_machines }} mesin aktif beroperasi
                    </div>
                </div>

                <div class="bg-white dark:bg-slate-900 p-4 rounded-xl border border-slate-200 dark:border-slate-800 shadow-2xs">
                    <div class="flex items-center justify-between text-xs text-slate-500">
                        <span>Jalur Produksi (Line)</span>
                        <GitCommit class="w-4 h-4 text-blue-500" />
                    </div>
                    <div class="mt-2 text-2xl font-bold text-blue-600 dark:text-blue-400">
                        {{ summary.total_lines }}
                    </div>
                    <div class="text-[11px] text-slate-400 mt-0.5">Stasiun Estafet Kerja</div>
                </div>

                <div class="bg-white dark:bg-slate-900 p-4 rounded-xl border border-slate-200 dark:border-slate-800 shadow-2xs">
                    <div class="flex items-center justify-between text-xs text-slate-500">
                        <span>Efektivitas OEE Acuan</span>
                        <Activity class="w-4 h-4 text-emerald-500" />
                    </div>
                    <div class="mt-2 text-2xl font-bold text-emerald-600 dark:text-emerald-400">
                        ≥ 85.0%
                    </div>
                    <div class="text-[11px] text-slate-400 mt-0.5">Target Kinerja WCM</div>
                </div>
            </div>

            <!-- Tab Navigation & Filters -->
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 bg-white dark:bg-slate-900 p-3 rounded-xl border border-slate-200 dark:border-slate-800">
                <div class="flex items-center gap-2">
                    <button
                        @click="activeTab = 'machines'"
                        :class="[
                            'px-4 py-2 rounded-xl text-xs font-bold transition',
                            activeTab === 'machines'
                                ? 'bg-rose-600 text-white shadow-xs'
                                : 'text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800',
                        ]"
                    >
                        Daftar Mesin ({{ summary.total_machines }})
                    </button>
                    <button
                        @click="activeTab = 'lines'"
                        :class="[
                            'px-4 py-2 rounded-xl text-xs font-bold transition',
                            activeTab === 'lines'
                                ? 'bg-rose-600 text-white shadow-xs'
                                : 'text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800',
                        ]"
                    >
                        Jalur Produksi / Line ({{ summary.total_lines }})
                    </button>
                </div>

                <div v-if="activeTab === 'machines'" class="flex items-center gap-2">
                    <div class="relative w-48 sm:w-64">
                        <Search class="w-3.5 h-3.5 absolute left-3 top-2.5 text-slate-400" />
                        <input
                            v-model="searchQuery"
                            type="text"
                            placeholder="Cari kode / nama mesin..."
                            class="w-full pl-8 pr-3 py-1.5 text-xs rounded-lg border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 text-slate-900 dark:text-white focus:outline-none focus:ring-1 focus:ring-rose-500"
                            @keyup.enter="applyFilters"
                        />
                    </div>
                    <select
                        v-model="selectedLineFilter"
                        @change="applyFilters"
                        class="px-2.5 py-1.5 text-xs rounded-lg border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 text-slate-900 dark:text-white"
                    >
                        <option value="all">Semua Jalur Line</option>
                        <option v-for="l in lines" :key="l.id" :value="l.id">
                            {{ l.line_number }} - {{ l.name }}
                        </option>
                    </select>
                </div>
            </div>

            <!-- Tab 1: Machines Table -->
            <div v-if="activeTab === 'machines'" class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 shadow-xs overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse text-xs">
                        <thead>
                            <tr class="bg-slate-50 dark:bg-slate-800/80 border-b border-slate-200 dark:border-slate-700 text-slate-500 uppercase tracking-wider text-[10px]">
                                <th class="py-3 px-4 font-semibold">Kode Mesin</th>
                                <th class="py-3 px-4 font-semibold">Nama Mesin</th>
                                <th class="py-3 px-4 font-semibold">Jalur (Line)</th>
                                <th class="py-3 px-4 text-right font-semibold">Cycle Time (CT)</th>
                                <th class="py-3 px-3 text-center font-semibold">Status Operasional</th>
                                <th class="py-3 px-3 text-center font-semibold">Status</th>
                                <th class="py-3 px-4 text-center font-semibold">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                            <tr
                                v-for="m in machines.data"
                                :key="m.id"
                                class="transition hover:bg-slate-50/60 dark:hover:bg-slate-800/40"
                            >
                                <td class="py-3 px-4 font-mono font-bold text-slate-900 dark:text-white whitespace-nowrap">
                                    {{ m.machine_code }}
                                </td>
                                <td class="py-3 px-4 font-semibold text-slate-800 dark:text-slate-200">
                                    {{ m.name }}
                                </td>
                                <td class="py-3 px-4 text-slate-600 dark:text-slate-400">
                                    {{ m.line?.line_number }} - {{ m.line?.name }}
                                </td>
                                <td class="py-3 px-4 text-right font-mono font-bold text-slate-900 dark:text-white whitespace-nowrap">
                                    {{ Number(m.cycle_time_seconds).toFixed(2) }} detik / pcs
                                </td>
                                <td class="py-3 px-3 text-center whitespace-nowrap">
                                    <span :class="['px-2 py-0.5 rounded-md text-[10px] font-bold', machineStatusLabels[m.status]?.color || '']">
                                        {{ machineStatusLabels[m.status]?.label || m.status }}
                                    </span>
                                </td>
                                <td class="py-3 px-3 text-center whitespace-nowrap">
                                    <span
                                        class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-semibold"
                                        :class="m.is_active ? 'bg-emerald-50 text-emerald-700 dark:bg-emerald-950/60 dark:text-emerald-300' : 'bg-slate-100 text-slate-400 dark:bg-slate-800'"
                                    >
                                        {{ m.is_active ? 'Aktif' : 'Nonaktif' }}
                                    </span>
                                </td>
                                <td class="py-3 px-4 text-center whitespace-nowrap">
                                    <div class="flex items-center justify-center gap-1.5">
                                        <button
                                            @click="openEditMachineModal(m)"
                                            class="p-1 rounded-md text-slate-500 hover:text-slate-700 dark:hover:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800"
                                            title="Ubah Mesin"
                                        >
                                            <Edit2 class="w-3.5 h-3.5" />
                                        </button>
                                        <button
                                            @click="deleteMachine(m)"
                                            class="p-1 rounded-md text-rose-500 hover:bg-rose-50 dark:hover:bg-rose-950/50"
                                            title="Hapus Mesin"
                                        >
                                            <Trash2 class="w-3.5 h-3.5" />
                                        </button>
                                    </div>
                                </td>
                            </tr>

                            <tr v-if="machines.data.length === 0">
                                <td colspan="7" class="py-8 text-center text-slate-400">
                                    Belum ada data mesin yang terdaftar.
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Tab 2: Lines Table -->
            <div v-else class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 shadow-xs overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse text-xs">
                        <thead>
                            <tr class="bg-slate-50 dark:bg-slate-800/80 border-b border-slate-200 dark:border-slate-700 text-slate-500 uppercase tracking-wider text-[10px]">
                                <th class="py-3 px-4 font-semibold">Nomor Line</th>
                                <th class="py-3 px-4 font-semibold">Nama Jalur Perakitan</th>
                                <th class="py-3 px-4 font-semibold">Lokasi Pabrik (Plant)</th>
                                <th class="py-3 px-4 text-right font-semibold">Kapasitas Desain</th>
                                <th class="py-3 px-3 text-center font-semibold">Jumlah Mesin</th>
                                <th class="py-3 px-3 text-center font-semibold">Status</th>
                                <th class="py-3 px-4 text-center font-semibold">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                            <tr
                                v-for="l in lines"
                                :key="l.id"
                                class="transition hover:bg-slate-50/60 dark:hover:bg-slate-800/40"
                            >
                                <td class="py-3 px-4 font-mono font-bold text-slate-900 dark:text-white whitespace-nowrap">
                                    {{ l.line_number }}
                                </td>
                                <td class="py-3 px-4 font-semibold text-slate-800 dark:text-slate-200">
                                    {{ l.name }}
                                </td>
                                <td class="py-3 px-4 text-slate-600 dark:text-slate-400">
                                    {{ l.plant?.name || 'Plant Utama' }}
                                </td>
                                <td class="py-3 px-4 text-right font-mono font-bold text-slate-900 dark:text-white whitespace-nowrap">
                                    {{ Number(l.design_capacity_hourly).toLocaleString('id-ID') }} unit / jam
                                </td>
                                <td class="py-3 px-3 text-center font-mono whitespace-nowrap">
                                    <span class="px-2 py-0.5 rounded bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 font-bold">
                                        {{ l.machines_count || 0 }} mesin
                                    </span>
                                </td>
                                <td class="py-3 px-3 text-center whitespace-nowrap">
                                    <span
                                        class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-semibold"
                                        :class="l.is_active ? 'bg-emerald-50 text-emerald-700 dark:bg-emerald-950/60 dark:text-emerald-300' : 'bg-slate-100 text-slate-400 dark:bg-slate-800'"
                                    >
                                        {{ l.is_active ? 'Aktif' : 'Nonaktif' }}
                                    </span>
                                </td>
                                <td class="py-3 px-4 text-center whitespace-nowrap">
                                    <div class="flex items-center justify-center gap-1.5">
                                        <button
                                            @click="openEditLineModal(l)"
                                            class="p-1 rounded-md text-slate-500 hover:text-slate-700 dark:hover:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800"
                                            title="Ubah Jalur"
                                        >
                                            <Edit2 class="w-3.5 h-3.5" />
                                        </button>
                                        <button
                                            @click="deleteLine(l)"
                                            class="p-1 rounded-md text-rose-500 hover:bg-rose-50 dark:hover:bg-rose-950/50"
                                            title="Hapus Jalur"
                                        >
                                            <Trash2 class="w-3.5 h-3.5" />
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Machine Modal -->
        <div
            v-if="isMachineModalOpen"
            class="fixed inset-0 z-50 bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4 overflow-y-auto"
            @click.self="isMachineModalOpen = false"
        >
            <div class="bg-white dark:bg-slate-900 w-full max-w-lg rounded-2xl border border-slate-200 dark:border-slate-800 shadow-2xl p-6 space-y-4">
                <div class="flex items-center justify-between pb-3 border-b border-slate-100 dark:border-slate-800">
                    <h3 class="font-bold text-slate-900 dark:text-white text-base">
                        {{ isEditingMachine ? 'Ubah Data Mesin' : 'Tambah Mesin Baru' }}
                    </h3>
                    <button @click="isMachineModalOpen = false" class="p-1 rounded-md text-slate-400 hover:text-slate-600">
                        <X class="w-4 h-4" />
                    </button>
                </div>

                <form @submit.prevent="submitMachineForm" class="space-y-4">
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">
                                Kode Mesin <span class="text-rose-500">*</span>
                            </label>
                            <input
                                v-model="machineForm.machine_code"
                                type="text"
                                placeholder="Contoh: M-EXT-01"
                                class="w-full px-3 py-2 text-xs font-mono rounded-lg border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 text-slate-900 dark:text-white focus:outline-none focus:ring-1 focus:ring-rose-500"
                                required
                            />
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">
                                Jalur Line <span class="text-rose-500">*</span>
                            </label>
                            <select
                                v-model="machineForm.production_line_id"
                                class="w-full px-3 py-2 text-xs rounded-lg border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 text-slate-900 dark:text-white focus:outline-none focus:ring-1 focus:ring-rose-500"
                                required
                            >
                                <option v-for="l in lines" :key="l.id" :value="l.id">
                                    {{ l.line_number }} - {{ l.name }}
                                </option>
                            </select>
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">
                            Nama Mesin <span class="text-rose-500">*</span>
                        </label>
                        <input
                            v-model="machineForm.name"
                            type="text"
                            placeholder="Contoh: Mesin Ekstraksi Herba Bertekanan 500L"
                            class="w-full px-3 py-2 text-xs rounded-lg border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 text-slate-900 dark:text-white focus:outline-none focus:ring-1 focus:ring-rose-500"
                            required
                        />
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">
                                Cycle Time Standar (Detik) <span class="text-rose-500">*</span>
                            </label>
                            <input
                                v-model="machineForm.cycle_time_seconds"
                                type="number"
                                step="0.01"
                                placeholder="1.20"
                                class="w-full px-3 py-2 text-xs font-mono rounded-lg border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 text-slate-900 dark:text-white focus:outline-none focus:ring-1 focus:ring-rose-500"
                                required
                            />
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">
                                Status Operasional <span class="text-rose-500">*</span>
                            </label>
                            <select
                                v-model="machineForm.status"
                                class="w-full px-3 py-2 text-xs rounded-lg border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 text-slate-900 dark:text-white focus:outline-none focus:ring-1 focus:ring-rose-500"
                                required
                            >
                                <option value="running">Beroperasi (Running)</option>
                                <option value="idle">Siaga (Idle)</option>
                                <option value="breakdown">Mogok (Breakdown)</option>
                                <option value="maintenance">Perawatan (Maintenance)</option>
                            </select>
                        </div>
                    </div>

                    <div class="flex items-center justify-end gap-2 pt-3 border-t border-slate-100 dark:border-slate-800">
                        <button
                            type="button"
                            @click="isMachineModalOpen = false"
                            class="px-4 py-2 text-xs font-semibold rounded-xl text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800 transition"
                        >
                            Batal
                        </button>
                        <button
                            type="submit"
                            :disabled="machineForm.processing"
                            class="px-5 py-2 text-xs font-semibold rounded-xl text-white bg-rose-600 hover:bg-rose-700 shadow-xs transition disabled:opacity-50"
                        >
                            {{ isEditingMachine ? 'Simpan Perubahan' : 'Buat Mesin' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Line Modal -->
        <div
            v-if="isLineModalOpen"
            class="fixed inset-0 z-50 bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4 overflow-y-auto"
            @click.self="isLineModalOpen = false"
        >
            <div class="bg-white dark:bg-slate-900 w-full max-w-md rounded-2xl border border-slate-200 dark:border-slate-800 shadow-2xl p-6 space-y-4">
                <div class="flex items-center justify-between pb-3 border-b border-slate-100 dark:border-slate-800">
                    <h3 class="font-bold text-slate-900 dark:text-white text-base">
                        {{ isEditingLine ? 'Ubah Jalur Line' : 'Tambah Jalur Line Baru' }}
                    </h3>
                    <button @click="isLineModalOpen = false" class="p-1 rounded-md text-slate-400 hover:text-slate-600">
                        <X class="w-4 h-4" />
                    </button>
                </div>

                <form @submit.prevent="submitLineForm" class="space-y-4">
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">
                            Nomor Line <span class="text-rose-500">*</span>
                        </label>
                        <input
                            v-model="lineForm.line_number"
                            type="text"
                            placeholder="Contoh: LINE-01"
                            class="w-full px-3 py-2 text-xs font-mono rounded-lg border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 text-slate-900 dark:text-white focus:outline-none focus:ring-1 focus:ring-rose-500"
                            required
                        />
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">
                            Nama Jalur Perakitan <span class="text-rose-500">*</span>
                        </label>
                        <input
                            v-model="lineForm.name"
                            type="text"
                            placeholder="Contoh: Line Ekstraksi Cair Herba"
                            class="w-full px-3 py-2 text-xs rounded-lg border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 text-slate-900 dark:text-white focus:outline-none focus:ring-1 focus:ring-rose-500"
                            required
                        />
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">
                            Lokasi Pabrik (Plant) <span class="text-rose-500">*</span>
                        </label>
                        <select
                            v-model="lineForm.plant_id"
                            class="w-full px-3 py-2 text-xs rounded-lg border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 text-slate-900 dark:text-white focus:outline-none focus:ring-1 focus:ring-rose-500"
                            required
                        >
                            <option v-for="p in plants" :key="p.id" :value="p.id">
                                {{ p.code }} - {{ p.name }}
                            </option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">
                            Kapasitas Desain (Unit / Jam) <span class="text-rose-500">*</span>
                        </label>
                        <input
                            v-model="lineForm.design_capacity_hourly"
                            type="number"
                            placeholder="3000"
                            class="w-full px-3 py-2 text-xs font-mono rounded-lg border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 text-slate-900 dark:text-white focus:outline-none focus:ring-1 focus:ring-rose-500"
                            required
                        />
                    </div>

                    <div class="flex items-center justify-end gap-2 pt-3 border-t border-slate-100 dark:border-slate-800">
                        <button
                            type="button"
                            @click="isLineModalOpen = false"
                            class="px-4 py-2 text-xs font-semibold rounded-xl text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800 transition"
                        >
                            Batal
                        </button>
                        <button
                            type="submit"
                            :disabled="lineForm.processing"
                            class="px-5 py-2 text-xs font-semibold rounded-xl text-white bg-rose-600 hover:bg-rose-700 shadow-xs transition disabled:opacity-50"
                        >
                            {{ isEditingLine ? 'Simpan Perubahan' : 'Buat Jalur' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </ModuleLayout>
</template>
