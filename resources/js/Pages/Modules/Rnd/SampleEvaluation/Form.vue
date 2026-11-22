<script setup>
import { computed } from 'vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import ModuleLayout from '@/Layouts/ModuleLayout.vue';
import { Award, Save, ArrowLeft } from 'lucide-vue-next';

const props = defineProps({
    module: Object,
    products: {
        type: Array,
        default: () => [],
    },
});

const form = useForm({
    product_id: props.products[0]?.id || '',
    sample_code: 'SMPL-202611-01',
    session_date: new Date().toISOString().slice(0, 10),
    batch_code: 'BATCH-LAB-01A',
    panelist_count: 20,
    average_taste: 4.5,
    average_color: 4.2,
    average_aroma: 4.6,
    average_texture: 4.3,
    average_aftertaste: 4.4,
    conclusion: 'Sampel sangat disukai panelis. Rasa jahe dan aroma herbal seimbang, siap dilanjutkan ke pengesahan formula komersial.',
});

const liveOverallScore = computed(() => {
    const t = Number(form.average_taste) || 0;
    const c = Number(form.average_color) || 0;
    const a = Number(form.average_aroma) || 0;
    const x = Number(form.average_texture) || 0;
    const f = Number(form.average_aftertaste) || 0;
    return ((t + c + a + x + f) / 5.0).toFixed(2);
});

function submit() {
    form.post('/rnd/sample-evaluations');
}
</script>

<template>
    <ModuleLayout
        :title="'Input Hasil Uji Sensori'"
        :module-slug="'rnd'"
        :module-name="'R&D (NPD Management)'"
        :color-theme="'cyan'"
        :menus="module?.menus || []"
        :breadcrumbs="['Uji Sensori', 'Catat Sesi']"
    >
        <Head title="Input Evaluasi Sensori Hedonik - R&D" />

        <div class="max-w-3xl mx-auto space-y-6">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <Link
                        href="/rnd/sample-evaluations"
                        class="p-2 rounded-xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 text-slate-500 hover:text-slate-900 dark:hover:text-white transition"
                    >
                        <ArrowLeft class="w-4 h-4" />
                    </Link>
                    <div>
                        <h1 class="text-xl font-bold text-slate-900 dark:text-white">Pencatatan Skor Uji Sensori Panelis</h1>
                        <p class="text-xs text-slate-500 dark:text-slate-400">Skor rata-rata hedonik 5 parameter uji (Skala 1.0 - 5.0).</p>
                    </div>
                </div>

                <div class="text-right">
                    <span class="text-[10px] uppercase font-bold text-slate-400">Rata-rata Skor:</span>
                    <div class="text-xl font-bold font-mono text-emerald-600 dark:text-emerald-400">
                        {{ liveOverallScore }} / 5.0
                    </div>
                </div>
            </div>

            <form @submit.prevent="submit" class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 p-6 space-y-4">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Produk</label>
                        <select
                            v-model="form.product_id"
                            required
                            class="w-full bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl px-3.5 py-2.5 text-xs text-slate-800 dark:text-slate-100"
                        >
                            <option v-for="p in products" :key="p.id" :value="p.id">{{ p.name }} ({{ p.sku }})</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Kode Sampel</label>
                        <input
                            v-model="form.sample_code"
                            type="text"
                            required
                            class="w-full bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl px-3.5 py-2.5 text-xs font-mono text-slate-800 dark:text-slate-100"
                        />
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Tanggal Sesi Uji</label>
                        <input
                            v-model="form.session_date"
                            type="date"
                            required
                            class="w-full bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl px-3.5 py-2.5 text-xs text-slate-800 dark:text-slate-100"
                        />
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Jumlah Panelis Terlibat</label>
                        <input
                            v-model="form.panelist_count"
                            type="number"
                            min="1"
                            required
                            class="w-full bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl px-3.5 py-2.5 text-xs font-mono text-slate-800 dark:text-slate-100"
                        />
                    </div>
                </div>

                <!-- 5 Hedonic Parameters -->
                <div class="border-t border-slate-100 dark:border-slate-800 pt-3 space-y-3">
                    <h3 class="text-xs font-bold uppercase tracking-wider text-slate-400">Skor 5 Parameter Organoleptik Hedonik (1.0 - 5.0)</h3>

                    <div class="grid grid-cols-2 sm:grid-cols-5 gap-3">
                        <div>
                            <label class="block text-[11px] font-semibold text-slate-700 dark:text-slate-300 mb-1">Rasa (Taste)</label>
                            <input
                                v-model="form.average_taste"
                                type="number"
                                step="0.1"
                                min="1"
                                max="5"
                                required
                                class="w-full bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg px-2.5 py-1.5 text-xs font-mono text-slate-800 dark:text-slate-100 text-center"
                            />
                        </div>

                        <div>
                            <label class="block text-[11px] font-semibold text-slate-700 dark:text-slate-300 mb-1">Warna (Color)</label>
                            <input
                                v-model="form.average_color"
                                type="number"
                                step="0.1"
                                min="1"
                                max="5"
                                required
                                class="w-full bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg px-2.5 py-1.5 text-xs font-mono text-slate-800 dark:text-slate-100 text-center"
                            />
                        </div>

                        <div>
                            <label class="block text-[11px] font-semibold text-slate-700 dark:text-slate-300 mb-1">Aroma (Odor)</label>
                            <input
                                v-model="form.average_aroma"
                                type="number"
                                step="0.1"
                                min="1"
                                max="5"
                                required
                                class="w-full bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg px-2.5 py-1.5 text-xs font-mono text-slate-800 dark:text-slate-100 text-center"
                            />
                        </div>

                        <div>
                            <label class="block text-[11px] font-semibold text-slate-700 dark:text-slate-300 mb-1">Tekstur</label>
                            <input
                                v-model="form.average_texture"
                                type="number"
                                step="0.1"
                                min="1"
                                max="5"
                                required
                                class="w-full bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg px-2.5 py-1.5 text-xs font-mono text-slate-800 dark:text-slate-100 text-center"
                            />
                        </div>

                        <div>
                            <label class="block text-[11px] font-semibold text-slate-700 dark:text-slate-300 mb-1">After Taste</label>
                            <input
                                v-model="form.average_aftertaste"
                                type="number"
                                step="0.1"
                                min="1"
                                max="5"
                                required
                                class="w-full bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg px-2.5 py-1.5 text-xs font-mono text-slate-800 dark:text-slate-100 text-center"
                            />
                        </div>
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Kesimpulan & Rekomendasi Panelis</label>
                    <textarea
                        v-model="form.conclusion"
                        rows="3"
                        class="w-full bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl px-3.5 py-2.5 text-xs text-slate-800 dark:text-slate-100"
                    ></textarea>
                </div>

                <div class="pt-4 flex justify-end">
                    <button
                        type="submit"
                        :disabled="form.processing"
                        class="px-5 py-2.5 rounded-xl bg-cyan-600 hover:bg-cyan-500 text-white font-semibold text-xs flex items-center gap-2 shadow-md"
                    >
                        <Save class="w-4 h-4" />
                        <span>Simpan Hasil Evaluasi</span>
                    </button>
                </div>
            </form>
        </div>
    </ModuleLayout>
</template>
