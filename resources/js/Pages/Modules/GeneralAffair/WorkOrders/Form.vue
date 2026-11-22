<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';
import ModuleLayout from '@/Layouts/ModuleLayout.vue';
import {
  Wrench,
  ArrowLeft,
  Send,
  AlertTriangle,
  Building,
  MapPin,
  Tag,
} from 'lucide-vue-next';

const props = defineProps({
  module: { type: Object, required: true },
  departments: { type: Array, default: () => [] },
  locations: { type: Array, default: () => [] },
  categories: { type: Array, default: () => [] },
  priorities: { type: Array, default: () => [] },
});

const form = useForm({
  department_id: props.departments[0]?.id || '',
  location_id: props.locations[0]?.id || '',
  category: props.categories[0] || 'Gedung & Sipil',
  priority: 'Normal',
  complaint_description: '',
  attachment_path: '',
});

const submit = () => {
  form.post('/general-affair/work-orders');
};
</script>

<template>
  <Head title="Buat Work Order (Form A) - General Affair" />

  <ModuleLayout :module="module">
    <div class="max-w-4xl mx-auto space-y-6">
      <!-- Header -->
      <div class="flex items-center justify-between">
        <div class="flex items-center gap-3">
          <Link
            href="/general-affair/work-orders"
            class="rounded-lg p-2 text-slate-500 hover:bg-slate-100 dark:hover:bg-slate-800 transition"
          >
            <ArrowLeft class="w-5 h-5" />
          </Link>
          <div>
            <h1 class="text-xl font-bold tracking-tight text-slate-900 dark:text-white sm:text-2xl">
              Form A: Pengajuan Keluhan / Kerusakan Fasilitas
            </h1>
            <p class="text-sm text-slate-500 dark:text-slate-400">
              Sampaikan permintaan perbaikan sarana gedung, utilitas, mesin umum, atau AC pabrik.
            </p>
          </div>
        </div>
      </div>

      <!-- Main Form -->
      <form @submit.prevent="submit" class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900 space-y-6">
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
          <!-- Departemen Pemohon -->
          <div>
            <label class="block text-sm font-semibold text-slate-900 dark:text-slate-100 mb-1">
              Departemen Pemohon <span class="text-red-500">*</span>
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
            <p v-if="form.errors.department_id" class="mt-1 text-xs text-red-500">{{ form.errors.department_id }}</p>
          </div>

          <!-- Lokasi / Ruangan -->
          <div>
            <label class="block text-sm font-semibold text-slate-900 dark:text-slate-100 mb-1">
              Lokasi Sarana / Ruangan
            </label>
            <select
              v-model="form.location_id"
              class="w-full rounded-lg border border-slate-200 bg-slate-50 px-3 py-2 text-sm text-slate-900 focus:border-amber-500 focus:outline-none dark:border-slate-700 dark:bg-slate-800 dark:text-white"
            >
              <option value="">-- Pilih Lokasi / Ruangan --</option>
              <option v-for="loc in locations" :key="loc.id" :value="loc.id">
                {{ loc.name }} (Plant {{ loc.plant_id }})
              </option>
            </select>
            <p v-if="form.errors.location_id" class="mt-1 text-xs text-red-500">{{ form.errors.location_id }}</p>
          </div>

          <!-- Kategori Pekerjaan -->
          <div>
            <label class="block text-sm font-semibold text-slate-900 dark:text-slate-100 mb-1">
              Kategori Pemeliharaan <span class="text-red-500">*</span>
            </label>
            <select
              v-model="form.category"
              class="w-full rounded-lg border border-slate-200 bg-slate-50 px-3 py-2 text-sm text-slate-900 focus:border-amber-500 focus:outline-none dark:border-slate-700 dark:bg-slate-800 dark:text-white"
              required
            >
              <option v-for="cat in categories" :key="cat" :value="cat">
                {{ cat }}
              </option>
            </select>
            <p v-if="form.errors.category" class="mt-1 text-xs text-red-500">{{ form.errors.category }}</p>
          </div>

          <!-- Prioritas -->
          <div>
            <label class="block text-sm font-semibold text-slate-900 dark:text-slate-100 mb-1">
              Tingkat Prioritas <span class="text-red-500">*</span>
            </label>
            <select
              v-model="form.priority"
              class="w-full rounded-lg border border-slate-200 bg-slate-50 px-3 py-2 text-sm text-slate-900 focus:border-amber-500 focus:outline-none dark:border-slate-700 dark:bg-slate-800 dark:text-white"
              required
            >
              <option v-for="prio in priorities" :key="prio" :value="prio">
                {{ prio }}
              </option>
            </select>
            <p v-if="form.errors.priority" class="mt-1 text-xs text-red-500">{{ form.errors.priority }}</p>
          </div>
        </div>

        <!-- Uraian Keluhan / Masalah -->
        <div>
          <label class="block text-sm font-semibold text-slate-900 dark:text-slate-100 mb-1">
            Uraian Masalah / Gejala Kerusakan <span class="text-red-500">*</span>
          </label>
          <textarea
            v-model="form.complaint_description"
            rows="4"
            placeholder="Jelaskan secara rinci kerusakan yang terjadi, dampak operasional, atau indikasi awal..."
            class="w-full rounded-lg border border-slate-200 bg-slate-50 p-3 text-sm text-slate-900 focus:border-amber-500 focus:outline-none dark:border-slate-700 dark:bg-slate-800 dark:text-white"
            required
          ></textarea>
          <p v-if="form.errors.complaint_description" class="mt-1 text-xs text-red-500">{{ form.errors.complaint_description }}</p>
        </div>

        <!-- Attachment Link -->
        <div>
          <label class="block text-sm font-semibold text-slate-900 dark:text-slate-100 mb-1">
            Lampiran Foto / Dokumen Pendukung (Opsional)
          </label>
          <input
            v-model="form.attachment_path"
            type="text"
            placeholder="URL atau path foto dokumentasi kerusakan (cth: /uploads/kerusakan-ac-01.jpg)"
            class="w-full rounded-lg border border-slate-200 bg-slate-50 px-3 py-2 text-sm text-slate-900 focus:border-amber-500 focus:outline-none dark:border-slate-700 dark:bg-slate-800 dark:text-white"
          />
        </div>

        <!-- Action Buttons -->
        <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-200 dark:border-slate-800">
          <Link
            href="/general-affair/work-orders"
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
            {{ form.processing ? 'Mengirim...' : 'Kirim Form A' }}
          </button>
        </div>
      </form>
    </div>
  </ModuleLayout>
</template>
