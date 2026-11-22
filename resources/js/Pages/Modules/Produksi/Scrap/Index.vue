<script setup>
import { ref } from 'vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import ModuleLayout from '@/Layouts/ModuleLayout.vue';
import {
    AlertTriangle,
    Plus,
    Filter,
    FileDown,
    Trash2,
    DollarSign,
    Boxes,
    Save,
    X,
    CheckCircle2,
} from 'lucide-vue-next';

const props = defineProps({
    rejects: {
        type: Object,
        required: true,
    },
    analytics: {
        type: Object,
        default: () => ({ total_items_scrapped: 0, total_loss_rupiah: 0, defects_breakdown: [] }),
    },
    activeReports: {
        type: Array,
        default: () => [],
    },
    filters: {
        type: Object,
        default: () => ({}),
    },
});

const showModal = ref(false);

const form = useForm({
    laporan_harian_id: props.activeReports[0]?.id || '',
    jenis_reject: 'Segel Induksi Cacat',
    material_name: 'Alufoil Induction Seal 38mm',
    jumlah: 10,
    satuan: 'PCS',
    biaya_satuan: 450,
    keterangan: '',
});

const submitReject = () => {
    form.post('/production/scrap', {
        onSuccess: () => {
            showModal.value = false;
            form.reset();
        },
    });
};

const formatRupiah = (val) => {
    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        maximumFractionDigits: 0,
    }).format(val || 0);
};
</script>

<template>
    <Head title="Manajemen Reject & Scrap Produk - Produksi" />

    <ModuleLayout module-slug="production">
        <div class="space-y-6">
            <!-- Header Bar -->
            <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h1 class="text-xl font-bold text-slate-900 dark:text-white sm:text-2xl">
                        Manajemen Reject & Scrap Produk
                    </h1>
                    <p class="text-xs text-slate-500">
                        Pencatatan produk cacat, kalkulasi kerugian nominal material, dan sinkronisasi Odoo stock.scrap.
                    </p>
                </div>

                <button
                    type="button"
                    @click="showModal = true"
                    class="inline-flex items-center gap-1.5 rounded-lg bg-rose-600 px-3.5 py-2 text-xs font-semibold text-white shadow-sm hover:bg-rose-700"
                >
                    <Plus class="h-4 w-4" /> Input Reject Baru
                </button>
            </div>

            <!-- Summary KPI Strip -->
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
                <div class="rounded-xl border border-slate-200 bg-white p-4 shadow-sm dark:border-slate-800 dark:bg-slate-900">
                    <span class="text-xs font-bold text-slate-500 uppercase">Total Unit Reject</span>
                    <div class="mt-1 text-2xl font-bold text-rose-600 dark:text-rose-400">
                        {{ Number(analytics.total_items_scrapped).toLocaleString('id-ID') }} Unit
                    </div>
                    <span class="text-[11px] text-slate-400">Akumulasi seluruh batch</span>
                </div>

                <div class="rounded-xl border border-rose-200 bg-rose-50/50 p-4 shadow-sm dark:border-rose-900 dark:bg-rose-950/20">
                    <span class="text-xs font-bold text-rose-700 dark:text-rose-400 uppercase">Estimasi Kerugian Scrap</span>
                    <div class="mt-1 text-2xl font-bold text-rose-700 dark:text-rose-300">
                        {{ formatRupiah(analytics.total_loss_rupiah) }}
                    </div>
                    <span class="text-[11px] text-rose-500">Beban biaya limbah material</span>
                </div>

                <div class="rounded-xl border border-slate-200 bg-white p-4 shadow-sm dark:border-slate-800 dark:bg-slate-900">
                    <span class="text-xs font-bold text-slate-500 uppercase">Kategori Cacat Terbanyak</span>
                    <div class="mt-1 text-base font-bold text-slate-900 dark:text-white truncate">
                        {{ analytics.defects_breakdown[0]?.jenis_reject || 'Segel Induksi Cacat' }}
                    </div>
                    <span class="text-[11px] text-slate-400">
                        {{ analytics.defects_breakdown[0]?.total_qty || 0 }} unit terbuang
                    </span>
                </div>
            </div>

            <!-- Reject Table -->
            <div class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm dark:border-slate-800 dark:bg-slate-900">
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm">
                        <thead class="border-b border-slate-200 bg-slate-50 text-xs font-semibold uppercase text-slate-600 dark:border-slate-800 dark:bg-slate-800/50 dark:text-slate-400">
                            <tr>
                                <th class="py-3 px-4">No. LHP / Batch</th>
                                <th class="py-3 px-4">Produk</th>
                                <th class="py-3 px-4">Jenis Cacat (Defect)</th>
                                <th class="py-3 px-4">Material Terkait</th>
                                <th class="py-3 px-4 text-right">Jumlah</th>
                                <th class="py-3 px-4 text-right">Nilai Kerugian</th>
                                <th class="py-3 px-4 text-center">Ref Odoo Scrap</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                            <tr v-for="rj in rejects.data" :key="rj.id" class="hover:bg-slate-50 dark:hover:bg-slate-800/40">
                                <td class="py-3 px-4">
                                    <div class="font-mono font-bold text-rose-600 dark:text-rose-400">{{ rj.laporan_harian?.report_number }}</div>
                                    <div class="text-xs font-mono text-slate-500 font-bold">Batch: {{ rj.laporan_harian?.batch_number }}</div>
                                </td>
                                <td class="py-3 px-4">
                                    <div class="font-medium text-slate-900 dark:text-white">{{ rj.laporan_harian?.product?.name }}</div>
                                </td>
                                <td class="py-3 px-4 font-semibold text-rose-600 dark:text-rose-400">
                                    {{ rj.jenis_reject }}
                                </td>
                                <td class="py-3 px-4 text-slate-600 dark:text-slate-400">
                                    {{ rj.material_name || '-' }}
                                </td>
                                <td class="py-3 px-4 text-right font-bold text-slate-900 dark:text-white">
                                    {{ Number(rj.jumlah).toLocaleString('id-ID') }} {{ rj.satuan }}
                                </td>
                                <td class="py-3 px-4 text-right font-semibold text-rose-600 dark:text-rose-400">
                                    {{ formatRupiah(rj.total_nilai_scrap) }}
                                </td>
                                <td class="py-3 px-4 text-center">
                                    <span v-if="rj.odoo_scrap_id" class="inline-flex items-center gap-1 rounded bg-emerald-100 px-2 py-0.5 text-xs font-mono font-bold text-emerald-800 dark:bg-emerald-950/40 dark:text-emerald-400">
                                        <CheckCircle2 class="h-3 w-3" /> #{{ rj.odoo_scrap_id }}
                                    </span>
                                    <span v-else class="text-xs text-slate-400">-</span>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Input Reject Modal -->
        <div v-if="showModal" class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/60 p-4 backdrop-blur-sm">
            <div class="w-full max-w-md rounded-2xl bg-white p-6 shadow-2xl dark:bg-slate-900">
                <div class="flex items-center justify-between border-b border-slate-100 pb-3 dark:border-slate-800">
                    <h3 class="text-base font-bold text-slate-900 dark:text-white">Input Reject / Scrap Produk</h3>
                    <button type="button" @click="showModal = false" class="text-slate-400 hover:text-slate-600">
                        <X class="h-5 w-5" />
                    </button>
                </div>

                <form @submit.prevent="submitReject" class="mt-4 space-y-3">
                    <div>
                        <label class="text-xs font-semibold text-slate-700 dark:text-slate-300">Pilih Batch Laporan Harian *</label>
                        <select
                            v-model="form.laporan_harian_id"
                            class="mt-1 w-full rounded-lg border border-slate-200 p-2.5 text-xs text-slate-900 dark:border-slate-700 dark:bg-slate-800 dark:text-white"
                            required
                        >
                            <option v-for="rep in activeReports" :key="rep.id" :value="rep.id">
                                {{ rep.report_number }} - Batch {{ rep.batch_number }} ({{ rep.product?.name }})
                            </option>
                        </select>
                    </div>

                    <div>
                        <label class="text-xs font-semibold text-slate-700 dark:text-slate-300">Jenis Cacat / Defect *</label>
                        <input
                            v-model="form.jenis_reject"
                            type="text"
                            placeholder="Contoh: Segel Bocor, Volume Kurang..."
                            class="mt-1 w-full rounded-lg border border-slate-200 p-2.5 text-xs text-slate-900 dark:border-slate-700 dark:bg-slate-800 dark:text-white"
                            required
                        />
                    </div>

                    <div>
                        <label class="text-xs font-semibold text-slate-700 dark:text-slate-300">Material Terbuang</label>
                        <input
                            v-model="form.material_name"
                            type="text"
                            placeholder="Contoh: Botol Kaca 100ml / Alufoil"
                            class="mt-1 w-full rounded-lg border border-slate-200 p-2.5 text-xs text-slate-900 dark:border-slate-700 dark:bg-slate-800 dark:text-white"
                        />
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="text-xs font-semibold text-slate-700 dark:text-slate-300">Jumlah Reject *</label>
                            <input
                                v-model.number="form.jumlah"
                                type="number"
                                step="1"
                                class="mt-1 w-full rounded-lg border border-slate-200 p-2.5 text-xs text-slate-900 dark:border-slate-700 dark:bg-slate-800 dark:text-white"
                                required
                            />
                        </div>
                        <div>
                            <label class="text-xs font-semibold text-slate-700 dark:text-slate-300">Biaya Satuan (Rp)</label>
                            <input
                                v-model.number="form.biaya_satuan"
                                type="number"
                                class="mt-1 w-full rounded-lg border border-slate-200 p-2.5 text-xs text-slate-900 dark:border-slate-700 dark:bg-slate-800 dark:text-white"
                            />
                        </div>
                    </div>

                    <div>
                        <label class="text-xs font-semibold text-slate-700 dark:text-slate-300">Keterangan Tambahan</label>
                        <textarea
                            v-model="form.keterangan"
                            rows="2"
                            placeholder="Penyebab kerusakan..."
                            class="mt-1 w-full rounded-lg border border-slate-200 p-2.5 text-xs text-slate-900 dark:border-slate-700 dark:bg-slate-800 dark:text-white"
                        ></textarea>
                    </div>

                    <div class="flex justify-end gap-2 pt-3 border-t border-slate-100 dark:border-slate-800">
                        <button
                            type="button"
                            @click="showModal = false"
                            class="rounded-lg border border-slate-200 px-3 py-1.5 text-xs font-medium text-slate-700 hover:bg-slate-50 dark:border-slate-700 dark:text-slate-300"
                        >
                            Batal
                        </button>
                        <button
                            type="submit"
                            class="rounded-lg bg-rose-600 px-4 py-1.5 text-xs font-semibold text-white hover:bg-rose-700"
                        >
                            Simpan Reject
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </ModuleLayout>
</template>
