<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';
import ModuleLayout from '@/Layouts/ModuleLayout.vue';
import {
  Building2,
  ArrowLeft,
  Send,
  Calendar,
  DollarSign,
  Tag,
} from 'lucide-vue-next';

const props = defineProps({
  module: { type: Object, required: true },
  plants: { type: Array, default: () => [] },
  departments: { type: Array, default: () => [] },
  locations: { type: Array, default: () => [] },
  categories: { type: Array, default: () => [] },
  conditions: { type: Array, default: () => [] },
});

const form = useForm({
  name: '',
  short_name: '',
  plant_id: props.plants[0]?.id || '',
  department_id: props.departments[0]?.id || '',
  location_id: props.locations[0]?.id || '',
  category_id: props.categories[0]?.id || '',
  classification: 'Fasilitas & Mesin Pendukung',
  purchase_date: new Date().toISOString().substring(0, 10),
  purchase_cost: 10000000,
  useful_life_years: 4,
  condition: 'BAIK',
  pic_name: '',
  notes: '',
});

const submit = () => {
  form.post('/general-affair/assets');
};
</script>

<template>
  <Head title="Registrasi Aset Tetap - General Affair" />

  <ModuleLayout :module="module">
    <div class="max-w-4xl mx-auto space-y-6">
      <!-- Header -->
      <div class="flex items-center justify-between">
        <div class="flex items-center gap-3">
          <Link
            href="/general-affair/assets"
            class="rounded-lg p-2 text-slate-500 hover:bg-slate-100 dark:hover:bg-slate-800 transition"
          >
            <ArrowLeft class="w-5 h-5" />
          </Link>
          <div>
            <h1 class="text-xl font-bold tracking-tight text-slate-900 dark:text-white sm:text-2xl">
              Registrasi Aset Tetap Baru
            </h1>
            <p class="text-sm text-slate-500 dark:text-slate-400">
              Sistem akan menghasilkan kode aset otomatis dengan format: HII-{PLANT}-{DEPT}-{KATEGORI}-{SINGKATAN}-{NOMOR}.
            </p>
          </div>
        </div>
      </div>

      <!-- Main Form -->
      <form @submit.prevent="submit" class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900 space-y-6">
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
          <!-- Nama Aset -->
          <div>
            <label class="block text-sm font-semibold text-slate-900 dark:text-slate-100 mb-1">
              Nama Aset Tetap <span class="text-red-500">*</span>
            </label>
            <input
              v-model="form.name"
              type="text"
              placeholder="Cth: Air Conditioner Split 2 PK Inverter"
              class="w-full rounded-lg border border-slate-200 bg-slate-50 px-3 py-2 text-sm text-slate-900 focus:border-amber-500 focus:outline-none dark:border-slate-700 dark:bg-slate-800 dark:text-white"
              required
            />
            <p v-if="form.errors.name" class="mt-1 text-xs text-red-500">{{ form.errors.name }}</p>
          </div>

          <!-- Singkatan / Short Code -->
          <div>
            <label class="block text-sm font-semibold text-slate-900 dark:text-slate-100 mb-1">
              Singkatan Nama (3-4 Karakter)
            </label>
            <input
              v-model="form.short_name"
              type="text"
              maxlength="4"
              placeholder="Cth: ACS"
              class="w-full rounded-lg border border-slate-200 bg-slate-50 px-3 py-2 text-sm text-slate-900 uppercase focus:border-amber-500 focus:outline-none dark:border-slate-700 dark:bg-slate-800 dark:text-white"
            />
          </div>

          <!-- Plant -->
          <div>
            <label class="block text-sm font-semibold text-slate-900 dark:text-slate-100 mb-1">
              Lokasi Pabrik (Plant) <span class="text-red-500">*</span>
            </label>
            <select
              v-model="form.plant_id"
              class="w-full rounded-lg border border-slate-200 bg-slate-50 px-3 py-2 text-sm text-slate-900 focus:border-amber-500 focus:outline-none dark:border-slate-700 dark:bg-slate-800 dark:text-white"
              required
            >
              <option v-for="p in plants" :key="p.id" :value="p.id">
                {{ p.name }} (Kode: {{ p.code }})
              </option>
            </select>
          </div>

          <!-- Departemen -->
          <div>
            <label class="block text-sm font-semibold text-slate-900 dark:text-slate-100 mb-1">
              Departemen Penanggung Jawab <span class="text-red-500">*</span>
            </label>
            <select
              v-model="form.department_id"
              class="w-full rounded-lg border border-slate-200 bg-slate-50 px-3 py-2 text-sm text-slate-900 focus:border-amber-500 focus:outline-none dark:border-slate-700 dark:bg-slate-800 dark:text-white"
              required
            >
              <option v-for="dept in departments" :key="dept.id" :value="dept.id">
                {{ dept.name }}
              </option>
            </select>
          </div>

          <!-- Ruangan / Lokasi Spesifik -->
          <div>
            <label class="block text-sm font-semibold text-slate-900 dark:text-slate-100 mb-1">
              Ruangan Fasilitas (Untuk DIR Lampiran C)
            </label>
            <select
              v-model="form.location_id"
              class="w-full rounded-lg border border-slate-200 bg-slate-50 px-3 py-2 text-sm text-slate-900 focus:border-amber-500 focus:outline-none dark:border-slate-700 dark:bg-slate-800 dark:text-white"
            >
              <option value="">-- Pilih Ruangan --</option>
              <option v-for="loc in locations" :key="loc.id" :value="loc.id">
                {{ loc.name }}
              </option>
            </select>
          </div>

          <!-- Kategori Aset -->
          <div>
            <label class="block text-sm font-semibold text-slate-900 dark:text-slate-100 mb-1">
              Kategori Aset Tetap <span class="text-red-500">*</span>
            </label>
            <select
              v-model="form.category_id"
              class="w-full rounded-lg border border-slate-200 bg-slate-50 px-3 py-2 text-sm text-slate-900 focus:border-amber-500 focus:outline-none dark:border-slate-700 dark:bg-slate-800 dark:text-white"
              required
            >
              <option v-for="cat in categories" :key="cat.id" :value="cat.id">
                {{ cat.name }} (Golongan: {{ cat.golongan_pajak }})
              </option>
            </select>
          </div>

          <!-- Tanggal Perolehan -->
          <div>
            <label class="block text-sm font-semibold text-slate-900 dark:text-slate-100 mb-1">
              Tanggal Perolehan / Pembelian <span class="text-red-500">*</span>
            </label>
            <input
              v-model="form.purchase_date"
              type="date"
              class="w-full rounded-lg border border-slate-200 bg-slate-50 px-3 py-2 text-sm text-slate-900 dark:border-slate-700 dark:bg-slate-800 dark:text-white"
              required
            />
          </div>

          <!-- Biaya Perolehan -->
          <div>
            <label class="block text-sm font-semibold text-slate-900 dark:text-slate-100 mb-1">
              Biaya Perolehan (Harga Beli Rp) <span class="text-red-500">*</span>
            </label>
            <input
              v-model.number="form.purchase_cost"
              type="number"
              min="0"
              class="w-full rounded-lg border border-slate-200 bg-slate-50 px-3 py-2 text-sm text-slate-900 dark:border-slate-700 dark:bg-slate-800 dark:text-white"
              required
            />
          </div>

          <!-- Masa Manfaat -->
          <div>
            <label class="block text-sm font-semibold text-slate-900 dark:text-slate-100 mb-1">
              Masa Manfaat (Tahun) <span class="text-red-500">*</span>
            </label>
            <input
              v-model.number="form.useful_life_years"
              type="number"
              min="1"
              max="30"
              class="w-full rounded-lg border border-slate-200 bg-slate-50 px-3 py-2 text-sm text-slate-900 dark:border-slate-700 dark:bg-slate-800 dark:text-white"
              required
            />
          </div>

          <!-- Kondisi Fisik -->
          <div>
            <label class="block text-sm font-semibold text-slate-900 dark:text-slate-100 mb-1">
              Kondisi Fisik Awal <span class="text-red-500">*</span>
            </label>
            <select
              v-model="form.condition"
              class="w-full rounded-lg border border-slate-200 bg-slate-50 px-3 py-2 text-sm text-slate-900 focus:border-amber-500 focus:outline-none dark:border-slate-700 dark:bg-slate-800 dark:text-white"
              required
            >
              <option v-for="c in conditions" :key="c" :value="c">{{ c }}</option>
            </select>
          </div>

          <!-- PIC -->
          <div class="sm:col-span-2">
            <label class="block text-sm font-semibold text-slate-900 dark:text-slate-100 mb-1">
              Nama PIC Penanggung Jawab Fisik
            </label>
            <input
              v-model="form.pic_name"
              type="text"
              placeholder="Cth: Joko Susanto (Staff Fasilitas GA)"
              class="w-full rounded-lg border border-slate-200 bg-slate-50 px-3 py-2 text-sm text-slate-900 dark:border-slate-700 dark:bg-slate-800 dark:text-white"
            />
          </div>
        </div>

        <!-- Notes -->
        <div>
          <label class="block text-sm font-semibold text-slate-900 dark:text-slate-100 mb-1">
            Catatan Tambahan / Spesifikasi Teknis
          </label>
          <textarea
            v-model="form.notes"
            rows="3"
            placeholder="Nomor seri pabrikan, garansi, daya listrik, atau vendor pemasok..."
            class="w-full rounded-lg border border-slate-200 bg-slate-50 p-3 text-sm text-slate-900 dark:border-slate-700 dark:bg-slate-800 dark:text-white"
          ></textarea>
        </div>

        <!-- Buttons -->
        <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-200 dark:border-slate-800">
          <Link
            href="/general-affair/assets"
            class="rounded-lg border border-slate-200 bg-white px-4 py-2.5 text-sm font-medium text-slate-700 hover:bg-slate-50 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-300"
          >
            Batal
          </Link>
          <button
            type="submit"
            :disabled="form.processing"
            class="inline-flex items-center gap-2 rounded-lg bg-amber-600 px-5 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-amber-500 disabled:opacity-50 transition"
          >
            <Send class="w-4 h-4" />
            {{ form.processing ? 'Menyimpan...' : 'Registrasi Aset' }}
          </button>
        </div>
      </form>
    </div>
  </ModuleLayout>
</template>
