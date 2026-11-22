<script setup>
import { ref } from 'vue';
import { Head, Link, useForm, router } from '@inertiajs/vue3';
import ModuleLayout from '@/Layouts/ModuleLayout.vue';
import {
    Cpu,
    Calendar,
    Factory,
    Clock,
    Plus,
    Eye,
    CheckCircle2,
    AlertCircle,
    Play,
    X,
    Filter,
    Boxes
} from 'lucide-vue-next';

const props = defineProps({
    mrpRuns: Object,
    latestRun: Object,
    activeSchedules: Array,
    plants: Array,
});

const isModalOpen = ref(false);

const form = useForm({
    plant_id: props.plants?.[0]?.id || '',
    planning_horizon_months: 3,
    notes: 'Kalkulasi kebutuhan bersih material formulasi',
});

const submitMrp = () => {
    form.post('/supply-chain/mrp/run', {
        onSuccess: () => {
            isModalOpen.value = false;
        },
    });
};

const formatNumber = (val) => new Intl.NumberFormat('id-ID').format(val || 0);
</script>

<template>
    <Head title="MRP Net Requirements - LogiFlow SCM" />

    <ModuleLayout current-module="supply-chain">
        <div class="space-y-6">
            <!-- Header Section -->
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 bg-white dark:bg-slate-800 p-6 rounded-2xl shadow-sm border border-slate-200 dark:border-slate-700">
                <div class="flex items-center gap-3">
                    <div class="p-2.5 bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 rounded-xl">
                        <Cpu class="w-7 h-7" />
                    </div>
                    <div>
                        <h1 class="text-2xl font-bold text-slate-900 dark:text-white tracking-tight">
                            Material Requirements Planning (MRP)
                        </h1>
                        <p class="text-sm text-slate-500 dark:text-slate-400 mt-0.5">
                            Ledakan kebutuhan bersih bahan baku & kemas berdasarkan Jadwal Induk Produksi (MPS) dan Resep BOM.
                        </p>
                    </div>
                </div>

                <div>
                    <button
                        @click="isModalOpen = true"
                        class="inline-flex items-center gap-2 px-4 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-semibold rounded-xl shadow-sm transition-all shadow-emerald-500/20"
                    >
                        <Play class="w-4 h-4 fill-white" />
                        <span>Jalankan Simulasi MRP</span>
                    </button>
                </div>
            </div>

            <!-- Active Master Production Schedules (MPS) -->
            <div class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-200 dark:border-slate-700 shadow-sm p-6">
                <div class="flex items-center justify-between mb-4">
                    <div>
                        <h2 class="text-base font-bold text-slate-900 dark:text-white">Jadwal Induk Produksi Aktif (MPS Target)</h2>
                        <p class="text-xs text-slate-500">Rencana batch manufaktur yang menjadi dasar kalkulasi kebutuhan material.</p>
                    </div>
                    <span class="text-xs font-semibold px-2.5 py-1 bg-emerald-50 text-emerald-700 dark:bg-emerald-950/40 dark:text-emerald-400 rounded-full">
                        {{ activeSchedules?.length || 0 }} Rencana Terjadwal
                    </span>
                </div>

                <div v-if="activeSchedules && activeSchedules.length > 0" class="overflow-x-auto">
                    <table class="w-full text-left text-xs">
                        <thead>
                            <tr class="border-b border-slate-200 dark:border-slate-700 text-slate-400 font-semibold uppercase">
                                <th class="pb-3">Kode Produk FG</th>
                                <th class="pb-3">Nama Produk Jadi</th>
                                <th class="pb-3">Pabrik</th>
                                <th class="pb-3 text-right">Target Kuantitas</th>
                                <th class="pb-3">Tanggal Produksi</th>
                                <th class="pb-3 text-center">Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 dark:divide-slate-700/50">
                            <tr v-for="sch in activeSchedules" :key="sch.id" class="hover:bg-slate-50 dark:hover:bg-slate-700/30">
                                <td class="py-3 font-semibold text-emerald-600">{{ sch.product?.code }}</td>
                                <td class="py-3 text-slate-800 dark:text-slate-200 font-medium">{{ sch.product?.name }}</td>
                                <td class="py-3 text-slate-500">{{ sch.plant?.name }}</td>
                                <td class="py-3 text-right font-bold text-slate-900 dark:text-white">
                                    {{ formatNumber(sch.planned_quantity) }} {{ sch.product?.uom?.code }}
                                </td>
                                <td class="py-3 text-slate-600 dark:text-slate-400">{{ sch.schedule_date }}</td>
                                <td class="py-3 text-center">
                                    <span class="inline-flex px-2 py-0.5 rounded-full text-[10px] font-bold uppercase bg-blue-50 text-blue-700 dark:bg-blue-950/40 dark:text-blue-400">
                                        {{ sch.status }}
                                    </span>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <div v-else class="py-6 text-center text-slate-500 text-xs">
                    Belum ada jadwal MPS aktif. Silakan tambahkan jadwal produksi.
                </div>
            </div>

            <!-- Latest MRP Explosion Items -->
            <div v-if="latestRun" class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-200 dark:border-slate-700 shadow-sm p-6">
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-4">
                    <div>
                        <div class="flex items-center gap-2">
                            <h2 class="text-base font-bold text-slate-900 dark:text-white">
                                Hasil Ledakan Kebutuhan Bersih (MRP: {{ latestRun.run_number }})
                            </h2>
                            <span class="text-xs px-2 py-0.5 bg-emerald-50 text-emerald-700 dark:bg-emerald-950/40 dark:text-emerald-400 rounded font-semibold">
                                Horizon {{ latestRun.planning_horizon_months }} Bulan
                            </span>
                        </div>
                        <p class="text-xs text-slate-500">
                            Dijalankan oleh {{ latestRun.planner?.name ?? 'Sistem' }} pada {{ latestRun.run_date }} di {{ latestRun.plant?.name }}.
                        </p>
                    </div>

                    <Link
                        :href="`/supply-chain/mrp/${latestRun.id}`"
                        class="text-xs text-emerald-600 hover:text-emerald-700 font-semibold flex items-center gap-1"
                    >
                        <span>Lihat Detail Lengkap</span>
                        <Eye class="w-4 h-4" />
                    </Link>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs">
                        <thead>
                            <tr class="border-b border-slate-200 dark:border-slate-700 text-slate-400 font-semibold uppercase">
                                <th class="pb-3">Kode & Bahan Baku</th>
                                <th class="pb-3 text-right">Kebutuhan Kotor</th>
                                <th class="pb-3 text-right">Stok Fisik Gudang</th>
                                <th class="pb-3 text-right">Stok Dipesan (PO)</th>
                                <th class="pb-3 text-right">Safety Stock</th>
                                <th class="pb-3 text-right text-rose-600 font-bold">Kebutuhan Bersih</th>
                                <th class="pb-3">Saran Tgl Order</th>
                                <th class="pb-3">Vendor Rekanan</th>
                                <th class="pb-3 text-center">Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 dark:divide-slate-700/50">
                            <tr v-for="item in latestRun.items" :key="item.id" class="hover:bg-slate-50 dark:hover:bg-slate-700/30">
                                <td class="py-3">
                                    <div class="font-semibold text-slate-800 dark:text-slate-200">{{ item.product?.code }}</div>
                                    <div class="text-slate-500 line-clamp-1">{{ item.product?.name }}</div>
                                </td>
                                <td class="py-3 text-right">{{ formatNumber(item.gross_requirement) }} {{ item.product?.uom?.code }}</td>
                                <td class="py-3 text-right">{{ formatNumber(item.on_hand_stock) }}</td>
                                <td class="py-3 text-right text-blue-600 font-medium">{{ formatNumber(item.outstanding_po_qty) }}</td>
                                <td class="py-3 text-right text-slate-500">{{ formatNumber(item.safety_stock) }}</td>
                                <td class="py-3 text-right font-bold text-rose-600">
                                    {{ formatNumber(item.net_requirement) }} {{ item.product?.uom?.code }}
                                </td>
                                <td class="py-3 text-slate-600 dark:text-slate-300">{{ item.suggested_order_date ?? '-' }}</td>
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

            <!-- Past MRP Runs History -->
            <div class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-200 dark:border-slate-700 shadow-sm p-6">
                <h2 class="text-base font-bold text-slate-900 dark:text-white mb-4">Riwayat Eksekusi MRP</h2>

                <div v-if="mrpRuns.data && mrpRuns.data.length > 0" class="overflow-x-auto">
                    <table class="w-full text-left text-xs">
                        <thead>
                            <tr class="border-b border-slate-200 dark:border-slate-700 text-slate-400 font-semibold uppercase">
                                <th class="pb-3">No. Run MRP</th>
                                <th class="pb-3">Pabrik</th>
                                <th class="pb-3">Tanggal Eksekusi</th>
                                <th class="pb-3">Horizon Perencanaan</th>
                                <th class="pb-3 text-center">Jumlah Item Kebutuhan</th>
                                <th class="pb-3">Planner</th>
                                <th class="pb-3 text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 dark:divide-slate-700/50">
                            <tr v-for="run in mrpRuns.data" :key="run.id" class="hover:bg-slate-50 dark:hover:bg-slate-700/30">
                                <td class="py-3 font-semibold text-emerald-600">{{ run.run_number }}</td>
                                <td class="py-3 text-slate-800 dark:text-slate-200">{{ run.plant?.name }}</td>
                                <td class="py-3 text-slate-500">{{ run.run_date }}</td>
                                <td class="py-3 text-slate-600 dark:text-slate-400">{{ run.planning_horizon_months }} Bulan</td>
                                <td class="py-3 text-center font-bold text-slate-800 dark:text-slate-200">{{ run.items_count }} Item</td>
                                <td class="py-3 text-slate-600 dark:text-slate-300">{{ run.planner?.name ?? 'System' }}</td>
                                <td class="py-3 text-right">
                                    <Link
                                        :href="`/supply-chain/mrp/${run.id}`"
                                        class="p-1.5 text-slate-400 hover:text-emerald-600 transition-colors inline-block"
                                        title="Buka Ledakan MRP"
                                    >
                                        <Eye class="w-4 h-4" />
                                    </Link>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Run MRP Modal -->
        <div v-if="isModalOpen" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/50 backdrop-blur-sm">
            <div class="bg-white dark:bg-slate-800 rounded-2xl max-w-md w-full p-6 shadow-xl border border-slate-200 dark:border-slate-700">
                <div class="flex items-center justify-between mb-4 pb-3 border-b border-slate-100 dark:border-slate-700">
                    <h3 class="font-bold text-slate-900 dark:text-white text-base">Jalankan Simulasi MRP</h3>
                    <button @click="isModalOpen = false" class="text-slate-400 hover:text-slate-600">
                        <X class="w-5 h-5" />
                    </button>
                </div>

                <form @submit.prevent="submitMrp" class="space-y-4 text-xs">
                    <div>
                        <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1">Pilih Pabrik / Fasilitas</label>
                        <select v-model="form.plant_id" class="w-full rounded-xl border border-slate-200 dark:border-slate-700 px-3 py-2 text-xs dark:bg-slate-900">
                            <option v-for="plant in plants" :key="plant.id" :value="plant.id">{{ plant.name }} ({{ plant.code }})</option>
                        </select>
                    </div>

                    <div>
                        <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1">Horizon Perencanaan (Bulan)</label>
                        <input
                            type="number"
                            v-model="form.planning_horizon_months"
                            min="1"
                            max="12"
                            class="w-full rounded-xl border border-slate-200 dark:border-slate-700 px-3 py-2 text-xs dark:bg-slate-900"
                        />
                    </div>

                    <div>
                        <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1">Catatan Simulasi</label>
                        <textarea
                            v-model="form.notes"
                            rows="3"
                            class="w-full rounded-xl border border-slate-200 dark:border-slate-700 px-3 py-2 text-xs dark:bg-slate-900"
                            placeholder="Tujuan kalkulasi atau nomor batch referensi..."
                        ></textarea>
                    </div>

                    <div class="flex items-center justify-end gap-3 pt-3">
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
                            class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white font-semibold rounded-xl"
                        >
                            {{ form.processing ? 'Menghitung...' : 'Mulai Eksekusi' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </ModuleLayout>
</template>
