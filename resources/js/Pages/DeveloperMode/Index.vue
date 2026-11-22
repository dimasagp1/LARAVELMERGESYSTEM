<script setup>
import { ref, computed } from 'vue';
import { Head, Link, useForm, router } from '@inertiajs/vue3';
import {
    Terminal,
    Layers,
    Plus,
    Check,
    X,
    Trash2,
    Edit3,
    ArrowLeft,
    Sparkles,
    Shield,
    Database,
    Cpu,
    GitBranch,
    LayoutDashboard,
    FileSpreadsheet,
    Eye,
    MoveRight,
    CheckCircle2,
    Clock,
    FileCheck,
    AlertCircle,
    Building2,
    Sliders,
    Table,
    BarChart3,
    Boxes,
    FileText,
    Wrench,
    DollarSign,
    Users,
    Activity,
    Upload,
    PenTool,
} from 'lucide-vue-next';

const props = defineProps({
    modules: {
        type: Array,
        default: () => [],
    },
    roles: {
        type: Array,
        default: () => [],
    },
    users: {
        type: Array,
        default: () => [],
    },
    stats: {
        type: Object,
        default: () => ({}),
    },
});

const activeTab = ref('modules'); // 'modules' | 'entities' | 'workflows' | 'dashboard'

// Selected Module & Entity for designer
const selectedModuleId = ref(props.modules.length > 0 ? props.modules[0].id : null);
const selectedEntityId = ref(null);

const selectedModule = computed(() => {
    return props.modules.find((m) => m.id === selectedModuleId.value) || null;
});

const customEntities = computed(() => {
    return selectedModule.value?.custom_entities || [];
});

const selectedEntity = computed(() => {
    if (!selectedEntityId.value && customEntities.value.length > 0) {
        return customEntities.value[0];
    }
    return customEntities.value.find((e) => e.id === selectedEntityId.value) || null;
});

// Modals state
const showCreateModuleModal = ref(false);
const showCreateEntityModal = ref(false);
const showFieldModal = ref(false);
const showWorkflowModal = ref(false);
const editingField = ref(null);

// Form for New Module
const moduleForm = useForm({
    name: '',
    slug: '',
    description: '',
    icon: 'Boxes',
    color_theme: 'emerald',
    roles: [],
});

function handleModuleNameInput() {
    moduleForm.slug = moduleForm.name
        .toLowerCase()
        .replace(/[^a-z0-9]+/g, '-')
        .replace(/(^-|-$)/g, '');
}

function submitModule() {
    moduleForm.post('/developer-mode/modules', {
        onSuccess: () => {
            showCreateModuleModal.value = false;
            moduleForm.reset();
        },
    });
}

// Form for New Entity
const entityForm = useForm({
    module_id: selectedModuleId.value,
    name: '',
    slug: '',
    icon: 'Table',
    description: '',
    has_approval: true,
    has_audit_log: true,
});

function handleEntityNameInput() {
    entityForm.slug = entityForm.name
        .toLowerCase()
        .replace(/[^a-z0-9]+/g, '-')
        .replace(/(^-|-$)/g, '');
}

function openCreateEntityModal() {
    entityForm.module_id = selectedModuleId.value;
    entityForm.reset('name', 'slug', 'description');
    showCreateEntityModal.value = true;
}

function submitEntity() {
    entityForm.module_id = selectedModuleId.value;
    entityForm.post('/developer-mode/entities', {
        onSuccess: () => {
            showCreateEntityModal.value = false;
            entityForm.reset();
        },
    });
}

// Form for Custom Field
const fieldForm = useForm({
    id: null,
    label: '',
    field_key: '',
    field_type: 'text',
    options_text: '',
    options_json: [],
    validation_rules: 'nullable|string|max:255',
    is_required: false,
    show_in_table: true,
    sort_order: 10,
});

function handleFieldLabelInput() {
    if (!editingField.value) {
        fieldForm.field_key = fieldForm.label
            .toLowerCase()
            .replace(/[^a-z0-9]+/g, '_')
            .replace(/(^-|-$)/g, '');
    }
}

function openAddFieldModal() {
    editingField.value = null;
    fieldForm.reset();
    fieldForm.field_type = 'text';
    fieldForm.validation_rules = 'nullable|string|max:255';
    fieldForm.show_in_table = true;
    fieldForm.sort_order = (selectedEntity.value?.fields?.length || 0) + 1;
    showFieldModal.value = true;
}

function openEditFieldModal(field) {
    editingField.value = field;
    fieldForm.id = field.id;
    fieldForm.label = field.label;
    fieldForm.field_key = field.field_key;
    fieldForm.field_type = field.field_type;
    fieldForm.options_json = field.options_json || [];
    fieldForm.options_text = Array.isArray(field.options_json) ? field.options_json.join(', ') : '';
    fieldForm.validation_rules = field.validation_rules || '';
    fieldForm.is_required = Boolean(field.is_required);
    fieldForm.show_in_table = Boolean(field.show_in_table);
    fieldForm.sort_order = field.sort_order;
    showFieldModal.value = true;
}

function submitField() {
    if (fieldForm.field_type === 'select' && fieldForm.options_text) {
        fieldForm.options_json = fieldForm.options_text
            .split(',')
            .map((item) => item.trim())
            .filter((item) => item.length > 0);
    }

    if (!selectedEntity.value) return;

    fieldForm.post(`/developer-mode/entities/${selectedEntity.value.id}/fields`, {
        onSuccess: () => {
            showFieldModal.value = false;
            fieldForm.reset();
        },
    });
}

function deleteField(fieldId) {
    if (confirm('Yakin ingin menghapus field ini? Data terkait di record lama mungkin tidak dapat ditampilkan.')) {
        router.delete(`/developer-mode/fields/${fieldId}`);
    }
}

// Workflow Builder Form
const workflowForm = useForm({
    name: 'Standard Approval Workflow',
    code: 'WF-STD',
    target_model: 'custom_records',
    min_amount: 0,
    max_amount: null,
    steps: [
        {
            step_name: 'Verifikasi Supervisor',
            approver_type: 'role',
            approver_role_id: props.roles.length > 0 ? props.roles[0].id : null,
            approver_user_id: null,
            sla_hours: 24,
            requires_digital_signature: false,
        },
        {
            step_name: 'Persetujuan Head of Dept',
            approver_type: 'department_head',
            approver_role_id: null,
            approver_user_id: null,
            sla_hours: 48,
            requires_digital_signature: true,
        },
    ],
});

function addWorkflowStep() {
    workflowForm.steps.push({
        step_name: `Tahap Persetujuan ${workflowForm.steps.length + 1}`,
        approver_type: 'role',
        approver_role_id: props.roles.length > 0 ? props.roles[0].id : null,
        approver_user_id: null,
        sla_hours: 24,
        requires_digital_signature: false,
    });
}

function removeWorkflowStep(index) {
    if (workflowForm.steps.length > 1) {
        workflowForm.steps.splice(index, 1);
    }
}

function submitWorkflow() {
    if (!selectedModule.value) return;
    workflowForm.post(`/developer-mode/modules/${selectedModule.value.id}/workflows`, {
        onSuccess: () => {
            alert('Workflow berhasil disimpan!');
        },
    });
}

// Dashboard Builder Form
const dashboardForm = useForm({
    widgets: [
        {
            title: 'Total Transaksi',
            widget_type: 'kpi',
            grid_span: 1,
            data_source_config_json: { metric: 'count', trend: '+12%' },
        },
        {
            title: 'Tren Dokumen Bulanan',
            widget_type: 'chart',
            grid_span: 2,
            data_source_config_json: { chart_type: 'bar', period: 'MTD' },
        },
        {
            title: 'Menunggu Persetujuan Anda',
            widget_type: 'inbox',
            grid_span: 1,
            data_source_config_json: {},
        },
        {
            title: 'Daftar Entitas Terkini',
            widget_type: 'table',
            grid_span: 2,
            data_source_config_json: { limit: 5 },
        },
    ],
});

function addWidget(type) {
    const titleMap = {
        kpi: 'Ringkasan Metrik Baru',
        chart: 'Grafik Analisis',
        table: 'Tabel Rekaman',
        inbox: 'Inbox Approval Modul',
    };
    dashboardForm.widgets.push({
        title: titleMap[type] || 'Widget Baru',
        widget_type: type,
        grid_span: type === 'chart' || type === 'table' ? 2 : 1,
        data_source_config_json: {},
    });
}

function removeWidget(idx) {
    dashboardForm.widgets.splice(idx, 1);
}

function submitWidgets() {
    if (!selectedModule.value) return;
    dashboardForm.post(`/developer-mode/modules/${selectedModule.value.id}/widgets`, {
        onSuccess: () => {
            alert('Tata letak widget dashboard berhasil disimpan!');
        },
    });
}

// Color palettes for modules
const colorThemes = [
    { key: 'emerald', name: 'Emerald Green', bg: 'bg-emerald-500', text: 'text-emerald-700' },
    { key: 'blue', name: 'Ocean Blue', bg: 'bg-blue-500', text: 'text-blue-700' },
    { key: 'amber', name: 'Warm Amber', bg: 'bg-amber-500', text: 'text-amber-700' },
    { key: 'violet', name: 'Royal Violet', bg: 'bg-violet-500', text: 'text-violet-700' },
    { key: 'rose', name: 'Rose Red', bg: 'bg-rose-500', text: 'text-rose-700' },
    { key: 'teal', name: 'Cyan Teal', bg: 'bg-teal-500', text: 'text-teal-700' },
    { key: 'indigo', name: 'Deep Indigo', bg: 'bg-indigo-500', text: 'text-indigo-700' },
];

const availableIcons = [
    'Boxes',
    'Table',
    'FileSpreadsheet',
    'Cpu',
    'Shield',
    'Wrench',
    'DollarSign',
    'Users',
    'Building2',
    'Activity',
    'Sparkles',
    'FileText',
];
</script>

<template>
    <div class="min-h-screen bg-slate-900 text-slate-100 font-sans selection:bg-emerald-500 selection:text-white">
        <Head title="Developer Mode & No-Code Module Builder" />

        <!-- Top Dark Developer Bar -->
        <header class="sticky top-0 z-40 bg-slate-950/90 backdrop-blur border-b border-slate-800">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex items-center justify-between h-16">
                    <div class="flex items-center gap-3">
                        <Link
                            href="/"
                            class="p-2 rounded-xl bg-slate-900 border border-slate-800 hover:border-slate-700 text-slate-400 hover:text-white transition"
                            title="Kembali ke App Launcher"
                        >
                            <ArrowLeft class="w-4 h-4" />
                        </Link>
                        <div class="flex items-center gap-2">
                            <div class="w-9 h-9 rounded-xl bg-emerald-500/10 border border-emerald-500/30 text-emerald-400 flex items-center justify-center font-mono font-bold text-sm shadow-sm">
                                <Terminal class="w-4 h-4" />
                            </div>
                            <div>
                                <div class="flex items-center gap-2">
                                    <h1 class="font-bold text-base text-white tracking-tight">No-Code Module Builder</h1>
                                    <span class="text-[10px] font-mono font-bold px-2 py-0.5 rounded bg-emerald-500/20 text-emerald-300 border border-emerald-500/30">
                                        SUPERADMIN / ARCHITECT
                                    </span>
                                </div>
                                <p class="text-xs text-slate-400">Konfigurasi Modul, Entitas Dinamis, Approval, dan Widget tanpa coding</p>
                            </div>
                        </div>
                    </div>

                    <!-- Header Stats & Actions -->
                    <div class="flex items-center gap-4">
                        <div class="hidden md:flex items-center gap-6 text-xs text-slate-400 font-mono">
                            <div>Modul: <strong class="text-white">{{ stats.total_modules }}</strong> ({{ stats.custom_modules }} kustom)</div>
                            <div>Entitas: <strong class="text-white">{{ stats.total_entities }}</strong></div>
                            <div>Workflows: <strong class="text-white">{{ stats.total_workflows }}</strong></div>
                        </div>

                        <Link
                            href="/"
                            class="px-3 py-1.5 rounded-lg bg-emerald-600 hover:bg-emerald-500 text-white text-xs font-semibold flex items-center gap-1.5 transition shadow-sm"
                        >
                            <LayoutDashboard class="w-3.5 h-3.5" />
                            <span>Buka ERP Launcher</span>
                        </Link>
                    </div>
                </div>

                <!-- Navigation Tabs -->
                <div class="flex items-center gap-2 border-t border-slate-800/80 -mb-px">
                    <button
                        @click="activeTab = 'modules'"
                        :class="[
                            'flex items-center gap-2 px-4 py-3 text-xs font-medium border-b-2 transition',
                            activeTab === 'modules'
                                ? 'border-emerald-500 text-emerald-400 font-semibold'
                                : 'border-transparent text-slate-400 hover:text-slate-200',
                        ]"
                    >
                        <Boxes class="w-4 h-4" />
                        <span>1. Module Builder</span>
                    </button>
                    <button
                        @click="activeTab = 'entities'"
                        :class="[
                            'flex items-center gap-2 px-4 py-3 text-xs font-medium border-b-2 transition',
                            activeTab === 'entities'
                                ? 'border-emerald-500 text-emerald-400 font-semibold'
                                : 'border-transparent text-slate-400 hover:text-slate-200',
                        ]"
                    >
                        <Table class="w-4 h-4" />
                        <span>2. Entity & Field Designer</span>
                    </button>
                    <button
                        @click="activeTab = 'workflows'"
                        :class="[
                            'flex items-center gap-2 px-4 py-3 text-xs font-medium border-b-2 transition',
                            activeTab === 'workflows'
                                ? 'border-emerald-500 text-emerald-400 font-semibold'
                                : 'border-transparent text-slate-400 hover:text-slate-200',
                        ]"
                    >
                        <GitBranch class="w-4 h-4" />
                        <span>3. Workflow & Approval Matrix</span>
                    </button>
                    <button
                        @click="activeTab = 'dashboard'"
                        :class="[
                            'flex items-center gap-2 px-4 py-3 text-xs font-medium border-b-2 transition',
                            activeTab === 'dashboard'
                                ? 'border-emerald-500 text-emerald-400 font-semibold'
                                : 'border-transparent text-slate-400 hover:text-slate-200',
                        ]"
                    >
                        <BarChart3 class="w-4 h-4" />
                        <span>4. Dashboard Overview Builder</span>
                    </button>
                </div>
            </div>
        </header>

        <!-- Main Content Area -->
        <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
            <!-- TAB 1: MODULE BUILDER -->
            <div v-if="activeTab === 'modules'" class="space-y-6">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                    <div>
                        <h2 class="text-lg font-bold text-white flex items-center gap-2">
                            <span>Daftar Modul ERP</span>
                            <span class="text-xs font-mono px-2 py-0.5 rounded bg-slate-800 text-slate-300">
                                {{ modules.length }} Modul Terdaftar
                            </span>
                        </h2>
                        <p class="text-xs text-slate-400">
                            Modul inti (Core) dan modul kustom (No-Code). Setiap modul kustom otomatis muncul di App Launcher & sistem perizinan Spatie RBAC.
                        </p>
                    </div>
                    <button
                        @click="showCreateModuleModal = true"
                        class="px-4 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-white font-semibold text-xs flex items-center gap-2 shadow-lg shadow-emerald-600/20 transition self-start"
                    >
                        <Plus class="w-4 h-4" />
                        <span>+ Tambah Modul Baru</span>
                    </button>
                </div>

                <!-- Modules Grid -->
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
                    <div
                        v-for="mod in modules"
                        :key="mod.id"
                        class="bg-slate-950/80 border border-slate-800 hover:border-slate-700 rounded-2xl p-5 flex flex-col justify-between transition group relative overflow-hidden"
                    >
                        <div class="space-y-3">
                            <div class="flex items-start justify-between">
                                <div class="flex items-center gap-3">
                                    <div
                                        :class="[
                                            'w-10 h-10 rounded-xl flex items-center justify-center font-bold text-white shadow-md',
                                            mod.color_theme === 'emerald' ? 'bg-emerald-600' : '',
                                            mod.color_theme === 'amber' ? 'bg-amber-600' : '',
                                            mod.color_theme === 'blue' ? 'bg-blue-600' : '',
                                            mod.color_theme === 'violet' ? 'bg-violet-600' : '',
                                            mod.color_theme === 'rose' ? 'bg-rose-600' : '',
                                            mod.color_theme === 'teal' ? 'bg-teal-600' : '',
                                            mod.color_theme === 'cyan' ? 'bg-cyan-600' : '',
                                            mod.color_theme === 'indigo' ? 'bg-indigo-600' : '',
                                        ]"
                                    >
                                        <Boxes class="w-5 h-5" />
                                    </div>
                                    <div>
                                        <h3 class="font-bold text-sm text-white group-hover:text-emerald-400 transition">
                                            {{ mod.name }}
                                        </h3>
                                        <span class="text-[11px] font-mono text-slate-400">/modules/{{ mod.slug }}</span>
                                    </div>
                                </div>

                                <span
                                    :class="[
                                        'text-[10px] font-semibold uppercase tracking-wider px-2 py-0.5 rounded border',
                                        mod.is_custom
                                            ? 'bg-amber-500/10 text-amber-300 border-amber-500/30'
                                            : 'bg-slate-800 text-slate-300 border-slate-700',
                                    ]"
                                >
                                    {{ mod.is_custom ? 'No-Code Custom' : 'Core System' }}
                                </span>
                            </div>

                            <p class="text-xs text-slate-400 line-clamp-2 leading-relaxed">
                                {{ mod.description || 'Modul terintegrasi HerbaTech Unified ERP.' }}
                            </p>

                            <div class="flex items-center gap-4 text-xs font-mono text-slate-400 pt-2 border-t border-slate-800/80">
                                <div>Entitas: <strong class="text-slate-200">{{ mod.custom_entities?.length || 0 }}</strong></div>
                                <div>Workflows: <strong class="text-slate-200">{{ mod.approval_workflows?.length || 0 }}</strong></div>
                                <div>Widgets: <strong class="text-slate-200">{{ mod.custom_widgets?.length || 0 }}</strong></div>
                            </div>
                        </div>

                        <div class="pt-4 mt-4 flex items-center justify-between border-t border-slate-800/60">
                            <button
                                @click="
                                    selectedModuleId = mod.id;
                                    activeTab = 'entities';
                                "
                                class="text-xs text-emerald-400 hover:text-emerald-300 font-medium flex items-center gap-1"
                            >
                                <Sliders class="w-3.5 h-3.5" />
                                <span>Kelola Entitas & Form</span>
                            </button>

                            <Link
                                :href="`/modules/${mod.slug}`"
                                class="text-xs text-slate-400 hover:text-white flex items-center gap-1 transition"
                            >
                                <span>Preview Modul</span>
                                <MoveRight class="w-3.5 h-3.5" />
                            </Link>
                        </div>
                    </div>
                </div>
            </div>

            <!-- TAB 2: ENTITY & FIELD DESIGNER -->
            <div v-if="activeTab === 'entities'" class="space-y-6">
                <!-- Module & Entity Selector Bar -->
                <div class="bg-slate-950 border border-slate-800 rounded-2xl p-4 flex flex-col md:flex-row md:items-center justify-between gap-4">
                    <div class="flex items-center gap-3">
                        <label class="text-xs font-bold text-slate-400 uppercase tracking-wider">Modul Terpilih:</label>
                        <select
                            v-model="selectedModuleId"
                            class="bg-slate-900 border border-slate-700 text-white text-xs rounded-xl px-3 py-2 focus:ring-emerald-500 focus:border-emerald-500"
                        >
                            <option v-for="m in modules" :key="m.id" :value="m.id">
                                {{ m.name }} ({{ m.slug }}) {{ m.is_custom ? '★ Kustom' : '' }}
                            </option>
                        </select>
                    </div>

                    <div class="flex items-center gap-2">
                        <button
                            @click="openCreateEntityModal"
                            class="px-3.5 py-1.5 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-white font-semibold text-xs flex items-center gap-1.5 transition"
                        >
                            <Plus class="w-4 h-4" />
                            <span>+ Buat Entitas Baru</span>
                        </button>
                    </div>
                </div>

                <!-- Two-Column Layout: Entities List & Field Designer -->
                <div class="grid grid-cols-1 lg:grid-cols-4 gap-6">
                    <!-- Left: Entity Navigation -->
                    <div class="lg:col-span-1 bg-slate-950 border border-slate-800 rounded-2xl p-4 space-y-3">
                        <h3 class="text-xs font-bold uppercase tracking-wider text-slate-400 flex items-center justify-between">
                            <span>Entitas Data</span>
                            <span class="text-emerald-400 font-mono">{{ customEntities.length }}</span>
                        </h3>

                        <div v-if="customEntities.length === 0" class="text-center py-8 text-slate-500 text-xs">
                            Belum ada entitas kustom di modul ini.
                            <button
                                @click="openCreateEntityModal"
                                class="mt-2 block mx-auto text-emerald-400 underline font-semibold"
                            >
                                Buat Entitas Pertama
                            </button>
                        </div>

                        <div v-else class="space-y-1.5">
                            <button
                                v-for="ent in customEntities"
                                :key="ent.id"
                                @click="selectedEntityId = ent.id"
                                :class="[
                                    'w-full text-left px-3 py-2.5 rounded-xl text-xs flex items-center justify-between transition',
                                    (selectedEntity?.id === ent.id)
                                        ? 'bg-emerald-600 text-white font-bold shadow-md shadow-emerald-600/20'
                                        : 'bg-slate-900/60 hover:bg-slate-900 text-slate-300',
                                ]"
                            >
                                <div class="flex items-center gap-2 truncate">
                                    <Table class="w-3.5 h-3.5 shrink-0" />
                                    <span class="truncate">{{ ent.name }}</span>
                                </div>
                                <span class="text-[10px] opacity-80 font-mono">{{ ent.fields?.length || 0 }} fld</span>
                            </button>
                        </div>
                    </div>

                    <!-- Right: Field Designer & Table Config -->
                    <div class="lg:col-span-3 space-y-6">
                        <div v-if="!selectedEntity" class="bg-slate-950 border border-slate-800 rounded-2xl p-12 text-center text-slate-400">
                            Pilih atau buat entitas data di kolom kiri untuk mendesain form dan field.
                        </div>

                        <div v-else class="space-y-6">
                            <!-- Selected Entity Header Card -->
                            <div class="bg-slate-950 border border-slate-800 rounded-2xl p-5 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                                <div>
                                    <div class="flex items-center gap-2">
                                        <h3 class="text-base font-bold text-white">{{ selectedEntity.name }}</h3>
                                        <span class="font-mono text-xs text-slate-400 bg-slate-900 px-2 py-0.5 rounded border border-slate-800">
                                            slug: {{ selectedEntity.slug }}
                                        </span>
                                        <span v-if="selectedEntity.has_approval" class="text-[10px] font-semibold bg-emerald-500/20 text-emerald-300 border border-emerald-500/30 px-1.5 py-0.5 rounded">
                                            Approval Workflow
                                        </span>
                                    </div>
                                    <p class="text-xs text-slate-400 mt-1">
                                        {{ selectedEntity.description || 'Entitas transaksi kustom tersimpan di custom_records.' }}
                                    </p>
                                </div>

                                <div class="flex items-center gap-2">
                                    <Link
                                        :href="`/modules/${selectedModule.slug}/entities/${selectedEntity.slug}`"
                                        class="px-3 py-1.5 rounded-xl bg-slate-900 border border-slate-700 hover:border-slate-600 text-slate-300 hover:text-white text-xs font-medium flex items-center gap-1.5 transition"
                                    >
                                        <Eye class="w-3.5 h-3.5" />
                                        <span>Buka Tabel Data</span>
                                    </Link>
                                    <button
                                        @click="openAddFieldModal"
                                        class="px-3.5 py-1.5 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-white font-semibold text-xs flex items-center gap-1.5 transition shadow-sm"
                                    >
                                        <Plus class="w-4 h-4" />
                                        <span>+ Tambah Field Baru</span>
                                    </button>
                                </div>
                            </div>

                            <!-- Fields Table -->
                            <div class="bg-slate-950 border border-slate-800 rounded-2xl overflow-hidden shadow-xs">
                                <div class="px-5 py-4 border-b border-slate-800 flex items-center justify-between">
                                    <h4 class="text-xs font-bold text-white uppercase tracking-wider">
                                        Field Metadata Schema ({{ selectedEntity.fields?.length || 0 }} Field)
                                    </h4>
                                    <span class="text-[11px] text-slate-400">
                                        Urutan field menentukan tampilan di Dynamic Form & Data Table
                                    </span>
                                </div>

                                <div class="overflow-x-auto">
                                    <table class="w-full text-left text-xs text-slate-300">
                                        <thead class="bg-slate-900 text-slate-400 uppercase font-mono text-[10px] border-b border-slate-800">
                                            <tr>
                                                <th class="px-4 py-3">Urutan</th>
                                                <th class="px-4 py-3">Label Field</th>
                                                <th class="px-4 py-3">Field Key (JSON)</th>
                                                <th class="px-4 py-3">Tipe Data</th>
                                                <th class="px-4 py-3 text-center">Wajib</th>
                                                <th class="px-4 py-3 text-center">Di Tabel</th>
                                                <th class="px-4 py-3 text-right">Aksi</th>
                                            </tr>
                                        </thead>
                                        <tbody class="divide-y divide-slate-800/80">
                                            <tr
                                                v-for="field in selectedEntity.fields"
                                                :key="field.id"
                                                class="hover:bg-slate-900/50 transition"
                                            >
                                                <td class="px-4 py-3 font-mono text-slate-400">{{ field.sort_order }}</td>
                                                <td class="px-4 py-3 font-semibold text-white">{{ field.label }}</td>
                                                <td class="px-4 py-3 font-mono text-emerald-400">{{ field.field_key }}</td>
                                                <td class="px-4 py-3">
                                                    <span class="px-2 py-0.5 rounded text-[10px] font-mono font-bold bg-slate-900 border border-slate-700 text-slate-300 uppercase">
                                                        {{ field.field_type }}
                                                    </span>
                                                </td>
                                                <td class="px-4 py-3 text-center">
                                                    <span v-if="field.is_required" class="text-emerald-400 font-bold">✓</span>
                                                    <span v-else class="text-slate-600">-</span>
                                                </td>
                                                <td class="px-4 py-3 text-center">
                                                    <span v-if="field.show_in_table" class="text-blue-400 font-bold">✓</span>
                                                    <span v-else class="text-slate-600">-</span>
                                                </td>
                                                <td class="px-4 py-3 text-right">
                                                    <div class="flex items-center justify-end gap-2">
                                                        <button
                                                            @click="openEditFieldModal(field)"
                                                            class="p-1 rounded bg-slate-900 hover:bg-slate-800 text-slate-400 hover:text-white"
                                                            title="Edit Field"
                                                        >
                                                            <Edit3 class="w-3.5 h-3.5" />
                                                        </button>
                                                        <button
                                                            @click="deleteField(field.id)"
                                                            class="p-1 rounded bg-slate-900 hover:bg-rose-950 text-slate-400 hover:text-rose-400"
                                                            title="Hapus Field"
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
                    </div>
                </div>
            </div>

            <!-- TAB 3: WORKFLOW & APPROVAL BUILDER -->
            <div v-if="activeTab === 'workflows'" class="space-y-6">
                <div class="bg-slate-950 border border-slate-800 rounded-2xl p-5 flex flex-col md:flex-row md:items-center justify-between gap-4">
                    <div>
                        <h2 class="text-base font-bold text-white flex items-center gap-2">
                            <GitBranch class="w-4 h-4 text-emerald-400" />
                            <span>Workflow & Multi-Stage Approval Matrix</span>
                        </h2>
                        <p class="text-xs text-slate-400">
                            Konfigurasi jenjang persetujuan (Supervisor > Dept Head > Direktur) berbasis Role atau User dengan SLA dan Tanda Tangan Digital.
                        </p>
                    </div>

                    <div class="flex items-center gap-3">
                        <label class="text-xs font-bold text-slate-400">Modul:</label>
                        <select
                            v-model="selectedModuleId"
                            class="bg-slate-900 border border-slate-700 text-white text-xs rounded-xl px-3 py-2"
                        >
                            <option v-for="m in modules" :key="m.id" :value="m.id">{{ m.name }}</option>
                        </select>
                    </div>
                </div>

                <div class="bg-slate-950 border border-slate-800 rounded-2xl p-6 space-y-6">
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div>
                            <label class="block text-xs font-semibold text-slate-300 mb-1">Nama Alur Persetujuan</label>
                            <input
                                v-model="workflowForm.name"
                                type="text"
                                class="w-full bg-slate-900 border border-slate-800 text-white rounded-xl px-3 py-2 text-xs focus:ring-emerald-500"
                            />
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-300 mb-1">Kode Workflow</label>
                            <input
                                v-model="workflowForm.code"
                                type="text"
                                class="w-full bg-slate-900 border border-slate-800 text-white rounded-xl px-3 py-2 text-xs font-mono focus:ring-emerald-500"
                            />
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-300 mb-1">Ambang Batas Nominal (Min Rp)</label>
                            <input
                                v-model="workflowForm.min_amount"
                                type="number"
                                class="w-full bg-slate-900 border border-slate-800 text-white rounded-xl px-3 py-2 text-xs focus:ring-emerald-500"
                            />
                        </div>
                    </div>

                    <!-- Steps Timeline -->
                    <div class="space-y-4">
                        <div class="flex items-center justify-between border-b border-slate-800 pb-2">
                            <h3 class="text-xs font-bold uppercase tracking-wider text-slate-300">
                                Tahapan Persetujuan Berjenjang (Sequential Pipeline)
                            </h3>
                            <button
                                @click="addWorkflowStep"
                                class="text-xs font-semibold text-emerald-400 hover:text-emerald-300 flex items-center gap-1"
                            >
                                <Plus class="w-3.5 h-3.5" />
                                <span>+ Tambah Level / Tahap</span>
                            </button>
                        </div>

                        <div class="space-y-3">
                            <div
                                v-for="(step, idx) in workflowForm.steps"
                                :key="idx"
                                class="bg-slate-900/80 border border-slate-800 rounded-xl p-4 flex flex-col md:flex-row md:items-center justify-between gap-4"
                            >
                                <div class="flex items-center gap-3">
                                    <div class="w-7 h-7 rounded-lg bg-emerald-500/20 text-emerald-400 font-mono font-bold text-xs flex items-center justify-center shrink-0">
                                        {{ idx + 1 }}
                                    </div>
                                    <div class="space-y-1">
                                        <input
                                            v-model="step.step_name"
                                            type="text"
                                            class="bg-slate-950 border border-slate-800 text-white text-xs rounded-lg px-2.5 py-1 font-semibold"
                                            placeholder="Nama Tahap"
                                        />
                                        <div class="flex items-center gap-2 text-[11px] text-slate-400">
                                            <span>SLA Target:</span>
                                            <input
                                                v-model="step.sla_hours"
                                                type="number"
                                                class="w-16 bg-slate-950 border border-slate-800 text-white text-[11px] rounded px-1.5 py-0.5 text-center font-mono"
                                            />
                                            <span>Jam</span>
                                        </div>
                                    </div>
                                </div>

                                <div class="flex flex-wrap items-center gap-3">
                                    <div>
                                        <select
                                            v-model="step.approver_type"
                                            class="bg-slate-950 border border-slate-800 text-white text-xs rounded-lg px-2.5 py-1.5"
                                        >
                                            <option value="role">Berdasarkan Role</option>
                                            <option value="user">Spesifik User</option>
                                            <option value="department_head">Kepala Departemen</option>
                                        </select>
                                    </div>

                                    <div v-if="step.approver_type === 'role'">
                                        <select
                                            v-model="step.approver_role_id"
                                            class="bg-slate-950 border border-slate-800 text-white text-xs rounded-lg px-2.5 py-1.5"
                                        >
                                            <option v-for="r in roles" :key="r.id" :value="r.id">{{ r.name }}</option>
                                        </select>
                                    </div>

                                    <div v-if="step.approver_type === 'user'">
                                        <select
                                            v-model="step.approver_user_id"
                                            class="bg-slate-950 border border-slate-800 text-white text-xs rounded-lg px-2.5 py-1.5"
                                        >
                                            <option v-for="u in users" :key="u.id" :value="u.id">{{ u.name }} ({{ u.email }})</option>
                                        </select>
                                    </div>

                                    <label class="flex items-center gap-1.5 text-xs text-slate-300 cursor-pointer">
                                        <input
                                            v-model="step.requires_digital_signature"
                                            type="checkbox"
                                            class="rounded bg-slate-950 border-slate-800 text-emerald-600 focus:ring-emerald-500"
                                        />
                                        <span>Wajib TTD Digital</span>
                                    </label>

                                    <button
                                        @click="removeWorkflowStep(idx)"
                                        class="p-1 text-slate-500 hover:text-rose-400 transition"
                                        title="Hapus Tahap"
                                    >
                                        <Trash2 class="w-4 h-4" />
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="pt-4 flex justify-end">
                        <button
                            @click="submitWorkflow"
                            class="px-5 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-white font-semibold text-xs flex items-center gap-2 shadow-lg shadow-emerald-600/20 transition"
                        >
                            <CheckCircle2 class="w-4 h-4" />
                            <span>Simpan Konfigurasi Workflow</span>
                        </button>
                    </div>
                </div>
            </div>

            <!-- TAB 4: DASHBOARD OVERVIEW BUILDER -->
            <div v-if="activeTab === 'dashboard'" class="space-y-6">
                <div class="bg-slate-950 border border-slate-800 rounded-2xl p-5 flex flex-col md:flex-row md:items-center justify-between gap-4">
                    <div>
                        <h2 class="text-base font-bold text-white flex items-center gap-2">
                            <BarChart3 class="w-4 h-4 text-emerald-400" />
                            <span>Overview Dashboard Widget Layout</span>
                        </h2>
                        <p class="text-xs text-slate-400">
                            Atur tata letak widget metrik KPI, grafik ApexCharts, dan inbox approval untuk modul terpilih.
                        </p>
                    </div>

                    <div class="flex items-center gap-3">
                        <label class="text-xs font-bold text-slate-400">Modul:</label>
                        <select
                            v-model="selectedModuleId"
                            class="bg-slate-900 border border-slate-700 text-white text-xs rounded-xl px-3 py-2"
                        >
                            <option v-for="m in modules" :key="m.id" :value="m.id">{{ m.name }}</option>
                        </select>
                    </div>
                </div>

                <!-- Add Widget Bar -->
                <div class="flex flex-wrap items-center gap-3">
                    <span class="text-xs font-bold text-slate-400 uppercase tracking-wider">Tambah Widget:</span>
                    <button
                        @click="addWidget('kpi')"
                        class="px-3 py-1.5 rounded-xl bg-slate-900 border border-slate-800 hover:border-slate-700 text-slate-300 text-xs font-medium flex items-center gap-1.5 transition"
                    >
                        <Activity class="w-3.5 h-3.5 text-emerald-400" />
                        <span>+ Kartu KPI</span>
                    </button>
                    <button
                        @click="addWidget('chart')"
                        class="px-3 py-1.5 rounded-xl bg-slate-900 border border-slate-800 hover:border-slate-700 text-slate-300 text-xs font-medium flex items-center gap-1.5 transition"
                    >
                        <BarChart3 class="w-3.5 h-3.5 text-blue-400" />
                        <span>+ ApexChart Card</span>
                    </button>
                    <button
                        @click="addWidget('table')"
                        class="px-3 py-1.5 rounded-xl bg-slate-900 border border-slate-800 hover:border-slate-700 text-slate-300 text-xs font-medium flex items-center gap-1.5 transition"
                    >
                        <Table class="w-3.5 h-3.5 text-amber-400" />
                        <span>+ Tabel Data Ringkas</span>
                    </button>
                    <button
                        @click="addWidget('inbox')"
                        class="px-3 py-1.5 rounded-xl bg-slate-900 border border-slate-800 hover:border-slate-700 text-slate-300 text-xs font-medium flex items-center gap-1.5 transition"
                    >
                        <FileCheck class="w-3.5 h-3.5 text-violet-400" />
                        <span>+ Inbox Persetujuan</span>
                    </button>
                </div>

                <!-- Grid Preview & Reorder -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
                    <div
                        v-for="(w, idx) in dashboardForm.widgets"
                        :key="idx"
                        :class="[
                            'bg-slate-950 border border-slate-800 rounded-2xl p-4 flex flex-col justify-between relative',
                            w.grid_span === 2 ? 'md:col-span-2' : '',
                            w.grid_span === 3 ? 'md:col-span-3' : '',
                        ]"
                    >
                        <div class="space-y-3">
                            <div class="flex items-center justify-between">
                                <span class="text-[10px] font-mono uppercase tracking-wider font-bold px-2 py-0.5 rounded bg-slate-900 border border-slate-800 text-slate-400">
                                    {{ w.widget_type }} (span {{ w.grid_span }})
                                </span>
                                <button
                                    @click="removeWidget(idx)"
                                    class="text-slate-500 hover:text-rose-400"
                                >
                                    <Trash2 class="w-3.5 h-3.5" />
                                </button>
                            </div>

                            <div>
                                <input
                                    v-model="w.title"
                                    type="text"
                                    class="w-full bg-slate-900 border border-slate-800 text-white font-bold text-xs rounded-lg px-2.5 py-1.5"
                                    placeholder="Judul Widget"
                                />
                            </div>

                            <div class="flex items-center gap-3 text-xs text-slate-400">
                                <span>Lebar Grid:</span>
                                <select
                                    v-model="w.grid_span"
                                    class="bg-slate-900 border border-slate-800 text-white text-xs rounded px-2 py-1"
                                >
                                    <option :value="1">1 Kolom (1/3)</option>
                                    <option :value="2">2 Kolom (2/3)</option>
                                    <option :value="3">Full Width (3/3)</option>
                                </select>
                            </div>
                        </div>

                        <div class="pt-4 mt-4 border-t border-slate-800/80 text-[11px] text-slate-500 flex items-center justify-between">
                            <span>Posisi: #{{ idx + 1 }}</span>
                            <span class="text-emerald-400">Siap Ditampilkan</span>
                        </div>
                    </div>
                </div>

                <div class="pt-4 flex justify-end">
                    <button
                        @click="submitWidgets"
                        class="px-5 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-white font-semibold text-xs flex items-center gap-2 shadow-lg shadow-emerald-600/20 transition"
                    >
                        <CheckCircle2 class="w-4 h-4" />
                        <span>Simpan Tata Letak Widget Dashboard</span>
                    </button>
                </div>
            </div>
        </main>

        <!-- MODAL: CREATE MODULE -->
        <div
            v-if="showCreateModuleModal"
            class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/80 backdrop-blur-xs"
        >
            <div class="bg-slate-950 border border-slate-800 rounded-3xl max-w-lg w-full p-6 space-y-5 shadow-2xl">
                <div class="flex items-center justify-between border-b border-slate-800 pb-3">
                    <h3 class="font-bold text-white text-base flex items-center gap-2">
                        <Boxes class="w-5 h-5 text-emerald-400" />
                        <span>Buat Modul ERP Baru</span>
                    </h3>
                    <button @click="showCreateModuleModal = false" class="text-slate-400 hover:text-white">
                        <X class="w-5 h-5" />
                    </button>
                </div>

                <form @submit.prevent="submitModule" class="space-y-4">
                    <div>
                        <label class="block text-xs font-semibold text-slate-300 mb-1">Nama Modul</label>
                        <input
                            v-model="moduleForm.name"
                            @input="handleModuleNameInput"
                            type="text"
                            required
                            placeholder="Contoh: Maintenance & Asset Management"
                            class="w-full bg-slate-900 border border-slate-800 text-white text-xs rounded-xl px-3 py-2.5 focus:ring-emerald-500"
                        />
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-300 mb-1">URL Route Prefix (Slug)</label>
                        <input
                            v-model="moduleForm.slug"
                            type="text"
                            required
                            placeholder="maintenance"
                            class="w-full bg-slate-900 border border-slate-800 text-emerald-400 font-mono text-xs rounded-xl px-3 py-2.5 focus:ring-emerald-500"
                        />
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-300 mb-1">Deskripsi Modul</label>
                        <textarea
                            v-model="moduleForm.description"
                            rows="2"
                            placeholder="Deskripsi fungsi dan tujuan operasional modul ini..."
                            class="w-full bg-slate-900 border border-slate-800 text-white text-xs rounded-xl px-3 py-2 focus:ring-emerald-500"
                        ></textarea>
                    </div>

                    <!-- Color theme selector -->
                    <div>
                        <label class="block text-xs font-semibold text-slate-300 mb-1.5">Tema Warna Modul</label>
                        <div class="flex flex-wrap gap-2">
                            <button
                                v-for="c in colorThemes"
                                :key="c.key"
                                type="button"
                                @click="moduleForm.color_theme = c.key"
                                :class="[
                                    'px-2.5 py-1.5 rounded-lg text-xs font-medium flex items-center gap-1.5 border transition',
                                    moduleForm.color_theme === c.key
                                        ? 'border-white bg-slate-800 text-white shadow-sm'
                                        : 'border-slate-800 bg-slate-900 text-slate-400 hover:text-white',
                                ]"
                            >
                                <span :class="['w-3 h-3 rounded-full', c.bg]" />
                                <span>{{ c.name }}</span>
                            </button>
                        </div>
                    </div>

                    <!-- Roles access -->
                    <div>
                        <label class="block text-xs font-semibold text-slate-300 mb-1.5">Hak Akses Role (Spatie RBAC)</label>
                        <div class="grid grid-cols-2 gap-2 max-h-32 overflow-y-auto p-2 bg-slate-900 rounded-xl border border-slate-800">
                            <label
                                v-for="r in roles"
                                :key="r.id"
                                class="flex items-center gap-2 text-xs text-slate-300 cursor-pointer"
                            >
                                <input
                                    type="checkbox"
                                    :value="r.name"
                                    v-model="moduleForm.roles"
                                    class="rounded bg-slate-950 border-slate-800 text-emerald-600 focus:ring-emerald-500"
                                />
                                <span>{{ r.name }}</span>
                            </label>
                        </div>
                    </div>

                    <div class="pt-4 flex items-center justify-end gap-3 border-t border-slate-800">
                        <button
                            type="button"
                            @click="showCreateModuleModal = false"
                            class="px-4 py-2 rounded-xl text-xs text-slate-400 hover:text-white"
                        >
                            Batal
                        </button>
                        <button
                            type="submit"
                            :disabled="moduleForm.processing"
                            class="px-5 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-white font-semibold text-xs flex items-center gap-2 transition disabled:opacity-50"
                        >
                            <Check class="w-4 h-4" />
                            <span>Simpan & Daftarkan Modul</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- MODAL: CREATE ENTITY -->
        <div
            v-if="showCreateEntityModal"
            class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/80 backdrop-blur-xs"
        >
            <div class="bg-slate-950 border border-slate-800 rounded-3xl max-w-md w-full p-6 space-y-5 shadow-2xl">
                <div class="flex items-center justify-between border-b border-slate-800 pb-3">
                    <h3 class="font-bold text-white text-base flex items-center gap-2">
                        <Table class="w-5 h-5 text-emerald-400" />
                        <span>Buat Entitas Data Baru</span>
                    </h3>
                    <button @click="showCreateEntityModal = false" class="text-slate-400 hover:text-white">
                        <X class="w-5 h-5" />
                    </button>
                </div>

                <form @submit.prevent="submitEntity" class="space-y-4">
                    <div>
                        <label class="block text-xs font-semibold text-slate-300 mb-1">Nama Entitas</label>
                        <input
                            v-model="entityForm.name"
                            @input="handleEntityNameInput"
                            type="text"
                            required
                            placeholder="Contoh: Work Order, Dokumen Audit, dsb."
                            class="w-full bg-slate-900 border border-slate-800 text-white text-xs rounded-xl px-3 py-2.5 focus:ring-emerald-500"
                        />
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-300 mb-1">Entity Slug</label>
                        <input
                            v-model="entityForm.slug"
                            type="text"
                            required
                            class="w-full bg-slate-900 border border-slate-800 text-emerald-400 font-mono text-xs rounded-xl px-3 py-2.5 focus:ring-emerald-500"
                        />
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-300 mb-1">Deskripsi</label>
                        <textarea
                            v-model="entityForm.description"
                            rows="2"
                            class="w-full bg-slate-900 border border-slate-800 text-white text-xs rounded-xl px-3 py-2 focus:ring-emerald-500"
                        ></textarea>
                    </div>

                    <div class="space-y-2 pt-2">
                        <label class="flex items-center gap-2 text-xs text-slate-300 cursor-pointer">
                            <input
                                v-model="entityForm.has_approval"
                                type="checkbox"
                                class="rounded bg-slate-950 border-slate-800 text-emerald-600 focus:ring-emerald-500"
                            />
                            <span>Sertakan Alur Persetujuan (Approval Workflow)</span>
                        </label>
                        <label class="flex items-center gap-2 text-xs text-slate-300 cursor-pointer">
                            <input
                                v-model="entityForm.has_audit_log"
                                type="checkbox"
                                class="rounded bg-slate-950 border-slate-800 text-emerald-600 focus:ring-emerald-500"
                            />
                            <span>Aktifkan Audit Trail (Spatie Activitylog)</span>
                        </label>
                    </div>

                    <div class="pt-4 flex items-center justify-end gap-3 border-t border-slate-800">
                        <button
                            type="button"
                            @click="showCreateEntityModal = false"
                            class="px-4 py-2 rounded-xl text-xs text-slate-400 hover:text-white"
                        >
                            Batal
                        </button>
                        <button
                            type="submit"
                            :disabled="entityForm.processing"
                            class="px-5 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-white font-semibold text-xs flex items-center gap-2 transition disabled:opacity-50"
                        >
                            <Check class="w-4 h-4" />
                            <span>Buat Entitas</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- MODAL: ADD / EDIT FIELD -->
        <div
            v-if="showFieldModal"
            class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/80 backdrop-blur-xs"
        >
            <div class="bg-slate-950 border border-slate-800 rounded-3xl max-w-md w-full p-6 space-y-5 shadow-2xl">
                <div class="flex items-center justify-between border-b border-slate-800 pb-3">
                    <h3 class="font-bold text-white text-base flex items-center gap-2">
                        <Sliders class="w-5 h-5 text-emerald-400" />
                        <span>{{ editingField ? 'Edit Field' : 'Tambah Field Baru' }}</span>
                    </h3>
                    <button @click="showFieldModal = false" class="text-slate-400 hover:text-white">
                        <X class="w-5 h-5" />
                    </button>
                </div>

                <form @submit.prevent="submitField" class="space-y-4">
                    <div>
                        <label class="block text-xs font-semibold text-slate-300 mb-1">Label Field</label>
                        <input
                            v-model="fieldForm.label"
                            @input="handleFieldLabelInput"
                            type="text"
                            required
                            placeholder="Contoh: Estimasi Biaya, Tgl Selesai, dsb."
                            class="w-full bg-slate-900 border border-slate-800 text-white text-xs rounded-xl px-3 py-2.5 focus:ring-emerald-500"
                        />
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-300 mb-1">Field Key (JSON property)</label>
                        <input
                            v-model="fieldForm.field_key"
                            type="text"
                            required
                            class="w-full bg-slate-900 border border-slate-800 text-emerald-400 font-mono text-xs rounded-xl px-3 py-2.5 focus:ring-emerald-500"
                        />
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-300 mb-1">Tipe Field</label>
                        <select
                            v-model="fieldForm.field_type"
                            class="w-full bg-slate-900 border border-slate-800 text-white text-xs rounded-xl px-3 py-2.5 focus:ring-emerald-500"
                        >
                            <option value="text">Text (Singkat)</option>
                            <option value="number">Number (Angka)</option>
                            <option value="currency">Currency (Mata Uang Rp)</option>
                            <option value="date">Date (Tanggal)</option>
                            <option value="select">Select (Pilihan Dropdown)</option>
                            <option value="relation">Relation (Relasi Entitas Core)</option>
                            <option value="file">File Upload (Dokumen / Foto)</option>
                            <option value="signature">Digital Signature (Tanda Tangan)</option>
                        </select>
                    </div>

                    <div v-if="fieldForm.field_type === 'select'">
                        <label class="block text-xs font-semibold text-slate-300 mb-1">Opsi Dropdown (Pisahkan koma)</label>
                        <input
                            v-model="fieldForm.options_text"
                            type="text"
                            placeholder="Rendah, Sedang, Tinggi, Kritis"
                            class="w-full bg-slate-900 border border-slate-800 text-white text-xs rounded-xl px-3 py-2"
                        />
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <label class="flex items-center gap-2 text-xs text-slate-300 cursor-pointer">
                            <input
                                v-model="fieldForm.is_required"
                                type="checkbox"
                                class="rounded bg-slate-950 border-slate-800 text-emerald-600 focus:ring-emerald-500"
                            />
                            <span>Wajib Diisi (Required)</span>
                        </label>
                        <label class="flex items-center gap-2 text-xs text-slate-300 cursor-pointer">
                            <input
                                v-model="fieldForm.show_in_table"
                                type="checkbox"
                                class="rounded bg-slate-950 border-slate-800 text-emerald-600 focus:ring-emerald-500"
                            />
                            <span>Tampilkan di Tabel Data</span>
                        </label>
                    </div>

                    <div class="pt-4 flex items-center justify-end gap-3 border-t border-slate-800">
                        <button
                            type="button"
                            @click="showFieldModal = false"
                            class="px-4 py-2 rounded-xl text-xs text-slate-400 hover:text-white"
                        >
                            Batal
                        </button>
                        <button
                            type="submit"
                            :disabled="fieldForm.processing"
                            class="px-5 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-white font-semibold text-xs flex items-center gap-2 transition disabled:opacity-50"
                        >
                            <Check class="w-4 h-4" />
                            <span>Simpan Field</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</template>
