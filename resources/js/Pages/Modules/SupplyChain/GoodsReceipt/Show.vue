<script setup>
import { Head, Link } from '@inertiajs/vue3';
import ModuleLayout from '@/Layouts/ModuleLayout.vue';
import {
    Truck,
    ArrowLeft,
    FileDown,
    Building2,
    Calendar,
    User,
    CheckCircle2,
    AlertCircle,
    ShieldCheck,
    Clock,
    FileText
} from 'lucide-vue-next';

const props = defineProps({
    receipt: Object,
});

const formatNumber = (val) => new Intl.NumberFormat('id-ID').format(val || 0);
</script>

<template>
    <Head :title="`Detail GRN ${receipt.grn_number} - LogiFlow SCM`" />

    <ModuleLayout current-module="supply-chain">
        <div class="space-y-6 max-w-5xl mx-auto">
            <!-- Header -->
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 bg-white dark:bg-slate-800 p-6 rounded-2xl shadow-sm border border-slate-200 dark:border-slate-700">
                <div class="flex items-center gap-3">
                    <Link
                        href="/supply-chain/goods-receipt"
                        class="p-2 text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 hover:bg-slate-100 dark:hover:bg-slate-700 rounded-xl transition-all"
                    >
                        <ArrowLeft class="w-5 h-5" />
                    </Link>
                    <div>
                        <div class="flex items-center gap-2">
                            <h1 class="text-2xl font-bold text-slate-900 dark:text-white tracking-tight">
                                {{ receipt.grn_number }}
                            </h1>
                            <span
                                :class="[
                                    'text-xs px-2.5 py-0.5 rounded-full font-bold uppercase',
                                    receipt.status === 'completed'
                                        ? 'bg-emerald-50 text-emerald-700 dark:bg-emerald-950/40 dark:text-emerald-400'
                                        : 'bg-amber-50 text-amber-700 dark:bg-amber-950/40 dark:text-amber-400'
                                ]"
                            >
                                {{ receipt.status === 'completed' ? 'LULUS QC' : 'DALAM KARANTINA' }}
                            </span>
                        </div>
                        <p class="text-xs text-slate-500 mt-1">
                            Penerimaan barang dari {{ receipt.vendor?.name }} pada {{ receipt.receipt_date }}.
                        </p>
                    </div>
                </div>

                <div class="flex items-center gap-2">
                    <a
                        :href="`/supply-chain/goods-receipt/${receipt.id}/pdf`"
                        target="_blank"
                        class="inline-flex items-center gap-2 px-4 py-2.5 bg-slate-100 hover:bg-slate-200 dark:bg-slate-700 dark:hover:bg-slate-600 text-slate-700 dark:text-slate-200 text-xs font-semibold rounded-xl transition-all"
                    >
                        <FileDown class="w-4 h-4 text-rose-500" />
                        <span>Unduh Bukti GRN (PDF)</span>
                    </a>
                    <Link
                        href="/supply-chain/quarantine"
                        class="inline-flex items-center gap-2 px-4 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-semibold rounded-xl transition-all"
                    >
                        <ShieldCheck class="w-4 h-4" />
                        <span>Pemeriksaan Mutu QC</span>
                    </Link>
                </div>
            </div>

            <!-- Metadata Info -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                <div class="p-4 bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700">
                    <span class="block text-xs font-medium text-slate-400">Vendor / Pemasok</span>
                    <span class="font-bold text-sm text-slate-800 dark:text-slate-200">{{ receipt.vendor?.name }}</span>
                    <span class="block text-[11px] text-slate-400 mt-0.5">Kode: {{ receipt.vendor?.code }}</span>
                </div>

                <div class="p-4 bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700">
                    <span class="block text-xs font-medium text-slate-400">Surat Jalan (DO)</span>
                    <span class="font-bold text-sm text-slate-800 dark:text-slate-200">{{ receipt.delivery_order_number }}</span>
                    <span class="block text-[11px] text-slate-400 mt-0.5">PO: {{ receipt.po_number ?? '-' }}</span>
                </div>

                <div class="p-4 bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700">
                    <span class="block text-xs font-medium text-slate-400">Fasilitas Penerima</span>
                    <span class="font-bold text-sm text-slate-800 dark:text-slate-200">{{ receipt.plant?.name }}</span>
                    <span class="block text-[11px] text-slate-400 mt-0.5">Gudang Karantina QC (WH-QRN)</span>
                </div>

                <div class="p-4 bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700">
                    <span class="block text-xs font-medium text-slate-400">Penerima Fisik</span>
                    <span class="font-bold text-sm text-slate-800 dark:text-slate-200">{{ receipt.receiver?.name ?? 'Petugas Gudang' }}</span>
                    <span class="block text-[11px] text-slate-400 mt-0.5">Tgl: {{ receipt.receipt_date }}</span>
                </div>
            </div>

            <!-- Items and QC status -->
            <div class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-200 dark:border-slate-700 shadow-sm p-6">
                <h2 class="text-base font-bold text-slate-900 dark:text-white mb-4">Daftar Material & Hasil Pengujian QC</h2>

                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs">
                        <thead>
                            <tr class="border-b border-slate-200 dark:border-slate-700 text-slate-400 font-semibold uppercase">
                                <th class="pb-3">Kode & Nama Produk</th>
                                <th class="pb-3">No. Batch Vendor</th>
                                <th class="pb-3">Tgl Kedaluwarsa</th>
                                <th class="pb-3 text-right">Kuantitas Tiba</th>
                                <th class="pb-3 text-right text-emerald-600">Lulus QC</th>
                                <th class="pb-3 text-right text-rose-600">Ditolak QC</th>
                                <th class="pb-3 text-center">Status Mutu</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 dark:divide-slate-700/50">
                            <tr v-for="item in receipt.items" :key="item.id" class="hover:bg-slate-50 dark:hover:bg-slate-700/30">
                                <td class="py-3.5">
                                    <div class="font-semibold text-slate-800 dark:text-slate-200">{{ item.product?.code }}</div>
                                    <div class="text-slate-500">{{ item.item_name }}</div>
                                </td>
                                <td class="py-3.5 text-slate-700 dark:text-slate-300">{{ item.batch_number ?? '-' }}</td>
                                <td class="py-3.5 text-slate-600 dark:text-slate-400">{{ item.expiry_date ?? '-' }}</td>
                                <td class="py-3.5 text-right font-medium text-slate-900 dark:text-white">
                                    {{ formatNumber(item.received_qty) }} {{ item.uom }}
                                </td>
                                <td class="py-3.5 text-right font-bold text-emerald-600">
                                    {{ formatNumber(item.passed_qty) }} {{ item.uom }}
                                </td>
                                <td class="py-3.5 text-right font-bold text-rose-600">
                                    {{ formatNumber(item.rejected_qty) }} {{ item.uom }}
                                </td>
                                <td class="py-3.5 text-center">
                                    <span
                                        :class="[
                                            'inline-flex px-2 py-0.5 rounded-full text-[10px] font-bold uppercase',
                                            item.status === 'passed'
                                                ? 'bg-emerald-50 text-emerald-700 dark:bg-emerald-950/40 dark:text-emerald-400'
                                                : item.status === 'rejected'
                                                ? 'bg-rose-50 text-rose-700 dark:bg-rose-950/40 dark:text-rose-400'
                                                : 'bg-amber-50 text-amber-700 dark:bg-amber-950/40 dark:text-amber-400'
                                        ]"
                                    >
                                        {{ item.status }}
                                    </span>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </ModuleLayout>
</template>
