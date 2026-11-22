<script setup>
import { ref } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import ModuleLayout from '@/Layouts/ModuleLayout.vue';
import {
  ClipboardList,
  ArrowLeft,
  FileText,
  Building,
  MapPin,
  CheckCircle2,
  DollarSign,
} from 'lucide-vue-next';

const props = defineProps({
  module: { type: Object, required: true },
  locations: { type: Array, default: () => [] },
  selectedLocationId: { type: Number, default: 0 },
  report: { type: Object, default: () => null },
});

const activeLocationId = ref(props.selectedLocationId || (props.locations[0]?.id ?? 0));

const onLocationChange = () => {
  router.get('/general-affair/assets/dir', {
    location_id: activeLocationId.value,
  }, { preserveState: true });
};
</script>

<template>
  <Head title="Daftar Inventaris Ruangan (DIR) - General Affair" />

  <ModuleLayout :module="module">
    <div class="space-y-6">
      <!-- Top Bar -->
      <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div class="flex items-center gap-3">
          <Link
            href="/general-affair/assets"
            class="rounded-lg p-2 text-slate-500 hover:bg-slate-100 dark:hover:bg-slate-800 transition"
          >
            <ArrowLeft class="w-5 h-5" />
          </Link>
          <div>
            <h1 class="text-xl font-bold tracking-tight text-slate-900 dark:text-white sm:text-2xl">
              Daftar Inventaris Ruangan (DIR) - Lampiran C
            </h1>
            <p class="text-sm text-slate-500 dark:text-slate-400">
              Pengawasan dan verifikasi fisik inventaris sarana per ruangan kerja dan fasilitas pabrik.
            </p>
          </div>
        </div>

        <div v-if="report && report.location" class="flex items-center gap-2">
          <a
            :href="`/general-affair/assets/dir/${report.location.id}/pdf`"
            target="_blank"
            class="inline-flex items-center gap-2 rounded-lg bg-amber-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-amber-500 transition"
          >
            <FileText class="w-4 h-4" />
            Cetak DIR Lampiran C (PDF)
          </a>
        </div>
      </div>

      <!-- Location Selector -->
      <div class="rounded-xl border border-slate-200 bg-white p-4 dark:border-slate-800 dark:bg-slate-900 flex flex-col sm:flex-row items-center justify-between gap-4">
        <div class="flex items-center gap-3 w-full sm:w-auto">
          <span class="text-xs font-semibold text-slate-700 dark:text-slate-300 whitespace-nowrap">Pilih Ruangan Fasilitas:</span>
          <select
            v-model="activeLocationId"
            @change="onLocationChange"
            class="w-full sm:w-80 rounded-lg border border-slate-200 bg-slate-50 px-3 py-2 text-sm text-slate-900 focus:border-amber-500 focus:outline-none dark:border-slate-700 dark:bg-slate-800 dark:text-white"
          >
            <option v-for="loc in locations" :key="loc.id" :value="loc.id">
              {{ loc.name }} - Plant {{ loc.plant?.name ?? loc.plant_id }} (Dept: {{ loc.department?.name ?? 'Umum' }})
            </option>
          </select>
        </div>

        <div v-if="report && report.location" class="text-xs text-slate-500">
          Kode Ruangan: <strong class="text-slate-900 dark:text-white font-mono">{{ report.location.code ?? 'LOC-' + report.location.id }}</strong>
        </div>
      </div>

      <!-- Summary Cards -->
      <div v-if="report" class="grid grid-cols-1 sm:grid-cols-4 gap-4">
        <div class="rounded-xl border border-slate-200 bg-white p-4 dark:border-slate-800 dark:bg-slate-900">
          <span class="text-xs font-medium text-slate-500">Total Unit Aset Ruangan</span>
          <p class="mt-1 text-2xl font-bold text-slate-900 dark:text-white">{{ report.summary.total_items }} Unit</p>
        </div>
        <div class="rounded-xl border border-slate-200 bg-white p-4 dark:border-slate-800 dark:bg-slate-900">
          <span class="text-xs font-medium text-slate-500">Total Nilai Perolehan</span>
          <p class="mt-1 text-xl font-bold text-slate-900 dark:text-white">
            Rp {{ Number(report.summary.total_acquisition_cost).toLocaleString('id-ID') }}
          </p>
        </div>
        <div class="rounded-xl border border-slate-200 bg-white p-4 dark:border-slate-800 dark:bg-slate-900">
          <span class="text-xs font-medium text-amber-600 dark:text-amber-400">Total Akumulasi Depresiasi</span>
          <p class="mt-1 text-xl font-bold text-amber-600 dark:text-amber-400">
            Rp {{ Number(report.summary.total_accumulated_depreciation).toLocaleString('id-ID') }}
          </p>
        </div>
        <div class="rounded-xl border border-slate-200 bg-white p-4 dark:border-slate-800 dark:bg-slate-900">
          <span class="text-xs font-medium text-emerald-600 dark:text-emerald-400">Total Nilai Buku Bersih</span>
          <p class="mt-1 text-xl font-bold text-emerald-600 dark:text-emerald-400">
            Rp {{ Number(report.summary.total_book_value).toLocaleString('id-ID') }}
          </p>
        </div>
      </div>

      <!-- Assets in Room Table -->
      <div v-if="report" class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm dark:border-slate-800 dark:bg-slate-900">
        <div class="overflow-x-auto">
          <table class="w-full text-left text-sm text-slate-600 dark:text-slate-300">
            <thead class="border-b border-slate-200 bg-slate-50 text-xs uppercase text-slate-500 dark:border-slate-800 dark:bg-slate-800/50 dark:text-slate-400">
              <tr>
                <th class="px-4 py-3.5">No</th>
                <th class="px-4 py-3.5">Kode Aset</th>
                <th class="px-4 py-3.5">Nama Aset & Spesifikasi</th>
                <th class="px-4 py-3.5">Kategori</th>
                <th class="px-4 py-3.5">Tgl Beli</th>
                <th class="px-4 py-3.5">Kondisi</th>
                <th class="px-4 py-3.5">PIC</th>
                <th class="px-4 py-3.5 text-right">Nilai Buku (Rp)</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
              <tr
                v-for="(ast, idx) in report.assets"
                :key="ast.id"
                class="hover:bg-slate-50 dark:hover:bg-slate-800/50 transition"
              >
                <td class="px-4 py-3 text-xs">{{ idx + 1 }}</td>
                <td class="px-4 py-3 font-semibold text-slate-900 dark:text-white">
                  <Link :href="`/general-affair/assets/${ast.id}`" class="text-amber-600 hover:underline">
                    {{ ast.asset_code }}
                  </Link>
                </td>
                <td class="px-4 py-3 font-medium text-slate-800 dark:text-slate-200">
                  {{ ast.name }}
                </td>
                <td class="px-4 py-3 text-xs">{{ ast.category?.name ?? '-' }}</td>
                <td class="px-4 py-3 text-xs">
                  {{ ast.purchase_date ? new Date(ast.purchase_date).toLocaleDateString('id-ID') : '-' }}
                </td>
                <td class="px-4 py-3">
                  <span class="px-2 py-0.5 text-xs font-semibold rounded bg-slate-100 text-slate-700">
                    {{ ast.condition }}
                  </span>
                </td>
                <td class="px-4 py-3 text-xs">{{ ast.pic_name ?? '-' }}</td>
                <td class="px-4 py-3 text-right font-bold text-emerald-600 dark:text-emerald-400">
                  Rp {{ Number(ast.book_value).toLocaleString('id-ID') }}
                </td>
              </tr>
              <tr v-if="!report.assets || report.assets.length === 0">
                <td colspan="8" class="px-4 py-8 text-center text-slate-500 dark:text-slate-400">
                  Tidak ada inventaris aset terdaftar di ruangan ini.
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </ModuleLayout>
</template>
