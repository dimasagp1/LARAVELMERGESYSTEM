<script setup>
import { ref } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import ModuleLayout from '@/Layouts/ModuleLayout.vue';
import {
  AlertOctagon,
  Plus,
  Search,
  Filter,
  FileDown,
  Layers,
  ArrowRight,
  ShieldAlert,
  CheckCircle2,
  AlertTriangle,
} from 'lucide-vue-next';

const props = defineProps({
  module: { type: Object, required: true },
  deviations: { type: Object, required: true },
  filters: { type: Object, default: () => ({}) },
});

const search = ref(props.filters.search || '');
const selectedStatus = ref(props.filters.status || '');
const selectedIdentifikasi = ref(props.filters.identifikasi || '');
const selectedDept = ref(props.filters.department || '');

const applyFilters = () => {
  router.get('/quality/deviations', {
    search: search.value || undefined,
    status: selectedStatus.value || undefined,
    identifikasi: selectedIdentifikasi.value || undefined,
    department: selectedDept.value || undefined,
  }, { preserveState: true, replace: true });
};

const resetFilters = () => {
  search.value = '';
  selectedStatus.value = '';
  selectedIdentifikasi.value = '';
  selectedDept.value = '';
  applyFilters();
};

const getSeverityBadge = (level) => {
  switch (level) {
    case 'Kritis':
      return 'bg-rose-50 text-rose-700 border-rose-200 dark:bg-rose-950/50 dark:text-rose-300';
    case 'Mayor':
      return 'bg-amber-50 text-amber-700 border-amber-200 dark:bg-amber-950/50 dark:text-amber-300';
    default:
      return 'bg-cyan-50 text-cyan-700 border-cyan-200 dark:bg-cyan-950/50 dark:text-cyan-300';
  }
};

const getStatusBadge = (status) => {
  switch (status) {
    case 'APPROVED':
      return 'bg-emerald-50 text-emerald-700 border-emerald-200 dark:bg-emerald-950/50 dark:text-emerald-300';
    case 'IN REVIEW':
      return 'bg-amber-50 text-amber-700 border-amber-200 dark:bg-amber-950/50 dark:text-amber-300';
    case 'REJECTED':
      return 'bg-rose-50 text-rose-700 border-rose-200 dark:bg-rose-950/50 dark:text-rose-300';
    default:
      return 'bg-slate-100 text-slate-700 border-slate-200 dark:bg-slate-800 dark:text-slate-300';
  }
};
</script>

<template>
  <Head title="Laporan Deviasi & Penyelidikan (DR / PK)" />

  <ModuleLayout :module="module">
    <div class="space-y-6">
      <!-- Header -->
      <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
          <h1 class="text-xl font-bold tracking-tight text-slate-900 dark:text-white sm:text-2xl">
            Laporan Deviasi & Penyelidikan (DR / PK)
          </h1>
          <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">
            Penanganan ketidaksesuaian operasional, investigasi 6M Fishbone & 5-Why, umpan balik atasan, dan otomasi CAPA.
          </p>
        </div>

        <Link
          href="/quality/deviations/create"
          class="inline-flex items-center gap-1.5 rounded-xl bg-teal-600 px-4 py-2.5 text-xs font-semibold text-white shadow-xs hover:bg-teal-700 transition"
        >
          <Plus class="h-4 w-4" />
          Laporkan Deviasi Baru
        </Link>
      </div>

      <!-- Filters & Search Toolbar -->
      <div class="rounded-xl border border-slate-200 bg-white p-4 shadow-xs dark:border-slate-800 dark:bg-slate-900">
        <div class="grid grid-cols-1 gap-3 sm:grid-cols-2 lg:grid-cols-5">
          <div class="relative lg:col-span-2">
            <Search class="pointer-events-none absolute left-3 top-2.5 h-4 w-4 text-slate-400" />
            <input
              v-model="search"
              @keyup.enter="applyFilters"
              type="text"
              placeholder="Cari nomor DR, PK, bets, atau topik..."
              class="w-full rounded-lg border border-slate-300 py-2 pl-9 pr-3 text-xs focus:border-teal-500 focus:outline-none focus:ring-1 focus:ring-teal-500 dark:border-slate-700 dark:bg-slate-800 dark:text-white"
            />
          </div>

          <div>
            <select
              v-model="selectedIdentifikasi"
              @change="applyFilters"
              class="w-full rounded-lg border border-slate-300 py-2 px-3 text-xs focus:border-teal-500 focus:outline-none focus:ring-1 focus:ring-teal-500 dark:border-slate-700 dark:bg-slate-800 dark:text-white"
            >
              <option value="">Semua Tingkat (CPOB)</option>
              <option value="Kritis">Kritis</option>
              <option value="Mayor">Mayor</option>
              <option value="Minor">Minor</option>
            </select>
          </div>

          <div>
            <select
              v-model="selectedStatus"
              @change="applyFilters"
              class="w-full rounded-lg border border-slate-300 py-2 px-3 text-xs focus:border-teal-500 focus:outline-none focus:ring-1 focus:ring-teal-500 dark:border-slate-700 dark:bg-slate-800 dark:text-white"
            >
              <option value="">Semua Status</option>
              <option value="OPEN">OPEN</option>
              <option value="IN REVIEW">IN REVIEW (Investigasi)</option>
              <option value="APPROVED">APPROVED (Spawns CAPA)</option>
              <option value="REJECTED">REJECTED</option>
            </select>
          </div>

          <div>
            <select
              v-model="selectedDept"
              @change="applyFilters"
              class="w-full rounded-lg border border-slate-300 py-2 px-3 text-xs focus:border-teal-500 focus:outline-none focus:ring-1 focus:ring-teal-500 dark:border-slate-700 dark:bg-slate-800 dark:text-white"
            >
              <option value="">Semua Departemen</option>
              <option value="Produksi">Produksi</option>
              <option value="QC">QC</option>
              <option value="R&D">R&D</option>
              <option value="Supply Chain">Supply Chain</option>
            </select>
          </div>
        </div>
      </div>

      <!-- Deviations Table -->
      <div class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-xs dark:border-slate-800 dark:bg-slate-900">
        <div class="overflow-x-auto">
          <table class="w-full text-left text-xs">
            <thead class="bg-slate-50 text-slate-600 border-b border-slate-200 dark:bg-slate-800/60 dark:text-slate-300 dark:border-slate-800">
              <tr>
                <th class="py-3 px-4 font-semibold">No. DR & No. PK</th>
                <th class="py-3 px-4 font-semibold">Topik & Objek Terdampak</th>
                <th class="py-3 px-4 font-semibold">Tingkat CPOB</th>
                <th class="py-3 px-4 font-semibold">Bets / Mesin</th>
                <th class="py-3 px-4 font-semibold">Tgl Temuan & Dept</th>
                <th class="py-3 px-4 font-semibold text-center">Status & CAPA</th>
                <th class="py-3 px-4 font-semibold text-right">Aksi</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
              <tr
                v-for="dev in deviations.data"
                :key="dev.id"
                class="hover:bg-slate-50/80 dark:hover:bg-slate-800/50 transition"
              >
                <td class="py-3.5 px-4 font-mono">
                  <div class="font-bold text-slate-900 dark:text-white flex items-center gap-1.5">
                    <AlertOctagon class="h-4 w-4 text-teal-600" />
                    {{ dev.deviation_number }}
                  </div>
                  <div class="text-[11px] text-slate-500 mt-0.5">
                    PK: {{ dev.pk_number }}
                  </div>
                </td>
                <td class="py-3.5 px-4 max-w-xs">
                  <div class="font-bold text-slate-800 dark:text-slate-200 line-clamp-1">{{ dev.topik }}</div>
                  <div class="text-[11px] text-slate-500 line-clamp-1">{{ dev.objek_terdampak || '-' }}</div>
                </td>
                <td class="py-3.5 px-4">
                  <span :class="['inline-flex items-center rounded-md px-2 py-0.5 text-[10px] font-bold border', getSeverityBadge(dev.identifikasi_penyimpangan)]">
                    {{ dev.identifikasi_penyimpangan }}
                  </span>
                </td>
                <td class="py-3.5 px-4">
                  <div class="font-mono text-slate-800 dark:text-slate-200">{{ dev.nomor_bets || '-' }}</div>
                  <div class="text-[10px] text-slate-400">{{ dev.nomor_alat || '-' }}</div>
                </td>
                <td class="py-3.5 px-4">
                  <div>{{ new Date(dev.tanggal_temuan).toLocaleDateString('id-ID') }}</div>
                  <div class="text-[11px] text-slate-500">{{ dev.department }}</div>
                </td>
                <td class="py-3.5 px-4 text-center">
                  <div>
                    <span :class="['inline-flex rounded-full px-2 py-0.5 text-[10px] font-bold border', getStatusBadge(dev.status)]">
                      {{ dev.status }}
                    </span>
                  </div>
                  <div v-if="dev.capa" class="mt-1">
                    <Link
                      :href="`/quality/capa/${dev.capa.id}`"
                      class="text-[10px] font-mono text-teal-600 hover:underline font-bold inline-flex items-center gap-0.5"
                    >
                      <CheckCircle2 class="h-3 w-3" />
                      {{ dev.capa.capa_number }}
                    </Link>
                  </div>
                </td>
                <td class="py-3.5 px-4 text-right">
                  <div class="flex items-center justify-end gap-1.5">
                    <a
                      :href="`/quality/deviations/${dev.id}/pdf`"
                      target="_blank"
                      class="rounded p-1.5 text-slate-500 hover:bg-slate-100 hover:text-slate-800 dark:hover:bg-slate-800"
                      title="Cetak PDF PK"
                    >
                      <FileDown class="h-4 w-4" />
                    </a>
                    <Link
                      :href="`/quality/deviations/${dev.id}/investigation`"
                      class="inline-flex items-center gap-1 rounded-lg bg-teal-50 px-2.5 py-1 text-xs font-semibold text-teal-700 hover:bg-teal-100 dark:bg-teal-950/60 dark:text-teal-300"
                    >
                      Investigasi 6M
                      <ArrowRight class="h-3 w-3" />
                    </Link>
                  </div>
                </td>
              </tr>

              <tr v-if="deviations.data.length === 0">
                <td colspan="7" class="py-12 text-center text-slate-400">
                  Belum ada laporan deviasi yang tercatat.
                </td>
              </tr>
            </tbody>
          </table>
        </div>

        <!-- Pagination -->
        <div v-if="deviations.links && deviations.links.length > 3" class="flex items-center justify-between border-t border-slate-200 px-4 py-3 dark:border-slate-800">
          <div class="text-xs text-slate-500">
            Menampilkan {{ deviations.from }} sampai {{ deviations.to }} dari total {{ deviations.total }} deviasi
          </div>
          <div class="flex items-center gap-1">
            <Link
              v-for="(link, i) in deviations.links"
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
