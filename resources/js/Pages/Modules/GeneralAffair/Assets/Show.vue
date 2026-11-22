<script setup>
import { ref } from 'vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import ModuleLayout from '@/Layouts/ModuleLayout.vue';
import {
  Building2,
  ArrowLeft,
  ArrowRightLeft,
  Trash2,
  Calendar,
  DollarSign,
  QrCode,
  MapPin,
  Clock,
  ShieldCheck,
  AlertTriangle,
} from 'lucide-vue-next';

const props = defineProps({
  module: { type: Object, required: true },
  asset: { type: Object, required: true },
  plants: { type: Array, default: () => [] },
  departments: { type: Array, default: () => [] },
  locations: { type: Array, default: () => [] },
});

const isMutateModalOpen = ref(false);
const isDisposeModalOpen = ref(false);

const mutateForm = useForm({
  to_plant_id: props.asset.plant_id,
  to_department_id: props.asset.department_id,
  to_location_id: props.asset.location_id,
  pic_receiver: props.asset.pic_name || '',
  reason: '',
  mutation_date: new Date().toISOString().substring(0, 10),
});

const disposeForm = useForm({
  reason: '',
  scrap_value: 0,
});

const submitMutate = () => {
  mutateForm.post(`/general-affair/assets/${props.asset.id}/mutate`, {
    onSuccess: () => {
      isMutateModalOpen.value = false;
    },
  });
};

const submitDispose = () => {
  disposeForm.post(`/general-affair/assets/${props.asset.id}/dispose`, {
    onSuccess: () => {
      isDisposeModalOpen.value = false;
    },
  });
};
</script>

<template>
  <Head :title="`Aset ${asset.asset_code}`" />

  <ModuleLayout :module="module">
    <div class="max-w-5xl mx-auto space-y-6">
      <!-- Top Bar -->
      <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div class="flex items-center gap-3">
          <Link
            href="/general-affair/assets"
            class="rounded-lg p-2 text-slate-500 hover:bg-slate-100 dark:hover:bg-slate-800 transition"
          >
            <ArrowLeft class="w-5 h-5" />
          </Link>
          <div>
            <div class="flex items-center gap-3">
              <h1 class="text-xl font-bold tracking-tight text-slate-900 dark:text-white sm:text-2xl">
                {{ asset.name }}
              </h1>
              <span class="inline-flex items-center rounded-full bg-amber-50 px-2.5 py-0.5 text-xs font-semibold text-amber-700 border border-amber-200 dark:bg-amber-950/40 dark:text-amber-300">
                {{ asset.condition }}
              </span>
            </div>
            <p class="text-sm font-mono text-amber-600 dark:text-amber-400">
              {{ asset.asset_code }} &bull; {{ asset.category?.name ?? 'Kategori' }}
            </p>
          </div>
        </div>

        <div class="flex items-center gap-2">
          <button
            v-if="asset.condition !== 'DISPOSED'"
            @click="isMutateModalOpen = true"
            class="inline-flex items-center gap-2 rounded-lg bg-blue-600 px-3.5 py-2 text-sm font-semibold text-white hover:bg-blue-500 shadow-sm transition"
          >
            <ArrowRightLeft class="w-4 h-4" />
            Mutasi Aset
          </button>
          <button
            v-if="asset.condition !== 'DISPOSED'"
            @click="isDisposeModalOpen = true"
            class="inline-flex items-center gap-2 rounded-lg bg-red-600 px-3.5 py-2 text-sm font-semibold text-white hover:bg-red-500 shadow-sm transition"
          >
            <Trash2 class="w-4 h-4" />
            Disposal / Hapus
          </button>
        </div>
      </div>

      <!-- Depreciation & Valuation Cards -->
      <div class="grid grid-cols-1 sm:grid-cols-4 gap-4">
        <div class="rounded-xl border border-slate-200 bg-white p-4 dark:border-slate-800 dark:bg-slate-900">
          <span class="text-xs font-medium text-slate-500">Harga Perolehan Awal</span>
          <p class="mt-1 text-lg font-bold text-slate-900 dark:text-white">
            Rp {{ Number(asset.purchase_cost).toLocaleString('id-ID') }}
          </p>
          <span class="text-[11px] text-slate-400">Tgl: {{ asset.purchase_date ? new Date(asset.purchase_date).toLocaleDateString('id-ID') : '-' }}</span>
        </div>

        <div class="rounded-xl border border-slate-200 bg-white p-4 dark:border-slate-800 dark:bg-slate-900">
          <span class="text-xs font-medium text-slate-500">Penyusutan per Bulan</span>
          <p class="mt-1 text-lg font-bold text-amber-600 dark:text-amber-400">
            Rp {{ Number(asset.monthly_depreciation).toLocaleString('id-ID') }}
          </p>
          <span class="text-[11px] text-slate-400">Masa: {{ asset.useful_life_years }} Thn ({{ asset.useful_life_years * 12 }} Bln)</span>
        </div>

        <div class="rounded-xl border border-slate-200 bg-white p-4 dark:border-slate-800 dark:bg-slate-900">
          <span class="text-xs font-medium text-slate-500">Akumulasi Depresiasi</span>
          <p class="mt-1 text-lg font-bold text-red-600 dark:text-red-400">
            Rp {{ Number(asset.accumulated_depreciation).toLocaleString('id-ID') }}
          </p>
          <span class="text-[11px] text-slate-400">Terpakai: {{ asset.months_used }} Bulan</span>
        </div>

        <div class="rounded-xl border border-slate-200 bg-white p-4 dark:border-slate-800 dark:bg-slate-900">
          <span class="text-xs font-medium text-slate-500">Nilai Buku Bersih (Saat Ini)</span>
          <p class="mt-1 text-lg font-bold text-emerald-600 dark:text-emerald-400">
            Rp {{ Number(asset.book_value).toLocaleString('id-ID') }}
          </p>
          <span class="text-[11px] text-emerald-500 font-medium">Straight-Line Method</span>
        </div>
      </div>

      <!-- Detail Card & Barcode QR -->
      <div class="grid grid-cols-1 sm:grid-cols-3 gap-6">
        <div class="sm:col-span-2 rounded-xl border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900 space-y-4">
          <h2 class="text-base font-bold text-slate-900 dark:text-white">Identitas & Penempatan Fisik</h2>
          <div class="grid grid-cols-2 gap-4 text-xs">
            <div>
              <span class="text-slate-400 block">Pabrik (Plant):</span>
              <span class="font-semibold text-slate-800 dark:text-slate-200">{{ asset.plant?.name ?? '-' }} (Kode: {{ asset.plant?.code ?? '-' }})</span>
            </div>
            <div>
              <span class="text-slate-400 block">Departemen:</span>
              <span class="font-semibold text-slate-800 dark:text-slate-200">{{ asset.department?.name ?? '-' }}</span>
            </div>
            <div>
              <span class="text-slate-400 block">Ruangan / Fasilitas:</span>
              <span class="font-semibold text-slate-800 dark:text-slate-200">{{ asset.location?.name ?? 'Belum Ditentukan' }}</span>
            </div>
            <div>
              <span class="text-slate-400 block">PIC Penanggung Jawab:</span>
              <span class="font-semibold text-slate-800 dark:text-slate-200">{{ asset.pic_name ?? '-' }}</span>
            </div>
            <div>
              <span class="text-slate-400 block">Golongan Pajak:</span>
              <span class="font-semibold text-slate-800 dark:text-slate-200">{{ asset.category?.golongan_pajak ?? '-' }}</span>
            </div>
            <div>
              <span class="text-slate-400 block">Klasifikasi:</span>
              <span class="font-semibold text-slate-800 dark:text-slate-200">{{ asset.classification }}</span>
            </div>
          </div>

          <div v-if="asset.notes" class="pt-2 border-t border-slate-100 dark:border-slate-800">
            <span class="text-xs text-slate-400 block mb-1">Catatan & Riwayat Spesifikasi:</span>
            <pre class="text-xs text-slate-700 dark:text-slate-300 whitespace-pre-wrap font-sans bg-slate-50 dark:bg-slate-800 p-3 rounded-lg">{{ asset.notes }}</pre>
          </div>
        </div>

        <div class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900 flex flex-col items-center justify-center text-center space-y-3">
          <div class="p-4 bg-slate-50 dark:bg-slate-800 rounded-xl border border-dashed border-slate-300 dark:border-slate-700">
            <QrCode class="w-24 h-24 text-amber-600 dark:text-amber-400" />
          </div>
          <div>
            <span class="text-xs font-mono font-bold text-slate-900 dark:text-white block">{{ asset.asset_code }}</span>
            <span class="text-[11px] text-slate-400">Barcode / QR Label Aset</span>
          </div>
        </div>
      </div>

      <!-- Mutation History Table -->
      <div class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900 space-y-4">
        <h2 class="text-base font-bold text-slate-900 dark:text-white flex items-center gap-2">
          <ArrowRightLeft class="w-5 h-5 text-blue-600" />
          Riwayat Mutasi & Perpindahan Aset
        </h2>

        <div class="overflow-x-auto">
          <table class="w-full text-left text-xs">
            <thead class="bg-slate-50 dark:bg-slate-800 text-slate-500 uppercase">
              <tr>
                <th class="p-3">Tgl Mutasi</th>
                <th class="p-3">Dari (Plant / Lokasi)</th>
                <th class="p-3">Ke (Plant / Lokasi)</th>
                <th class="p-3">Penerima (PIC)</th>
                <th class="p-3">Alasan Mutasi</th>
                <th class="p-3">Diproses Oleh</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
              <tr v-for="mut in asset.mutations" :key="mut.id">
                <td class="p-3 font-semibold">{{ mut.mutation_date }}</td>
                <td class="p-3">{{ mut.from_plant?.name ?? '-' }} / {{ mut.from_location?.name ?? mut.from_department?.name ?? '-' }}</td>
                <td class="p-3 font-medium text-blue-600 dark:text-blue-400">{{ mut.to_plant?.name ?? '-' }} / {{ mut.to_location?.name ?? mut.to_department?.name ?? '-' }}</td>
                <td class="p-3 font-semibold">{{ mut.pic_receiver }}</td>
                <td class="p-3">{{ mut.reason }}</td>
                <td class="p-3 text-slate-400">{{ mut.transferred_by_user?.name ?? 'Admin GA' }}</td>
              </tr>
              <tr v-if="!asset.mutations || asset.mutations.length === 0">
                <td colspan="6" class="p-4 text-center text-slate-400">Belum ada riwayat mutasi untuk aset ini.</td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

      <!-- Modal Mutasi -->
      <div v-if="isMutateModalOpen" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4">
        <div class="w-full max-w-lg rounded-xl bg-white p-6 shadow-xl dark:bg-slate-900 space-y-4">
          <h3 class="text-base font-bold text-slate-900 dark:text-white">Mutasi Aset Tetap</h3>
          <form @submit.prevent="submitMutate" class="space-y-4 text-xs">
            <div>
              <label class="block font-semibold mb-1">Pindah ke Pabrik (Plant):</label>
              <select v-model="mutateForm.to_plant_id" class="w-full rounded border p-2 bg-slate-50 dark:bg-slate-800">
                <option v-for="p in plants" :key="p.id" :value="p.id">{{ p.name }}</option>
              </select>
            </div>
            <div>
              <label class="block font-semibold mb-1">Pindah ke Departemen:</label>
              <select v-model="mutateForm.to_department_id" class="w-full rounded border p-2 bg-slate-50 dark:bg-slate-800">
                <option v-for="d in departments" :key="d.id" :value="d.id">{{ d.name }}</option>
              </select>
            </div>
            <div>
              <label class="block font-semibold mb-1">Pindah ke Ruangan (Lokasi):</label>
              <select v-model="mutateForm.to_location_id" class="w-full rounded border p-2 bg-slate-50 dark:bg-slate-800">
                <option :value="null">-- Belum Ditentukan --</option>
                <option v-for="l in locations" :key="l.id" :value="l.id">{{ l.name }}</option>
              </select>
            </div>
            <div>
              <label class="block font-semibold mb-1">Nama PIC Penerima Baru:</label>
              <input v-model="mutateForm.pic_receiver" type="text" class="w-full rounded border p-2 bg-slate-50 dark:bg-slate-800" required />
            </div>
            <div>
              <label class="block font-semibold mb-1">Alasan Pemindahan / Mutasi:</label>
              <textarea v-model="mutateForm.reason" rows="2" class="w-full rounded border p-2 bg-slate-50 dark:bg-slate-800" required></textarea>
            </div>
            <div class="flex justify-end gap-2 pt-2">
              <button type="button" @click="isMutateModalOpen = false" class="px-4 py-2 border rounded">Batal</button>
              <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded font-semibold">Proses Mutasi</button>
            </div>
          </form>
        </div>
      </div>

      <!-- Modal Dispose -->
      <div v-if="isDisposeModalOpen" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4">
        <div class="w-full max-w-md rounded-xl bg-white p-6 shadow-xl dark:bg-slate-900 space-y-4">
          <h3 class="text-base font-bold text-red-600">Disposal / Penghapusan Aset Tetap</h3>
          <p class="text-xs text-slate-500">Aset yang dihapuskan akan berhenti disusutkan dan nilai buku diset ke Rp 0.</p>
          <form @submit.prevent="submitDispose" class="space-y-4 text-xs">
            <div>
              <label class="block font-semibold mb-1">Alasan Penghapusan (Rusak total / Dijual / Hilang):</label>
              <textarea v-model="disposeForm.reason" rows="2" class="w-full rounded border p-2 bg-slate-50 dark:bg-slate-800" required></textarea>
            </div>
            <div>
              <label class="block font-semibold mb-1">Nilai Sisa / Penjualan Scrap (Rp):</label>
              <input v-model.number="disposeForm.scrap_value" type="number" min="0" class="w-full rounded border p-2 bg-slate-50 dark:bg-slate-800" />
            </div>
            <div class="flex justify-end gap-2 pt-2">
              <button type="button" @click="isDisposeModalOpen = false" class="px-4 py-2 border rounded">Batal</button>
              <button type="submit" class="px-4 py-2 bg-red-600 text-white rounded font-semibold">Konfirmasi Hapus Aset</button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </ModuleLayout>
</template>
