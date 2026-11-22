<script setup>
import { ref } from 'vue';
import { Head, Link, useForm, router } from '@inertiajs/vue3';
import ModuleLayout from '@/Layouts/ModuleLayout.vue';
import {
  MapPin,
  Scan,
  CheckCircle2,
  Clock,
  Sparkles,
  Check,
} from 'lucide-vue-next';

const props = defineProps({
  module: { type: Object, required: true },
  checkpoints: { type: Array, default: () => [] },
  logs: { type: Object, required: true },
  summary: { type: Object, default: () => ({}) },
  plants: { type: Array, default: () => [] },
  selectedPlantId: { type: Number, default: null },
  filters: { type: Object, default: () => ({}) },
});

const isScanModalOpen = ref(false);
const activePlant = ref(props.selectedPlantId || '');

const scanForm = useForm({
  qr_token: '',
  checkpoint_id: null,
  is_sanitized: true,
  checklist_json: {
    lantai: true,
    tempat_sampah: true,
    aroma: true,
    refill_sabun: true,
  },
  notes: 'Area telah disanitasi sesuai standar 5R CPOB.',
});

const openScanModal = (cp = null) => {
  if (cp) {
    scanForm.qr_token = cp.qr_token;
    scanForm.checkpoint_id = cp.id;
  } else {
    scanForm.qr_token = props.checkpoints[0]?.qr_token || '';
    scanForm.checkpoint_id = props.checkpoints[0]?.id || null;
  }
  isScanModalOpen.value = true;
};

const submitScan = () => {
  scanForm.post('/general-affair/patrols/cleaning/scan', {
    onSuccess: () => {
      isScanModalOpen.value = false;
      scanForm.reset();
    },
  });
};

const onPlantFilterChange = () => {
  router.get('/general-affair/patrols/cleaning', {
    plant_id: activePlant.value || undefined,
  }, { preserveState: true });
};
</script>

<template>
  <Head title="Pemeriksaan Kebersihan & Sanitasi 5R - General Affair" />

  <ModuleLayout :module="module">
    <div class="space-y-6">
      <!-- Top Bar -->
      <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
          <h1 class="text-xl font-bold tracking-tight text-slate-900 dark:text-white sm:text-2xl">
            Pemeriksaan Sanitasi & Kebersihan 5R
          </h1>
          <p class="text-sm text-slate-500 dark:text-slate-400">
            Checklist digital inspeksi toilet, koridor, ruang ganti (locker), kantin, dan area fasilitas umum pabrik.
          </p>
        </div>
        <div class="flex items-center gap-3">
          <Link
            href="/general-affair/patrols/security"
            class="inline-flex items-center gap-2 rounded-lg border border-slate-200 bg-white px-3.5 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-300"
          >
            Lihat Keamanan QR
          </Link>
          <button
            @click="openScanModal()"
            class="inline-flex items-center gap-2 rounded-lg bg-teal-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-teal-500 transition"
          >
            <Sparkles class="h-4 w-4" />
            Catat Pemeriksaan Sanitasi
          </button>
        </div>
      </div>

      <!-- Stats Cards -->
      <div class="grid grid-cols-1 sm:grid-cols-4 gap-4">
        <div class="rounded-xl border border-slate-200 bg-white p-4 dark:border-slate-800 dark:bg-slate-900">
          <span class="text-xs font-medium text-slate-500">Total Titik Kebersihan</span>
          <p class="mt-1 text-2xl font-bold text-slate-900 dark:text-white">{{ summary.total_checkpoints ?? 0 }} Titik</p>
        </div>
        <div class="rounded-xl border border-slate-200 bg-white p-4 dark:border-slate-800 dark:bg-slate-900">
          <span class="text-xs font-medium text-teal-600 dark:text-teal-400">Terscan Hari Ini</span>
          <p class="mt-1 text-2xl font-bold text-teal-600 dark:text-teal-400">{{ summary.scanned_today ?? 0 }} Titik</p>
        </div>
        <div class="rounded-xl border border-slate-200 bg-white p-4 dark:border-slate-800 dark:bg-slate-900">
          <span class="text-xs font-medium text-emerald-600 dark:text-emerald-400">Kepatuhan Jadwal</span>
          <p class="mt-1 text-2xl font-bold text-emerald-600 dark:text-emerald-400">{{ summary.compliance_percent ?? 100 }}%</p>
        </div>
        <div class="rounded-xl border border-slate-200 bg-white p-4 dark:border-slate-800 dark:bg-slate-900">
          <span class="text-xs font-medium text-sky-600 dark:text-sky-400">Tingkat Sanitasi Memenuhi</span>
          <p class="mt-1 text-2xl font-bold text-sky-600 dark:text-sky-400">{{ summary.sanitized_rate ?? 100 }}%</p>
        </div>
      </div>

      <!-- Plant Selector -->
      <div class="flex items-center gap-3">
        <span class="text-xs font-medium text-slate-500">Filter Pabrik:</span>
        <select
          v-model="activePlant"
          @change="onPlantFilterChange"
          class="rounded-lg border border-slate-200 bg-white px-3 py-1.5 text-xs text-slate-900 dark:border-slate-700 dark:bg-slate-800 dark:text-white"
        >
          <option value="">Semua Pabrik</option>
          <option v-for="p in plants" :key="p.id" :value="p.id">{{ p.name }}</option>
        </select>
      </div>

      <!-- Checkpoints Grid -->
      <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
        <div
          v-for="cp in checkpoints"
          :key="cp.id"
          class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-900 flex flex-col justify-between space-y-3"
        >
          <div>
            <div class="flex items-center justify-between mb-1">
              <span class="font-mono text-xs font-bold text-teal-600 dark:text-teal-400">{{ cp.code }}</span>
              <span class="text-[11px] px-2 py-0.5 rounded bg-teal-50 text-teal-700 dark:bg-teal-950 dark:text-teal-300 font-semibold">
                {{ cp.plant?.name }}
              </span>
            </div>
            <h3 class="text-sm font-bold text-slate-900 dark:text-white">{{ cp.name }}</h3>
          </div>

          <div class="pt-3 border-t border-slate-100 dark:border-slate-800 flex items-center justify-between">
            <span class="text-[11px] font-mono text-slate-400">Token: {{ cp.qr_token }}</span>
            <button
              @click="openScanModal(cp)"
              class="inline-flex items-center gap-1 px-2.5 py-1 text-xs font-semibold bg-teal-50 text-teal-700 rounded hover:bg-teal-100 dark:bg-teal-950 dark:text-teal-300 transition"
            >
              <Sparkles class="w-3.5 h-3.5" />
              Checklist Sanitasi
            </button>
          </div>
        </div>
      </div>

      <!-- Logs Table -->
      <div class="rounded-xl border border-slate-200 bg-white shadow-sm dark:border-slate-800 dark:bg-slate-900 space-y-4 p-5">
        <h2 class="text-base font-bold text-slate-900 dark:text-white flex items-center gap-2">
          <Clock class="w-5 h-5 text-teal-600" />
          Log Riwayat Pemeriksaan Kebersihan
        </h2>

        <div class="overflow-x-auto">
          <table class="w-full text-left text-xs">
            <thead class="bg-slate-50 dark:bg-slate-800 text-slate-500 uppercase">
              <tr>
                <th class="p-3">Waktu Cek</th>
                <th class="p-3">Titik Fasilitas</th>
                <th class="p-3">Petugas Kebersihan</th>
                <th class="p-3">Status Sanitasi</th>
                <th class="p-3">Checklist 5R</th>
                <th class="p-3">Catatan</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
              <tr v-for="log in logs.data" :key="log.id">
                <td class="p-3 font-semibold">{{ log.scanned_at ? new Date(log.scanned_at).toLocaleString('id-ID') : '-' }}</td>
                <td class="p-3">
                  <span class="font-medium text-slate-900 dark:text-white">{{ log.checkpoint?.name }}</span>
                  <span class="block text-[11px] text-slate-400">{{ log.checkpoint?.code }} &bull; {{ log.checkpoint?.plant?.name }}</span>
                </td>
                <td class="p-3">{{ log.user?.name ?? 'Petugas Kebersihan' }}</td>
                <td class="p-3">
                  <span
                    :class="[
                      'px-2 py-0.5 rounded text-[11px] font-bold',
                      log.is_sanitized ? 'bg-emerald-100 text-emerald-700' : 'bg-red-100 text-red-700'
                    ]"
                  >
                    {{ log.is_sanitized ? 'MEMENUHI' : 'PERLU PEMBERSIHAN ULANG' }}
                  </span>
                </td>
                <td class="p-3 text-[11px]">
                  <span class="text-slate-600 dark:text-slate-300">
                    Lantai: {{ log.checklist_json?.lantai ? '✓' : '✗' }} |
                    Sampah: {{ log.checklist_json?.tempat_sampah ? '✓' : '✗' }} |
                    Aroma: {{ log.checklist_json?.aroma ? '✓' : '✗' }} |
                    Sabun: {{ log.checklist_json?.refill_sabun ? '✓' : '✗' }}
                  </span>
                </td>
                <td class="p-3 text-slate-500">{{ log.notes ?? '-' }}</td>
              </tr>
              <tr v-if="!logs.data || logs.data.length === 0">
                <td colspan="6" class="p-4 text-center text-slate-400">Belum ada catatan sanitasi.</td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

      <!-- Scan Modal -->
      <div v-if="isScanModalOpen" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4">
        <div class="w-full max-w-md rounded-xl bg-white p-6 shadow-xl dark:bg-slate-900 space-y-4">
          <h3 class="text-base font-bold text-slate-900 dark:text-white flex items-center gap-2">
            <Sparkles class="w-5 h-5 text-teal-600" />
            Checklist Pemeriksaan Sanitasi 5R
          </h3>
          <form @submit.prevent="submitScan" class="space-y-4 text-xs">
            <div>
              <label class="block font-semibold mb-1">QR Token / Kode Checkpoint:</label>
              <input v-model="scanForm.qr_token" type="text" class="w-full rounded border p-2 bg-slate-50 dark:bg-slate-800 font-mono" required />
            </div>

            <!-- Checkboxes Checklist -->
            <div class="p-3 rounded-lg border bg-slate-50 dark:bg-slate-800 space-y-2">
              <span class="font-bold block text-slate-700 dark:text-slate-300">Poin Evaluasi Kebersihan:</span>
              <div class="flex items-center gap-2">
                <input type="checkbox" id="chkLantai" v-model="scanForm.checklist_json.lantai" class="rounded text-teal-600" />
                <label for="chkLantai">Lantai bersih, disapu & dipel bebas debu/noda</label>
              </div>
              <div class="flex items-center gap-2">
                <input type="checkbox" id="chkSampah" v-model="scanForm.checklist_json.tempat_sampah" class="rounded text-teal-600" />
                <label for="chkSampah">Tempat sampah dikosongkan & plastik diganti</label>
              </div>
              <div class="flex items-center gap-2">
                <input type="checkbox" id="chkAroma" v-model="scanForm.checklist_json.aroma" class="rounded text-teal-600" />
                <label for="chkAroma">Sirkulasi udara baik, tidak berbau tidak sedap</label>
              </div>
              <div class="flex items-center gap-2">
                <input type="checkbox" id="chkSabun" v-model="scanForm.checklist_json.refill_sabun" class="rounded text-teal-600" />
                <label for="chkSabun">Sabun cuci tangan & tisu telah diisi ulang</label>
              </div>
            </div>

            <div class="flex items-center gap-2">
              <input type="checkbox" id="isSanitized" v-model="scanForm.is_sanitized" class="rounded text-teal-600" />
              <label for="isSanitized" class="font-bold text-teal-700 dark:text-teal-300">Hasil: Sanitasi Memenuhi Standar</label>
            </div>

            <div>
              <label class="block font-semibold mb-1">Catatan Tambahan:</label>
              <textarea v-model="scanForm.notes" rows="2" class="w-full rounded border p-2 bg-slate-50 dark:bg-slate-800" placeholder="Catatan inspektur..."></textarea>
            </div>

            <div class="flex justify-end gap-2 pt-2">
              <button type="button" @click="isScanModalOpen = false" class="px-4 py-2 border rounded">Batal</button>
              <button type="submit" class="px-4 py-2 bg-teal-600 text-white rounded font-semibold">Simpan Sanitasi</button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </ModuleLayout>
</template>
