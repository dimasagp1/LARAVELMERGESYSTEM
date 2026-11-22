<script setup>
import { ref, watch } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import ModuleLayout from '@/Layouts/ModuleLayout.vue';
import {
  FileText,
  Plus,
  Search,
  Filter,
  Folder,
  FolderPlus,
  ExternalLink,
  History,
  Trash2,
  Edit,
  FolderTree,
  ChevronRight,
  Layers,
  X,
} from 'lucide-vue-next';

const props = defineProps({
  module: { type: Object, required: true },
  documents: { type: Object, required: true },
  folders: { type: Array, default: () => [] },
  filters: { type: Object, default: () => ({}) },
});

const search = ref(props.filters.search || '');
const selectedLevel = ref(props.filters.level || '');
const selectedDept = ref(props.filters.department || '');
const selectedScope = ref(props.filters.scope || '');
const selectedFolderId = ref(props.filters.folder_id || '');
const selectedStatus = ref(props.filters.status || '');

// History Modal state
const showRevisionModal = ref(false);
const activeDocument = ref(null);

// New Folder Modal state
const showFolderModal = ref(false);
const newFolderName = ref('');
const newFolderColor = ref('#0d9488');
const newFolderParentId = ref('');

const applyFilters = () => {
  router.get('/quality/documents', {
    search: search.value || undefined,
    level: selectedLevel.value || undefined,
    department: selectedDept.value || undefined,
    scope: selectedScope.value || undefined,
    folder_id: selectedFolderId.value || undefined,
    status: selectedStatus.value || undefined,
  }, { preserveState: true, replace: true });
};

const resetFilters = () => {
  search.value = '';
  selectedLevel.value = '';
  selectedDept.value = '';
  selectedScope.value = '';
  selectedFolderId.value = '';
  selectedStatus.value = '';
  applyFilters();
};

const openRevisionHistory = (doc) => {
  activeDocument.value = doc;
  showRevisionModal.value = true;
};

const submitNewFolder = () => {
  if (!newFolderName.value.trim()) return;
  router.post('/quality/documents/folders', {
    name: newFolderName.value,
    color: newFolderColor.value,
    parent_id: newFolderParentId.value || null,
  }, {
    onSuccess: () => {
      showFolderModal.value = false;
      newFolderName.value = '';
    },
  });
};

const deleteDoc = (doc) => {
  if (confirm(`Apakah Anda yakin ingin menghapus dokumen ${doc.document_number}?`)) {
    router.delete(`/quality/documents/${doc.id}`);
  }
};

const levels = [
  { id: '', label: 'Semua Tingkat' },
  { id: 1, label: 'Level 1: Manual Mutu (MM)' },
  { id: 2, label: 'Level 2: Prosedur Tetap (SOP)' },
  { id: 3, label: 'Level 3: Instruksi Kerja (IK)' },
  { id: 4, label: 'Level 4: Formulir & Rekaman (FR)' },
];

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
  <Head title="Master Induk Dokumen Mutu QMS" />

  <ModuleLayout :module="module">
    <div class="space-y-6">
      <!-- Header Actions -->
      <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
          <h1 class="text-xl font-bold tracking-tight text-slate-900 dark:text-white sm:text-2xl">
            Master Induk Dokumen Mutu (QMS)
          </h1>
          <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">
            Hierarki piramida 4-tingkat kepatuhan CPOB/ISO, repositori folder bersarang, dan pelacakan riwayat revisi.
          </p>
        </div>

        <div class="flex items-center gap-2">
          <button
            type="button"
            @click="showFolderModal = true"
            class="inline-flex items-center gap-1.5 rounded-lg border border-slate-300 bg-white px-3.5 py-2 text-xs font-semibold text-slate-700 shadow-xs hover:bg-slate-50 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-200"
          >
            <FolderPlus class="h-4 w-4 text-teal-600" />
            Folder Baru
          </button>
          <Link
            href="/quality/documents/create"
            class="inline-flex items-center gap-1.5 rounded-lg bg-teal-600 px-4 py-2 text-xs font-semibold text-white shadow-xs hover:bg-teal-700"
          >
            <Plus class="h-4 w-4" />
            Tambah Dokumen
          </Link>
        </div>
      </div>

      <!-- Pyramid Level Quick Selector Pills -->
      <div class="flex flex-wrap items-center gap-2">
        <button
          v-for="lvl in levels"
          :key="lvl.id"
          type="button"
          @click="selectedLevel = lvl.id; applyFilters()"
          :class="[
            'rounded-full px-4 py-1.5 text-xs font-semibold transition',
            selectedLevel === lvl.id
              ? 'bg-teal-600 text-white shadow-xs'
              : 'bg-white text-slate-700 border border-slate-200 hover:bg-slate-50 dark:bg-slate-800 dark:text-slate-300 dark:border-slate-700'
          ]"
        >
          {{ lvl.label }}
        </button>
      </div>

      <!-- Search & Filters Toolbar -->
      <div class="rounded-xl border border-slate-200 bg-white p-4 shadow-xs dark:border-slate-800 dark:bg-slate-900">
        <div class="grid grid-cols-1 gap-3 sm:grid-cols-2 lg:grid-cols-5">
          <div class="relative lg:col-span-2">
            <Search class="pointer-events-none absolute left-3 top-2.5 h-4 w-4 text-slate-400" />
            <input
              v-model="search"
              @keyup.enter="applyFilters"
              type="text"
              placeholder="Cari no. dokumen, judul, atau tempat simpan..."
              class="w-full rounded-lg border border-slate-300 py-2 pl-9 pr-3 text-xs focus:border-teal-500 focus:outline-none focus:ring-1 focus:ring-teal-500 dark:border-slate-700 dark:bg-slate-800 dark:text-white"
            />
          </div>

          <div>
            <select
              v-model="selectedDept"
              @change="applyFilters"
              class="w-full rounded-lg border border-slate-300 py-2 px-3 text-xs focus:border-teal-500 focus:outline-none focus:ring-1 focus:ring-teal-500 dark:border-slate-700 dark:bg-slate-800 dark:text-white"
            >
              <option value="">Semua Departemen</option>
              <option v-for="d in departments" :key="d" :value="d">{{ d }}</option>
            </select>
          </div>

          <div>
            <select
              v-model="selectedScope"
              @change="applyFilters"
              class="w-full rounded-lg border border-slate-300 py-2 px-3 text-xs focus:border-teal-500 focus:outline-none focus:ring-1 focus:ring-teal-500 dark:border-slate-700 dark:bg-slate-800 dark:text-white"
            >
              <option value="">Semua Ruang Lingkup CPOB</option>
              <option v-for="sc in scopes" :key="sc" :value="sc">{{ sc }}</option>
            </select>
          </div>

          <div class="flex items-center gap-2">
            <select
              v-model="selectedStatus"
              @change="applyFilters"
              class="w-full rounded-lg border border-slate-300 py-2 px-3 text-xs focus:border-teal-500 focus:outline-none focus:ring-1 focus:ring-teal-500 dark:border-slate-700 dark:bg-slate-800 dark:text-white"
            >
              <option value="">Status: Semua</option>
              <option value="Active">Active</option>
              <option value="Obsolete">Obsolete</option>
            </select>

            <button
              v-if="search || selectedLevel || selectedDept || selectedScope || selectedStatus"
              type="button"
              @click="resetFilters"
              class="rounded-lg p-2 text-slate-400 hover:bg-slate-100 hover:text-slate-600 dark:hover:bg-slate-800"
              title="Reset Filter"
            >
              <X class="h-4 w-4" />
            </button>
          </div>
        </div>
      </div>

      <!-- Documents Table -->
      <div class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-xs dark:border-slate-800 dark:bg-slate-900">
        <div class="overflow-x-auto">
          <table class="w-full text-left text-xs">
            <thead class="bg-slate-50 text-slate-600 border-b border-slate-200 dark:bg-slate-800/60 dark:text-slate-300 dark:border-slate-800">
              <tr>
                <th class="py-3 px-4 font-semibold">No. Dokumen & Judul</th>
                <th class="py-3 px-4 font-semibold">Tingkat Mutu</th>
                <th class="py-3 px-4 font-semibold">Dept & Ruang Lingkup</th>
                <th class="py-3 px-4 font-semibold">Lokasi Simpan / Tautan</th>
                <th class="py-3 px-4 font-semibold text-center">Revisi</th>
                <th class="py-3 px-4 font-semibold text-center">Status</th>
                <th class="py-3 px-4 font-semibold text-right">Aksi</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
              <tr
                v-for="doc in documents.data"
                :key="doc.id"
                class="hover:bg-slate-50/80 dark:hover:bg-slate-800/50 transition"
              >
                <td class="py-3 px-4">
                  <div class="font-bold text-slate-900 dark:text-white flex items-center gap-1.5">
                    <FileText class="h-4 w-4 text-teal-600" />
                    {{ doc.document_number }}
                  </div>
                  <div class="text-slate-600 dark:text-slate-300 mt-0.5 line-clamp-1 max-w-sm">
                    {{ doc.title }}
                  </div>
                </td>
                <td class="py-3 px-4">
                  <span class="inline-flex items-center rounded-md px-2 py-0.5 text-[11px] font-semibold bg-teal-50 text-teal-700 dark:bg-teal-950/60 dark:text-teal-300">
                    {{ doc.level_label }}
                  </span>
                </td>
                <td class="py-3 px-4">
                  <div class="font-medium text-slate-800 dark:text-slate-200">{{ doc.department }}</div>
                  <div class="text-[11px] text-slate-500 line-clamp-1">{{ doc.scope || '-' }}</div>
                </td>
                <td class="py-3 px-4">
                  <div class="text-[11px] text-slate-600 dark:text-slate-400">
                    {{ doc.tempat_penyimpanan || 'Arsip QA Pusat' }}
                  </div>
                  <div v-if="doc.document_links && doc.document_links.length > 0" class="flex items-center gap-1.5 mt-1">
                    <a
                      v-for="(link, lIdx) in doc.document_links"
                      :key="lIdx"
                      :href="link.url"
                      target="_blank"
                      class="inline-flex items-center gap-0.5 text-[10px] text-teal-600 hover:underline dark:text-teal-400"
                    >
                      <ExternalLink class="h-3 w-3" />
                      {{ link.label || 'File Link' }}
                    </a>
                  </div>
                </td>
                <td class="py-3 px-4 text-center">
                  <span class="font-mono font-bold text-slate-700 dark:text-slate-300 bg-slate-100 dark:bg-slate-800 px-2 py-0.5 rounded text-[11px]">
                    Rev. {{ doc.revision }}
                  </span>
                </td>
                <td class="py-3 px-4 text-center">
                  <span
                    :class="[
                      'inline-flex rounded-full px-2 py-0.5 text-[10px] font-bold',
                      doc.status === 'Active'
                        ? 'bg-emerald-50 text-emerald-700 dark:bg-emerald-950/60 dark:text-emerald-300'
                        : 'bg-slate-100 text-slate-600 dark:bg-slate-800 dark:text-slate-400'
                    ]"
                  >
                    {{ doc.status }}
                  </span>
                </td>
                <td class="py-3 px-4 text-right">
                  <div class="flex items-center justify-end gap-1.5">
                    <button
                      type="button"
                      @click="openRevisionHistory(doc)"
                      class="rounded p-1 text-slate-500 hover:bg-slate-100 hover:text-slate-800 dark:hover:bg-slate-800"
                      title="Histori Revisi"
                    >
                      <History class="h-4 w-4" />
                    </button>
                    <Link
                      :href="`/quality/documents/${doc.id}/edit`"
                      class="rounded p-1 text-teal-600 hover:bg-teal-50 dark:hover:bg-teal-950/50"
                      title="Edit Dokumen"
                    >
                      <Edit class="h-4 w-4" />
                    </Link>
                    <button
                      type="button"
                      @click="deleteDoc(doc)"
                      class="rounded p-1 text-rose-500 hover:bg-rose-50 dark:hover:bg-rose-950/50"
                      title="Hapus"
                    >
                      <Trash2 class="h-4 w-4" />
                    </button>
                  </div>
                </td>
              </tr>

              <tr v-if="documents.data.length === 0">
                <td colspan="7" class="py-10 text-center text-slate-400">
                  Belum ada dokumen yang sesuai dengan kriteria pencarian.
                </td>
              </tr>
            </tbody>
          </table>
        </div>

        <!-- Pagination -->
        <div v-if="documents.links && documents.links.length > 3" class="flex items-center justify-between border-t border-slate-200 px-4 py-3 dark:border-slate-800">
          <div class="text-xs text-slate-500">
            Menampilkan {{ documents.from }} sampai {{ documents.to }} dari total {{ documents.total }} dokumen
          </div>
          <div class="flex items-center gap-1">
            <Link
              v-for="(link, i) in documents.links"
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

      <!-- Revision History Modal -->
      <div v-if="showRevisionModal" class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/60 p-4 backdrop-blur-xs">
        <div class="w-full max-w-2xl rounded-2xl bg-white p-6 shadow-xl dark:bg-slate-900 border border-slate-200 dark:border-slate-800">
          <div class="flex items-center justify-between border-b border-slate-100 pb-3 dark:border-slate-800">
            <div>
              <h3 class="text-base font-bold text-slate-900 dark:text-white">
                Logbook Riwayat Revisi Dokumen
              </h3>
              <p class="text-xs text-slate-500 mt-0.5">
                {{ activeDocument?.document_number }} - {{ activeDocument?.title }}
              </p>
            </div>
            <button
              @click="showRevisionModal = false"
              class="rounded-lg p-1 text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800"
            >
              <X class="h-5 w-5" />
            </button>
          </div>

          <div class="mt-4 max-h-80 overflow-y-auto space-y-3">
            <div
              v-for="(rev, rIdx) in activeDocument?.revisions || []"
              :key="rIdx"
              class="rounded-xl border border-slate-100 p-3 bg-slate-50/60 dark:border-slate-800 dark:bg-slate-800/40"
            >
              <div class="flex items-center justify-between">
                <span class="inline-flex rounded-md bg-teal-100 px-2 py-0.5 text-xs font-bold text-teal-800 dark:bg-teal-950 dark:text-teal-300">
                  Revisi {{ rev.revision }}
                </span>
                <span class="text-[11px] text-slate-500">
                  Berlaku: {{ rev.effective_date }} &bullet; Oleh: {{ rev.created_by_user?.name || 'QA Admin' }}
                </span>
              </div>
              <p class="mt-2 text-xs text-slate-700 dark:text-slate-300">
                <strong>Catatan Perubahan:</strong> {{ rev.change_notes || 'Pembaruan dokumen berkala.' }}
              </p>
            </div>
          </div>

          <div class="mt-6 flex justify-end">
            <button
              type="button"
              @click="showRevisionModal = false"
              class="rounded-lg bg-slate-100 px-4 py-2 text-xs font-semibold text-slate-700 hover:bg-slate-200 dark:bg-slate-800 dark:text-slate-200"
            >
              Tutup
            </button>
          </div>
        </div>
      </div>

      <!-- New Folder Modal -->
      <div v-if="showFolderModal" class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/60 p-4 backdrop-blur-xs">
        <div class="w-full max-w-md rounded-2xl bg-white p-6 shadow-xl dark:bg-slate-900 border border-slate-200 dark:border-slate-800">
          <div class="flex items-center justify-between border-b border-slate-100 pb-3 dark:border-slate-800">
            <h3 class="text-base font-bold text-slate-900 dark:text-white">
              Buat Folder Baru
            </h3>
            <button
              @click="showFolderModal = false"
              class="rounded-lg p-1 text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800"
            >
              <X class="h-5 w-5" />
            </button>
          </div>

          <div class="mt-4 space-y-4">
            <div>
              <label class="block text-xs font-medium text-slate-700 dark:text-slate-300">Nama Folder</label>
              <input
                v-model="newFolderName"
                type="text"
                placeholder="misal: SOP Pengolahan Sirup"
                class="mt-1 w-full rounded-lg border border-slate-300 py-2 px-3 text-xs focus:border-teal-500 focus:outline-none focus:ring-1 focus:ring-teal-500 dark:border-slate-700 dark:bg-slate-800 dark:text-white"
              />
            </div>

            <div>
              <label class="block text-xs font-medium text-slate-700 dark:text-slate-300">Folder Induk (Opsional)</label>
              <select
                v-model="newFolderParentId"
                class="mt-1 w-full rounded-lg border border-slate-300 py-2 px-3 text-xs focus:border-teal-500 focus:outline-none focus:ring-1 focus:ring-teal-500 dark:border-slate-700 dark:bg-slate-800 dark:text-white"
              >
                <option value="">(Sebagai Folder Utama)</option>
                <option v-for="f in folders" :key="f.id" :value="f.id">{{ f.name }}</option>
              </select>
            </div>

            <div>
              <label class="block text-xs font-medium text-slate-700 dark:text-slate-300">Warna Tag Badge</label>
              <input
                v-model="newFolderColor"
                type="color"
                class="mt-1 h-9 w-20 cursor-pointer rounded border border-slate-300 p-1"
              />
            </div>
          </div>

          <div class="mt-6 flex justify-end gap-2">
            <button
              type="button"
              @click="showFolderModal = false"
              class="rounded-lg border border-slate-200 px-3.5 py-2 text-xs font-semibold text-slate-600 hover:bg-slate-50 dark:border-slate-700 dark:text-slate-300"
            >
              Batal
            </button>
            <button
              type="button"
              @click="submitNewFolder"
              class="rounded-lg bg-teal-600 px-4 py-2 text-xs font-semibold text-white hover:bg-teal-700"
            >
              Simpan Folder
            </button>
          </div>
        </div>
      </div>
    </div>
  </ModuleLayout>
</template>
