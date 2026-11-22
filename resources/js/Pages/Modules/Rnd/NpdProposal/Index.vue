<script setup>
import { Head, Link } from '@inertiajs/vue3';
import ModuleLayout from '@/Layouts/ModuleLayout.vue';
import { Layers, Plus, ArrowLeft } from 'lucide-vue-next';

const props = defineProps({
    module: Object,
    proposals: Object,
    stats: Object,
});
</script>

<template>
    <ModuleLayout
        :title="module?.name || 'NPD Proposal'"
        :module-slug="'rnd'"
        :module-name="'R&D (NPD Management)'"
        :color-theme="'cyan'"
        :menus="module?.menus || []"
        :breadcrumbs="['NPD Proposals']"
    >
        <Head title="NPD Proposals - R&D" />

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
                            Proposal Pengembangan Produk Baru (NPD)
                        </h1>
                        <p class="text-xs text-slate-500 dark:text-slate-400">
                            Transformasi PRF menjadi rencana teknis, target COGS, dan linimasa jadwal riset.
                        </p>
                    </div>
                </div>

                <Link
                    href="/rnd/npd-proposals/create"
                    class="px-4 py-2 rounded-xl bg-cyan-600 hover:bg-cyan-500 text-white font-semibold text-xs flex items-center gap-2 shadow-md shadow-cyan-600/20 transition"
                >
                    <Plus class="w-4 h-4" />
                    <span>+ Buat Proposal NPD</span>
                </Link>
            </div>

            <!-- Table -->
            <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 shadow-xs overflow-hidden">
                <table class="w-full text-left text-xs text-slate-700 dark:text-slate-300">
                    <thead class="bg-slate-50 dark:bg-slate-800/60 uppercase font-mono text-[10px] text-slate-500 dark:text-slate-400 border-b border-slate-200 dark:border-slate-800">
                        <tr>
                            <th class="px-4 py-3">Kode Proposal</th>
                            <th class="px-4 py-3">Produk</th>
                            <th class="px-4 py-3">Ref. PRF</th>
                            <th class="px-4 py-3 text-right">Target COGS</th>
                            <th class="px-4 py-3 text-right">Target Harga Jual</th>
                            <th class="px-4 py-3">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                        <tr v-if="proposals.data.length === 0">
                            <td colspan="6" class="px-6 py-10 text-center text-slate-400">Belum ada proposal NPD.</td>
                        </tr>
                        <tr v-for="npd in proposals.data" :key="npd.id">
                            <td class="px-4 py-3 font-mono font-bold text-slate-900 dark:text-white">{{ npd.code }}</td>
                            <td class="px-4 py-3 font-semibold">{{ npd.product?.name }}</td>
                            <td class="px-4 py-3 font-mono text-cyan-600">{{ npd.prf?.code || '-' }}</td>
                            <td class="px-4 py-3 text-right font-mono">Rp {{ Number(npd.target_cogs).toLocaleString('id-ID') }}</td>
                            <td class="px-4 py-3 text-right font-mono font-bold">Rp {{ Number(npd.target_selling_price).toLocaleString('id-ID') }}</td>
                            <td class="px-4 py-3">
                                <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase tracking-wider bg-emerald-50 text-emerald-700 border border-emerald-200">
                                    {{ npd.status }}
                                </span>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </ModuleLayout>
</template>
