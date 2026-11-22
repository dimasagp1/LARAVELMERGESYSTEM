<script setup>
import { Head, Link } from '@inertiajs/vue3';
import ModuleLayout from '@/Layouts/ModuleLayout.vue';
import {
    Receipt,
    Plus,
    FileText,
    Eye,
    Clock,
    CheckCircle2,
    AlertCircle,
    RotateCcw,
} from 'lucide-vue-next';

const props = defineProps({
    module: Object,
    bons: Object,
});

const getBonBadge = (st) => {
    switch (st) {
        case 'waiting_gm': return { label: 'Menunggu GM', class: 'bg-amber-100 text-amber-800 font-bold' };
        case 'revision': return { label: 'Revisi GM', class: 'bg-rose-100 text-rose-800 font-bold' };
        case 'approved_gm': return { label: 'Disetujui Penuh', class: 'bg-emerald-100 text-emerald-800 font-bold' };
        case 'partial_approved': return { label: 'Disetujui Sebagian', class: 'bg-teal-100 text-teal-800 font-bold' };
        case 'rejected_gm': return { label: 'Ditolak GM', class: 'bg-rose-100 text-rose-800 font-bold' };
        default: return { label: st, class: 'bg-slate-100 text-slate-700' };
    }
};
</script>

<template>
    <Head title="Pengadaan Khusus (PR Bon) - ERP" />

    <ModuleLayout :module="module">
        <div class="space-y-6">
            <!-- Header -->
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-6 shadow-xs">
                <div>
                    <h1 class="text-xl font-bold text-slate-900 dark:text-white">
                        Daftar Berkas Pengadaan Khusus (PR Bon)
                    </h1>
                    <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">
                        Pengadaan belanja langsung vendor offline, kas kecil, dan persetujuan terpusat GM
                    </p>
                </div>
                <Link
                    href="/procurement/pr-bons/staging"
                    class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-amber-500 hover:bg-amber-600 text-white text-xs font-bold shadow-xs transition-colors self-start sm:self-auto"
                >
                    <Plus class="w-4 h-4" />
                    Kelompokkan Staging Bon Baru
                </Link>
            </div>

            <!-- Table Card -->
            <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl overflow-hidden shadow-xs">
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="border-b border-slate-200 dark:border-slate-800 text-[11px] font-semibold text-slate-500 uppercase tracking-wider bg-slate-50/50 dark:bg-slate-800/40">
                                <th class="py-3.5 px-4">Nomor Berkas & Tanggal</th>
                                <th class="py-3.5 px-4">Vendor / Toko Penyedia</th>
                                <th class="py-3.5 px-4 text-center">Jml Item</th>
                                <th class="py-3.5 px-4 text-right">Total Bon (Rp)</th>
                                <th class="py-3.5 px-4">Dibuat Oleh</th>
                                <th class="py-3.5 px-4">Status GM</th>
                                <th class="py-3.5 px-4 text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 dark:divide-slate-800 text-xs text-slate-700 dark:text-slate-300">
                            <tr
                                v-for="bon in bons.data"
                                :key="bon.id"
                                class="hover:bg-slate-50/60 dark:hover:bg-slate-800/30 transition-colors"
                            >
                                <td class="py-3.5 px-4 font-mono font-bold text-slate-900 dark:text-white">
                                    {{ bon.bon_number }}
                                    <div class="text-[11px] font-sans font-normal text-slate-400">{{ bon.bon_date }}</div>
                                </td>
                                <td class="py-3.5 px-4 font-semibold text-slate-800 dark:text-slate-200">
                                    {{ bon.vendor_name }}
                                </td>
                                <td class="py-3.5 px-4 text-center font-bold">
                                    {{ bon.items?.length || 0 }}
                                </td>
                                <td class="py-3.5 px-4 text-right font-extrabold text-amber-600 dark:text-amber-400">
                                    Rp {{ Number(bon.total_amount).toLocaleString('id-ID') }}
                                </td>
                                <td class="py-3.5 px-4">
                                    {{ bon.creator?.name || 'Procurement' }}
                                </td>
                                <td class="py-3.5 px-4">
                                    <span :class="['px-2.5 py-1 rounded-full text-[10px] inline-block', getBonBadge(bon.status).class]">
                                        {{ getBonBadge(bon.status).label }}
                                    </span>
                                </td>
                                <td class="py-3.5 px-4 text-right">
                                    <div class="flex items-center justify-end gap-1.5">
                                        <Link
                                            :href="`/procurement/pr-bons/${bon.id}`"
                                            class="p-1.5 rounded-lg text-slate-600 hover:text-amber-600 hover:bg-slate-100 dark:text-slate-400 dark:hover:bg-slate-800"
                                            title="Periksa Detail & Keputusan GM"
                                        >
                                            <Eye class="w-4 h-4" />
                                        </Link>
                                        <a
                                            :href="`/procurement/pr-bons/${bon.id}/print`"
                                            target="_blank"
                                            class="p-1.5 rounded-lg text-slate-600 hover:text-emerald-600 hover:bg-slate-100 dark:text-slate-400 dark:hover:bg-slate-800"
                                            title="Cetak Form Bon"
                                        >
                                            <FileText class="w-4 h-4" />
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div v-if="bons.data?.length === 0" class="text-center py-12 text-slate-400 text-xs">
                    Belum ada riwayat berkas PR Bon sementara.
                </div>
            </div>
        </div>
    </ModuleLayout>
</template>
