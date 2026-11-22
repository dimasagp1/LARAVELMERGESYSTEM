<script setup>
import { Head, Link } from '@inertiajs/vue3';
import ModuleLayout from '@/Layouts/ModuleLayout.vue';
import { Award, Plus, ArrowLeft } from 'lucide-vue-next';

const props = defineProps({
    module: Object,
    evaluations: Object,
    stats: Object,
});
</script>

<template>
    <ModuleLayout
        :title="module?.name || 'Uji Sensori'"
        :module-slug="'rnd'"
        :module-name="'R&D (NPD Management)'"
        :color-theme="'cyan'"
        :menus="module?.menus || []"
        :breadcrumbs="['Uji Sensori & Panelis']"
    >
        <Head title="Uji Sensori & Evaluasi Panelis - R&D" />

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
                            Evaluasi Sensori & Panelis (Hedonik)
                        </h1>
                        <p class="text-xs text-slate-500 dark:text-slate-400">
                            Pengujian organoleptik panelis: Rasa, Warna, Aroma, Tekstur, dan After Taste (Skala 1-5).
                        </p>
                    </div>
                </div>

                <Link
                    href="/rnd/sample-evaluations/create"
                    class="px-4 py-2 rounded-xl bg-cyan-600 hover:bg-cyan-500 text-white font-semibold text-xs flex items-center gap-2 shadow-md shadow-cyan-600/20 transition"
                >
                    <Plus class="w-4 h-4" />
                    <span>+ Catat Sesi Sensori</span>
                </Link>
            </div>

            <!-- Table -->
            <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 shadow-xs overflow-hidden">
                <table class="w-full text-left text-xs text-slate-700 dark:text-slate-300">
                    <thead class="bg-slate-50 dark:bg-slate-800/60 uppercase font-mono text-[10px] text-slate-500 dark:text-slate-400 border-b border-slate-200 dark:border-slate-800">
                        <tr>
                            <th class="px-4 py-3">Kode Sampel</th>
                            <th class="px-4 py-3">Produk</th>
                            <th class="px-4 py-3">Tgl Sesi</th>
                            <th class="px-4 py-3 text-center">Panelis</th>
                            <th class="px-4 py-3 text-right">Rasa</th>
                            <th class="px-4 py-3 text-right">Warna</th>
                            <th class="px-4 py-3 text-right">Aroma</th>
                            <th class="px-4 py-3 text-right font-bold">Skor Akhir</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                        <tr v-if="evaluations.data.length === 0">
                            <td colspan="8" class="px-6 py-10 text-center text-slate-400">Belum ada sesi evaluasi sensori.</td>
                        </tr>
                        <tr v-for="e in evaluations.data" :key="e.id">
                            <td class="px-4 py-3 font-mono font-bold text-slate-900 dark:text-white">{{ e.sample_code }}</td>
                            <td class="px-4 py-3 font-semibold">{{ e.product?.name }}</td>
                            <td class="px-4 py-3 font-mono">{{ e.session_date }}</td>
                            <td class="px-4 py-3 text-center font-mono">{{ e.panelist_count }} org</td>
                            <td class="px-4 py-3 text-right font-mono">{{ e.average_taste }}/5</td>
                            <td class="px-4 py-3 text-right font-mono">{{ e.average_color }}/5</td>
                            <td class="px-4 py-3 text-right font-mono">{{ e.average_aroma }}/5</td>
                            <td class="px-4 py-3 text-right font-mono font-bold text-emerald-600 dark:text-emerald-400">
                                {{ e.overall_score }} / 5.0
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </ModuleLayout>
</template>
