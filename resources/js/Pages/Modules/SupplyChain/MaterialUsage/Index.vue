<script setup>
import { ref } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import ModuleLayout from '@/Layouts/ModuleLayout.vue';
import {
    ArrowRightCircle,
    Search,
    Plus,
    Eye,
    FileDown,
    Building2,
    Calendar,
    User
} from 'lucide-vue-next';

const props = defineProps({
    usages: Object,
    filters: Object,
});

const search = ref(props.filters?.search || '');

const handleFilter = () => {
    router.get('/supply-chain/material-usage', {
        search: search.value || undefined,
    }, { preserveState: true, replace: true });
};
</script>

<template>
    <Head title="Bukti Serah Terima Bahan (BSTB) - LogiFlow SCM" />

    <ModuleLayout current-module="supply-chain">
        <div class="space-y-6">
            <!-- Header -->
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 bg-white dark:bg-slate-800 p-6 rounded-2xl shadow-sm border border-slate-200 dark:border-slate-700">
                <div class="flex items-center gap-3">
                    <div class="p-2.5 bg-blue-500/10 text-blue-600 dark:text-blue-400 rounded-xl">
                        <ArrowRightCircle class="w-7 h-7" />
                    </div>
                    <div>
                        <h1 class="text-2xl font-bold text-slate-900 dark:text-white tracking-tight">
                            Bukti Serah Terima Bahan (BSTB)
                        </h1>
                        <p class="text-sm text-slate-500 dark:text-slate-400 mt-0.5">
                            Dokumen resmi pengeluaran bahan baku & kemas dari gudang menuju stasiun kerja lantai manufaktur.
                        </p>
                    </div>
                </div>

                <Link
                    href="/supply-chain/material-usage/create"
                    class="inline-flex items-center gap-2 px-4 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-semibold rounded-xl shadow-sm transition-all shadow-emerald-500/20"
                >
                    <Plus class="w-4 h-4" />
                    <span>Terbitkan BSTB Baru</span>
                </Link>
            </div>

            <!-- Search Bar -->
            <div class="bg-white dark:bg-slate-800 p-4 rounded-xl border border-slate-200 dark:border-slate-700 shadow-sm flex items-center gap-3">
                <div class="relative flex-1 w-full">
                    <Search class="w-4 h-4 text-slate-400 absolute left-3 top-1/2 -translate-y-1/2" />
                    <input
                        type="text"
                        v-model="search"
                        @keyup.enter="handleFilter"
                        placeholder="Cari No. BSTB atau No. SPK / Work Order..."
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
                                <th class="p-3.5">No. BSTB</th>
                                <th class="p-3.5">No. SPK / Work Order</th>
                                <th class="p-3.5">Pabrik & Dept</th>
                                <th class="p-3.5">Petugas Penyerah</th>
                                <th class="p-3.5">Tanggal Penyerahan</th>
                                <th class="p-3.5 text-center">Jumlah Item</th>
                                <th class="p-3.5 text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 dark:divide-slate-700/50">
                            <tr v-for="usg in usages.data" :key="usg.id" class="hover:bg-slate-50/70 dark:hover:bg-slate-700/30">
                                <td class="p-3.5 font-bold text-blue-600">
                                    {{ usg.usage_number }}
                                </td>
                                <td class="p-3.5 font-semibold text-slate-800 dark:text-slate-200">
                                    {{ usg.work_order_number }}
                                </td>
                                <td class="p-3.5">
                                    <div class="text-slate-800 dark:text-slate-200">{{ usg.plant?.name }}</div>
                                    <div class="text-[11px] text-slate-400">{{ usg.department?.name }}</div>
                                </td>
                                <td class="p-3.5 text-slate-600 dark:text-slate-300">
                                    {{ usg.issuer?.name ?? 'Staff Gudang' }}
                                </td>
                                <td class="p-3.5 text-slate-600 dark:text-slate-300">
                                    {{ usg.usage_date }}
                                </td>
                                <td class="p-3.5 text-center font-bold text-slate-700 dark:text-slate-300">
                                    {{ usg.items_count }} Item
                                </td>
                                <td class="p-3.5 text-right">
                                    <div class="flex items-center justify-end gap-1">
                                        <Link
                                            :href="`/supply-chain/material-usage/${usg.id}`"
                                            class="p-1.5 text-slate-400 hover:text-blue-600 rounded-lg hover:bg-slate-100 dark:hover:bg-slate-700"
                                            title="Lihat Detail BSTB"
                                        >
                                            <Eye class="w-4 h-4" />
                                        </Link>
                                        <a
                                            :href="`/supply-chain/material-usage/${usg.id}/pdf`"
                                            target="_blank"
                                            class="p-1.5 text-slate-400 hover:text-rose-600 rounded-lg hover:bg-slate-100 dark:hover:bg-slate-700"
                                            title="Unduh PDF BSTB"
                                        >
                                            <FileDown class="w-4 h-4" />
                                        </a>
                                    </div>
                                </td>
                            </tr>
                            <tr v-if="!usages.data || usages.data.length === 0">
                                <td colspan="7" class="p-8 text-center text-slate-400">
                                    Belum ada catatan serah terima bahan (BSTB).
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </ModuleLayout>
</template>
