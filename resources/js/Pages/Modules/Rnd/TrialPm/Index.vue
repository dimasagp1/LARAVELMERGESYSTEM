<script setup>
import { Head, Link } from '@inertiajs/vue3';
import ModuleLayout from '@/Layouts/ModuleLayout.vue';
import {
    Boxes,
    Plus,
    Search,
    Eye,
    CheckCircle2,
    Clock,
    ArrowLeft,
    Check,
} from 'lucide-vue-next';

const props = defineProps({
    module: Object,
    trials: Object,
    stats: Object,
});
</script>

<template>
    <ModuleLayout
        :title="module?.name || 'Trial PM (Kemas Mesin)'"
        :module-slug="'rnd'"
        :module-name="'R&D (NPD Management)'"
        :color-theme="'cyan'"
        :menus="module?.menus || []"
        :breadcrumbs="['Trial Packaging Material (PM)']"
    >
        <Head title="Trial Packaging Material (PM) - R&D" />

        <div class="space-y-6">
            <!-- Header -->
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
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
                                Trial Packaging Material (PM)
                            </h1>
                            <span class="text-xs font-mono px-2 py-0.5 rounded bg-cyan-100 dark:bg-cyan-950 text-cyan-800 dark:text-cyan-300 font-bold border border-cyan-200 dark:border-cyan-800">
                                Konsensus 4 Departemen
                            </span>
                        </div>
                        <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">
                            Pengujian performa bahan kemas pada mesin pengemas dengan verifikasi kolektif R&D, QC, Produksi, dan Engineering.
                        </p>
                    </div>
                </div>

                <Link
                    href="/rnd/trial-pms/create"
                    class="px-4 py-2 rounded-xl bg-cyan-600 hover:bg-cyan-500 text-white font-semibold text-xs flex items-center gap-2 shadow-md shadow-cyan-600/20 transition"
                >
                    <Plus class="w-4 h-4" />
                    <span>+ Catat Trial PM Baru</span>
                </Link>
            </div>

            <!-- Stats Ribbon -->
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
                <div class="bg-white dark:bg-slate-900 rounded-xl border border-slate-200 dark:border-slate-800 p-3.5">
                    <div class="text-[10px] uppercase font-bold text-slate-400">Total Trial Kemas</div>
                    <div class="text-lg font-bold text-slate-900 dark:text-white mt-0.5">{{ stats?.total || 0 }}</div>
                </div>
                <div class="bg-white dark:bg-slate-900 rounded-xl border border-slate-200 dark:border-slate-800 p-3.5">
                    <div class="text-[10px] uppercase font-bold text-slate-400">Draf / Uji Mesin</div>
                    <div class="text-lg font-bold text-slate-700 dark:text-slate-300 mt-0.5">{{ stats?.draft || 0 }}</div>
                </div>
                <div class="bg-white dark:bg-slate-900 rounded-xl border border-slate-200 dark:border-slate-800 p-3.5">
                    <div class="text-[10px] uppercase font-bold text-amber-500">Menunggu Verifikasi 4-Dept</div>
                    <div class="text-lg font-bold text-amber-600 dark:text-amber-400 mt-0.5">{{ stats?.pending || 0 }}</div>
                </div>
                <div class="bg-white dark:bg-slate-900 rounded-xl border border-slate-200 dark:border-slate-800 p-3.5">
                    <div class="text-[10px] uppercase font-bold text-emerald-500">Disetujui OM (Final)</div>
                    <div class="text-lg font-bold text-emerald-600 dark:text-emerald-400 mt-0.5">{{ stats?.approved || 0 }}</div>
                </div>
            </div>

            <!-- Table Container -->
            <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 shadow-xs overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs text-slate-700 dark:text-slate-300">
                        <thead class="bg-slate-50 dark:bg-slate-800/60 uppercase font-mono text-[10px] text-slate-500 dark:text-slate-400 border-b border-slate-200 dark:border-slate-800">
                            <tr>
                                <th class="px-4 py-3">Kode Trial PM</th>
                                <th class="px-4 py-3">Produk</th>
                                <th class="px-4 py-3">Vendor Kemasan</th>
                                <th class="px-4 py-3 text-center">Paraf 4 Departemen</th>
                                <th class="px-4 py-3">Status</th>
                                <th class="px-4 py-3 text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                            <tr v-if="trials.data.length === 0">
                                <td colspan="6" class="px-6 py-12 text-center text-slate-400 text-xs">
                                    <Boxes class="w-8 h-8 mx-auto text-slate-300 dark:text-slate-700 mb-2" />
                                    <p class="font-medium">Belum ada catatan trial kemasan.</p>
                                </td>
                            </tr>

                            <tr v-for="t in trials.data" :key="t.id" class="hover:bg-slate-50/50 dark:hover:bg-slate-800/40">
                                <td class="px-4 py-3 font-mono font-bold text-slate-900 dark:text-white">
                                    <Link :href="`/rnd/trial-pms/${t.id}`" class="hover:text-cyan-600">
                                        {{ t.code }}
                                    </Link>
                                </td>
                                <td class="px-4 py-3 font-semibold text-slate-900 dark:text-white">
                                    {{ t.product?.name }}
                                </td>
                                <td class="px-4 py-3 text-slate-600 dark:text-slate-400">
                                    {{ t.vendor?.name || 'Vendor Acuan' }}
                                </td>
                                <td class="px-4 py-3 text-center">
                                    <div class="inline-flex items-center gap-1 font-mono text-[10px]">
                                        <span
                                            v-for="dept in ['R&D', 'QC', 'Production', 'Engineering']"
                                            :key="dept"
                                            :class="[
                                                'px-1.5 py-0.5 rounded border',
                                                t.approvals?.find((a) => a.department_name === dept && a.status === 'approved')
                                                    ? 'bg-emerald-50 dark:bg-emerald-950 text-emerald-700 dark:text-emerald-300 border-emerald-200 dark:border-emerald-800 font-bold'
                                                    : 'bg-slate-100 dark:bg-slate-800 text-slate-400 border-slate-200 dark:border-slate-700',
                                            ]"
                                            :title="dept"
                                        >
                                            {{ dept }}
                                        </span>
                                    </div>
                                </td>
                                <td class="px-4 py-3">
                                    <span
                                        :class="[
                                            'px-2 py-0.5 rounded text-[10px] font-bold uppercase tracking-wider border',
                                            t.status === 'approved'
                                                ? 'bg-emerald-50 dark:bg-emerald-950 text-emerald-700 dark:text-emerald-300 border-emerald-200 dark:border-emerald-800'
                                                : 'bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-400 border-slate-200 dark:border-slate-700',
                                        ]"
                                    >
                                        {{ t.status }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-right">
                                    <Link
                                        :href="`/rnd/trial-pms/${t.id}`"
                                        class="p-1.5 rounded hover:bg-slate-100 dark:hover:bg-slate-800 text-slate-500 hover:text-cyan-600"
                                    >
                                        <Eye class="w-4 h-4 inline" />
                                    </Link>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </ModuleLayout>
</template>
