<script setup>
import { ref } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import ModuleLayout from '@/Layouts/ModuleLayout.vue';
import {
    Calendar,
    Plus,
    Search,
    Filter,
    RefreshCw,
    FileDown,
    Layers,
    PauseCircle,
    PlayCircle,
    CheckCircle2,
    XCircle,
    AlertCircle,
    Building2,
    Users,
    Kanban,
    List,
} from 'lucide-vue-next';

const props = defineProps({
    plans: {
        type: Object,
        required: true,
    },
    kanban: {
        type: Object,
        default: () => ({ draft: [], aktif: [], selesai: [], cancelled: [] }),
    },
    lines: {
        type: Array,
        default: () => [],
    },
    products: {
        type: Array,
        default: () => [],
    },
    filters: {
        type: Object,
        default: () => ({}),
    },
});

const activeView = ref('list'); // 'list' or 'kanban'
const search = ref(props.filters.search || '');
const selectedLine = ref(props.filters.line_id || '');
const selectedStatus = ref(props.filters.status || '');
const selectedProses = ref(props.filters.proses || '');

// Odoo Sync Modal State
const showSyncModal = ref(false);
const syncForm = ref({
    odoo_mo_id: '',
    mo_status: 'confirmed',
});

// Hold Modal State
const showHoldModal = ref(false);
const selectedPlanToHold = ref(null);
const holdReason = ref('');

const applyFilters = () => {
    router.get('/production/schedules', {
        search: search.value,
        line_id: selectedLine.value,
        status: selectedStatus.value,
        proses: selectedProses.value,
    }, { preserveState: true, preserveScroll: true });
};

const openHoldModal = (plan) => {
    selectedPlanToHold.value = plan;
    holdReason.value = '';
    showHoldModal.value = true;
};

const submitToggleHold = () => {
    if (!selectedPlanToHold.value) return;

    const newHoldStatus = !selectedPlanToHold.value.packing_hold;
    router.post(`/production/schedules/${selectedPlanToHold.value.id}/toggle-hold`, {
        is_hold: newHoldStatus,
        reason: holdReason.value,
    }, {
        onSuccess: () => {
            showHoldModal.value = false;
        },
    });
};

const submitOdooSync = () => {
    router.post('/production/schedules/sync-odoo', {
        odoo_mo_id: syncForm.value.odoo_mo_id,
        mo_status: syncForm.value.mo_status,
    }, {
        onSuccess: () => {
            showSyncModal.value = false;
            syncForm.value.odoo_mo_id = '';
        },
    });
};
</script>

<template>
    <Head title="Weekly Production Schedule (WPS) - PPIC" />

    <ModuleLayout module-slug="production">
        <div class="space-y-6">
            <!-- Header Bar -->
            <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h1 class="text-xl font-bold text-slate-900 dark:text-white sm:text-2xl">
                        Weekly Production Schedule (WPS)
                    </h1>
                    <p class="text-xs text-slate-500 dark:text-slate-400">
                        Perencanaan jadwal mingguan, kontrol status MO Odoo terkonfirmasi, dan penundaan Packing Hold.
                    </p>
                </div>

                <div class="flex flex-wrap items-center gap-2">
                    <button
                        type="button"
                        @click="showSyncModal = true"
                        class="inline-flex items-center gap-1.5 rounded-lg border border-slate-200 bg-white px-3 py-2 text-xs font-semibold text-slate-700 shadow-sm hover:bg-slate-50 dark:border-slate-800 dark:bg-slate-900 dark:text-slate-300"
                    >
                        <RefreshCw class="h-3.5 w-3.5 text-rose-600" /> Tarik MO Odoo
                    </button>

                    <div class="flex rounded-lg border border-slate-200 bg-white p-0.5 dark:border-slate-800 dark:bg-slate-900">
                        <button
                            type="button"
                            @click="activeView = 'list'"
                            :class="[
                                'inline-flex items-center gap-1 rounded-md px-2.5 py-1.5 text-xs font-medium transition-all',
                                activeView === 'list' ? 'bg-rose-600 text-white shadow-sm' : 'text-slate-600 hover:text-slate-900 dark:text-slate-400'
                            ]"
                        >
                            <List class="h-3.5 w-3.5" /> Tabel
                        </button>
                        <button
                            type="button"
                            @click="activeView = 'kanban'"
                            :class="[
                                'inline-flex items-center gap-1 rounded-md px-2.5 py-1.5 text-xs font-medium transition-all',
                                activeView === 'kanban' ? 'bg-rose-600 text-white shadow-sm' : 'text-slate-600 hover:text-slate-900 dark:text-slate-400'
                            ]"
                        >
                            <Kanban class="h-3.5 w-3.5" /> Kanban
                        </button>
                    </div>

                    <Link
                        href="/production/schedules/create"
                        class="inline-flex items-center gap-1.5 rounded-lg bg-rose-600 px-3.5 py-2 text-xs font-semibold text-white shadow-sm hover:bg-rose-700 focus:ring-2 focus:ring-rose-500"
                    >
                        <Plus class="h-4 w-4" /> Buat Rencana Batch
                    </Link>
                </div>
            </div>

            <!-- Filters Strip -->
            <div class="flex flex-col gap-3 rounded-xl border border-slate-200 bg-white p-4 shadow-sm md:flex-row md:items-center dark:border-slate-800 dark:bg-slate-900">
                <div class="relative flex-1">
                    <Search class="absolute left-3 top-2.5 h-4 w-4 text-slate-400" />
                    <input
                        v-model="search"
                        type="text"
                        placeholder="Cari no. WPS, batch number, atau nama produk..."
                        class="w-full rounded-lg border border-slate-200 bg-slate-50 pl-9 pr-3 py-1.5 text-xs text-slate-900 focus:border-rose-500 focus:ring-1 focus:ring-rose-500 dark:border-slate-800 dark:bg-slate-800/50 dark:text-white"
                        @keyup.enter="applyFilters"
                    />
                </div>

                <div class="flex flex-wrap items-center gap-2">
                    <select
                        v-model="selectedLine"
                        class="rounded-lg border border-slate-200 bg-slate-50 px-2.5 py-1.5 text-xs text-slate-900 focus:border-rose-500 dark:border-slate-800 dark:bg-slate-800/50 dark:text-white"
                        @change="applyFilters"
                    >
                        <option value="">Semua Lini</option>
                        <option v-for="line in lines" :key="line.id" :value="line.id">{{ line.name }}</option>
                    </select>

                    <select
                        v-model="selectedProses"
                        class="rounded-lg border border-slate-200 bg-slate-50 px-2.5 py-1.5 text-xs text-slate-900 focus:border-rose-500 dark:border-slate-800 dark:bg-slate-800/50 dark:text-white"
                        @change="applyFilters"
                    >
                        <option value="">Semua Tahap</option>
                        <option value="mixing">1. Mixing</option>
                        <option value="filling">2. Filling</option>
                        <option value="packing">3. Packing</option>
                    </select>

                    <select
                        v-model="selectedStatus"
                        class="rounded-lg border border-slate-200 bg-slate-50 px-2.5 py-1.5 text-xs text-slate-900 focus:border-rose-500 dark:border-slate-800 dark:bg-slate-800/50 dark:text-white"
                        @change="applyFilters"
                    >
                        <option value="">Semua Status</option>
                        <option value="draft">Draft</option>
                        <option value="aktif">Aktif</option>
                        <option value="selesai">Selesai</option>
                        <option value="cancelled">Cancelled</option>
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

            <!-- 1. List View Mode -->
            <div v-if="activeView === 'list'" class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm dark:border-slate-800 dark:bg-slate-900">
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm">
                        <thead class="border-b border-slate-200 bg-slate-50 text-xs font-semibold uppercase text-slate-600 dark:border-slate-800 dark:bg-slate-800/50 dark:text-slate-400">
                            <tr>
                                <th class="py-3 px-4">No. WPS / Batch</th>
                                <th class="py-3 px-4">Produk</th>
                                <th class="py-3 px-4">Proses</th>
                                <th class="py-3 px-4">Lini Pabrik</th>
                                <th class="py-3 px-4 text-right">Target Output</th>
                                <th class="py-3 px-4">Tanggal / Shift</th>
                                <th class="py-3 px-4 text-center">Packing Hold</th>
                                <th class="py-3 px-4 text-center">Status</th>
                                <th class="py-3 px-4 text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                            <tr v-for="plan in plans.data" :key="plan.id" class="hover:bg-slate-50 dark:hover:bg-slate-800/40">
                                <td class="py-3 px-4">
                                    <div class="font-mono font-semibold text-rose-600 dark:text-rose-400">{{ plan.plan_number }}</div>
                                    <div class="text-xs font-mono text-slate-500 font-bold">Batch: {{ plan.batch_number }}</div>
                                    <div v-if="plan.odoo_mo_id" class="text-[10px] text-emerald-600 font-medium">Odoo MO #{{ plan.odoo_mo_id }} (Confirmed)</div>
                                </td>
                                <td class="py-3 px-4">
                                    <div class="font-medium text-slate-900 dark:text-white">{{ plan.product?.name }}</div>
                                    <div class="text-xs text-slate-500">{{ plan.product?.sku }}</div>
                                </td>
                                <td class="py-3 px-4">
                                    <span class="inline-flex rounded-md bg-rose-50 px-2 py-0.5 text-xs font-bold text-rose-700 uppercase dark:bg-rose-950/40 dark:text-rose-300">
                                        {{ plan.proses }}
                                    </span>
                                </td>
                                <td class="py-3 px-4 text-slate-600 dark:text-slate-400">
                                    {{ plan.line?.name }}
                                </td>
                                <td class="py-3 px-4 text-right">
                                    <div class="font-semibold text-slate-900 dark:text-white">{{ plan.target_output.toLocaleString('id-ID') }} pcs</div>
                                    <div class="text-xs text-slate-500">{{ plan.mp_count }} MP ({{ plan.multiplier.toLocaleString('id-ID') }}/MP)</div>
                                </td>
                                <td class="py-3 px-4">
                                    <div class="text-slate-900 dark:text-white">{{ plan.tanggal }}</div>
                                    <div class="text-xs text-slate-500 uppercase">{{ plan.shift }}</div>
                                </td>
                                <td class="py-3 px-4 text-center">
                                    <button
                                        type="button"
                                        @click="openHoldModal(plan)"
                                        :class="[
                                            'inline-flex items-center gap-1 rounded-full px-2 py-0.5 text-xs font-semibold transition-all',
                                            plan.packing_hold
                                                ? 'bg-amber-100 text-amber-800 hover:bg-amber-200 dark:bg-amber-950/40 dark:text-amber-400'
                                                : 'bg-slate-100 text-slate-600 hover:bg-slate-200 dark:bg-slate-800 dark:text-slate-400'
                                        ]"
                                        title="Klik untuk toggle Packing Hold"
                                    >
                                        <PauseCircle v-if="plan.packing_hold" class="h-3 w-3" />
                                        <PlayCircle v-else class="h-3 w-3" />
                                        {{ plan.packing_hold ? 'HOLD' : 'NORMAL' }}
                                    </button>
                                </td>
                                <td class="py-3 px-4 text-center">
                                    <span :class="[
                                        'inline-flex rounded-full px-2 py-0.5 text-xs font-semibold',
                                        plan.status === 'selesai' ? 'bg-emerald-100 text-emerald-800 dark:bg-emerald-950/40 dark:text-emerald-400' :
                                        plan.status === 'aktif' ? 'bg-blue-100 text-blue-800 dark:bg-blue-950/40 dark:text-blue-400' :
                                        plan.status === 'cancelled' ? 'bg-slate-100 text-slate-600 dark:bg-slate-800 dark:text-slate-400' :
                                        'bg-amber-100 text-amber-800 dark:bg-amber-950/40 dark:text-amber-400'
                                    ]">
                                        {{ plan.status.toUpperCase() }}
                                    </span>
                                </td>
                                <td class="py-3 px-4 text-center">
                                    <div class="flex items-center justify-center gap-1.5">
                                        <a
                                            :href="`/production/schedules/${plan.id}/pdf`"
                                            target="_blank"
                                            class="rounded-lg p-1 text-slate-500 hover:bg-slate-100 hover:text-slate-700 dark:hover:bg-slate-800"
                                            title="Cetak SPK (PDF)"
                                        >
                                            <FileDown class="h-4 w-4" />
                                        </a>
                                        <Link
                                            :href="`/production/daily-reports/create?weekly_plan_id=${plan.id}`"
                                            class="rounded-lg bg-rose-50 px-2 py-1 text-xs font-semibold text-rose-700 hover:bg-rose-100 dark:bg-rose-950/40 dark:text-rose-300"
                                        >
                                            Buat LHP
                                        </Link>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div v-if="plans.links && plans.links.length > 3" class="flex items-center justify-between border-t border-slate-200 px-4 py-3 dark:border-slate-800">
                    <div class="text-xs text-slate-500">
                        Menampilkan {{ plans.from }} - {{ plans.to }} dari total {{ plans.total }} rencana WPS
                    </div>
                    <div class="flex items-center gap-1">
                        <Link
                            v-for="(link, i) in plans.links"
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

            <!-- 2. Kanban Board View Mode -->
            <div v-else class="grid grid-cols-1 gap-4 md:grid-cols-4">
                <div
                    v-for="(items, colStatus) in kanban"
                    :key="colStatus"
                    class="rounded-xl border border-slate-200 bg-slate-50 p-3 dark:border-slate-800 dark:bg-slate-900/60"
                >
                    <div class="flex items-center justify-between pb-2 border-b border-slate-200 dark:border-slate-800">
                        <span class="text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300">
                            {{ colStatus }} ({{ items.length }})
                        </span>
                    </div>

                    <div class="mt-3 space-y-2.5">
                        <div
                            v-for="plan in items"
                            :key="plan.id"
                            class="rounded-lg border border-slate-200 bg-white p-3 shadow-sm dark:border-slate-800 dark:bg-slate-900"
                        >
                            <div class="flex items-start justify-between">
                                <span class="text-xs font-bold text-rose-600 font-mono">{{ plan.plan_number }}</span>
                                <span class="rounded bg-rose-50 px-1.5 py-0.5 text-[10px] font-bold text-rose-700 uppercase">
                                    {{ plan.proses }}
                                </span>
                            </div>
                            <div class="mt-1 text-xs font-semibold text-slate-900 dark:text-white line-clamp-1">
                                {{ plan.product?.name }}
                            </div>
                            <div class="mt-1 text-[11px] text-slate-500">
                                Batch: <strong class="text-slate-700 dark:text-slate-300 font-mono">{{ plan.batch_number }}</strong>
                            </div>
                            <div class="mt-2 flex items-center justify-between text-[11px] text-slate-400">
                                <span>{{ plan.target_output.toLocaleString('id-ID') }} pcs</span>
                                <span>{{ plan.tanggal }}</span>
                            </div>
                        </div>

                        <div v-if="items.length === 0" class="py-6 text-center text-[11px] text-slate-400">
                            Kosong
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Odoo MO Sync Modal -->
        <div v-if="showSyncModal" class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/60 p-4 backdrop-blur-sm">
            <div class="w-full max-w-md rounded-2xl bg-white p-6 shadow-xl dark:bg-slate-900">
                <h3 class="text-base font-bold text-slate-900 dark:text-white">Tarik & Sinkronkan MO Odoo</h3>
                <p class="mt-1 text-xs text-slate-500">
                    Sistem hanya memvalidasi Manufacturing Order Odoo yang berstatus <strong>confirmed</strong>.
                </p>

                <div class="mt-4 space-y-3">
                    <div>
                        <label class="text-xs font-semibold text-slate-700 dark:text-slate-300">ID Manufacturing Order (MO) Odoo</label>
                        <input
                            v-model="syncForm.odoo_mo_id"
                            type="number"
                            placeholder="Contoh: 1011"
                            class="mt-1 w-full rounded-lg border border-slate-200 px-3 py-2 text-xs text-slate-900 dark:border-slate-700 dark:bg-slate-800 dark:text-white"
                        />
                    </div>
                    <div>
                        <label class="text-xs font-semibold text-slate-700 dark:text-slate-300">Status MO di Odoo</label>
                        <select
                            v-model="syncForm.mo_status"
                            class="mt-1 w-full rounded-lg border border-slate-200 px-3 py-2 text-xs text-slate-900 dark:border-slate-700 dark:bg-slate-800 dark:text-white"
                        >
                            <option value="confirmed">Confirmed (Siap Dijadwalkan)</option>
                            <option value="done">Done (Telah Selesai)</option>
                            <option value="cancelled">Cancelled (Batal)</option>
                        </select>
                    </div>
                </div>

                <div class="mt-6 flex justify-end gap-2">
                    <button
                        type="button"
                        @click="showSyncModal = false"
                        class="rounded-lg border border-slate-200 px-3 py-1.5 text-xs font-medium text-slate-700 hover:bg-slate-50 dark:border-slate-700 dark:text-slate-300"
                    >
                        Batal
                    </button>
                    <button
                        type="button"
                        @click="submitOdooSync"
                        class="rounded-lg bg-rose-600 px-3 py-1.5 text-xs font-semibold text-white hover:bg-rose-700"
                    >
                        Simpan Sinkronisasi
                    </button>
                </div>
            </div>
        </div>

        <!-- Packing Hold Modal -->
        <div v-if="showHoldModal" class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/60 p-4 backdrop-blur-sm">
            <div class="w-full max-w-md rounded-2xl bg-white p-6 shadow-xl dark:bg-slate-900">
                <h3 class="text-base font-bold text-slate-900 dark:text-white">
                    {{ selectedPlanToHold?.packing_hold ? 'Resume Packing' : 'Aktifkan Packing Hold' }}
                </h3>
                <p class="mt-1 text-xs text-slate-500">
                    Penundaan pengemasan sekunder tanpa memutus silsilah batch untuk Batch <strong>{{ selectedPlanToHold?.batch_number }}</strong>.
                </p>

                <div class="mt-4">
                    <label class="text-xs font-semibold text-slate-700 dark:text-slate-300">Alasan Hold / Resume</label>
                    <textarea
                        v-model="holdReason"
                        rows="3"
                        placeholder="Contoh: Menunggu pengiriman kemasan sekunder box dari vendor..."
                        class="mt-1 w-full rounded-lg border border-slate-200 p-2.5 text-xs text-slate-900 dark:border-slate-700 dark:bg-slate-800 dark:text-white"
                    ></textarea>
                </div>

                <div class="mt-6 flex justify-end gap-2">
                    <button
                        type="button"
                        @click="showHoldModal = false"
                        class="rounded-lg border border-slate-200 px-3 py-1.5 text-xs font-medium text-slate-700 hover:bg-slate-50 dark:border-slate-700 dark:text-slate-300"
                    >
                        Tutup
                    </button>
                    <button
                        type="button"
                        @click="submitToggleHold"
                        class="rounded-lg bg-amber-600 px-3 py-1.5 text-xs font-semibold text-white hover:bg-amber-700"
                    >
                        Konfirmasi Perubahan Hold
                    </button>
                </div>
            </div>
        </div>
    </ModuleLayout>
</template>
