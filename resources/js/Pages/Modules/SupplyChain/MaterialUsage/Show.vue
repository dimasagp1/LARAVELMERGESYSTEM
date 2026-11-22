<script setup>
import { Head, Link } from '@inertiajs/vue3';
import ModuleLayout from '@/Layouts/ModuleLayout.vue';
import {
    ArrowRightCircle,
    ArrowLeft,
    FileDown,
    Building2,
    Calendar,
    User,
    CheckCircle2,
    FileText
} from 'lucide-vue-next';

const props = defineProps({
    usage: Object,
});

const formatCurrency = (val) => new Intl.NumberFormat('id-ID', {
    style: 'currency',
    currency: 'IDR',
    maximumFractionDigits: 0,
}).format(val || 0);

const formatNumber = (val) => new Intl.NumberFormat('id-ID').format(val || 0);
</script>

<template>
    <Head :title="`Detail BSTB ${usage.usage_number} - LogiFlow SCM`" />

    <ModuleLayout current-module="supply-chain">
        <div class="space-y-6 max-w-5xl mx-auto">
            <!-- Header -->
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 bg-white dark:bg-slate-800 p-6 rounded-2xl shadow-sm border border-slate-200 dark:border-slate-700">
                <div class="flex items-center gap-3">
                    <Link
                        href="/supply-chain/material-usage"
                        class="p-2 text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 hover:bg-slate-100 dark:hover:bg-slate-700 rounded-xl transition-all"
                    >
                        <ArrowLeft class="w-5 h-5" />
                    </Link>
                    <div>
                        <div class="flex items-center gap-2">
                            <h1 class="text-2xl font-bold text-slate-900 dark:text-white tracking-tight">
                                {{ usage.usage_number }}
                            </h1>
                            <span class="text-xs px-2.5 py-0.5 rounded-full font-bold uppercase bg-blue-50 text-blue-700 dark:bg-blue-950/40 dark:text-blue-400">
                                Diserahkan (Issued)
                            </span>
                        </div>
                        <p class="text-xs text-slate-500 mt-1">
                            Penyerahan material untuk SPK {{ usage.work_order_number }} ke departemen {{ usage.department?.name }}.
                        </p>
                    </div>
                </div>

                <div>
                    <a
                        :href="`/supply-chain/material-usage/${usage.id}/pdf`"
                        target="_blank"
                        class="inline-flex items-center gap-2 px-4 py-2.5 bg-slate-100 hover:bg-slate-200 dark:bg-slate-700 dark:hover:bg-slate-600 text-slate-700 dark:text-slate-200 text-xs font-semibold rounded-xl transition-all"
                    >
                        <FileDown class="w-4 h-4 text-rose-500" />
                        <span>Unduh Bukti BSTB (PDF)</span>
                    </a>
                </div>
            </div>

            <!-- Meta Cards -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                <div class="p-4 bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700">
                    <span class="block text-xs font-medium text-slate-400">No. Work Order / SPK</span>
                    <span class="font-bold text-sm text-slate-800 dark:text-slate-200">{{ usage.work_order_number }}</span>
                    <span class="block text-[11px] text-slate-400 mt-0.5">Produksi Manufaktur</span>
                </div>

                <div class="p-4 bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700">
                    <span class="block text-xs font-medium text-slate-400">Pabrik Fasilitas</span>
                    <span class="font-bold text-sm text-slate-800 dark:text-slate-200">{{ usage.plant?.name }}</span>
                    <span class="block text-[11px] text-slate-400 mt-0.5">Kode: {{ usage.plant?.code }}</span>
                </div>

                <div class="p-4 bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700">
                    <span class="block text-xs font-medium text-slate-400">Departemen Penerima</span>
                    <span class="font-bold text-sm text-slate-800 dark:text-slate-200">{{ usage.department?.name }}</span>
                    <span class="block text-[11px] text-slate-400 mt-0.5">Diterima: {{ usage.recipient?.name ?? 'Operator Mesin' }}</span>
                </div>

                <div class="p-4 bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700">
                    <span class="block text-xs font-medium text-slate-400">Petugas Penyerah</span>
                    <span class="font-bold text-sm text-slate-800 dark:text-slate-200">{{ usage.issuer?.name ?? 'Staff Gudang' }}</span>
                    <span class="block text-[11px] text-slate-400 mt-0.5">Tgl: {{ usage.usage_date }}</span>
                </div>
            </div>

            <!-- Items Table -->
            <div class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-200 dark:border-slate-700 shadow-sm p-6">
                <h2 class="text-base font-bold text-slate-900 dark:text-white mb-4">Rincian Komponen Material Diserahkan</h2>

                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs">
                        <thead>
                            <tr class="border-b border-slate-200 dark:border-slate-700 text-slate-400 font-semibold uppercase">
                                <th class="pb-3">Kode & Nama Bahan</th>
                                <th class="pb-3">No. Batch Asal</th>
                                <th class="pb-3 text-right">Kuantitas</th>
                                <th class="pb-3 text-center">Satuan</th>
                                <th class="pb-3 text-right">Estimasi Biaya Satuan</th>
                                <th class="pb-3 text-right">Subtotal Biaya</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 dark:divide-slate-700/50">
                            <tr v-for="item in usage.items" :key="item.id" class="hover:bg-slate-50 dark:hover:bg-slate-700/30">
                                <td class="py-3.5">
                                    <div class="font-semibold text-slate-800 dark:text-slate-200">{{ item.product?.code }}</div>
                                    <div class="text-slate-500">{{ item.product?.name }}</div>
                                </td>
                                <td class="py-3.5 text-slate-700 dark:text-slate-300">{{ item.batch_number ?? '-' }}</td>
                                <td class="py-3.5 text-right font-bold text-slate-900 dark:text-white">
                                    {{ formatNumber(item.quantity) }}
                                </td>
                                <td class="py-3.5 text-center text-slate-500">{{ item.uom }}</td>
                                <td class="py-3.5 text-right text-slate-600 dark:text-slate-400">
                                    {{ formatCurrency(item.unit_cost) }}
                                </td>
                                <td class="py-3.5 text-right font-semibold text-emerald-600">
                                    {{ formatCurrency(item.quantity * item.unit_cost) }}
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </ModuleLayout>
</template>
