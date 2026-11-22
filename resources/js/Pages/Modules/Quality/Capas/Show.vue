<script setup>
import { ref } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import ModuleLayout from '@/Layouts/ModuleLayout.vue';
import {
  ArrowLeft,
  CheckCircle2,
  FileDown,
  Plus,
  Upload,
  Calendar,
  AlertTriangle,
  User,
  ShieldCheck,
  Check,
  X,
  FileText,
} from 'lucide-vue-next';

const props = defineProps({
  module: { type: Object, required: true },
  capa: { type: Object, required: true },
  users: { type: Array, default: () => [] },
});

// Modals state
const showAddActionModal = ref(false);
const newActionTindakan = ref('');
const newActionJenis = ref('korektif');
const newActionDept = ref('Produksi');
const newActionPicId = ref('');
const newActionDeadline = ref('');

const showProofModal = ref(false);
const activeAction = ref(null);
const proofFilePath = ref('');
const proofNotes = ref('');

const showVerifyModal = ref(false);
const verifyDecision = ref('CLOSE');
const verifyNotes = ref('');

const submitAddAction = () => {
  if (!newActionTindakan.value.trim() || !newActionDeadline.value) return;
  router.post(`/quality/capa/${props.capa.id}/actions`, {
    tindakan: newActionTindakan.value,
    jenis_tindakan: newActionJenis.value,
    department: newActionDept.value,
    pic_id: newActionPicId.value || null,
    deadline: newActionDeadline.value,
  }, {
    onSuccess: () => {
      showAddActionModal.value = false;
      newActionTindakan.value = '';
    },
  });
};

const openProofModal = (action) => {
  activeAction.value = action;
  proofFilePath.value = action.bukti_file_path || '';
  proofNotes.value = action.catatan_pelaksanaan || '';
  showProofModal.value = true;
};

const submitProof = () => {
  if (!proofFilePath.value.trim()) return;
  router.post(`/quality/capa/actions/${activeAction.value.id}/proof`, {
    bukti_file_path: proofFilePath.value,
    catatan_pelaksanaan: proofNotes.value,
  }, {
    onSuccess: () => {
      showProofModal.value = false;
    },
  });
};

const submitVerify = () => {
  if (!verifyNotes.value.trim()) return;
  router.post(`/quality/capa/${props.capa.id}/verify`, {
    decision: verifyDecision.value,
    verification_notes: verifyNotes.value,
  }, {
    onSuccess: () => {
      showVerifyModal.value = false;
    },
  });
};

const departments = ['Produksi', 'QC', 'R&D', 'Engineering', 'Supply Chain', 'QA'];
</script>

<template>
  <Head :title="`Detail CAPA - ${capa.capa_number}`" />

  <ModuleLayout :module="module">
    <div class="max-w-5xl space-y-6">
      <!-- Header -->
      <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div class="flex items-center gap-3">
          <Link
            href="/quality/capa"
            class="rounded-lg p-2 text-slate-500 hover:bg-slate-100 dark:hover:bg-slate-800"
          >
            <ArrowLeft class="h-5 w-5" />
          </Link>
          <div>
            <div class="flex items-center gap-2">
              <span class="font-mono text-xs font-bold text-teal-600 bg-teal-50 px-2 py-0.5 rounded border border-teal-200">
                {{ capa.capa_number }}
              </span>
              <span class="text-xs text-slate-500">
                Ref Deviasi:
                <Link
                  v-if="capa.deviation_id"
                  :href="`/quality/deviations/${capa.deviation_id}/investigation`"
                  class="font-mono text-teal-600 hover:underline font-bold"
                >
                  {{ capa.deviation_number_ref }}
                </Link>
              </span>
              <span class="inline-flex rounded-full bg-slate-100 px-2.5 py-0.5 text-xs font-bold text-slate-700 border border-slate-200">
                Status: {{ capa.status }}
              </span>
            </div>
            <h1 class="mt-1 text-xl font-bold tracking-tight text-slate-900 dark:text-white sm:text-2xl">
              Lembar Tindakan Korektif & Preventif (CAPA)
            </h1>
          </div>
        </div>

        <div class="flex items-center gap-2">
          <a
            :href="`/quality/capa/${capa.id}/pdf`"
            target="_blank"
            class="inline-flex items-center gap-1.5 rounded-xl border border-slate-300 bg-white px-3.5 py-2 text-xs font-semibold text-slate-700 shadow-xs hover:bg-slate-50 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-200"
          >
            <FileDown class="h-4 w-4 text-teal-600" />
            Cetak PDF CAPA
          </a>
          <button
            v-if="capa.status !== 'CLOSE'"
            type="button"
            @click="showVerifyModal = true"
            class="inline-flex items-center gap-1.5 rounded-xl bg-teal-600 px-4 py-2 text-xs font-semibold text-white shadow-xs hover:bg-teal-700"
          >
            <ShieldCheck class="h-4 w-4" />
            Verifikasi Efektivitas QA
          </button>
        </div>
      </div>

      <!-- Problem & Root Cause Box -->
      <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-xs dark:border-slate-800 dark:bg-slate-900 space-y-4">
        <h2 class="text-xs font-bold uppercase tracking-wider text-slate-400">
          Ringkasan Masalah & Akar Penyebab
        </h2>

        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
          <div class="rounded-xl border border-slate-100 p-4 bg-slate-50/70 text-xs">
            <span class="font-bold text-slate-500 block mb-1">Penyimpangan Mutu:</span>
            <p class="text-slate-800 leading-relaxed font-semibold">
              {{ capa.deviation?.topik || capa.tindakan_capa }}
            </p>
          </div>

          <div class="rounded-xl border border-teal-100 p-4 bg-teal-50/40 text-xs">
            <span class="font-bold text-teal-700 block mb-1">Akar Masalah (Root Cause):</span>
            <p class="text-slate-800 leading-relaxed">
              {{ capa.deviation?.root_cause || 'Teridentifikasi pada penyelidikan ketidaksesuaian terkait.' }}
            </p>
          </div>
        </div>

        <div v-if="capa.hasil_verifikasi_qa" class="rounded-xl border border-emerald-200 bg-emerald-50/50 p-4 text-xs text-emerald-900">
          <span class="font-bold block mb-1 flex items-center gap-1">
            <CheckCircle2 class="h-4 w-4 text-emerald-600" />
            Catatan Verifikasi Efektivitas QA:
          </span>
          <p>{{ capa.hasil_verifikasi_qa }}</p>
        </div>
      </div>

      <!-- Multi-Actions Section -->
      <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-xs dark:border-slate-800 dark:bg-slate-900 space-y-4">
        <div class="flex items-center justify-between">
          <div>
            <h2 class="text-sm font-bold text-slate-900 dark:text-white uppercase tracking-wider text-teal-600">
              Rencana Multi-Tindakan Lapangan (Corrective & Preventive Actions)
            </h2>
            <p class="text-xs text-slate-500">Penetapan tindakan spesifik, penugasan PIC, dan pengunggahan bukti lapangan.</p>
          </div>

          <button
            v-if="capa.status !== 'CLOSE'"
            type="button"
            @click="showAddActionModal = true"
            class="inline-flex items-center gap-1.5 rounded-lg bg-teal-600 px-3 py-1.5 text-xs font-semibold text-white hover:bg-teal-700"
          >
            <Plus class="h-3.5 w-3.5" />
            Tambah Tindakan
          </button>
        </div>

        <!-- Actions List Table -->
        <div class="overflow-x-auto">
          <table class="w-full text-left text-xs">
            <thead class="bg-slate-50 text-slate-600 border-b border-slate-200">
              <tr>
                <th class="py-2.5 px-3 font-semibold">No. Aksi</th>
                <th class="py-2.5 px-3 font-semibold">Tipe</th>
                <th class="py-2.5 px-3 font-semibold">Rincian Tindakan</th>
                <th class="py-2.5 px-3 font-semibold">Dept & PIC</th>
                <th class="py-2.5 px-3 font-semibold">Tenggat</th>
                <th class="py-2.5 px-3 font-semibold text-center">Status</th>
                <th class="py-2.5 px-3 font-semibold text-right">Bukti Lapangan</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
              <tr v-for="action in capa.actions" :key="action.id" class="hover:bg-slate-50/60 transition">
                <td class="py-3 px-3 font-mono font-bold text-slate-800">
                  {{ action.action_number || '-' }}
                </td>
                <td class="py-3 px-3">
                  <span
                    :class="[
                      'rounded px-2 py-0.5 text-[10px] font-bold uppercase',
                      action.jenis_tindakan === 'korektif' ? 'bg-teal-50 text-teal-700' : 'bg-blue-50 text-blue-700'
                    ]"
                  >
                    {{ action.jenis_tindakan }}
                  </span>
                </td>
                <td class="py-3 px-3 max-w-sm">
                  <div class="font-medium text-slate-800">{{ action.tindakan }}</div>
                  <div v-if="action.catatan_pelaksanaan" class="text-[11px] text-slate-500 mt-1 italic">
                    Catatan: {{ action.catatan_pelaksanaan }}
                  </div>
                </td>
                <td class="py-3 px-3">
                  <div>{{ action.department }}</div>
                  <div class="text-slate-500 text-[11px]">{{ action.pic?.name || '-' }}</div>
                </td>
                <td class="py-3 px-3">
                  {{ action.deadline ? new Date(action.deadline).toLocaleDateString('id-ID') : '-' }}
                </td>
                <td class="py-3 px-3 text-center">
                  <span
                    :class="[
                      'inline-flex rounded-full px-2 py-0.5 text-[10px] font-bold',
                      action.status === 'VERIFIED' ? 'bg-emerald-100 text-emerald-800' :
                      (action.status === 'SUBMITTED' ? 'bg-blue-100 text-blue-800' : 'bg-amber-100 text-amber-800')
                    ]"
                  >
                    {{ action.status }}
                  </span>
                </td>
                <td class="py-3 px-3 text-right">
                  <button
                    v-if="action.status !== 'VERIFIED'"
                    type="button"
                    @click="openProofModal(action)"
                    class="inline-flex items-center gap-1 rounded-lg border border-slate-300 px-2.5 py-1 text-xs font-semibold text-slate-700 hover:bg-slate-50"
                  >
                    <Upload class="h-3.5 w-3.5 text-teal-600" />
                    {{ action.bukti_file_path ? 'Ubah Bukti' : 'Upload Bukti' }}
                  </button>
                  <span v-else class="text-[11px] text-emerald-700 font-bold inline-flex items-center gap-1">
                    <Check class="h-3.5 w-3.5" />
                    Verified
                  </span>
                </td>
              </tr>

              <tr v-if="!capa.actions || capa.actions.length === 0">
                <td colspan="7" class="py-8 text-center text-slate-400">
                  Belum ada sub-tindakan terdaftar pada CAPA ini.
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

      <!-- Modals -->
      <!-- Modal Tambah Tindakan -->
      <div v-if="showAddActionModal" class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/60 p-4">
        <div class="w-full max-w-lg rounded-2xl bg-white p-6 shadow-xl space-y-4">
          <h3 class="text-sm font-bold text-slate-900">Tambah Tindakan Korektif / Preventif</h3>
          <div>
            <label class="block text-xs font-semibold text-slate-700">Jenis Tindakan</label>
            <select v-model="newActionJenis" class="mt-1 w-full rounded-lg border border-slate-300 py-2 px-3 text-xs">
              <option value="korektif">Tindakan Korektif (Perbaikan Masalah Yang Terjadi)</option>
              <option value="preventif">Tindakan Preventif (Pencegahan Keberulangan)</option>
            </select>
          </div>
          <div>
            <label class="block text-xs font-semibold text-slate-700">Rincian Tindakan yang Akan Dilakukan *</label>
            <textarea v-model="newActionTindakan" rows="3" placeholder="misal: Kalibrasi ulang sensor suhu thermocouple..." class="mt-1 w-full rounded-lg border border-slate-300 py-2 px-3 text-xs"></textarea>
          </div>
          <div class="grid grid-cols-2 gap-4">
            <div>
              <label class="block text-xs font-semibold text-slate-700">Departemen</label>
              <select v-model="newActionDept" class="mt-1 w-full rounded-lg border border-slate-300 py-2 px-3 text-xs">
                <option v-for="d in departments" :key="d" :value="d">{{ d }}</option>
              </select>
            </div>
            <div>
              <label class="block text-xs font-semibold text-slate-700">Tenggat Waktu (Deadline) *</label>
              <input v-model="newActionDeadline" type="date" class="mt-1 w-full rounded-lg border border-slate-300 py-2 px-3 text-xs" />
            </div>
          </div>
          <div>
            <label class="block text-xs font-semibold text-slate-700">PIC Pelaksana</label>
            <select v-model="newActionPicId" class="mt-1 w-full rounded-lg border border-slate-300 py-2 px-3 text-xs">
              <option value="">(Pilih PIC Pelaksana)</option>
              <option v-for="u in users" :key="u.id" :value="u.id">{{ u.name }}</option>
            </select>
          </div>
          <div class="flex justify-end gap-2 pt-2">
            <button @click="showAddActionModal = false" class="rounded-lg border px-3 py-1.5 text-xs">Batal</button>
            <button @click="submitAddAction" class="rounded-lg bg-teal-600 px-4 py-1.5 text-xs font-semibold text-white">Simpan Tindakan</button>
          </div>
        </div>
      </div>

      <!-- Modal Upload Bukti Lapangan -->
      <div v-if="showProofModal" class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/60 p-4">
        <div class="w-full max-w-md rounded-2xl bg-white p-6 shadow-xl space-y-4">
          <h3 class="text-sm font-bold text-slate-900">Serahkan Bukti Pelaksanaan Lapangan</h3>
          <p class="text-xs text-slate-500 font-semibold">{{ activeAction?.tindakan }}</p>

          <div>
            <label class="block text-xs font-semibold text-slate-700">Tautan File Bukti / Path Storage *</label>
            <input
              v-model="proofFilePath"
              type="text"
              placeholder="https://drive.google.com/... atau bukti-kalibrasi.pdf"
              class="mt-1 w-full rounded-lg border border-slate-300 py-2 px-3 text-xs"
            />
          </div>
          <div>
            <label class="block text-xs font-semibold text-slate-700">Catatan Pelaksanaan Lapangan</label>
            <textarea
              v-model="proofNotes"
              rows="3"
              placeholder="Jelaskan hasil pekerjaan fisik yang telah selesai dilaksanakan..."
              class="mt-1 w-full rounded-lg border border-slate-300 py-2 px-3 text-xs"
            ></textarea>
          </div>
          <div class="flex justify-end gap-2 pt-2">
            <button @click="showProofModal = false" class="rounded-lg border px-3 py-1.5 text-xs">Batal</button>
            <button @click="submitProof" class="rounded-lg bg-teal-600 px-4 py-1.5 text-xs font-semibold text-white">Upload & Serahkan</button>
          </div>
        </div>
      </div>

      <!-- Modal Verifikasi QA & Closing -->
      <div v-if="showVerifyModal" class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/60 p-4">
        <div class="w-full max-w-md rounded-2xl bg-white p-6 shadow-xl space-y-4">
          <h3 class="text-sm font-bold text-slate-900">Verifikasi Efektivitas QA & Penutupan CAPA</h3>
          <div>
            <label class="block text-xs font-semibold text-slate-700">Keputusan Verifikasi</label>
            <select v-model="verifyDecision" class="mt-1 w-full rounded-lg border border-slate-300 py-2 px-3 text-xs font-bold">
              <option value="CLOSE">TUTUP CAPA (CLOSE - Efektivitas Terbukti)</option>
              <option value="REJECTED">KEMBALIKAN (REVISE - Bukti Belum Cukup)</option>
            </select>
          </div>
          <div>
            <label class="block text-xs font-semibold text-slate-700">Catatan Hasil Verifikasi QA *</label>
            <textarea
              v-model="verifyNotes"
              rows="3"
              placeholder="Hasil pemantauan efektivitas, tidak ada deviasi berulang selama 3 bets..."
              class="mt-1 w-full rounded-lg border border-slate-300 py-2 px-3 text-xs"
            ></textarea>
          </div>
          <div class="flex justify-end gap-2 pt-2">
            <button @click="showVerifyModal = false" class="rounded-lg border px-3 py-1.5 text-xs">Batal</button>
            <button @click="submitVerify" class="rounded-lg bg-teal-600 px-4 py-1.5 text-xs font-semibold text-white">Konfirmasi Verifikasi</button>
          </div>
        </div>
      </div>
    </div>
  </ModuleLayout>
</template>
