<script setup>
import { ref } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import ModuleLayout from '@/Layouts/ModuleLayout.vue';
import { useDeveloperModeStore } from '@/Stores/developerModeStore';
import {
    Plus,
    Search,
    Download,
    FileSpreadsheet,
    FileText,
    Edit3,
    Trash2,
    Eye,
    CheckCircle2,
    XCircle,
    Clock,
    Sliders,
    Layers,
    PenTool,
    Paperclip,
    ArrowUpDown,
} from 'lucide-vue-next';

const props = defineProps({
    module: {
        type: Object,
        required: true,
    },
    entity: {
        type: Object,
        required: true,
    },
    fields: {
        type: Array,
        default: () => [],
    },
    records: {
        type: Object,
        required: true,
    },
    stats: {
        type: Object,
        default: () => ({}),
    },
    relationOptions: {
        type: Object,
        default: () => ({}),
    },
});

const devStore = useDeveloperModeStore();
const searchQuery = ref('');
const activeStatusFilter = ref('all');

// Table columns to show
const tableFields = computedFields();

function computedFields() {
    return props.fields.filter((f) => f.show_in_table);
}

function handleSearch() {
    router.get(
        `/modules/${props.module.slug}/entities/${props.entity.slug}`,
        { q: searchQuery.value },
        { preserveState: true, replace: true }
    );
}

function formatFieldValue(val, field) {
    if (val === null || val === undefined || val === '') return '-';

    if (field.field_type === 'currency') {
        const num = Number(val);
        if (isNaN(num)) return val;
        return new Intl.NumberFormat('id-ID', {
            style: 'currency',
            currency: 'IDR',
            maximumFractionDigits: 0,
        }).format(num);
    }

    if (field.field_type === 'date') {
        try {
            return new Date(val).toLocaleDateString('id-ID', {
                year: 'numeric',
                month: 'short',
                day: 'numeric',
            });
        } catch (e) {
            return val;
        }
    }

    return val;
}

function deleteRecord(recordId) {
    if (confirm('Yakin ingin menghapus dokumen transaksi ini?')) {
        router.delete(`/modules/${props.module.slug}/entities/${props.entity.slug}/${recordId}`);
    }
}

function quickApprove(recordId) {
    router.put(
        `/modules/${props.module.slug}/entities/${props.entity.slug}/${recordId}`,
        { status: 'approved' },
        { preserveScroll: true }
    );
}

function quickReject(recordId) {
    router.put(
        `/modules/${props.module.slug}/entities/${props.entity.slug}/${recordId}`,
        { status: 'rejected' },
        { preserveScroll: true }
    );
}
</script>

<template>
    <ModuleLayout
        :title="`${entity.name} - Data Table`"
        :module-slug="module.slug"
        :module-name="module.name"
        :color-theme="module.color_theme"
        :menus="module.menus"
        :breadcrumbs="['Entitas', entity.name]"
    >
        <Head :title="`${entity.name} - ${module.name}`" />

        <div class="space-y-6">
            <!-- Header Section -->
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
                <div>
                    <div class="flex items-center gap-3">
                        <h1 class="text-xl font-bold text-slate-900 dark:text-white tracking-tight">
                            {{ entity.name }}
                        </h1>
                        <span class="text-xs font-mono px-2 py-0.5 rounded bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-400 border border-slate-200 dark:border-slate-700">
                            {{ records.total }} Dokumen
                        </span>
                    </div>
                    <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">
                        {{ entity.description || 'Kelola dan arsipkan data transaksi modul kustom.' }}
                    </p>
                </div>

                <!-- Action Controls: Exports & Create Button -->
                <div class="flex flex-wrap items-center gap-2">
                    <Link
                        v-if="devStore.isEnabled"
                        href="/developer-mode"
                        class="px-3 py-2 rounded-xl bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 text-emerald-600 dark:text-emerald-400 border border-slate-300 dark:border-slate-700 font-semibold text-xs flex items-center gap-1.5 transition"
                        title="Sesuaikan field dan workflow entitas ini"
                    >
                        <Sliders class="w-3.5 h-3.5" />
                        <span class="hidden sm:inline">Desain Field</span>
                    </Link>

                    <!-- Export PDF -->
                    <a
                        :href="`/modules/${module.slug}/entities/${entity.slug}/export/pdf`"
                        target="_blank"
                        class="px-3 py-2 rounded-xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 hover:bg-slate-50 dark:hover:bg-slate-800 text-slate-700 dark:text-slate-300 text-xs font-semibold flex items-center gap-1.5 transition shadow-2xs"
                    >
                        <FileText class="w-3.5 h-3.5 text-rose-500" />
                        <span>PDF</span>
                    </a>

                    <!-- Export Excel / CSV -->
                    <a
                        :href="`/modules/${module.slug}/entities/${entity.slug}/export/excel`"
                        class="px-3 py-2 rounded-xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 hover:bg-slate-50 dark:hover:bg-slate-800 text-slate-700 dark:text-slate-300 text-xs font-semibold flex items-center gap-1.5 transition shadow-2xs"
                    >
                        <FileSpreadsheet class="w-3.5 h-3.5 text-emerald-500" />
                        <span>Excel</span>
                    </a>

                    <!-- Create New Document -->
                    <Link
                        :href="`/modules/${module.slug}/entities/${entity.slug}/create`"
                        class="px-4 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-white font-semibold text-xs flex items-center gap-1.5 shadow-md shadow-emerald-600/20 transition"
                    >
                        <Plus class="w-4 h-4" />
                        <span>+ Dokumen Baru</span>
                    </Link>
                </div>
            </div>

            <!-- Stats Ribbon -->
            <div class="grid grid-cols-3 gap-3 sm:gap-4">
                <div class="bg-white dark:bg-slate-900 rounded-xl border border-slate-200 dark:border-slate-800 p-3 flex items-center justify-between">
                    <div>
                        <div class="text-[10px] uppercase font-bold text-slate-400">Total Dokumen</div>
                        <div class="text-base font-bold text-slate-900 dark:text-white mt-0.5">{{ stats.total_records || 0 }}</div>
                    </div>
                    <Layers class="w-5 h-5 text-slate-400" />
                </div>
                <div class="bg-white dark:bg-slate-900 rounded-xl border border-slate-200 dark:border-slate-800 p-3 flex items-center justify-between">
                    <div>
                        <div class="text-[10px] uppercase font-bold text-amber-500">Draft / Menunggu</div>
                        <div class="text-base font-bold text-amber-600 dark:text-amber-400 mt-0.5">{{ stats.draft_records || 0 }}</div>
                    </div>
                    <Clock class="w-5 h-5 text-amber-500" />
                </div>
                <div class="bg-white dark:bg-slate-900 rounded-xl border border-slate-200 dark:border-slate-800 p-3 flex items-center justify-between">
                    <div>
                        <div class="text-[10px] uppercase font-bold text-emerald-500">Disetujui</div>
                        <div class="text-base font-bold text-emerald-600 dark:text-emerald-400 mt-0.5">{{ stats.approved_records || 0 }}</div>
                    </div>
                    <CheckCircle2 class="w-5 h-5 text-emerald-500" />
                </div>
            </div>

            <!-- Table Container -->
            <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 shadow-xs overflow-hidden">
                <!-- Search & Filters Toolbar -->
                <div class="p-4 border-b border-slate-200 dark:border-slate-800 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                    <form @submit.prevent="handleSearch" class="relative max-w-sm w-full">
                        <Search class="w-3.5 h-3.5 absolute left-3 top-1/2 -translate-y-1/2 text-slate-400" />
                        <input
                            v-model="searchQuery"
                            type="text"
                            placeholder="Cari no. dokumen atau atribut data..."
                            class="w-full pl-9 pr-4 py-2 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-xs text-slate-800 dark:text-slate-100 placeholder-slate-400 focus:ring-emerald-500 focus:border-emerald-500"
                        />
                    </form>

                    <div class="text-xs text-slate-400 flex items-center gap-2">
                        <span>Menampilkan halaman {{ records.current_page }} dari {{ records.last_page }}</span>
                    </div>
                </div>

                <!-- Table Content -->
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs text-slate-700 dark:text-slate-300">
                        <thead class="bg-slate-50 dark:bg-slate-800/60 uppercase font-mono text-[10px] text-slate-500 dark:text-slate-400 border-b border-slate-200 dark:border-slate-800">
                            <tr>
                                <th class="px-4 py-3 font-semibold">No. Dokumen</th>
                                <th
                                    v-for="field in tableFields"
                                    :key="field.id"
                                    class="px-4 py-3 font-semibold"
                                >
                                    {{ field.label }}
                                </th>
                                <th class="px-4 py-3 font-semibold">Status</th>
                                <th class="px-4 py-3 font-semibold">Dibuat Oleh</th>
                                <th class="px-4 py-3 font-semibold text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                            <tr v-if="records.data.length === 0">
                                <td :colspan="tableFields.length + 4" class="px-6 py-12 text-center text-slate-400 text-xs">
                                    <div class="max-w-xs mx-auto space-y-2">
                                        <Layers class="w-8 h-8 mx-auto text-slate-300 dark:text-slate-700" />
                                        <p class="font-medium">Belum ada rekaman data transaksi.</p>
                                        <Link
                                            :href="`/modules/${module.slug}/entities/${entity.slug}/create`"
                                            class="inline-block mt-2 text-emerald-600 dark:text-emerald-400 font-semibold hover:underline"
                                        >
                                            Buat Dokumen Pertama Sekarang
                                        </Link>
                                    </div>
                                </td>
                            </tr>

                            <tr
                                v-for="record in records.data"
                                :key="record.id"
                                class="hover:bg-slate-50/80 dark:hover:bg-slate-800/40 transition"
                            >
                                <!-- Record Number -->
                                <td class="px-4 py-3 font-mono font-bold text-slate-900 dark:text-white">
                                    <Link
                                        :href="`/modules/${module.slug}/entities/${entity.slug}/${record.id}/edit`"
                                        class="hover:text-emerald-600 dark:hover:text-emerald-400"
                                    >
                                        {{ record.record_number }}
                                    </Link>
                                </td>

                                <!-- Dynamic Columns -->
                                <td
                                    v-for="field in tableFields"
                                    :key="field.id"
                                    class="px-4 py-3"
                                >
                                    <!-- Signature Preview / Indicator -->
                                    <template v-if="field.field_type === 'signature'">
                                        <span v-if="record.data_json && record.data_json[field.field_key]" class="inline-flex items-center gap-1 text-[11px] font-semibold text-emerald-600 dark:text-emerald-400 bg-emerald-50 dark:bg-emerald-950 px-2 py-0.5 rounded border border-emerald-200 dark:border-emerald-800">
                                            <PenTool class="w-3 h-3" />
                                            <span>Signed</span>
                                        </span>
                                        <span v-else class="text-slate-400 text-[11px]">Belum TTD</span>
                                    </template>

                                    <!-- File Preview -->
                                    <template v-else-if="field.field_type === 'file'">
                                        <span v-if="record.data_json && record.data_json[field.field_key]" class="inline-flex items-center gap-1 text-[11px] text-blue-600 dark:text-blue-400 underline">
                                            <Paperclip class="w-3 h-3" />
                                            <span>Lampiran</span>
                                        </span>
                                        <span v-else class="text-slate-400 text-[11px]">-</span>
                                    </template>

                                    <!-- Select Badge -->
                                    <template v-else-if="field.field_type === 'select'">
                                        <span class="px-2 py-0.5 rounded text-[11px] font-medium bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300">
                                            {{ record.data_json ? record.data_json[field.field_key] || '-' : '-' }}
                                        </span>
                                    </template>

                                    <!-- Default Field Output -->
                                    <template v-else>
                                        <span>
                                            {{ formatFieldValue(record.data_json ? record.data_json[field.field_key] : null, field) }}
                                        </span>
                                    </template>
                                </td>

                                <!-- Status Badge -->
                                <td class="px-4 py-3">
                                    <span
                                        :class="[
                                            'px-2 py-0.5 rounded text-[10px] font-bold uppercase tracking-wider border',
                                            record.status === 'approved'
                                                ? 'bg-emerald-50 dark:bg-emerald-950/60 text-emerald-700 dark:text-emerald-300 border-emerald-200 dark:border-emerald-800'
                                                : record.status === 'submitted'
                                                ? 'bg-blue-50 dark:bg-blue-950/60 text-blue-700 dark:text-blue-300 border-blue-200 dark:border-blue-800'
                                                : record.status === 'rejected'
                                                ? 'bg-rose-50 dark:bg-rose-950/60 text-rose-700 dark:text-rose-300 border-rose-200 dark:border-rose-800'
                                                : 'bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-400 border-slate-200 dark:border-slate-700',
                                        ]"
                                    >
                                        {{ record.status }}
                                    </span>
                                </td>

                                <!-- Creator & Timestamp -->
                                <td class="px-4 py-3 text-slate-500 dark:text-slate-400 text-[11px]">
                                    <div>{{ record.creator?.name || 'System' }}</div>
                                    <div class="text-[10px] opacity-70">{{ new Date(record.created_at).toLocaleDateString('id-ID') }}</div>
                                </td>

                                <!-- Action Buttons -->
                                <td class="px-4 py-3 text-right">
                                    <div class="flex items-center justify-end gap-1.5">
                                        <!-- Quick Approve / Reject if submitted -->
                                        <template v-if="record.status === 'submitted'">
                                            <button
                                                @click="quickApprove(record.id)"
                                                class="p-1 rounded bg-emerald-50 hover:bg-emerald-100 dark:bg-emerald-950/60 dark:hover:bg-emerald-900 text-emerald-600 dark:text-emerald-400"
                                                title="Setujui Dokumen (Quick Approve)"
                                            >
                                                <CheckCircle2 class="w-4 h-4" />
                                            </button>
                                            <button
                                                @click="quickReject(record.id)"
                                                class="p-1 rounded bg-rose-50 hover:bg-rose-100 dark:bg-rose-950/60 dark:hover:bg-rose-900 text-rose-600 dark:text-rose-400"
                                                title="Tolak Dokumen (Quick Reject)"
                                            >
                                                <XCircle class="w-4 h-4" />
                                            </button>
                                        </template>

                                        <!-- Edit -->
                                        <Link
                                            :href="`/modules/${module.slug}/entities/${entity.slug}/${record.id}/edit`"
                                            class="p-1 rounded hover:bg-slate-100 dark:hover:bg-slate-800 text-slate-500 hover:text-slate-800 dark:hover:text-white"
                                            title="Edit Dokumen"
                                        >
                                            <Edit3 class="w-4 h-4" />
                                        </Link>

                                        <!-- Delete -->
                                        <button
                                            @click="deleteRecord(record.id)"
                                            class="p-1 rounded hover:bg-rose-50 dark:hover:bg-rose-950/60 text-slate-400 hover:text-rose-600 dark:hover:text-rose-400"
                                            title="Hapus Dokumen"
                                        >
                                            <Trash2 class="w-4 h-4" />
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Pagination footer -->
                <div v-if="records.links && records.links.length > 3" class="px-4 py-3 border-t border-slate-200 dark:border-slate-800 flex items-center justify-between">
                    <div class="text-xs text-slate-500">
                        Total {{ records.total }} dokumen
                    </div>
                    <div class="flex items-center gap-1">
                        <Link
                            v-for="(link, idx) in records.links"
                            :key="idx"
                            :href="link.url || '#'"
                            :class="[
                                'px-2.5 py-1 text-xs rounded-lg transition',
                                link.active
                                    ? 'bg-emerald-600 text-white font-bold'
                                    : 'text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800',
                                !link.url ? 'opacity-40 cursor-not-allowed' : '',
                            ]"
                            v-html="link.label"
                        />
                    </div>
                </div>
            </div>
        </div>
    </ModuleLayout>
</template>
