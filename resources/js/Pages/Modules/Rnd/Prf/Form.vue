<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';
import ModuleLayout from '@/Layouts/ModuleLayout.vue';
import { FileText, Save, ArrowLeft } from 'lucide-vue-next';

const props = defineProps({
    module: Object,
    products: {
        type: Array,
        default: () => [],
    },
});

const form = useForm({
    product_id: props.products[0]?.id || '',
    target_market: 'Dewasa & Lansia (Suplemen Imunitas)',
    product_category: 'Herbal Obat Tradisional',
    concept: 'Produk cair sachet ekstrak meniran dan pegagan untuk daya tahan tubuh harian.',
    target_launch_date: '2027-03-31',
});

function submit() {
    form.post('/rnd/prfs');
}
</script>

<template>
    <ModuleLayout
        :title="'Buat PRF Baru'"
        :module-slug="'rnd'"
        :module-name="'R&D (NPD Management)'"
        :color-theme="'cyan'"
        :menus="module?.menus || []"
        :breadcrumbs="['PRF', 'Buat Form']"
    >
        <Head title="Buat Product Request Form (PRF) - R&D" />

        <div class="max-w-3xl mx-auto space-y-6">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <Link
                        href="/rnd/prfs"
                        class="p-2 rounded-xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 text-slate-500 hover:text-slate-900 dark:hover:text-white transition"
                    >
                        <ArrowLeft class="w-4 h-4" />
                    </Link>
                    <div>
                        <h1 class="text-xl font-bold text-slate-900 dark:text-white">Formulir Permintaan Produk Baru (PRF)</h1>
                        <p class="text-xs text-slate-500 dark:text-slate-400">Inisiasi brief dari tim Bisnis / Pemasaran ke R&D.</p>
                    </div>
                </div>
            </div>

            <form @submit.prevent="submit" class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 p-6 space-y-4">
                <div>
                    <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Target Produk</label>
                    <select
                        v-model="form.product_id"
                        required
                        class="w-full bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl px-3.5 py-2.5 text-xs text-slate-800 dark:text-slate-100"
                    >
                        <option v-for="p in products" :key="p.id" :value="p.id">{{ p.name }} ({{ p.sku }})</option>
                    </select>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Target Pasar</label>
                        <input
                            v-model="form.target_market"
                            type="text"
                            required
                            class="w-full bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl px-3.5 py-2.5 text-xs text-slate-800 dark:text-slate-100"
                        />
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Target Tanggal Peluncuran</label>
                        <input
                            v-model="form.target_launch_date"
                            type="date"
                            class="w-full bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl px-3.5 py-2.5 text-xs text-slate-800 dark:text-slate-100"
                        />
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Konsep & Target Manfaat Produk</label>
                    <textarea
                        v-model="form.concept"
                        rows="4"
                        required
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
                        <span>Kirim PRF ke R&D</span>
                    </button>
                </div>
            </form>
        </div>
    </ModuleLayout>
</template>
