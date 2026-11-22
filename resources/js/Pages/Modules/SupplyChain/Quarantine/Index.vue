<script setup>
import { ref } from 'vue';
import { Head, Link, useForm, router } from '@inertiajs/vue3';
import ModuleLayout from '@/Layouts/ModuleLayout.vue';
import {
    ShieldCheck,
    Search,
    CheckCircle2,
    XCircle,
    AlertTriangle,
    Eye,
    X,
    Check,
    FileText,
    Boxes,
    Filter
} from 'lucide-vue-next';

const props = defineProps({
    inspections: Object,
    filters: Object,
    stats: Object,
});

const search = ref(props.filters?.search || '');
const status = ref(props.filters?.status || '');

const handleFilter = () => {
    router.get('/supply-chain/quarantine', {
        search: search.value || undefined,
        status: status.value || undefined,
    }, { preserveState: true, replace: true });
};

// Modal Inspection State
const activeInspection = ref(null);
const isModalOpen = ref(false);

const form = useForm({
    is_visual_passed: true,
    is_lab_passed: true,
    is_quantity_passed: true,
    is_docs_passed: true,
    sample_qty: 0,
    passed_qty: 0,
    rejected_qty: 0,
    notes: '',
    coa_attachment_path: '',
});

const openInspectionModal = (insp) => {
    activeInspection.value = insp;
    const received = insp.goods_receipt_item?.received_qty || 0;
    form.is_visual_passed = insp.is_visual_passed ?? true;
    form.is_lab_passed = insp.is_lab_passed ?? true;
    form.is_quantity_passed = insp.is_quantity_passed ?? true;
    form.is_docs_passed = insp.is_docs_passed ?? true;
    form.sample_qty = insp.sample_qty || Math.max(1, (received * 0.05).toFixed(2));
    form.passed_qty = insp.passed_qty > 0 ? insp.passed_qty : received;
    form.rejected_qty = insp.rejected_qty || 0;
    form.notes = insp.notes || '';
    isModalOpen.value = true;
};

const submitInspection = () => {
    if (!activeInspection.value) return;

    form.post(`/supply-chain/quarantine/${activeInspection.value.id}/inspect`, {
        onSuccess: () => {
            isModalOpen.value = false;
            activeInspection.value = null;
        },
    });
};

const formatNumber = (val) => new Intl.NumberFormat('id-ID').format(val || 0);
</script>

<template>
    <Head title="Karantina & Inspeksi Mutu Masuk - LogiFlow SCM" />

    <ModuleLayout current-module="supply-chain">
        <div class="space-y-6">
            <!-- Header -->
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 bg-white dark:bg-slate-800 p-6 rounded-2xl shadow-sm border border-slate-200 dark:border-slate-700">
                <div class="flex items-center gap-3">
                    <div class="p-2.5 bg-amber-500/10 text-amber-600 dark:text-amber-400 rounded-xl">
                        <ShieldCheck class="w-7 h-7" />
                    </div>
                    <div>
                        <h1 class="text-2xl font-bold text-slate-900 dark:text-white tracking-tight">
                            Gerbang Karantina Mutu QC Incoming (CPOTB)
                        </h1>
                        <p class="text-sm text-slate-500 dark:text-slate-400 mt-0.5">
                            Verifikasi 4 parameter wajib mutu (Visual, Lab, Timbangan Netto, Dokumen CoA) sebelum material dialihkan ke gudang aktif.
                        </p>
                    </div>
                </div>

                <div class="flex items-center gap-3 text-xs">
                    <span class="px-3 py-1.5 bg-amber-50 dark:bg-amber-950/40 text-amber-700 dark:text-amber-400 font-semibold rounded-xl border border-amber-200 dark:border-amber-800">
                        {{ stats.total_pending }} Menunggu Uji
                    </span>
                    <span class="px-3 py-1.5 bg-emerald-50 dark:bg-emerald-950/40 text-emerald-700 dark:text-emerald-400 font-semibold rounded-xl border border-emerald-200 dark:border-emerald-800">
                        {{ stats.total_passed }} Lulus
                    </span>
                    <span class="px-3 py-1.5 bg-rose-50 dark:bg-rose-950/40 text-rose-700 dark:text-rose-400 font-semibold rounded-xl border border-rose-200 dark:border-rose-800">
                        {{ stats.total_rejected }} Ditolak
                    </span>
                </div>
            </div>

            <!-- Search and Filter Bar -->
            <div class="bg-white dark:bg-slate-800 p-4 rounded-xl border border-slate-200 dark:border-slate-700 shadow-sm flex flex-col sm:flex-row items-center gap-3">
                <div class="relative flex-1 w-full">
                    <Search class="w-4 h-4 text-slate-400 absolute left-3 top-1/2 -translate-y-1/2" />
                    <input
                        type="text"
                        v-model="search"
                        @keyup.enter="handleFilter"
                        placeholder="Cari No. Inspeksi, Batch, Nama Material, atau No. GRN..."
                        class="w-full pl-9 pr-3 py-2 text-xs rounded-lg border border-slate-200 dark:border-slate-700 dark:bg-slate-900 dark:text-white"
                    />
                </div>

                <select
                    v-model="status"
                    @change="handleFilter"
                    class="w-full sm:w-44 text-xs rounded-lg border border-slate-200 dark:border-slate-700 dark:bg-slate-900 dark:text-white py-2"
                >
                    <option value="">Semua Status</option>
                    <option value="pending">Menunggu (Pending)</option>
                    <option value="passed">Lulus (Passed)</option>
                    <option value="rejected">Ditolak (Rejected)</option>
                    <option value="partial">Lulus Sebagian</option>
                </select>

                <button
                    @click="handleFilter"
                    class="px-4 py-2 bg-slate-100 hover:bg-slate-200 dark:bg-slate-700 dark:hover:bg-slate-600 text-slate-700 dark:text-slate-200 text-xs font-semibold rounded-lg transition-colors"
                >
                    Filter
                </button>
            </div>

            <!-- Inspection Table -->
            <div class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-200 dark:border-slate-700 shadow-sm overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs">
                        <thead>
                            <tr class="bg-slate-50 dark:bg-slate-700/50 border-b border-slate-200 dark:border-slate-700 text-slate-500 font-semibold uppercase">
                                <th class="p-3.5">No. Inspeksi</th>
                                <th class="p-3.5">Ref GRN & Vendor</th>
                                <th class="p-3.5">Material & No. Batch</th>
                                <th class="p-3.5 text-right">Kuantitas Tiba</th>
                                <th class="p-3.5 text-center">4 Parameter Mutu</th>
                                <th class="p-3.5 text-center">Status</th>
                                <th class="p-3.5 text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 dark:divide-slate-700/50">
                            <tr v-for="insp in inspections.data" :key="insp.id" class="hover:bg-slate-50/70 dark:hover:bg-slate-700/30">
                                <td class="p-3.5 font-bold text-amber-600">
                                    {{ insp.inspection_number }}
                                </td>
                                <td class="p-3.5">
                                    <div class="font-medium text-slate-800 dark:text-slate-200">
                                        {{ insp.goods_receipt_item?.goods_receipt?.grn_number }}
                                    </div>
                                    <div class="text-[11px] text-slate-400">
                                        {{ insp.goods_receipt_item?.goods_receipt?.vendor?.name }}
                                    </div>
                                </td>
                                <td class="p-3.5">
                                    <div class="font-semibold text-slate-800 dark:text-slate-200">
                                        {{ insp.product?.name }}
                                    </div>
                                    <div class="text-[11px] text-slate-400">
                                        Batch: {{ insp.batch_number ?? '-' }}
                                    </div>
                                </td>
                                <td class="p-3.5 text-right font-medium text-slate-800 dark:text-slate-200">
                                    {{ formatNumber(insp.goods_receipt_item?.received_qty) }} {{ insp.goods_receipt_item?.uom }}
                                </td>
                                <td class="p-3.5 text-center">
                                    <div class="inline-flex items-center gap-1.5">
                                        <span :title="insp.is_visual_passed ? 'Fisik: Lulus' : 'Fisik: Gagal'" :class="['w-2 h-2 rounded-full', insp.is_visual_passed ? 'bg-emerald-500' : 'bg-slate-300']"></span>
                                        <span :title="insp.is_lab_passed ? 'Lab: Lulus' : 'Lab: Gagal'" :class="['w-2 h-2 rounded-full', insp.is_lab_passed ? 'bg-emerald-500' : 'bg-slate-300']"></span>
                                        <span :title="insp.is_quantity_passed ? 'Kuantitas: Lulus' : 'Kuantitas: Gagal'" :class="['w-2 h-2 rounded-full', insp.is_quantity_passed ? 'bg-emerald-500' : 'bg-slate-300']"></span>
                                        <span :title="insp.is_docs_passed ? 'Dokumen CoA: Lulus' : 'Dokumen CoA: Gagal'" :class="['w-2 h-2 rounded-full', insp.is_docs_passed ? 'bg-emerald-500' : 'bg-slate-300']"></span>
                                    </div>
                                </td>
                                <td class="p-3.5 text-center">
                                    <span
                                        :class="[
                                            'inline-flex px-2.5 py-0.5 rounded-full text-[10px] font-bold uppercase',
                                            insp.status === 'passed'
                                                ? 'bg-emerald-50 text-emerald-700 dark:bg-emerald-950/40 dark:text-emerald-400'
                                                : insp.status === 'rejected'
                                                ? 'bg-rose-50 text-rose-700 dark:bg-rose-950/40 dark:text-rose-400'
                                                : 'bg-amber-50 text-amber-700 dark:bg-amber-950/40 dark:text-amber-400'
                                        ]"
                                    >
                                        {{ insp.status }}
                                    </span>
                                </td>
                                <td class="p-3.5 text-right">
                                    <button
                                        @click="openInspectionModal(insp)"
                                        class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-amber-500 hover:bg-amber-600 text-white font-semibold text-xs rounded-lg transition-colors shadow-sm"
                                    >
                                        <ShieldCheck class="w-3.5 h-3.5" />
                                        <span>Uji Mutu</span>
                                    </button>
                                </td>
                            </tr>
                            <tr v-if="!inspections.data || inspections.data.length === 0">
                                <td colspan="7" class="p-8 text-center text-slate-400">
                                    Tidak ada antrean inspeksi karantina mutu.
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- 4-Parameter CPOTB Inspection Modal -->
        <div v-if="isModalOpen && activeInspection" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/50 backdrop-blur-sm">
            <div class="bg-white dark:bg-slate-800 rounded-2xl max-w-lg w-full p-6 shadow-xl border border-slate-200 dark:border-slate-700 space-y-4">
                <div class="flex items-center justify-between pb-3 border-b border-slate-100 dark:border-slate-700">
                    <div>
                        <h3 class="font-bold text-slate-900 dark:text-white text-base">
                            Pengujian Mutu: {{ activeInspection.inspection_number }}
                        </h3>
                        <p class="text-xs text-slate-500">
                            {{ activeInspection.product?.name }} (Batch: {{ activeInspection.batch_number }})
                        </p>
                    </div>
                    <button @click="isModalOpen = false" class="text-slate-400 hover:text-slate-600">
                        <X class="w-5 h-5" />
                    </button>
                </div>

                <form @submit.prevent="submitInspection" class="space-y-4 text-xs">
                    <!-- 4 Mandatory CPOTB Checkpoints -->
                    <div class="space-y-2 bg-slate-50 dark:bg-slate-900/50 p-4 rounded-xl border border-slate-200 dark:border-slate-700">
                        <div class="font-semibold text-slate-800 dark:text-slate-200 mb-2">
                            4 Parameter Wajib CPOTB / cGMP:
                        </div>

                        <label class="flex items-start gap-2.5 cursor-pointer">
                            <input type="checkbox" v-model="form.is_visual_passed" class="mt-0.5 rounded text-emerald-600" />
                            <div>
                                <span class="font-semibold text-slate-800 dark:text-slate-200">1. Pemeriksaan Fisik & Visual</span>
                                <p class="text-[11px] text-slate-500">Wadah bersih, segel utuh produsen, tidak bocor/pecah/terkontaminasi.</p>
                            </div>
                        </label>

                        <label class="flex items-start gap-2.5 cursor-pointer">
                            <input type="checkbox" v-model="form.is_lab_passed" class="mt-0.5 rounded text-emerald-600" />
                            <div>
                                <span class="font-semibold text-slate-800 dark:text-slate-200">2. Uji Spesifikasi Laboratorium</span>
                                <p class="text-[11px] text-slate-500">Kadar air, identifikasi zat aktif, cemaran mikroba sesuai monografi.</p>
                            </div>
                        </label>

                        <label class="flex items-start gap-2.5 cursor-pointer">
                            <input type="checkbox" v-model="form.is_quantity_passed" class="mt-0.5 rounded text-emerald-600" />
                            <div>
                                <span class="font-semibold text-slate-800 dark:text-slate-200">3. Kesesuaian Kuantitas (Timbangan Netto)</span>
                                <p class="text-[11px] text-slate-500">Berat timbangan aktual masuk dalam batas toleransi surat jalan.</p>
                            </div>
                        </label>

                        <label class="flex items-start gap-2.5 cursor-pointer">
                            <input type="checkbox" v-model="form.is_docs_passed" class="mt-0.5 rounded text-emerald-600" />
                            <div>
                                <span class="font-semibold text-slate-800 dark:text-slate-200">4. Kelengkapan Dokumen (CoA & MSDS)</span>
                                <p class="text-[11px] text-slate-500">Sertifikat Analisis (CoA) asli dari produsen terverifikasi.</p>
                            </div>
                        </label>
                    </div>

                    <!-- Quantities -->
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1">Kuantitas Lulus (Passed) *</label>
                            <input
                                type="number"
                                step="0.01"
                                v-model="form.passed_qty"
                                required
                                class="w-full rounded-xl border border-slate-200 dark:border-slate-700 px-3 py-2 text-xs dark:bg-slate-900 text-right"
                            />
                        </div>

                        <div>
                            <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1">Kuantitas Tolak (Rejected)</label>
                            <input
                                type="number"
                                step="0.01"
                                v-model="form.rejected_qty"
                                class="w-full rounded-xl border border-slate-200 dark:border-slate-700 px-3 py-2 text-xs dark:bg-slate-900 text-right text-rose-600"
                            />
                        </div>
                    </div>

                    <div>
                        <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1">Catatan Analisis QC / Justifikasi</label>
                        <textarea
                            v-model="form.notes"
                            rows="2"
                            class="w-full rounded-xl border border-slate-200 dark:border-slate-700 px-3 py-2 text-xs dark:bg-slate-900"
                            placeholder="Catatan hasil pengujian laboratorium atau rincian deviasi..."
                        ></textarea>
                    </div>

                    <div class="flex items-center justify-end gap-3 pt-3 border-t border-slate-100 dark:border-slate-700">
                        <button
                            type="button"
                            @click="isModalOpen = false"
                            class="px-4 py-2 border border-slate-200 dark:border-slate-700 text-slate-600 dark:text-slate-300 rounded-xl"
                        >
                            Batal
                        </button>
                        <button
                            type="submit"
                            :disabled="form.processing"
                            class="px-5 py-2 bg-emerald-600 hover:bg-emerald-700 text-white font-semibold rounded-xl shadow-sm"
                        >
                            {{ form.processing ? 'Menyimpan...' : 'Simpan & Rilis Stok' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </ModuleLayout>
</template>
