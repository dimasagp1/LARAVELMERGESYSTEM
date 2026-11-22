<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';
import ModuleLayout from '@/Layouts/ModuleLayout.vue';
import {
  Package,
  ArrowLeft,
  Send,
  Plus,
  X,
  FileText,
} from 'lucide-vue-next';

const props = defineProps({
  module: { type: Object, required: true },
  departments: { type: Array, default: () => [] },
  categories: { type: Array, default: () => [] },
  items: { type: Array, default: () => [] },
  uoms: { type: Array, default: () => [] },
});

const form = useForm({
  type: 'ATK',
  department_id: props.departments[0]?.id || '',
  alasan_pengajuan: '',
  tanggal_pengajuan: new Date().toISOString().substring(0, 10),
  items: [
    { atk_item_id: props.items[0]?.id || null, custom_item_name: '', quantity: 1, uom_id: props.items[0]?.uom_id || null },
  ],
});

const addItem = () => {
  form.items.push({
    atk_item_id: null,
    custom_item_name: '',
    quantity: 1,
    uom_id: null,
  });
};

const removeItem = (index) => {
  if (form.items.length > 1) {
    form.items.splice(index, 1);
  }
};

const onItemSelect = (index) => {
  const selectedId = form.items[index].atk_item_id;
  const found = props.items.find(i => i.id === selectedId);
  if (found) {
    form.items[index].uom_id = found.uom_id;
  }
};

const submit = () => {
  form.post('/general-affair/atk');
};
</script>

<template>
  <Head title="Ajukan Permintaan ATK / RT - General Affair" />

  <ModuleLayout :module="module">
    <div class="max-w-4xl mx-auto space-y-6">
      <!-- Header -->
      <div class="flex items-center justify-between">
        <div class="flex items-center gap-3">
          <Link
            href="/general-affair/atk"
            class="rounded-lg p-2 text-slate-500 hover:bg-slate-100 dark:hover:bg-slate-800 transition"
          >
            <ArrowLeft class="w-5 h-5" />
          </Link>
          <div>
            <h1 class="text-xl font-bold tracking-tight text-slate-900 dark:text-white sm:text-2xl">
              Formulir Permintaan Barang ATK / Rumah Tangga
            </h1>
            <p class="text-sm text-slate-500 dark:text-slate-400">
              Pengajuan pengadaan perlengkapan kantor, kertas, tinta, atau kebutuhan kebersihan rutin.
            </p>
          </div>
        </div>
      </div>

      <!-- Main Form -->
      <form @submit.prevent="submit" class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900 space-y-6">
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-6">
          <!-- Jenis Pengajuan -->
          <div>
            <label class="block text-sm font-semibold text-slate-900 dark:text-slate-100 mb-1">
              Jenis Permintaan <span class="text-red-500">*</span>
            </label>
            <select
              v-model="form.type"
              class="w-full rounded-lg border border-slate-200 bg-slate-50 px-3 py-2 text-sm text-slate-900 focus:border-amber-500 focus:outline-none dark:border-slate-700 dark:bg-slate-800 dark:text-white"
              required
            >
              <option value="ATK">Alat Tulis Kantor (ATK)</option>
              <option value="RT">Rumah Tangga / Sanitasi (RT)</option>
            </select>
          </div>

          <!-- Departemen -->
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
          </div>

          <!-- Tanggal -->
          <div>
            <label class="block text-sm font-semibold text-slate-900 dark:text-slate-100 mb-1">
              Tanggal Pengajuan
            </label>
            <input
              v-model="form.tanggal_pengajuan"
              type="date"
              class="w-full rounded-lg border border-slate-200 bg-slate-50 px-3 py-2 text-sm text-slate-900 dark:border-slate-700 dark:bg-slate-800 dark:text-white"
              required
            />
          </div>
        </div>

        <!-- Alasan Kebutuhan -->
        <div>
          <label class="block text-sm font-semibold text-slate-900 dark:text-slate-100 mb-1">
            Alasan Kebutuhan / Peruntukan <span class="text-red-500">*</span>
          </label>
          <textarea
            v-model="form.alasan_pengajuan"
            rows="3"
            placeholder="Jelaskan kebutuhan pengajuan barang (cth: Keperluan dokumentasi audit CPOB rutin)..."
            class="w-full rounded-lg border border-slate-200 bg-slate-50 p-3 text-sm text-slate-900 dark:border-slate-700 dark:bg-slate-800 dark:text-white"
            required
          ></textarea>
        </div>

        <!-- Daftar Barang -->
        <div class="space-y-3 pt-4 border-t border-slate-200 dark:border-slate-800">
          <div class="flex items-center justify-between">
            <h3 class="text-sm font-bold text-slate-900 dark:text-white">Daftar Barang yang Diajukan</h3>
            <button
              type="button"
              @click="addItem"
              class="inline-flex items-center gap-1.5 text-xs font-semibold text-amber-600 hover:text-amber-700"
            >
              <Plus class="w-4 h-4" />
              Tambah Baris
            </button>
          </div>

          <div
            v-for="(row, idx) in form.items"
            :key="idx"
            class="grid grid-cols-12 gap-3 items-center p-3 rounded-lg border border-slate-200 dark:border-slate-700 bg-slate-50/50 dark:bg-slate-800/40"
          >
            <!-- Select Item -->
            <div class="col-span-6">
              <label class="block text-[11px] font-medium text-slate-500 mb-1">Pilih Master Barang</label>
              <select
                v-model="row.atk_item_id"
                @change="onItemSelect(idx)"
                class="w-full rounded border border-slate-200 bg-white p-2 text-xs text-slate-900 dark:border-slate-700 dark:bg-slate-800 dark:text-white"
              >
                <option :value="null">-- Input Manual / Non-Katalog --</option>
                <option v-for="it in items" :key="it.id" :value="it.id">
                  {{ it.name }} (Stok: {{ it.stock_actual }} {{ it.uom?.code ?? 'Pcs' }})
                </option>
              </select>
              <input
                v-if="!row.atk_item_id"
                v-model="row.custom_item_name"
                type="text"
                placeholder="Tulis nama barang khusus..."
                class="mt-1 w-full rounded border border-slate-200 bg-white p-1.5 text-xs text-slate-900 dark:border-slate-700 dark:bg-slate-800 dark:text-white"
                required
              />
            </div>

            <!-- Quantity -->
            <div class="col-span-3">
              <label class="block text-[11px] font-medium text-slate-500 mb-1">Jumlah (Qty)</label>
              <input
                v-model.number="row.quantity"
                type="number"
                min="1"
                class="w-full rounded border border-slate-200 bg-white p-2 text-xs text-slate-900 dark:border-slate-700 dark:bg-slate-800 dark:text-white"
                required
              />
            </div>

            <!-- Satuan (UOM) -->
            <div class="col-span-2">
              <label class="block text-[11px] font-medium text-slate-500 mb-1">Satuan</label>
              <select
                v-model="row.uom_id"
                class="w-full rounded border border-slate-200 bg-white p-2 text-xs text-slate-900 dark:border-slate-700 dark:bg-slate-800 dark:text-white"
              >
                <option :value="null">--</option>
                <option v-for="uom in uoms" :key="uom.id" :value="uom.id">
                  {{ uom.code }}
                </option>
              </select>
            </div>

            <!-- Delete Row -->
            <div class="col-span-1 text-center pt-4">
              <button
                type="button"
                @click="removeItem(idx)"
                class="text-red-500 hover:text-red-700"
              >
                <X class="w-4 h-4" />
              </button>
            </div>
          </div>
        </div>

        <!-- Buttons -->
        <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-200 dark:border-slate-800">
          <Link
            href="/general-affair/atk"
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
            {{ form.processing ? 'Mengirim...' : 'Kirim Permintaan' }}
          </button>
        </div>
      </form>
    </div>
  </ModuleLayout>
</template>
