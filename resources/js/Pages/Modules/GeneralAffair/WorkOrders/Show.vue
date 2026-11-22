<script setup>
import { ref } from 'vue';
import { Head, Link, useForm, router } from '@inertiajs/vue3';
import ModuleLayout from '@/Layouts/ModuleLayout.vue';
import {
  Wrench,
  ArrowLeft,
  FileText,
  Clock,
  UserCheck,
  CheckCircle2,
  AlertTriangle,
  Plus,
  Star,
  Check,
  X,
  Send,
  ClipboardCheck,
  Package,
} from 'lucide-vue-next';

const props = defineProps({
  module: { type: Object, required: true },
  workOrder: { type: Object, required: true },
  engineers: { type: Array, default: () => [] },
});

// Forms
const assignForm = useForm({
  assigned_engineer_id: props.workOrder.assigned_engineer_id || '',
});

const checkForm = useForm({
  check_result: props.workOrder.check_result || '',
  action_plan: props.workOrder.action_plan || '',
  needs_materials: false,
  materials: [
    { material_name: '', quantity: 1, uom_id: null, estimated_unit_price: 0 },
  ],
});

const progressForm = useForm({
  progress_percentage: 50,
  notes: '',
  attachment_path: '',
});

const handoverForm = useForm({
  root_cause: '',
  action_taken: '',
  engineer_signature: 'Digital Signature - Teknisi FPU',
});

const confirmHandoverForm = useForm({
  rating: 5,
  feedback: '',
  client_signature: 'Digital Signature - Pemohon',
});

const addMaterialRow = () => {
  checkForm.materials.push({
    material_name: '',
    quantity: 1,
    uom_id: null,
    estimated_unit_price: 0,
  });
};

const removeMaterialRow = (index) => {
  if (checkForm.materials.length > 1) {
    checkForm.materials.splice(index, 1);
  }
};

const submitAssign = () => {
  assignForm.post(`/general-affair/work-orders/${props.workOrder.id}/assign`);
};

const submitCheck = () => {
  checkForm.post(`/general-affair/work-orders/${props.workOrder.id}/check`);
};

const submitProgress = () => {
  progressForm.post(`/general-affair/work-orders/${props.workOrder.id}/progress`, {
    onSuccess: () => {
      progressForm.reset();
    },
  });
};

const submitFpuApproval = (tier, decision) => {
  if (!props.workOrder.fpu_request) return;
  router.post(`/general-affair/work-orders/fpu/${props.workOrder.fpu_request.id}/approve`, {
    tier,
    decision,
    notes: `Persetujuan ${tier.toUpperCase()} dari halaman detail WO`,
  });
};

const submitHandover = () => {
  handoverForm.post(`/general-affair/work-orders/${props.workOrder.id}/handover`);
};

const submitConfirmHandover = () => {
  confirmHandoverForm.post(`/general-affair/work-orders/${props.workOrder.id}/confirm-handover`);
};
</script>

<template>
  <Head :title="`Work Order ${workOrder.wo_number}`" />

  <ModuleLayout :module="module">
    <div class="max-w-5xl mx-auto space-y-6">
      <!-- Top Bar -->
      <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div class="flex items-center gap-3">
          <Link
            href="/general-affair/work-orders"
            class="rounded-lg p-2 text-slate-500 hover:bg-slate-100 dark:hover:bg-slate-800 transition"
          >
            <ArrowLeft class="w-5 h-5" />
          </Link>
          <div>
            <div class="flex items-center gap-3">
              <h1 class="text-xl font-bold tracking-tight text-slate-900 dark:text-white sm:text-2xl">
                {{ workOrder.wo_number }}
              </h1>
              <span class="inline-flex items-center rounded-full bg-amber-50 px-2.5 py-0.5 text-xs font-semibold text-amber-700 border border-amber-200 dark:bg-amber-950/40 dark:text-amber-300">
                {{ workOrder.status }}
              </span>
            </div>
            <p class="text-sm text-slate-500 dark:text-slate-400">
              Kategori: {{ workOrder.category }} &bull; Prioritas: {{ workOrder.priority }} &bull; Lokasi: {{ workOrder.location?.name ?? '-' }}
            </p>
          </div>
        </div>

        <div class="flex items-center gap-2">
          <a
            :href="`/general-affair/work-orders/${workOrder.id}/pdf`"
            target="_blank"
            class="inline-flex items-center gap-2 rounded-lg border border-slate-200 bg-white px-3.5 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50 shadow-sm dark:border-slate-700 dark:bg-slate-800 dark:text-slate-300"
          >
            <FileText class="w-4 h-4 text-amber-600" />
            Cetak WO (PDF)
          </a>
          <a
            v-if="workOrder.fpu_request"
            :href="`/general-affair/work-orders/${workOrder.id}/pdf?type=fpu`"
            target="_blank"
            class="inline-flex items-center gap-2 rounded-lg border border-amber-200 bg-amber-50 px-3.5 py-2 text-sm font-medium text-amber-800 hover:bg-amber-100 dark:border-amber-900/50 dark:bg-amber-950/40 dark:text-amber-300"
          >
            <Package class="w-4 h-4" />
            Cetak Form C FPU
          </a>
        </div>
      </div>

      <!-- Lifecycle Steps Navigation -->
      <div class="grid grid-cols-4 gap-2 text-center text-xs font-semibold">
        <div class="p-2.5 rounded-lg bg-amber-500 text-white shadow-sm">
          1. Form A: Keluhan
        </div>
        <div :class="['p-2.5 rounded-lg border', workOrder.check_result ? 'bg-amber-500 text-white' : 'bg-slate-100 text-slate-600 dark:bg-slate-800 dark:text-slate-400']">
          2. Form B: Pengecekan
        </div>
        <div :class="['p-2.5 rounded-lg border', workOrder.fpu_request ? 'bg-amber-500 text-white' : 'bg-slate-100 text-slate-600 dark:bg-slate-800 dark:text-slate-400']">
          3. Form C: FPU Material
        </div>
        <div :class="['p-2.5 rounded-lg border', workOrder.handover ? 'bg-amber-500 text-white' : 'bg-slate-100 text-slate-600 dark:bg-slate-800 dark:text-slate-400']">
          4. Form D: BAST Selesai
        </div>
      </div>

      <!-- Section 1: Form A Detail -->
      <div class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900 space-y-4">
        <h2 class="text-base font-bold text-slate-900 dark:text-white flex items-center gap-2">
          <FileText class="w-5 h-5 text-amber-600" />
          Form A: Permintaan & Keluhan Kerusakan
        </h2>
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 text-sm bg-slate-50 dark:bg-slate-800/50 p-4 rounded-lg">
          <div>
            <span class="text-xs text-slate-400 block">Pemohon:</span>
            <span class="font-medium text-slate-800 dark:text-slate-200">{{ workOrder.user?.name ?? '-' }} ({{ workOrder.department?.name ?? '-' }})</span>
          </div>
          <div>
            <span class="text-xs text-slate-400 block">Lokasi & Plant:</span>
            <span class="font-medium text-slate-800 dark:text-slate-200">{{ workOrder.location?.name ?? '-' }} (Plant {{ workOrder.location?.plant?.name ?? '-' }})</span>
          </div>
          <div>
            <span class="text-xs text-slate-400 block">Tanggal Diajukan:</span>
            <span class="font-medium text-slate-800 dark:text-slate-200">{{ workOrder.created_at ? new Date(workOrder.created_at).toLocaleString('id-ID') : '-' }}</span>
          </div>
        </div>
        <div>
          <span class="text-xs text-slate-400 block mb-1">Uraian Keluhan:</span>
          <p class="text-sm text-slate-800 dark:text-slate-200 leading-relaxed bg-white dark:bg-slate-800 p-3 rounded-lg border border-slate-200 dark:border-slate-700">
            {{ workOrder.complaint_description }}
          </p>
        </div>
      </div>

      <!-- Section 2: Triage & Penugasan Teknisi -->
      <div class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900 space-y-4">
        <h2 class="text-base font-bold text-slate-900 dark:text-white flex items-center gap-2">
          <UserCheck class="w-5 h-5 text-indigo-600" />
          Triage & Penugasan Teknisi FPU
        </h2>

        <div v-if="workOrder.assigned_engineer" class="flex items-center justify-between p-4 bg-indigo-50 dark:bg-indigo-950/30 rounded-lg border border-indigo-100 dark:border-indigo-900/40">
          <div>
            <span class="text-xs text-indigo-500 font-medium">Teknisi Ditugaskan:</span>
            <p class="text-sm font-bold text-indigo-900 dark:text-indigo-300">{{ workOrder.assigned_engineer.name }} ({{ workOrder.assigned_engineer.email }})</p>
            <span class="text-xs text-slate-400">Ditugaskan pada: {{ workOrder.assigned_at ? new Date(workOrder.assigned_at).toLocaleString('id-ID') : '-' }}</span>
          </div>
          <span class="px-2.5 py-1 text-xs font-semibold bg-indigo-600 text-white rounded-full">Aktif</span>
        </div>

        <form v-else @submit.prevent="submitAssign" class="flex flex-col sm:flex-row gap-3 items-end">
          <div class="flex-1">
            <label class="block text-xs font-medium text-slate-500 mb-1">Pilih Teknisi untuk Menangani WO:</label>
            <select
              v-model="assignForm.assigned_engineer_id"
              class="w-full rounded-lg border border-slate-200 bg-slate-50 px-3 py-2 text-sm text-slate-900 focus:border-amber-500 focus:outline-none dark:border-slate-700 dark:bg-slate-800 dark:text-white"
              required
            >
              <option value="">-- Pilih Teknisi --</option>
              <option v-for="eng in engineers" :key="eng.id" :value="eng.id">
                {{ eng.name }} ({{ eng.email }})
              </option>
            </select>
          </div>
          <button
            type="submit"
            :disabled="assignForm.processing"
            class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-500 transition disabled:opacity-50"
          >
            Tugaskan Teknisi
          </button>
        </form>
      </div>

      <!-- Section 3: Form B (Diagnosa Teknisi) -->
      <div v-if="workOrder.assigned_engineer" class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900 space-y-4">
        <h2 class="text-base font-bold text-slate-900 dark:text-white flex items-center gap-2">
          <Wrench class="w-5 h-5 text-amber-600" />
          Form B: Hasil Pemeriksaan & Diagnosa Teknisi
        </h2>

        <div v-if="workOrder.check_result" class="space-y-3">
          <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div class="p-4 bg-slate-50 dark:bg-slate-800/50 rounded-lg">
              <span class="text-xs font-semibold text-slate-500 block mb-1">Hasil Diagnosis Pengecekan:</span>
              <p class="text-sm text-slate-800 dark:text-slate-200">{{ workOrder.check_result }}</p>
            </div>
            <div class="p-4 bg-slate-50 dark:bg-slate-800/50 rounded-lg">
              <span class="text-xs font-semibold text-slate-500 block mb-1">Rencana Tindakan Perbaikan:</span>
              <p class="text-sm text-slate-800 dark:text-slate-200">{{ workOrder.action_plan }}</p>
            </div>
          </div>
          <div class="text-xs text-slate-500">
            Kebutuhan Pembelian Material: <strong class="text-slate-900 dark:text-white">{{ workOrder.needs_materials ? 'YA (Form C Aktif)' : 'TIDAK (Menggunakan Stok / Tanpa Komponen Baru)' }}</strong>
          </div>
        </div>

        <form v-else @submit.prevent="submitCheck" class="space-y-4">
          <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
              <label class="block text-xs font-medium text-slate-500 mb-1">Hasil Diagnosis / Temuan:</label>
              <textarea
                v-model="checkForm.check_result"
                rows="3"
                placeholder="Jelaskan kondisi kerusakan setelah dilakukan inspeksi fisik..."
                class="w-full rounded-lg border border-slate-200 bg-slate-50 p-2.5 text-sm text-slate-900 dark:border-slate-700 dark:bg-slate-800 dark:text-white"
                required
              ></textarea>
            </div>
            <div>
              <label class="block text-xs font-medium text-slate-500 mb-1">Rencana Tindakan:</label>
              <textarea
                v-model="checkForm.action_plan"
                rows="3"
                placeholder="Langkah perbaikan yang akan dikerjakan..."
                class="w-full rounded-lg border border-slate-200 bg-slate-50 p-2.5 text-sm text-slate-900 dark:border-slate-700 dark:bg-slate-800 dark:text-white"
                required
              ></textarea>
            </div>
          </div>

          <div class="flex items-center gap-2">
            <input
              type="checkbox"
              id="needsMaterials"
              v-model="checkForm.needs_materials"
              class="rounded border-slate-300 text-amber-600 focus:ring-amber-500"
            />
            <label for="needsMaterials" class="text-sm font-medium text-slate-700 dark:text-slate-300">
              Perlu Pembelian Material / Suku Cadang (Otomatis Buat Form C FPU)
            </label>
          </div>

          <!-- Dynamic Materials Input -->
          <div v-if="checkForm.needs_materials" class="p-4 bg-amber-50/50 dark:bg-amber-950/20 rounded-lg border border-amber-200 dark:border-amber-900/40 space-y-3">
            <div class="flex items-center justify-between">
              <span class="text-xs font-bold text-amber-800 dark:text-amber-300 uppercase">Daftar Material yang Dibutuhkan:</span>
              <button
                type="button"
                @click="addMaterialRow"
                class="inline-flex items-center gap-1 text-xs font-semibold text-amber-700 hover:text-amber-800"
              >
                <Plus class="w-3.5 h-3.5" /> Tambah Material
              </button>
            </div>

            <div v-for="(mat, idx) in checkForm.materials" :key="idx" class="grid grid-cols-12 gap-2 items-center">
              <div class="col-span-5">
                <input
                  v-model="mat.material_name"
                  type="text"
                  placeholder="Nama material / part..."
                  class="w-full rounded border border-slate-200 bg-white px-2.5 py-1.5 text-xs text-slate-900 dark:border-slate-700 dark:bg-slate-800 dark:text-white"
                  required
                />
              </div>
              <div class="col-span-3">
                <input
                  v-model.number="mat.quantity"
                  type="number"
                  min="1"
                  placeholder="Qty"
                  class="w-full rounded border border-slate-200 bg-white px-2.5 py-1.5 text-xs text-slate-900 dark:border-slate-700 dark:bg-slate-800 dark:text-white"
                  required
                />
              </div>
              <div class="col-span-3">
                <input
                  v-model.number="mat.estimated_unit_price"
                  type="number"
                  min="0"
                  placeholder="Estimasi Harga"
                  class="w-full rounded border border-slate-200 bg-white px-2.5 py-1.5 text-xs text-slate-900 dark:border-slate-700 dark:bg-slate-800 dark:text-white"
                />
              </div>
              <div class="col-span-1 text-center">
                <button
                  type="button"
                  @click="removeMaterialRow(idx)"
                  class="text-red-500 hover:text-red-700"
                >
                  <X class="w-4 h-4" />
                </button>
              </div>
            </div>
          </div>

          <div class="flex justify-end">
            <button
              type="submit"
              :disabled="checkForm.processing"
              class="rounded-lg bg-amber-600 px-4 py-2 text-sm font-semibold text-white hover:bg-amber-500 transition disabled:opacity-50"
            >
              Simpan Form B
            </button>
          </div>
        </form>
      </div>

      <!-- Section 4: Form C (FPU Material & Multi-Tier Approvals) -->
      <div v-if="workOrder.fpu_request" class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900 space-y-4">
        <div class="flex items-center justify-between">
          <h2 class="text-base font-bold text-slate-900 dark:text-white flex items-center gap-2">
            <Package class="w-5 h-5 text-purple-600" />
            Form C: Pengadaan Material FPU ({{ workOrder.fpu_request.fpu_number }})
          </h2>
          <span class="px-2.5 py-0.5 text-xs font-semibold rounded-full bg-purple-50 text-purple-700 border border-purple-200 dark:bg-purple-950/40 dark:text-purple-300">
            {{ workOrder.fpu_request.status }}
          </span>
        </div>

        <!-- Materials Table -->
        <table class="w-full text-xs text-left">
          <thead class="bg-slate-50 dark:bg-slate-800 text-slate-500">
            <tr>
              <th class="p-2">Material / Part</th>
              <th class="p-2 text-right">Jumlah</th>
              <th class="p-2 text-right">Estimasi Satuan</th>
              <th class="p-2 text-right">Total Estimasi</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
            <tr v-for="mat in workOrder.fpu_request.materials" :key="mat.id">
              <td class="p-2 font-medium">{{ mat.material_name }}</td>
              <td class="p-2 text-right">{{ mat.quantity }} {{ mat.uom?.code ?? 'Pcs' }}</td>
              <td class="p-2 text-right">Rp {{ Number(mat.estimated_unit_price).toLocaleString('id-ID') }}</td>
              <td class="p-2 text-right font-semibold">Rp {{ Number(mat.total_estimated_price).toLocaleString('id-ID') }}</td>
            </tr>
          </tbody>
          <tfoot>
            <tr class="font-bold bg-amber-50/40 dark:bg-amber-950/20">
              <td colspan="3" class="p-2 text-right">TOTAL BIAYA MATERIAL:</td>
              <td class="p-2 text-right text-amber-700 dark:text-amber-300">
                Rp {{ Number(workOrder.fpu_request.total_estimated_cost).toLocaleString('id-ID') }}
              </td>
            </tr>
          </tfoot>
        </table>

        <!-- Approval Tier Buttons -->
        <div class="pt-4 border-t border-slate-200 dark:border-slate-800 space-y-3">
          <span class="text-xs font-bold text-slate-700 dark:text-slate-300">Otorisasi Berjenjang Form C:</span>
          <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
            <!-- Tier 1: HRGA -->
            <div class="p-3 rounded-lg border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 flex items-center justify-between">
              <div>
                <span class="text-xs font-semibold block">Staff HRGA</span>
                <span class="text-[11px] text-slate-400">{{ workOrder.fpu_request.approved_by_hrga ? 'Disetujui' : 'Menunggu' }}</span>
              </div>
              <button
                v-if="!workOrder.fpu_request.approved_by_hrga"
                @click="submitFpuApproval('hrga', 'approve')"
                class="px-2.5 py-1 text-xs font-semibold bg-emerald-600 text-white rounded hover:bg-emerald-500 transition"
              >
                Approve
              </button>
              <CheckCircle2 v-else class="w-5 h-5 text-emerald-500" />
            </div>

            <!-- Tier 2: Manager -->
            <div class="p-3 rounded-lg border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 flex items-center justify-between">
              <div>
                <span class="text-xs font-semibold block">Manager GA / Plant</span>
                <span class="text-[11px] text-slate-400">{{ workOrder.fpu_request.approved_by_manager ? 'Disetujui' : 'Menunggu' }}</span>
              </div>
              <button
                v-if="!workOrder.fpu_request.approved_by_manager"
                @click="submitFpuApproval('manager', 'approve')"
                class="px-2.5 py-1 text-xs font-semibold bg-emerald-600 text-white rounded hover:bg-emerald-500 transition"
              >
                Approve
              </button>
              <CheckCircle2 v-else class="w-5 h-5 text-emerald-500" />
            </div>

            <!-- Tier 3: GM -->
            <div class="p-3 rounded-lg border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 flex items-center justify-between">
              <div>
                <span class="text-xs font-semibold block">General Manager</span>
                <span class="text-[11px] text-slate-400">{{ workOrder.fpu_request.approved_by_gm ? 'Disetujui' : (workOrder.fpu_request.total_estimated_cost > 5000000 ? 'Perlu Approve' : 'N/A') }}</span>
              </div>
              <button
                v-if="!workOrder.fpu_request.approved_by_gm && workOrder.fpu_request.total_estimated_cost > 5000000"
                @click="submitFpuApproval('gm', 'approve')"
                class="px-2.5 py-1 text-xs font-semibold bg-emerald-600 text-white rounded hover:bg-emerald-500 transition"
              >
                Approve
              </button>
              <CheckCircle2 v-else-if="workOrder.fpu_request.approved_by_gm" class="w-5 h-5 text-emerald-500" />
            </div>
          </div>
        </div>
      </div>

      <!-- Section 5: Log Progres Pekerjaan -->
      <div v-if="workOrder.assigned_engineer" class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900 space-y-4">
        <h2 class="text-base font-bold text-slate-900 dark:text-white flex items-center gap-2">
          <Clock class="w-5 h-5 text-blue-600" />
          Progres & Log Pekerjaan FPU
        </h2>

        <!-- Progress Timeline -->
        <div v-if="workOrder.progresses && workOrder.progresses.length > 0" class="space-y-3">
          <div
            v-for="prog in workOrder.progresses"
            :key="prog.id"
            class="p-3 rounded-lg border border-slate-100 bg-slate-50 dark:border-slate-800 dark:bg-slate-800/40 flex items-start justify-between"
          >
            <div>
              <div class="flex items-center gap-2">
                <span class="px-2 py-0.5 text-xs font-bold bg-blue-600 text-white rounded">{{ prog.progress_percentage }}%</span>
                <span class="text-xs font-medium text-slate-800 dark:text-slate-200">{{ prog.user?.name ?? 'Teknisi' }}</span>
                <span class="text-xs text-slate-400">&bull; {{ new Date(prog.logged_at).toLocaleString('id-ID') }}</span>
              </div>
              <p class="text-xs text-slate-600 dark:text-slate-300 mt-1">{{ prog.notes }}</p>
            </div>
          </div>
        </div>

        <!-- Add Progress Form -->
        <form @submit.prevent="submitProgress" class="flex flex-col sm:flex-row gap-3 items-end pt-3 border-t border-slate-200 dark:border-slate-800">
          <div class="w-24">
            <label class="block text-xs font-medium text-slate-500 mb-1">Progres (%):</label>
            <input
              v-model.number="progressForm.progress_percentage"
              type="number"
              min="1"
              max="100"
              class="w-full rounded-lg border border-slate-200 bg-slate-50 px-3 py-2 text-sm text-slate-900 dark:border-slate-700 dark:bg-slate-800 dark:text-white"
              required
            />
          </div>
          <div class="flex-1">
            <label class="block text-xs font-medium text-slate-500 mb-1">Catatan Tindakan / Pekerjaan:</label>
            <input
              v-model="progressForm.notes"
              type="text"
              placeholder="Cth: Mengganti kabel power dan uji coba 30 menit..."
              class="w-full rounded-lg border border-slate-200 bg-slate-50 px-3 py-2 text-sm text-slate-900 dark:border-slate-700 dark:bg-slate-800 dark:text-white"
              required
            />
          </div>
          <button
            type="submit"
            :disabled="progressForm.processing"
            class="rounded-lg bg-blue-600 px-4 py-2 text-sm font-semibold text-white hover:bg-blue-500 transition disabled:opacity-50"
          >
            Catat Progres
          </button>
        </form>
      </div>

      <!-- Section 6: Form D (BAST Serah Terima) -->
      <div v-if="workOrder.assigned_engineer" class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900 space-y-4">
        <h2 class="text-base font-bold text-slate-900 dark:text-white flex items-center gap-2">
          <ClipboardCheck class="w-5 h-5 text-emerald-600" />
          Form D: Berita Acara Serah Terima (BAST) & Rating
        </h2>

        <!-- If BAST already exists -->
        <div v-if="workOrder.handover" class="space-y-4">
          <div class="p-4 bg-emerald-50 dark:bg-emerald-950/30 rounded-lg border border-emerald-200 dark:border-emerald-900/40">
            <div class="flex items-center justify-between mb-2">
              <span class="font-bold text-emerald-800 dark:text-emerald-300">BAST Nomor: {{ workOrder.handover.bast_number }}</span>
              <span class="text-xs text-emerald-600 font-medium">Tgl: {{ workOrder.handover.handover_date }}</span>
            </div>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-xs">
              <div>
                <span class="text-slate-400 block">Penyebab Masalah:</span>
                <span class="font-medium text-slate-800 dark:text-slate-200">{{ workOrder.handover.root_cause }}</span>
              </div>
              <div>
                <span class="text-slate-400 block">Solusi yang Diterapkan:</span>
                <span class="font-medium text-slate-800 dark:text-slate-200">{{ workOrder.handover.action_taken }}</span>
              </div>
            </div>
          </div>

          <!-- Rating confirmation by Client -->
          <div v-if="workOrder.handover.rating" class="p-4 bg-slate-50 dark:bg-slate-800 rounded-lg flex items-center justify-between">
            <div>
              <span class="text-xs text-slate-400 block">Rating Kepuasan Pemohon:</span>
              <div class="flex items-center gap-1 text-amber-500 mt-1">
                <Star v-for="i in 5" :key="i" :class="['w-4 h-4', i <= workOrder.handover.rating ? 'fill-current' : 'text-slate-300']" />
                <span class="text-xs font-bold text-slate-700 dark:text-slate-300 ml-1">({{ workOrder.handover.rating }} / 5)</span>
              </div>
              <p v-if="workOrder.handover.feedback" class="text-xs text-slate-600 dark:text-slate-300 mt-1 italic">
                "{{ workOrder.handover.feedback }}"
              </p>
            </div>
            <span class="px-3 py-1 bg-emerald-600 text-white text-xs font-semibold rounded-full">Selesai & Diterima</span>
          </div>

          <form v-else @submit.prevent="submitConfirmHandover" class="p-4 bg-amber-50/50 dark:bg-amber-950/20 rounded-lg border border-amber-200 dark:border-amber-900/40 space-y-3">
            <span class="text-xs font-bold text-amber-800 dark:text-amber-300 uppercase">Konfirmasi & Beri Nilai Hasil Pekerjaan (Pemohon):</span>
            <div class="flex items-center gap-2">
              <label class="text-xs font-medium text-slate-600 dark:text-slate-300">Nilai Bintang (1-5):</label>
              <select
                v-model.number="confirmHandoverForm.rating"
                class="rounded border border-slate-200 bg-white px-2.5 py-1 text-xs"
              >
                <option value="5">5 Bintang (Sangat Memuaskan)</option>
                <option value="4">4 Bintang (Baik)</option>
                <option value="3">3 Bintang (Cukup)</option>
                <option value="2">2 Bintang (Kurang)</option>
                <option value="1">1 Bintang (Tidak Memuaskan)</option>
              </select>
            </div>
            <div>
              <input
                v-model="confirmHandoverForm.feedback"
                type="text"
                placeholder="Feedback atau ulasan kepuasan hasil perbaikan..."
                class="w-full rounded border border-slate-200 bg-white p-2 text-xs text-slate-900"
              />
            </div>
            <button
              type="submit"
              :disabled="confirmHandoverForm.processing"
              class="rounded-lg bg-emerald-600 px-4 py-1.5 text-xs font-semibold text-white hover:bg-emerald-500 transition"
            >
              Konfirmasi Penerimaan BAST
            </button>
          </form>
        </div>

        <!-- Submit Handover Form (Engineer) -->
        <form v-else @submit.prevent="submitHandover" class="space-y-4">
          <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
              <label class="block text-xs font-medium text-slate-500 mb-1">Akar Masalah (Root Cause):</label>
              <textarea
                v-model="handoverForm.root_cause"
                rows="2"
                placeholder="Penyebab kerusakan teknis..."
                class="w-full rounded-lg border border-slate-200 bg-slate-50 p-2 text-sm text-slate-900 dark:border-slate-700 dark:bg-slate-800 dark:text-white"
                required
              ></textarea>
            </div>
            <div>
              <label class="block text-xs font-medium text-slate-500 mb-1">Solusi yang Diterapkan:</label>
              <textarea
                v-model="handoverForm.action_taken"
                rows="2"
                placeholder="Rincian perbaikan yang telah tuntas..."
                class="w-full rounded-lg border border-slate-200 bg-slate-50 p-2 text-sm text-slate-900 dark:border-slate-700 dark:bg-slate-800 dark:text-white"
                required
              ></textarea>
            </div>
          </div>
          <div class="flex justify-end">
            <button
              type="submit"
              :disabled="handoverForm.processing"
              class="rounded-lg bg-emerald-600 px-5 py-2 text-sm font-semibold text-white hover:bg-emerald-500 transition disabled:opacity-50"
            >
              Ajukan BAST Form D
            </button>
          </div>
        </form>
      </div>
    </div>
  </ModuleLayout>
</template>
