<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';
import ModuleLayout from '@/Layouts/ModuleLayout.vue';
import { Layers, Save, ArrowLeft } from 'lucide-vue-next';

const props = defineProps({
    module: Object,
    prfs: {
        type: Array,
        default: () => [],
    },
    products: {
        type: Array,
        default: () => [],
    },
});

const form = useForm({
    prf_id: props.prfs[0]?.id || null,
    product_id: props.products[0]?.id || '',
    target_cogs: 14500,
    target_selling_price: 45000,
    development_start: '2026-12-01',
    development_end: '2027-04-30',
    notes: 'Riset stabilitas dipercepat 6 bulan dan persiapan berkas Dossier e-Registration BPOM.',
});

function submit() {
    form.post('/rnd/npd-proposals');
}
</script>

<template>
    <ModuleLayout
        :title="'Inisiasi Proposal NPD'"
        :module-slug="'rnd'"
        :module-name="'R&D (NPD Management)'"
        :color-theme="'cyan'"
        :menus="module?.menus || []"
        :breadcrumbs="['NPD Proposals', 'Inisiasi Proposal']"
    >
        <Head title="Inisiasi Proposal NPD Baru - R&D" />

        <div class="max-w-3xl mx-auto space-y-6">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <Link
                        href="/rnd/npd-proposals"
                        class="p-2 rounded-xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 text-slate-500 hover:text-slate-900 dark:hover:text-white transition"
                    >
                        <ArrowLeft class="w-4 h-4" />
                    </Link>
                    <div>
                        <h1 class="text-xl font-bold text-slate-900 dark:text-white">Proposal New Product Development (NPD)</h1>
                        <p class="text-xs text-slate-500 dark:text-slate-400">Target kelayakan finansial COGS & jadwal riset.</p>
                    </div>
                </div>
            </div>

            <form @submit.prevent="submit" class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 p-6 space-y-4">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
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

                    <div>
                        <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Referensi PRF Terkait</label>
                        <select
                            v-model="form.prf_id"
                            class="w-full bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl px-3.5 py-2.5 text-xs text-slate-800 dark:text-slate-100"
                        >
                            <option :value="null">-- Tanpa PRF (Inisiatif R&D) --</option>
                            <option v-for="prf in prfs" :key="prf.id" :value="prf.id">
                                {{ prf.code }} - {{ prf.product?.name }}
                            </option>
                        </select>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Target COGS (Rp)</label>
                        <input
                            v-model="form.target_cogs"
                            type="number"
                            required
                            class="w-full bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl px-3.5 py-2.5 text-xs font-mono text-slate-800 dark:text-slate-100"
                        />
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Target Harga Jual (Rp)</label>
                        <input
                            v-model="form.target_selling_price"
                            type="number"
                            required
                            class="w-full bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl px-3.5 py-2.5 text-xs font-mono text-slate-800 dark:text-slate-100"
                        />
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Mulai Riset</label>
                        <input
                            v-model="form.development_start"
                            type="date"
                            class="w-full bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl px-3.5 py-2.5 text-xs text-slate-800 dark:text-slate-100"
                        />
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Target Selesai Riset</label>
                        <input
                            v-model="form.development_end"
                            type="date"
                            class="w-full bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl px-3.5 py-2.5 text-xs text-slate-800 dark:text-slate-100"
                        />
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Rencana Strategis & Target Mutu</label>
                    <textarea
                        v-model="form.notes"
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
                        <span>Inisiasi Proposal NPD</span>
                    </button>
                </div>
            </form>
        </div>
    </ModuleLayout>
</template>
