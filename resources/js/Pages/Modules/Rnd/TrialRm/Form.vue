<script setup>
import { ref } from 'vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import ModuleLayout from '@/Layouts/ModuleLayout.vue';
import {
    FlaskConical,
    Save,
    ArrowLeft,
    CheckCircle2,
    XCircle,
    Layers,
    Table,
} from 'lucide-vue-next';

const props = defineProps({
    module: Object,
    approvedFormulas: {
        type: Array,
        default: () => [],
    },
    defaultParameters: {
        type: Array,
        default: () => [],
    },
});

const form = useForm({
    formula_id: props.approvedFormulas[0]?.id || '',
    product_id: props.approvedFormulas[0]?.product_id || '',
    batch_qty_kg: 1.0,
    process_steps: '1. Timbang bahan aktif dan eksipien.\n2. Mixing kecepatan 1500 RPM selama 15 menit.\n3. Uji homogenitas dan organoleptik sediaan.',
    decision: 'lulus',
    notes: '',
    verifications: props.defaultParameters.map((p) => ({
        parameter: p.parameter,
        target_spec: p.target_spec,
        actual_result: p.actual_result,
        is_compliant: p.is_compliant,
        notes: '',
    })),
});

function handleFormulaChange() {
    const f = props.approvedFormulas.find((item) => item.id === form.formula_id);
    if (f) {
        form.product_id = f.product_id;
    }
}

function submit() {
    form.post('/rnd/trial-rms');
}
</script>

<template>
    <ModuleLayout
        :title="'Catat Trial Lab RM'"
        :module-slug="'rnd'"
        :module-name="'R&D (NPD Management)'"
        :color-theme="'cyan'"
        :menus="module?.menus || []"
        :breadcrumbs="['Trial Raw Material', 'Input Hasil Lab']"
    >
        <Head title="Catat Hasil Trial RM Laboratorium - R&D" />

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
                        <h1 class="text-xl font-bold text-slate-900 dark:text-white tracking-tight">
                            Pencatatan Hasil Uji Coba Laboratorium (Trial RM)
                        </h1>
                        <p class="text-xs text-slate-500 dark:text-slate-400">
                            Evaluasi kesesuaian target spek vs aktual lab terhadap formula yang telah disetujui.
                        </p>
                    </div>
                </div>

                <button
                    type="button"
                    @click="submit"
                    :disabled="form.processing"
                    class="px-5 py-2.5 rounded-xl bg-cyan-600 hover:bg-cyan-500 text-white font-semibold text-xs flex items-center gap-2 shadow-md shadow-cyan-600/20 transition disabled:opacity-50"
                >
                    <Save class="w-4 h-4" />
                    <span>Simpan Hasil Trial</span>
                </button>
            </div>

            <!-- Form Body -->
            <form @submit.prevent="submit" class="space-y-6">
                <!-- Formula & Batch Info -->
                <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 shadow-xs p-6 space-y-4">
                    <h2 class="text-xs font-bold uppercase tracking-wider text-slate-400 border-b border-slate-100 dark:border-slate-800 pb-2">
                        1. Parameter Formula & Batch Laboratorium
                    </h2>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
                        <div class="md:col-span-2">
                            <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">
                                Formula Acuan (Approved Only)
                            </label>
                            <select
                                v-model="form.formula_id"
                                @change="handleFormulaChange"
                                required
                                class="w-full bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl px-3.5 py-2.5 text-xs text-slate-800 dark:text-slate-100 focus:ring-cyan-500"
                            >
                                <option v-for="f in approvedFormulas" :key="f.id" :value="f.id">
                                    {{ f.code }} - {{ f.product?.name }} (V{{ f.version }})
                                </option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">
                                Ukuran Batch Uji Lab (kg)
                            </label>
                            <input
                                v-model="form.batch_qty_kg"
                                type="number"
                                step="0.1"
                                min="0.1"
                                required
                                class="w-full bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl px-3.5 py-2.5 text-xs font-mono text-slate-800 dark:text-slate-100 focus:ring-cyan-500"
                            />
                        </div>

                        <div class="md:col-span-2">
                            <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">
                                Langkah Proses Preparasi Lab
                            </label>
                            <textarea
                                v-model="form.process_steps"
                                rows="3"
                                class="w-full bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl px-3 py-2 text-xs text-slate-800 dark:text-slate-100"
                            ></textarea>
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">
                                Keputusan Evaluasi Uji Lab
                            </label>
                            <select
                                v-model="form.decision"
                                class="w-full bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl px-3.5 py-2.5 text-xs font-bold text-slate-800 dark:text-slate-100 focus:ring-cyan-500"
                            >
                                <option value="lulus">LULUS (Memenuhi Kriteria Mutu)</option>
                                <option value="reformulasi">REFORMULASI (Memicu Auto-Clone V+1)</option>
                            </select>
                        </div>
                    </div>
                </div>

                <!-- Verifications Table -->
                <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 shadow-xs p-6 space-y-4">
                    <h2 class="text-xs font-bold uppercase tracking-wider text-slate-400 border-b border-slate-100 dark:border-slate-800 pb-2">
                        2. Verifikasi Parameter Fisika-Kimia & Organoleptik (Target vs Aktual)
                    </h2>

                    <div class="overflow-x-auto">
                        <table class="w-full text-left text-xs">
                            <thead class="bg-slate-50 dark:bg-slate-800/60 uppercase font-mono text-[10px] text-slate-500 dark:text-slate-400 border-b border-slate-200 dark:border-slate-800">
                                <tr>
                                    <th class="px-3 py-2.5" style="width: 25%;">Parameter Pengujian</th>
                                    <th class="px-3 py-2.5" style="width: 25%;">Target Spesifikasi</th>
                                    <th class="px-3 py-2.5" style="width: 30%;">Hasil Aktual Lab</th>
                                    <th class="px-3 py-2.5 text-center" style="width: 20%;">Kesesuaian</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                                <tr v-for="(v, idx) in form.verifications" :key="idx" class="hover:bg-slate-50/50 dark:hover:bg-slate-800/30">
                                    <td class="p-2 font-semibold text-slate-900 dark:text-white">
                                        {{ v.parameter }}
                                    </td>
                                    <td class="p-2 font-mono text-slate-600 dark:text-slate-400">
                                        {{ v.target_spec }}
                                    </td>
                                    <td class="p-2">
                                        <input
                                            v-model="v.actual_result"
                                            type="text"
                                            required
                                            class="w-full bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg px-2.5 py-1.5 text-xs text-slate-800 dark:text-slate-100 font-mono"
                                        />
                                    </td>
                                    <td class="p-2 text-center">
                                        <label class="inline-flex items-center gap-1.5 cursor-pointer">
                                            <input
                                                v-model="v.is_compliant"
                                                type="checkbox"
                                                class="rounded bg-slate-100 border-slate-300 text-cyan-600 focus:ring-cyan-500"
                                            />
                                            <span :class="v.is_compliant ? 'text-emerald-600 font-bold' : 'text-rose-500'">
                                                {{ v.is_compliant ? 'Lolos' : 'Deviasi' }}
                                            </span>
                                        </label>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Footer -->
                <div class="flex items-center justify-between pt-4 border-t border-slate-200 dark:border-slate-800">
                    <Link
                        href="/rnd/trial-rms"
                        class="text-xs text-slate-500 hover:text-slate-800 dark:hover:text-white"
                    >
                        &larr; Kembali ke daftar trial
                    </Link>

                    <button
                        type="submit"
                        :disabled="form.processing"
                        class="px-6 py-2.5 rounded-xl bg-cyan-600 hover:bg-cyan-500 text-white font-semibold text-xs flex items-center gap-2 shadow-lg shadow-cyan-600/30 transition disabled:opacity-50"
                    >
                        <Save class="w-4 h-4" />
                        <span>Simpan Catatan Uji Lab</span>
                    </button>
                </div>
            </form>
        </div>
    </ModuleLayout>
</template>
