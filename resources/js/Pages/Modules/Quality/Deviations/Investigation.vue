<script setup>
import { ref, computed } from 'vue';
import { Head, Link, useForm, router } from '@inertiajs/vue3';
import ModuleLayout from '@/Layouts/ModuleLayout.vue';
import {
  ArrowLeft,
  AlertOctagon,
  FileDown,
  Save,
  CheckCircle2,
  XCircle,
  Plus,
  Trash2,
  HelpCircle,
  Sparkles,
  Send,
  UserCheck,
  Check,
  ShieldCheck,
  Layers,
} from 'lucide-vue-next';

const props = defineProps({
  module: { type: Object, required: true },
  deviation: { type: Object, required: true },
  users: { type: Array, default: () => [] },
});

// 6M Fishbone data state
const fishbone = ref(props.deviation.fishbone_6m || {
  machine: [],
  man: [],
  method: [],
  materials: [],
  milieu: [],
  measurement: [],
});

const newItems = ref({
  machine: '',
  man: '',
  method: '',
  materials: '',
  milieu: '',
  measurement: '',
});

const addItem = (category) => {
  if (!newItems.value[category].trim()) return;
  if (!fishbone.value[category]) fishbone.value[category] = [];
  fishbone.value[category].push(newItems.value[category].trim());
  newItems.value[category] = '';
};

const removeItem = (category, index) => {
  fishbone.value[category].splice(index, 1);
};

// 5-Why analysis state
const fiveWhys = ref(props.deviation.five_why && props.deviation.five_why.length > 0
  ? [...props.deviation.five_why]
  : ['', '', '', '', '']
);
const rootCause = ref(props.deviation.root_cause || '');

// FMEA Action Evaluation Matrix state
const evaluasiTindakan = ref(props.deviation.evaluasi_tindakan && props.deviation.evaluasi_tindakan.length > 0
  ? [...props.deviation.evaluasi_tindakan]
  : [
      { tindakan: '', severity: 3, occurrence: 1, detection: 3, rpn: 9, evaluasi: '' },
    ]
);

const addEvaluasiRow = () => {
  evaluasiTindakan.value.push({ tindakan: '', severity: 3, occurrence: 1, detection: 3, rpn: 9, evaluasi: '' });
};

const removeEvaluasiRow = (idx) => {
  evaluasiTindakan.value.splice(idx, 1);
};

const calcRpn = (row) => {
  row.rpn = row.severity * row.occurrence * row.detection;
};

// Mengetahui (Superior) feedback state
const showMengetahuiModal = ref(false);
const assignUserId = ref('');
const assignRole = ref('Produksi Supervisor');

const showFeedbackModal = ref(false);
const superiorFeedback = ref('');

// QA Decision Modal state
const showDecisionModal = ref(false);
const qaDecision = ref('APPROVE');
const qaNotes = ref('');

// Save entire investigation form
const isSaving = ref(false);
const saveInvestigation = () => {
  isSaving.value = true;
  router.post(`/quality/deviations/${props.deviation.id}/investigation`, {
    fishbone_6m: fishbone.value,
    five_why: fiveWhys.value.filter(w => w.trim() !== ''),
    root_cause: rootCause.value,
    evaluasi_tindakan: evaluasiTindakan.value,
  }, {
    onFinish: () => { isSaving.value = false; },
  });
};

const submitAssignMengetahui = () => {
  if (!assignUserId.value) return;
  router.post(`/quality/deviations/${props.deviation.id}/assign-mengetahui`, {
    user_id: assignUserId.value,
    role: assignRole.value,
  }, {
    onSuccess: () => {
      showMengetahuiModal.value = false;
      assignUserId.value = '';
    },
  });
};

const submitFeedback = () => {
  if (!superiorFeedback.value.trim()) return;
  router.post(`/quality/deviations/${props.deviation.id}/submit-mengetahui`, {
    feedback: superiorFeedback.value,
  }, {
    onSuccess: () => {
      showFeedbackModal.value = false;
      superiorFeedback.value = '';
    },
  });
};

const submitDecision = () => {
  router.post(`/quality/deviations/${props.deviation.id}/decide`, {
    decision: qaDecision.value,
    notes: qaNotes.value,
  }, {
    onSuccess: () => {
      showDecisionModal.value = false;
    },
  });
};

const categoriesMeta = [
  { key: 'machine', label: '1. Machine (Mesin & Peralatan)', color: 'text-amber-700 bg-amber-50 border-amber-200' },
  { key: 'man', label: '2. Man (Manusia & Ketaatan SOP)', color: 'text-blue-700 bg-blue-50 border-blue-200' },
  { key: 'method', label: '3. Method (Metode & Prosedur)', color: 'text-teal-700 bg-teal-50 border-teal-200' },
  { key: 'materials', label: '4. Materials (Bahan Awal & Pengemas)', color: 'text-emerald-700 bg-emerald-50 border-emerald-200' },
  { key: 'milieu', label: '5. Milieu (Lingkungan & Fasilitas)', color: 'text-purple-700 bg-purple-50 border-purple-200' },
  { key: 'measurement', label: '6. Measurement (Pengukuran & Lab)', color: 'text-cyan-700 bg-cyan-50 border-cyan-200' },
];
</script>

<template>
  <Head :title="`Investigasi 6M & 5-Why - ${deviation.deviation_number}`" />

  <ModuleLayout :module="module">
    <div class="max-w-5xl space-y-6">
      <!-- Header -->
      <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div class="flex items-center gap-3">
          <Link
            href="/quality/deviations"
            class="rounded-lg p-2 text-slate-500 hover:bg-slate-100 dark:hover:bg-slate-800"
          >
            <ArrowLeft class="h-5 w-5" />
          </Link>
          <div>
            <div class="flex items-center gap-2">
              <span class="font-mono text-xs font-bold text-teal-600 bg-teal-50 px-2 py-0.5 rounded border border-teal-200">
                {{ deviation.deviation_number }}
              </span>
              <span class="font-mono text-xs text-slate-500 bg-slate-100 px-2 py-0.5 rounded">
                PK: {{ deviation.pk_number }}
              </span>
              <span
                :class="[
                  'inline-flex items-center rounded-md px-2 py-0.5 text-[10px] font-bold border',
                  deviation.identifikasi_penyimpangan === 'Kritis' ? 'bg-rose-50 text-rose-700 border-rose-200' :
                  (deviation.identifikasi_penyimpangan === 'Mayor' ? 'bg-amber-50 text-amber-700 border-amber-200' : 'bg-cyan-50 text-cyan-700 border-cyan-200')
                ]"
              >
                {{ deviation.identifikasi_penyimpangan }}
              </span>
              <span class="inline-flex rounded-full bg-slate-100 px-2.5 py-0.5 text-xs font-bold text-slate-700 border border-slate-200">
                Status: {{ deviation.status }}
              </span>
            </div>
            <h1 class="mt-1 text-xl font-bold tracking-tight text-slate-900 dark:text-white sm:text-2xl">
              {{ deviation.topik }}
            </h1>
          </div>
        </div>

        <div class="flex items-center gap-2">
          <a
            :href="`/quality/deviations/${deviation.id}/pdf`"
            target="_blank"
            class="inline-flex items-center gap-1.5 rounded-xl border border-slate-300 bg-white px-3.5 py-2 text-xs font-semibold text-slate-700 shadow-xs hover:bg-slate-50 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-200"
          >
            <FileDown class="h-4 w-4 text-teal-600" />
            Cetak Form PK
          </a>
          <button
            type="button"
            @click="saveInvestigation"
            :disabled="isSaving"
            class="inline-flex items-center gap-1.5 rounded-xl bg-teal-600 px-4 py-2 text-xs font-semibold text-white shadow-xs hover:bg-teal-700 disabled:opacity-50"
          >
            <Save class="h-4 w-4" />
            {{ isSaving ? 'Menyimpan...' : 'Simpan Analisis' }}
          </button>
        </div>
      </div>

      <!-- Quick Summary Box -->
      <div class="rounded-xl border border-slate-200 bg-white p-4 shadow-xs dark:border-slate-800 dark:bg-slate-900 text-xs">
        <div class="grid grid-cols-2 gap-4 sm:grid-cols-4">
          <div>
            <span class="text-slate-400 block font-semibold">Departemen Pelapor:</span>
            <span class="font-bold text-slate-800 dark:text-slate-200">{{ deviation.department }} ({{ deviation.pic || deviation.initiator?.name }})</span>
          </div>
          <div>
            <span class="text-slate-400 block font-semibold">Nomor Bets & Alat:</span>
            <span class="font-bold text-slate-800 dark:text-slate-200">{{ deviation.nomor_bets || '-' }} / {{ deviation.nomor_alat || '-' }}</span>
          </div>
          <div>
            <span class="text-slate-400 block font-semibold">Produksi Dihentikan:</span>
            <span :class="deviation.is_production_stopped ? 'text-rose-600 font-bold' : 'text-slate-700'">
              {{ deviation.is_production_stopped ? 'YA (Dihentikan)' : 'TIDAK' }}
            </span>
          </div>
          <div>
            <span class="text-slate-400 block font-semibold">Status CAPA Otomatis:</span>
            <span v-if="deviation.capa" class="text-teal-600 font-bold font-mono">
              Terbit: {{ deviation.capa.capa_number }}
            </span>
            <span v-else class="text-slate-500 italic">Menunggu Approval QA</span>
          </div>
        </div>
      </div>

      <!-- 1. Interactive 6M Ishikawa Fishbone Grid -->
      <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-xs dark:border-slate-800 dark:bg-slate-900 space-y-4">
        <div class="flex items-center justify-between">
          <div>
            <h2 class="text-sm font-bold text-slate-900 dark:text-white uppercase tracking-wider text-teal-600 flex items-center gap-1.5">
              <Layers class="h-4 w-4" />
              1. Analisis Diagram Tulang Ikan (6M Ishikawa Fishbone)
            </h2>
            <p class="text-xs text-slate-500">
              Klasifikasikan faktor-faktor potensial penyebab kegagalan berdasarkan standar 6M CPOB/GMP.
            </p>
          </div>
        </div>

        <div class="grid grid-cols-1 gap-4 md:grid-cols-2 lg:grid-cols-3">
          <div
            v-for="cat in categoriesMeta"
            :key="cat.key"
            :class="['rounded-xl border p-4 flex flex-col justify-between', cat.color]"
          >
            <div>
              <div class="font-bold text-xs uppercase tracking-wide border-b border-black/10 pb-2 mb-3">
                {{ cat.label }}
              </div>

              <!-- List of causes -->
              <div class="space-y-1.5 min-h-24">
                <div
                  v-for="(item, iIdx) in (fishbone[cat.key] || [])"
                  :key="iIdx"
                  class="flex items-start justify-between gap-1 rounded bg-white/80 px-2.5 py-1.5 text-xs text-slate-800 shadow-2xs dark:bg-slate-800/80 dark:text-slate-200"
                >
                  <span class="leading-relaxed">&bull; {{ item }}</span>
                  <button
                    type="button"
                    @click="removeItem(cat.key, iIdx)"
                    class="text-slate-400 hover:text-rose-500 ml-1 mt-0.5"
                  >
                    <Trash2 class="h-3 w-3" />
                  </button>
                </div>

                <div v-if="!fishbone[cat.key] || fishbone[cat.key].length === 0" class="text-center py-4 text-[11px] text-slate-400 italic">
                  Belum ada faktor teridentifikasi.
                </div>
              </div>
            </div>

            <!-- Add cause input -->
            <div class="mt-3 pt-2 border-t border-black/10 flex items-center gap-1.5">
              <input
                v-model="newItems[cat.key]"
                @keyup.enter="addItem(cat.key)"
                type="text"
                placeholder="Tambah penyebab..."
                class="flex-1 rounded-md border border-slate-300 bg-white py-1 px-2 text-xs focus:border-teal-500 focus:outline-none dark:bg-slate-800 dark:border-slate-700"
              />
              <button
                type="button"
                @click="addItem(cat.key)"
                class="rounded-md bg-teal-600 p-1 text-white hover:bg-teal-700"
              >
                <Plus class="h-3.5 w-3.5" />
              </button>
            </div>
          </div>
        </div>
      </div>

      <!-- 2. Analisis 5-Why Drill Down -->
      <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-xs dark:border-slate-800 dark:bg-slate-900 space-y-4">
        <div>
          <h2 class="text-sm font-bold text-slate-900 dark:text-white uppercase tracking-wider text-teal-600 flex items-center gap-1.5">
            <HelpCircle class="h-4 w-4" />
            2. Analisis Akar Masalah Metode 5-Why
          </h2>
          <p class="text-xs text-slate-500">
            Telusuri rantai sebab-akibat secara hierarkis hingga menemukan akar penyebab paling mendasar (Root Cause).
          </p>
        </div>

        <div class="space-y-3">
          <div
            v-for="(why, wIdx) in fiveWhys"
            :key="wIdx"
            class="flex items-center gap-3"
          >
            <div class="w-16 text-xs font-bold text-teal-700 font-mono">
              Why {{ wIdx + 1 }}:
            </div>
            <input
              v-model="fiveWhys[wIdx]"
              type="text"
              :placeholder="`Mengapa masalah ini terjadi? (Tahap ${wIdx + 1})`"
              class="flex-1 rounded-lg border border-slate-300 py-2 px-3 text-xs focus:border-teal-500 focus:outline-none focus:ring-1 focus:ring-teal-500 dark:border-slate-700 dark:bg-slate-800 dark:text-white"
            />
          </div>
        </div>

        <div class="pt-3 border-t border-slate-100 dark:border-slate-800">
          <label class="block text-xs font-bold text-teal-800 dark:text-teal-300 uppercase">
            Akar Penyebab Utama (Root Cause Statement) *
          </label>
          <textarea
            v-model="rootCause"
            rows="3"
            placeholder="Simpulkan akar masalah yang menjadi pemicu utama kegagalan..."
            class="mt-1 w-full rounded-lg border border-teal-300 bg-teal-50/20 py-2 px-3 text-xs font-semibold focus:border-teal-500 focus:outline-none focus:ring-1 focus:ring-teal-500 dark:border-teal-800 dark:bg-teal-950/20 dark:text-white"
          ></textarea>
        </div>
      </div>

      <!-- 3. Matriks Evaluasi Tindakan FMEA (Tindakan Awal) -->
      <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-xs dark:border-slate-800 dark:bg-slate-900 space-y-4">
        <div class="flex items-center justify-between">
          <div>
            <h2 class="text-sm font-bold text-slate-900 dark:text-white uppercase tracking-wider text-teal-600">
              3. Matriks Evaluasi Tindakan Investigasi (FMEA Skoring)
            </h2>
            <p class="text-xs text-slate-500">Tindakan terencana dievaluasi dengan skor S x O x D.</p>
          </div>
          <button
            type="button"
            @click="addEvaluasiRow"
            class="inline-flex items-center gap-1 text-xs font-semibold text-teal-600 hover:underline"
          >
            <Plus class="h-3.5 w-3.5" />
            Tambah Tindakan
          </button>
        </div>

        <div class="overflow-x-auto">
          <table class="w-full text-left text-xs">
            <thead class="bg-slate-50 text-slate-600 border-b border-slate-200">
              <tr>
                <th class="py-2 px-3 font-semibold">Rencana Tindakan</th>
                <th class="py-2 px-3 font-semibold text-center w-20">S (1-10)</th>
                <th class="py-2 px-3 font-semibold text-center w-20">O (1-10)</th>
                <th class="py-2 px-3 font-semibold text-center w-20">D (1-10)</th>
                <th class="py-2 px-3 font-semibold text-center w-20">RPN</th>
                <th class="py-2 px-3 font-semibold">Evaluasi Pengendalian</th>
                <th class="py-2 px-3 font-semibold w-10"></th>
              </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
              <tr v-for="(row, rIdx) in evaluasiTindakan" :key="rIdx">
                <td class="py-2 px-3">
                  <input
                    v-model="row.tindakan"
                    type="text"
                    placeholder="misal: Kalibrasi ulang sensor & ganti gasket"
                    class="w-full rounded border border-slate-300 py-1 px-2 text-xs"
                  />
                </td>
                <td class="py-2 px-3 text-center">
                  <input
                    v-model.number="row.severity"
                    @input="calcRpn(row)"
                    type="number"
                    min="1"
                    max="10"
                    class="w-14 rounded border border-slate-300 py-1 px-1 text-center text-xs font-bold"
                  />
                </td>
                <td class="py-2 px-3 text-center">
                  <input
                    v-model.number="row.occurrence"
                    @input="calcRpn(row)"
                    type="number"
                    min="1"
                    max="10"
                    class="w-14 rounded border border-slate-300 py-1 px-1 text-center text-xs font-bold"
                  />
                </td>
                <td class="py-2 px-3 text-center">
                  <input
                    v-model.number="row.detection"
                    @input="calcRpn(row)"
                    type="number"
                    min="1"
                    max="10"
                    class="w-14 rounded border border-slate-300 py-1 px-1 text-center text-xs font-bold"
                  />
                </td>
                <td class="py-2 px-3 text-center font-bold font-mono text-teal-700">
                  {{ row.rpn }}
                </td>
                <td class="py-2 px-3">
                  <input
                    v-model="row.evaluasi"
                    type="text"
                    placeholder="Evaluasi efektivitas..."
                    class="w-full rounded border border-slate-300 py-1 px-2 text-xs"
                  />
                </td>
                <td class="py-2 px-3 text-center">
                  <button
                    type="button"
                    @click="removeEvaluasiRow(rIdx)"
                    class="text-slate-400 hover:text-rose-500"
                  >
                    <Trash2 class="h-3.5 w-3.5" />
                  </button>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

      <!-- 4. Umpan Balik Atasan ('Mengetahui') & Keputusan QA -->
      <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
        <!-- Atasan Mengetahui Block -->
        <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-xs dark:border-slate-800 dark:bg-slate-900 space-y-3">
          <div class="flex items-center justify-between border-b border-slate-100 pb-2">
            <div>
              <h3 class="text-xs font-bold uppercase tracking-wider text-slate-900">
                Umpan Balik Atasan (Mengetahui)
              </h3>
              <p class="text-[11px] text-slate-500">Tanggapan supervisi dan verifikasi lapangan.</p>
            </div>
            <div class="flex items-center gap-2">
              <button
                type="button"
                @click="showMengetahuiModal = true"
                class="text-xs font-semibold text-slate-600 hover:underline"
              >
                + Tunjuk Atasan
              </button>
              <button
                type="button"
                @click="showFeedbackModal = true"
                class="text-xs font-semibold text-teal-600 hover:underline"
              >
                Beri Tanggapan
              </button>
            </div>
          </div>

          <div class="space-y-2">
            <div
              v-for="(m, mIdx) in (deviation.mengetahui_data || [])"
              :key="mIdx"
              class="rounded-xl border border-slate-100 p-3 bg-slate-50/60 text-xs"
            >
              <div class="flex items-center justify-between font-bold text-slate-800">
                <span>{{ m.name }} ({{ m.role }})</span>
                <span class="text-[10px] text-emerald-700 bg-emerald-50 px-2 py-0.5 rounded font-mono">
                  {{ m.feedback_at ? 'SIGNED' : 'PENDING' }}
                </span>
              </div>
              <p class="mt-1 text-slate-600 italic">
                "{{ m.feedback || 'Menunggu umpan balik supervisi...' }}"
              </p>
            </div>

            <div v-if="!deviation.mengetahui_data || deviation.mengetahui_data.length === 0" class="text-center py-4 text-xs text-slate-400">
              Belum ada atasan yang ditugaskan.
            </div>
          </div>
        </div>

        <!-- QA Decision & CAPA Auto-Spawn Block -->
        <div class="rounded-2xl border border-teal-200 bg-teal-50/30 p-5 shadow-xs dark:border-teal-900 dark:bg-teal-950/10 space-y-3 flex flex-col justify-between">
          <div>
            <h3 class="text-xs font-bold uppercase tracking-wider text-teal-900 dark:text-teal-300 flex items-center gap-1.5">
              <ShieldCheck class="h-4 w-4" />
              Keputusan QA & Otomasi CAPA
            </h3>
            <p class="text-xs text-slate-600 mt-1 leading-relaxed">
              Persetujuan atas laporan penyelidikan ini akan <strong>secara otomatis membuat record CAPA baru</strong> yang terikat langsung dengan nomor deviasi ini.
            </p>

            <div v-if="deviation.status === 'APPROVED'" class="mt-4 rounded-xl border border-emerald-300 bg-emerald-50 p-3 text-xs text-emerald-800">
              <div class="font-bold flex items-center gap-1">
                <CheckCircle2 class="h-4 w-4 text-emerald-600" />
                Deviasi Telah Disetujui (APPROVED)
              </div>
              <div v-if="deviation.capa" class="mt-2 font-mono">
                Nomor CAPA: <strong>{{ deviation.capa.capa_number }}</strong>
                <Link :href="`/quality/capa/${deviation.capa.id}`" class="block text-teal-700 hover:underline mt-1 font-sans">
                  &rarr; Buka Lembar Tindakan CAPA
                </Link>
              </div>
            </div>
          </div>

          <div v-if="deviation.status !== 'APPROVED'" class="pt-2">
            <button
              type="button"
              @click="showDecisionModal = true"
              class="w-full rounded-xl bg-teal-600 py-2.5 text-xs font-bold text-white shadow-xs hover:bg-teal-700 transition text-center"
            >
              Keputusan Persetujuan QA (Decide)
            </button>
          </div>
        </div>
      </div>

      <!-- Modals -->
      <!-- Modal Tunjuk Atasan -->
      <div v-if="showMengetahuiModal" class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/60 p-4">
        <div class="w-full max-w-md rounded-2xl bg-white p-6 shadow-xl space-y-4">
          <h3 class="text-sm font-bold text-slate-900">Tunjuk Atasan Mengetahui</h3>
          <div>
            <label class="block text-xs font-semibold text-slate-700">Peran / Jabatan</label>
            <input v-model="assignRole" type="text" class="mt-1 w-full rounded-lg border border-slate-300 py-2 px-3 text-xs" />
          </div>
          <div>
            <label class="block text-xs font-semibold text-slate-700">Pilih User Atasan</label>
            <select v-model="assignUserId" class="mt-1 w-full rounded-lg border border-slate-300 py-2 px-3 text-xs">
              <option value="">(Pilih Atasan)</option>
              <option v-for="u in users" :key="u.id" :value="u.id">{{ u.name }} ({{ u.email }})</option>
            </select>
          </div>
          <div class="flex justify-end gap-2 pt-2">
            <button @click="showMengetahuiModal = false" class="rounded-lg border px-3 py-1.5 text-xs">Batal</button>
            <button @click="submitAssignMengetahui" class="rounded-lg bg-teal-600 px-4 py-1.5 text-xs font-semibold text-white">Tugaskan</button>
          </div>
        </div>
      </div>

      <!-- Modal Input Tanggapan Atasan -->
      <div v-if="showFeedbackModal" class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/60 p-4">
        <div class="w-full max-w-md rounded-2xl bg-white p-6 shadow-xl space-y-4">
          <h3 class="text-sm font-bold text-slate-900">Tanggapan & Tanda Tangan Atasan</h3>
          <div>
            <label class="block text-xs font-semibold text-slate-700">Tanggapan / Catatan Supervisi *</label>
            <textarea v-model="superiorFeedback" rows="3" placeholder="Penyebab telah diperiksa di lapangan..." class="mt-1 w-full rounded-lg border border-slate-300 py-2 px-3 text-xs"></textarea>
          </div>
          <div class="flex justify-end gap-2 pt-2">
            <button @click="showFeedbackModal = false" class="rounded-lg border px-3 py-1.5 text-xs">Batal</button>
            <button @click="submitFeedback" class="rounded-lg bg-teal-600 px-4 py-1.5 text-xs font-semibold text-white">Kirim Tanggapan</button>
          </div>
        </div>
      </div>

      <!-- Modal QA Decision -->
      <div v-if="showDecisionModal" class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/60 p-4">
        <div class="w-full max-w-md rounded-2xl bg-white p-6 shadow-xl space-y-4">
          <h3 class="text-sm font-bold text-slate-900">Keputusan Penjaminan Mutu (QA)</h3>
          <div>
            <label class="block text-xs font-semibold text-slate-700">Pilihan Keputusan</label>
            <select v-model="qaDecision" class="mt-1 w-full rounded-lg border border-slate-300 py-2 px-3 text-xs font-bold">
              <option value="APPROVE">SETUJU (APPROVE & Auto-Spawn CAPA)</option>
              <option value="REJECT">TOLAK (Kembalikan ke Inisiator untuk Revisi)</option>
            </select>
          </div>
          <div>
            <label class="block text-xs font-semibold text-slate-700">Catatan Otorisasi QA</label>
            <textarea v-model="qaNotes" rows="3" placeholder="Catatan atau alasan persetujuan/penolakan..." class="mt-1 w-full rounded-lg border border-slate-300 py-2 px-3 text-xs"></textarea>
          </div>
          <div class="flex justify-end gap-2 pt-2">
            <button @click="showDecisionModal = false" class="rounded-lg border px-3 py-1.5 text-xs">Batal</button>
            <button @click="submitDecision" class="rounded-lg bg-teal-600 px-4 py-1.5 text-xs font-semibold text-white">Konfirmasi Keputusan</button>
          </div>
        </div>
      </div>
    </div>
  </ModuleLayout>
</template>
