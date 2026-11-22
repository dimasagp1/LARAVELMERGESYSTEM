<script setup>
import { ref } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import ModuleLayout from '@/Layouts/ModuleLayout.vue';
import {
    Warehouse,
    Search,
    Filter,
    Boxes,
    Clock,
    AlertTriangle,
    CheckCircle2,
    ArrowRightLeft,
    Building2,
    Calendar,
    DollarSign
} from 'lucide-vue-next';

const props = defineProps({
    stocks: Object,
    recentMovements: Array,
    plants: Array,
    locations: Array,
    filters: Object,
});

const search = ref(props.filters?.search || '');
const plantId = ref(props.filters?.plant_id || '');
const locationId = ref(props.filters?.location_id || '');
const status = ref(props.filters?.status || '');

const handleFilter = () => {
    router.get('/supply-chain/stock', {
        search: search.value || undefined,
        plant_id: plantId.value || undefined,
        location_id: locationId.value || undefined,
        status: status.value || undefined,
    }, { preserveState: true, replace: true });
};

const formatCurrency = (val) => new Intl.NumberFormat('id-ID', {
    style: 'currency',
    currency: 'IDR',
    maximumFractionDigits: 0,
}).format(val || 0);

const formatNumber = (val) => new Intl.NumberFormat('id-ID').format(val || 0);
</script>

<template>
    <Head title="Stok Multi-Gudang & Mutasi - LogiFlow SCM" />

    <ModuleLayout current-module="supply-chain">
        <div class="space-y-6">
            <!-- Header -->
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 bg-white dark:bg-slate-800 p-6 rounded-2xl shadow-sm border border-slate-200 dark:border-slate-700">
                <div class="flex items-center gap-3">
                    <div class="p-2.5 bg-indigo-500/10 text-indigo-600 dark:text-indigo-400 rounded-xl">
                        <Warehouse class="w-7 h-7" />
                    </div>
                    <div>
                        <h1 class="text-2xl font-bold text-slate-900 dark:text-white tracking-tight">
                            Matriks Stok Multi-Gudang & Pelacakan Batch
                        </h1>
                        <p class="text-sm text-slate-500 dark:text-slate-400 mt-0.5">
                            Saldo fisik bahan baku, kemas, dan produk jadi di seluruh lokasi pabrik lengkap dengan rotasi FEFO dan ledger audit mutasi.
                        </p>
                    </div>
                </div>

                <div class="flex items-center gap-2">
                    <Link
                        href="/supply-chain/goods-receipt/create"
                        class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-semibold rounded-xl"
                    >
                        + Inbound Baru
                    </Link>
                    <Link
                        href="/supply-chain/material-usage/create"
                        class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-xs font-semibold rounded-xl"
                    >
                        + BSTB Produksi
                    </Link>
                </div>
            </div>

            <!-- Filter Controls -->
            <div class="bg-white dark:bg-slate-800 p-4 rounded-xl border border-slate-200 dark:border-slate-700 shadow-sm flex flex-col sm:flex-row items-center gap-3 text-xs">
                <div class="relative flex-1 w-full">
                    <Search class="w-4 h-4 text-slate-400 absolute left-3 top-1/2 -translate-y-1/2" />
                    <input
                        type="text"
                        v-model="search"
                        @keyup.enter="handleFilter"
                        placeholder="Cari kode/nama produk atau nomor batch..."
                        class="w-full pl-9 pr-3 py-2 rounded-lg border border-slate-200 dark:border-slate-700 dark:bg-slate-900 dark:text-white"
                    />
                </div>

                <select
                    v-model="plantId"
                    @change="handleFilter"
                    class="w-full sm:w-44 rounded-lg border border-slate-200 dark:border-slate-700 dark:bg-slate-900 dark:text-white py-2"
                >
                    <option value="">Semua Pabrik</option>
                    <option v-for="p in plants" :key="p.id" :value="p.id">{{ p.name }}</option>
                </select>

                <select
                    v-model="locationId"
                    @change="handleFilter"
                    class="w-full sm:w-48 rounded-lg border border-slate-200 dark:border-slate-700 dark:bg-slate-900 dark:text-white py-2"
                >
                    <option value="">Semua Lokasi Gudang</option>
                    <option v-for="loc in locations" :key="loc.id" :value="loc.id">
                        {{ loc.code }} - {{ loc.name }}
                    </option>
                </select>

                <select
                    v-model="status"
                    @change="handleFilter"
                    class="w-full sm:w-40 rounded-lg border border-slate-200 dark:border-slate-700 dark:bg-slate-900 dark:text-white py-2"
                >
                    <option value="">Semua Status</option>
                    <option value="available">Tersedia (Passed)</option>
                    <option value="quarantine">Karantina QC</option>
                    <option value="reserved">Dialokasikan (SPK)</option>
                    <option value="rejected">Ditolak (Reject)</option>
                </select>

                <button
                    @click="handleFilter"
                    class="px-4 py-2 bg-slate-100 hover:bg-slate-200 dark:bg-slate-700 dark:hover:bg-slate-600 font-semibold rounded-lg"
                >
                    Terapkan
                </button>
            </div>

            <!-- Stocks Grid Table -->
            <div class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-200 dark:border-slate-700 shadow-sm overflow-hidden">
                <div class="p-4 border-b border-slate-100 dark:border-slate-700 flex items-center justify-between">
                    <h2 class="text-base font-bold text-slate-900 dark:text-white">Saldo Fisik Material Per Lokasi & Lot</h2>
                    <span class="text-xs text-slate-500">{{ stocks.total || 0 }} Saldo Batch Tercatat</span>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs">
                        <thead>
                            <tr class="bg-slate-50 dark:bg-slate-700/50 border-b border-slate-200 dark:border-slate-700 text-slate-500 font-semibold uppercase">
                                <th class="p-3.5">Material & Kode</th>
                                <th class="p-3.5">Pabrik & Lokasi Gudang</th>
                                <th class="p-3.5">No. Batch / Lot</th>
                                <th class="p-3.5">Tgl Kedaluwarsa (ED)</th>
                                <th class="p-3.5 text-right">Stok Fisik</th>
                                <th class="p-3.5 text-right">Terkunci SPK</th>
                                <th class="p-3.5 text-right font-bold text-emerald-600">Stok Bebas</th>
                                <th class="p-3.5 text-right">Valuasi (IDR)</th>
                                <th class="p-3.5 text-center">Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 dark:divide-slate-700/50">
                            <tr v-for="stk in stocks.data" :key="stk.id" class="hover:bg-slate-50/70 dark:hover:bg-slate-700/30">
                                <td class="p-3.5">
                                    <div class="font-bold text-slate-800 dark:text-slate-200">{{ stk.product?.code }}</div>
                                    <div class="text-slate-500 line-clamp-1">{{ stk.product?.name }}</div>
                                </td>
                                <td class="p-3.5">
                                    <div class="font-medium text-slate-800 dark:text-slate-200">{{ stk.location?.name ?? stk.location?.code }}</div>
                                    <div class="text-[11px] text-slate-400">{{ stk.plant?.name }}</div>
                                </td>
                                <td class="p-3.5 font-medium text-slate-700 dark:text-slate-300">
                                    {{ stk.batch_number ?? '-' }}
                                </td>
                                <td class="p-3.5">
                                    <div class="text-slate-700 dark:text-slate-300">{{ stk.expiry_date ?? '-' }}</div>
                                    <span v-if="stk.expiry_date" class="text-[10px] text-amber-500 font-medium">FEFO Managed</span>
                                </td>
                                <td class="p-3.5 text-right font-medium text-slate-800 dark:text-slate-200">
                                    {{ formatNumber(stk.quantity_on_hand) }} {{ stk.product?.uom?.code }}
                                </td>
                                <td class="p-3.5 text-right text-blue-600">
                                    {{ formatNumber(stk.quantity_reserved) }}
                                </td>
                                <td class="p-3.5 text-right font-bold text-emerald-600">
                                    {{ formatNumber(stk.quantity_available) }} {{ stk.product?.uom?.code }}
                                </td>
                                <td class="p-3.5 text-right font-semibold text-slate-800 dark:text-slate-200">
                                    {{ formatCurrency(stk.quantity_on_hand * stk.unit_cost) }}
                                </td>
                                <td class="p-3.5 text-center">
                                    <span
                                        :class="[
                                            'inline-flex px-2.5 py-0.5 rounded-full text-[10px] font-bold uppercase',
                                            stk.status === 'available'
                                                ? 'bg-emerald-50 text-emerald-700 dark:bg-emerald-950/40 dark:text-emerald-400'
                                                : stk.status === 'quarantine'
                                                ? 'bg-amber-50 text-amber-700 dark:bg-amber-950/40 dark:text-amber-400'
                                                : stk.status === 'reserved'
                                                ? 'bg-blue-50 text-blue-700 dark:bg-blue-950/40 dark:text-blue-400'
                                                : 'bg-rose-50 text-rose-700 dark:bg-rose-950/40 dark:text-rose-400'
                                        ]"
                                    >
                                        {{ stk.status }}
                                    </span>
                                </td>
                            </tr>
                            <tr v-if="!stocks.data || stocks.data.length === 0">
                                <td colspan="9" class="p-8 text-center text-slate-400">
                                    Tidak ada data saldo stok gudang sesuai filter.
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Stock Movements Ledger -->
            <div class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-200 dark:border-slate-700 shadow-sm p-6 space-y-4">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <ArrowRightLeft class="w-5 h-5 text-indigo-500" />
                        <h2 class="text-base font-bold text-slate-900 dark:text-white">Ledger Audit Mutasi Stok Terbaru</h2>
                    </div>
                    <span class="text-xs text-slate-500">Pelacakan transaksi real-time</span>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs">
                        <thead>
                            <tr class="border-b border-slate-200 dark:border-slate-700 text-slate-400 font-semibold uppercase">
                                <th class="pb-3">No. Mutasi</th>
                                <th class="pb-3">Material</th>
                                <th class="pb-3">Dari Lokasi</th>
                                <th class="pb-3">Ke Lokasi</th>
                                <th class="pb-3">Tipe Referensi</th>
                                <th class="pb-3 text-right">Kuantitas</th>
                                <th class="pb-3">Keterangan</th>
                                <th class="pb-3">Petugas</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 dark:divide-slate-700/50">
                            <tr v-for="mov in recentMovements" :key="mov.id" class="hover:bg-slate-50 dark:hover:bg-slate-700/30">
                                <td class="py-3 font-semibold text-indigo-600">{{ mov.movement_number }}</td>
                                <td class="py-3 font-medium text-slate-800 dark:text-slate-200">{{ mov.product?.name }}</td>
                                <td class="py-3 text-slate-500">{{ mov.from_location?.name ?? 'Pemasok / Luar' }}</td>
                                <td class="py-3 text-slate-700 dark:text-slate-300 font-medium">{{ mov.to_location?.name ?? 'Lantai Pabrik / Retur' }}</td>
                                <td class="py-3 uppercase font-bold text-[10px] text-slate-500">{{ mov.reference_type }}</td>
                                <td class="py-3 text-right font-bold text-slate-900 dark:text-white">
                                    {{ formatNumber(mov.quantity) }} {{ mov.product?.uom?.code }}
                                </td>
                                <td class="py-3 text-slate-500 max-w-xs truncate">{{ mov.notes ?? '-' }}</td>
                                <td class="py-3 text-slate-600 dark:text-slate-400">{{ mov.creator?.name ?? 'System' }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </ModuleLayout>
</template>
