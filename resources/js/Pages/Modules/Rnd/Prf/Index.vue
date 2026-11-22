<script setup>
import { Head, Link } from '@inertiajs/vue3';
import ModuleLayout from '@/Layouts/ModuleLayout.vue';
import {
    FileText,
    Plus,
    ArrowLeft,
} from 'lucide-vue-next';

const props = defineProps({
    module: Object,
    prfs: Object,
    stats: Object,
});
</script>

<template>
    <ModuleLayout
        :title="module?.name || 'PRF'"
        :module-slug="'rnd'"
        :module-name="'R&D (NPD Management)'"
        :color-theme="'cyan'"
        :menus="module?.menus || []"
        :breadcrumbs="['Product Request Form (PRF)']"
    >
        <Head title="Product Request Form (PRF) - R&D" />

        <div class="space-y-6">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <Link
                        href="/rnd"
                        class="p-2 rounded-xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 text-slate-500 hover:text-slate-900 dark:hover:text-white transition"
                    >
                        <ArrowLeft class="w-4 h-4" />
                    </Link>
                    <div>
                        <h1 class="text-xl font-bold text-slate-900 dark:text-white tracking-tight">
                            Product Request Form (PRF)
                        </h1>
                        <p class="text-xs text-slate-500 dark:text-slate-400">
                            Permintaan inisiasi produk baru dari divisi Marketing / Bisnis ke R&D.
                        </p>
                    </div>
                </div>

                <Link
                    href="/rnd/prfs/create"
                    class="px-4 py-2 rounded-xl bg-cyan-600 hover:bg-cyan-500 text-white font-semibold text-xs flex items-center gap-2 shadow-md shadow-cyan-600/20 transition"
                >
                    <Plus class="w-4 h-4" />
                    <span>+ Buat PRF Baru</span>
                </Link>
            </div>

            <!-- Table -->
            <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 shadow-xs overflow-hidden">
                <table class="w-full text-left text-xs text-slate-700 dark:text-slate-300">
                    <thead class="bg-slate-50 dark:bg-slate-800/60 uppercase font-mono text-[10px] text-slate-500 dark:text-slate-400 border-b border-slate-200 dark:border-slate-800">
                        <tr>
                            <th class="px-4 py-3">Kode PRF</th>
                            <th class="px-4 py-3">Target Produk</th>
                            <th class="px-4 py-3">Target Pasar</th>
                            <th class="px-4 py-3">Target Launch</th>
                            <th class="px-4 py-3">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                        <tr v-if="prfs.data.length === 0">
                            <td colspan="5" class="px-6 py-10 text-center text-slate-400">Belum ada dokumen PRF.</td>
                        </tr>
                        <tr v-for="p in prfs.data" :key="p.id">
                            <td class="px-4 py-3 font-mono font-bold text-slate-900 dark:text-white">{{ p.code }}</td>
                            <td class="px-4 py-3 font-semibold">{{ p.product?.name }}</td>
                            <td class="px-4 py-3">{{ p.target_market || 'Umum' }}</td>
                            <td class="px-4 py-3 font-mono">{{ p.target_launch_date || '-' }}</td>
                            <td class="px-4 py-3">
                                <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase tracking-wider bg-cyan-50 text-cyan-700 border border-cyan-200">
                                    {{ p.status }}
                                </span>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </ModuleLayout>
</template>
