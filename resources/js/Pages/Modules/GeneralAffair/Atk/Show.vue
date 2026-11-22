<script setup>
import { ref } from 'vue';
import { Head, Link, useForm, router } from '@inertiajs/vue3';
import ModuleLayout from '@/Layouts/ModuleLayout.vue';
import {
  Package,
  ArrowLeft,
  FileText,
  CheckCircle2,
  AlertTriangle,
  RotateCcw,
  Check,
  X,
  Send,
  UserCheck,
  Truck,
} from 'lucide-vue-next';

const props = defineProps({
  module: { type: Object, required: true },
  request: { type: Object, required: true },
});

// Manager Approval Form
const managerApprovalForm = useForm({
  action: 'approve', // approve, reject, revision
  notes: '',
  items: props.request.items?.map(it => ({
    id: it.id,
    approved_quantity: it.approved_quantity ?? it.quantity,
    rejection_note: it.rejection_note || '',
  })) || [],
});

// Admin GA Approval Form
const adminApprovalForm = useForm({
  notes: '',
});

// Handover Form
const handoverForm = useForm({
  receiver_name: props.request.user?.name || '',
  receiver_signature: 'Digital Signature - Penerima Barang',
});

// Revision Form
const isRevisionModalOpen = ref(false);
const revisionForm = useForm({
  alasan_pengajuan: props.request.alasan_pengajuan || '',
  items: props.request.items?.map(it => ({
    atk_item_id: it.atk_item_id,
    custom_item_name: it.custom_item_name,
    quantity: it.approved_quantity > 0 ? it.approved_quantity : it.quantity,
    uom_id: it.uom_id,
  })) || [],
});

const submitManagerDecision = (action) => {
  managerApprovalForm.action = action;
  managerApprovalForm.post(`/general-affair/atk/${props.request.id}/approve-manager`);
};

const submitAdminApproval = () => {
  adminApprovalForm.post(`/general-affair/atk/${props.request.id}/approve-admin`);
};

const submitHandover = () => {
  handoverForm.post(`/general-affair/atk/${props.request.id}/handover`);
};

const submitRevision = () => {
  revisionForm.post(`/general-affair/atk/${props.request.id}/revision`, {
    onSuccess: () => {
      isRevisionModalOpen.value = false;
    },
  });
};
</script>

<template>
  <Head :title="`Permintaan ${request.request_number}`" />

  <ModuleLayout :module="module">
    <div class="max-w-4xl mx-auto space-y-6">
      <!-- Top Bar -->
      <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div class="flex items-center gap-3">
          <Link
            href="/general-affair/atk"
            class="rounded-lg p-2 text-slate-500 hover:bg-slate-100 dark:hover:bg-slate-800 transition"
          >
            <ArrowLeft class="w-5 h-5" />
          </Link>
          <div>
            <div class="flex items-center gap-3">
              <h1 class="text-xl font-bold tracking-tight text-slate-900 dark:text-white sm:text-2xl">
                {{ request.request_number }}
              </h1>
              <span class="inline-flex items-center rounded-full bg-amber-50 px-2.5 py-0.5 text-xs font-semibold text-amber-700 border border-amber-200 dark:bg-amber-950/40 dark:text-amber-300">
                {{ request.status }}
              </span>
            </div>
            <p class="text-sm text-slate-500 dark:text-slate-400">
              Jenis: {{ request.type }} &bull; Pemohon: {{ request.user?.name ?? '-' }} ({{ request.department?.name ?? '-' }})
            </p>
          </div>
        </div>

        <div class="flex items-center gap-2">
          <a
            :href="`/general-affair/atk/${request.id}/pdf`"
            target="_blank"
            class="inline-flex items-center gap-2 rounded-lg border border-slate-200 bg-white px-3.5 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50 shadow-sm dark:border-slate-700 dark:bg-slate-800 dark:text-slate-300"
          >
            <FileText class="w-4 h-4 text-amber-600" />
            Cetak Formulir (PDF)
          </a>
          <button
            v-if="request.status === 'Revisi Diminta'"
            @click="isRevisionModalOpen = true"
            class="inline-flex items-center gap-2 rounded-lg bg-purple-600 px-3.5 py-2 text-sm font-semibold text-white hover:bg-purple-500 shadow-sm transition"
          >
            <RotateCcw class="w-4 h-4" />
            Buat Revisi (-R1)
          </button>
        </div>
      </div>

      <!-- Detail Card -->
      <div class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900 space-y-4">
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 text-sm bg-slate-50 dark:bg-slate-800/50 p-4 rounded-lg">
          <div>
            <span class="text-xs text-slate-400 block">Departemen:</span>
            <span class="font-medium text-slate-800 dark:text-slate-200">{{ request.department?.name ?? '-' }}</span>
          </div>
          <div>
            <span class="text-xs text-slate-400 block">Tanggal Pengajuan:</span>
            <span class="font-medium text-slate-800 dark:text-slate-200">{{ request.tanggal_pengajuan ? new Date(request.tanggal_pengajuan).toLocaleDateString('id-ID') : '-' }}</span>
          </div>
          <div>
            <span class="text-xs text-slate-400 block">Diserahkan Pada:</span>
            <span class="font-medium text-slate-800 dark:text-slate-200">{{ request.diberikan_pada ? new Date(request.diberikan_pada).toLocaleDateString('id-ID') : 'Belum Diserahkan' }}</span>
          </div>
        </div>

        <div>
          <span class="text-xs text-slate-400 block mb-1">Alasan Pengajuan:</span>
          <p class="text-sm text-slate-800 dark:text-slate-200 bg-white dark:bg-slate-800 p-3 rounded-lg border border-slate-200 dark:border-slate-700">
            {{ request.alasan_pengajuan }}
          </p>
        </div>

        <!-- Items Table -->
        <div class="space-y-2 pt-2">
          <h3 class="text-sm font-bold text-slate-900 dark:text-white">Daftar Barang Diminta & Persetujuan</h3>
          <div class="overflow-x-auto rounded-lg border border-slate-200 dark:border-slate-700">
            <table class="w-full text-xs text-left">
              <thead class="bg-slate-50 dark:bg-slate-800 text-slate-500 uppercase">
                <tr>
                  <th class="p-3">Nama Barang</th>
                  <th class="p-3 text-right">Diajukan</th>
                  <th class="p-3 text-right">Disetujui Manager</th>
                  <th class="p-3">Satuan</th>
                  <th class="p-3">Stok Saat Ini</th>
                  <th class="p-3">Keterangan</th>
                </tr>
              </thead>
              <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                <tr v-for="(it, idx) in request.items" :key="it.id">
                  <td class="p-3 font-medium text-slate-900 dark:text-white">{{ it.item_display_name }}</td>
                  <td class="p-3 text-right">{{ it.quantity }}</td>
                  <td class="p-3 text-right font-bold text-emerald-600 dark:text-emerald-400">
                    {{ it.approved_quantity }}
                  </td>
                  <td class="p-3">{{ it.uom?.code ?? 'Pcs' }}</td>
                  <td class="p-3">
                    <span v-if="it.atk_item" class="font-semibold" :class="it.atk_item.stock_actual < it.quantity ? 'text-red-500' : 'text-slate-700 dark:text-slate-300'">
                      {{ it.atk_item.stock_actual }}
                    </span>
                    <span v-else class="text-slate-400">-</span>
                  </td>
                  <td class="p-3 text-slate-500">{{ it.rejection_note ?? '-' }}</td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>

      <!-- Manager Approval Section -->
      <div v-if="request.status === 'Menunggu Persetujuan Manager'" class="rounded-xl border border-amber-200 bg-amber-50/40 p-6 dark:border-amber-900/50 dark:bg-amber-950/20 space-y-4">
        <h2 class="text-base font-bold text-amber-900 dark:text-amber-300 flex items-center gap-2">
          <UserCheck class="w-5 h-5 text-amber-600" />
          Pemeriksaan & Otorisasi Manager Departemen
        </h2>

        <div class="space-y-3">
          <span class="text-xs font-semibold text-slate-700 dark:text-slate-300">Sesuaikan kuantitas barang yang disetujui:</span>
          <div v-for="(it, idx) in managerApprovalForm.items" :key="it.id" class="grid grid-cols-12 gap-3 items-center">
            <div class="col-span-6 text-xs font-medium text-slate-800 dark:text-slate-200">
              {{ request.items[idx]?.item_display_name }} (Diminta: {{ request.items[idx]?.quantity }})
            </div>
            <div class="col-span-3">
              <input
                v-model.number="it.approved_quantity"
                type="number"
                min="0"
                class="w-full rounded border border-slate-300 bg-white p-1.5 text-xs text-slate-900"
              />
            </div>
            <div class="col-span-3">
              <input
                v-model="it.rejection_note"
                type="text"
                placeholder="Catatan penyesuaian..."
                class="w-full rounded border border-slate-300 bg-white p-1.5 text-xs text-slate-900"
              />
            </div>
          </div>
        </div>

        <div>
          <label class="block text-xs font-medium text-slate-600 dark:text-slate-300 mb-1">Catatan Manager:</label>
          <input
            v-model="managerApprovalForm.notes"
            type="text"
            placeholder="Catatan umum persetujuan / revisi..."
            class="w-full rounded-lg border border-slate-300 bg-white p-2 text-sm text-slate-900"
          />
        </div>

        <div class="flex items-center justify-end gap-2 pt-2">
          <button
            type="button"
            @click="submitManagerDecision('reject')"
            class="px-4 py-2 bg-red-600 text-white rounded-lg text-xs font-semibold hover:bg-red-500 transition"
          >
            Tolak Pengajuan
          </button>
          <button
            type="button"
            @click="submitManagerDecision('revision')"
            class="px-4 py-2 bg-purple-600 text-white rounded-lg text-xs font-semibold hover:bg-purple-500 transition"
          >
            Minta Revisi
          </button>
          <button
            type="button"
            @click="submitManagerDecision('approve')"
            class="px-5 py-2 bg-emerald-600 text-white rounded-lg text-xs font-semibold hover:bg-emerald-500 transition"
          >
            Setujui Pengajuan
          </button>
        </div>
      </div>

      <!-- Admin GA Approval Section -->
      <div v-if="request.status === 'Menunggu Admin GA'" class="rounded-xl border border-indigo-200 bg-indigo-50/40 p-6 dark:border-indigo-900/50 dark:bg-indigo-950/20 space-y-4">
        <h2 class="text-base font-bold text-indigo-900 dark:text-indigo-300 flex items-center gap-2">
          <CheckCircle2 class="w-5 h-5 text-indigo-600" />
          Verifikasi Stok & Persetujuan Admin GA
        </h2>
        <p class="text-xs text-slate-600 dark:text-slate-400">
          Manager telah menyetujui. Pastikan stok fisik tersedia di gudang GA sebelum memberikan persetujuan penyerahan.
        </p>
        <div class="flex items-center gap-3">
          <input
            v-model="adminApprovalForm.notes"
            type="text"
            placeholder="Catatan persiapan barang / jadwal pengambilan..."
            class="flex-1 rounded-lg border border-slate-300 bg-white p-2 text-sm text-slate-900"
          />
          <button
            @click="submitAdminApproval"
            :disabled="adminApprovalForm.processing"
            class="px-5 py-2 bg-indigo-600 text-white rounded-lg text-xs font-semibold hover:bg-indigo-500 transition"
          >
            Setujui Siap Diambil
          </button>
        </div>
      </div>

      <!-- Handover / Penyerahan Section -->
      <div v-if="request.status === 'Disetujui Admin GA'" class="rounded-xl border border-emerald-200 bg-emerald-50/40 p-6 dark:border-emerald-900/50 dark:bg-emerald-950/20 space-y-4">
        <h2 class="text-base font-bold text-emerald-900 dark:text-emerald-300 flex items-center gap-2">
          <Truck class="w-5 h-5 text-emerald-600" />
          Distribusi & Penyerahan Barang (Potong Stok Otomatis)
        </h2>
        <form @submit.prevent="submitHandover" class="flex flex-col sm:flex-row gap-3 items-end">
          <div class="flex-1">
            <label class="block text-xs font-medium text-slate-600 dark:text-slate-300 mb-1">Nama Penerima Barang:</label>
            <input
              v-model="handoverForm.receiver_name"
              type="text"
              class="w-full rounded-lg border border-slate-300 bg-white p-2 text-sm text-slate-900"
              required
            />
          </div>
          <button
            type="submit"
            :disabled="handoverForm.processing"
            class="px-5 py-2.5 bg-emerald-600 text-white rounded-lg text-xs font-semibold hover:bg-emerald-500 transition"
          >
            Konfirmasi Serah Terima
          </button>
        </form>
      </div>
    </div>
  </ModuleLayout>
</template>
