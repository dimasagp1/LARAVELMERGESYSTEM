<script setup>
import { ref } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import ModuleLayout from '@/Layouts/ModuleLayout.vue';
import {
    Truck,
    Search,
    Filter,
    Plus,
    Eye,
    FileDown,
    Building2,
    Calendar,
    CheckCircle2,
    AlertCircle
} from 'lucide-vue-next';

const props = defineProps({
    receipts: Object,
    filters: Object,
    plants: Array,
});

const search = ref(props.filters?.search || '');
const status = ref(props.filters?.status || '');
const plantId = ref(props.filters?.plant_id || '');

const handleFilter = () => {
    router.get('/supply-chain/goods-receipt', {
        search: search.value || undefined,
        status: status.value || undefined,
        plant_id: plantId.value || undefined,
    }, { preserveState: true, replace: true });
};
</script>

<template>
    <Head title="Inbound Goods Receipt (GRN) - LogiFlow SCM" />

    <ModuleLayout current-module="supply-chain">
        <div class="space-y-6">
            <!-- Header -->
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 bg-white dark:bg-slate-800 p-6 rounded-2xl shadow-sm border border-slate-200 dark:border-slate-700">
                <div class="flex items-center gap-3">
                    <div class="p-2.5 bg-teal-500/10 text-teal-600 dark:text-teal-400 rounded-xl">
                        <Truck class="w-7 h-7" />
                    </div>
                    <div>
                        <h1 class="text-2xl font-bold text-slate-900 dark:text-white tracking-tight">
                            Penerimaan Barang Masuk (Inbound GRN)
                        </h1>
                        <p class="text-sm text-slate-500 dark:text-slate-400 mt-0.5">
                            Pintu masuk fisik seluruh material vendor, pencatatan surat jalan, dan auto-karantina mutu CPOTB.
                        </p>
                    </div>
                </div>

                <Link
                    href="/supply-chain/goods-receipt/create"
                    class="inline-flex items-center gap-2 px-4 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-semibold rounded-xl shadow-sm transition-all shadow-emerald-500/20"
                >
                    <Plus class="w-4 h-4" />
                    <span>Catat Penerimaan Baru</span>
                </Link>
            </div>

            <!-- Filters -->
            <div class="bg-white dark:bg-slate-800 p-4 rounded-xl border border-slate-200 dark:border-slate-700 shadow-sm flex flex-col sm:flex-row items-center gap-3">
                <div class="relative flex-1 w-full">
                    <Search class="w-4 h-4 text-slate-400 absolute left-3 top-1/2 -translate-y-1/2" />
                    <input
                        type="text"
                        v-model="search"
                        @keyup.enter="handleFilter"
                        placeholder="Cari No. GRN, PO, Surat Jalan, atau Vendor..."
                        class="w-full pl-9 pr-3 py-2 text-xs rounded-lg border border-slate-200 dark:border-slate-700 dark:bg-slate-900 dark:text-white"
                    />
                </div>

                <select
                    v-model="status"
                    @change="handleFilter"
                    class="w-full sm:w-44 text-xs rounded-lg border border-slate-200 dark:border-slate-700 dark:bg-slate-900 dark:text-white py-2"
                >
                    <option value="">Semua Status</option>
                    <option value="quarantine">Karantina QC</option>
                    <option value="inspected">Diperiksa Sebagian</option>
                    <option value="completed">Selesai Lulus</option>
                </select>

                <select
                    v-model="plantId"
                    @change="handleFilter"
                    class="w-full sm:w-48 text-xs rounded-lg border border-slate-200 dark:border-slate-700 dark:bg-slate-900 dark:text-white py-2"
                >
                    <option value="">Semua Pabrik</option>
                    <option v-for="plant in plants" :key="plant.id" :value="plant.id">{{ plant.name }}</option>
                </select>

                <button
                    @click="handleFilter"
                    class="px-4 py-2 bg-slate-100 hover:bg-slate-200 dark:bg-slate-700 dark:hover:bg-slate-600 text-slate-700 dark:text-slate-200 text-xs font-semibold rounded-lg transition-colors"
                >
                    Terapkan
                </button>
            </div>

            <!-- Data Table -->
            <div class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-200 dark:border-slate-700 shadow-sm overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs">
                        <thead>
                            <tr class="bg-slate-50 dark:bg-slate-700/50 border-b border-slate-200 dark:border-slate-700 text-slate-500 font-semibold uppercase">
                                <th class="p-3.5">No. GRN</th>
                                <th class="p-3.5">No. PO & Surat Jalan</th>
                                <th class="p-3.5">Vendor / Pemasok</th>
                                <th class="p-3.5">Pabrik & Tanggal</th>
                                <th class="p-3.5 text-center">Jumlah Item</th>
                                <th class="p-3.5 text-center">Status</th>
                                <th class="p-3.5 text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 dark:divide-slate-700/50">
                            <tr v-for="rc in receipts.data" :key="rc.id" class="hover:bg-slate-50/70 dark:hover:bg-slate-700/30">
                                <td class="p-3.5 font-bold text-teal-600">
                                    {{ rc.grn_number }}
                                </td>
                                <td class="p-3.5">
                                    <div class="font-medium text-slate-800 dark:text-slate-200">{{ rc.po_number ?? 'PO Reguler' }}</div>
                                    <div class="text-[11px] text-slate-400">SJ: {{ rc.delivery_order_number }}</div>
                                </td>
                                <td class="p-3.5 font-medium text-slate-700 dark:text-slate-300">
                                    {{ rc.vendor?.name }}
                                </td>
                                <td class="p-3.5">
                                    <div class="text-slate-800 dark:text-slate-200">{{ rc.plant?.name }}</div>
                                    <div class="text-[11px] text-slate-400">{{ rc.receipt_date }}</div>
                                </td>
                                <td class="p-3.5 text-center font-semibold text-slate-700 dark:text-slate-300">
                                    {{ rc.items_count }} Item
                                </td>
                                <td class="p-3.5 text-center">
                                    <span
                                        :class="[
                                            'inline-flex px-2.5 py-0.5 rounded-full text-[10px] font-bold uppercase',
                                            rc.status === 'completed'
                                                ? 'bg-emerald-50 text-emerald-700 dark:bg-emerald-950/40 dark:text-emerald-400'
                                                : 'bg-amber-50 text-amber-700 dark:bg-amber-950/40 dark:text-amber-400'
                                        ]"
                                    >
                                        {{ rc.status === 'completed' ? 'LULUS / SELESAI' : 'KARANTINA QC' }}
                                    </span>
                                </td>
                                <td class="p-3.5 text-right">
                                    <div class="flex items-center justify-end gap-1">
                                        <Link
                                            :href="`/supply-chain/goods-receipt/${rc.id}`"
                                            class="p-1.5 text-slate-400 hover:text-teal-600 rounded-lg hover:bg-slate-100 dark:hover:bg-slate-700"
                                            title="Lihat Detail"
                                        >
                                            <Eye class="w-4 h-4" />
                                        </Link>
                                        <a
                                            :href="`/supply-chain/goods-receipt/${rc.id}/pdf`"
                                            target="_blank"
                                            class="p-1.5 text-slate-400 hover:text-rose-600 rounded-lg hover:bg-slate-100 dark:hover:bg-slate-700"
                                            title="Unduh PDF GRN"
                                        >
                                            <FileDown class="w-4 h-4" />
                                        </a>
                                    </div>
                                </td>
                            </tr>
                            <tr v-if="!receipts.data || receipts.data.length === 0">
                                <td colspan="7" class="p-8 text-center text-slate-400">
                                    Tidak ada data penerimaan barang yang sesuai dengan filter.
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </ModuleLayout>
</template>
