<script setup>
import { ref } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import ModuleLayout from '@/Layouts/ModuleLayout.vue';
import {
  CheckCircle2,
  Search,
  Filter,
  FileDown,
  ArrowRight,
  Eye,
  Clock,
  Layers,
} from 'lucide-vue-next';

const props = defineProps({
  module: { type: Object, required: true },
  capas: { type: Object, required: true },
  filters: { type: Object, default: () => ({}) },
});

const search = ref(props.filters.search || '');
const selectedStatus = ref(props.filters.status || '');

const applyFilters = () => {
  router.get('/quality/capa', {
    search: search.value || undefined,
    status: selectedStatus.value || undefined,
  }, { preserveState: true, replace: true });
};

const resetFilters = () => {
  search.value = '';
  selectedStatus.value = '';
  applyFilters();
};

const getStatusBadge = (status) => {
  switch (status) {
    case 'CLOSE':
      return 'bg-emerald-50 text-emerald-700 border-emerald-200 dark:bg-emerald-950/50 dark:text-emerald-300';
    case 'APPROVED':
      return 'bg-blue-50 text-blue-700 border-blue-200 dark:bg-blue-950/50 dark:text-blue-300';
    case 'IN PROGRESS':
      return 'bg-amber-50 text-amber-700 border-amber-200 dark:bg-amber-950/50 dark:text-amber-300';
    default:
      return 'bg-slate-100 text-slate-700 border-slate-200 dark:bg-slate-800 dark:text-slate-300';
  }
};
</script>

<template>
  <Head title="Manajemen Tindakan Korektif & Preventif (CAPA)" />

  <ModuleLayout :module="module">
    <div class="space-y-6">
      <!-- Header -->
      <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
          <h1 class="text-xl font-bold tracking-tight text-slate-900 dark:text-white sm:text-2xl">
            Manajemen Tindakan Korektif & Pencegahan (CAPA)
          </h1>
          <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">
            Penugasan multi-tindakan perbaikan lintas divisi, pengunggahan bukti fisik lapangan, dan verifikasi efektivitas penutupan.
          </p>
        </div>
      </div>

      <!-- Filters & Search Toolbar -->
      <div class="rounded-xl border border-slate-200 bg-white p-4 shadow-xs dark:border-slate-800 dark:bg-slate-900">
        <div class="grid grid-cols-1 gap-3 sm:grid-cols-3">
          <div class="relative sm:col-span-2">
            <Search class="pointer-events-none absolute left-3 top-2.5 h-4 w-4 text-slate-400" />
            <input
              v-model="search"
              @keyup.enter="applyFilters"
              type="text"
              placeholder="Cari nomor CAPA, nomor referensi deviasi, atau tindakan..."
              class="w-full rounded-lg border border-slate-300 py-2 pl-9 pr-3 text-xs focus:border-teal-500 focus:outline-none focus:ring-1 focus:ring-teal-500 dark:border-slate-700 dark:bg-slate-800 dark:text-white"
            />
          </div>

          <div>
            <select
              v-model="selectedStatus"
              @change="applyFilters"
              class="w-full rounded-lg border border-slate-300 py-2 px-3 text-xs focus:border-teal-500 focus:outline-none focus:ring-1 focus:ring-teal-500 dark:border-slate-700 dark:bg-slate-800 dark:text-white"
            >
              <option value="">Semua Status</option>
              <option value="DRAFT">DRAFT</option>
              <option value="IN PROGRESS">IN PROGRESS (Pelaksanaan)</option>
              <option value="APPROVED">APPROVED (Bukti Siap Verifikasi)</option>
              <option value="CLOSE">CLOSE (Tuntas & Ditutup)</option>
            </select>
          </div>
        </div>
      </div>

      <!-- CAPA Table -->
      <div class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-xs dark:border-slate-800 dark:bg-slate-900">
        <div class="overflow-x-auto">
          <table class="w-full text-left text-xs">
            <thead class="bg-slate-50 text-slate-600 border-b border-slate-200 dark:bg-slate-800/60 dark:text-slate-300 dark:border-slate-800">
              <tr>
                <th class="py-3 px-4 font-semibold">Nomor CAPA & Tanggal</th>
                <th class="py-3 px-4 font-semibold">Ref. Deviasi</th>
                <th class="py-3 px-4 font-semibold">Rencana Tindakan</th>
                <th class="py-3 px-4 font-semibold">Progres Tindakan</th>
                <th class="py-3 px-4 font-semibold text-center">Status</th>
                <th class="py-3 px-4 font-semibold text-right">Aksi</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
              <tr
                v-for="capa in capas.data"
                :key="capa.id"
                class="hover:bg-slate-50/80 dark:hover:bg-slate-800/50 transition"
              >
                <td class="py-3.5 px-4 font-mono">
                  <div class="font-bold text-slate-900 dark:text-white flex items-center gap-1.5">
                    <CheckCircle2 class="h-4 w-4 text-teal-600" />
                    {{ capa.capa_number }}
                  </div>
                  <div class="text-[11px] text-slate-500 mt-0.5">
                    Tgl: {{ new Date(capa.tanggal_penyimpangan).toLocaleDateString('id-ID') }}
                  </div>
                </td>
                <td class="py-3.5 px-4">
                  <Link
                    v-if="capa.deviation_id"
                    :href="`/quality/deviations/${capa.deviation_id}/investigation`"
                    class="font-mono text-teal-600 hover:underline font-bold"
                  >
                    {{ capa.deviation_number_ref }}
                  </Link>
                  <span v-else class="font-mono text-slate-500">{{ capa.deviation_number_ref }}</span>
                </td>
                <td class="py-3.5 px-4 max-w-xs">
                  <div class="line-clamp-2 text-slate-700 dark:text-slate-300">
                    {{ capa.tindakan_capa }}
                  </div>
                </td>
                <td class="py-3.5 px-4 w-48">
                  <div class="flex items-center justify-between text-[11px] mb-1">
                    <span class="text-slate-500">Tindakan Lapangan</span>
                    <span class="font-bold text-teal-600">{{ capa.progress_percentage }}%</span>
                  </div>
                  <div class="w-full bg-slate-100 rounded-full h-2 dark:bg-slate-800 overflow-hidden">
                    <div
                      class="bg-teal-600 h-2 rounded-full transition-all"
                      :style="{ width: `${capa.progress_percentage}%` }"
                    ></div>
                  </div>
                </td>
                <td class="py-3.5 px-4 text-center">
                  <span :class="['inline-flex rounded-full px-2.5 py-0.5 text-[10px] font-bold border', getStatusBadge(capa.status)]">
                    {{ capa.status }}
                  </span>
                </td>
                <td class="py-3.5 px-4 text-right">
                  <div class="flex items-center justify-end gap-1.5">
                    <a
                      :href="`/quality/capa/${capa.id}/pdf`"
                      target="_blank"
                      class="rounded p-1.5 text-slate-500 hover:bg-slate-100 hover:text-slate-800 dark:hover:bg-slate-800"
                      title="Cetak PDF CAPA"
                    >
                      <FileDown class="h-4 w-4" />
                    </a>
                    <Link
                      :href="`/quality/capa/${capa.id}`"
                      class="inline-flex items-center gap-1 rounded-lg bg-teal-50 px-2.5 py-1 text-xs font-semibold text-teal-700 hover:bg-teal-100 dark:bg-teal-950/60 dark:text-teal-300"
                    >
                      <Eye class="h-3.5 w-3.5" />
                      Detail Aksi
                    </Link>
                  </div>
                </td>
              </tr>

              <tr v-if="capas.data.length === 0">
                <td colspan="6" class="py-12 text-center text-slate-400">
                  Belum ada data tindakan CAPA.
                </td>
              </tr>
            </tbody>
          </table>
        </div>

        <!-- Pagination -->
        <div v-if="capas.links && capas.links.length > 3" class="flex items-center justify-between border-t border-slate-200 px-4 py-3 dark:border-slate-800">
          <div class="text-xs text-slate-500">
            Menampilkan {{ capas.from }} sampai {{ capas.to }} dari total {{ capas.total }} CAPA
          </div>
          <div class="flex items-center gap-1">
            <Link
              v-for="(link, i) in capas.links"
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
