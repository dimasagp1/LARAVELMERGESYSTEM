<script setup>
import { Head, Link, router } from '@inertiajs/vue3';
import ModuleLayout from '@/Layouts/ModuleLayout.vue';
import {
    Cpu,
    ArrowLeft,
    Calendar,
    Factory,
    User,
    CheckCircle2,
    AlertCircle,
    Boxes,
    FileText,
    ShoppingCart
} from 'lucide-vue-next';

const props = defineProps({
    mrpRun: Object,
});

const formatNumber = (val) => new Intl.NumberFormat('id-ID').format(val || 0);

function convertToPr() {
    if (confirm('Konversi rekomendasi kebutuhan bersih MRP ini menjadi Draft PR di modul Procurement?')) {
        router.post(`/supply-chain/mrp/${props.mrpRun.id}/convert-to-pr`);
    }
}
</script>

<template>
    <Head :title="`Detail MRP ${mrpRun.run_number} - LogiFlow SCM`" />

    <ModuleLayout current-module="supply-chain">
        <div class="space-y-6">
            <!-- Header -->
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 bg-white dark:bg-slate-800 p-6 rounded-2xl shadow-sm border border-slate-200 dark:border-slate-700">
                <div class="flex items-center gap-3">
                    <Link
                        href="/supply-chain/mrp"
                        class="p-2 text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 hover:bg-slate-100 dark:hover:bg-slate-700 rounded-xl transition-all"
                    >
                        <ArrowLeft class="w-5 h-5" />
                    </Link>
                    <div>
                        <div class="flex items-center gap-2">
                            <h1 class="text-2xl font-bold text-slate-900 dark:text-white tracking-tight">
                                {{ mrpRun.run_number }}
                            </h1>
                            <span class="text-xs px-2.5 py-0.5 rounded-full font-bold bg-emerald-50 text-emerald-700 dark:bg-emerald-950/40 dark:text-emerald-400">
                                Selesai Dihitung
                            </span>
                        </div>
                        <p class="text-xs text-slate-500 mt-1">
                            Simulasi ledakan kebutuhan material untuk horizon {{ mrpRun.planning_horizon_months }} bulan di {{ mrpRun.plant?.name }}.
                        </p>
                    </div>
                </div>

                <div class="flex items-center gap-4 text-xs text-slate-500">
                    <div>
                        <span class="block font-semibold text-slate-700 dark:text-slate-300">Tanggal Eksekusi</span>
                        <span>{{ mrpRun.run_date }}</span>
                    </div>
                    <div>
                        <span class="block font-semibold text-slate-700 dark:text-slate-300">Planner</span>
                        <span>{{ mrpRun.planner?.name ?? 'System' }}</span>
                    </div>

                    <button
                        @click="convertToPr"
                        class="px-4 py-2 bg-gradient-to-r from-violet-600 to-indigo-600 hover:from-violet-500 hover:to-indigo-500 text-white font-semibold text-xs rounded-xl shadow-md flex items-center gap-1.5 transition-all"
                    >
                        <ShoppingCart class="w-4 h-4" />
                        <span>Konversi ke Draft PR</span>
                    </button>
                </div>
            </div>

            <!-- Items Table -->
            <div class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-200 dark:border-slate-700 shadow-sm p-6">
                <div class="flex items-center justify-between mb-4">
                    <h2 class="text-base font-bold text-slate-900 dark:text-white">Rincian Ledakan Kebutuhan Bersih Material</h2>
                    <span class="text-xs text-slate-500">{{ mrpRun.items?.length || 0 }} Komponen Material</span>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs">
                        <thead>
                            <tr class="border-b border-slate-200 dark:border-slate-700 text-slate-400 font-semibold uppercase">
                                <th class="pb-3">Kode & Nama Material</th>
                                <th class="pb-3 text-right">Kebutuhan Kotor</th>
                                <th class="pb-3 text-right">Stok Fisik Gudang</th>
                                <th class="pb-3 text-right">Stok Dipesan (PO)</th>
                                <th class="pb-3 text-right">Safety Stock</th>
                                <th class="pb-3 text-right text-rose-600 font-bold">Kebutuhan Bersih (Net)</th>
                                <th class="pb-3">Saran Tgl Order</th>
                                <th class="pb-3">Saran Tgl Tiba</th>
                                <th class="pb-3">Vendor Rekanan</th>
                                <th class="pb-3 text-center">Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 dark:divide-slate-700/50">
                            <tr v-for="item in mrpRun.items" :key="item.id" class="hover:bg-slate-50 dark:hover:bg-slate-700/30">
                                <td class="py-3">
                                    <div class="font-semibold text-slate-800 dark:text-slate-200">{{ item.product?.code }}</div>
                                    <div class="text-slate-500">{{ item.product?.name }}</div>
                                </td>
                                <td class="py-3 text-right font-medium">{{ formatNumber(item.gross_requirement) }} {{ item.product?.uom?.code }}</td>
                                <td class="py-3 text-right">{{ formatNumber(item.on_hand_stock) }}</td>
                                <td class="py-3 text-right text-blue-600">{{ formatNumber(item.outstanding_po_qty) }}</td>
                                <td class="py-3 text-right text-slate-500">{{ formatNumber(item.safety_stock) }}</td>
                                <td class="py-3 text-right font-bold text-rose-600">
                                    {{ formatNumber(item.net_requirement) }} {{ item.product?.uom?.code }}
                                </td>
                                <td class="py-3 text-slate-600 dark:text-slate-300">{{ item.suggested_order_date ?? '-' }}</td>
                                <td class="py-3 text-slate-600 dark:text-slate-300">{{ item.suggested_due_date ?? '-' }}</td>
                                <td class="py-3 text-slate-600 dark:text-slate-300">{{ item.vendor?.name ?? '-' }}</td>
                                <td class="py-3 text-center">
                                    <span
                                        :class="[
                                            'inline-flex px-2 py-0.5 rounded-full text-[10px] font-bold uppercase',
                                            item.net_requirement > 0
                                                ? 'bg-rose-50 text-rose-700 dark:bg-rose-950/40 dark:text-rose-400'
                                                : 'bg-emerald-50 text-emerald-700 dark:bg-emerald-950/40 dark:text-emerald-400'
                                        ]"
                                    >
                                        {{ item.net_requirement > 0 ? 'ORDER PR' : 'STOK CUKUP' }}
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
