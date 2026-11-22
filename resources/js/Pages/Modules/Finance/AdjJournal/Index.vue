<script setup>
import { ref, watch } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import ModuleLayout from '@/Layouts/ModuleLayout.vue';
import {
    FileSpreadsheet,
    Plus,
    Search,
    Filter,
    CheckCircle2,
    Clock,
    AlertCircle,
    Eye,
    Download,
    ChevronRight,
    ArrowUpDown,
    Check,
    X,
    RotateCcw
} from 'lucide-vue-next';

const props = defineProps({
    module: Object,
    journals: Object,
    periods: Array,
    filters: Object,
});

const search = ref(props.filters?.search || '');
const status = ref(props.filters?.status || '');
const periodId = ref(props.filters?.period_id || '');

const applyFilters = () => {
    router.get(
        '/finance/aje',
        {
            search: search.value || undefined,
            status: status.value || undefined,
            period_id: periodId.value || undefined,
        },
        { preserveState: true, replace: true }
    );
};

const resetFilters = () => {
    search.value = '';
    status.value = '';
    periodId.value = '';
    applyFilters();
};

const formatCurrency = (val) => {
    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        maximumFractionDigits: 0,
    }).format(val || 0);
};

const getStatusBadge = (st) => {
    switch (st) {
        case 'draft':
            return { label: 'DRAFT', class: 'bg-slate-100 text-slate-700 dark:bg-slate-800 dark:text-slate-300' };
        case 'submitted':
            return { label: 'SUBMITTED', class: 'bg-amber-100 text-amber-800 dark:bg-amber-900/40 dark:text-amber-300' };
        case 'approved':
            return { label: 'APPROVED', class: 'bg-emerald-100 text-emerald-800 dark:bg-emerald-900/40 dark:text-emerald-300' };
        case 'rejected':
            return { label: 'REJECTED', class: 'bg-rose-100 text-rose-800 dark:bg-rose-900/40 dark:text-rose-300' };
        case 'reversed':
            return { label: 'REVERSED', class: 'bg-purple-100 text-purple-800 dark:bg-purple-900/40 dark:text-purple-300' };
        default:
            return { label: st?.toUpperCase() || 'UNKNOWN', class: 'bg-slate-100 text-slate-600' };
    }
};
</script>

<template>
    <Head title="Jurnal Penyesuaian (AJE)" />

    <ModuleLayout :module="module">
        <div class="space-y-6">
            <!-- Header & Action -->
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div>
                    <h1 class="text-2xl font-bold tracking-tight text-slate-900 dark:text-white flex items-center gap-2">
                        <FileSpreadsheet class="w-6 h-6 text-emerald-500" />
                        <span>Jurnal Penyesuaian (Adjusting Journal Entries - AJE)</span>
                    </h1>
                    <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">
                        Maker-Checker Workflow: Setiap penyesuaian wajib berimbang (Debet = Kredit) dan diverifikasi sebelum masuk ke Kertas Kerja 4-Kolom.
                    </p>
                </div>
                <Link
                    href="/finance/aje/create"
                    class="inline-flex items-center gap-2 px-4 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-semibold rounded-xl shadow-md transition-all self-start sm:self-auto"
                >
                    <Plus class="w-4 h-4" />
                    <span>Buat AJE Baru (Maker)</span>
                </Link>
            </div>

            <!-- Filter Card -->
            <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl p-4 shadow-sm space-y-3">
                <div class="grid grid-cols-1 sm:grid-cols-4 gap-3">
                    <div class="sm:col-span-2 relative">
                        <Search class="w-4 h-4 absolute left-3 top-3 text-slate-400" />
                        <input
                            v-model="search"
                            @keyup.enter="applyFilters"
                            type="text"
                            placeholder="Cari nomor jurnal atau uraian penyesuaian..."
                            class="w-full pl-9 pr-3 py-2 text-xs rounded-lg border border-slate-300 dark:border-slate-700 dark:bg-slate-800 text-slate-900 dark:text-white focus:ring-emerald-500 focus:border-emerald-500"
                        />
                    </div>
                    <div>
                        <select
                            v-model="status"
                            @change="applyFilters"
                            class="w-full py-2 px-3 text-xs rounded-lg border border-slate-300 dark:border-slate-700 dark:bg-slate-800 text-slate-900 dark:text-white focus:ring-emerald-500 focus:border-emerald-500"
                        >
                            <option value="">Semua Status</option>
                            <option value="draft">Draft (Belum Diajukan)</option>
                            <option value="submitted">Submitted (Antrean Checker)</option>
                            <option value="approved">Approved (Masuk Kertas Kerja)</option>
                            <option value="rejected">Rejected (Ditolak)</option>
                            <option value="reversed">Reversed (Dibatalkan)</option>
                        </select>
                    </div>
                    <div class="flex items-center gap-2">
                        <select
                            v-model="periodId"
                            @change="applyFilters"
                            class="w-full py-2 px-3 text-xs rounded-lg border border-slate-300 dark:border-slate-700 dark:bg-slate-800 text-slate-900 dark:text-white focus:ring-emerald-500 focus:border-emerald-500"
                        >
                            <option value="">Semua Periode</option>
                            <option v-for="p in periods" :key="p.id" :value="p.id">
                                {{ p.period_name }}
                            </option>
                        </select>
                        <button
                            v-if="search || status || periodId"
                            @click="resetFilters"
                            class="px-2.5 py-2 text-xs text-slate-500 hover:text-slate-800 dark:hover:text-slate-200 border rounded-lg hover:bg-slate-100 dark:hover:bg-slate-800"
                            title="Reset Filter"
                        >
                            <RotateCcw class="w-3.5 h-3.5" />
                        </button>
                    </div>
                </div>
            </div>

            <!-- Table Card -->
            <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl shadow-sm overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm">
                        <thead class="bg-slate-50 dark:bg-slate-800/60 text-xs font-semibold text-slate-500 uppercase border-b border-slate-200 dark:border-slate-800">
                            <tr>
                                <th class="px-4 py-3">No. Jurnal & Tanggal</th>
                                <th class="px-4 py-3">Periode</th>
                                <th class="px-4 py-3">Kategori & Dasar Penyesuaian</th>
                                <th class="px-4 py-3">Maker / Checker</th>
                                <th class="px-4 py-3 text-right">Total Nilai Penyesuaian</th>
                                <th class="px-4 py-3 text-center">Status</th>
                                <th class="px-4 py-3 text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-200 dark:divide-slate-800">
                            <tr
                                v-for="journal in journals.data"
                                :key="journal.id"
                                class="hover:bg-slate-50 dark:hover:bg-slate-800/30 transition-colors"
                            >
                                <td class="px-4 py-3">
                                    <Link :href="`/finance/aje/${journal.id}`" class="font-bold text-emerald-600 dark:text-emerald-400 hover:underline">
                                        {{ journal.journal_number }}
                                    </Link>
                                    <div class="text-xs text-slate-400 mt-0.5">{{ journal.journal_date }}</div>
                                </td>
                                <td class="px-4 py-3 text-xs text-slate-600 dark:text-slate-300">
                                    {{ journal.period?.period_name || '-' }}
                                </td>
                                <td class="px-4 py-3">
                                    <span class="inline-block px-2 py-0.5 rounded text-[10px] font-semibold bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 capitalize mb-1">
                                        {{ journal.category || 'General' }}
                                    </span>
                                    <p class="font-medium text-xs text-slate-800 dark:text-slate-100 max-w-sm truncate">
                                        {{ journal.description }}
                                    </p>
                                </td>
                                <td class="px-4 py-3 text-xs">
                                    <div class="text-slate-700 dark:text-slate-300">
                                        <span class="text-slate-400">Maker:</span> {{ journal.creator?.name || 'Staff' }}
                                    </div>
                                    <div v-if="journal.approver" class="text-emerald-600 dark:text-emerald-400 text-[11px] mt-0.5">
                                        <span class="text-slate-400">Checker:</span> {{ journal.approver.name }}
                                    </div>
                                </td>
                                <td class="px-4 py-3 text-right">
                                    <div class="font-bold text-sm text-slate-900 dark:text-white">
                                        {{ formatCurrency(journal.total_debit) }}
                                    </div>
                                    <span class="text-[10px] text-emerald-600 dark:text-emerald-400">Debet = Kredit</span>
                                </td>
                                <td class="px-4 py-3 text-center">
                                    <span :class="['inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold', getStatusBadge(journal.status).class]">
                                        {{ getStatusBadge(journal.status).label }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-right space-x-1">
                                    <Link
                                        :href="`/finance/aje/${journal.id}`"
                                        class="inline-flex items-center p-1.5 text-slate-400 hover:text-emerald-600 dark:hover:text-emerald-400 rounded-lg hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors"
                                        title="Lihat Detail & Keputusan"
                                    >
                                        <Eye class="w-4 h-4" />
                                    </Link>
                                    <a
                                        :href="`/finance/aje/${journal.id}/pdf`"
                                        target="_blank"
                                        class="inline-flex items-center p-1.5 text-slate-400 hover:text-slate-700 dark:hover:text-slate-200 rounded-lg hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors"
                                        title="Unduh PDF Berita Acara AJE"
                                    >
                                        <Download class="w-4 h-4" />
                                    </a>
                                </td>
                            </tr>
                            <tr v-if="journals.data.length === 0">
                                <td colspan="7" class="px-4 py-8 text-center text-slate-400">
                                    Tidak ada data Jurnal Penyesuaian yang sesuai filter.
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div v-if="journals.links && journals.links.length > 3" class="px-4 py-3 border-t border-slate-200 dark:border-slate-800 flex items-center justify-between text-xs">
                    <div class="text-slate-500">
                        Menampilkan {{ journals.from || 0 }} - {{ journals.to || 0 }} dari {{ journals.total || 0 }} entri
                    </div>
                    <div class="flex items-center gap-1">
                        <Link
                            v-for="(link, i) in journals.links"
                            :key="i"
                            :href="link.url || '#'"
                            :class="[
                                'px-2.5 py-1 rounded border transition-colors',
                                link.active
                                    ? 'bg-emerald-600 text-white border-emerald-600 font-bold'
                                    : 'border-slate-300 dark:border-slate-700 text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800',
                                !link.url && 'opacity-40 cursor-not-allowed'
                            ]"
                            v-html="link.label"
                        />
                    </div>
                </div>
            </div>
        </div>
    </ModuleLayout>
</template>
