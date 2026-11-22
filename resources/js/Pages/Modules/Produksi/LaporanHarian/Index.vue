<script setup>
import { ref } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import ModuleLayout from '@/Layouts/ModuleLayout.vue';
import {
    Timer,
    Plus,
    Search,
    Filter,
    FileDown,
    Play,
    Pause,
    Clock,
    Lock,
    Unlock,
    CheckCircle2,
    ArrowUpRight,
} from 'lucide-vue-next';

const props = defineProps({
    reports: {
        type: Object,
        required: true,
    },
    lines: {
        type: Array,
        default: () => [],
    },
    filters: {
        type: Object,
        default: () => ({}),
    },
});

const search = ref(props.filters.search || '');
const selectedLine = ref(props.filters.line_id || '');
const selectedStatus = ref(props.filters.status || '');
const selectedProses = ref(props.filters.proses || '');
const selectedShift = ref(props.filters.shift || '');

const applyFilters = () => {
    router.get('/production/daily-reports', {
        search: search.value,
        line_id: selectedLine.value,
        status: selectedStatus.value,
        proses: selectedProses.value,
        shift: selectedShift.value,
    }, { preserveState: true, preserveScroll: true });
};
</script>

<template>
    <Head title="Laporan Harian Produksi & Timer - Shop Floor" />

    <ModuleLayout module-slug="production">
        <div class="space-y-6">
            <!-- Header Bar -->
            <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h1 class="text-xl font-bold text-slate-900 dark:text-white sm:text-2xl">
                        Laporan Harian Produksi (LHP) & Timer
                    </h1>
                    <p class="text-xs text-slate-500 dark:text-slate-400">
                        Eksekusi lantai pabrik, pencatatan waktu operasi kotor/bersih, OEE, dan verifikasi supervisor.
                    </p>
                </div>

                <Link
                    href="/production/daily-reports/create"
                    class="inline-flex items-center gap-1.5 rounded-lg bg-rose-600 px-3.5 py-2 text-xs font-semibold text-white shadow-sm hover:bg-rose-700 focus:ring-2 focus:ring-rose-500"
                >
                    <Plus class="h-4 w-4" /> Buka LHP Baru
                </Link>
            </div>

            <!-- Filter Strip -->
            <div class="flex flex-col gap-3 rounded-xl border border-slate-200 bg-white p-4 shadow-sm md:flex-row md:items-center dark:border-slate-800 dark:bg-slate-900">
                <div class="relative flex-1">
                    <Search class="absolute left-3 top-2.5 h-4 w-4 text-slate-400" />
                    <input
                        v-model="search"
                        type="text"
                        placeholder="Cari no. LHP, batch, produk..."
                        class="w-full rounded-lg border border-slate-200 bg-slate-50 pl-9 pr-3 py-1.5 text-xs text-slate-900 focus:border-rose-500 dark:border-slate-800 dark:bg-slate-800/50 dark:text-white"
                        @keyup.enter="applyFilters"
                    />
                </div>

                <div class="flex flex-wrap items-center gap-2">
                    <select
                        v-model="selectedLine"
                        class="rounded-lg border border-slate-200 bg-slate-50 px-2.5 py-1.5 text-xs text-slate-900 dark:border-slate-800 dark:bg-slate-800/50 dark:text-white"
                        @change="applyFilters"
                    >
                        <option value="">Semua Lini</option>
                        <option v-for="l in lines" :key="l.id" :value="l.id">{{ l.name }}</option>
                    </select>

                    <select
                        v-model="selectedProses"
                        class="rounded-lg border border-slate-200 bg-slate-50 px-2.5 py-1.5 text-xs text-slate-900 dark:border-slate-800 dark:bg-slate-800/50 dark:text-white"
                        @change="applyFilters"
                    >
                        <option value="">Semua Tahap</option>
                        <option value="mixing">1. Mixing</option>
                        <option value="filling">2. Filling</option>
                        <option value="packing">3. Packing</option>
                    </select>

                    <select
                        v-model="selectedStatus"
                        class="rounded-lg border border-slate-200 bg-slate-50 px-2.5 py-1.5 text-xs text-slate-900 dark:border-slate-800 dark:bg-slate-800/50 dark:text-white"
                        @change="applyFilters"
                    >
                        <option value="">Semua Status</option>
                        <option value="draft">Draft</option>
                        <option value="submitted">Submitted</option>
                        <option value="locked">Locked</option>
                    </select>

                    <button
                        type="button"
                        @click="applyFilters"
                        class="rounded-lg bg-slate-100 px-3 py-1.5 text-xs font-medium text-slate-700 hover:bg-slate-200 dark:bg-slate-800 dark:text-slate-300"
                    >
                        Filter
                    </button>
                </div>
            </div>

            <!-- Table -->
            <div class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm dark:border-slate-800 dark:bg-slate-900">
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm">
                        <thead class="border-b border-slate-200 bg-slate-50 text-xs font-semibold uppercase text-slate-600 dark:border-slate-800 dark:bg-slate-800/50 dark:text-slate-400">
                            <tr>
                                <th class="py-3 px-4">No. LHP / Batch</th>
                                <th class="py-3 px-4">Produk</th>
                                <th class="py-3 px-4">Proses</th>
                                <th class="py-3 px-4">Lini / Mesin</th>
                                <th class="py-3 px-4 text-right">Output / Reject</th>
                                <th class="py-3 px-4 text-right">OEE / Yield</th>
                                <th class="py-3 px-4 text-center">Timer Status</th>
                                <th class="py-3 px-4 text-center">Status Lock</th>
                                <th class="py-3 px-4 text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                            <tr v-for="r in reports.data" :key="r.id" class="hover:bg-slate-50 dark:hover:bg-slate-800/40">
                                <td class="py-3 px-4">
                                    <div class="font-mono font-bold text-rose-600 dark:text-rose-400">{{ r.report_number }}</div>
                                    <div class="text-xs font-mono text-slate-600 dark:text-slate-300 font-bold">Batch: {{ r.batch_number }}</div>
                                    <div class="text-[10px] text-slate-400">{{ r.tanggal }} | {{ r.shift?.toUpperCase() }}</div>
                                </td>
                                <td class="py-3 px-4">
                                    <div class="font-medium text-slate-900 dark:text-white">{{ r.product?.name }}</div>
                                    <div class="text-xs text-slate-500">{{ r.product?.sku }}</div>
                                </td>
                                <td class="py-3 px-4">
                                    <span class="inline-flex rounded-md bg-rose-50 px-2 py-0.5 text-xs font-bold text-rose-700 uppercase dark:bg-rose-950/40 dark:text-rose-300">
                                        {{ r.proses }}
                                    </span>
                                </td>
                                <td class="py-3 px-4 text-slate-600 dark:text-slate-400">
                                    <div>{{ r.line?.name }}</div>
                                    <div class="text-xs text-slate-400 font-mono">CT: {{ r.ct }}s</div>
                                </td>
                                <td class="py-3 px-4 text-right">
                                    <div class="font-semibold text-slate-900 dark:text-white">{{ r.output_fisik.toLocaleString('id-ID') }} pcs</div>
                                    <div class="text-xs text-rose-600 font-medium">Reject: {{ r.total_reject.toLocaleString('id-ID') }} pcs</div>
                                </td>
                                <td class="py-3 px-4 text-right">
                                    <div class="font-bold text-slate-900 dark:text-white" :class="r.oee_persen >= 85 ? 'text-emerald-600' : 'text-amber-600'">
                                        OEE: {{ r.oee_persen }}%
                                    </div>
                                    <div class="text-xs text-slate-500">Yield: {{ r.yield_persen }}%</div>
                                </td>
                                <td class="py-3 px-4 text-center">
                                    <span :class="[
                                        'inline-flex items-center gap-1 rounded-full px-2 py-0.5 text-xs font-semibold',
                                        r.timer_status === 'start' ? 'bg-emerald-100 text-emerald-800 animate-pulse' :
                                        r.timer_status === 'pause' ? 'bg-amber-100 text-amber-800' :
                                        r.timer_status === 'end' ? 'bg-blue-100 text-blue-800' :
                                        'bg-slate-100 text-slate-700'
                                    ]">
                                        <Play v-if="r.timer_status === 'start'" class="h-3 w-3 fill-current" />
                                        <Pause v-else-if="r.timer_status === 'pause'" class="h-3 w-3" />
                                        <Clock v-else class="h-3 w-3" />
                                        {{ r.timer_status?.toUpperCase() }}
                                    </span>
                                </td>
                                <td class="py-3 px-4 text-center">
                                    <span :class="[
                                        'inline-flex items-center gap-1 rounded-full px-2.5 py-0.5 text-xs font-bold',
                                        r.status === 'locked' ? 'bg-emerald-100 text-emerald-800 dark:bg-emerald-950/40 dark:text-emerald-400' :
                                        r.status === 'submitted' ? 'bg-blue-100 text-blue-800 dark:bg-blue-950/40 dark:text-blue-400' :
                                        'bg-slate-100 text-slate-600 dark:bg-slate-800 dark:text-slate-400'
                                    ]">
                                        <Lock v-if="r.status === 'locked'" class="h-3 w-3" />
                                        <Unlock v-else class="h-3 w-3" />
                                        {{ r.status?.toUpperCase() }}
                                    </span>
                                </td>
                                <td class="py-3 px-4 text-center">
                                    <div class="flex items-center justify-center gap-1.5">
                                        <a
                                            :href="`/production/daily-reports/${r.id}/pdf`"
                                            target="_blank"
                                            class="rounded-lg p-1 text-slate-500 hover:bg-slate-100 hover:text-slate-700 dark:hover:bg-slate-800"
                                            title="Cetak LHP (PDF)"
                                        >
                                            <FileDown class="h-4 w-4" />
                                        </a>
                                        <Link
                                            :href="`/production/daily-reports/${r.id}`"
                                            class="inline-flex items-center gap-1 rounded-lg bg-rose-50 px-2.5 py-1 text-xs font-semibold text-rose-700 hover:bg-rose-100 dark:bg-rose-950/40 dark:text-rose-300"
                                        >
                                            Buka Timer <ArrowUpRight class="h-3 w-3" />
                                        </Link>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div v-if="reports.links && reports.links.length > 3" class="flex items-center justify-between border-t border-slate-200 px-4 py-3 dark:border-slate-800">
                    <div class="text-xs text-slate-500">
                        Menampilkan {{ reports.from }} - {{ reports.to }} dari {{ reports.total }} laporan
                    </div>
                    <div class="flex items-center gap-1">
                        <Link
                            v-for="(link, i) in reports.links"
                            :key="i"
                            :href="link.url || '#'"
                            :class="[
                                'rounded-md px-2.5 py-1 text-xs font-medium',
                                link.active ? 'bg-rose-600 text-white' : 'text-slate-600 hover:bg-slate-100 dark:text-slate-400'
                            ]"
                            v-html="link.label"
                        />
                    </div>
                </div>
            </div>
        </div>
    </ModuleLayout>
</template>
