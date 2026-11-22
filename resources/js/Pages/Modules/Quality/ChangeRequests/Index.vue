<script setup>
import { ref } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import ModuleLayout from '@/Layouts/ModuleLayout.vue';
import {
  GitCommit,
  Plus,
  Search,
  Filter,
  Eye,
  FileDown,
  Calendar,
  AlertTriangle,
  CheckCircle2,
  XCircle,
  Clock,
} from 'lucide-vue-next';

const props = defineProps({
  module: { type: Object, required: true },
  changeRequests: { type: Object, required: true },
  filters: { type: Object, default: () => ({}) },
});

const search = ref(props.filters.search || '');
const selectedType = ref(props.filters.type || '');
const selectedStatus = ref(props.filters.status || '');

const applyFilters = () => {
  router.get('/quality/change-requests', {
    search: search.value || undefined,
    type: selectedType.value || undefined,
    status: selectedStatus.value || undefined,
  }, { preserveState: true, replace: true });
};

const resetFilters = () => {
  search.value = '';
  selectedType.value = '';
  selectedStatus.value = '';
  applyFilters();
};

const getStatusBadge = (status) => {
  switch (status) {
    case 'COMPLETE':
      return 'bg-emerald-50 text-emerald-700 border-emerald-200 dark:bg-emerald-950/50 dark:text-emerald-300';
    case 'APPROVED':
    case 'IN PROGRESS':
      return 'bg-teal-50 text-teal-700 border-teal-200 dark:bg-teal-950/50 dark:text-teal-300';
    case 'IN REVIEW':
    case 'OPEN':
      return 'bg-amber-50 text-amber-700 border-amber-200 dark:bg-amber-950/50 dark:text-amber-300';
    case 'REJECT':
      return 'bg-rose-50 text-rose-700 border-rose-200 dark:bg-rose-950/50 dark:text-rose-300';
    default:
      return 'bg-slate-100 text-slate-700 border-slate-200 dark:bg-slate-800 dark:text-slate-300';
  }
};
</script>

<template>
  <Head title="Manajemen Usulan Perubahan (Change Request CRA/CRB)" />

  <ModuleLayout :module="module">
    <div class="space-y-6">
      <!-- Header -->
      <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
          <h1 class="text-xl font-bold tracking-tight text-slate-900 dark:text-white sm:text-2xl">
            Manajemen Change Request (CRA & CRB)
          </h1>
          <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">
            Siklus usulan perubahan CPOB: CRA (Mayor dengan FMEA RPN) & CRB (Minor), kajian lintas divisi, dan approval bertingkat.
          </p>
        </div>

        <Link
          href="/quality/change-requests/create"
          class="inline-flex items-center gap-1.5 rounded-xl bg-teal-600 px-4 py-2.5 text-xs font-semibold text-white shadow-xs hover:bg-teal-700 transition"
        >
          <Plus class="h-4 w-4" />
          Ajukan Change Request
        </Link>
      </div>

      <!-- Filters & Search Toolbar -->
      <div class="rounded-xl border border-slate-200 bg-white p-4 shadow-xs dark:border-slate-800 dark:bg-slate-900">
        <div class="grid grid-cols-1 gap-3 sm:grid-cols-3 lg:grid-cols-4">
          <div class="relative sm:col-span-2">
            <Search class="pointer-events-none absolute left-3 top-2.5 h-4 w-4 text-slate-400" />
            <input
              v-model="search"
              @keyup.enter="applyFilters"
              type="text"
              placeholder="Cari nomor CR, nama produk, atau usulan..."
              class="w-full rounded-lg border border-slate-300 py-2 pl-9 pr-3 text-xs focus:border-teal-500 focus:outline-none focus:ring-1 focus:ring-teal-500 dark:border-slate-700 dark:bg-slate-800 dark:text-white"
            />
          </div>

          <div>
            <select
              v-model="selectedType"
              @change="applyFilters"
              class="w-full rounded-lg border border-slate-300 py-2 px-3 text-xs focus:border-teal-500 focus:outline-none focus:ring-1 focus:ring-teal-500 dark:border-slate-700 dark:bg-slate-800 dark:text-white"
            >
              <option value="">Semua Tipe (CRA & CRB)</option>
              <option value="CRA">CRA (Mayor / Signifikan - FMEA)</option>
              <option value="CRB">CRB (Minor / Editorial)</option>
            </select>
          </div>

          <div>
            <select
              v-model="selectedStatus"
              @change="applyFilters"
              class="w-full rounded-lg border border-slate-300 py-2 px-3 text-xs focus:border-teal-500 focus:outline-none focus:ring-1 focus:ring-teal-500 dark:border-slate-700 dark:bg-slate-800 dark:text-white"
            >
              <option value="">Semua Status</option>
              <option value="OPEN">OPEN (Diajukan)</option>
              <option value="IN REVIEW">IN REVIEW (Kajian Departemen)</option>
              <option value="IN PROGRESS">IN PROGRESS (Pelaksanaan)</option>
              <option value="COMPLETE">COMPLETE (Selesai & Tutup)</option>
              <option value="REJECT">REJECT (Ditolak)</option>
            </select>
          </div>
        </div>
      </div>

      <!-- Change Requests Table -->
      <div class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-xs dark:border-slate-800 dark:bg-slate-900">
        <div class="overflow-x-auto">
          <table class="w-full text-left text-xs">
            <thead class="bg-slate-50 text-slate-600 border-b border-slate-200 dark:bg-slate-800/60 dark:text-slate-300 dark:border-slate-800">
              <tr>
                <th class="py-3 px-4 font-semibold">Nomor CR & Tanggal</th>
                <th class="py-3 px-4 font-semibold">Tipe</th>
                <th class="py-3 px-4 font-semibold">Produk / Sistem & Sifat</th>
                <th class="py-3 px-4 font-semibold">Usulan Perubahan</th>
                <th class="py-3 px-4 font-semibold text-center">Skor FMEA RPN</th>
                <th class="py-3 px-4 font-semibold text-center">Status</th>
                <th class="py-3 px-4 font-semibold text-right">Aksi</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
              <tr
                v-for="cr in changeRequests.data"
                :key="cr.id"
                class="hover:bg-slate-50/80 dark:hover:bg-slate-800/50 transition"
              >
                <td class="py-3.5 px-4">
                  <div class="font-bold text-slate-900 dark:text-white flex items-center gap-1.5 font-mono">
                    <GitCommit class="h-4 w-4 text-teal-600" />
                    {{ cr.cr_number }}
                  </div>
                  <div class="text-[11px] text-slate-500 mt-0.5">
                    {{ cr.initiator?.name }} &bullet; {{ cr.created_at ? new Date(cr.created_at).toLocaleDateString('id-ID') : '-' }}
                  </div>
                </td>
                <td class="py-3.5 px-4">
                  <span
                    :class="[
                      'inline-flex items-center rounded-md px-2 py-0.5 text-[10px] font-bold border',
                      cr.type === 'CRA'
                        ? 'bg-rose-50 text-rose-700 border-rose-200 dark:bg-rose-950/50 dark:text-rose-300'
                        : 'bg-blue-50 text-blue-700 border-blue-200 dark:bg-blue-950/50 dark:text-blue-300'
                    ]"
                  >
                    {{ cr.type }}
                  </span>
                </td>
                <td class="py-3.5 px-4">
                  <div class="font-semibold text-slate-800 dark:text-slate-200">{{ cr.nama_produk }}</div>
                  <div class="text-[11px] text-slate-500">{{ cr.sifat_perubahan }}</div>
                </td>
                <td class="py-3.5 px-4 max-w-xs">
                  <div class="line-clamp-2 text-slate-600 dark:text-slate-300 leading-snug">
                    {{ cr.usulan_perubahan }}
                  </div>
                </td>
                <td class="py-3.5 px-4 text-center">
                  <div v-if="cr.type === 'CRA'">
                    <span
                      :class="[
                        'inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-bold font-mono',
                        cr.rpn >= 80 ? 'bg-rose-100 text-rose-800 dark:bg-rose-950 dark:text-rose-300' :
                        (cr.rpn >= 40 ? 'bg-amber-100 text-amber-800 dark:bg-amber-950 dark:text-amber-300' :
                        'bg-emerald-100 text-emerald-800 dark:bg-emerald-950 dark:text-emerald-300')
                      ]"
                    >
                      RPN {{ cr.rpn }}
                    </span>
                    <div class="text-[9px] text-slate-400 mt-0.5">S:{{ cr.severity }} O:{{ cr.occurrence }} D:{{ cr.detection }}</div>
                  </div>
                  <div v-else class="text-slate-400 text-[11px] italic">
                    Minor (Non-FMEA)
                  </div>
                </td>
                <td class="py-3.5 px-4 text-center">
                  <span :class="['inline-flex rounded-full px-2.5 py-0.5 text-[10px] font-bold border', getStatusBadge(cr.status)]">
                    {{ cr.status }}
                  </span>
                </td>
                <td class="py-3.5 px-4 text-right">
                  <div class="flex items-center justify-end gap-1.5">
                    <a
                      :href="`/quality/change-requests/${cr.id}/pdf`"
                      target="_blank"
                      class="rounded p-1.5 text-slate-500 hover:bg-slate-100 hover:text-slate-800 dark:hover:bg-slate-800"
                      title="Cetak PDF Resmi"
                    >
                      <FileDown class="h-4 w-4" />
                    </a>
                    <Link
                      :href="`/quality/change-requests/${cr.id}`"
                      class="inline-flex items-center gap-1 rounded-lg bg-teal-50 px-2.5 py-1 text-xs font-semibold text-teal-700 hover:bg-teal-100 dark:bg-teal-950/60 dark:text-teal-300"
                    >
                      <Eye class="h-3.5 w-3.5" />
                      Detail
                    </Link>
                  </div>
                </td>
              </tr>

              <tr v-if="changeRequests.data.length === 0">
                <td colspan="7" class="py-12 text-center text-slate-400">
                  Belum ada data usulan perubahan (Change Request).
                </td>
              </tr>
            </tbody>
          </table>
        </div>

        <!-- Pagination -->
        <div v-if="changeRequests.links && changeRequests.links.length > 3" class="flex items-center justify-between border-t border-slate-200 px-4 py-3 dark:border-slate-800">
          <div class="text-xs text-slate-500">
            Menampilkan {{ changeRequests.from }} sampai {{ changeRequests.to }} dari total {{ changeRequests.total }} usulan
          </div>
          <div class="flex items-center gap-1">
            <Link
              v-for="(link, i) in changeRequests.links"
              :key="i"
              :href="link.url || '#'"
              v-html="link.label"
              :class="[
                'px-2.5 py-1 text-xs rounded transition',
                link.active ? 'bg-teal-600 text-white font-bold' : 'text-slate-600 hover:bg-slate-100 dark:text-slate-300 dark:hover:bg-slate-800',
                !link.url ? 'opacity-40 pointer-events-none' : ''
              ]"
            />
          </div>
        </div>
      </div>
    </div>
  </ModuleLayout>
</template>
