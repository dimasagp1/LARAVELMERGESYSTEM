<script setup>
import { ref } from 'vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import ModuleLayout from '@/Layouts/ModuleLayout.vue';
import {
    Boxes,
    Save,
    ArrowLeft,
    Wrench,
    CheckCircle2,
} from 'lucide-vue-next';

const props = defineProps({
    module: Object,
    products: {
        type: Array,
        default: () => [],
    },
    vendors: {
        type: Array,
        default: () => [],
    },
});

const form = useForm({
    product_id: props.products[0]?.id || '',
    vendor_id: props.vendors[0]?.id || '',
    packaging_specs_json: {
        material: 'Aluminium Foil Laminated',
        thickness_micron: 65,
        dimension_mm: '80 x 120 mm',
        grammage_gsm: 85,
    },
    machine_params_json: {
        speed_cpm: 45,
        temp_vertical_c: 145,
        temp_horizontal_c: 150,
        pressure_bar: 3.5,
    },
    leakage_test_result: 'Lolos (Tidak ada kebocoran dye test)',
    seal_strength_result: 'Kuat (Tidak sobek pada tarikan 15N)',
    barcode_readability: 'Grade A (ISO/IEC 15416)',
});

function submit() {
    form.post('/rnd/trial-pms');
}
</script>

<template>
    <ModuleLayout
        :title="'Catat Trial PM Baru'"
        :module-slug="'rnd'"
        :module-name="'R&D (NPD Management)'"
        :color-theme="'cyan'"
        :menus="module?.menus || []"
        :breadcrumbs="['Trial Packaging Material', 'Input Uji Kemasan Mesin']"
    >
        <Head title="Input Trial Packaging Material Mesin - R&D" />

        <div class="max-w-4xl mx-auto space-y-6">
            <!-- Header -->
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <Link
                        href="/rnd/trial-pms"
                        class="p-2 rounded-xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 text-slate-500 hover:text-slate-900 dark:hover:text-white transition shadow-2xs"
                    >
                        <ArrowLeft class="w-4 h-4" />
                    </Link>
                    <div>
                        <h1 class="text-xl font-bold text-slate-900 dark:text-white tracking-tight">
                            Pencatatan Uji Coba Kemasan pada Mesin (Trial PM)
                        </h1>
                        <p class="text-xs text-slate-500 dark:text-slate-400">
                            Evaluasi performa sachet/kemasan pada mesin pengemas otomatis pabrik.
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
                    <span>Simpan & Buka Matriks 4-Dept</span>
                </button>
            </div>

            <!-- Form Body -->
            <form @submit.prevent="submit" class="space-y-6">
                <!-- Product & Vendor -->
                <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 shadow-xs p-6 space-y-4">
                    <h2 class="text-xs font-bold uppercase tracking-wider text-slate-400 border-b border-slate-100 dark:border-slate-800 pb-2">
                        1. Informasi Produk & Vendor Kemasan
                    </h2>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">
                                Produk Jadi Target
                            </label>
                            <select
                                v-model="form.product_id"
                                required
                                class="w-full bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl px-3.5 py-2.5 text-xs text-slate-800 dark:text-slate-100 focus:ring-cyan-500"
                            >
                                <option v-for="p in products" :key="p.id" :value="p.id">
                                    {{ p.name }} ({{ p.sku }})
                                </option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">
                                Supplier / Vendor Kemasan
                            </label>
                            <select
                                v-model="form.vendor_id"
                                class="w-full bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl px-3.5 py-2.5 text-xs text-slate-800 dark:text-slate-100 focus:ring-cyan-500"
                            >
                                <option v-for="v in vendors" :key="v.id" :value="v.id">
                                    {{ v.name }} ({{ v.code }})
                                </option>
                            </select>
                        </div>
                    </div>
                </div>

                <!-- Specs & Machine Parameters -->
                <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 shadow-xs p-6 space-y-4">
                    <h2 class="text-xs font-bold uppercase tracking-wider text-slate-400 border-b border-slate-100 dark:border-slate-800 pb-2">
                        2. Spesifikasi Material & Setting Parameter Mesin
                    </h2>

                    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Ketebalan (Mikron)</label>
                            <input
                                v-model="form.packaging_specs_json.thickness_micron"
                                type="number"
                                class="w-full bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl px-3 py-2 text-xs font-mono text-slate-800 dark:text-slate-100"
                            />
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Dimensi (P x L mm)</label>
                            <input
                                v-model="form.packaging_specs_json.dimension_mm"
                                type="text"
                                class="w-full bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl px-3 py-2 text-xs font-mono text-slate-800 dark:text-slate-100"
                            />
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Kecepatan Mesin (CPM)</label>
                            <input
                                v-model="form.machine_params_json.speed_cpm"
                                type="number"
                                class="w-full bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl px-3 py-2 text-xs font-mono text-slate-800 dark:text-slate-100"
                            />
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Suhu Sealing (°C)</label>
                            <input
                                v-model="form.machine_params_json.temp_horizontal_c"
                                type="number"
                                class="w-full bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl px-3 py-2 text-xs font-mono text-slate-800 dark:text-slate-100"
                            />
                        </div>
                    </div>
                </div>

                <!-- Test Results -->
                <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 shadow-xs p-6 space-y-4">
                    <h2 class="text-xs font-bold uppercase tracking-wider text-slate-400 border-b border-slate-100 dark:border-slate-800 pb-2">
                        3. Hasil Uji Kebocoran, Rekat & Barcode
                    </h2>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Uji Kebocoran (Leakage Dye Test)</label>
                            <input
                                v-model="form.leakage_test_result"
                                type="text"
                                required
                                class="w-full bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl px-3 py-2 text-xs text-slate-800 dark:text-slate-100"
                            />
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Kekuatan Rekat (Seal Strength)</label>
                            <input
                                v-model="form.seal_strength_result"
                                type="text"
                                required
                                class="w-full bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl px-3 py-2 text-xs text-slate-800 dark:text-slate-100"
                            />
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Keterbacaan Barcode (Readability)</label>
                            <input
                                v-model="form.barcode_readability"
                                type="text"
                                required
                                class="w-full bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl px-3 py-2 text-xs text-slate-800 dark:text-slate-100"
                            />
                        </div>
                    </div>
                </div>

                <!-- Footer -->
                <div class="flex items-center justify-between pt-4 border-t border-slate-200 dark:border-slate-800">
                    <Link
                        href="/rnd/trial-pms"
                        class="text-xs text-slate-500 hover:text-slate-800 dark:hover:text-white"
                    >
                        &larr; Kembali ke daftar trial PM
                    </Link>

                    <button
                        type="submit"
                        :disabled="form.processing"
                        class="px-6 py-2.5 rounded-xl bg-cyan-600 hover:bg-cyan-500 text-white font-semibold text-xs flex items-center gap-2 shadow-lg shadow-cyan-600/30 transition disabled:opacity-50"
                    >
                        <Save class="w-4 h-4" />
                        <span>Simpan & Buka Matriks 4-Dept</span>
                    </button>
                </div>
            </form>
        </div>
    </ModuleLayout>
</template>
