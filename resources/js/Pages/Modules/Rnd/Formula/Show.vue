<script setup>
import { ref } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import ModuleLayout from '@/Layouts/ModuleLayout.vue';
import {
    Calculator,
    ArrowLeft,
    FileText,
    RotateCcw,
    CheckCircle2,
    CheckCheck,
    Layers,
    Clock,
    Lock,
    Send,
    AlertCircle,
    Building2,
    Calendar,
    DollarSign,
    Scale,
} from 'lucide-vue-next';

const props = defineProps({
    module: Object,
    formula: Object,
});

const isSubmitting = ref(false);

function submitForApproval() {
    if (confirm(`Ajukan formula ${props.formula.code} ke Operational Manager (Tahap 1)?`)) {
        router.post(`/rnd/formulas/${props.formula.id}/submit`);
    }
}

function triggerReformulation() {
    if (
        confirm(
            `Inisiasi Auto-Clone Reformulasi?\nSistem akan menduplikasi komposisi formula ${props.formula.code} menjadi versi baru (V${props.formula.version + 1}) sebagai draf kerja baru.`
        )
    ) {
        router.post(`/rnd/formulas/${props.formula.id}/reformula`);
    }
}

function promoteToBom() {
    if (confirm('Promosikan formula ini ke Master BOM resmi? BOM akan langsung aktif di SCM (MRP) dan Produksi.')) {
        router.post(`/rnd/formulas/${props.formula.id}/promote-to-bom`);
    }
}
</script>

<template>
    <ModuleLayout
        :title="`${formula.code} - Formulasi RM`"
        :module-slug="'rnd'"
        :module-name="'R&D (NPD Management)'"
        :color-theme="'cyan'"
        :menus="module?.menus || []"
        :breadcrumbs="['Formulasi Raw Material', formula.code]"
    >
        <Head :title="`${formula.code} - ${formula.product?.name}`" />

        <div class="max-w-5xl mx-auto space-y-6">
            <!-- Header Bar -->
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
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
                                {{ formula.code }}
                            </h1>
                            <span class="text-xs font-mono font-bold px-2 py-0.5 rounded bg-cyan-100 dark:bg-cyan-950 text-cyan-800 dark:text-cyan-300 border border-cyan-200 dark:border-cyan-800">
                                Versi {{ formula.version }}
                            </span>
                            <span
                                :class="[
                                    'px-2 py-0.5 rounded text-[10px] font-bold uppercase tracking-wider border',
                                    formula.status === 'approved'
                                        ? 'bg-emerald-50 dark:bg-emerald-950 text-emerald-700 dark:text-emerald-300 border-emerald-200 dark:border-emerald-800'
                                        : formula.status.startsWith('pending')
                                        ? 'bg-amber-50 dark:bg-amber-950 text-amber-700 dark:text-amber-300 border-amber-200 dark:border-amber-800'
                                        : 'bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 border-slate-200 dark:border-slate-700',
                                ]"
                            >
                                {{ formula.status }}
                            </span>
                        </div>
                        <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">
                            Produk: <strong class="text-slate-800 dark:text-slate-200">{{ formula.product?.name }}</strong> ({{ formula.product?.sku }})
                        </p>
                    </div>
                </div>

                <!-- Action Controls -->
                <div class="flex flex-wrap items-center gap-2">
                    <!-- Export PDF -->
                    <a
                        :href="`/rnd/formulas/${formula.id}/export/pdf`"
                        target="_blank"
                        class="px-3.5 py-2 rounded-xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 hover:bg-slate-50 dark:hover:bg-slate-800 text-slate-700 dark:text-slate-300 font-semibold text-xs flex items-center gap-1.5 transition shadow-2xs"
                    >
                        <FileText class="w-4 h-4 text-rose-500" />
                        <span>Cetak PDF</span>
                    </a>

                    <!-- Submit if Draft -->
                    <button
                        v-if="formula.status === 'draft' || formula.status === 'rejected'"
                        @click="submitForApproval"
                        class="px-4 py-2 rounded-xl bg-cyan-600 hover:bg-cyan-500 text-white font-semibold text-xs flex items-center gap-1.5 shadow-md shadow-cyan-600/20 transition"
                    >
                        <Send class="w-4 h-4" />
                        <span>Ajukan Approval (Tahap 1)</span>
                    </button>

                    <!-- Auto-Clone Reformulation -->
                    <button
                        v-if="formula.status === 'approved' || formula.status === 'rejected'"
                        @click="triggerReformulation"
                        class="px-4 py-2 rounded-xl bg-amber-600 hover:bg-amber-500 text-white font-semibold text-xs flex items-center gap-1.5 shadow-md shadow-amber-600/20 transition"
                        title="Duplikasi resep ke versi berikutnya (Auto-Clone)"
                    >
                        <RotateCcw class="w-4 h-4" />
                        <span>Reformulasi (Auto-Clone)</span>
                    </button>

                    <!-- Promote to Master BOM Action -->
                    <button
                        v-if="formula.status === 'approved' && !formula.custom_attributes?.promoted_to_bom_id"
                        @click="promoteToBom"
                        class="px-4 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-white font-semibold text-xs flex items-center gap-1.5 shadow-md shadow-emerald-600/20 transition"
                        title="Promote formula ke Core Master BOM untuk SCM MRP & Produksi"
                    >
                        <Layers class="w-4 h-4" />
                        <span>Promote to Master BOM</span>
                    </button>
                    <span
                        v-else-if="formula.custom_attributes?.promoted_to_bom_id"
                        class="px-3 py-1.5 rounded-xl bg-emerald-100 dark:bg-emerald-950 text-emerald-800 dark:text-emerald-300 font-mono text-xs font-semibold flex items-center gap-1 border border-emerald-300 dark:border-emerald-800"
                    >
                        <CheckCheck class="w-3.5 h-3.5" />
                        <span>BOM: {{ formula.custom_attributes?.promoted_bom_number }}</span>
                    </span>
                </div>
            </div>

            <!-- Immutability Alert Banner when Approved -->
            <div
                v-if="formula.status === 'approved'"
                class="rounded-2xl border border-emerald-300 dark:border-emerald-800 bg-emerald-50 dark:bg-emerald-950/40 p-4 text-emerald-900 dark:text-emerald-200 flex items-center gap-3 text-xs"
            >
                <Lock class="w-5 h-5 text-emerald-600 dark:text-emerald-400 shrink-0" />
                <div>
                    <strong class="font-bold">Dokumen Terkunci Permanen (Immutability Enforced):</strong>
                    Formula ini telah disahkan secara legal oleh General Manager dan tidak dapat diedit atau dihapus. Untuk melakukan penyesuaian komposisi, gunakan tombol <strong>"Reformulasi (Auto-Clone)"</strong> di kanan atas untuk membuat Versi {{ formula.version + 1 }}.
                </div>
            </div>

            <!-- Specification & Parameter Summary -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <div class="bg-white dark:bg-slate-900 rounded-xl border border-slate-200 dark:border-slate-800 p-4">
                    <div class="text-[10px] uppercase font-bold text-slate-400">Takaran per Sediaan</div>
                    <div class="text-base font-bold font-mono text-slate-900 dark:text-white mt-1">
                        {{ formula.target_serving_size_g }} gram
                    </div>
                    <div class="text-[11px] text-slate-500 mt-0.5">Bentuk: {{ formula.serving_unit }}</div>
                </div>

                <div class="bg-white dark:bg-slate-900 rounded-xl border border-slate-200 dark:border-slate-800 p-4">
                    <div class="text-[10px] uppercase font-bold text-slate-400">Kemasan Selesai (Pack)</div>
                    <div class="text-base font-bold font-mono text-slate-900 dark:text-white mt-1">
                        {{ formula.servings_per_pack }} {{ formula.serving_unit }}
                    </div>
                    <div class="text-[11px] text-slate-500 mt-0.5">per kemasan komersial</div>
                </div>

                <div class="bg-white dark:bg-slate-900 rounded-xl border border-slate-200 dark:border-slate-800 p-4">
                    <div class="text-[10px] uppercase font-bold text-emerald-600">Total Komposisi</div>
                    <div class="text-base font-bold font-mono text-emerald-600 dark:text-emerald-400 mt-1">
                        {{ Number(formula.total_percentage).toFixed(2) }}%
                    </div>
                    <div class="text-[11px] text-emerald-600 mt-0.5">Presisi 100.00% Valid</div>
                </div>

                <div class="bg-white dark:bg-slate-900 rounded-xl border border-slate-200 dark:border-slate-800 p-4">
                    <div class="text-[10px] uppercase font-bold text-cyan-600">HPP Raw Material / Pack</div>
                    <div class="text-base font-bold font-mono text-cyan-600 dark:text-cyan-400 mt-1">
                        Rp {{ Number(formula.total_cost_per_pack).toLocaleString('id-ID') }}
                    </div>
                    <div class="text-[11px] text-slate-500 mt-0.5">Total per Kemasan Produk</div>
                </div>
            </div>

            <!-- Ingredients Table -->
            <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 shadow-xs overflow-hidden">
                <div class="px-5 py-4 border-b border-slate-200 dark:border-slate-800 flex items-center justify-between">
                    <h3 class="text-xs font-bold uppercase tracking-wider text-slate-900 dark:text-white">
                        Susunan Komposisi Bahan Baku ({{ formula.items?.length || 0 }} Komponen)
                    </h3>
                    <span class="text-xs font-mono text-slate-400">Total Komposisi Wajib 100.00%</span>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs text-slate-700 dark:text-slate-300">
                        <thead class="bg-slate-50 dark:bg-slate-800/60 uppercase font-mono text-[10px] text-slate-500 dark:text-slate-400 border-b border-slate-200 dark:border-slate-800">
                            <tr>
                                <th class="px-4 py-3">No</th>
                                <th class="px-4 py-3">Nama Bahan Baku</th>
                                <th class="px-4 py-3">Supplier Rekanan</th>
                                <th class="px-4 py-3 text-right">Rasio (%)</th>
                                <th class="px-4 py-3 text-right">Dosis (g)</th>
                                <th class="px-4 py-3 text-right">Harga/Kg</th>
                                <th class="px-4 py-3 text-right">Biaya/Sediaan</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                            <tr v-for="(item, idx) in formula.items" :key="item.id" class="hover:bg-slate-50/50 dark:hover:bg-slate-800/40">
                                <td class="px-4 py-3 text-slate-400 font-mono">{{ idx + 1 }}</td>
                                <td class="px-4 py-3 font-semibold text-slate-900 dark:text-white">
                                    {{ item.material?.name }}
                                    <div class="text-[10px] text-slate-400 font-mono font-normal">{{ item.material?.sku }}</div>
                                </td>
                                <td class="px-4 py-3 text-slate-600 dark:text-slate-400">
                                    {{ item.vendor?.name || 'Acuan Katalog R&D' }}
                                </td>
                                <td class="px-4 py-3 text-right font-mono font-bold text-slate-900 dark:text-white">
                                    {{ Number(item.percentage).toFixed(2) }}%
                                </td>
                                <td class="px-4 py-3 text-right font-mono">
                                    {{ Number(item.weight_per_serving_g).toFixed(4) }} g
                                </td>
                                <td class="px-4 py-3 text-right font-mono">
                                    Rp {{ Number(item.price_per_kg).toLocaleString('id-ID') }}
                                </td>
                                <td class="px-4 py-3 text-right font-mono font-semibold text-cyan-600 dark:text-cyan-400">
                                    Rp {{ Number(item.price_per_serving).toLocaleString('id-ID') }}
                                </td>
                            </tr>
                        </tbody>
                        <tfoot class="bg-slate-50 dark:bg-slate-800/80 font-bold border-t border-slate-200 dark:border-slate-800">
                            <tr>
                                <td colspan="3" class="px-4 py-3 text-right">TOTAL:</td>
                                <td class="px-4 py-3 text-right font-mono text-emerald-600 dark:text-emerald-400">
                                    {{ Number(formula.total_percentage).toFixed(2) }}%
                                </td>
                                <td class="px-4 py-3 text-right font-mono">
                                    {{ Number(formula.target_serving_size_g).toFixed(2) }} g
                                </td>
                                <td class="px-4 py-3 text-right font-mono">
                                    Rp {{ Number(formula.total_cost_per_kg).toLocaleString('id-ID') }}/kg
                                </td>
                                <td class="px-4 py-3 text-right font-mono text-cyan-600 dark:text-cyan-400">
                                    Rp {{ Number(formula.total_cost_per_pack / (formula.servings_per_pack || 1)).toLocaleString('id-ID') }}
                                </td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>

            <!-- Approval Signatures Gate -->
            <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 p-6 space-y-4">
                <h3 class="text-xs font-bold uppercase tracking-wider text-slate-400">
                    Matriks Pengesahan Berjenjang (Approval Gate)
                </h3>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-4 text-xs">
                    <!-- Disusun Oleh -->
                    <div class="border border-slate-200 dark:border-slate-800 rounded-xl p-4 text-center space-y-2">
                        <span class="text-[11px] text-slate-400 font-bold uppercase">Disusun Oleh (Formulator)</span>
                        <div class="h-12 flex items-center justify-center font-bold text-cyan-600">
                            DIGITALLY SIGNED
                        </div>
                        <div class="font-bold text-slate-800 dark:text-slate-200">{{ formula.creator?.name || 'Staff Formulator' }}</div>
                        <div class="text-[10px] text-slate-400">{{ new Date(formula.created_at).toLocaleDateString('id-ID') }}</div>
                    </div>

                    <!-- Diperiksa OM -->
                    <div class="border border-slate-200 dark:border-slate-800 rounded-xl p-4 text-center space-y-2">
                        <span class="text-[11px] text-slate-400 font-bold uppercase">Tahap 1: Operational Manager</span>
                        <div class="h-12 flex items-center justify-center font-bold">
                            <span v-if="formula.approved_by_om" class="text-emerald-600 flex items-center gap-1">
                                <CheckCircle2 class="w-4 h-4" /> APPROVED TAHAP 1
                            </span>
                            <span v-else class="text-slate-400 flex items-center gap-1">
                                <Clock class="w-4 h-4" /> Pending Review
                            </span>
                        </div>
                        <div class="font-bold text-slate-800 dark:text-slate-200">{{ formula.om_approver?.name || 'Operational Manager' }}</div>
                        <div class="text-[10px] text-slate-400">Verifikasi Kesesuaian Dosis</div>
                    </div>

                    <!-- Disahkan GM -->
                    <div class="border border-slate-200 dark:border-slate-800 rounded-xl p-4 text-center space-y-2">
                        <span class="text-[11px] text-slate-400 font-bold uppercase">Tahap 2: General Manager (Final)</span>
                        <div class="h-12 flex items-center justify-center font-bold">
                            <span v-if="formula.approved_by_gm" class="text-emerald-600 flex items-center gap-1">
                                <Lock class="w-4 h-4" /> APPROVED & LOCKED
                            </span>
                            <span v-else class="text-slate-400 flex items-center gap-1">
                                <Clock class="w-4 h-4" /> Pending GM
                            </span>
                        </div>
                        <div class="font-bold text-slate-800 dark:text-slate-200">{{ formula.gm_approver?.name || 'General Manager' }}</div>
                        <div class="text-[10px] text-slate-400">Pengesahan Komersial Final</div>
                    </div>
                </div>
            </div>
        </div>
    </ModuleLayout>
</template>
