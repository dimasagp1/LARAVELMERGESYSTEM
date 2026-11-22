<script setup>
import { Head, Link, router } from '@inertiajs/vue3';
import ModuleLayout from '@/Layouts/ModuleLayout.vue';
import {
    FlaskConical,
    ArrowLeft,
    RotateCcw,
    CheckCircle2,
    XCircle,
    AlertCircle,
    Layers,
    Table,
} from 'lucide-vue-next';

const props = defineProps({
    module: Object,
    trial: Object,
});

function triggerAutoClone() {
    if (
        confirm(
            `Hasil trial menyatakan 'Reformulasi'. Inisiasi Auto-Clone untuk menduplikasi formula ${props.trial.formula?.code} ke versi baru?`
        )
    ) {
        router.post(`/rnd/formulas/${props.trial.formula_id}/reformula`);
    }
}
</script>

<template>
    <ModuleLayout
        :title="`${trial.code} - Trial RM`"
        :module-slug="'rnd'"
        :module-name="'R&D (NPD Management)'"
        :color-theme="'cyan'"
        :menus="module?.menus || []"
        :breadcrumbs="['Trial Raw Material', trial.code]"
    >
        <Head :title="`${trial.code} - ${trial.product?.name}`" />

        <div class="max-w-4xl mx-auto space-y-6">
            <!-- Header Bar -->
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <Link
                        href="/rnd/trial-rms"
                        class="p-2 rounded-xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 text-slate-500 hover:text-slate-900 dark:hover:text-white transition shadow-2xs"
                    >
                        <ArrowLeft class="w-4 h-4" />
                    </Link>
                    <div>
                        <div class="flex items-center gap-2">
                            <h1 class="text-xl font-bold text-slate-900 dark:text-white tracking-tight">
                                {{ trial.code }}
                            </h1>
                            <span
                                :class="[
                                    'px-2 py-0.5 rounded text-[10px] font-bold uppercase tracking-wider border',
                                    trial.decision === 'lulus'
                                        ? 'bg-emerald-50 text-emerald-700 border-emerald-200'
                                        : 'bg-rose-50 text-rose-700 border-rose-200',
                                ]"
                            >
                                Keputusan: {{ trial.decision }}
                            </span>
                        </div>
                        <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">
                            Produk: {{ trial.product?.name }} &bull; Formula Acuan: {{ trial.formula?.code }}
                        </p>
                    </div>
                </div>

                <!-- Auto-Clone Button if Reformulasi -->
                <div v-if="trial.decision === 'reformulasi'">
                    <button
                        @click="triggerAutoClone"
                        class="px-4 py-2 rounded-xl bg-amber-600 hover:bg-amber-500 text-white font-semibold text-xs flex items-center gap-2 shadow-md shadow-amber-600/20 transition"
                    >
                        <RotateCcw class="w-4 h-4" />
                        <span>Inisiasi Reformulasi (Auto-Clone)</span>
                    </button>
                </div>
            </div>

            <!-- Reformulation Alert Banner if trial failed -->
            <div
                v-if="trial.decision === 'reformulasi'"
                class="rounded-2xl border border-rose-300 dark:border-rose-800 bg-rose-50 dark:bg-rose-950/40 p-4 text-rose-900 dark:text-rose-200 flex items-center justify-between text-xs"
            >
                <div class="flex items-center gap-3">
                    <AlertCircle class="w-5 h-5 text-rose-600 dark:text-rose-400 shrink-0" />
                    <div>
                        <strong class="font-bold">Uji Coba Laboratorium Belum Memenuhi Syarat Mutu:</strong>
                        Sampel memerlukan perbaikan rasio/eksipien. Klik tombol di kanan untuk melakukan <strong>Auto-Clone</strong> komposisi ke versi formula baru.
                    </div>
                </div>
                <button
                    @click="triggerAutoClone"
                    class="px-3.5 py-1.5 rounded-lg bg-rose-600 text-white font-semibold text-xs shrink-0"
                >
                    Auto-Clone Sekarang &rarr;
                </button>
            </div>

            <!-- Parameters Card -->
            <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 shadow-xs overflow-hidden">
                <div class="px-5 py-4 border-b border-slate-200 dark:border-slate-800 flex items-center justify-between">
                    <h3 class="text-xs font-bold uppercase tracking-wider text-slate-900 dark:text-white">
                        Hasil Evaluasi Parameter Fisika-Kimia & Organoleptik
                    </h3>
                    <span class="text-xs font-mono text-slate-400">{{ trial.verifications?.length || 0 }} Parameter</span>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs text-slate-700 dark:text-slate-300">
                        <thead class="bg-slate-50 dark:bg-slate-800/60 uppercase font-mono text-[10px] text-slate-500 dark:text-slate-400 border-b border-slate-200 dark:border-slate-800">
                            <tr>
                                <th class="px-4 py-3">Parameter Uji</th>
                                <th class="px-4 py-3">Target Spesifikasi</th>
                                <th class="px-4 py-3 font-mono">Hasil Aktual Lab</th>
                                <th class="px-4 py-3 text-center">Status Kesesuaian</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                            <tr v-for="v in trial.verifications" :key="v.id" class="hover:bg-slate-50/50 dark:hover:bg-slate-800/40">
                                <td class="px-4 py-3 font-semibold text-slate-900 dark:text-white">{{ v.parameter }}</td>
                                <td class="px-4 py-3 font-mono text-slate-500">{{ v.target_spec }}</td>
                                <td class="px-4 py-3 font-mono font-bold text-slate-800 dark:text-slate-200">{{ v.actual_result }}</td>
                                <td class="px-4 py-3 text-center">
                                    <span
                                        :class="[
                                            'px-2 py-0.5 rounded text-[10px] font-bold uppercase tracking-wider border',
                                            v.is_compliant
                                                ? 'bg-emerald-50 text-emerald-700 border-emerald-200'
                                                : 'bg-rose-50 text-rose-700 border-rose-200',
                                        ]"
                                    >
                                        {{ v.is_compliant ? 'Lolos' : 'Deviasi' }}
                                    </span>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Steps & Notes -->
            <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 p-5 space-y-2 text-xs">
                <h4 class="font-bold text-slate-900 dark:text-white uppercase tracking-wider text-[11px]">
                    Langkah Preparasi & Catatan Lab
                </h4>
                <p class="text-slate-600 dark:text-slate-300 whitespace-pre-line leading-relaxed">
                    {{ trial.process_steps || 'Tidak ada catatan preparasi khusus.' }}
                </p>
            </div>
        </div>
    </ModuleLayout>
</template>
