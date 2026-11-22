<script setup>
import { ref } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import ModuleLayout from '@/Layouts/ModuleLayout.vue';
import {
  Building2,
  Plus,
  Search,
  Filter,
  Eye,
  ArrowRightLeft,
  FileText,
  Trash2,
  ClipboardList,
} from 'lucide-vue-next';

const props = defineProps({
  module: { type: Object, required: true },
  assets: { type: Object, required: true },
  filters: { type: Object, default: () => ({}) },
  plants: { type: Array, default: () => [] },
  departments: { type: Array, default: () => [] },
  categories: { type: Array, default: () => [] },
  stats: { type: Object, default: () => ({}) },
});

const search = ref(props.filters.search || '');
const selectedPlant = ref(props.filters.plant_id || '');
const selectedDept = ref(props.filters.department_id || '');
const selectedCat = ref(props.filters.category_id || '');
const selectedCond = ref(props.filters.condition || '');

const applyFilters = () => {
  router.get('/general-affair/assets', {
    search: search.value || undefined,
    plant_id: selectedPlant.value || undefined,
    department_id: selectedDept.value || undefined,
    category_id: selectedCat.value || undefined,
    condition: selectedCond.value || undefined,
  }, { preserveState: true, replace: true });
};

const resetFilters = () => {
  search.value = '';
  selectedPlant.value = '';
  selectedDept.value = '';
  selectedCat.value = '';
  selectedCond.value = '';
  applyFilters();
};

const getConditionBadge = (condition) => {
  switch (condition) {
    case 'BAIK':
      return 'bg-emerald-50 text-emerald-700 border-emerald-200 dark:bg-emerald-950/40 dark:text-emerald-300';
    case 'RUSAK_RINGAN':
      return 'bg-amber-50 text-amber-700 border-amber-200 dark:bg-amber-950/40 dark:text-amber-300';
    case 'RUSAK_BERAT':
      return 'bg-red-50 text-red-700 border-red-200 dark:bg-red-950/40 dark:text-red-300';
    case 'DISPOSED':
      return 'bg-slate-200 text-slate-700 border-slate-300 dark:bg-slate-800 dark:text-slate-400';
    default:
      return 'bg-slate-100 text-slate-700 border-slate-200 dark:bg-slate-800 dark:text-slate-300';
  }
};
</script>

<template>
  <Head title="Manajemen Aset Tetap - General Affair" />

  <ModuleLayout :module="module">
    <div class="space-y-6">
      <!-- Header -->
      <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
          <h1 class="text-xl font-bold tracking-tight text-slate-900 dark:text-white sm:text-2xl">
            Manajemen Aset Tetap Multi-Plant
          </h1>
          <p class="text-sm text-slate-500 dark:text-slate-400">
            Pencatatan inventaris, depresiasi garis lurus otomatis, mutasi antar plant/ruangan, dan pelaporan DIR Lampiran C.
          </p>
        </div>
        <div class="flex items-center gap-3">
          <Link
            href="/general-affair/assets/dir"
            class="inline-flex items-center gap-2 rounded-lg border border-slate-200 bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 shadow-sm hover:bg-slate-50 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-200 transition"
          >
            <ClipboardList class="h-4 w-4 text-purple-600" />
            Cek DIR Lampiran C
          </Link>
          <Link
            href="/general-affair/assets/create"
            class="inline-flex items-center gap-2 rounded-lg bg-amber-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-amber-500 transition"
          >
            <Plus class="h-4 w-4" />
            Registrasi Aset Baru
          </Link>
        </div>
      </div>

      <!-- Stats Bar -->
      <div class="grid grid-cols-1 sm:grid-cols-4 gap-4">
        <div class="rounded-xl border border-slate-200 bg-white p-4 dark:border-slate-800 dark:bg-slate-900">
          <span class="text-xs font-medium text-slate-500 dark:text-slate-400">Total Unit Aset Aktif</span>
          <p class="mt-1 text-2xl font-bold text-slate-900 dark:text-white">{{ stats.total_assets ?? 0 }} Unit</p>
        </div>
        <div class="rounded-xl border border-slate-200 bg-white p-4 dark:border-slate-800 dark:bg-slate-900">
          <span class="text-xs font-medium text-slate-500 dark:text-slate-400">Total Biaya Perolehan</span>
          <p class="mt-1 text-xl font-bold text-slate-900 dark:text-white">
            Rp {{ Number(stats.total_acquisition_cost ?? 0).toLocaleString('id-ID') }}
          </p>
        </div>
        <div class="rounded-xl border border-slate-200 bg-white p-4 dark:border-slate-800 dark:bg-slate-900">
          <span class="text-xs font-medium text-amber-600 dark:text-amber-400">Akumulasi Penyusutan</span>
          <p class="mt-1 text-xl font-bold text-amber-600 dark:text-amber-400">
            Rp {{ Number(stats.total_accumulated_depreciation ?? 0).toLocaleString('id-ID') }}
          </p>
        </div>
        <div class="rounded-xl border border-slate-200 bg-white p-4 dark:border-slate-800 dark:bg-slate-900">
          <span class="text-xs font-medium text-emerald-600 dark:text-emerald-400">Total Nilai Buku Bersih</span>
          <p class="mt-1 text-xl font-bold text-emerald-600 dark:text-emerald-400">
            Rp {{ Number(stats.total_book_value ?? 0).toLocaleString('id-ID') }}
          </p>
        </div>
      </div>

      <!-- Filters -->
      <div class="rounded-xl border border-slate-200 bg-white p-4 dark:border-slate-800 dark:bg-slate-900">
        <div class="grid grid-cols-1 gap-3 sm:grid-cols-6">
          <div class="relative sm:col-span-2">
            <Search class="absolute left-3 top-2.5 h-4 w-4 text-slate-400" />
            <input
              v-model="search"
              type="text"
              placeholder="Cari kode aset, nama, atau PIC..."
              class="w-full rounded-lg border border-slate-200 bg-slate-50 pl-9 pr-3 py-2 text-sm text-slate-900 focus:border-amber-500 focus:outline-none dark:border-slate-700 dark:bg-slate-800 dark:text-white"
              @keyup.enter="applyFilters"
            />
          </div>

          <div>
            <select
              v-model="selectedPlant"
              class="w-full rounded-lg border border-slate-200 bg-slate-50 px-3 py-2 text-sm text-slate-900 focus:border-amber-500 focus:outline-none dark:border-slate-700 dark:bg-slate-800 dark:text-white"
              @change="applyFilters"
            >
              <option value="">Semua Plant</option>
              <option v-for="p in plants" :key="p.id" :value="p.id">{{ p.name }}</option>
            </select>
          </div>

          <div>
            <select
              v-model="selectedCat"
              class="w-full rounded-lg border border-slate-200 bg-slate-50 px-3 py-2 text-sm text-slate-900 focus:border-amber-500 focus:outline-none dark:border-slate-700 dark:bg-slate-800 dark:text-white"
              @change="applyFilters"
            >
              <option value="">Semua Kategori</option>
              <option v-for="c in categories" :key="c.id" :value="c.id">{{ c.name }}</option>
            </select>
          </div>

          <div>
            <select
              v-model="selectedCond"
              class="w-full rounded-lg border border-slate-200 bg-slate-50 px-3 py-2 text-sm text-slate-900 focus:border-amber-500 focus:outline-none dark:border-slate-700 dark:bg-slate-800 dark:text-white"
              @change="applyFilters"
            >
              <option value="">Semua Kondisi</option>
              <option value="BAIK">Baik</option>
              <option value="RUSAK_RINGAN">Rusak Ringan</option>
              <option value="RUSAK_BERAT">Rusak Berat</option>
              <option value="DISPOSED">Disposed</option>
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

      <!-- Table -->
      <div class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm dark:border-slate-800 dark:bg-slate-900">
        <div class="overflow-x-auto">
          <table class="w-full text-left text-sm text-slate-600 dark:text-slate-300">
            <thead class="border-b border-slate-200 bg-slate-50 text-xs uppercase text-slate-500 dark:border-slate-800 dark:bg-slate-800/50 dark:text-slate-400">
              <tr>
                <th class="px-4 py-3.5">Kode Aset</th>
                <th class="px-4 py-3.5">Nama Aset & Kategori</th>
                <th class="px-4 py-3.5">Lokasi & Plant</th>
                <th class="px-4 py-3.5">PIC</th>
                <th class="px-4 py-3.5 text-right">Biaya Perolehan</th>
                <th class="px-4 py-3.5 text-right">Nilai Buku Saat Ini</th>
                <th class="px-4 py-3.5">Kondisi</th>
                <th class="px-4 py-3.5 text-right">Aksi</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
              <tr
                v-for="ast in assets.data"
                :key="ast.id"
                class="hover:bg-slate-50 dark:hover:bg-slate-800/50 transition"
              >
                <td class="px-4 py-3 font-semibold text-slate-900 dark:text-white">
                  <Link :href="`/general-affair/assets/${ast.id}`" class="text-amber-600 hover:underline">
                    {{ ast.asset_code }}
                  </Link>
                </td>
                <td class="px-4 py-3">
                  <div class="font-medium text-slate-800 dark:text-slate-200">{{ ast.name }}</div>
                  <div class="text-xs text-slate-400">{{ ast.category?.name ?? '-' }}</div>
                </td>
                <td class="px-4 py-3">
                  <div class="text-slate-800 dark:text-slate-200 font-medium">{{ ast.plant?.name ?? '-' }}</div>
                  <div class="text-xs text-slate-400">{{ ast.location?.name ?? ast.department?.name ?? '-' }}</div>
                </td>
                <td class="px-4 py-3 text-xs">
                  {{ ast.pic_name ?? '-' }}
                </td>
                <td class="px-4 py-3 text-right font-medium">
                  Rp {{ Number(ast.purchase_cost).toLocaleString('id-ID') }}
                </td>
                <td class="px-4 py-3 text-right font-bold text-emerald-600 dark:text-emerald-400">
                  Rp {{ Number(ast.book_value).toLocaleString('id-ID') }}
                </td>
                <td class="px-4 py-3">
                  <span :class="['inline-flex items-center rounded-full border px-2.5 py-0.5 text-xs font-semibold', getConditionBadge(ast.condition)]">
                    {{ ast.condition }}
                  </span>
                </td>
                <td class="px-4 py-3 text-right">
                  <div class="flex items-center justify-end gap-2">
                    <Link
                      :href="`/general-affair/assets/${ast.id}`"
                      class="inline-flex items-center gap-1 rounded bg-slate-100 px-2 py-1 text-xs font-medium text-slate-700 hover:bg-amber-50 hover:text-amber-700 dark:bg-slate-800 dark:text-slate-300 dark:hover:bg-amber-950/40"
                    >
                      <Eye class="h-3.5 w-3.5" />
                      Detail
                    </Link>
                  </div>
                </td>
              </tr>
              <tr v-if="!assets.data || assets.data.length === 0">
                <td colspan="8" class="px-4 py-8 text-center text-slate-500 dark:text-slate-400">
                  Belum ada aset tetap terdaftar.
                </td>
              </tr>
            </tbody>
          </table>
        </div>

        <!-- Pagination -->
        <div v-if="assets.links && assets.links.length > 3" class="flex items-center justify-between border-t border-slate-200 px-4 py-3 dark:border-slate-800">
          <div class="text-xs text-slate-500">
            Menampilkan {{ assets.from }} - {{ assets.to }} dari {{ assets.total }} data
          </div>
          <div class="flex gap-1">
            <template v-for="(link, key) in assets.links" :key="key">
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
