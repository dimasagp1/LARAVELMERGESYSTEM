<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';
import ModuleLayout from '@/Layouts/ModuleLayout.vue';
import {
  ArrowLeft,
  Save,
  Send,
  AlertOctagon,
  ShieldAlert,
} from 'lucide-vue-next';

const props = defineProps({
  module: { type: Object, required: true },
});

const form = useForm({
  topik: '',
  department: 'Produksi',
  pic: '',
  nomor_bets: '',
  nomor_alat: '',
  nomor_dokumen: '',
  objek_terdampak: '',
  jenis_penyimpangan: 'Proses',
  identifikasi_penyimpangan: 'Mayor',
  tanggal_temuan: new Date().toISOString().split('T')[0],
  is_other_batch_affected: false,
  other_batch_details: '',
  frekuensi_kejadian: 'Tidak Pernah Sebelumnya',
  is_production_stopped: false,
  immediate_action_details: '',
  action: 'submit',
});

const submit = (actionType) => {
  form.action = actionType;
  form.post('/quality/deviations');
};

const departments = ['Produksi', 'QC', 'R&D', 'Supply Chain', 'Engineering / Pemeliharaan', 'QA', 'Logistik'];
const jenisOptions = ['Peralatan / Mesin', 'Bahan Baku / Tambahan', 'Proses Pengolahan', 'Dokumentasi / SOP', 'Lingkungan & HVAC', 'Pengemasan Primer / Sekunder'];
</script>

<template>
  <Head title="Pelaporan Deviasi Baru (Deviation Report - DR)" />

  <ModuleLayout :module="module">
    <div class="max-w-4xl space-y-6">
      <div class="flex items-center gap-3">
        <Link
          href="/quality/deviations"
          class="rounded-lg p-2 text-slate-500 hover:bg-slate-100 dark:hover:bg-slate-800"
        >
          <ArrowLeft class="h-5 w-5" />
        </Link>
        <div>
          <h1 class="text-xl font-bold tracking-tight text-slate-900 dark:text-white sm:text-2xl">
            Laporan Temuan Deviasi Baru (Deviation Report)
          </h1>
          <p class="text-xs text-slate-500 dark:text-slate-400">
            Pencatatan insiden ketidaksesuaian operasional pabrik sesuai regulasi CPOB / GMP.
          </p>
        </div>
      </div>

      <form @submit.prevent="submit('submit')" class="space-y-6">
        <!-- 1. Identitas Temuan Deviasi -->
        <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-xs dark:border-slate-800 dark:bg-slate-900 space-y-5">
          <h2 class="text-sm font-bold text-slate-900 dark:text-white uppercase tracking-wider text-teal-600">
            1. Rincian Temuan & Identitas Kejadian
          </h2>

          <div>
            <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300">Topik / Judul Ketidaksesuaian *</label>
            <input
              v-model="form.topik"
              type="text"
              placeholder="misal: Suhu Ekstraksi Vakum Fluktuasi Melebihi Batas Toleransi"
              class="mt-1 w-full rounded-lg border border-slate-300 py-2 px-3 text-xs focus:border-teal-500 focus:outline-none focus:ring-1 focus:ring-teal-500 dark:border-slate-700 dark:bg-slate-800 dark:text-white"
            />
            <p v-if="form.errors.topik" class="text-xs text-rose-500 mt-1">{{ form.errors.topik }}</p>
          </div>

          <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
            <div>
              <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300">Tanggal Kejadian / Temuan *</label>
              <input
                v-model="form.tanggal_temuan"
                type="date"
                class="mt-1 w-full rounded-lg border border-slate-300 py-2 px-3 text-xs focus:border-teal-500 focus:outline-none focus:ring-1 focus:ring-teal-500 dark:border-slate-700 dark:bg-slate-800 dark:text-white"
              />
            </div>

            <div>
              <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300">Departemen Terkait *</label>
              <select
                v-model="form.department"
                class="mt-1 w-full rounded-lg border border-slate-300 py-2 px-3 text-xs focus:border-teal-500 focus:outline-none focus:ring-1 focus:ring-teal-500 dark:border-slate-700 dark:bg-slate-800 dark:text-white"
              >
                <option v-for="d in departments" :key="d" :value="d">{{ d }}</option>
              </select>
            </div>

            <div>
              <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300">Identifikasi Tingkat CPOB *</label>
              <select
                v-model="form.identifikasi_penyimpangan"
                class="mt-1 w-full rounded-lg border border-slate-300 py-2 px-3 text-xs font-bold focus:border-teal-500 focus:outline-none focus:ring-1 focus:ring-teal-500 dark:border-slate-700 dark:bg-slate-800 dark:text-white"
              >
                <option value="Kritis">Kritis (Risiko Keamanan/Pemberhentian)</option>
                <option value="Mayor">Mayor (Penyimpangan Spesifikasi Kualitas)</option>
                <option value="Minor">Minor (Non-Kritis / Prosedural)</option>
              </select>
            </div>
          </div>

          <div class="grid grid-cols-1 gap-4 sm:grid-cols-4">
            <div>
              <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300">Nomor Bets (Jika Ada)</label>
              <input
                v-model="form.nomor_bets"
                type="text"
                placeholder="misal: EXT-2026-088"
                class="mt-1 w-full rounded-lg border border-slate-300 py-2 px-3 text-xs font-mono focus:border-teal-500 focus:outline-none focus:ring-1 focus:ring-teal-500 dark:border-slate-700 dark:bg-slate-800 dark:text-white"
              />
            </div>

            <div>
              <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300">Nomor Mesin / Alat</label>
              <input
                v-model="form.nomor_alat"
                type="text"
                placeholder="misal: MSN-EXT-01"
                class="mt-1 w-full rounded-lg border border-slate-300 py-2 px-3 text-xs font-mono focus:border-teal-500 focus:outline-none focus:ring-1 focus:ring-teal-500 dark:border-slate-700 dark:bg-slate-800 dark:text-white"
              />
            </div>

            <div>
              <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300">Objek Terdampak</label>
              <input
                v-model="form.objek_terdampak"
                type="text"
                placeholder="misal: Ekstrak Temulawak"
                class="mt-1 w-full rounded-lg border border-slate-300 py-2 px-3 text-xs focus:border-teal-500 focus:outline-none focus:ring-1 focus:ring-teal-500 dark:border-slate-700 dark:bg-slate-800 dark:text-white"
              />
            </div>

            <div>
              <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300">Jenis Penyimpangan</label>
              <select
                v-model="form.jenis_penyimpangan"
                class="mt-1 w-full rounded-lg border border-slate-300 py-2 px-3 text-xs focus:border-teal-500 focus:outline-none focus:ring-1 focus:ring-teal-500 dark:border-slate-700 dark:bg-slate-800 dark:text-white"
              >
                <option v-for="j in jenisOptions" :key="j" :value="j">{{ j }}</option>
              </select>
            </div>
          </div>
        </div>

        <!-- 2. Parameter Kepatuhan CPOB & Penilaian Dampak -->
        <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-xs dark:border-slate-800 dark:bg-slate-900 space-y-4">
          <h2 class="text-sm font-bold text-slate-900 dark:text-white uppercase tracking-wider text-teal-600">
            2. Ceklis Penilaian Dampak CPOB
          </h2>

          <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
            <div class="rounded-xl border border-slate-100 p-4 bg-slate-50/60 dark:border-slate-800 dark:bg-slate-800/40">
              <label class="flex items-center gap-2 cursor-pointer font-semibold text-xs text-slate-800 dark:text-slate-200">
                <input
                  v-model="form.is_production_stopped"
                  type="checkbox"
                  class="rounded border-slate-300 text-teal-600 focus:ring-teal-500"
                />
                Apakah Operasional Produksi Dihentikan Sementara?
              </label>
              <p class="text-[11px] text-slate-500 mt-1">Centang jika jalur produksi/mesin distop demi mencegah penyebaran risiko.</p>
            </div>

            <div class="rounded-xl border border-slate-100 p-4 bg-slate-50/60 dark:border-slate-800 dark:bg-slate-800/40">
              <label class="flex items-center gap-2 cursor-pointer font-semibold text-xs text-slate-800 dark:text-slate-200">
                <input
                  v-model="form.is_other_batch_affected"
                  type="checkbox"
                  class="rounded border-slate-300 text-teal-600 focus:ring-teal-500"
                />
                Apakah Bets Lain Berpotensi Terdampak (Efek Domino)?
              </label>
              <p class="text-[11px] text-slate-500 mt-1">Pemeriksaan terhadap batch sebelum/sesudah yang menggunakan bahan/mesin sama.</p>
            </div>
          </div>

          <div v-if="form.is_other_batch_affected">
            <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300">Rincian Bets Lain yang Terdampak</label>
            <textarea
              v-model="form.other_batch_details"
              rows="2"
              placeholder="Tuliskan nomor bets lain yang sedang diisolasi..."
              class="mt-1 w-full rounded-lg border border-slate-300 py-2 px-3 text-xs"
            ></textarea>
          </div>

          <div>
            <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300">Frekuensi Kejadian Serupa</label>
            <select
              v-model="form.frekuensi_kejadian"
              class="mt-1 w-full rounded-lg border border-slate-300 py-2 px-3 text-xs focus:border-teal-500 focus:outline-none focus:ring-1 focus:ring-teal-500 dark:border-slate-700 dark:bg-slate-800 dark:text-white"
            >
              <option value="Tidak Pernah Sebelumnya">Tidak Pernah Sebelumnya (Pertama Kali)</option>
              <option value="Jarang">Jarang (1 - 2 kali dalam 6 bulan terakhir)</option>
              <option value="Sering">Sering (Berulang > 3 kali dalam 6 bulan terakhir)</option>
            </select>
          </div>
        </div>

        <!-- 3. Tindakan Segera di Lapangan -->
        <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-xs dark:border-slate-800 dark:bg-slate-900 space-y-4">
          <h2 class="text-sm font-bold text-slate-900 dark:text-white uppercase tracking-wider text-teal-600">
            3. Tindakan Segera di Lapangan (Immediate Action)
          </h2>
          <p class="text-xs text-slate-500">Tindakan isolasi risiko yang diambil sesaat setelah deviasi ditemukan.</p>

          <div>
            <textarea
              v-model="form.immediate_action_details"
              rows="3"
              placeholder="misal: Bets dihentikan prosesnya dan diberi label Karantina Merah. Suhu tangki diturunkan ke 45°C..."
              class="w-full rounded-lg border border-slate-300 py-2 px-3 text-xs focus:border-teal-500 focus:outline-none focus:ring-1 focus:ring-teal-500 dark:border-slate-700 dark:bg-slate-800 dark:text-white"
            ></textarea>
          </div>
        </div>

        <!-- Buttons -->
        <div class="flex items-center justify-end gap-3 pt-2">
          <Link
            href="/quality/deviations"
            class="rounded-xl border border-slate-300 px-4 py-2.5 text-xs font-semibold text-slate-700 hover:bg-slate-50 dark:border-slate-700 dark:text-slate-300"
          >
            Batal
          </Link>
          <button
            type="button"
            @click="submit('draft')"
            :disabled="form.processing"
            class="inline-flex items-center gap-1.5 rounded-xl border border-slate-300 bg-white px-4 py-2.5 text-xs font-semibold text-slate-700 hover:bg-slate-50 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-200"
          >
            <Save class="h-4 w-4" />
            Simpan Draft
          </button>
          <button
            type="submit"
            :disabled="form.processing"
            class="inline-flex items-center gap-2 rounded-xl bg-teal-600 px-5 py-2.5 text-xs font-semibold text-white shadow-xs hover:bg-teal-700 disabled:opacity-50"
          >
            <Send class="h-4 w-4" />
            Kirim Laporan & Lanjut Investigasi
          </button>
        </div>
      </form>
    </div>
  </ModuleLayout>
</template>
