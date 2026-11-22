<script setup>
import { ref } from 'vue';
import { Head, Link, useForm, router } from '@inertiajs/vue3';
import ModuleLayout from '@/Layouts/ModuleLayout.vue';
import {
  ArrowLeft,
  GitCommit,
  FileDown,
  CheckCircle2,
  XCircle,
  AlertTriangle,
  UserCheck,
  Send,
  Upload,
  Check,
  Calendar,
  Layers,
  ShieldCheck,
  FileText,
} from 'lucide-vue-next';

const props = defineProps({
  module: { type: Object, required: true },
  changeRequest: { type: Object, required: true },
  users: { type: Array, default: () => [] },
});

// Modals / forms
const showAssignModal = ref(false);
const assignUserId = ref('');
const assignDepartment = ref('Produksi');
const assignPicName = ref('');

const showAssessmentModal = ref(false);
const assessmentRisk = ref('');
const assessmentRecommendation = ref('');
const assessmentDecision = ref('APPROVED');

const showApprovalModal = ref(false);
const approvalLevel = ref('head_of_quality');
const approvalDecision = ref('APPROVED');
const approvalNotes = ref('');

const showStage2Modal = ref(false);
const stage2Summary = ref('');

const completeNotes = ref('');

const submitAssign = () => {
  if (!assignUserId.value) return;
  const user = props.users.find(u => u.id === parseInt(assignUserId.value));
  router.post(`/quality/change-requests/${props.changeRequest.id}/assign-assessments`, {
    assignments: [
      {
        user_id: user.id,
        department: assignDepartment.value,
        pic_name: user.name,
      },
    ],
  }, {
    onSuccess: () => {
      showAssignModal.value = false;
      assignUserId.value = '';
    },
  });
};

const submitAssessment = () => {
  router.post(`/quality/change-requests/${props.changeRequest.id}/submit-assessment`, {
    risk_analysis: assessmentRisk.value,
    recommendation: assessmentRecommendation.value,
    decision: assessmentDecision.value,
  }, {
    onSuccess: () => {
      showAssessmentModal.value = false;
    },
  });
};

const submitApproval = () => {
  router.post(`/quality/change-requests/${props.changeRequest.id}/approve`, {
    approval_level: approvalLevel.value,
    decision: approvalDecision.value,
    notes: approvalNotes.value,
  }, {
    onSuccess: () => {
      showApprovalModal.value = false;
    },
  });
};

const submitStage2 = () => {
  router.post(`/quality/change-requests/${props.changeRequest.id}/stage-2`, {
    implementation_summary: stage2Summary.value,
  }, {
    onSuccess: () => {
      showStage2Modal.value = false;
    },
  });
};

const submitComplete = () => {
  if (!confirm('Tutup Change Request ini dan tandai sebagai COMPLETE?')) return;
  router.post(`/quality/change-requests/${props.changeRequest.id}/complete-stage-2`, {
    notes: completeNotes.value || 'Pelaksanaan telah diverifikasi QA dan memenuhi spesifikasi.',
  });
};

const assessments = ref(props.changeRequest.qa_verification_data?.stage_1?.assessments || []);
const approvals = ref(props.changeRequest.qa_verification_data?.stage_1?.approvals || {});
const stage2Data = ref(props.changeRequest.stage_2_data || {});
</script>

<template>
  <Head :title="`Detail Change Request - ${changeRequest.cr_number}`" />

  <ModuleLayout :module="module">
    <div class="max-w-5xl space-y-6">
      <!-- Header -->
      <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div class="flex items-center gap-3">
          <Link
            href="/quality/change-requests"
            class="rounded-lg p-2 text-slate-500 hover:bg-slate-100 dark:hover:bg-slate-800"
          >
            <ArrowLeft class="h-5 w-5" />
          </Link>
          <div>
            <div class="flex items-center gap-2">
              <span class="font-mono text-xs font-bold text-teal-600 bg-teal-50 px-2 py-0.5 rounded border border-teal-200">
                {{ changeRequest.cr_number }}
              </span>
              <span
                :class="[
                  'inline-flex items-center rounded-md px-2 py-0.5 text-[10px] font-bold border',
                  changeRequest.type === 'CRA' ? 'bg-rose-50 text-rose-700 border-rose-200' : 'bg-blue-50 text-blue-700 border-blue-200'
                ]"
              >
                {{ changeRequest.type }} ({{ changeRequest.type === 'CRA' ? 'Mayor' : 'Minor' }})
              </span>
              <span class="inline-flex rounded-full bg-slate-100 px-2.5 py-0.5 text-xs font-bold text-slate-700 border border-slate-200">
                {{ changeRequest.status }}
              </span>
            </div>
            <h1 class="mt-1 text-xl font-bold tracking-tight text-slate-900 dark:text-white sm:text-2xl">
              {{ changeRequest.nama_produk }}
            </h1>
          </div>
        </div>

        <div class="flex items-center gap-2">
          <a
            :href="`/quality/change-requests/${changeRequest.id}/pdf`"
            target="_blank"
            class="inline-flex items-center gap-1.5 rounded-xl border border-slate-300 bg-white px-3.5 py-2 text-xs font-semibold text-slate-700 shadow-xs hover:bg-slate-50 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-200"
          >
            <FileDown class="h-4 w-4 text-teal-600" />
            Cetak Form PDF
          </a>
        </div>
      </div>

      <!-- Overview Cards -->
      <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
        <!-- Main details -->
        <div class="lg:col-span-2 space-y-6">
          <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-xs dark:border-slate-800 dark:bg-slate-900 space-y-4">
            <h2 class="text-xs font-bold uppercase tracking-wider text-slate-400">
              Perbandingan Kondisi & Usulan
            </h2>

            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
              <div class="rounded-xl border border-slate-200 p-4 bg-slate-50/70 dark:border-slate-800 dark:bg-slate-800/40">
                <span class="text-xs font-bold text-slate-500 block mb-1">Kondisi Awal (Sebelum Perubahan)</span>
                <p class="text-xs text-slate-800 dark:text-slate-200 leading-relaxed whitespace-pre-line">
                  {{ changeRequest.awal_sebelum_perubahan }}
                </p>
              </div>

              <div class="rounded-xl border border-teal-200 p-4 bg-teal-50/40 dark:border-teal-900/50 dark:bg-teal-950/20">
                <span class="text-xs font-bold text-teal-700 dark:text-teal-300 block mb-1">Usulan Perubahan</span>
                <p class="text-xs text-slate-800 dark:text-slate-200 leading-relaxed whitespace-pre-line">
                  {{ changeRequest.usulan_perubahan }}
                </p>
              </div>
            </div>

            <div v-if="changeRequest.alasan_perubahan" class="text-xs text-slate-600 dark:text-slate-400 pt-2 border-t border-slate-100 dark:border-slate-800">
              <strong>Alasan Perubahan:</strong> {{ changeRequest.alasan_perubahan }}
            </div>
          </div>

          <!-- Stage 1: Department Assessments -->
          <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-xs dark:border-slate-800 dark:bg-slate-900 space-y-4">
            <div class="flex items-center justify-between">
              <div>
                <h2 class="text-sm font-bold text-slate-900 dark:text-white uppercase tracking-wider text-teal-600">
                  Stage 1: Pengkajian Departemen Terkait
                </h2>
                <p class="text-xs text-slate-500">Evaluasi risiko dan rekomendasi dari PIC departemen teknis.</p>
              </div>
              <div class="flex items-center gap-2">
                <button
                  type="button"
                  @click="showAssignModal = true"
                  class="rounded-lg border border-slate-300 px-3 py-1.5 text-xs font-semibold text-slate-700 hover:bg-slate-50"
                >
                  Tunjuk PIC
                </button>
                <button
                  type="button"
                  @click="showAssessmentModal = true"
                  class="rounded-lg bg-teal-600 px-3 py-1.5 text-xs font-semibold text-white hover:bg-teal-700"
                >
                  Isi Kajian
                </button>
              </div>
            </div>

            <div class="space-y-3">
              <div
                v-for="(ass, idx) in assessments"
                :key="idx"
                class="rounded-xl border border-slate-100 p-4 bg-slate-50/60 dark:border-slate-800 dark:bg-slate-800/40"
              >
                <div class="flex items-center justify-between">
                  <div class="font-bold text-xs text-slate-800 dark:text-slate-200">
                    {{ ass.department }} &bullet; {{ ass.pic_name }}
                  </div>
                  <span
                    :class="[
                      'rounded-full px-2 py-0.5 text-[10px] font-bold',
                      ass.status === 'APPROVED' ? 'bg-emerald-100 text-emerald-800' : 'bg-amber-100 text-amber-800'
                    ]"
                  >
                    {{ ass.status }}
                  </span>
                </div>
                <div class="mt-2 text-xs text-slate-600 dark:text-slate-300">
                  <div><strong>Kajian Risiko:</strong> {{ ass.risk_analysis || '(Belum diisi)' }}</div>
                  <div class="mt-1"><strong>Rekomendasi:</strong> {{ ass.recommendation || '(Belum diisi)' }}</div>
                </div>
              </div>

              <div v-if="assessments.length === 0" class="text-center py-6 text-xs text-slate-400">
                Belum ada penunjukan kajian departemen.
              </div>
            </div>
          </div>

          <!-- Stage 2: Implementation & Completion -->
          <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-xs dark:border-slate-800 dark:bg-slate-900 space-y-4">
            <div class="flex items-center justify-between">
              <div>
                <h2 class="text-sm font-bold text-slate-900 dark:text-white uppercase tracking-wider text-teal-600">
                  Stage 2: Pelaksanaan Lapangan & Verifikasi QA
                </h2>
                <p class="text-xs text-slate-500">Unggah bukti pelaksanaan fisik dan verifikasi akhir penutupan.</p>
              </div>
              <div class="flex items-center gap-2">
                <button
                  v-if="changeRequest.status === 'IN PROGRESS' || changeRequest.status === 'APPROVED'"
                  type="button"
                  @click="showStage2Modal = true"
                  class="rounded-lg bg-teal-600 px-3 py-1.5 text-xs font-semibold text-white hover:bg-teal-700"
                >
                  Upload Bukti Stage 2
                </button>
              </div>
            </div>

            <div v-if="stage2Data.submitted_at" class="rounded-xl border border-teal-100 bg-teal-50/40 p-4 text-xs space-y-2">
              <div class="flex items-center justify-between text-teal-800 font-bold">
                <span>Dokumentasi Pelaksanaan Diserahkan oleh: {{ stage2Data.submitted_by }}</span>
                <span>{{ stage2Data.submitted_at }}</span>
              </div>
              <p class="text-slate-700 whitespace-pre-line">{{ stage2Data.implementation_summary }}</p>

              <div v-if="changeRequest.status === 'IN PROGRESS'" class="pt-3 border-t border-teal-200 flex items-center gap-3">
                <input
                  v-model="completeNotes"
                  type="text"
                  placeholder="Catatan verifikasi efektivitas QA..."
                  class="flex-1 rounded-lg border border-slate-300 py-1.5 px-3 text-xs"
                />
                <button
                  type="button"
                  @click="submitComplete"
                  class="rounded-lg bg-emerald-600 px-3.5 py-1.5 text-xs font-semibold text-white hover:bg-emerald-700"
                >
                  Verifikasi & Tutup (COMPLETE)
                </button>
              </div>
            </div>

            <div v-else class="text-xs text-slate-400 py-4 text-center">
              Tahap pelaksanaan belum diunggah. Menunggu penyelesaian persetujuan manajemen.
            </div>
          </div>
        </div>

        <!-- Sidebar: FMEA Risk & Approvals Chain -->
        <div class="space-y-6">
          <!-- FMEA Summary Card (If CRA) -->
          <div v-if="changeRequest.type === 'CRA'" class="rounded-2xl border border-rose-200 bg-rose-50/40 p-5 shadow-xs dark:border-rose-950 dark:bg-rose-950/20 space-y-3">
            <div class="flex items-center justify-between">
              <span class="text-xs font-bold uppercase text-rose-800 dark:text-rose-300">Skor FMEA RPN</span>
              <span class="rounded-lg bg-rose-600 px-2.5 py-1 text-sm font-black text-white font-mono">
                {{ changeRequest.rpn }}
              </span>
            </div>

            <div class="grid grid-cols-3 gap-2 text-center text-xs">
              <div class="rounded-lg bg-white p-2 border border-rose-100">
                <div class="text-[10px] text-slate-400 font-bold">SEVERITY</div>
                <div class="text-base font-bold text-slate-800">{{ changeRequest.severity }}</div>
              </div>
              <div class="rounded-lg bg-white p-2 border border-rose-100">
                <div class="text-[10px] text-slate-400 font-bold">OCCURRENCE</div>
                <div class="text-base font-bold text-slate-800">{{ changeRequest.occurrence }}</div>
              </div>
              <div class="rounded-lg bg-white p-2 border border-rose-100">
                <div class="text-[10px] text-slate-400 font-bold">DETECTION</div>
                <div class="text-base font-bold text-slate-800">{{ changeRequest.detection }}</div>
              </div>
            </div>

            <div v-if="changeRequest.risk_mitigation" class="text-xs text-slate-700 pt-2 border-t border-rose-200">
              <strong>Rencana Mitigasi:</strong>
              <p class="mt-1 text-[11px] leading-relaxed">{{ changeRequest.risk_mitigation }}</p>
            </div>
          </div>

          <!-- Approval Chain Card -->
          <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-xs dark:border-slate-800 dark:bg-slate-900 space-y-4">
            <div class="flex items-center justify-between border-b border-slate-100 pb-2">
              <h3 class="text-xs font-bold uppercase tracking-wider text-slate-900 dark:text-white">
                Rantai Otorisasi Manajemen
              </h3>
              <button
                type="button"
                @click="showApprovalModal = true"
                class="text-xs font-semibold text-teal-600 hover:underline"
              >
                Beri Approval
              </button>
            </div>

            <!-- Level 1: Head of Quality -->
            <div class="flex items-start gap-3 text-xs">
              <div :class="['mt-0.5 rounded-full p-1', approvals.head_of_quality?.status === 'APPROVED' ? 'bg-emerald-100 text-emerald-700' : 'bg-slate-100 text-slate-400']">
                <Check class="h-3.5 w-3.5" />
              </div>
              <div>
                <div class="font-bold text-slate-800 dark:text-slate-200">Level 1: Head of Quality</div>
                <div class="text-slate-500 text-[11px]">
                  {{ approvals.head_of_quality?.name || 'Menunggu persetujuan...' }}
                </div>
              </div>
            </div>

            <!-- Level 2: Operational Manager (Khusus CRA) -->
            <div v-if="changeRequest.type === 'CRA'" class="flex items-start gap-3 text-xs">
              <div :class="['mt-0.5 rounded-full p-1', approvals.operational_manager?.status === 'APPROVED' ? 'bg-emerald-100 text-emerald-700' : 'bg-slate-100 text-slate-400']">
                <Check class="h-3.5 w-3.5" />
              </div>
              <div>
                <div class="font-bold text-slate-800 dark:text-slate-200">Level 2: Operational Manager</div>
                <div class="text-slate-500 text-[11px]">
                  {{ approvals.operational_manager?.name || 'Menunggu persetujuan...' }}
                </div>
              </div>
            </div>

            <!-- Level 3: General Manager (Khusus CRA) -->
            <div v-if="changeRequest.type === 'CRA'" class="flex items-start gap-3 text-xs">
              <div :class="['mt-0.5 rounded-full p-1', approvals.general_manager?.status === 'APPROVED' ? 'bg-emerald-100 text-emerald-700' : 'bg-slate-100 text-slate-400']">
                <Check class="h-3.5 w-3.5" />
              </div>
              <div>
                <div class="font-bold text-slate-800 dark:text-slate-200">Level 3: General Manager (Final)</div>
                <div class="text-slate-500 text-[11px]">
                  {{ approvals.general_manager?.name || 'Menunggu persetujuan...' }}
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Modals -->
      <!-- Modal Tunjuk PIC -->
      <div v-if="showAssignModal" class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/60 p-4">
        <div class="w-full max-w-md rounded-2xl bg-white p-6 shadow-xl space-y-4">
          <h3 class="text-sm font-bold text-slate-900">Tunjuk PIC Pengkajian Departemen</h3>
          <div>
            <label class="block text-xs font-semibold text-slate-700">Departemen</label>
            <select v-model="assignDepartment" class="mt-1 w-full rounded-lg border border-slate-300 py-2 px-3 text-xs">
              <option value="Produksi">Produksi</option>
              <option value="QC">QC</option>
              <option value="R&D">R&D</option>
              <option value="Engineering">Engineering / Pemeliharaan</option>
              <option value="Supply Chain">Supply Chain</option>
            </select>
          </div>
          <div>
            <label class="block text-xs font-semibold text-slate-700">Pilih User / PIC</label>
            <select v-model="assignUserId" class="mt-1 w-full rounded-lg border border-slate-300 py-2 px-3 text-xs">
              <option value="">(Pilih Personel)</option>
              <option v-for="u in users" :key="u.id" :value="u.id">{{ u.name }} ({{ u.email }})</option>
            </select>
          </div>
          <div class="flex justify-end gap-2 pt-2">
            <button @click="showAssignModal = false" class="rounded-lg border px-3 py-1.5 text-xs">Batal</button>
            <button @click="submitAssign" class="rounded-lg bg-teal-600 px-4 py-1.5 text-xs font-semibold text-white">Tugaskan</button>
          </div>
        </div>
      </div>

      <!-- Modal Input Kajian -->
      <div v-if="showAssessmentModal" class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/60 p-4">
        <div class="w-full max-w-lg rounded-2xl bg-white p-6 shadow-xl space-y-4">
          <h3 class="text-sm font-bold text-slate-900">Form Kajian Dampak Departemen</h3>
          <div>
            <label class="block text-xs font-semibold text-slate-700">Analisis Dampak & Risiko Teknis *</label>
            <textarea v-model="assessmentRisk" rows="3" class="mt-1 w-full rounded-lg border border-slate-300 py-2 px-3 text-xs"></textarea>
          </div>
          <div>
            <label class="block text-xs font-semibold text-slate-700">Rekomendasi Departemen *</label>
            <textarea v-model="assessmentRecommendation" rows="2" class="mt-1 w-full rounded-lg border border-slate-300 py-2 px-3 text-xs"></textarea>
          </div>
          <div>
            <label class="block text-xs font-semibold text-slate-700">Keputusan Kajian</label>
            <select v-model="assessmentDecision" class="mt-1 w-full rounded-lg border border-slate-300 py-2 px-3 text-xs">
              <option value="APPROVED">APPROVED (Menyetujui Usulan)</option>
              <option value="REJECTED">REJECTED (Menolak Usulan)</option>
            </select>
          </div>
          <div class="flex justify-end gap-2 pt-2">
            <button @click="showAssessmentModal = false" class="rounded-lg border px-3 py-1.5 text-xs">Batal</button>
            <button @click="submitAssessment" class="rounded-lg bg-teal-600 px-4 py-1.5 text-xs font-semibold text-white">Simpan Kajian</button>
          </div>
        </div>
      </div>

      <!-- Modal Approval Manajemen -->
      <div v-if="showApprovalModal" class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/60 p-4">
        <div class="w-full max-w-md rounded-2xl bg-white p-6 shadow-xl space-y-4">
          <h3 class="text-sm font-bold text-slate-900">Keputusan Persetujuan Manajemen</h3>
          <div>
            <label class="block text-xs font-semibold text-slate-700">Level Approval</label>
            <select v-model="approvalLevel" class="mt-1 w-full rounded-lg border border-slate-300 py-2 px-3 text-xs">
              <option value="head_of_quality">Level 1: Head of Quality</option>
              <option v-if="changeRequest.type === 'CRA'" value="operational_manager">Level 2: Operational Manager</option>
              <option v-if="changeRequest.type === 'CRA'" value="general_manager">Level 3: General Manager</option>
            </select>
          </div>
          <div>
            <label class="block text-xs font-semibold text-slate-700">Keputusan</label>
            <select v-model="approvalDecision" class="mt-1 w-full rounded-lg border border-slate-300 py-2 px-3 text-xs">
              <option value="APPROVED">SETUJU (APPROVED)</option>
              <option value="REJECTED">TOLAK (REJECTED)</option>
            </select>
          </div>
          <div>
            <label class="block text-xs font-semibold text-slate-700">Catatan Otorisasi</label>
            <textarea v-model="approvalNotes" rows="2" class="mt-1 w-full rounded-lg border border-slate-300 py-2 px-3 text-xs"></textarea>
          </div>
          <div class="flex justify-end gap-2 pt-2">
            <button @click="showApprovalModal = false" class="rounded-lg border px-3 py-1.5 text-xs">Batal</button>
            <button @click="submitApproval" class="rounded-lg bg-teal-600 px-4 py-1.5 text-xs font-semibold text-white">Konfirmasi Otorisasi</button>
          </div>
        </div>
      </div>

      <!-- Modal Stage 2 Upload -->
      <div v-if="showStage2Modal" class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/60 p-4">
        <div class="w-full max-w-md rounded-2xl bg-white p-6 shadow-xl space-y-4">
          <h3 class="text-sm font-bold text-slate-900">Dokumentasi Pelaksanaan Stage 2</h3>
          <div>
            <label class="block text-xs font-semibold text-slate-700">Ringkasan Pelaksanaan di Lapangan *</label>
            <textarea v-model="stage2Summary" rows="4" placeholder="Jelaskan perubahan fisik yang telah diselesaikan..." class="mt-1 w-full rounded-lg border border-slate-300 py-2 px-3 text-xs"></textarea>
          </div>
          <div class="flex justify-end gap-2 pt-2">
            <button @click="showStage2Modal = false" class="rounded-lg border px-3 py-1.5 text-xs">Batal</button>
            <button @click="submitStage2" class="rounded-lg bg-teal-600 px-4 py-1.5 text-xs font-semibold text-white">Simpan Stage 2</button>
          </div>
        </div>
      </div>
    </div>
  </ModuleLayout>
</template>
