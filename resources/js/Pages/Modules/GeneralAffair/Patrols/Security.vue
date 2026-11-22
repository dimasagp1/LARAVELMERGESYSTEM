<script setup>
import { ref } from 'vue';
import { Head, Link, useForm, router } from '@inertiajs/vue3';
import ModuleLayout from '@/Layouts/ModuleLayout.vue';
import {
  ShieldCheck,
  Scan,
  AlertTriangle,
  Clock,
  QrCode,
  CheckCircle2,
  Filter,
  Plus,
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
  duration_seconds: 450,
  is_anomaly: false,
  anomaly_notes: '',
  evidence_photo_path: '',
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
  scanForm.post('/general-affair/patrols/security/scan', {
    onSuccess: () => {
      isScanModalOpen.value = false;
      scanForm.reset();
    },
  });
};

const onPlantFilterChange = () => {
  router.get('/general-affair/patrols/security', {
    plant_id: activePlant.value || undefined,
  }, { preserveState: true });
};
</script>

<template>
  <Head title="Patroli Keamanan QR - General Affair" />

  <ModuleLayout :module="module">
    <div class="space-y-6">
      <!-- Top Bar -->
      <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
          <h1 class="text-xl font-bold tracking-tight text-slate-900 dark:text-white sm:text-2xl">
            Sistem Patroli Keamanan Berbasis QR Checkpoint
          </h1>
          <p class="text-sm text-slate-500 dark:text-slate-400">
            Pemantauan rute penjagaan pos satpam, verifikasi target durasi patroli, dan pencatatan anomali fisik fasilitas.
          </p>
        </div>
        <div class="flex items-center gap-3">
          <Link
            href="/general-affair/patrols/cleaning"
            class="inline-flex items-center gap-2 rounded-lg border border-slate-200 bg-white px-3.5 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-300"
          >
            Lihat Sanitasi & 5R
          </Link>
          <button
            @click="openScanModal()"
            class="inline-flex items-center gap-2 rounded-lg bg-indigo-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500 transition"
          >
            <Scan class="h-4 w-4" />
            Simulasi Scan Checkpoint
          </button>
        </div>
      </div>

      <!-- Stats Cards -->
      <div class="grid grid-cols-1 sm:grid-cols-4 gap-4">
        <div class="rounded-xl border border-slate-200 bg-white p-4 dark:border-slate-800 dark:bg-slate-900">
          <span class="text-xs font-medium text-slate-500">Total Titik Checkpoint</span>
          <p class="mt-1 text-2xl font-bold text-slate-900 dark:text-white">{{ summary.total_checkpoints ?? 0 }} Titik</p>
        </div>
        <div class="rounded-xl border border-slate-200 bg-white p-4 dark:border-slate-800 dark:bg-slate-900">
          <span class="text-xs font-medium text-indigo-600 dark:text-indigo-400">Terscan Hari Ini</span>
          <p class="mt-1 text-2xl font-bold text-indigo-600 dark:text-indigo-400">{{ summary.scanned_today ?? 0 }} Titik</p>
        </div>
        <div class="rounded-xl border border-slate-200 bg-white p-4 dark:border-slate-800 dark:bg-slate-900">
          <span class="text-xs font-medium text-emerald-600 dark:text-emerald-400">Tingkat Kepatuhan Patroli</span>
          <p class="mt-1 text-2xl font-bold text-emerald-600 dark:text-emerald-400">{{ summary.compliance_percent ?? 100 }}%</p>
        </div>
        <div class="rounded-xl border border-slate-200 bg-white p-4 dark:border-slate-800 dark:bg-slate-900">
          <span class="text-xs font-medium text-red-600 dark:text-red-400">Temuan Anomali</span>
          <p class="mt-1 text-2xl font-bold text-red-600 dark:text-red-400">{{ summary.anomalies_count ?? 0 }} Kejadian</p>
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
              <span class="font-mono text-xs font-bold text-amber-600 dark:text-amber-400">{{ cp.code }}</span>
              <span class="text-[11px] px-2 py-0.5 rounded bg-slate-100 text-slate-600 dark:bg-slate-800 dark:text-slate-400">
                Target: {{ cp.target_duration_minutes }} Mnt
              </span>
            </div>
            <h3 class="text-sm font-bold text-slate-900 dark:text-white">{{ cp.name }}</h3>
            <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">{{ cp.location_description }} &bull; {{ cp.plant?.name }}</p>
          </div>

          <div class="pt-3 border-t border-slate-100 dark:border-slate-800 flex items-center justify-between">
            <span class="text-[11px] font-mono text-slate-400">Token: {{ cp.qr_token }}</span>
            <button
              @click="openScanModal(cp)"
              class="inline-flex items-center gap-1 px-2.5 py-1 text-xs font-semibold bg-indigo-50 text-indigo-700 rounded hover:bg-indigo-100 dark:bg-indigo-950 dark:text-indigo-300 transition"
            >
              <Scan class="w-3.5 h-3.5" />
              Scan QR
            </button>
          </div>
        </div>
      </div>

      <!-- Scan Logs Table -->
      <div class="rounded-xl border border-slate-200 bg-white shadow-sm dark:border-slate-800 dark:bg-slate-900 space-y-4 p-5">
        <h2 class="text-base font-bold text-slate-900 dark:text-white flex items-center gap-2">
          <Clock class="w-5 h-5 text-indigo-600" />
          Log Riwayat Scan Patroli Keamanan
        </h2>

        <div class="overflow-x-auto">
          <table class="w-full text-left text-xs">
            <thead class="bg-slate-50 dark:bg-slate-800 text-slate-500 uppercase">
              <tr>
                <th class="p-3">Waktu Scan</th>
                <th class="p-3">Checkpoint</th>
                <th class="p-3">Petugas Satpam</th>
                <th class="p-3">Durasi</th>
                <th class="p-3">Status</th>
                <th class="p-3">Anomali / Catatan</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
              <tr v-for="log in logs.data" :key="log.id">
                <td class="p-3 font-semibold">{{ log.scanned_at ? new Date(log.scanned_at).toLocaleString('id-ID') : '-' }}</td>
                <td class="p-3">
                  <span class="font-medium text-slate-900 dark:text-white">{{ log.checkpoint?.name }}</span>
                  <span class="block text-[11px] text-slate-400">{{ log.checkpoint?.code }} &bull; {{ log.checkpoint?.plant?.name }}</span>
                </td>
                <td class="p-3">{{ log.user?.name ?? 'Petugas Keamanan' }}</td>
                <td class="p-3 font-mono">{{ Math.round(log.duration_seconds / 60) }} Menit</td>
                <td class="p-3">
                  <span
                    :class="[
                      'px-2 py-0.5 rounded text-[11px] font-bold',
                      log.status === 'FLAGGED' ? 'bg-red-100 text-red-700' : (log.status === 'LATE' ? 'bg-amber-100 text-amber-700' : 'bg-emerald-100 text-emerald-700')
                    ]"
                  >
                    {{ log.status }}
                  </span>
                </td>
                <td class="p-3">
                  <span v-if="log.is_anomaly" class="text-red-600 font-semibold flex items-center gap-1">
                    <AlertTriangle class="w-3.5 h-3.5" /> {{ log.anomaly_notes }}
                  </span>
                  <span v-else class="text-slate-400">Normal (Aman)</span>
                </td>
              </tr>
              <tr v-if="!logs.data || logs.data.length === 0">
                <td colspan="6" class="p-4 text-center text-slate-400">Belum ada catatan scan patroli keamanan.</td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

      <!-- Scan Modal Simulator -->
      <div v-if="isScanModalOpen" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4">
        <div class="w-full max-w-md rounded-xl bg-white p-6 shadow-xl dark:bg-slate-900 space-y-4">
          <h3 class="text-base font-bold text-slate-900 dark:text-white flex items-center gap-2">
            <Scan class="w-5 h-5 text-indigo-600" />
            Simulasi Scan Checkpoint Keamanan
          </h3>
          <form @submit.prevent="submitScan" class="space-y-4 text-xs">
            <div>
              <label class="block font-semibold mb-1">QR Token / Kode Checkpoint:</label>
              <input v-model="scanForm.qr_token" type="text" class="w-full rounded border p-2 bg-slate-50 dark:bg-slate-800 font-mono" required />
            </div>
            <div>
              <label class="block font-semibold mb-1">Durasi Patroli (Detik):</label>
              <input v-model.number="scanForm.duration_seconds" type="number" min="0" class="w-full rounded border p-2 bg-slate-50 dark:bg-slate-800" required />
            </div>
            <div class="flex items-center gap-2">
              <input type="checkbox" id="isAnomaly" v-model="scanForm.is_anomaly" class="rounded border-slate-300 text-red-600" />
              <label for="isAnomaly" class="font-semibold text-red-600">Ditemukan Anomali / Kerusakan / Insiden Keamanan</label>
            </div>
            <div v-if="scanForm.is_anomaly">
              <label class="block font-semibold mb-1 text-red-600">Uraian Anomali:</label>
              <textarea v-model="scanForm.anomaly_notes" rows="2" class="w-full rounded border p-2 bg-slate-50 dark:bg-slate-800" placeholder="Jelaskan anomali yang ditemukan..." required></textarea>
            </div>
            <div class="flex justify-end gap-2 pt-2">
              <button type="button" @click="isScanModalOpen = false" class="px-4 py-2 border rounded">Batal</button>
              <button type="submit" class="px-4 py-2 bg-indigo-600 text-white rounded font-semibold">Simpan Scan</button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </ModuleLayout>
</template>
