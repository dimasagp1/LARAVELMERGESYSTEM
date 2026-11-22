<script setup>
import { ref, computed, watchEffect } from 'vue';
import { Head, Link, useForm, router } from '@inertiajs/vue3';
import KpiStatCard from '@/Components/Dashboard/KpiStatCard.vue';
import InteractiveChartCard from '@/Components/Dashboard/InteractiveChartCard.vue';
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
    Monitor,
    Tablet as TabletIcon,
    Smartphone,
    MoveUp,
    MoveDown,
    Copy,
    Maximize2,
    Package,
    Truck,
    Factory,
    ShieldCheck,
    Briefcase,
    Car,
    FlaskConical,
    BookOpen,
    HelpCircle,
    Save,
    Search,
    ExternalLink,
    RefreshCw,
    Maximize,
    GripVertical,
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
    dataSources: {
        type: Object,
        default: () => ({}),
    },
    availableMetrics: {
        type: Object,
        default: () => ({}),
    },
    stats: {
        type: Object,
        default: () => ({}),
    },
});

// Active Tab: 'modules' | 'entities' | 'canvas' | 'workflows' | 'cross_data'
const activeTab = ref('canvas');

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

// ==========================================
// CANVAS DESIGNER STATE ("Kaya Kanvas")
// ==========================================
const canvasViewMode = ref('editor'); // 'editor' | 'preview'
const canvasViewport = ref('desktop'); // 'desktop' | 'tablet' | 'mobile'

const canvasConfig = ref({
    columns: 4,
    theme: 'dark', // 'dark' | 'light' | 'emerald' | 'indigo'
    card_radius: 'rounded-2xl', // 'rounded-none' | 'rounded-2xl' | 'rounded-3xl'
    card_padding: 'p-5', // 'p-3' | 'p-5' | 'p-7'
    card_elevation: 'shadow-lg',
});

const canvasWidgets = ref([]);
const isSavingCanvas = ref(false);
const editingWidgetIndex = ref(null);
const showWidgetConfigModal = ref(false);
const widgetEditForm = ref({
    title: '',
    widget_type: 'kpi',
    grid_span: 1,
    metric_key: '',
    custom_value: '',
    unit: '',
    icon: 'Activity',
    color: 'emerald',
    subtext: '',
    chart_type: 'area',
    source_key: '',
});

// Initialize canvas from module schema
watchEffect(() => {
    if (selectedModule.value) {
        const schemaCanvas = selectedModule.value.schema_config_json?.canvas;
        if (schemaCanvas) {
            canvasConfig.value = { ...canvasConfig.value, ...schemaCanvas };
        }
        if (selectedModule.value.custom_widgets && selectedModule.value.custom_widgets.length > 0) {
            canvasWidgets.value = selectedModule.value.custom_widgets.map((w) => ({
                id: w.id,
                title: w.title,
                widget_type: w.widget_type,
                grid_span: w.grid_span || 1,
                data_source_config_json: w.data_source_config_json || {},
            }));
        } else {
            // Default widgets starter
            canvasWidgets.value = [
                {
                    title: 'Total Karyawan Terhubung',
                    widget_type: 'kpi',
                    grid_span: 1,
                    data_source_config_json: {
                        metric_key: 'total_employees',
                        icon: 'Users',
                        color: 'emerald',
                        unit: 'Orang',
                        subtext: 'Terhubung master HRIS',
                        trend_direction: 'up',
                        trend_percentage: '+12%',
                    },
                },
                {
                    title: 'Katalog Produk SCM',
                    widget_type: 'kpi',
                    grid_span: 1,
                    data_source_config_json: {
                        metric_key: 'total_products',
                        icon: 'Package',
                        color: 'blue',
                        unit: 'SKU',
                        subtext: 'Stok simplisia & ekstrak',
                        trend_direction: 'up',
                        trend_percentage: 'Aktif',
                    },
                },
                {
                    title: 'Bagan Akun Finansial',
                    widget_type: 'kpi',
                    grid_span: 1,
                    data_source_config_json: {
                        metric_key: 'total_coa_accounts',
                        icon: 'BookOpen',
                        color: 'violet',
                        unit: 'Akun',
                        subtext: 'Buku besar COA GL',
                        trend_direction: 'neutral',
                        trend_percentage: 'GL',
                    },
                },
                {
                    title: 'Transaksi 215 Fitur Menu',
                    widget_type: 'kpi',
                    grid_span: 1,
                    data_source_config_json: {
                        metric_key: 'total_feature_records',
                        icon: 'Layers',
                        color: 'amber',
                        unit: 'Entri',
                        subtext: 'Record transaksi modul',
                        trend_direction: 'up',
                        trend_percentage: 'Live',
                    },
                },
                {
                    title: 'Tren Aktivitas Transaksi & Dokumen',
                    widget_type: 'chart',
                    grid_span: 3,
                    data_source_config_json: {
                        subtitle: 'Volume pengajuan vs persetujuan 7 hari terakhir',
                        chart_type: 'area',
                    },
                },
                {
                    title: 'Aksi Cepat & Pintasan',
                    widget_type: 'actions',
                    grid_span: 1,
                    data_source_config_json: {
                        subtitle: 'Pintasan navigasi instan modul',
                    },
                },
            ];
        }
    }
});

// Canvas Manipulation Methods
function addCanvasWidget(type) {
    const defaults = {
        kpi: {
            title: 'Metrik KPI Baru',
            widget_type: 'kpi',
            grid_span: 1,
            data_source_config_json: {
                metric_key: 'total_employees',
                icon: 'Activity',
                color: 'emerald',
                unit: 'Data',
                subtext: 'Terhubung data sistem',
                trend_direction: 'up',
                trend_percentage: '+5%',
            },
        },
        chart: {
            title: 'Grafik Analisis Dinamis',
            widget_type: 'chart',
            grid_span: 2,
            data_source_config_json: {
                subtitle: 'Tren analisis waktu nyata',
                chart_type: 'area',
            },
        },
        table: {
            title: 'Tabel Data Lintas Modul',
            widget_type: 'table',
            grid_span: 4,
            data_source_config_json: {
                source_key: 'hris_employees',
                subtext: 'Data langsung dari modul terkait',
            },
        },
        actions: {
            title: 'Pintasan & Aksi Cepat',
            widget_type: 'actions',
            grid_span: 1,
            data_source_config_json: {
                subtitle: 'Tombol navigasi cepat',
            },
        },
        banner: {
            title: 'Pengumuman / Arahan Operasional',
            widget_type: 'banner',
            grid_span: 4,
            data_source_config_json: {
                message: 'Pastikan seluruh transaksi modul ini telah melalui verifikasi dokumen.',
            },
        },
    };

    if (defaults[type]) {
        canvasWidgets.value.push(JSON.parse(JSON.stringify(defaults[type])));
    }
}

// Drag & Drop Canvas State & Handlers ("Bisa Ditarik-Tarik")
const draggedWidgetIndex = ref(null);
const dragOverWidgetIndex = ref(null);
const isDragging = ref(false);

function onDragStart(e, idx) {
    draggedWidgetIndex.value = idx;
    isDragging.value = true;
    if (e.dataTransfer) {
        e.dataTransfer.effectAllowed = 'move';
        e.dataTransfer.setData('text/plain', String(idx));
    }
}

function onDragOver(e, idx) {
    e.preventDefault();
    if (e.dataTransfer) {
        e.dataTransfer.dropEffect = 'move';
    }
    if (draggedWidgetIndex.value === null || draggedWidgetIndex.value === idx) {
        dragOverWidgetIndex.value = null;
        return;
    }
    dragOverWidgetIndex.value = idx;
}

function onDragLeave(e, idx) {
    if (e.currentTarget && e.relatedTarget && e.currentTarget.contains(e.relatedTarget)) {
        return;
    }
    if (dragOverWidgetIndex.value === idx) {
        dragOverWidgetIndex.value = null;
    }
}

function onDrop(e, targetIdx) {
    e.preventDefault();
    const sourceIdx = draggedWidgetIndex.value;
    if (sourceIdx !== null && sourceIdx !== undefined && sourceIdx !== targetIdx) {
        const itemToMove = canvasWidgets.value.splice(sourceIdx, 1)[0];
        canvasWidgets.value.splice(targetIdx, 0, itemToMove);
    }
    onDragEnd();
}

function onDragEnd() {
    draggedWidgetIndex.value = null;
    dragOverWidgetIndex.value = null;
    isDragging.value = false;
}

function moveWidgetUp(idx) {
    if (idx <= 0) return;
    const temp = canvasWidgets.value[idx];
    canvasWidgets.value[idx] = canvasWidgets.value[idx - 1];
    canvasWidgets.value[idx - 1] = temp;
}

function moveWidgetDown(idx) {
    if (idx >= canvasWidgets.value.length - 1) return;
    const temp = canvasWidgets.value[idx];
    canvasWidgets.value[idx] = canvasWidgets.value[idx + 1];
    canvasWidgets.value[idx + 1] = temp;
}

function setWidgetSpan(idx, span) {
    canvasWidgets.value[idx].grid_span = span;
}

function duplicateWidget(idx) {
    const clone = JSON.parse(JSON.stringify(canvasWidgets.value[idx]));
    clone.title = clone.title + ' (Salinan)';
    delete clone.id;
    canvasWidgets.value.splice(idx + 1, 0, clone);
}

function removeCanvasWidget(idx) {
    canvasWidgets.value.splice(idx, 1);
}

function openEditWidgetModal(w, idx) {
    editingWidgetIndex.value = idx;
    const conf = w.data_source_config_json || {};
    widgetEditForm.value = {
        title: w.title,
        widget_type: w.widget_type,
        grid_span: w.grid_span || 1,
        metric_key: conf.metric_key || '',
        custom_value: conf.custom_value || '',
        unit: conf.unit || '',
        icon: conf.icon || 'Activity',
        color: conf.color || 'emerald',
        subtext: conf.subtext || '',
        chart_type: conf.chart_type || 'area',
        source_key: conf.source_key || 'hris_employees',
        message: conf.message || '',
    };
    showWidgetConfigModal.value = true;
}

function applyWidgetConfig() {
    if (editingWidgetIndex.value === null) return;
    const idx = editingWidgetIndex.value;
    const f = widgetEditForm.value;

    canvasWidgets.value[idx].title = f.title;
    canvasWidgets.value[idx].grid_span = f.grid_span;
    canvasWidgets.value[idx].data_source_config_json = {
        ...canvasWidgets.value[idx].data_source_config_json,
        metric_key: f.metric_key,
        custom_value: f.custom_value,
        unit: f.unit,
        icon: f.icon,
        color: f.color,
        subtext: f.subtext,
        chart_type: f.chart_type,
        source_key: f.source_key,
        message: f.message,
    };
    showWidgetConfigModal.value = false;
}

function saveCanvasLayout() {
    if (!selectedModule.value) return;
    isSavingCanvas.value = true;

    router.post(
        `/developer-mode/modules/${selectedModule.value.id}/canvas`,
        {
            canvas_config: canvasConfig.value,
            widgets: canvasWidgets.value,
        },
        {
            preserveScroll: true,
            onFinish: () => {
                isSavingCanvas.value = false;
            },
        }
    );
}

// Chart Fallbacks
const defaultChartSeries = [
    { name: 'Dokumen Diajukan', data: [12, 19, 15, 27, 34, 42, 38] },
    { name: 'Disetujui', data: [10, 16, 13, 24, 30, 40, 36] },
];

const defaultChartOptions = {
    chart: { type: 'area', toolbar: { show: false }, fontFamily: 'inherit' },
    colors: ['#10b981', '#06b6d4'],
    dataLabels: { enabled: false },
    stroke: { curve: 'smooth', width: 2 },
    xaxis: { categories: ['Sen', 'Sel', 'Rab', 'Kam', 'Jum', 'Sab', 'Min'] },
};

function resolveMetricValue(w) {
    const conf = w.data_source_config_json || {};
    if (conf.metric_key && props.availableMetrics[conf.metric_key]) {
        return props.availableMetrics[conf.metric_key].value;
    }
    if (conf.custom_value !== undefined && conf.custom_value !== '') {
        return conf.custom_value;
    }
    return 100;
}

// ==========================================
// CROSS-MODULE DATA EXPLORER STATE (Tab 5)
// ==========================================
const selectedDataSourceKey = ref('hris_employees');
const crossDataSearch = ref('');
const liveRecords = ref([]);
const isLoadingRecords = ref(false);

const currentDataSourceInfo = computed(() => {
    return props.dataSources[selectedDataSourceKey.value] || null;
});

async function loadSourceRecords(sourceKey) {
    selectedDataSourceKey.value = sourceKey;
    isLoadingRecords.value = true;
    try {
        const res = await fetch(`/developer-mode/sources/${sourceKey}?search=${encodeURIComponent(crossDataSearch.value)}`);
        const json = await res.json();
        liveRecords.value = json;
    } catch (e) {
        liveRecords.value = [];
    } finally {
        isLoadingRecords.value = false;
    }
}

// ==========================================
// ENTITY & FIELD DESIGNER STATE (Tab 2)
// ==========================================
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

function submitEntity() {
    entityForm.module_id = selectedModuleId.value;
    entityForm.post('/developer-mode/entities', {
        onSuccess: () => {
            showCreateEntityModal.value = false;
            entityForm.reset();
        },
    });
}

// Form for Custom Field with Cross-Module Relation Support
const fieldForm = useForm({
    id: null,
    label: '',
    field_key: '',
    field_type: 'text',
    options_text: '',
    relation_source: 'hris_employees',
    is_required: false,
    show_in_table: true,
    sort_order: 1,
});

function openAddFieldModal() {
    editingField.value = null;
    fieldForm.reset();
    fieldForm.field_type = 'text';
    fieldForm.relation_source = 'hris_employees';
    fieldForm.sort_order = (selectedEntity.value?.fields?.length || 0) + 1;
    showFieldModal.value = true;
}

function openEditFieldModal(field) {
    editingField.value = field;
    fieldForm.id = field.id;
    fieldForm.label = field.label;
    fieldForm.field_key = field.field_key;
    fieldForm.field_type = field.field_type;
    fieldForm.options_text = Array.isArray(field.options_json) ? field.options_json.join(', ') : '';
    fieldForm.relation_source = field.options_json?.source || 'hris_employees';
    fieldForm.is_required = field.is_required;
    fieldForm.show_in_table = field.show_in_table;
    fieldForm.sort_order = field.sort_order;
    showFieldModal.value = true;
}

function handleFieldLabelInput() {
    if (!editingField.value) {
        fieldForm.field_key = fieldForm.label
            .toLowerCase()
            .replace(/[^a-z0-9]+/g, '_')
            .replace(/(^_|_$)/g, '');
    }
}

function submitField() {
    if (!selectedEntity.value) return;

    let options = null;
    if (fieldForm.field_type === 'select') {
        options = fieldForm.options_text.split(',').map((s) => s.trim()).filter(Boolean);
    } else if (fieldForm.field_type === 'relation') {
        options = {
            source: fieldForm.relation_source,
        };
    }

    const payload = {
        id: fieldForm.id,
        label: fieldForm.label,
        field_key: fieldForm.field_key,
        field_type: fieldForm.field_type,
        options_json: options,
        is_required: fieldForm.is_required,
        show_in_table: fieldForm.show_in_table,
        sort_order: fieldForm.sort_order,
    };

    router.post(`/developer-mode/entities/${selectedEntity.value.id}/fields`, payload, {
        onSuccess: () => {
            showFieldModal.value = false;
        },
    });
}

function deleteField(fieldId) {
    if (confirm('Hapus field ini? Kolom data pada entitas ini tidak akan ditampilkan lagi.')) {
        router.delete(`/developer-mode/fields/${fieldId}`);
    }
}

// Colors & Theme definitions
const colorThemes = [
    { key: 'emerald', name: 'Emerald Green', bg: 'bg-emerald-500' },
    { key: 'blue', name: 'Ocean Blue', bg: 'bg-blue-500' },
    { key: 'amber', name: 'Warm Amber', bg: 'bg-amber-500' },
    { key: 'violet', name: 'Royal Violet', bg: 'bg-violet-500' },
    { key: 'rose', name: 'Rose Red', bg: 'bg-rose-500' },
    { key: 'teal', name: 'Cyan Teal', bg: 'bg-teal-500' },
    { key: 'indigo', name: 'Deep Indigo', bg: 'bg-indigo-500' },
];
</script>

<template>
    <div class="min-h-screen bg-slate-900 text-slate-100 font-sans selection:bg-emerald-500 selection:text-white">
        <Head title="Developer Mode - Canvas Designer & No-Code Builder" />

        <!-- Top Dark Developer Navigation Bar -->
        <header class="sticky top-0 z-40 bg-slate-950/95 backdrop-blur border-b border-slate-800">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex items-center justify-between h-16">
                    <div class="flex items-center gap-3">
                        <Link
                            href="/"
                            class="p-2 rounded-xl bg-slate-900 border border-slate-800 hover:border-slate-700 text-slate-400 hover:text-white transition shadow-sm"
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
                                    <h1 class="font-bold text-base text-white tracking-tight">No-Code Module & Canvas Engine</h1>
                                    <span class="text-[10px] font-mono font-bold px-2 py-0.5 rounded bg-emerald-500/20 text-emerald-300 border border-emerald-500/30">
                                        CANVAS v2.5
                                    </span>
                                </div>
                                <p class="text-[11px] text-slate-400 hidden sm:block">
                                    Desain tampilan kanvas dinamis, relasi lintas modul, & alur approval instan
                                </p>
                            </div>
                        </div>
                    </div>

                    <div class="flex items-center gap-2.5">
                        <Link
                            v-if="selectedModule"
                            :href="`/modules/${selectedModule.slug}`"
                            target="_blank"
                            class="px-3.5 py-1.5 rounded-xl bg-slate-800 hover:bg-slate-700 text-emerald-400 border border-emerald-500/30 text-xs font-semibold flex items-center gap-1.5 transition"
                        >
                            <ExternalLink class="w-3.5 h-3.5" />
                            <span>Buka Modul di Tab Baru</span>
                        </Link>

                        <Link
                            href="/"
                            class="px-3.5 py-1.5 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-white font-bold text-xs flex items-center gap-1.5 transition shadow-sm"
                        >
                            <LayoutDashboard class="w-3.5 h-3.5" />
                            <span>ERP Launcher</span>
                        </Link>
                    </div>
                </div>

                <!-- 5 Main Navigation Tabs -->
                <div class="flex items-center gap-1 border-t border-slate-800/80 -mb-px overflow-x-auto custom-scrollbar">
                    <button
                        @click="activeTab = 'canvas'"
                        :class="[
                            'flex items-center gap-2 px-4 py-3 text-xs font-medium border-b-2 transition whitespace-nowrap cursor-pointer',
                            activeTab === 'canvas'
                                ? 'border-emerald-500 text-emerald-400 font-bold bg-emerald-500/5'
                                : 'border-transparent text-slate-400 hover:text-slate-200',
                        ]"
                    >
                        <Sparkles class="w-4 h-4 text-emerald-400" />
                        <span>1. Desain Kanvas Tampilan (Adjustable Canvas)</span>
                    </button>

                    <button
                        @click="activeTab = 'entities'"
                        :class="[
                            'flex items-center gap-2 px-4 py-3 text-xs font-medium border-b-2 transition whitespace-nowrap cursor-pointer',
                            activeTab === 'entities'
                                ? 'border-emerald-500 text-emerald-400 font-bold bg-emerald-500/5'
                                : 'border-transparent text-slate-400 hover:text-slate-200',
                        ]"
                    >
                        <Table class="w-4 h-4" />
                        <span>2. Entitas & Relasi Data</span>
                    </button>

                    <button
                        @click="activeTab = 'cross_data'"
                        :class="[
                            'flex items-center gap-2 px-4 py-3 text-xs font-medium border-b-2 transition whitespace-nowrap cursor-pointer',
                            activeTab === 'cross_data'
                                ? 'border-emerald-500 text-emerald-400 font-bold bg-emerald-500/5'
                                : 'border-transparent text-slate-400 hover:text-slate-200',
                        ]"
                    >
                        <Database class="w-4 h-4 text-amber-400" />
                        <span>3. Penjelajah Data Lintas Modul (215 Menu)</span>
                    </button>

                    <button
                        @click="activeTab = 'modules'"
                        :class="[
                            'flex items-center gap-2 px-4 py-3 text-xs font-medium border-b-2 transition whitespace-nowrap cursor-pointer',
                            activeTab === 'modules'
                                ? 'border-emerald-500 text-emerald-400 font-bold bg-emerald-500/5'
                                : 'border-transparent text-slate-400 hover:text-slate-200',
                        ]"
                    >
                        <Boxes class="w-4 h-4" />
                        <span>4. Manajemen Modul</span>
                    </button>

                    <button
                        @click="activeTab = 'workflows'"
                        :class="[
                            'flex items-center gap-2 px-4 py-3 text-xs font-medium border-b-2 transition whitespace-nowrap cursor-pointer',
                            activeTab === 'workflows'
                                ? 'border-emerald-500 text-emerald-400 font-bold bg-emerald-500/5'
                                : 'border-transparent text-slate-400 hover:text-slate-200',
                        ]"
                    >
                        <GitBranch class="w-4 h-4" />
                        <span>5. Alur Persetujuan & Approvals</span>
                    </button>
                </div>
            </div>
        </header>

        <!-- Main Developer Body -->
        <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-6">
            <!-- ============================================================== -->
            <!-- TAB 1: VISUAL ADJUSTABLE CANVAS LAYOUT DESIGNER ("Kaya Kanvas") -->
            <!-- ============================================================== -->
            <div v-if="activeTab === 'canvas'" class="space-y-6">
                <!-- Canvas Control Bar -->
                <div class="bg-slate-950 border border-slate-800 rounded-3xl p-5 shadow-xl space-y-4">
                    <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4">
                        <div class="space-y-1">
                            <div class="flex items-center gap-2">
                                <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-emerald-500/20 text-emerald-300 border border-emerald-500/30">
                                    CANVAS DESIGNER
                                </span>
                                <h2 class="text-base font-bold text-white flex items-center gap-2">
                                    Desain Tata Letak Kanvas Dinamis
                                </h2>
                            </div>
                            <p class="text-xs text-slate-400">
                                Atur jumlah kolom, padding, bentuk sudut, dan susun blok widget secara leluasa layaknya kanvas visual.
                            </p>
                        </div>

                        <!-- Module Selector -->
                        <div class="flex items-center gap-3">
                            <label class="text-xs font-bold text-slate-400 uppercase tracking-wider">Pilih Modul:</label>
                            <select
                                v-model="selectedModuleId"
                                class="bg-slate-900 border border-slate-700 text-white text-xs rounded-xl px-3.5 py-2 font-medium focus:ring-emerald-500 focus:border-emerald-500"
                            >
                                <option v-for="m in modules" :key="m.id" :value="m.id">
                                    {{ m.name }} ({{ m.slug }})
                                </option>
                            </select>
                        </div>
                    </div>

                    <!-- Adjustable Canvas Parameters Bar -->
                    <div class="pt-3 border-t border-slate-800/80 flex flex-wrap items-center justify-between gap-4">
                        <div class="flex flex-wrap items-center gap-4">
                            <!-- Grid Columns -->
                            <div class="flex items-center gap-1.5 bg-slate-900 p-1 rounded-xl border border-slate-800">
                                <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400 px-2">Grid:</span>
                                <button
                                    v-for="c in [1, 2, 3, 4]"
                                    :key="c"
                                    type="button"
                                    @click="canvasConfig.columns = c"
                                    :class="[
                                        'px-2.5 py-1 rounded-lg text-xs font-bold transition cursor-pointer',
                                        canvasConfig.columns === c
                                            ? 'bg-emerald-600 text-white shadow-xs'
                                            : 'text-slate-400 hover:text-white'
                                    ]"
                                >
                                    {{ c }} Kolom
                                </button>
                            </div>

                            <!-- Card Corner Radius -->
                            <div class="flex items-center gap-1.5 bg-slate-900 p-1 rounded-xl border border-slate-800">
                                <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400 px-2">Sudut:</span>
                                <button
                                    @click="canvasConfig.card_radius = 'rounded-none'"
                                    :class="['px-2 py-1 rounded text-xs font-medium transition cursor-pointer', canvasConfig.card_radius === 'rounded-none' ? 'bg-emerald-600 text-white' : 'text-slate-400']"
                                    title="Sharp"
                                >
                                    Kotak
                                </button>
                                <button
                                    @click="canvasConfig.card_radius = 'rounded-2xl'"
                                    :class="['px-2 py-1 rounded-lg text-xs font-medium transition cursor-pointer', canvasConfig.card_radius === 'rounded-2xl' ? 'bg-emerald-600 text-white' : 'text-slate-400']"
                                    title="Sleek Rounded"
                                >
                                    Modern
                                </button>
                                <button
                                    @click="canvasConfig.card_radius = 'rounded-3xl'"
                                    :class="['px-2 py-1 rounded-xl text-xs font-medium transition cursor-pointer', canvasConfig.card_radius === 'rounded-3xl' ? 'bg-emerald-600 text-white' : 'text-slate-400']"
                                    title="Capsule Soft"
                                >
                                    Capsule
                                </button>
                            </div>

                            <!-- Card Padding -->
                            <div class="flex items-center gap-1.5 bg-slate-900 p-1 rounded-xl border border-slate-800">
                                <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400 px-2">Padding:</span>
                                <button
                                    v-for="p in [{ key: 'p-3', label: 'Rapat' }, { key: 'p-5', label: 'Standar' }, { key: 'p-7', label: 'Lapang' }]"
                                    :key="p.key"
                                    @click="canvasConfig.card_padding = p.key"
                                    :class="['px-2.5 py-1 rounded-lg text-xs font-medium transition cursor-pointer', canvasConfig.card_padding === p.key ? 'bg-emerald-600 text-white' : 'text-slate-400']"
                                >
                                    {{ p.label }}
                                </button>
                            </div>
                        </div>

                        <!-- Viewport Simulator & Editor/Preview Switcher -->
                        <div class="flex items-center gap-3">
                            <!-- Viewport device selector -->
                            <div class="flex items-center gap-1 bg-slate-900 p-1 rounded-xl border border-slate-800">
                                <button
                                    @click="canvasViewport = 'desktop'"
                                    :class="['p-1.5 rounded-lg transition cursor-pointer', canvasViewport === 'desktop' ? 'bg-emerald-600 text-white' : 'text-slate-400 hover:text-white']"
                                    title="Tampilan Desktop (100%)"
                                >
                                    <Monitor class="w-4 h-4" />
                                </button>
                                <button
                                    @click="canvasViewport = 'tablet'"
                                    :class="['p-1.5 rounded-lg transition cursor-pointer', canvasViewport === 'tablet' ? 'bg-emerald-600 text-white' : 'text-slate-400 hover:text-white']"
                                    title="Simulasi Tablet (768px)"
                                >
                                    <TabletIcon class="w-4 h-4" />
                                </button>
                                <button
                                    @click="canvasViewport = 'mobile'"
                                    :class="['p-1.5 rounded-lg transition cursor-pointer', canvasViewport === 'mobile' ? 'bg-emerald-600 text-white' : 'text-slate-400 hover:text-white']"
                                    title="Simulasi Mobile (420px)"
                                >
                                    <Smartphone class="w-4 h-4" />
                                </button>
                            </div>

                            <!-- Mode Toggle: Editor vs Live Preview -->
                            <div class="flex items-center gap-1 bg-slate-900 p-1 rounded-xl border border-slate-800">
                                <button
                                    @click="canvasViewMode = 'editor'"
                                    :class="['px-3 py-1 rounded-lg text-xs font-bold transition cursor-pointer', canvasViewMode === 'editor' ? 'bg-emerald-600 text-white shadow-xs' : 'text-slate-400']"
                                >
                                    Mode Desain
                                </button>
                                <button
                                    @click="canvasViewMode = 'preview'"
                                    :class="['px-3 py-1 rounded-lg text-xs font-bold transition cursor-pointer flex items-center gap-1', canvasViewMode === 'preview' ? 'bg-emerald-600 text-white shadow-xs' : 'text-slate-400']"
                                >
                                    <Eye class="w-3.5 h-3.5" />
                                    <span>Live Preview</span>
                                </button>
                            </div>

                            <!-- Save Canvas Button -->
                            <button
                                @click="saveCanvasLayout"
                                :disabled="isSavingCanvas"
                                class="px-4 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-white font-bold text-xs flex items-center gap-2 shadow-lg shadow-emerald-600/30 transition disabled:opacity-50 cursor-pointer"
                            >
                                <Save class="w-4 h-4" />
                                <span>{{ isSavingCanvas ? 'Menyimpan...' : 'Simpan Desain Kanvas' }}</span>
                            </button>
                        </div>
                    </div>

                    <!-- Component Palette: Add new widgets to canvas -->
                    <div class="pt-3 border-t border-slate-800/80 flex flex-wrap items-center gap-2">
                        <span class="text-xs font-bold text-slate-400 uppercase tracking-wider mr-2 flex items-center gap-1">
                            <Plus class="w-3.5 h-3.5 text-emerald-400" />
                            Tambah Blok ke Kanvas:
                        </span>
                        <button
                            @click="addCanvasWidget('kpi')"
                            class="px-3 py-1.5 rounded-xl bg-slate-900 hover:bg-slate-800 text-emerald-300 border border-emerald-500/30 text-xs font-medium flex items-center gap-1.5 transition cursor-pointer"
                        >
                            <Activity class="w-3.5 h-3.5 text-emerald-400" />
                            <span>+ Kartu Metrik KPI</span>
                        </button>
                        <button
                            @click="addCanvasWidget('chart')"
                            class="px-3 py-1.5 rounded-xl bg-slate-900 hover:bg-slate-800 text-blue-300 border border-blue-500/30 text-xs font-medium flex items-center gap-1.5 transition cursor-pointer"
                        >
                            <BarChart3 class="w-3.5 h-3.5 text-blue-400" />
                            <span>+ Grafik ApexCharts</span>
                        </button>
                        <button
                            @click="addCanvasWidget('table')"
                            class="px-3 py-1.5 rounded-xl bg-slate-900 hover:bg-slate-800 text-amber-300 border border-amber-500/30 text-xs font-medium flex items-center gap-1.5 transition cursor-pointer"
                        >
                            <Table class="w-3.5 h-3.5 text-amber-400" />
                            <span>+ Tabel Lintas Modul</span>
                        </button>
                        <button
                            @click="addCanvasWidget('actions')"
                            class="px-3 py-1.5 rounded-xl bg-slate-900 hover:bg-slate-800 text-violet-300 border border-violet-500/30 text-xs font-medium flex items-center gap-1.5 transition cursor-pointer"
                        >
                            <Sparkles class="w-3.5 h-3.5 text-violet-400" />
                            <span>+ Bilah Aksi Cepat</span>
                        </button>
                        <button
                            @click="addCanvasWidget('banner')"
                            class="px-3 py-1.5 rounded-xl bg-slate-900 hover:bg-slate-800 text-teal-300 border border-teal-500/30 text-xs font-medium flex items-center gap-1.5 transition cursor-pointer"
                        >
                            <Sparkles class="w-3.5 h-3.5 text-teal-400" />
                            <span>+ Banner Informasi</span>
                        </button>
                    </div>
                </div>

                <!-- CANVAS VIEWPORT CONTAINER -->
                <div class="flex justify-center transition-all duration-300">
                    <div
                        :class="[
                            'w-full transition-all duration-300 space-y-6',
                            canvasViewport === 'tablet' ? 'max-w-[768px] border-x border-slate-700/50 px-4' : '',
                            canvasViewport === 'mobile' ? 'max-w-[420px] border-x border-slate-700/50 px-3' : '',
                        ]"
                    >
                        <!-- ========================= -->
                        <!-- A. CANVAS DESIGNER MODE   -->
                        <!-- ========================= -->
                        <div v-if="canvasViewMode === 'editor'" class="canvas-grid-bg bg-slate-950/70 border border-slate-800/90 rounded-3xl p-6 sm:p-8 min-h-[550px] shadow-2xl relative space-y-6">
                            <!-- Canvas Workspace Header Bar -->
                            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pb-4 border-b border-slate-800/80">
                                <div class="flex items-center gap-2.5">
                                    <span class="relative flex h-2.5 w-2.5">
                                        <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                                        <span class="relative inline-flex rounded-full h-2.5 w-2.5 bg-emerald-500"></span>
                                    </span>
                                    <span class="text-xs font-bold text-white uppercase tracking-wider font-mono">Workspace Kanvas Visual (Drag & Drop)</span>
                                    <span class="text-[11px] px-2.5 py-0.5 rounded-full bg-slate-800/90 text-emerald-400 font-mono border border-slate-700/60 font-semibold">
                                        {{ canvasWidgets.length }} Blok Terpasang
                                    </span>
                                </div>
                                <div class="text-[11px] text-slate-400 flex items-center gap-2 bg-slate-900/80 px-3 py-1.5 rounded-xl border border-slate-800/70">
                                    <GripVertical class="w-3.5 h-3.5 text-emerald-400" />
                                    <span>Tarik ikon ⠿ untuk ubah urutan posisi • Ubah lebar kolom di bawah tiap blok</span>
                                </div>
                            </div>

                            <!-- Empty State if no widgets -->
                            <div
                                v-if="canvasWidgets.length === 0"
                                class="py-20 text-center border-2 border-dashed border-slate-800 rounded-3xl p-8 space-y-4 bg-slate-900/30"
                            >
                                <div class="w-14 h-14 mx-auto rounded-2xl bg-emerald-500/10 text-emerald-400 flex items-center justify-center">
                                    <Layers class="w-7 h-7" />
                                </div>
                                <div class="space-y-1">
                                    <h3 class="text-base font-bold text-white">Kanvas Masih Kosong</h3>
                                    <p class="text-xs text-slate-400 max-w-md mx-auto">
                                        Gunakan bilah tombol di atas untuk menambahkan blok KPI, Grafik, Tabel Lintas Modul, atau Tombol Pintasan ke kanvas ini.
                                    </p>
                                </div>
                                <button
                                    @click="addCanvasWidget('kpi')"
                                    class="px-4 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-white font-bold text-xs inline-flex items-center gap-2 transition cursor-pointer shadow-lg shadow-emerald-600/30"
                                >
                                    <Plus class="w-4 h-4" />
                                    <span>Tambah Blok Pertama</span>
                                </button>
                            </div>

                            <!-- Interactive Draggable Grid -->
                            <div
                                v-else
                                :class="[
                                    'grid gap-5',
                                    canvasConfig.columns === 1 ? 'grid-cols-1' : '',
                                    canvasConfig.columns === 2 ? 'grid-cols-1 md:grid-cols-2' : '',
                                    canvasConfig.columns === 3 ? 'grid-cols-1 md:grid-cols-3' : '',
                                    canvasConfig.columns === 4 ? 'grid-cols-1 sm:grid-cols-2 lg:grid-cols-4' : '',
                                ]"
                            >
                                <div
                                    v-for="(w, idx) in canvasWidgets"
                                    :key="w.id || `canvas-widget-${idx}`"
                                    :draggable="true"
                                    @dragstart="onDragStart($event, idx)"
                                    @dragover.prevent="onDragOver($event, idx)"
                                    @dragleave="onDragLeave($event, idx)"
                                    @drop="onDrop($event, idx)"
                                    @dragend="onDragEnd"
                                    :class="[
                                        'bg-slate-900/90 border border-slate-800 flex flex-col justify-between group relative transition-all duration-200 shadow-xl backdrop-blur-md select-none',
                                        canvasConfig.card_radius,
                                        canvasConfig.card_padding,
                                        w.grid_span === 2 ? 'md:col-span-2' : '',
                                        w.grid_span === 3 ? 'md:col-span-3' : '',
                                        w.grid_span === 4 ? 'col-span-full' : '',
                                        draggedWidgetIndex === idx ? 'opacity-30 scale-95 border-emerald-500 border-dashed ring-2 ring-emerald-500/40' : '',
                                        dragOverWidgetIndex === idx ? 'ring-2 ring-emerald-400 border-emerald-400 bg-emerald-500/10 scale-[1.02] shadow-2xl shadow-emerald-500/20' : '',
                                    ]"
                                >
                                    <!-- Block Header & Controls -->
                                    <div class="space-y-3.5">
                                        <div class="flex items-center justify-between gap-2 pb-3 border-b border-slate-800/80">
                                            <div class="flex items-center gap-2 min-w-0 flex-1">
                                                <!-- Drag Grip Handle -->
                                                <div
                                                    class="p-1 -ml-1 rounded-md text-slate-500 hover:text-emerald-400 cursor-grab active:cursor-grabbing hover:bg-slate-800 transition shrink-0"
                                                    title="Tahan & tarik untuk mengatur urutan posisi blok"
                                                >
                                                    <GripVertical class="w-4 h-4" />
                                                </div>

                                                <!-- Widget Type Badge -->
                                                <span
                                                    :class="[
                                                        'px-2 py-0.5 rounded text-[10px] font-mono font-bold uppercase shrink-0 border',
                                                        w.widget_type === 'kpi' ? 'bg-emerald-500/15 text-emerald-400 border-emerald-500/30' : '',
                                                        w.widget_type === 'chart' ? 'bg-sky-500/15 text-sky-400 border-sky-500/30' : '',
                                                        w.widget_type === 'table' ? 'bg-amber-500/15 text-amber-400 border-amber-500/30' : '',
                                                        w.widget_type === 'actions' ? 'bg-violet-500/15 text-violet-400 border-violet-500/30' : '',
                                                        w.widget_type === 'banner' ? 'bg-teal-500/15 text-teal-400 border-teal-500/30' : '',
                                                    ]"
                                                >
                                                    {{ w.widget_type }}
                                                </span>

                                                <!-- Title -->
                                                <span class="text-xs font-bold text-white truncate" :title="w.title">
                                                    {{ w.title }}
                                                </span>
                                            </div>

                                            <!-- Secondary Move & Action Buttons -->
                                            <div class="flex items-center gap-1 shrink-0">
                                                <button
                                                    @click="moveWidgetUp(idx)"
                                                    :disabled="idx === 0"
                                                    class="p-1 rounded bg-slate-950/80 hover:bg-slate-800 text-slate-400 hover:text-white disabled:opacity-20 cursor-pointer transition"
                                                    title="Pindah Ke Atas"
                                                >
                                                    <MoveUp class="w-3.5 h-3.5" />
                                                </button>
                                                <button
                                                    @click="moveWidgetDown(idx)"
                                                    :disabled="idx === canvasWidgets.length - 1"
                                                    class="p-1 rounded bg-slate-950/80 hover:bg-slate-800 text-slate-400 hover:text-white disabled:opacity-20 cursor-pointer transition"
                                                    title="Pindah Ke Bawah"
                                                >
                                                    <MoveDown class="w-3.5 h-3.5" />
                                                </button>
                                                <button
                                                    @click="openEditWidgetModal(w, idx)"
                                                    class="p-1 rounded bg-slate-950/80 hover:bg-slate-800 text-emerald-400 cursor-pointer transition"
                                                    title="Konfigurasi Properti & Data"
                                                >
                                                    <Edit3 class="w-3.5 h-3.5" />
                                                </button>
                                                <button
                                                    @click="duplicateWidget(idx)"
                                                    class="p-1 rounded bg-slate-950/80 hover:bg-slate-800 text-slate-400 hover:text-white cursor-pointer transition"
                                                    title="Duplikasi Blok"
                                                >
                                                    <Copy class="w-3.5 h-3.5" />
                                                </button>
                                                <button
                                                    @click="removeCanvasWidget(idx)"
                                                    class="p-1 rounded bg-slate-950/80 hover:bg-slate-800 text-rose-400 cursor-pointer transition"
                                                    title="Hapus Blok"
                                                >
                                                    <Trash2 class="w-3.5 h-3.5" />
                                                </button>
                                            </div>
                                        </div>

                                        <!-- Block Visual Preview in Editor -->
                                        <div class="py-1">
                                            <!-- KPI Preview -->
                                            <div v-if="w.widget_type === 'kpi'" class="flex items-center justify-between gap-3">
                                                <div class="space-y-1">
                                                    <div class="text-2xl font-black text-white font-mono tracking-tight">
                                                        {{ resolveMetricValue(w) }} <span class="text-xs text-slate-400 font-sans font-normal">{{ w.data_source_config_json?.unit }}</span>
                                                    </div>
                                                    <div class="text-[11px] text-slate-400">{{ w.data_source_config_json?.subtext || 'Terhubung data sistem' }}</div>
                                                    <div class="inline-flex items-center gap-1 px-1.5 py-0.5 rounded text-[10px] font-bold bg-emerald-500/10 text-emerald-400 border border-emerald-500/20">
                                                        <span>Live Connected</span>
                                                    </div>
                                                </div>
                                                <div class="w-11 h-11 rounded-2xl bg-emerald-500/15 border border-emerald-500/30 text-emerald-400 flex items-center justify-center font-bold shadow-lg shadow-emerald-500/10">
                                                    <Activity class="w-5 h-5" />
                                                </div>
                                            </div>

                                            <!-- Chart Preview with Realistic SVG Wave -->
                                            <div v-else-if="w.widget_type === 'chart'" class="rounded-2xl bg-slate-950/80 border border-slate-800/80 p-3 space-y-2">
                                                <div class="flex items-center justify-between text-[11px] text-slate-400 font-mono">
                                                    <span class="flex items-center gap-1.5 text-sky-400 font-bold">
                                                        <BarChart3 class="w-3.5 h-3.5" />
                                                        ApexCharts ({{ w.data_source_config_json?.chart_type || 'area' }})
                                                    </span>
                                                    <span class="text-[10px] text-emerald-400">7 Hari Terakhir</span>
                                                </div>
                                                <!-- SVG Line & Gradient Wave -->
                                                <div class="h-20 w-full relative flex items-end">
                                                    <svg class="w-full h-full overflow-visible" viewBox="0 0 300 70" preserveAspectRatio="none">
                                                        <defs>
                                                            <linearGradient id="chartGrad" x1="0%" y1="0%" x2="0%" y2="100%">
                                                                <stop offset="0%" stop-color="#10b981" stop-opacity="0.35" />
                                                                <stop offset="100%" stop-color="#10b981" stop-opacity="0.0" />
                                                            </linearGradient>
                                                        </defs>
                                                        <path d="M 0 50 Q 50 20, 100 35 T 200 15 T 300 10 L 300 70 L 0 70 Z" fill="url(#chartGrad)" />
                                                        <path d="M 0 50 Q 50 20, 100 35 T 200 15 T 300 10" fill="none" stroke="#10b981" stroke-width="2.5" />
                                                    </svg>
                                                </div>
                                                <div class="flex justify-between text-[9px] text-slate-500 font-mono pt-1 border-t border-slate-800/60">
                                                    <span>Sen</span><span>Sel</span><span>Rab</span><span>Kam</span><span>Jum</span><span>Sab</span><span>Min</span>
                                                </div>
                                            </div>

                                            <!-- Table Preview with Structured Mini Rows -->
                                            <div v-else-if="w.widget_type === 'table'" class="rounded-2xl bg-slate-950/80 border border-slate-800/80 p-3 space-y-2">
                                                <div class="flex items-center justify-between font-bold text-white text-[11px]">
                                                    <span class="flex items-center gap-1.5 text-amber-400">
                                                        <Table class="w-3.5 h-3.5" />
                                                        {{ w.data_source_config_json?.source_key || 'hris_employees' }}
                                                    </span>
                                                    <span class="text-[10px] text-emerald-400 font-mono">Live Grid Sync</span>
                                                </div>
                                                <div class="divide-y divide-slate-800/60 text-[10px]">
                                                    <div class="flex justify-between py-1 text-slate-500 font-mono font-bold">
                                                        <span>KODE / REF</span>
                                                        <span>STATUS</span>
                                                    </div>
                                                    <div class="flex justify-between py-1 text-slate-300">
                                                        <span>DOC-2026-001</span>
                                                        <span class="text-emerald-400 font-bold">Approved</span>
                                                    </div>
                                                    <div class="flex justify-between py-1 text-slate-300">
                                                        <span>DOC-2026-002</span>
                                                        <span class="text-amber-400 font-bold">Pending</span>
                                                    </div>
                                                </div>
                                            </div>

                                            <!-- Actions Preview -->
                                            <div v-else-if="w.widget_type === 'actions'" class="flex flex-wrap gap-2 py-1">
                                                <span class="px-3 py-1.5 rounded-xl bg-emerald-600/20 text-emerald-300 text-[11px] font-bold border border-emerald-500/30 flex items-center gap-1">
                                                    <Plus class="w-3 h-3" /> Input Data
                                                </span>
                                                <span class="px-3 py-1.5 rounded-xl bg-slate-800 text-slate-300 text-[11px] font-bold border border-slate-700/60">
                                                    📄 Cetak PDF
                                                </span>
                                                <span class="px-3 py-1.5 rounded-xl bg-slate-800 text-slate-300 text-[11px] font-bold border border-slate-700/60">
                                                    📊 Ekspor CSV
                                                </span>
                                            </div>

                                            <!-- Banner Preview -->
                                            <div v-else class="p-3.5 rounded-2xl bg-teal-950/40 border border-teal-800/40 text-xs text-teal-300 flex items-start gap-2.5">
                                                <Sparkles class="w-4 h-4 text-teal-400 shrink-0 mt-0.5" />
                                                <span>{{ w.data_source_config_json?.message || 'Banner pemberitahuan arahan operasional modul.' }}</span>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Bottom Width Span Controls -->
                                    <div class="pt-3 border-t border-slate-800/80 flex items-center justify-between text-[11px] text-slate-400 mt-2">
                                        <span class="font-mono text-[10px] text-slate-400">
                                            Lebar: <span class="text-white font-bold">{{ w.grid_span === 4 ? 'Penuh (Full)' : `${w.grid_span || 1} Kolom` }}</span>
                                        </span>
                                        <div class="flex items-center gap-1 bg-slate-950/90 p-0.5 rounded-xl border border-slate-800">
                                            <button
                                                v-for="s in [1, 2, 3, 4]"
                                                :key="s"
                                                type="button"
                                                @click="setWidgetSpan(idx, s)"
                                                :class="[
                                                    'px-2 py-0.5 rounded-lg text-[10px] font-bold transition cursor-pointer',
                                                    w.grid_span === s
                                                        ? 'bg-emerald-600 text-white shadow-xs'
                                                        : 'text-slate-400 hover:text-white hover:bg-slate-800'
                                                ]"
                                            >
                                                {{ s === 4 ? 'Full' : `${s} Kol` }}
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- =============================== -->
                        <!-- B. LIVE RUNTIME PREVIEW ON CANVAS -->
                        <!-- =============================== -->
                        <div v-else class="space-y-6">
                            <!-- Live Banner -->
                            <div class="p-6 rounded-3xl bg-gradient-to-r from-slate-950 via-slate-900 to-slate-950 border border-slate-800 shadow-xl flex items-center justify-between">
                                <div class="space-y-1">
                                    <span class="text-[10px] font-bold uppercase tracking-wider text-emerald-400 font-mono">LIVE PREVIEW</span>
                                    <h3 class="text-xl font-black text-white">{{ selectedModule?.name }} Overview</h3>
                                    <p class="text-xs text-slate-400">{{ selectedModule?.description }}</p>
                                </div>
                                <span class="px-3 py-1 rounded-full text-xs font-bold bg-emerald-500/20 text-emerald-300 border border-emerald-500/30">
                                    Aktif di Runtime
                                </span>
                            </div>

                            <!-- Live Grid Render -->
                            <div
                                :class="[
                                    'grid gap-5',
                                    canvasConfig.columns === 1 ? 'grid-cols-1' : '',
                                    canvasConfig.columns === 2 ? 'grid-cols-1 md:grid-cols-2' : '',
                                    canvasConfig.columns === 3 ? 'grid-cols-1 md:grid-cols-3' : '',
                                    canvasConfig.columns === 4 ? 'grid-cols-1 sm:grid-cols-2 lg:grid-cols-4' : '',
                                ]"
                            >
                                <template v-for="w in canvasWidgets" :key="w.id || w.title">
                                    <div
                                        v-if="w.widget_type === 'kpi'"
                                        :class="[
                                            w.grid_span === 2 ? 'sm:col-span-2' : '',
                                            w.grid_span === 3 ? 'sm:col-span-3' : '',
                                            w.grid_span === 4 ? 'col-span-full' : '',
                                        ]"
                                    >
                                        <KpiStatCard
                                            :title="w.title"
                                            :value="resolveMetricValue(w)"
                                            :formatted-value="w.data_source_config_json?.unit ? ' ' + w.data_source_config_json.unit : ''"
                                            :subtext="w.data_source_config_json?.subtext || 'Terhubung data sistem ERP'"
                                            :icon="w.data_source_config_json?.icon || 'Activity'"
                                            :color-theme="w.data_source_config_json?.color || 'emerald'"
                                            :trend-direction="w.data_source_config_json?.trend_direction || 'up'"
                                            :trend-percentage="w.data_source_config_json?.trend_percentage || 'OK'"
                                        />
                                    </div>

                                    <div
                                        v-else-if="w.widget_type === 'chart'"
                                        :class="[
                                            w.grid_span === 2 ? 'sm:col-span-2' : '',
                                            w.grid_span === 3 ? 'sm:col-span-3' : '',
                                            w.grid_span === 4 ? 'col-span-full' : '',
                                        ]"
                                    >
                                        <InteractiveChartCard
                                            :title="w.title"
                                            :subtitle="w.data_source_config_json?.subtitle || 'Tren analisis waktu nyata'"
                                            :chart-type="w.data_source_config_json?.chart_type || 'area'"
                                            :series="defaultChartSeries"
                                            :chart-options="defaultChartOptions"
                                            :height="260"
                                        />
                                    </div>

                                    <div
                                        v-else-if="w.widget_type === 'actions'"
                                        :class="[
                                            'bg-slate-950 border border-slate-800 shadow-xl flex flex-col justify-between',
                                            canvasConfig.card_radius,
                                            canvasConfig.card_padding,
                                            w.grid_span === 2 ? 'sm:col-span-2' : '',
                                            w.grid_span === 3 ? 'sm:col-span-3' : '',
                                            w.grid_span === 4 ? 'col-span-full' : '',
                                        ]"
                                    >
                                        <div class="space-y-1 mb-3">
                                            <h4 class="font-bold text-xs text-white uppercase tracking-wider">{{ w.title }}</h4>
                                            <p class="text-[11px] text-slate-400">{{ w.data_source_config_json?.subtitle }}</p>
                                        </div>
                                        <div class="flex flex-wrap gap-2">
                                            <button class="px-3 py-1.5 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-white font-bold text-xs flex items-center gap-1.5 transition">
                                                <Plus class="w-3.5 h-3.5" />
                                                <span>+ Input Baru</span>
                                            </button>
                                            <button class="px-3 py-1.5 rounded-xl bg-slate-900 border border-slate-800 text-slate-300 font-semibold text-xs flex items-center gap-1.5">
                                                <FileSpreadsheet class="w-3.5 h-3.5" />
                                                <span>Ekspor Laporan</span>
                                            </button>
                                        </div>
                                    </div>

                                    <div
                                        v-else
                                        :class="[
                                            'bg-gradient-to-r from-emerald-950/60 to-slate-900 border border-emerald-800/60 p-5 text-white flex items-center justify-between',
                                            canvasConfig.card_radius,
                                            w.grid_span === 2 ? 'sm:col-span-2' : '',
                                            w.grid_span === 3 ? 'sm:col-span-3' : '',
                                            w.grid_span === 4 ? 'col-span-full' : '',
                                        ]"
                                    >
                                        <div class="space-y-1">
                                            <h4 class="font-bold text-sm text-emerald-300 flex items-center gap-2">
                                                <Sparkles class="w-4 h-4 text-emerald-400" />
                                                {{ w.title }}
                                            </h4>
                                            <p class="text-xs text-slate-300">{{ w.data_source_config_json?.message }}</p>
                                        </div>
                                    </div>
                                </template>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- ============================================================== -->
            <!-- TAB 2: ENTITY & FIELD DESIGNER WITH CROSS-MODULE RELATIONS     -->
            <!-- ============================================================== -->
            <div v-if="activeTab === 'entities'" class="space-y-6">
                <!-- Module & Entity Selector Bar -->
                <div class="bg-slate-950 border border-slate-800 rounded-3xl p-5 flex flex-col md:flex-row md:items-center justify-between gap-4">
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
                            @click="showCreateEntityModal = true"
                            class="px-4 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-white font-bold text-xs flex items-center gap-1.5 transition cursor-pointer"
                        >
                            <Plus class="w-4 h-4" />
                            <span>+ Buat Entitas Baru</span>
                        </button>
                    </div>
                </div>

                <!-- Two-Column Layout: Entities List & Field Designer -->
                <div class="grid grid-cols-1 lg:grid-cols-4 gap-6">
                    <!-- Left: Entity Navigation -->
                    <div class="lg:col-span-1 bg-slate-950 border border-slate-800 rounded-3xl p-4 space-y-3">
                        <h3 class="text-xs font-bold uppercase tracking-wider text-slate-400 flex items-center justify-between">
                            <span>Entitas Data</span>
                            <span class="text-emerald-400 font-mono">{{ customEntities.length }}</span>
                        </h3>

                        <div v-if="customEntities.length === 0" class="text-center py-8 text-slate-500 text-xs">
                            Belum ada entitas kustom di modul ini.
                            <button
                                @click="showCreateEntityModal = true"
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
                                    'w-full text-left px-3 py-2.5 rounded-2xl text-xs flex items-center justify-between transition cursor-pointer',
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

                    <!-- Right: Field Designer -->
                    <div class="lg:col-span-3 space-y-6">
                        <div v-if="!selectedEntity" class="bg-slate-950 border border-slate-800 rounded-3xl p-12 text-center text-slate-400">
                            Pilih atau buat entitas data di kolom kiri untuk mendesain form dan field.
                        </div>

                        <div v-else class="space-y-6">
                            <!-- Selected Entity Header -->
                            <div class="bg-slate-950 border border-slate-800 rounded-3xl p-5 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                                <div>
                                    <div class="flex items-center gap-2">
                                        <h3 class="text-base font-bold text-white">{{ selectedEntity.name }}</h3>
                                        <span class="font-mono text-xs text-slate-400 bg-slate-900 px-2 py-0.5 rounded-lg border border-slate-800">
                                            slug: {{ selectedEntity.slug }}
                                        </span>
                                    </div>
                                    <p class="text-xs text-slate-400 mt-1">
                                        {{ selectedEntity.description || 'Entitas data terintegrasi ke form input dan database.' }}
                                    </p>
                                </div>

                                <div class="flex items-center gap-2.5">
                                    <Link
                                        :href="`/modules/${selectedModule?.slug}/entities/${selectedEntity.slug}`"
                                        class="px-3.5 py-1.5 rounded-xl bg-slate-900 hover:bg-slate-800 text-slate-300 text-xs font-semibold flex items-center gap-1.5 transition border border-slate-800"
                                    >
                                        <Eye class="w-3.5 h-3.5" />
                                        <span>Buka Tabel Runtime</span>
                                    </Link>
                                    <button
                                        @click="openAddFieldModal"
                                        class="px-3.5 py-1.5 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-white font-bold text-xs flex items-center gap-1.5 transition cursor-pointer"
                                    >
                                        <Plus class="w-3.5 h-3.5" />
                                        <span>+ Tambah Field</span>
                                    </button>
                                </div>
                            </div>

                            <!-- Fields Table -->
                            <div class="bg-slate-950 border border-slate-800 rounded-3xl overflow-hidden shadow-xl">
                                <table class="w-full text-left text-xs">
                                    <thead class="bg-slate-900/90 text-slate-400 uppercase font-mono text-[10px] tracking-wider border-b border-slate-800">
                                        <tr>
                                            <th class="py-3 px-4">Urutan</th>
                                            <th class="py-3 px-4">Label Field</th>
                                            <th class="py-3 px-4">Field Key (JSON)</th>
                                            <th class="py-3 px-4">Tipe Data</th>
                                            <th class="py-3 px-4">Sumber Relasi / Opsi</th>
                                            <th class="py-3 px-4">Wajib?</th>
                                            <th class="py-3 px-4 text-right">Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-slate-800/60 text-slate-300">
                                        <tr v-for="f in selectedEntity.fields" :key="f.id" class="hover:bg-slate-900/40 transition">
                                            <td class="py-3 px-4 font-mono text-slate-500">#{{ f.sort_order }}</td>
                                            <td class="py-3 px-4 font-bold text-white">{{ f.label }}</td>
                                            <td class="py-3 px-4 font-mono text-emerald-400">{{ f.field_key }}</td>
                                            <td class="py-3 px-4">
                                                <span class="px-2 py-0.5 rounded-md text-[10px] font-mono bg-slate-900 border border-slate-800 text-slate-300">
                                                    {{ f.field_type }}
                                                </span>
                                            </td>
                                            <td class="py-3 px-4">
                                                <span v-if="f.field_type === 'relation' && f.options_json?.source" class="px-2 py-0.5 rounded-md text-[10px] font-semibold bg-emerald-950 text-emerald-300 border border-emerald-800">
                                                    Relasi: {{ f.options_json.source }}
                                                </span>
                                                <span v-else-if="f.field_type === 'select'" class="text-slate-400 truncate max-w-[150px] inline-block">
                                                    {{ Array.isArray(f.options_json) ? f.options_json.join(', ') : '-' }}
                                                </span>
                                                <span v-else class="text-slate-500">-</span>
                                            </td>
                                            <td class="py-3 px-4">
                                                <span v-if="f.is_required" class="text-rose-400 font-bold">Ya</span>
                                                <span v-else class="text-slate-500">Opsional</span>
                                            </td>
                                            <td class="py-3 px-4 text-right space-x-2">
                                                <button @click="openEditFieldModal(f)" class="text-emerald-400 hover:text-emerald-300 font-medium">Edit</button>
                                                <button @click="deleteField(f.id)" class="text-rose-400 hover:text-rose-300 font-medium">Hapus</button>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- ============================================================== -->
            <!-- TAB 3: CROSS-MODULE DATA EXPLORER (215 MENU & 8 CORE MODULES) -->
            <!-- ============================================================== -->
            <div v-if="activeTab === 'cross_data'" class="space-y-6">
                <div class="bg-slate-950 border border-slate-800 rounded-3xl p-5 shadow-xl space-y-4">
                    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
                        <div class="space-y-1">
                            <h2 class="text-base font-bold text-white flex items-center gap-2">
                                <Database class="w-4 h-4 text-amber-400" />
                                <span>Konektor & Penjelajah Data Lintas Modul</span>
                            </h2>
                            <p class="text-xs text-slate-400">
                                Jelajahi data dari seluruh 8 modul utama (HRIS, Finance, SCM, Procurement, Produksi, Quality, GA, R&D) dan 215 menu fitur untuk dihubungkan ke modul kustom Anda.
                            </p>
                        </div>

                        <div class="flex items-center gap-3">
                            <div class="relative">
                                <Search class="w-4 h-4 absolute left-3 top-1/2 -translate-y-1/2 text-slate-500" />
                                <input
                                    v-model="crossDataSearch"
                                    @keyup.enter="loadSourceRecords(selectedDataSourceKey)"
                                    type="text"
                                    placeholder="Cari entri data..."
                                    class="pl-9 pr-4 py-2 rounded-xl bg-slate-900 border border-slate-700 text-xs text-white placeholder-slate-500 focus:ring-emerald-500 focus:border-emerald-500"
                                />
                            </div>
                            <button
                                @click="loadSourceRecords(selectedDataSourceKey)"
                                class="px-3.5 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-white font-bold text-xs flex items-center gap-1.5 transition cursor-pointer"
                            >
                                <RefreshCw class="w-3.5 h-3.5" :class="{ 'animate-spin': isLoadingRecords }" />
                                <span>Muat Data</span>
                            </button>
                        </div>
                    </div>

                    <!-- Available Data Sources Pills -->
                    <div class="pt-3 border-t border-slate-800/80 flex flex-wrap gap-2">
                        <button
                            v-for="(info, key) in dataSources"
                            :key="key"
                            @click="loadSourceRecords(key)"
                            :class="[
                                'px-3 py-1.5 rounded-xl border text-xs font-semibold transition flex items-center gap-2 cursor-pointer',
                                selectedDataSourceKey === key
                                    ? 'bg-emerald-600 text-white border-emerald-500 shadow-md'
                                    : 'bg-slate-900 border-slate-800 text-slate-400 hover:text-white hover:border-slate-700'
                            ]"
                        >
                            <span class="truncate">{{ info.name }}</span>
                            <span class="text-[10px] font-mono px-1.5 py-0.2 rounded bg-black/40 text-emerald-300">
                                {{ info.count }} data
                            </span>
                        </button>
                    </div>
                </div>

                <!-- Live Data Records Table -->
                <div class="bg-slate-950 border border-slate-800 rounded-3xl overflow-hidden shadow-xl">
                    <div class="p-4 bg-slate-900/90 border-b border-slate-800 flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <span class="text-xs font-bold text-white">{{ currentDataSourceInfo?.name || selectedDataSourceKey }}</span>
                            <span class="text-[10px] text-emerald-400 font-mono">({{ liveRecords.length }} record ditampilkan)</span>
                        </div>
                        <span class="text-[11px] text-slate-400">{{ currentDataSourceInfo?.description }}</span>
                    </div>

                    <div v-if="isLoadingRecords" class="p-12 text-center text-slate-400 text-xs">
                        <RefreshCw class="w-6 h-6 animate-spin mx-auto text-emerald-500 mb-2" />
                        <span>Memuat data lintas modul...</span>
                    </div>

                    <div v-else-if="liveRecords.length === 0" class="p-12 text-center text-slate-500 text-xs">
                        Tidak ada rekaman data ditemukan atau klik tombol "Muat Data" di atas.
                    </div>

                    <table v-else class="w-full text-left text-xs">
                        <thead class="bg-slate-900/60 text-slate-400 uppercase font-mono text-[10px] tracking-wider border-b border-slate-800">
                            <tr>
                                <th class="py-3 px-4">ID</th>
                                <th class="py-3 px-4">Kode / Ref</th>
                                <th class="py-3 px-4">Judul / Entri Data</th>
                                <th class="py-3 px-4">Deskripsi / Detail Terkait</th>
                                <th class="py-3 px-4">Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-800/60 text-slate-300">
                            <tr v-for="rec in liveRecords" :key="rec.id" class="hover:bg-slate-900/40 transition">
                                <td class="py-3 px-4 font-mono text-slate-500">#{{ rec.id }}</td>
                                <td class="py-3 px-4 font-mono font-bold text-emerald-400">{{ rec.code || '-' }}</td>
                                <td class="py-3 px-4 font-bold text-white">{{ rec.title }}</td>
                                <td class="py-3 px-4 text-slate-400">{{ rec.subtitle || '-' }}</td>
                                <td class="py-3 px-4">
                                    <span class="px-2 py-0.5 rounded-md text-[10px] font-bold uppercase bg-slate-900 border border-slate-800 text-emerald-300">
                                        {{ rec.badge || 'Aktif' }}
                                    </span>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- ============================================================== -->
            <!-- TAB 4: MODULE BUILDER & ROLES RBAC                             -->
            <!-- ============================================================== -->
            <div v-if="activeTab === 'modules'" class="space-y-6">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                    <div>
                        <h2 class="text-base font-bold text-white flex items-center gap-2">
                            <Boxes class="w-4 h-4 text-emerald-400" />
                            <span>Daftar Modul ERP Aktif</span>
                        </h2>
                        <p class="text-xs text-slate-400">
                            Kelola modul bawaan sistem dan daftarkan modul kustom baru tanpa coding.
                        </p>
                    </div>

                    <button
                        @click="showCreateModuleModal = true"
                        class="px-4 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-white font-bold text-xs flex items-center gap-1.5 transition shadow-sm cursor-pointer"
                    >
                        <Plus class="w-4 h-4" />
                        <span>+ Buat Modul Kustom Baru</span>
                    </button>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
                    <div
                        v-for="mod in modules"
                        :key="mod.id"
                        class="bg-slate-950 border border-slate-800 rounded-3xl p-5 flex flex-col justify-between hover:border-slate-700 transition group"
                    >
                        <div class="space-y-3">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 rounded-2xl bg-slate-900 border border-slate-800 text-emerald-400 flex items-center justify-center font-bold">
                                        <Boxes class="w-5 h-5" />
                                    </div>
                                    <div>
                                        <h3 class="font-bold text-sm text-white group-hover:text-emerald-400 transition">
                                            {{ mod.name }}
                                        </h3>
                                        <span class="text-[11px] font-mono text-slate-400">/{{ mod.slug }}</span>
                                    </div>
                                </div>

                                <span v-if="mod.is_custom" class="text-[10px] font-semibold px-2 py-0.5 rounded-full bg-emerald-500/20 text-emerald-300 border border-emerald-500/30">
                                    Kustom
                                </span>
                                <span v-else class="text-[10px] font-semibold px-2 py-0.5 rounded-full bg-slate-900 text-slate-400 border border-slate-800">
                                    Core
                                </span>
                            </div>

                            <p class="text-xs text-slate-400 line-clamp-2">
                                {{ mod.description || 'Modul terintegrasi HerbaTech Unified ERP.' }}
                            </p>
                        </div>

                        <div class="pt-4 mt-4 border-t border-slate-800 flex items-center justify-between">
                            <Link
                                :href="mod.route_prefix"
                                class="text-xs font-semibold text-emerald-400 hover:underline flex items-center gap-1"
                            >
                                <span>Buka Modul</span>
                                <ArrowLeft class="w-3.5 h-3.5 rotate-180" />
                            </Link>

                            <button
                                @click="
                                    selectedModuleId = mod.id;
                                    activeTab = 'canvas';
                                "
                                class="px-3 py-1.5 rounded-xl bg-slate-900 hover:bg-slate-800 text-slate-300 text-xs font-semibold transition"
                            >
                                Edit Kanvas
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- ============================================================== -->
            <!-- TAB 5: WORKFLOWS & APPROVAL PIPELINE                          -->
            <!-- ============================================================== -->
            <div v-if="activeTab === 'workflows'" class="space-y-6">
                <div class="bg-slate-950 border border-slate-800 rounded-3xl p-5 shadow-xl space-y-2">
                    <h2 class="text-base font-bold text-white flex items-center gap-2">
                        <GitBranch class="w-4 h-4 text-emerald-400" />
                        <span>Alur Persetujuan & Multi-Stage Approval Matrix</span>
                    </h2>
                    <p class="text-xs text-slate-400">
                        Setiap modul dapat memiliki alur persetujuan bertingkat dengan batas nominal (threshold), SLA jam kerja, dan validasi tanda tangan digital.
                    </p>
                </div>
            </div>
        </main>

        <!-- ========================================== -->
        <!-- MODAL: ADD / EDIT WIDGET PROPERTIES        -->
        <!-- ========================================== -->
        <div v-if="showWidgetConfigModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/80 backdrop-blur-xs">
            <div class="bg-slate-950 border border-slate-800 rounded-3xl max-w-lg w-full p-6 space-y-5 shadow-2xl text-xs">
                <div class="flex items-center justify-between border-b border-slate-800 pb-3">
                    <h3 class="font-bold text-white text-sm flex items-center gap-2">
                        <Edit3 class="w-4 h-4 text-emerald-400" />
                        <span>Konfigurasi Blok Kanvas: {{ widgetEditForm.title }}</span>
                    </h3>
                    <button @click="showWidgetConfigModal = false" class="text-slate-400 hover:text-white cursor-pointer">
                        <X class="w-5 h-5" />
                    </button>
                </div>

                <div class="space-y-4">
                    <div>
                        <label class="block font-semibold text-slate-300 mb-1">Judul Blok</label>
                        <input
                            v-model="widgetEditForm.title"
                            type="text"
                            class="w-full bg-slate-900 border border-slate-700 text-white text-xs rounded-xl px-3.5 py-2.5 focus:ring-emerald-500"
                        />
                    </div>

                    <!-- If KPI: Metric Source or Manual -->
                    <template v-if="widgetEditForm.widget_type === 'kpi'">
                        <div>
                            <label class="block font-semibold text-slate-300 mb-1">Ikatan Metrik Lintas Modul (Data Source)</label>
                            <select
                                v-model="widgetEditForm.metric_key"
                                class="w-full bg-slate-900 border border-slate-700 text-white text-xs rounded-xl px-3.5 py-2.5"
                            >
                                <option value="">-- Manual (Input Angka Sendiri) --</option>
                                <option v-for="(m, k) in availableMetrics" :key="k" :value="k">
                                    {{ m.label }} (Saat Ini: {{ m.value }} {{ m.unit }})
                                </option>
                            </select>
                        </div>

                        <div v-if="!widgetEditForm.metric_key" class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="block font-semibold text-slate-300 mb-1">Nilai Manual</label>
                                <input
                                    v-model="widgetEditForm.custom_value"
                                    type="text"
                                    placeholder="Contoh: 150 atau 99.5%"
                                    class="w-full bg-slate-900 border border-slate-700 text-white text-xs rounded-xl px-3.5 py-2"
                                />
                            </div>
                            <div>
                                <label class="block font-semibold text-slate-300 mb-1">Satuan / Unit</label>
                                <input
                                    v-model="widgetEditForm.unit"
                                    type="text"
                                    placeholder="Unit / Dokumen / Orang"
                                    class="w-full bg-slate-900 border border-slate-700 text-white text-xs rounded-xl px-3.5 py-2"
                                />
                            </div>
                        </div>

                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="block font-semibold text-slate-300 mb-1">Warna Aksen</label>
                                <select v-model="widgetEditForm.color" class="w-full bg-slate-900 border border-slate-700 text-white text-xs rounded-xl px-3 py-2">
                                    <option value="emerald">Emerald</option>
                                    <option value="blue">Blue</option>
                                    <option value="amber">Amber</option>
                                    <option value="violet">Violet</option>
                                    <option value="rose">Rose</option>
                                    <option value="teal">Teal</option>
                                    <option value="cyan">Cyan</option>
                                </select>
                            </div>
                            <div>
                                <label class="block font-semibold text-slate-300 mb-1">Subteks Penjelas</label>
                                <input
                                    v-model="widgetEditForm.subtext"
                                    type="text"
                                    placeholder="Contoh: Target bulanan"
                                    class="w-full bg-slate-900 border border-slate-700 text-white text-xs rounded-xl px-3 py-2"
                                />
                            </div>
                        </div>
                    </template>

                    <!-- If Chart -->
                    <template v-else-if="widgetEditForm.widget_type === 'chart'">
                        <div>
                            <label class="block font-semibold text-slate-300 mb-1">Tipe ApexChart</label>
                            <select v-model="widgetEditForm.chart_type" class="w-full bg-slate-900 border border-slate-700 text-white text-xs rounded-xl px-3.5 py-2.5">
                                <option value="area">Area Chart (Smooth)</option>
                                <option value="bar">Bar Chart (Kolom)</option>
                                <option value="line">Line Chart (Garis)</option>
                                <option value="donut">Donut Chart (Lingkaran)</option>
                            </select>
                        </div>
                    </template>

                    <!-- If Table: Source Key -->
                    <template v-else-if="widgetEditForm.widget_type === 'table'">
                        <div>
                            <label class="block font-semibold text-slate-300 mb-1">Sumber Data Lintas Modul Tersemat</label>
                            <select v-model="widgetEditForm.source_key" class="w-full bg-slate-900 border border-slate-700 text-white text-xs rounded-xl px-3.5 py-2.5">
                                <option v-for="(info, key) in dataSources" :key="key" :value="key">
                                    {{ info.name }} ({{ info.module }})
                                </option>
                            </select>
                        </div>
                    </template>

                    <!-- If Banner -->
                    <template v-else-if="widgetEditForm.widget_type === 'banner'">
                        <div>
                            <label class="block font-semibold text-slate-300 mb-1">Pesan / Instruksi Banner</label>
                            <textarea
                                v-model="widgetEditForm.message"
                                rows="3"
                                class="w-full bg-slate-900 border border-slate-700 text-white text-xs rounded-xl px-3.5 py-2"
                            ></textarea>
                        </div>
                    </template>
                </div>

                <div class="pt-4 flex items-center justify-end gap-3 border-t border-slate-800">
                    <button
                        type="button"
                        @click="showWidgetConfigModal = false"
                        class="px-4 py-2 rounded-xl text-slate-400 hover:text-white"
                    >
                        Batal
                    </button>
                    <button
                        type="button"
                        @click="applyWidgetConfig"
                        class="px-5 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-white font-bold flex items-center gap-2 cursor-pointer shadow-sm"
                    >
                        <Check class="w-4 h-4" />
                        <span>Terapkan ke Kanvas</span>
                    </button>
                </div>
            </div>
        </div>

        <!-- ========================================== -->
        <!-- MODAL: CREATE MODULE                       -->
        <!-- ========================================== -->
        <div v-if="showCreateModuleModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/80 backdrop-blur-xs">
            <div class="bg-slate-950 border border-slate-800 rounded-3xl max-w-md w-full p-6 space-y-5 shadow-2xl">
                <div class="flex items-center justify-between border-b border-slate-800 pb-3">
                    <h3 class="font-bold text-white text-base flex items-center gap-2">
                        <Boxes class="w-5 h-5 text-emerald-400" />
                        <span>Daftarkan Modul Kustom Baru</span>
                    </h3>
                    <button @click="showCreateModuleModal = false" class="text-slate-400 hover:text-white cursor-pointer">
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
                            placeholder="Contoh: Maintenance & Utility"
                            class="w-full bg-slate-900 border border-slate-800 text-white text-xs rounded-xl px-3 py-2.5 focus:ring-emerald-500"
                        />
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-300 mb-1">Slug Route Prefix</label>
                        <input
                            v-model="moduleForm.slug"
                            type="text"
                            required
                            class="w-full bg-slate-900 border border-slate-800 text-emerald-400 font-mono text-xs rounded-xl px-3 py-2.5"
                        />
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-300 mb-1">Deskripsi</label>
                        <textarea
                            v-model="moduleForm.description"
                            rows="2"
                            class="w-full bg-slate-900 border border-slate-800 text-white text-xs rounded-xl px-3 py-2"
                        ></textarea>
                    </div>

                    <div class="pt-4 flex items-center justify-end gap-3 border-t border-slate-800">
                        <button type="button" @click="showCreateModuleModal = false" class="px-4 py-2 rounded-xl text-xs text-slate-400 hover:text-white">Batal</button>
                        <button type="submit" :disabled="moduleForm.processing" class="px-5 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-white font-bold text-xs flex items-center gap-2">
                            <Check class="w-4 h-4" />
                            <span>Simpan Modul</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- ========================================== -->
        <!-- MODAL: CREATE ENTITY                       -->
        <!-- ========================================== -->
        <div v-if="showCreateEntityModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/80 backdrop-blur-xs">
            <div class="bg-slate-950 border border-slate-800 rounded-3xl max-w-md w-full p-6 space-y-5 shadow-2xl">
                <div class="flex items-center justify-between border-b border-slate-800 pb-3">
                    <h3 class="font-bold text-white text-base flex items-center gap-2">
                        <Table class="w-5 h-5 text-emerald-400" />
                        <span>Buat Entitas Data Baru</span>
                    </h3>
                    <button @click="showCreateEntityModal = false" class="text-slate-400 hover:text-white cursor-pointer">
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
                            placeholder="Contoh: Dokumen Pemeliharaan"
                            class="w-full bg-slate-900 border border-slate-800 text-white text-xs rounded-xl px-3 py-2.5 focus:ring-emerald-500"
                        />
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-300 mb-1">Entity Slug</label>
                        <input
                            v-model="entityForm.slug"
                            type="text"
                            required
                            class="w-full bg-slate-900 border border-slate-800 text-emerald-400 font-mono text-xs rounded-xl px-3 py-2.5"
                        />
                    </div>

                    <div class="pt-4 flex items-center justify-end gap-3 border-t border-slate-800">
                        <button type="button" @click="showCreateEntityModal = false" class="px-4 py-2 rounded-xl text-xs text-slate-400 hover:text-white">Batal</button>
                        <button type="submit" :disabled="entityForm.processing" class="px-5 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-white font-bold text-xs flex items-center gap-2">
                            <Check class="w-4 h-4" />
                            <span>Simpan Entitas</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- ========================================== -->
        <!-- MODAL: ADD / EDIT FIELD WITH RELATION      -->
        <!-- ========================================== -->
        <div v-if="showFieldModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/80 backdrop-blur-xs">
            <div class="bg-slate-950 border border-slate-800 rounded-3xl max-w-md w-full p-6 space-y-5 shadow-2xl">
                <div class="flex items-center justify-between border-b border-slate-800 pb-3">
                    <h3 class="font-bold text-white text-base flex items-center gap-2">
                        <Sliders class="w-5 h-5 text-emerald-400" />
                        <span>{{ editingField ? 'Edit Field' : 'Tambah Field Baru' }}</span>
                    </h3>
                    <button @click="showFieldModal = false" class="text-slate-400 hover:text-white cursor-pointer">
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
                            placeholder="Contoh: PIC Penanggung Jawab, Produk Terkait"
                            class="w-full bg-slate-900 border border-slate-800 text-white text-xs rounded-xl px-3 py-2.5 focus:ring-emerald-500"
                        />
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-300 mb-1">Field Key (JSON property)</label>
                        <input
                            v-model="fieldForm.field_key"
                            type="text"
                            required
                            class="w-full bg-slate-900 border border-slate-800 text-emerald-400 font-mono text-xs rounded-xl px-3 py-2.5"
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
                            <option value="relation">Relation (Relasi Data Lintas Modul & Menu)</option>
                            <option value="file">File Upload (Dokumen / Foto)</option>
                            <option value="signature">Digital Signature (Tanda Tangan)</option>
                        </select>
                    </div>

                    <!-- IF RELATION: SELECT CROSS-MODULE SOURCE -->
                    <div v-if="fieldForm.field_type === 'relation'" class="p-3 bg-emerald-950/40 border border-emerald-800/60 rounded-2xl space-y-2">
                        <label class="block text-xs font-bold text-emerald-300">Sumber Relasi Data Lintas Modul</label>
                        <select
                            v-model="fieldForm.relation_source"
                            class="w-full bg-slate-900 border border-emerald-500/50 text-white text-xs rounded-xl px-3 py-2"
                        >
                            <option v-for="(info, key) in dataSources" :key="key" :value="key">
                                {{ info.name }} ({{ info.module }}) - {{ info.count }} data
                            </option>
                        </select>
                        <p class="text-[10px] text-slate-400">
                            Saat pengguna mengisi formulir, field ini otomatis menampilkan dropdown data dari modul tersebut.
                        </p>
                    </div>

                    <!-- IF SELECT: OPTIONS -->
                    <div v-if="fieldForm.field_type === 'select'">
                        <label class="block text-xs font-semibold text-slate-300 mb-1">Opsi Dropdown (Pisahkan koma)</label>
                        <input
                            v-model="fieldForm.options_text"
                            type="text"
                            placeholder="Rendah, Sedang, Tinggi, Kritis"
                            class="w-full bg-slate-900 border border-slate-800 text-white text-xs rounded-xl px-3 py-2"
                        />
                    </div>

                    <div class="grid grid-cols-2 gap-4 pt-1">
                        <label class="flex items-center gap-2 text-xs text-slate-300 cursor-pointer">
                            <input
                                v-model="fieldForm.is_required"
                                type="checkbox"
                                class="rounded bg-slate-950 border-slate-800 text-emerald-600 focus:ring-emerald-500"
                            />
                            <span>Wajib Diisi</span>
                        </label>
                        <label class="flex items-center gap-2 text-xs text-slate-300 cursor-pointer">
                            <input
                                v-model="fieldForm.show_in_table"
                                type="checkbox"
                                class="rounded bg-slate-950 border-slate-800 text-emerald-600 focus:ring-emerald-500"
                            />
                            <span>Tampil di Tabel</span>
                        </label>
                    </div>

                    <div class="pt-4 flex items-center justify-end gap-3 border-t border-slate-800">
                        <button type="button" @click="showFieldModal = false" class="px-4 py-2 rounded-xl text-xs text-slate-400 hover:text-white">Batal</button>
                        <button type="submit" class="px-5 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-white font-bold text-xs flex items-center gap-2 shadow-sm">
                            <Check class="w-4 h-4" />
                            <span>Simpan Field</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</template>
