<script setup>
import { ref } from 'vue';
import { Head, router } from '@inertiajs/vue3';
import ModuleLayout from '@/Layouts/ModuleLayout.vue';
import {
    Truck,
    PackageCheck,
    RotateCcw,
    Search,
    Calendar,
    FileCheck,
    AlertTriangle,
    Eye,
} from 'lucide-vue-next';

const props = defineProps({
    module: Object,
    items: Object,
    filters: Object,
});

const search = ref(props.filters?.search || '');
const applyFilter = () => {
    router.get('/procurement/deliveries', { search: search.value || undefined }, { preserveState: true, replace: true });
};

// Modal for Store Delivery
const deliveryModalOpen = ref(false);
const activeItem = ref(null);
const deliveredQty = ref(0);
const deliveryDate = ref(new Date().toISOString().split('T')[0]);
const deliveryOrderNumber = ref('');
const attachmentPath = ref('');
const modalLoading = ref(false);

const openDeliveryModal = (it) => {
    activeItem.value = it;
    deliveredQty.value = Number(it.quantity) - Number(it.ready_quantity);
    deliveryDate.value = new Date().toISOString().split('T')[0];
    deliveryOrderNumber.value = 'SJ-' + Math.floor(Math.random() * 90000 + 10000);
    attachmentPath.value = '';
    deliveryModalOpen.value = true;
};

const submitDelivery = () => {
    modalLoading.value = true;
    router.post(`/procurement/items/${activeItem.value.id}/deliveries`, {
        delivered_quantity: deliveredQty.value,
        delivery_date: deliveryDate.value,
        delivery_order_number: deliveryOrderNumber.value,
        attachment_path: attachmentPath.value,
    }, {
        onSuccess: () => {
            deliveryModalOpen.value = false;
            modalLoading.value = false;
        },
        onError: () => { modalLoading.value = false; },
    });
};

// Modal for Store Retur
const returModalOpen = ref(false);
const returQty = ref(0);
const returReason = ref('');
const returDeliveryNumber = ref('');

const openReturModal = (it) => {
    activeItem.value = it;
    returQty.value = 1;
    returReason.value = 'Barang rusak / bocor / tidak lolos inspeksi fisik';
    returDeliveryNumber.value = 'RET-' + Math.floor(Math.random() * 90000 + 10000);
    returModalOpen.value = true;
};

const submitRetur = () => {
    modalLoading.value = true;
    router.post(`/procurement/items/${activeItem.value.id}/retur`, {
        rejected_quantity: returQty.value,
        delivery_date: new Date().toISOString().split('T')[0],
        reject_reason: returReason.value,
        delivery_order_number: returDeliveryNumber.value,
    }, {
        onSuccess: () => {
            returModalOpen.value = false;
            modalLoading.value = false;
        },
        onError: () => { modalLoading.value = false; },
    });
};
</script>

<template>
    <Head title="Logistik & Penerimaan Fisik - ERP" />

    <ModuleLayout :module="module">
        <div class="space-y-6">
            <!-- Header -->
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-6 shadow-xs">
                <div>
                    <h1 class="text-xl font-bold text-slate-900 dark:text-white">
                        Logistik & Penerimaan Fisik Barang
                    </h1>
                    <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">
                        Pencatatan Surat Jalan (SJ), kedatangan parsial, dan retur barang cacat
                    </p>
                </div>
                <div class="flex items-center gap-2">
                    <input
                        v-model="search"
                        @keyup.enter="applyFilter"
                        type="text"
                        placeholder="Cari PO / Item / Surat Jalan..."
                        class="text-xs rounded-xl border border-slate-300 dark:border-slate-700 dark:bg-slate-800 p-2.5 w-64"
                    />
                </div>
            </div>

            <!-- Table of Ordered Items -->
            <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl overflow-hidden shadow-xs">
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="border-b border-slate-200 dark:border-slate-800 text-[11px] font-semibold text-slate-500 uppercase tracking-wider bg-slate-50/50 dark:bg-slate-800/40">
                                <th class="py-3.5 px-4">Nomor PO & PR</th>
                                <th class="py-3.5 px-4">Nama Item & Spesifikasi</th>
                                <th class="py-3.5 px-4">Vendor Suplier</th>
                                <th class="py-3.5 px-4 text-center">Pesanan</th>
                                <th class="py-3.5 px-4 text-center">Telah Tiba</th>
                                <th class="py-3.5 px-4">Progress Kedatangan</th>
                                <th class="py-3.5 px-4 text-right">Aksi Logistik</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 dark:divide-slate-800 text-xs">
                            <tr v-for="it in items.data" :key="it.id" class="hover:bg-slate-50/50 dark:hover:bg-slate-800/30">
                                <td class="py-3.5 px-4">
                                    <div class="font-mono font-bold text-violet-600 dark:text-violet-400">
                                        {{ it.po_number || 'PO-BELUM-TERBIT' }}
                                    </div>
                                    <div class="text-[11px] text-slate-400">
                                        {{ it.purchase_request?.pr_number }} ({{ it.purchase_request?.department?.name }})
                                    </div>
                                </td>
                                <td class="py-3.5 px-4 font-semibold text-slate-900 dark:text-white">
                                    {{ it.item_name }}
                                </td>
                                <td class="py-3.5 px-4 text-slate-700 dark:text-slate-300">
                                    {{ it.vendor?.name || it.vendor_name || 'Vendor Umum' }}
                                </td>
                                <td class="py-3.5 px-4 text-center font-bold">
                                    {{ Number(it.quantity) }} {{ it.uom }}
                                </td>
                                <td class="py-3.5 px-4 text-center font-extrabold text-emerald-600">
                                    {{ Number(it.ready_quantity) }} {{ it.uom }}
                                </td>
                                <td class="py-3.5 px-4">
                                    <div class="w-full bg-slate-100 dark:bg-slate-800 rounded-full h-2 max-w-[120px] overflow-hidden mb-1">
                                        <div
                                            class="bg-emerald-500 h-2 rounded-full"
                                            :style="{ width: `${Math.min(100, ((it.ready_quantity / it.quantity) * 100))}%` }"
                                        ></div>
                                    </div>
                                    <span class="text-[10px] font-bold text-slate-500">
                                        {{ Math.round((it.ready_quantity / it.quantity) * 100) }}% Terpenuhi
                                    </span>
                                </td>
                                <td class="py-3.5 px-4 text-right">
                                    <div class="flex items-center justify-end gap-1.5">
                                        <button
                                            v-if="it.ready_quantity < it.quantity"
                                            @click="openDeliveryModal(it)"
                                            class="px-3 py-1.5 rounded-lg bg-emerald-600 hover:bg-emerald-700 text-white text-[11px] font-semibold inline-flex items-center gap-1 shadow-xs"
                                        >
                                            <PackageCheck class="w-3.5 h-3.5" />
                                            Catat Kedatangan
                                        </button>
                                        <button
                                            v-if="it.ready_quantity > 0"
                                            @click="openReturModal(it)"
                                            class="px-2 py-1.5 rounded-lg bg-rose-50 text-rose-600 hover:bg-rose-100 text-[11px] font-semibold inline-flex items-center gap-1"
                                            title="Catat Retur Barang Cacat"
                                        >
                                            <RotateCcw class="w-3.5 h-3.5" />
                                            Retur
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div v-if="items.data?.length === 0" class="text-center py-12 text-slate-400 text-xs">
                    Belum ada item berstatus PO / Pengiriman saat ini.
                </div>
            </div>
        </div>

        <!-- Modal Catat Kedatangan Fisik -->
        <div v-if="deliveryModalOpen" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-xs">
            <div class="bg-white dark:bg-slate-900 rounded-2xl max-w-md w-full p-6 shadow-2xl border border-slate-200 dark:border-slate-800 space-y-4">
                <h3 class="text-sm font-bold text-slate-900 dark:text-white">
                    Penerimaan Fisik: {{ activeItem?.item_name }}
                </h3>
                <div>
                    <label class="block text-xs font-semibold mb-1">Nomor Surat Jalan (SJ):</label>
                    <input v-model="deliveryOrderNumber" type="text" class="w-full text-xs rounded-xl border border-slate-300 dark:border-slate-700 dark:bg-slate-800 p-2 font-mono font-bold" required />
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-semibold mb-1">Jumlah Tiba ({{ activeItem?.uom }}):</label>
                        <input v-model.number="deliveredQty" type="number" step="0.01" class="w-full text-xs rounded-xl border border-slate-300 dark:border-slate-700 dark:bg-slate-800 p-2 font-bold text-emerald-600" required />
                    </div>
                    <div>
                        <label class="block text-xs font-semibold mb-1">Tanggal Tiba Aktual:</label>
                        <input v-model="deliveryDate" type="date" class="w-full text-xs rounded-xl border border-slate-300 dark:border-slate-700 dark:bg-slate-800 p-2" required />
                    </div>
                </div>
                <div class="flex justify-end gap-2 pt-3">
                    <button @click="deliveryModalOpen = false" class="px-4 py-2 rounded-xl text-xs font-semibold text-slate-600">Batal</button>
                    <button @click="submitDelivery" :disabled="modalLoading" class="px-4 py-2 rounded-xl text-xs font-semibold bg-emerald-600 hover:bg-emerald-700 text-white">Simpan Penerimaan</button>
                </div>
            </div>
        </div>

        <!-- Modal Catat Retur -->
        <div v-if="returModalOpen" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-xs">
            <div class="bg-white dark:bg-slate-900 rounded-2xl max-w-md w-full p-6 shadow-2xl border border-slate-200 dark:border-slate-800 space-y-4">
                <h3 class="text-sm font-bold text-rose-600">
                    Catat Retur Barang Cacat: {{ activeItem?.item_name }}
                </h3>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-semibold mb-1">Jumlah Cacat ({{ activeItem?.uom }}):</label>
                        <input v-model.number="returQty" type="number" step="0.01" class="w-full text-xs rounded-xl border border-rose-300 dark:border-rose-800 dark:bg-slate-800 p-2 font-bold text-rose-600" required />
                    </div>
                    <div>
                        <label class="block text-xs font-semibold mb-1">Nomor Form Retur:</label>
                        <input v-model="returDeliveryNumber" type="text" class="w-full text-xs rounded-xl border border-slate-300 dark:border-slate-700 dark:bg-slate-800 p-2 font-mono" required />
                    </div>
                </div>
                <div>
                    <label class="block text-xs font-semibold mb-1">Alasan Penolakan / Kerusakan:</label>
                    <textarea v-model="returReason" rows="2" class="w-full text-xs rounded-xl border border-slate-300 dark:border-slate-700 dark:bg-slate-800 p-2" required></textarea>
                </div>
                <div class="flex justify-end gap-2 pt-3">
                    <button @click="returModalOpen = false" class="px-4 py-2 rounded-xl text-xs font-semibold text-slate-600">Batal</button>
                    <button @click="submitRetur" :disabled="modalLoading" class="px-4 py-2 rounded-xl text-xs font-semibold bg-rose-600 hover:bg-rose-700 text-white">Konfirmasi Retur</button>
                </div>
            </div>
        </div>
    </ModuleLayout>
</template>
