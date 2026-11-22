<script setup>
import { ref, computed } from 'vue';
import { Head, Link } from '@inertiajs/vue3';
import ModuleLayout from '@/Layouts/ModuleLayout.vue';
import {
    Layers,
    Search,
    Filter,
    Download,
    Plus,
    RefreshCw,
    CheckCircle2,
    Clock,
    AlertCircle,
    ArrowUpRight,
    ChevronRight,
    FileSpreadsheet,
    FileText,
    Calendar,
    Eye,
    Edit3,
    MoreVertical,
    Sparkles,
} from 'lucide-vue-next';

const props = defineProps({
    module: {
        type: Object,
        default: () => ({ name: 'Modul ERP', slug: '', menus: [] }),
    },
    feature: {
        type: Object,
        default: () => ({ name: 'Fitur Operasional', route: '', category: 'Operasional', description: '' }),
    },
    category: {
        type: String,
        default: 'Operasional',
    },
    breadcrumbs: {
        type: Array,
        default: () => [],
    },
});

const searchQuery = ref('');
const selectedStatus = ref('all');
const isRefreshing = ref(false);

function refreshData() {
    isRefreshing.value = true;
    setTimeout(() => {
        isRefreshing.value = false;
    }, 600);
}

// Generate realistic mock records suited to the feature context
const records = computed(() => {
    const list = [];
    const featureName = props.feature?.name || 'Dokumen';
    const slug = props.module?.slug || 'erp';

    const statuses = ['Approved', 'In Progress', 'Pending Review', 'Draft'];
    const users = ['Budi Pratama (SPV)', 'Siti Rahmawati (Lead)', 'Agus Setiawan (FAT)', 'Dewi Lestari (QA)', 'Rian Firmansyah'];

    for (let i = 1; i <= 8; i++) {
        const idNumber = String(i).padStart(4, '0');
        const codePrefix = slug.substring(0, 3).toUpperCase();
        list.push({
            id: i,
            code: `${codePrefix}-${new Date().getFullYear()}-${idNumber}`,
            title: `${featureName} Batch #${idNumber}`,
            creator: users[i % users.length],
            date: new Date(Date.now() - i * 86400000 * 2).toLocaleDateString('id-ID', { day: '2-digit', month: 'short', year: 'numeric' }),
            status: statuses[i % statuses.length],
            value: `Rp ${(i * 1250000 + 4500000).toLocaleString('id-ID')}`,
        });
    }

    if (searchQuery.value.trim()) {
        const q = searchQuery.value.toLowerCase();
        return list.filter(r => r.code.toLowerCase().includes(q) || r.title.toLowerCase().includes(q) || r.creator.toLowerCase().includes(q));
    }

    if (selectedStatus.value !== 'all') {
        return list.filter(r => r.status.toLowerCase() === selectedStatus.value.toLowerCase());
    }

    return list;
});

function getStatusBadge(status) {
    switch (status) {
        case 'Approved':
            return 'bg-emerald-50 dark:bg-emerald-950/60 text-emerald-700 dark:text-emerald-300 border-emerald-200 dark:border-emerald-800';
        case 'In Progress':
            return 'bg-blue-50 dark:bg-blue-950/60 text-blue-700 dark:text-blue-300 border-blue-200 dark:border-blue-800';
        case 'Pending Review':
            return 'bg-amber-50 dark:bg-amber-950/60 text-amber-700 dark:text-amber-300 border-amber-200 dark:border-amber-800';
        default:
            return 'bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 border-slate-200 dark:border-slate-700';
    }
}
</script>

<template>
    <Head :title="`${feature.name} - ${module.name}`" />

    <ModuleLayout
        :module-slug="module.slug"
        :module-name="module.name"
        :breadcrumbs="breadcrumbs"
        :menus="module.menus"
    >
        <div class="space-y-6">
            <!-- Feature Header & Summary Card -->
            <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 p-6 shadow-xs">
                <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
                    <div class="flex items-start gap-4">
                        <div class="w-12 h-12 rounded-2xl bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-200 flex items-center justify-center shrink-0 border border-slate-200 dark:border-slate-700 shadow-xs">
                            <Layers class="w-6 h-6 text-emerald-600 dark:text-emerald-400" />
                        </div>
                        <div>
                            <div class="flex items-center gap-2">
                                <span class="px-2.5 py-0.5 rounded-full text-[11px] font-semibold bg-emerald-50 dark:bg-emerald-950/50 text-emerald-700 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800">
                                    {{ category }}
                                </span>
                                <span class="text-xs text-slate-400 font-mono">{{ feature.route }}</span>
                            </div>
                            <h1 class="text-xl font-bold text-slate-900 dark:text-white mt-1">
                                {{ feature.name }}
                            </h1>
                            <p class="text-xs text-slate-500 dark:text-slate-400 mt-1 max-w-2xl">
                                {{ feature.description || 'Pusat operasional dan tata kelola data terpadu untuk kepatuhan standar industri.' }}
                            </p>
                        </div>
                    </div>

                    <!-- Action Buttons -->
                    <div class="flex flex-wrap items-center gap-2">
                        <button
                            type="button"
                            @click="refreshData"
                            class="p-2 rounded-xl text-slate-500 hover:text-slate-700 dark:hover:text-slate-200 hover:bg-slate-100 dark:hover:bg-slate-800 border border-slate-200 dark:border-slate-700 transition"
                            title="Segarkan Data"
                        >
                            <RefreshCw :class="['w-4 h-4', isRefreshing ? 'animate-spin text-emerald-600' : '']" />
                        </button>
                        <button
                            type="button"
                            class="inline-flex items-center gap-1.5 px-3 py-2 rounded-xl text-xs font-semibold text-slate-700 dark:text-slate-200 bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 border border-slate-200 dark:border-slate-700 transition"
                        >
                            <Download class="w-3.5 h-3.5" />
                            <span>Ekspor Data</span>
                        </button>
                        <button
                            type="button"
                            class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl text-xs font-semibold text-white bg-emerald-600 hover:bg-emerald-700 shadow-xs transition"
                        >
                            <Plus class="w-3.5 h-3.5" />
                            <span>Entri / Buat Baru</span>
                        </button>
                    </div>
                </div>

                <!-- KPI Metric Mini Strip -->
                <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 mt-6 pt-5 border-t border-slate-100 dark:border-slate-800">
                    <div class="p-3 rounded-xl bg-slate-50 dark:bg-slate-800/40 border border-slate-100 dark:border-slate-800">
                        <div class="text-[11px] font-medium text-slate-400 uppercase tracking-wider">Total Entri</div>
                        <div class="text-lg font-bold text-slate-900 dark:text-white mt-0.5">142</div>
                    </div>
                    <div class="p-3 rounded-xl bg-emerald-50/50 dark:bg-emerald-950/20 border border-emerald-100 dark:border-emerald-900/40">
                        <div class="text-[11px] font-medium text-emerald-700 dark:text-emerald-400 uppercase tracking-wider">Approved / Selesai</div>
                        <div class="text-lg font-bold text-emerald-700 dark:text-emerald-300 mt-0.5">118</div>
                    </div>
                    <div class="p-3 rounded-xl bg-amber-50/50 dark:bg-amber-950/20 border border-amber-100 dark:border-amber-900/40">
                        <div class="text-[11px] font-medium text-amber-700 dark:text-amber-400 uppercase tracking-wider">Pending Review</div>
                        <div class="text-lg font-bold text-amber-700 dark:text-amber-300 mt-0.5">18</div>
                    </div>
                    <div class="p-3 rounded-xl bg-blue-50/50 dark:bg-blue-950/20 border border-blue-100 dark:border-blue-900/40">
                        <div class="text-[11px] font-medium text-blue-700 dark:text-blue-400 uppercase tracking-wider">Tingkat Kepatuhan</div>
                        <div class="text-lg font-bold text-blue-700 dark:text-blue-300 mt-0.5">98.4%</div>
                    </div>
                </div>
            </div>

            <!-- Workspace Table & Filter Section -->
            <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 overflow-hidden shadow-xs">
                <!-- Toolbar Filter -->
                <div class="p-4 border-b border-slate-100 dark:border-slate-800 flex flex-col sm:flex-row items-stretch sm:items-center justify-between gap-3 bg-slate-50/40 dark:bg-slate-800/30">
                    <div class="relative flex-1 max-w-sm">
                        <Search class="w-4 h-4 absolute left-3 top-1/2 -translate-y-1/2 text-slate-400" />
                        <input
                            v-model="searchQuery"
                            type="text"
                            placeholder="Cari nomor kode, nama entri, atau PIC..."
                            class="w-full pl-9 pr-3 py-1.5 text-xs rounded-xl bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-slate-800 dark:text-slate-100 placeholder-slate-400 focus:outline-none focus:ring-1 focus:ring-emerald-500"
                        />
                    </div>

                    <div class="flex items-center gap-2">
                        <div class="flex items-center gap-1.5 text-xs text-slate-500">
                            <Filter class="w-3.5 h-3.5 text-slate-400" />
                            <span>Status:</span>
                        </div>
                        <select
                            v-model="selectedStatus"
                            class="text-xs px-2.5 py-1.5 rounded-xl bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-slate-700 dark:text-slate-200 focus:outline-none focus:ring-1 focus:ring-emerald-500"
                        >
                            <option value="all">Semua Status</option>
                            <option value="Approved">Approved</option>
                            <option value="In Progress">In Progress</option>
                            <option value="Pending Review">Pending Review</option>
                            <option value="Draft">Draft</option>
                        </select>
                    </div>
                </div>

                <!-- Table Content -->
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs">
                        <thead class="bg-slate-50 dark:bg-slate-800/60 text-slate-400 uppercase tracking-wider text-[10px] border-b border-slate-100 dark:border-slate-800">
                            <tr>
                                <th class="py-3 px-4 font-semibold">Kode Entri</th>
                                <th class="py-3 px-4 font-semibold">Rincian Dokumen / Aktivitas</th>
                                <th class="py-3 px-4 font-semibold">PIC / Pembuat</th>
                                <th class="py-3 px-4 font-semibold">Tanggal</th>
                                <th class="py-3 px-4 font-semibold">Nominal / Nilai</th>
                                <th class="py-3 px-4 font-semibold">Status</th>
                                <th class="py-3 px-4 font-semibold text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 dark:divide-slate-800/80">
                            <tr
                                v-for="rec in records"
                                :key="rec.id"
                                class="hover:bg-slate-50/70 dark:hover:bg-slate-800/40 transition"
                            >
                                <td class="py-3 px-4 font-mono font-semibold text-slate-900 dark:text-slate-100 whitespace-nowrap">
                                    {{ rec.code }}
                                </td>
                                <td class="py-3 px-4 font-medium text-slate-800 dark:text-slate-200">
                                    {{ rec.title }}
                                </td>
                                <td class="py-3 px-4 text-slate-600 dark:text-slate-300 whitespace-nowrap">
                                    {{ rec.creator }}
                                </td>
                                <td class="py-3 px-4 text-slate-500 dark:text-slate-400 whitespace-nowrap">
                                    {{ rec.date }}
                                </td>
                                <td class="py-3 px-4 font-mono text-slate-700 dark:text-slate-300 whitespace-nowrap">
                                    {{ rec.value }}
                                </td>
                                <td class="py-3 px-4 whitespace-nowrap">
                                    <span :class="['px-2.5 py-0.5 rounded-full text-[10px] font-semibold border', getStatusBadge(rec.status)]">
                                        {{ rec.status }}
                                    </span>
                                </td>
                                <td class="py-3 px-4 text-right whitespace-nowrap">
                                    <div class="inline-flex items-center gap-1">
                                        <button
                                            type="button"
                                            class="p-1 rounded-lg text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 hover:bg-slate-100 dark:hover:bg-slate-800"
                                            title="Lihat Detail"
                                        >
                                            <Eye class="w-3.5 h-3.5" />
                                        </button>
                                        <button
                                            type="button"
                                            class="p-1 rounded-lg text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 hover:bg-slate-100 dark:hover:bg-slate-800"
                                            title="Edit Data"
                                        >
                                            <Edit3 class="w-3.5 h-3.5" />
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Pagination Footer -->
                <div class="p-4 border-t border-slate-100 dark:border-slate-800 flex items-center justify-between text-xs text-slate-500 bg-slate-50/20 dark:bg-slate-800/20">
                    <div>
                        Menampilkan <span class="font-semibold text-slate-700 dark:text-slate-300">{{ records.length }}</span> entri
                    </div>
                    <div class="flex items-center gap-1">
                        <button
                            type="button"
                            disabled
                            class="px-2.5 py-1 rounded-lg border border-slate-200 dark:border-slate-700 text-slate-400 disabled:opacity-50 text-xs"
                        >
                            Sebelumnya
                        </button>
                        <button
                            type="button"
                            class="px-2.5 py-1 rounded-lg bg-emerald-600 text-white font-semibold text-xs"
                        >
                            1
                        </button>
                        <button
                            type="button"
                            disabled
                            class="px-2.5 py-1 rounded-lg border border-slate-200 dark:border-slate-700 text-slate-400 disabled:opacity-50 text-xs"
                        >
                            Selanjutnya
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </ModuleLayout>
</template>
