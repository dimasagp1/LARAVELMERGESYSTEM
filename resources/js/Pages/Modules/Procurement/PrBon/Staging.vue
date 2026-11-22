<script setup>
import { ref, computed } from 'vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import ModuleLayout from '@/Layouts/ModuleLayout.vue';
import {
    ArrowLeft,
    Receipt,
    Send,
    CheckCircle2,
    AlertCircle,
    Store,
    Calendar,
} from 'lucide-vue-next';

const props = defineProps({
    module: Object,
    stagedItems: Array,
});

const form = useForm({
    vendor_name: '',
    bon_date: new Date().toISOString().split('T')[0],
    item_ids: [],
    item_prices: {},
});

const toggleSelectAll = (event) => {
    if (event.target.checked) {
        form.item_ids = props.stagedItems.map((it) => it.id);
        props.stagedItems.forEach((it) => {
            if (!form.item_prices[it.id]) {
                form.item_prices[it.id] = Number(it.bon_unit_price || it.actual_price || it.estimated_price);
            }
        });
    } else {
        form.item_ids = [];
    }
};

const onItemCheck = (item) => {
    if (form.item_ids.includes(item.id)) {
        if (!form.item_prices[item.id]) {
            form.item_prices[item.id] = Number(item.bon_unit_price || item.actual_price || item.estimated_price);
        }
    }
};

const totalSelectedAmount = computed(() => {
    return form.item_ids.reduce((sum, id) => {
        const it = props.stagedItems.find((i) => i.id === id);
        if (!it) return sum;
        const price = form.item_prices[id] || Number(it.actual_price || it.estimated_price);
        return sum + price;
    }, 0);
});

const submitBon = () => {
    if (form.item_ids.length === 0) {
        alert('Pilih minimal satu item untuk dikelompokkan ke berkas Bon.');
        return;
    }
    form.post('/procurement/pr-bons');
};
</script>

<template>
    <Head title="Staging Bon Sementara - ERP" />

    <ModuleLayout :module="module">
        <div class="space-y-6 max-w-5xl mx-auto">
            <!-- Header -->
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <Link
                        href="/procurement/pr-bons"
                        class="p-2 rounded-xl bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 hover:bg-slate-100 transition-colors"
                    >
                        <ArrowLeft class="w-4 h-4 text-slate-600 dark:text-slate-300" />
                    </Link>
                    <div>
                        <h1 class="text-xl font-bold text-slate-900 dark:text-white">
                            Staging Pengadaan Khusus (PR Bon)
                        </h1>
                        <p class="text-xs text-slate-500">
                            Kelompokkan item jalur bon lintas PR menjadi satu berkas penelaahan GM
                        </p>
                    </div>
                </div>

                <button
                    type="button"
                    @click="submitBon"
                    :disabled="form.processing || form.item_ids.length === 0"
                    class="px-5 py-2.5 rounded-xl bg-amber-500 hover:bg-amber-600 text-white text-xs font-bold shadow-xs transition-colors inline-flex items-center gap-1.5 disabled:opacity-50"
                >
                    <Send class="w-4 h-4" />
                    Ajukan Berkas ke GM ({{ form.item_ids.length }} Item)
                </button>
            </div>

            <!-- Vendor & Date Card -->
            <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-6 shadow-xs space-y-4">
                <h2 class="text-sm font-bold text-slate-900 dark:text-white border-b border-slate-100 dark:border-slate-800 pb-2">
                    Informasi Toko / Vendor Bon Kas Kecil
                </h2>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">
                            Nama Toko / Rekanan Offline Penyedia Bon <span class="text-rose-500">*</span>
                        </label>
                        <div class="relative">
                            <Store class="w-4 h-4 text-slate-400 absolute left-3 top-3" />
                            <input
                                v-model="form.vendor_name"
                                type="text"
                                placeholder="Contoh: Toko Baut Jaya Abadi / Supermarket Bahan Kimia"
                                class="w-full pl-9 pr-3 py-2 text-xs rounded-xl border border-slate-300 dark:border-slate-700 dark:bg-slate-800 font-medium"
                                required
                            />
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">
                            Rencana Tanggal Bon / Pembelian <span class="text-rose-500">*</span>
                        </label>
                        <div class="relative">
                            <Calendar class="w-4 h-4 text-slate-400 absolute left-3 top-3" />
                            <input
                                v-model="form.bon_date"
                                type="date"
                                class="w-full pl-9 pr-3 py-2 text-xs rounded-xl border border-slate-300 dark:border-slate-700 dark:bg-slate-800 font-medium"
                                required
                            />
                        </div>
                    </div>
                </div>
            </div>

            <!-- Staged Items Selection Table -->
            <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-6 shadow-xs space-y-4">
                <div class="flex items-center justify-between border-b border-slate-100 dark:border-slate-800 pb-2">
                    <h2 class="text-sm font-bold text-slate-900 dark:text-white">
                        Daftar Item Antrean Menunggu Pengelompokan Bon ({{ stagedItems.length }})
                    </h2>
                    <span class="text-xs text-amber-600 font-bold">
                        Pilih item yang akan dibelanjakan di toko yang sama
                    </span>
                </div>

                <div v-if="stagedItems.length === 0" class="text-center py-12 text-slate-400 text-xs">
                    <CheckCircle2 class="w-8 h-8 mx-auto mb-2 text-emerald-500 opacity-80" />
                    Tidak ada item PR yang sedang menunggu pengelompokan bon sementara saat ini.
                </div>

                <div v-else class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="border-b border-slate-200 dark:border-slate-800 text-[11px] font-semibold text-slate-500 uppercase tracking-wider bg-slate-50/50 dark:bg-slate-800/40">
                                <th class="py-3 px-3 text-center">
                                    <input
                                        type="checkbox"
                                        @change="toggleSelectAll"
                                        class="rounded text-amber-500 focus:ring-amber-500"
                                    />
                                </th>
                                <th class="py-3 px-3">No PR & Departemen</th>
                                <th class="py-3 px-3">Nama Item & Spesifikasi</th>
                                <th class="py-3 px-3 text-center">Kuantitas</th>
                                <th class="py-3 px-3 text-right">Harga Bon (Rp)</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 dark:divide-slate-800 text-xs">
                            <tr
                                v-for="item in stagedItems"
                                :key="item.id"
                                :class="['hover:bg-slate-50/60 dark:hover:bg-slate-800/30 transition-colors', form.item_ids.includes(item.id) && 'bg-amber-50/40 dark:bg-amber-950/20']"
                            >
                                <td class="py-3 px-3 text-center">
                                    <input
                                        type="checkbox"
                                        :value="item.id"
                                        v-model="form.item_ids"
                                        @change="onItemCheck(item)"
                                        class="rounded text-amber-500 focus:ring-amber-500"
                                    />
                                </td>
                                <td class="py-3 px-3">
                                    <div class="font-bold text-slate-900 dark:text-white">
                                        {{ item.purchase_request?.pr_number }}
                                    </div>
                                    <div class="text-[11px] text-slate-500">
                                        {{ item.purchase_request?.department?.name }}
                                    </div>
                                </td>
                                <td class="py-3 px-3">
                                    <div class="font-semibold text-slate-800 dark:text-slate-200">
                                        {{ item.item_name }}
                                    </div>
                                    <div v-if="item.validation_notes" class="text-[10px] text-amber-600">
                                        Catatan: {{ item.validation_notes }}
                                    </div>
                                </td>
                                <td class="py-3 px-3 text-center font-bold">
                                    {{ Number(item.quantity) }} {{ item.uom }}
                                </td>
                                <td class="py-3 px-3 text-right">
                                    <input
                                        v-if="form.item_ids.includes(item.id)"
                                        v-model.number="form.item_prices[item.id]"
                                        type="number"
                                        class="w-32 text-right py-1 px-2 text-xs rounded border border-amber-300 dark:border-amber-700 font-bold"
                                    />
                                    <span v-else class="text-slate-600 dark:text-slate-400 font-medium">
                                        Rp {{ Number(item.actual_price || item.estimated_price).toLocaleString('id-ID') }}
                                    </span>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Total Bar -->
                <div class="p-4 rounded-xl bg-amber-50 dark:bg-amber-950/40 border border-amber-200 dark:border-amber-900 flex items-center justify-between">
                    <div>
                        <span class="text-xs font-bold text-amber-900 dark:text-amber-200 block">
                            Total Pengadaan Berkas Bon Ini:
                        </span>
                        <span class="text-[11px] text-amber-700 dark:text-amber-300">
                            {{ form.item_ids.length }} item dipilih dari antrean staging
                        </span>
                    </div>
                    <span class="text-lg font-extrabold text-amber-600 dark:text-amber-400">
                        Rp {{ totalSelectedAmount.toLocaleString('id-ID') }}
                    </span>
                </div>
            </div>
        </div>
    </ModuleLayout>
</template>
