<script setup>
import { ref } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import ModuleLayout from '@/Layouts/ModuleLayout.vue';
import {
    Calculator,
    Plus,
    Search,
    FileText,
    Eye,
    RotateCcw,
    CheckCircle2,
    Clock,
    XCircle,
    ArrowLeft,
    Layers,
    ChevronRight,
} from 'lucide-vue-next';

const props = defineProps({
    module: Object,
    formulas: Object,
    stats: Object,
    filters: Object,
});

const searchQuery = ref(props.filters?.q || '');
const statusFilter = ref(props.filters?.status || '');

function handleSearch() {
    router.get(
        '/rnd/formulas',
        { q: searchQuery.value, status: statusFilter.value },
        { preserveState: true, replace: true }
    );
}

function setStatus(st) {
    statusFilter.value = st;
    handleSearch();
}
</script>

<template>
    <ModuleLayout
        :title="module?.name || 'Formulasi RM'"
        :module-slug="'rnd'"
        :module-name="'R&D (NPD Management)'"
        :color-theme="'cyan'"
        :menus="module?.menus || []"
        :breadcrumbs="['Formulasi Raw Material (RM)']"
    >
        <Head title="Formulasi Raw Material (100% Presisi) - R&D" />

        <div class="space-y-6">
            <!-- Header Row -->
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
                <div>
                    <div class="flex items-center gap-3">
                        <Link
                            href="/rnd"
                            class="p-2 rounded-xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 text-slate-500 hover:text-slate-900 dark:hover:text-white transition"
                        >
                            <ArrowLeft class="w-4 h-4" />
                        </Link>
                        <div>
                            <div class="flex items-center gap-2">
                                <h1 class="text-xl font-bold text-slate-900 dark:text-white tracking-tight">
                                    Formulasi Raw Material (RM)
                                </h1>
                                <span class="text-xs font-mono px-2 py-0.5 rounded bg-cyan-100 dark:bg-cyan-950 text-cyan-800 dark:text-cyan-300 font-bold border border-cyan-200 dark:border-cyan-800">
                                    Wajib 100.00% Presisi
                                </span>
                            </div>
                            <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">
                                Perancangan resep racikan bahan baku, kalkulasi dosis sediaan, dan simulasi HPP kemasan.
                            </p>
                        </div>
                    </div>
                </div>

                <div class="flex items-center gap-2">
                    <Link
                        href="/rnd/formulas/create"
                        class="px-4 py-2 rounded-xl bg-cyan-600 hover:bg-cyan-500 text-white font-semibold text-xs flex items-center gap-2 shadow-md shadow-cyan-600/20 transition"
                    >
                        <Plus class="w-4 h-4" />
                        <span>+ Susun Formulasi Baru</span>
                    </Link>
                </div>
            </div>

            <!-- Stats Ribbon -->
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 sm:gap-4">
                <button
                    @click="setStatus('')"
                    :class="[
                        'p-3.5 rounded-xl border text-left transition',
                        statusFilter === ''
                            ? 'bg-cyan-50 dark:bg-cyan-950/40 border-cyan-500 text-cyan-900 dark:text-cyan-200 font-bold'
                            : 'bg-white dark:bg-slate-900 border-slate-200 dark:border-slate-800 text-slate-600 dark:text-slate-300',
                    ]"
                >
                    <div class="text-[10px] uppercase font-bold text-slate-400">Total Formula</div>
                    <div class="text-lg font-bold mt-0.5">{{ stats?.total || 0 }}</div>
                </button>

                <button
                    @click="setStatus('draft')"
                    :class="[
                        'p-3.5 rounded-xl border text-left transition',
                        statusFilter === 'draft'
                            ? 'bg-slate-200 dark:bg-slate-800 border-slate-500 font-bold'
                            : 'bg-white dark:bg-slate-900 border-slate-200 dark:border-slate-800 text-slate-600 dark:text-slate-300',
                    ]"
                >
                    <div class="text-[10px] uppercase font-bold text-slate-400">Draf / Revisi</div>
                    <div class="text-lg font-bold mt-0.5">{{ stats?.draft || 0 }}</div>
                </button>

                <button
                    @click="setStatus('pending_stage_1')"
                    :class="[
                        'p-3.5 rounded-xl border text-left transition',
                        statusFilter === 'pending_stage_1'
                            ? 'bg-amber-50 dark:bg-amber-950/40 border-amber-500 text-amber-900 dark:text-amber-200 font-bold'
                            : 'bg-white dark:bg-slate-900 border-slate-200 dark:border-slate-800 text-slate-600 dark:text-slate-300',
                    ]"
                >
                    <div class="text-[10px] uppercase font-bold text-amber-500">Menunggu Approval</div>
                    <div class="text-lg font-bold text-amber-600 dark:text-amber-400 mt-0.5">{{ stats?.pending || 0 }}</div>
                </button>

                <button
                    @click="setStatus('approved')"
                    :class="[
                        'p-3.5 rounded-xl border text-left transition',
                        statusFilter === 'approved'
                            ? 'bg-emerald-50 dark:bg-emerald-950/40 border-emerald-500 text-emerald-900 dark:text-emerald-200 font-bold'
                            : 'bg-white dark:bg-slate-900 border-slate-200 dark:border-slate-800 text-slate-600 dark:text-slate-300',
                    ]"
                >
                    <div class="text-[10px] uppercase font-bold text-emerald-500">Disahkan (Locked)</div>
                    <div class="text-lg font-bold text-emerald-600 dark:text-emerald-400 mt-0.5">{{ stats?.approved || 0 }}</div>
                </button>
            </div>

            <!-- Table Container -->
            <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 shadow-xs overflow-hidden">
                <!-- Search Toolbar -->
                <div class="p-4 border-b border-slate-200 dark:border-slate-800 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                    <form @submit.prevent="handleSearch" class="relative max-w-sm w-full">
                        <Search class="w-3.5 h-3.5 absolute left-3 top-1/2 -translate-y-1/2 text-slate-400" />
                        <input
                            v-model="searchQuery"
                            type="text"
                            placeholder="Cari kode formula atau nama produk..."
                            class="w-full pl-9 pr-4 py-2 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-xs text-slate-800 dark:text-slate-100 placeholder-slate-400 focus:ring-cyan-500 focus:border-cyan-500"
                        />
                    </form>

                    <div class="text-xs text-slate-400">
                        Total {{ formulas.total }} formula terdaftar
                    </div>
                </div>

                <!-- Table Content -->
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs text-slate-700 dark:text-slate-300">
                        <thead class="bg-slate-50 dark:bg-slate-800/60 uppercase font-mono text-[10px] text-slate-500 dark:text-slate-400 border-b border-slate-200 dark:border-slate-800">
                            <tr>
                                <th class="px-4 py-3 font-semibold">Kode Formula</th>
                                <th class="px-4 py-3 font-semibold">Nama Produk</th>
                                <th class="px-4 py-3 font-semibold">Versi</th>
                                <th class="px-4 py-3 font-semibold">Bentuk & Dosis</th>
                                <th class="px-4 py-3 font-semibold text-right">Rasio Total</th>
                                <th class="px-4 py-3 font-semibold text-right">HPP RM / Pack</th>
                                <th class="px-4 py-3 font-semibold">Status</th>
                                <th class="px-4 py-3 font-semibold text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                            <tr v-if="formulas.data.length === 0">
                                <td colspan="8" class="px-6 py-12 text-center text-slate-400 text-xs">
                                    <Calculator class="w-8 h-8 mx-auto text-slate-300 dark:text-slate-700 mb-2" />
                                    <p class="font-medium">Belum ada dokumen formulasi.</p>
                                    <Link
                                        href="/rnd/formulas/create"
                                        class="inline-block mt-2 text-cyan-600 dark:text-cyan-400 font-semibold hover:underline"
                                    >
                                        Susun Resep Formula Pertama
                                    </Link>
                                </td>
                            </tr>

                            <tr
                                v-for="f in formulas.data"
                                :key="f.id"
                                class="hover:bg-slate-50/80 dark:hover:bg-slate-800/40 transition"
                            >
                                <td class="px-4 py-3 font-mono font-bold text-slate-900 dark:text-white">
                                    <Link :href="`/rnd/formulas/${f.id}`" class="hover:text-cyan-600 dark:hover:text-cyan-400">
                                        {{ f.code }}
                                    </Link>
                                </td>
                                <td class="px-4 py-3">
                                    <div class="font-semibold text-slate-900 dark:text-white">{{ f.product?.name }}</div>
                                    <div class="text-[10px] text-slate-400 font-mono">{{ f.product?.sku }}</div>
                                </td>
                                <td class="px-4 py-3 font-mono">
                                    <span class="px-1.5 py-0.5 rounded bg-slate-100 dark:bg-slate-800 font-bold">
                                        V{{ f.version }}
                                    </span>
                                </td>
                                <td class="px-4 py-3">
                                    <div>{{ f.target_serving_size_g }} g / {{ f.serving_unit }}</div>
                                    <div class="text-[10px] text-slate-400">{{ f.servings_per_pack }} {{ f.serving_unit }}/pack</div>
                                </td>
                                <td class="px-4 py-3 text-right font-mono font-bold text-emerald-600 dark:text-emerald-400">
                                    {{ Number(f.total_percentage).toFixed(2) }}%
                                </td>
                                <td class="px-4 py-3 text-right font-mono font-bold text-slate-900 dark:text-white">
                                    Rp {{ Number(f.total_cost_per_pack).toLocaleString('id-ID') }}
                                </td>
                                <td class="px-4 py-3">
                                    <span
                                        :class="[
                                            'px-2 py-0.5 rounded text-[10px] font-bold uppercase tracking-wider border',
                                            f.status === 'approved'
                                                ? 'bg-emerald-50 dark:bg-emerald-950/60 text-emerald-700 dark:text-emerald-300 border-emerald-200 dark:border-emerald-800'
                                                : f.status.startsWith('pending')
                                                ? 'bg-amber-50 dark:bg-amber-950/60 text-amber-700 dark:text-amber-300 border-amber-200 dark:border-amber-800'
                                                : f.status === 'rejected'
                                                ? 'bg-rose-50 dark:bg-rose-950/60 text-rose-700 dark:text-rose-300 border-rose-200 dark:border-rose-800'
                                                : 'bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-400 border-slate-200 dark:border-slate-700',
                                        ]"
                                    >
                                        {{ f.status }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-right">
                                    <div class="flex items-center justify-end gap-1.5">
                                        <!-- PDF Export -->
                                        <a
                                            :href="`/rnd/formulas/${f.id}/export/pdf`"
                                            target="_blank"
                                            class="p-1 rounded hover:bg-slate-100 dark:hover:bg-slate-800 text-slate-400 hover:text-slate-700 dark:hover:text-slate-200"
                                            title="Cetak Lembar Formulasi PDF"
                                        >
                                            <FileText class="w-4 h-4" />
                                        </a>

                                        <!-- View / Edit -->
                                        <Link
                                            :href="`/rnd/formulas/${f.id}`"
                                            class="p-1 rounded hover:bg-slate-100 dark:hover:bg-slate-800 text-slate-400 hover:text-cyan-600 dark:hover:text-cyan-400"
                                            title="Lihat Detail Resep"
                                        >
                                            <Eye class="w-4 h-4" />
                                        </Link>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div v-if="formulas.links && formulas.links.length > 3" class="px-4 py-3 border-t border-slate-200 dark:border-slate-800 flex items-center justify-between">
                    <div class="text-xs text-slate-500">
                        Halaman {{ formulas.current_page }} dari {{ formulas.last_page }}
                    </div>
                    <div class="flex items-center gap-1">
                        <Link
                            v-for="(link, idx) in formulas.links"
                            :key="idx"
                            :href="link.url || '#'"
                            :class="[
                                'px-2.5 py-1 text-xs rounded-lg transition',
                                link.active
                                    ? 'bg-cyan-600 text-white font-bold'
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
