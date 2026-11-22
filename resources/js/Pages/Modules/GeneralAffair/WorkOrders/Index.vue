<script setup>
import { ref } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import ModuleLayout from '@/Layouts/ModuleLayout.vue';
import {
  Wrench,
  Plus,
  Search,
  Filter,
  ArrowRight,
  FileText,
  Clock,
  CheckCircle2,
  AlertCircle,
  Eye,
} from 'lucide-vue-next';

const props = defineProps({
  module: { type: Object, required: true },
  workOrders: { type: Object, required: true },
  filters: { type: Object, default: () => ({}) },
  departments: { type: Array, default: () => [] },
  stats: { type: Object, default: () => ({}) },
});

const search = ref(props.filters.search || '');
const selectedStatus = ref(props.filters.status || '');
const selectedPriority = ref(props.filters.priority || '');
const selectedCategory = ref(props.filters.category || '');
const selectedDept = ref(props.filters.department_id || '');

const applyFilters = () => {
  router.get('/general-affair/work-orders', {
    search: search.value || undefined,
    status: selectedStatus.value || undefined,
    priority: selectedPriority.value || undefined,
    category: selectedCategory.value || undefined,
    department_id: selectedDept.value || undefined,
  }, { preserveState: true, replace: true });
};

const resetFilters = () => {
  search.value = '';
  selectedStatus.value = '';
  selectedPriority.value = '';
  selectedCategory.value = '';
  selectedDept.value = '';
  applyFilters();
};

const getPriorityBadge = (priority) => {
  switch (priority) {
    case 'Darurat':
      return 'bg-red-50 text-red-700 border-red-200 dark:bg-red-950/40 dark:text-red-300';
    case 'Tinggi':
      return 'bg-amber-50 text-amber-700 border-amber-200 dark:bg-amber-950/40 dark:text-amber-300';
    case 'Normal':
      return 'bg-sky-50 text-sky-700 border-sky-200 dark:bg-sky-950/40 dark:text-sky-300';
    default:
      return 'bg-slate-100 text-slate-700 border-slate-200 dark:bg-slate-800 dark:text-slate-300';
  }
};

const getStatusBadge = (status) => {
  switch (status) {
    case 'Selesai':
      return 'bg-emerald-50 text-emerald-700 border-emerald-200 dark:bg-emerald-950/40 dark:text-emerald-300';
    case 'Dalam Pengerjaan':
      return 'bg-blue-50 text-blue-700 border-blue-200 dark:bg-blue-950/40 dark:text-blue-300';
    case 'Menunggu Penugasan':
      return 'bg-amber-50 text-amber-700 border-amber-200 dark:bg-amber-950/40 dark:text-amber-300';
    case 'Pengecekan Engineer':
      return 'bg-indigo-50 text-indigo-700 border-indigo-200 dark:bg-indigo-950/40 dark:text-indigo-300';
    case 'Menunggu Pembelian Material':
      return 'bg-purple-50 text-purple-700 border-purple-200 dark:bg-purple-950/40 dark:text-purple-300';
    default:
      return 'bg-slate-100 text-slate-700 border-slate-200 dark:bg-slate-800 dark:text-slate-300';
  }
};
</script>

<template>
  <Head title="Work Orders (FPU) - General Affair" />

  <ModuleLayout :module="module">
    <div class="space-y-6">
      <!-- Header -->
      <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
          <h1 class="text-xl font-bold tracking-tight text-slate-900 dark:text-white sm:text-2xl">
            Work Order Pemeliharaan Fasilitas (FPU)
          </h1>
          <p class="text-sm text-slate-500 dark:text-slate-400">
            Form A (Pengajuan Keluhan), Form B (Diagnosa Teknisi), Form C (FPU Material), dan Form D (BAST Serah Terima).
          </p>
        </div>
        <div class="flex items-center gap-3">
          <Link
            href="/general-affair/work-orders/create"
            class="inline-flex items-center gap-2 rounded-lg bg-amber-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-amber-500 transition"
          >
            <Plus class="h-4 w-4" />
            Buat Work Order (Form A)
          </Link>
        </div>
      </div>

      <!-- Stats Bar -->
      <div class="grid grid-cols-2 gap-4 sm:grid-cols-5">
        <div class="rounded-xl border border-slate-200 bg-white p-4 dark:border-slate-800 dark:bg-slate-900">
          <span class="text-xs font-medium text-slate-500 dark:text-slate-400">Total Work Order</span>
          <p class="mt-1 text-2xl font-bold text-slate-900 dark:text-white">{{ stats.total ?? 0 }}</p>
        </div>
        <div class="rounded-xl border border-slate-200 bg-white p-4 dark:border-slate-800 dark:bg-slate-900">
          <span class="text-xs font-medium text-amber-600 dark:text-amber-400">Menunggu Penugasan</span>
          <p class="mt-1 text-2xl font-bold text-amber-600 dark:text-amber-400">{{ stats.menunggu_penugasan ?? 0 }}</p>
        </div>
        <div class="rounded-xl border border-slate-200 bg-white p-4 dark:border-slate-800 dark:bg-slate-900">
          <span class="text-xs font-medium text-blue-600 dark:text-blue-400">Sedang Dikerjakan</span>
          <p class="mt-1 text-2xl font-bold text-blue-600 dark:text-blue-400">{{ stats.sedang_dikerjakan ?? 0 }}</p>
        </div>
        <div class="rounded-xl border border-slate-200 bg-white p-4 dark:border-slate-800 dark:bg-slate-900">
          <span class="text-xs font-medium text-purple-600 dark:text-purple-400">Menunggu Material (FPU)</span>
          <p class="mt-1 text-2xl font-bold text-purple-600 dark:text-purple-400">{{ stats.menunggu_material ?? 0 }}</p>
        </div>
        <div class="rounded-xl border border-slate-200 bg-white p-4 dark:border-slate-800 dark:bg-slate-900">
          <span class="text-xs font-medium text-emerald-600 dark:text-emerald-400">Selesai (BAST)</span>
          <p class="mt-1 text-2xl font-bold text-emerald-600 dark:text-emerald-400">{{ stats.selesai ?? 0 }}</p>
        </div>
      </div>

      <!-- Filters -->
      <div class="rounded-xl border border-slate-200 bg-white p-4 dark:border-slate-800 dark:bg-slate-900">
        <div class="grid grid-cols-1 gap-3 sm:grid-cols-5">
          <div class="relative sm:col-span-2">
            <Search class="absolute left-3 top-2.5 h-4 w-4 text-slate-400" />
            <input
              v-model="search"
              type="text"
              placeholder="Cari nomor WO atau deskripsi keluhan..."
              class="w-full rounded-lg border border-slate-200 bg-slate-50 pl-9 pr-3 py-2 text-sm text-slate-900 focus:border-amber-500 focus:outline-none dark:border-slate-700 dark:bg-slate-800 dark:text-white"
              @keyup.enter="applyFilters"
            />
          </div>

          <div>
            <select
              v-model="selectedStatus"
              class="w-full rounded-lg border border-slate-200 bg-slate-50 px-3 py-2 text-sm text-slate-900 focus:border-amber-500 focus:outline-none dark:border-slate-700 dark:bg-slate-800 dark:text-white"
              @change="applyFilters"
            >
              <option value="">Semua Status</option>
              <option value="Menunggu Penugasan">Menunggu Penugasan</option>
              <option value="Pengecekan Engineer">Pengecekan Engineer</option>
              <option value="Menunggu Pembelian Material">Menunggu Material</option>
              <option value="Dalam Pengerjaan">Dalam Pengerjaan</option>
              <option value="Selesai">Selesai</option>
            </select>
          </div>

          <div>
            <select
              v-model="selectedPriority"
              class="w-full rounded-lg border border-slate-200 bg-slate-50 px-3 py-2 text-sm text-slate-900 focus:border-amber-500 focus:outline-none dark:border-slate-700 dark:bg-slate-800 dark:text-white"
              @change="applyFilters"
            >
              <option value="">Semua Prioritas</option>
              <option value="Darurat">Darurat</option>
              <option value="Tinggi">Tinggi</option>
              <option value="Normal">Normal</option>
              <option value="Rendah">Rendah</option>
            </select>
          </div>

          <div class="flex items-center gap-2">
            <button
              @click="applyFilters"
              class="flex-1 rounded-lg bg-amber-600 px-3 py-2 text-sm font-medium text-white hover:bg-amber-500 transition"
            >
              Terapkan
            </button>
            <button
              @click="resetFilters"
              class="rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm text-slate-600 hover:bg-slate-50 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-300"
            >
              Reset
            </button>
          </div>
        </div>
      </div>

      <!-- Data Table -->
      <div class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm dark:border-slate-800 dark:bg-slate-900">
        <div class="overflow-x-auto">
          <table class="w-full text-left text-sm text-slate-600 dark:text-slate-300">
            <thead class="border-b border-slate-200 bg-slate-50 text-xs uppercase text-slate-500 dark:border-slate-800 dark:bg-slate-800/50 dark:text-slate-400">
              <tr>
                <th class="px-4 py-3.5">No. Work Order</th>
                <th class="px-4 py-3.5">Pemohon & Dept</th>
                <th class="px-4 py-3.5">Kategori / Lokasi</th>
                <th class="px-4 py-3.5">Uraian Masalah</th>
                <th class="px-4 py-3.5">Prioritas</th>
                <th class="px-4 py-3.5">Teknisi</th>
                <th class="px-4 py-3.5">Status</th>
                <th class="px-4 py-3.5 text-right">Aksi</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
              <tr
                v-for="wo in workOrders.data"
                :key="wo.id"
                class="hover:bg-slate-50 dark:hover:bg-slate-800/50 transition"
              >
                <td class="px-4 py-3 font-semibold text-slate-900 dark:text-white">
                  <Link :href="`/general-affair/work-orders/${wo.id}`" class="text-amber-600 hover:underline">
                    {{ wo.wo_number }}
                  </Link>
                </td>
                <td class="px-4 py-3">
                  <div class="font-medium text-slate-800 dark:text-slate-200">{{ wo.user?.name ?? '-' }}</div>
                  <div class="text-xs text-slate-400">{{ wo.department?.name ?? '-' }}</div>
                </td>
                <td class="px-4 py-3">
                  <div class="text-slate-800 dark:text-slate-200 font-medium">{{ wo.category }}</div>
                  <div class="text-xs text-slate-400">{{ wo.location?.name ?? '-' }}</div>
                </td>
                <td class="px-4 py-3">
                  <p class="line-clamp-2 max-w-xs text-xs text-slate-600 dark:text-slate-300">
                    {{ wo.complaint_description }}
                  </p>
                </td>
                <td class="px-4 py-3">
                  <span :class="['inline-flex items-center rounded-full border px-2.5 py-0.5 text-xs font-semibold', getPriorityBadge(wo.priority)]">
                    {{ wo.priority }}
                  </span>
                </td>
                <td class="px-4 py-3">
                  <span class="text-xs font-medium text-slate-700 dark:text-slate-300">
                    {{ wo.assigned_engineer?.name ?? 'Belum Ditugaskan' }}
                  </span>
                </td>
                <td class="px-4 py-3">
                  <span :class="['inline-flex items-center rounded-full border px-2.5 py-0.5 text-xs font-semibold', getStatusBadge(wo.status)]">
                    {{ wo.status }}
                  </span>
                </td>
                <td class="px-4 py-3 text-right">
                  <div class="flex items-center justify-end gap-2">
                    <a
                      :href="`/general-affair/work-orders/${wo.id}/pdf`"
                      target="_blank"
                      title="Download PDF"
                      class="rounded p-1 text-slate-400 hover:text-amber-600 dark:hover:text-amber-400"
                    >
                      <FileText class="h-4 w-4" />
                    </a>
                    <Link
                      :href="`/general-affair/work-orders/${wo.id}`"
                      class="inline-flex items-center gap-1 rounded bg-slate-100 px-2 py-1 text-xs font-medium text-slate-700 hover:bg-amber-50 hover:text-amber-700 dark:bg-slate-800 dark:text-slate-300 dark:hover:bg-amber-950/40"
                    >
                      <Eye class="h-3.5 w-3.5" />
                      Detail
                    </Link>
                  </div>
                </td>
              </tr>
              <tr v-if="!workOrders.data || workOrders.data.length === 0">
                <td colspan="8" class="px-4 py-8 text-center text-slate-500 dark:text-slate-400">
                  Belum ada data Work Order ditemukan.
                </td>
              </tr>
            </tbody>
          </table>
        </div>

        <!-- Pagination -->
        <div v-if="workOrders.links && workOrders.links.length > 3" class="flex items-center justify-between border-t border-slate-200 px-4 py-3 dark:border-slate-800">
          <div class="text-xs text-slate-500">
            Menampilkan {{ workOrders.from }} - {{ workOrders.to }} dari {{ workOrders.total }} data
          </div>
          <div class="flex gap-1">
            <template v-for="(link, key) in workOrders.links" :key="key">
              <Link
                v-if="link.url"
                :href="link.url"
                :class="[
                  'rounded px-2.5 py-1 text-xs font-medium transition',
                  link.active ? 'bg-amber-600 text-white' : 'text-slate-600 hover:bg-slate-100 dark:text-slate-400 dark:hover:bg-slate-800'
                ]"
                v-html="link.label"
              />
            </template>
          </div>
        </div>
      </div>
    </div>
  </ModuleLayout>
</template>
