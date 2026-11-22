<script setup>
import { ref } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import ModuleLayout from '@/Layouts/ModuleLayout.vue';
import {
    CornerUpLeft,
    Search,
    Plus,
    Eye,
    FileDown,
    Building2,
    Calendar,
    AlertCircle
} from 'lucide-vue-next';

const props = defineProps({
    returns: Object,
    filters: Object,
});

const search = ref(props.filters?.search || '');

const handleFilter = () => {
    router.get('/supply-chain/material-return', {
        search: search.value || undefined,
    }, { preserveState: true, replace: true });
};

const formatCurrency = (val) => new Intl.NumberFormat('id-ID', {
    style: 'currency',
    currency: 'IDR',
    maximumFractionDigits: 0,
}).format(val || 0);
</script>

<template>
    <Head title="Retur Material Vendor - LogiFlow SCM" />

    <ModuleLayout current-module="supply-chain">
        <div class="space-y-6">
            <!-- Header -->
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 bg-white dark:bg-slate-800 p-6 rounded-2xl shadow-sm border border-slate-200 dark:border-slate-700">
                <div class="flex items-center gap-3">
                    <div class="p-2.5 bg-rose-500/10 text-rose-600 dark:text-rose-400 rounded-xl">
                        <CornerUpLeft class="w-7 h-7" />
                    </div>
                    <div>
                        <h1 class="text-2xl font-bold text-slate-900 dark:text-white tracking-tight">
                            Pengembalian Material Cacat (Vendor Return)
                        </h1>
                        <p class="text-sm text-slate-500 dark:text-slate-400 mt-0.5">
                            Dokumen resmi memo retur atas material yang ditolak (Reject) oleh gerbang mutu QC incoming.
                        </p>
                    </div>
                </div>

                <Link
                    href="/supply-chain/material-return/create"
                    class="inline-flex items-center gap-2 px-4 py-2.5 bg-rose-600 hover:bg-rose-700 text-white text-sm font-semibold rounded-xl shadow-sm transition-all shadow-rose-500/20"
                >
                    <Plus class="w-4 h-4" />
                    <span>Buat Memo Retur Baru</span>
                </Link>
            </div>

            <!-- Search -->
            <div class="bg-white dark:bg-slate-800 p-4 rounded-xl border border-slate-200 dark:border-slate-700 shadow-sm flex items-center gap-3">
                <div class="relative flex-1 w-full">
                    <Search class="w-4 h-4 text-slate-400 absolute left-3 top-1/2 -translate-y-1/2" />
                    <input
                        type="text"
                        v-model="search"
                        @keyup.enter="handleFilter"
                        placeholder="Cari No. Retur atau Nama Vendor..."
                        class="w-full pl-9 pr-3 py-2 text-xs rounded-lg border border-slate-200 dark:border-slate-700 dark:bg-slate-900 dark:text-white"
                    />
                </div>
                <button
                    @click="handleFilter"
                    class="px-4 py-2 bg-slate-100 hover:bg-slate-200 dark:bg-slate-700 dark:hover:bg-slate-600 text-slate-700 dark:text-slate-200 text-xs font-semibold rounded-lg transition-colors"
                >
                    Cari
                </button>
            </div>

            <!-- Table -->
            <div class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-200 dark:border-slate-700 shadow-sm overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs">
                        <thead>
                            <tr class="bg-slate-50 dark:bg-slate-700/50 border-b border-slate-200 dark:border-slate-700 text-slate-500 font-semibold uppercase">
                                <th class="p-3.5">No. Retur</th>
                                <th class="p-3.5">Vendor Rekanan</th>
                                <th class="p-3.5">Ref. GRN</th>
                                <th class="p-3.5">Tanggal Retur</th>
                                <th class="p-3.5">Alasan Penolakan</th>
                                <th class="p-3.5 text-right">Nilai Klaim</th>
                                <th class="p-3.5 text-center">Status</th>
                                <th class="p-3.5 text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 dark:divide-slate-700/50">
                            <tr v-for="ret in returns.data" :key="ret.id" class="hover:bg-slate-50/70 dark:hover:bg-slate-700/30">
                                <td class="p-3.5 font-bold text-rose-600">
                                    {{ ret.return_number }}
                                </td>
                                <td class="p-3.5 font-medium text-slate-800 dark:text-slate-200">
                                    {{ ret.vendor?.name }}
                                </td>
                                <td class="p-3.5 text-slate-600 dark:text-slate-300">
                                    {{ ret.goods_receipt?.grn_number ?? '-' }}
                                </td>
                                <td class="p-3.5 text-slate-600 dark:text-slate-300">
                                    {{ ret.return_date }}
                                </td>
                                <td class="p-3.5 text-slate-500 max-w-xs truncate">
                                    {{ ret.reason }}
                                </td>
                                <td class="p-3.5 text-right font-bold text-slate-900 dark:text-white">
                                    {{ formatCurrency(ret.total_amount) }}
                                </td>
                                <td class="p-3.5 text-center">
                                    <span class="inline-flex px-2 py-0.5 rounded-full text-[10px] font-bold uppercase bg-rose-50 text-rose-700 dark:bg-rose-950/40 dark:text-rose-400">
                                        {{ ret.status }}
                                    </span>
                                </td>
                                <td class="p-3.5 text-right">
                                    <a
                                        :href="`/supply-chain/material-return/${ret.id}/pdf`"
                                        target="_blank"
                                        class="p-1.5 text-slate-400 hover:text-rose-600 rounded-lg hover:bg-slate-100 dark:hover:bg-slate-700 inline-block"
                                        title="Unduh PDF Surat Jalan Retur"
                                    >
                                        <FileDown class="w-4 h-4" />
                                    </a>
                                </td>
                            </tr>
                            <tr v-if="!returns.data || returns.data.length === 0">
                                <td colspan="8" class="p-8 text-center text-slate-400">
                                    Tidak ada catatan pengembalian barang reject.
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </ModuleLayout>
</template>
