<script setup>
import { ref, computed } from 'vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import ModuleLayout from '@/Layouts/ModuleLayout.vue';
import {
    Calculator,
    Plus,
    Trash2,
    Save,
    ArrowLeft,
    AlertCircle,
    CheckCircle2,
    DollarSign,
    Scale,
    Layers,
    Sparkles,
} from 'lucide-vue-next';

const props = defineProps({
    module: Object,
    finishedGoods: {
        type: Array,
        default: () => [],
    },
    rawMaterials: {
        type: Array,
        default: () => [],
    },
    vendors: {
        type: Array,
        default: () => [],
    },
    formula: Object,
    mode: {
        type: String,
        default: 'create',
    },
});

const form = useForm({
    product_id: props.formula?.product_id || (props.finishedGoods[0]?.id ?? ''),
    formula_type: props.formula?.formula_type || 'new_product',
    stage: props.formula?.stage || 'lab_trial',
    target_serving_size_g: props.formula?.target_serving_size_g || 2.0,
    serving_unit: props.formula?.serving_unit || 'sachet',
    servings_per_pack: props.formula?.servings_per_pack || 30,
    notes: props.formula?.notes || '',
    status: 'draft',
    items: props.formula?.items?.length
        ? props.formula.items.map((i) => ({
              material_product_id: i.material_product_id,
              vendor_id: i.vendor_id,
              percentage: Number(i.percentage),
              price_per_kg: Number(i.price_per_kg),
              notes: i.notes || '',
          }))
        : [
              {
                  material_product_id: props.rawMaterials[0]?.id || '',
                  vendor_id: props.vendors[0]?.id || '',
                  percentage: 60.0,
                  price_per_kg: Number(props.rawMaterials[0]?.standard_cost || 45000),
                  notes: 'Bahan Aktif Utama',
              },
              {
                  material_product_id: props.rawMaterials[1]?.id || '',
                  vendor_id: props.vendors[0]?.id || '',
                  percentage: 40.0,
                  price_per_kg: Number(props.rawMaterials[1]?.standard_cost || 18000),
                  notes: 'Eksipien Pengisi',
              },
          ],
});

function addItem() {
    form.items.push({
        material_product_id: props.rawMaterials[0]?.id || '',
        vendor_id: props.vendors[0]?.id || '',
        percentage: 0.0,
        price_per_kg: Number(props.rawMaterials[0]?.standard_cost || 20000),
        notes: '',
    });
}

function removeItem(index) {
    if (form.items.length > 1) {
        form.items.splice(index, 1);
    }
}

function handleMaterialChange(item) {
    const found = props.rawMaterials.find((m) => m.id === item.material_product_id);
    if (found && found.standard_cost) {
        item.price_per_kg = Number(found.standard_cost);
    }
}

// Live Calculations
const totalPercentage = computed(() => {
    return form.items.reduce((sum, i) => sum + (Number(i.percentage) || 0), 0);
});

const isExactly100Percent = computed(() => {
    return Math.abs(totalPercentage.value - 100.0) <= 0.001;
});

const percentageDiff = computed(() => {
    return (totalPercentage.value - 100.0).toFixed(2);
});

const totalCostPerPack = computed(() => {
    const servingSize = Number(form.target_serving_size_g) || 2.0;
    const packCount = Number(form.servings_per_pack) || 30;

    let costPerServing = 0;
    form.items.forEach((item) => {
        const pct = Number(item.percentage) || 0;
        const pricePerKg = Number(item.price_per_kg) || 0;
        const doseG = (pct / 100.0) * servingSize;
        const itemCost = (pricePerKg / 1000.0) * doseG;
        costPerServing += itemCost;
    });

    return Math.round(costPerServing * packCount);
});

function submit(statusTarget = 'draft') {
    form.status = statusTarget;
    form.post('/rnd/formulas');
}
</script>

<template>
    <ModuleLayout
        :title="'Susun Formulasi RM'"
        :module-slug="'rnd'"
        :module-name="'R&D (NPD Management)'"
        :color-theme="'cyan'"
        :menus="module?.menus || []"
        :breadcrumbs="['Formulasi Raw Material', 'Susun Resep Baru']"
    >
        <Head title="Susun Formulasi Resep (100% Presisi) - R&D" />

        <div class="max-w-5xl mx-auto space-y-6">
            <!-- Header Bar -->
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <Link
                        href="/rnd/formulas"
                        class="p-2 rounded-xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 text-slate-500 hover:text-slate-900 dark:hover:text-white transition shadow-2xs"
                    >
                        <ArrowLeft class="w-4 h-4" />
                    </Link>
                    <div>
                        <div class="flex items-center gap-2">
                            <h1 class="text-xl font-bold text-slate-900 dark:text-white tracking-tight">
                                Formulir Peracikan Resep Bahan Baku
                            </h1>
                            <span class="text-xs font-mono font-bold px-2 py-0.5 rounded bg-cyan-100 dark:bg-cyan-950 text-cyan-800 dark:text-cyan-300 border border-cyan-200 dark:border-cyan-800">
                                100.00% Presisi
                            </span>
                        </div>
                        <p class="text-xs text-slate-500 dark:text-slate-400">
                            Rasio total seluruh bahan baku wajib tepat 100.00% sesuai standar cGMP/CPOTB & BPOM.
                        </p>
                    </div>
                </div>

                <!-- Top Submit Buttons -->
                <div class="flex items-center gap-2">
                    <button
                        type="button"
                        @click="submit('draft')"
                        :disabled="form.processing || !isExactly100Percent"
                        class="px-4 py-2 rounded-xl bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200 text-xs font-semibold transition disabled:opacity-50"
                    >
                        Simpan Draft
                    </button>
                    <button
                        type="button"
                        @click="submit('pending_stage_1')"
                        :disabled="form.processing || !isExactly100Percent"
                        class="px-5 py-2 rounded-xl bg-cyan-600 hover:bg-cyan-500 text-white font-semibold text-xs flex items-center gap-2 shadow-md shadow-cyan-600/20 transition disabled:opacity-50"
                    >
                        <Save class="w-4 h-4" />
                        <span>Submit ke OM (Tahap 1)</span>
                    </button>
                </div>
            </div>

            <!-- LIVE REAL-TIME 100.00% COMPOSITION BAR -->
            <div
                :class="[
                    'rounded-2xl border p-4.5 transition duration-200 shadow-xs flex flex-col md:flex-row md:items-center justify-between gap-4',
                    isExactly100Percent
                        ? 'bg-emerald-50 dark:bg-emerald-950/40 border-emerald-300 dark:border-emerald-800 text-emerald-900 dark:text-emerald-100'
                        : 'bg-amber-50 dark:bg-amber-950/40 border-amber-300 dark:border-amber-800 text-amber-900 dark:text-amber-100',
                ]"
            >
                <div class="flex items-center gap-3">
                    <CheckCircle2 v-if="isExactly100Percent" class="w-6 h-6 text-emerald-600 dark:text-emerald-400 shrink-0" />
                    <AlertCircle v-else class="w-6 h-6 text-amber-600 dark:text-amber-400 shrink-0 animate-bounce" />

                    <div>
                        <div class="font-bold text-sm flex items-center gap-2">
                            <span>Total Komposisi Formula:</span>
                            <span class="font-mono text-base underline">{{ totalPercentage.toFixed(2) }}%</span>
                            <span v-if="isExactly100Percent" class="text-xs bg-emerald-600 text-white px-2 py-0.5 rounded font-bold uppercase">
                                Presisi Sempurna (100.00%)
                            </span>
                            <span v-else class="text-xs bg-amber-600 text-white px-2 py-0.5 rounded font-bold uppercase">
                                Deviasi: {{ percentageDiff > 0 ? `+${percentageDiff}` : percentageDiff }}%
                            </span>
                        </div>
                        <p class="text-xs opacity-90 mt-0.5">
                            {{
                                isExactly100Percent
                                    ? 'Resep memenuhi syarat validasi 100.00%. Siap disimpan atau diajukan ke Approval Gate.'
                                    : 'Perhatian: Total rasio bahan baku harus tepat 100.00% sebelum tombol simpan dapat diaktifkan.'
                            }}
                        </p>
                    </div>
                </div>

                <div class="text-right shrink-0">
                    <div class="text-[11px] uppercase font-bold text-slate-500 dark:text-slate-400">Estimasi HPP RM / Kemasan</div>
                    <div class="text-lg font-bold font-mono text-cyan-600 dark:text-cyan-400">
                        Rp {{ totalCostPerPack.toLocaleString('id-ID') }}
                    </div>
                </div>
            </div>

            <!-- Main Form Body -->
            <form @submit.prevent="submit('pending_stage_1')" class="space-y-6">
                <!-- Step 1: Target Product & Sediaan -->
                <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 shadow-xs p-6 space-y-4">
                    <div class="flex items-center justify-between border-b border-slate-100 dark:border-slate-800 pb-3">
                        <h2 class="text-xs font-bold uppercase tracking-wider text-slate-400 flex items-center gap-1.5">
                            <Layers class="w-4 h-4 text-cyan-600" />
                            <span>1. Informasi Produk & Spesifikasi Sediaan</span>
                        </h2>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
                        <div class="md:col-span-2">
                            <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">
                                Produk Target (Finished Good)
                            </label>
                            <select
                                v-model="form.product_id"
                                required
                                class="w-full bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl px-3.5 py-2.5 text-xs text-slate-800 dark:text-slate-100 focus:ring-cyan-500 focus:border-cyan-500"
                            >
                                <option v-for="p in finishedGoods" :key="p.id" :value="p.id">
                                    {{ p.name }} ({{ p.sku }}) - {{ p.category || 'Herbal' }}
                                </option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">
                                Tipe Formulasi
                            </label>
                            <select
                                v-model="form.formula_type"
                                class="w-full bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl px-3.5 py-2.5 text-xs text-slate-800 dark:text-slate-100 focus:ring-cyan-500 focus:border-cyan-500"
                            >
                                <option value="new_product">Produk Baru (New Product)</option>
                                <option value="existing">Revisi Resep Eksisting</option>
                                <option value="substitution">Substitusi Bahan / Supplier</option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">
                                Ukuran Takaran per Sediaan (gram)
                            </label>
                            <input
                                v-model="form.target_serving_size_g"
                                type="number"
                                step="0.01"
                                min="0.01"
                                required
                                class="w-full bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl px-3.5 py-2.5 text-xs font-mono text-slate-800 dark:text-slate-100 focus:ring-cyan-500 focus:border-cyan-500"
                            />
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">
                                Bentuk Satuan Sediaan
                            </label>
                            <select
                                v-model="form.serving_unit"
                                class="w-full bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl px-3.5 py-2.5 text-xs text-slate-800 dark:text-slate-100 focus:ring-cyan-500 focus:border-cyan-500"
                            >
                                <option value="sachet">Sachet</option>
                                <option value="kapsul">Kapsul</option>
                                <option value="tablet">Tablet</option>
                                <option value="botol">Botol (Sirup/Cair)</option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">
                                Jumlah Sediaan per Kemasan (Pack/Box)
                            </label>
                            <input
                                v-model="form.servings_per_pack"
                                type="number"
                                min="1"
                                required
                                class="w-full bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl px-3.5 py-2.5 text-xs font-mono text-slate-800 dark:text-slate-100 focus:ring-cyan-500 focus:border-cyan-500"
                            />
                        </div>
                    </div>
                </div>

                <!-- Step 2: Recipe Ingredients Table -->
                <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 shadow-xs p-6 space-y-4">
                    <div class="flex items-center justify-between border-b border-slate-100 dark:border-slate-800 pb-3">
                        <h2 class="text-xs font-bold uppercase tracking-wider text-slate-400 flex items-center gap-1.5">
                            <Scale class="w-4 h-4 text-cyan-600" />
                            <span>2. Komposisi Bahan Baku (Raw Material)</span>
                        </h2>

                        <button
                            type="button"
                            @click="addItem"
                            class="px-3.5 py-1.5 rounded-xl bg-cyan-600 hover:bg-cyan-500 text-white font-semibold text-xs flex items-center gap-1.5 transition shadow-xs"
                        >
                            <Plus class="w-3.5 h-3.5" />
                            <span>+ Tambah Bahan</span>
                        </button>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="w-full text-left text-xs">
                            <thead class="bg-slate-50 dark:bg-slate-800/60 uppercase font-mono text-[10px] text-slate-500 dark:text-slate-400 border-b border-slate-200 dark:border-slate-800">
                                <tr>
                                    <th class="px-3 py-2.5" style="width: 35%;">Bahan Baku (Raw Material)</th>
                                    <th class="px-3 py-2.5" style="width: 25%;">Supplier / Pemasok</th>
                                    <th class="px-3 py-2.5 text-right" style="width: 15%;">Rasio (%)</th>
                                    <th class="px-3 py-2.5 text-right" style="width: 15%;">Harga/Kg (Rp)</th>
                                    <th class="px-3 py-2.5 text-center" style="width: 10%;">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                                <tr v-for="(item, idx) in form.items" :key="idx" class="hover:bg-slate-50/50 dark:hover:bg-slate-800/30">
                                    <td class="p-2">
                                        <select
                                            v-model="item.material_product_id"
                                            @change="handleMaterialChange(item)"
                                            required
                                            class="w-full bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg px-2.5 py-1.5 text-xs text-slate-800 dark:text-slate-100"
                                        >
                                            <option v-for="rm in rawMaterials" :key="rm.id" :value="rm.id">
                                                {{ rm.name }} ({{ rm.sku }})
                                            </option>
                                        </select>
                                    </td>
                                    <td class="p-2">
                                        <select
                                            v-model="item.vendor_id"
                                            class="w-full bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg px-2.5 py-1.5 text-xs text-slate-800 dark:text-slate-100"
                                        >
                                            <option :value="null">-- Rekanan Terpilih --</option>
                                            <option v-for="v in vendors" :key="v.id" :value="v.id">
                                                {{ v.name }}
                                            </option>
                                        </select>
                                    </td>
                                    <td class="p-2">
                                        <input
                                            v-model="item.percentage"
                                            type="number"
                                            step="0.01"
                                            min="0.01"
                                            max="100"
                                            required
                                            class="w-full text-right bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg px-2.5 py-1.5 text-xs font-mono font-bold text-slate-800 dark:text-slate-100 focus:ring-cyan-500"
                                        />
                                    </td>
                                    <td class="p-2">
                                        <input
                                            v-model="item.price_per_kg"
                                            type="number"
                                            min="0"
                                            step="100"
                                            class="w-full text-right bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg px-2.5 py-1.5 text-xs font-mono text-slate-800 dark:text-slate-100"
                                        />
                                    </td>
                                    <td class="p-2 text-center">
                                        <button
                                            type="button"
                                            @click="removeItem(idx)"
                                            class="p-1 rounded text-slate-400 hover:text-rose-500 transition"
                                            title="Hapus baris bahan"
                                        >
                                            <Trash2 class="w-4 h-4" />
                                        </button>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Catatan Tambahan Formulator</label>
                        <textarea
                            v-model="form.notes"
                            rows="2"
                            placeholder="Catatan parameter preparasi, urutan mixing, atau acuan uji lab..."
                            class="w-full bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl px-3 py-2 text-xs text-slate-800 dark:text-slate-100"
                        ></textarea>
                    </div>
                </div>

                <!-- Bottom Action Row -->
                <div class="flex items-center justify-between pt-4 border-t border-slate-200 dark:border-slate-800">
                    <Link
                        href="/rnd/formulas"
                        class="text-xs text-slate-500 hover:text-slate-800 dark:hover:text-white"
                    >
                        &larr; Batal dan kembali ke daftar formula
                    </Link>

                    <div class="flex items-center gap-3">
                        <button
                            type="button"
                            @click="submit('draft')"
                            :disabled="form.processing || !isExactly100Percent"
                            class="px-4 py-2.5 rounded-xl bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200 text-xs font-semibold transition disabled:opacity-50"
                        >
                            Simpan Draft
                        </button>
                        <button
                            type="submit"
                            :disabled="form.processing || !isExactly100Percent"
                            class="px-6 py-2.5 rounded-xl bg-cyan-600 hover:bg-cyan-500 text-white font-semibold text-xs flex items-center gap-2 shadow-lg shadow-cyan-600/30 transition disabled:opacity-50"
                        >
                            <Save class="w-4 h-4" />
                            <span>Simpan & Ajukan ke OM (Tahap 1)</span>
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </ModuleLayout>
</template>
