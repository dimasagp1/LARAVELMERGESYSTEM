<script setup>
import { computed } from 'vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import ModuleLayout from '@/Layouts/ModuleLayout.vue';
import {
  ArrowLeft,
  Save,
  Send,
  AlertTriangle,
  Info,
  GitCommit,
  ShieldCheck,
} from 'lucide-vue-next';

const props = defineProps({
  module: { type: Object, required: true },
  users: { type: Array, default: () => [] },
});

const form = useForm({
  type: 'CRA',
  sifat_perubahan: 'Proses',
  nama_produk: '',
  awal_sebelum_perubahan: '',
  usulan_perubahan: '',
  alasan_perubahan: '',
  target_pelaksanaan: '',
  severity: 5,
  occurrence: 3,
  detection: 4,
  risk_mitigation: '',
  pic_id: '',
  action: 'submit',
});

// Computed RPN
const calculatedRpn = computed(() => {
  return form.severity * form.occurrence * form.detection;
});

const rpnRiskCategory = computed(() => {
  const rpn = calculatedRpn.value;
  if (rpn >= 80) return { label: 'Tinggi (Kritis) - Wajib Mitigasi Ketat & Uji Validasi', color: 'text-rose-600 bg-rose-50 border-rose-200' };
  if (rpn >= 40) return { label: 'Sedang (Moderat) - Memerlukan Kajian Lintas Departemen', color: 'text-amber-600 bg-amber-50 border-amber-200' };
  return { label: 'Rendah (Dapat Diterima) - Risiko Dapat Dikendalikan Rutin', color: 'text-emerald-600 bg-emerald-50 border-emerald-200' };
});

const submit = (actionType) => {
  form.action = actionType;
  form.post('/quality/change-requests');
};

const sifatOptions = [
  'Formula / Resep',
  'Bahan Awal (Bahan Baku / Tambahan)',
  'Proses Pengolahan',
  'Fasilitas / Cleanroom',
  'Bahan Pengemas Primer / Sekunder',
  'Mesin / Peralatan Produksi',
  'Dokumen Prosedur (SOP / IK)',
  'Spesifikasi Mutu & Pengujian',
];
</script>

<template>
  <Head title="Ajukan Usulan Perubahan (Change Request CRA/CRB)" />

  <ModuleLayout :module="module">
    <div class="max-w-4xl space-y-6">
      <!-- Header -->
      <div class="flex items-center gap-3">
        <Link
          href="/quality/change-requests"
          class="rounded-lg p-2 text-slate-500 hover:bg-slate-100 dark:hover:bg-slate-800"
        >
          <ArrowLeft class="h-5 w-5" />
        </Link>
        <div>
          <h1 class="text-xl font-bold tracking-tight text-slate-900 dark:text-white sm:text-2xl">
            Formulir Usulan Perubahan (Change Request)
          </h1>
          <p class="text-xs text-slate-500 dark:text-slate-400">
            Standar kepatuhan CPOB / GMP untuk perubahan formula, proses, mesin, atau dokumen dengan analisis FMEA.
          </p>
        </div>
      </div>

      <form @submit.prevent="submit('submit')" class="space-y-6">
        <!-- 1. Klasifikasi Perubahan (CRA vs CRB) -->
        <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-xs dark:border-slate-800 dark:bg-slate-900 space-y-5">
          <h2 class="text-sm font-bold text-slate-900 dark:text-white uppercase tracking-wider text-teal-600 dark:text-teal-400">
            1. Klasifikasi Usulan Perubahan
          </h2>

          <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
            <!-- CRA Radio -->
            <label
              :class="[
                'relative flex cursor-pointer rounded-xl border p-4 shadow-xs transition focus:outline-none',
                form.type === 'CRA'
                  ? 'border-rose-500 bg-rose-50/40 ring-1 ring-rose-500 dark:bg-rose-950/20'
                  : 'border-slate-200 hover:bg-slate-50 dark:border-slate-800 dark:hover:bg-slate-800/60'
              ]"
            >
              <input
                type="radio"
                name="cr_type"
                value="CRA"
                v-model="form.type"
                class="sr-only"
              />
              <div class="flex flex-col">
                <span class="flex items-center gap-1.5 font-bold text-slate-900 dark:text-white text-sm">
                  <span class="inline-block h-2 w-2 rounded-full bg-rose-600"></span>
                  CRA (Change Request A — Mayor)
                </span>
                <span class="mt-1 text-xs text-slate-500 dark:text-slate-400 leading-relaxed">
                  Perubahan berdampak pada mutu produk, keamanan, registrasi, atau kepatuhan CPOB. <strong>Wajib Analisis Risiko FMEA</strong> & approval hingga Direksi/GM.
                </span>
              </div>
            </label>

            <!-- CRB Radio -->
            <label
              :class="[
                'relative flex cursor-pointer rounded-xl border p-4 shadow-xs transition focus:outline-none',
                form.type === 'CRB'
                  ? 'border-blue-500 bg-blue-50/40 ring-1 ring-blue-500 dark:bg-blue-950/20'
                  : 'border-slate-200 hover:bg-slate-50 dark:border-slate-800 dark:hover:bg-slate-800/60'
              ]"
            >
              <input
                type="radio"
                name="cr_type"
                value="CRB"
                v-model="form.type"
                class="sr-only"
              />
              <div class="flex flex-col">
                <span class="flex items-center gap-1.5 font-bold text-slate-900 dark:text-white text-sm">
                  <span class="inline-block h-2 w-2 rounded-full bg-blue-600"></span>
                  CRB (Change Request B — Minor)
                </span>
                <span class="mt-1 text-xs text-slate-500 dark:text-slate-400 leading-relaxed">
                  Perubahan editorial/minor tanpa dampak terhadap mutu kritis produk. Tanpa FMEA, persetujuan hingga Head of Quality.
                </span>
              </div>
            </label>
          </div>

          <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
            <div>
              <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300">Sifat / Kategori Perubahan *</label>
              <select
                v-model="form.sifat_perubahan"
                class="mt-1 w-full rounded-lg border border-slate-300 py-2 px-3 text-xs focus:border-teal-500 focus:outline-none focus:ring-1 focus:ring-teal-500 dark:border-slate-700 dark:bg-slate-800 dark:text-white"
              >
                <option v-for="opt in sifatOptions" :key="opt" :value="opt">{{ opt }}</option>
              </select>
            </div>

            <div>
              <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300">Produk / Sistem / Mesin Terdampak *</label>
              <input
                v-model="form.nama_produk"
                type="text"
                placeholder="misal: Sirup Temulawak Plus 100ml / Tangki Ekstraktor 500L"
                class="mt-1 w-full rounded-lg border border-slate-300 py-2 px-3 text-xs focus:border-teal-500 focus:outline-none focus:ring-1 focus:ring-teal-500 dark:border-slate-700 dark:bg-slate-800 dark:text-white"
              />
              <p v-if="form.errors.nama_produk" class="text-xs text-rose-500 mt-1">{{ form.errors.nama_produk }}</p>
            </div>
          </div>
        </div>

        <!-- 2. Rincian Usulan Komparasi Sebelum vs Sesudah -->
        <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-xs dark:border-slate-800 dark:bg-slate-900 space-y-4">
          <h2 class="text-sm font-bold text-slate-900 dark:text-white uppercase tracking-wider text-teal-600 dark:text-teal-400">
            2. Perbandingan Kondisi Sebelum vs Usulan Perubahan
          </h2>

          <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
            <div>
              <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300">
                Kondisi Awal (Sebelum Perubahan) *
              </label>
              <textarea
                v-model="form.awal_sebelum_perubahan"
                rows="4"
                placeholder="Jelaskan kondisi, spesifikasi, atau prosedur yang saat ini sedang berlaku..."
                class="mt-1 w-full rounded-lg border border-slate-300 py-2 px-3 text-xs focus:border-teal-500 focus:outline-none focus:ring-1 focus:ring-teal-500 dark:border-slate-700 dark:bg-slate-800 dark:text-white"
              ></textarea>
              <p v-if="form.errors.awal_sebelum_perubahan" class="text-xs text-rose-500 mt-1">{{ form.errors.awal_sebelum_perubahan }}</p>
            </div>

            <div>
              <label class="block text-xs font-semibold text-teal-700 dark:text-teal-300">
                Usulan Perubahan yang Diajukan *
              </label>
              <textarea
                v-model="form.usulan_perubahan"
                rows="4"
                placeholder="Rincikan detail usulan perubahan yang ingin diterapkan..."
                class="mt-1 w-full rounded-lg border border-teal-300 bg-teal-50/20 py-2 px-3 text-xs focus:border-teal-500 focus:outline-none focus:ring-1 focus:ring-teal-500 dark:border-teal-800 dark:bg-teal-950/20 dark:text-white"
              ></textarea>
              <p v-if="form.errors.usulan_perubahan" class="text-xs text-rose-500 mt-1">{{ form.errors.usulan_perubahan }}</p>
            </div>
          </div>

          <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
            <div>
              <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300">Alasan & Urgensi Perubahan</label>
              <textarea
                v-model="form.alasan_perubahan"
                rows="2"
                placeholder="Latar belakang, temuan deviasi, efisiensi, atau instruksi manajemen..."
                class="mt-1 w-full rounded-lg border border-slate-300 py-2 px-3 text-xs focus:border-teal-500 focus:outline-none focus:ring-1 focus:ring-teal-500 dark:border-slate-700 dark:bg-slate-800 dark:text-white"
              ></textarea>
            </div>

            <div>
              <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300">Target Tanggal Pelaksanaan</label>
              <input
                v-model="form.target_pelaksanaan"
                type="date"
                class="mt-1 w-full rounded-lg border border-slate-300 py-2 px-3 text-xs focus:border-teal-500 focus:outline-none focus:ring-1 focus:ring-teal-500 dark:border-slate-700 dark:bg-slate-800 dark:text-white"
              />
            </div>
          </div>
        </div>

        <!-- 3. Analisis Risiko FMEA (Khusus CRA) -->
        <div v-if="form.type === 'CRA'" class="rounded-2xl border border-rose-200 bg-rose-50/30 p-6 shadow-xs dark:border-rose-900/50 dark:bg-rose-950/10 space-y-4">
          <div class="flex items-center justify-between">
            <div>
              <h2 class="text-sm font-bold text-rose-800 dark:text-rose-300 uppercase tracking-wider flex items-center gap-1.5">
                <AlertTriangle class="h-4 w-4" />
                3. Analisis Risiko FMEA (Risk Priority Number)
              </h2>
              <p class="text-xs text-rose-600 dark:text-rose-400 mt-0.5">
                Kalkulasi otomatis RPN = Severity (S) x Occurrence (O) x Detection (D) skala 1 - 10.
              </p>
            </div>

            <div :class="['rounded-xl border px-4 py-2 font-mono font-bold text-center', rpnRiskCategory.color]">
              <div class="text-xs uppercase">Skor RPN</div>
              <div class="text-2xl font-black">{{ calculatedRpn }}</div>
            </div>
          </div>

          <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
            <div>
              <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300">
                Severity (Tingkat Keparahan: 1 - 10)
              </label>
              <input
                v-model.number="form.severity"
                type="number"
                min="1"
                max="10"
                class="mt-1 w-full rounded-lg border border-slate-300 py-2 px-3 text-xs font-bold focus:border-rose-500 focus:outline-none focus:ring-1 focus:ring-rose-500 dark:border-slate-700 dark:bg-slate-800 dark:text-white"
              />
              <span class="text-[10px] text-slate-400">1: Sangat Ringan &bull; 10: Fatal / Berbahaya</span>
            </div>

            <div>
              <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300">
                Occurrence (Kekerapan Terjadi: 1 - 10)
              </label>
              <input
                v-model.number="form.occurrence"
                type="number"
                min="1"
                max="10"
                class="mt-1 w-full rounded-lg border border-slate-300 py-2 px-3 text-xs font-bold focus:border-rose-500 focus:outline-none focus:ring-1 focus:ring-rose-500 dark:border-slate-700 dark:bg-slate-800 dark:text-white"
              />
              <span class="text-[10px] text-slate-400">1: Sangat Jarang &bull; 10: Hampir Pasti</span>
            </div>

            <div>
              <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300">
                Detection (Kemudahan Deteksi: 1 - 10)
              </label>
              <input
                v-model.number="form.detection"
                type="number"
                min="1"
                max="10"
                class="mt-1 w-full rounded-lg border border-slate-300 py-2 px-3 text-xs font-bold focus:border-rose-500 focus:outline-none focus:ring-1 focus:ring-rose-500 dark:border-slate-700 dark:bg-slate-800 dark:text-white"
              />
              <span class="text-[10px] text-slate-400">1: Sangat Mudah Terdeteksi &bull; 10: Mustahil</span>
            </div>
          </div>

          <div class="rounded-lg bg-white p-3 border border-slate-200 dark:bg-slate-800 dark:border-slate-700">
            <span class="text-xs font-bold text-slate-700 dark:text-slate-300">Hasil Evaluasi Tingkat Risiko:</span>
            <div class="text-xs font-semibold mt-0.5" :class="calculatedRpn >= 80 ? 'text-rose-600' : (calculatedRpn >= 40 ? 'text-amber-600' : 'text-emerald-600')">
              {{ rpnRiskCategory.label }}
            </div>
          </div>

          <div>
            <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300">
              Rencana Mitigasi & Pengendalian Risiko *
            </label>
            <textarea
              v-model="form.risk_mitigation"
              rows="3"
              placeholder="Jelaskan tindakan pencegahan, pengujian laboratorium ekstra, atau validasi 3 bets komersial..."
              class="mt-1 w-full rounded-lg border border-slate-300 py-2 px-3 text-xs focus:border-rose-500 focus:outline-none focus:ring-1 focus:ring-rose-500 dark:border-slate-700 dark:bg-slate-800 dark:text-white"
            ></textarea>
          </div>
        </div>

        <!-- Buttons -->
        <div class="flex items-center justify-end gap-3 pt-2">
          <Link
            href="/quality/change-requests"
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
            Ajukan Perubahan (Submit)
          </button>
        </div>
      </form>
    </div>
  </ModuleLayout>
</template>
