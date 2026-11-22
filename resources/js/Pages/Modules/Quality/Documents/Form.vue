<script setup>
import { ref } from 'vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import ModuleLayout from '@/Layouts/ModuleLayout.vue';
import {
  ArrowLeft,
  Save,
  Plus,
  Trash2,
  FileText,
  Link2,
} from 'lucide-vue-next';

const props = defineProps({
  module: { type: Object, required: true },
  document: { type: Object, default: null },
  folders: { type: Array, default: () => [] },
});

const isEditing = Boolean(props.document);

const form = useForm({
  level: props.document?.level || 2,
  document_code: props.document?.document_code || 'SOP',
  document_number: props.document?.document_number || '',
  title: props.document?.title || '',
  scope: props.document?.scope || '',
  department: props.document?.department || 'QA',
  folder_id: props.document?.folder_id || '',
  tempat_penyimpanan: props.document?.tempat_penyimpanan || '',
  no_perubahan_cc: props.document?.no_perubahan_cc || '',
  effective_date: props.document?.effective_date || new Date().toISOString().split('T')[0],
  revision: props.document?.revision || '00',
  status: props.document?.status || 'Active',
  change_notes: '',
  document_links: props.document?.document_links || [
    { label: 'Google Drive Dokumen', url: '' },
  ],
});

const addLink = () => {
  form.document_links.push({ label: '', url: '' });
};

const removeLink = (index) => {
  form.document_links.splice(index, 1);
};

const onLevelChange = () => {
  if (form.level === 1) form.document_code = 'MM';
  else if (form.level === 2) form.document_code = 'SOP';
  else if (form.level === 3) form.document_code = 'IK';
  else if (form.level === 4) form.document_code = 'FR';
};

const submit = () => {
  if (isEditing) {
    form.put(`/quality/documents/${props.document.id}`);
  } else {
    form.post('/quality/documents');
  }
};

const departments = ['QA', 'QC', 'Produksi', 'R&D', 'Supply Chain', 'Engineering', 'HRIS', 'Finance'];

const scopes = [
  'Sistem Manajemen Mutu',
  'Personalia & Pelatihan',
  'Bangunan & Fasilitas',
  'Peralatan & Mesin',
  'Sanitasi & Higiene',
  'Produksi & Pengolahan',
  'Pengawasan Mutu (QC)',
  'Inspeksi Diri & Audit Mutu',
  'Penanganan Keluhan & Penarikan Produk',
  'Dokumentasi & Integritas Data',
  'Kualifikasi & Validasi',
  'Pembuatan & Analisis Kontrak',
];
</script>

<template>
  <Head :title="isEditing ? 'Edit Dokumen Mutu' : 'Tambah Dokumen Mutu Baru'" />

  <ModuleLayout :module="module">
    <div class="max-w-4xl space-y-6">
      <div class="flex items-center justify-between">
        <div class="flex items-center gap-3">
          <Link
            href="/quality/documents"
            class="rounded-lg p-2 text-slate-500 hover:bg-slate-100 dark:hover:bg-slate-800"
          >
            <ArrowLeft class="h-5 w-5" />
          </Link>
          <div>
            <h1 class="text-xl font-bold tracking-tight text-slate-900 dark:text-white sm:text-2xl">
              {{ isEditing ? 'Edit Dokumen Mutu' : 'Formulir Dokumen Mutu Baru' }}
            </h1>
            <p class="text-xs text-slate-500 dark:text-slate-400">
              Pengaturan hierarki piramida mutu CPOB, lokasi arsip fisik, dan multi-link cloud.
            </p>
          </div>
        </div>
      </div>

      <form @submit.prevent="submit" class="space-y-6">
        <!-- Section 1: Identitas Dokumen -->
        <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-xs dark:border-slate-800 dark:bg-slate-900 space-y-5">
          <h2 class="text-sm font-bold text-slate-900 dark:text-white uppercase tracking-wider text-teal-600 dark:text-teal-400">
            1. Klasifikasi & Identitas Dokumen
          </h2>

          <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
            <div>
              <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300">Tingkat Piramida Mutu *</label>
              <select
                v-model="form.level"
                @change="onLevelChange"
                class="mt-1 w-full rounded-lg border border-slate-300 py-2 px-3 text-xs focus:border-teal-500 focus:outline-none focus:ring-1 focus:ring-teal-500 dark:border-slate-700 dark:bg-slate-800 dark:text-white"
              >
                <option :value="1">Level 1: Manual Mutu (MM)</option>
                <option :value="2">Level 2: Prosedur Tetap (SOP)</option>
                <option :value="3">Level 3: Instruksi Kerja (IK)</option>
                <option :value="4">Level 4: Formulir & Rekaman (FR)</option>
              </select>
            </div>

            <div>
              <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300">Kode Dokumen *</label>
              <input
                v-model="form.document_code"
                type="text"
                placeholder="misal: SOP, IK, MM"
                class="mt-1 w-full rounded-lg border border-slate-300 py-2 px-3 text-xs font-mono uppercase focus:border-teal-500 focus:outline-none focus:ring-1 focus:ring-teal-500 dark:border-slate-700 dark:bg-slate-800 dark:text-white"
              />
            </div>

            <div>
              <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300">Nomor Dokumen Resmi *</label>
              <input
                v-model="form.document_number"
                type="text"
                placeholder="misal: SOP-QA-004"
                class="mt-1 w-full rounded-lg border border-slate-300 py-2 px-3 text-xs font-mono font-bold focus:border-teal-500 focus:outline-none focus:ring-1 focus:ring-teal-500 dark:border-slate-700 dark:bg-slate-800 dark:text-white"
              />
              <p v-if="form.errors.document_number" class="text-xs text-rose-500 mt-1">{{ form.errors.document_number }}</p>
            </div>
          </div>

          <div>
            <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300">Judul Dokumen Mutu *</label>
            <input
              v-model="form.title"
              type="text"
              placeholder="misal: Prosedur Penanganan Usulan Perubahan (Change Request)"
              class="mt-1 w-full rounded-lg border border-slate-300 py-2 px-3 text-xs focus:border-teal-500 focus:outline-none focus:ring-1 focus:ring-teal-500 dark:border-slate-700 dark:bg-slate-800 dark:text-white"
            />
            <p v-if="form.errors.title" class="text-xs text-rose-500 mt-1">{{ form.errors.title }}</p>
          </div>

          <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
            <div>
              <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300">Departemen Pemilik *</label>
              <select
                v-model="form.department"
                class="mt-1 w-full rounded-lg border border-slate-300 py-2 px-3 text-xs focus:border-teal-500 focus:outline-none focus:ring-1 focus:ring-teal-500 dark:border-slate-700 dark:bg-slate-800 dark:text-white"
              >
                <option v-for="d in departments" :key="d" :value="d">{{ d }}</option>
              </select>
            </div>

            <div>
              <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300">Ruang Lingkup CPOB / ISO</label>
              <select
                v-model="form.scope"
                class="mt-1 w-full rounded-lg border border-slate-300 py-2 px-3 text-xs focus:border-teal-500 focus:outline-none focus:ring-1 focus:ring-teal-500 dark:border-slate-700 dark:bg-slate-800 dark:text-white"
              >
                <option value="">(Pilih Ruang Lingkup)</option>
                <option v-for="sc in scopes" :key="sc" :value="sc">{{ sc }}</option>
              </select>
            </div>
          </div>

          <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
            <div>
              <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300">Folder Penempatan</label>
              <select
                v-model="form.folder_id"
                class="mt-1 w-full rounded-lg border border-slate-300 py-2 px-3 text-xs focus:border-teal-500 focus:outline-none focus:ring-1 focus:ring-teal-500 dark:border-slate-700 dark:bg-slate-800 dark:text-white"
              >
                <option value="">(Tanpa Folder / Root)</option>
                <option v-for="f in folders" :key="f.id" :value="f.id">{{ f.name }}</option>
              </select>
            </div>

            <div>
              <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300">Lokasi Simpan Fisik</label>
              <input
                v-model="form.tempat_penyimpanan"
                type="text"
                placeholder="misal: Lemari QA 01 Rak B"
                class="mt-1 w-full rounded-lg border border-slate-300 py-2 px-3 text-xs focus:border-teal-500 focus:outline-none focus:ring-1 focus:ring-teal-500 dark:border-slate-700 dark:bg-slate-800 dark:text-white"
              />
            </div>

            <div>
              <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300">No. Perubahan Terkait (CR)</label>
              <input
                v-model="form.no_perubahan_cc"
                type="text"
                placeholder="misal: CRA-2026-0001"
                class="mt-1 w-full rounded-lg border border-slate-300 py-2 px-3 text-xs font-mono focus:border-teal-500 focus:outline-none focus:ring-1 focus:ring-teal-500 dark:border-slate-700 dark:bg-slate-800 dark:text-white"
              />
            </div>
          </div>
        </div>

        <!-- Section 2: Revisi & Tanggal Berlaku -->
        <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-xs dark:border-slate-800 dark:bg-slate-900 space-y-4">
          <h2 class="text-sm font-bold text-slate-900 dark:text-white uppercase tracking-wider text-teal-600 dark:text-teal-400">
            2. Siklus Revisi & Validitas
          </h2>

          <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
            <div>
              <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300">Nomor Revisi</label>
              <input
                v-model="form.revision"
                type="text"
                placeholder="00, 01, 02..."
                class="mt-1 w-full rounded-lg border border-slate-300 py-2 px-3 text-xs font-mono font-bold focus:border-teal-500 focus:outline-none focus:ring-1 focus:ring-teal-500 dark:border-slate-700 dark:bg-slate-800 dark:text-white"
              />
            </div>

            <div>
              <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300">Tanggal Berlaku Efektif</label>
              <input
                v-model="form.effective_date"
                type="date"
                class="mt-1 w-full rounded-lg border border-slate-300 py-2 px-3 text-xs focus:border-teal-500 focus:outline-none focus:ring-1 focus:ring-teal-500 dark:border-slate-700 dark:bg-slate-800 dark:text-white"
              />
            </div>

            <div>
              <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300">Status Dokumen</label>
              <select
                v-model="form.status"
                class="mt-1 w-full rounded-lg border border-slate-300 py-2 px-3 text-xs focus:border-teal-500 focus:outline-none focus:ring-1 focus:ring-teal-500 dark:border-slate-700 dark:bg-slate-800 dark:text-white"
              >
                <option value="Active">Active (Berlaku)</option>
                <option value="Obsolete">Obsolete (Usang / Diarsipkan)</option>
              </select>
            </div>
          </div>

          <div v-if="isEditing">
            <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300">Catatan Perubahan Revisi</label>
            <textarea
              v-model="form.change_notes"
              rows="2"
              placeholder="Tuliskan ringkasan poin perubahan revisi baru ini..."
              class="mt-1 w-full rounded-lg border border-slate-300 py-2 px-3 text-xs focus:border-teal-500 focus:outline-none focus:ring-1 focus:ring-teal-500 dark:border-slate-700 dark:bg-slate-800 dark:text-white"
            ></textarea>
          </div>
        </div>

        <!-- Section 3: Tautan Multi-Dokumen Cloud -->
        <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-xs dark:border-slate-800 dark:bg-slate-900 space-y-4">
          <div class="flex items-center justify-between">
            <h2 class="text-sm font-bold text-slate-900 dark:text-white uppercase tracking-wider text-teal-600 dark:text-teal-400">
              3. Tautan File Cloud (Google Drive / SharePoint)
            </h2>
            <button
              type="button"
              @click="addLink"
              class="inline-flex items-center gap-1 text-xs font-semibold text-teal-600 hover:text-teal-700 dark:text-teal-400"
            >
              <Plus class="h-3.5 w-3.5" />
              Tambah Tautan
            </button>
          </div>

          <div class="space-y-3">
            <div
              v-for="(link, lIdx) in form.document_links"
              :key="lIdx"
              class="flex items-center gap-3"
            >
              <input
                v-model="link.label"
                type="text"
                placeholder="Label (misal: PDF Scan Bertanda Tangan)"
                class="w-1/3 rounded-lg border border-slate-300 py-2 px-3 text-xs focus:border-teal-500 focus:outline-none focus:ring-1 focus:ring-teal-500 dark:border-slate-700 dark:bg-slate-800 dark:text-white"
              />
              <input
                v-model="link.url"
                type="url"
                placeholder="https://drive.google.com/..."
                class="flex-1 rounded-lg border border-slate-300 py-2 px-3 text-xs focus:border-teal-500 focus:outline-none focus:ring-1 focus:ring-teal-500 dark:border-slate-700 dark:bg-slate-800 dark:text-white"
              />
              <button
                type="button"
                @click="removeLink(lIdx)"
                class="rounded p-1.5 text-slate-400 hover:text-rose-500"
              >
                <Trash2 class="h-4 w-4" />
              </button>
            </div>
          </div>
        </div>

        <div class="flex items-center justify-end gap-3 pt-2">
          <Link
            href="/quality/documents"
            class="rounded-xl border border-slate-300 px-4 py-2.5 text-xs font-semibold text-slate-700 hover:bg-slate-50 dark:border-slate-700 dark:text-slate-300"
          >
            Batal
          </Link>
          <button
            type="submit"
            :disabled="form.processing"
            class="inline-flex items-center gap-2 rounded-xl bg-teal-600 px-5 py-2.5 text-xs font-semibold text-white shadow-xs hover:bg-teal-700 disabled:opacity-50"
          >
            <Save class="h-4 w-4" />
            {{ isEditing ? 'Simpan Perubahan' : 'Terbitkan Dokumen' }}
          </button>
        </div>
      </form>
    </div>
  </ModuleLayout>
</template>
