<script setup>
import { ref, computed } from 'vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import ModuleLayout from '@/Layouts/ModuleLayout.vue';
import {
    Calendar,
    ArrowLeft,
    Save,
    Factory,
    Boxes,
    Layers,
    Users,
    AlertCircle,
    Info,
} from 'lucide-vue-next';

const props = defineProps({
    plan: {
        type: Object,
        default: null,
    },
    lines: {
        type: Array,
        default: () => [],
    },
    products: {
        type: Array,
        default: () => [],
    },
    defaultBatchNumber: {
        type: String,
        default: 'EXT-2026-001',
    },
});

const form = useForm({
    produk_id: props.plan?.produk_id || (props.products[0]?.id || ''),
    line_id: props.plan?.line_id || (props.lines[0]?.id || ''),
    proses: props.plan?.proses || 'mixing',
    batch_number: props.plan?.batch_number || props.defaultBatchNumber,
    odoo_mo_id: props.plan?.odoo_mo_id || null,
    mo_status: props.plan?.mo_status || 'manual',
    mp_count: props.plan?.mp_count || 10,
    multiplier: props.plan?.multiplier || 2000,
    target_output: props.plan?.target_output || 20000,
    packing_hold: props.plan?.packing_hold || false,
    tanggal: props.plan?.tanggal || new Date().toISOString().split('T')[0],
    shift: props.plan?.shift || 'shift1',
    notes: props.plan?.notes || '',
});

// Auto-recalculate target output when MP or Multiplier changes
const updateTarget = () => {
    form.target_output = form.mp_count * form.multiplier;
};

const submit = () => {
    form.post('/production/schedules', {
        onError: () => {
            // Error handling
        },
    });
};
</script>

<template>
    <Head title="Buat Jadwal Rencana Produksi (WPS)" />

    <ModuleLayout module-slug="production">
        <div class="max-w-4xl space-y-6">
            <!-- Header Breadcrumb -->
            <div class="flex items-center gap-3">
                <Link
                    href="/production/schedules"
                    class="rounded-lg border border-slate-200 p-2 text-slate-500 hover:bg-slate-50 dark:border-slate-800 dark:text-slate-400"
                >
                    <ArrowLeft class="h-4 w-4" />
                </Link>
                <div>
                    <h1 class="text-xl font-bold text-slate-900 dark:text-white sm:text-2xl">
                        Buat Jadwal Rencana Produksi (WPS)
                    </h1>
                    <p class="text-xs text-slate-500">
                        PPIC Schedule Mapping berorientasi Batch Number dan validasi Odoo MO Confirmed
                    </p>
                </div>
            </div>

            <!-- Main Form Card -->
            <form @submit.prevent="submit" class="space-y-6 rounded-2xl border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900">
                <div class="border-b border-slate-100 pb-4 dark:border-slate-800">
                    <h2 class="text-sm font-bold text-slate-900 dark:text-white uppercase tracking-wider">
                        1. Informasi Produk & Parameter Batch
                    </h2>
                    <p class="text-xs text-slate-500">Pilih item yang akan diproduksi dan nomor batch estafet</p>
                </div>

                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <div>
                        <label class="text-xs font-semibold text-slate-700 dark:text-slate-300">Produk yang Diproduksi *</label>
                        <select
                            v-model="form.produk_id"
                            class="mt-1 w-full rounded-lg border border-slate-200 p-2.5 text-xs text-slate-900 focus:border-rose-500 dark:border-slate-700 dark:bg-slate-800 dark:text-white"
                            required
                        >
                            <option v-for="p in products" :key="p.id" :value="p.id">
                                {{ p.name }} ({{ p.sku || p.code }})
                            </option>
                        </select>
                        <span v-if="form.errors.produk_id" class="text-[11px] text-rose-500">{{ form.errors.produk_id }}</span>
                    </div>

                    <div>
                        <label class="text-xs font-semibold text-slate-700 dark:text-slate-300">Nomor Batch *</label>
                        <input
                            v-model="form.batch_number"
                            type="text"
                            placeholder="Contoh: EXT-2026-088"
                            class="mt-1 w-full rounded-lg border border-slate-200 p-2.5 text-xs font-mono font-bold text-rose-600 focus:border-rose-500 dark:border-slate-700 dark:bg-slate-800 dark:text-rose-400"
                            required
                        />
                        <span v-if="form.errors.batch_number" class="text-[11px] text-rose-500">{{ form.errors.batch_number }}</span>
                    </div>

                    <div>
                        <label class="text-xs font-semibold text-slate-700 dark:text-slate-300">Lini Produksi *</label>
                        <select
                            v-model="form.line_id"
                            class="mt-1 w-full rounded-lg border border-slate-200 p-2.5 text-xs text-slate-900 focus:border-rose-500 dark:border-slate-700 dark:bg-slate-800 dark:text-white"
                            required
                        >
                            <option v-for="l in lines" :key="l.id" :value="l.id">
                                {{ l.name }} ({{ l.line_number }})
                            </option>
                        </select>
                    </div>

                    <div>
                        <label class="text-xs font-semibold text-slate-700 dark:text-slate-300">Tahap Proses Estafet *</label>
                        <select
                            v-model="form.proses"
                            class="mt-1 w-full rounded-lg border border-slate-200 p-2.5 text-xs text-slate-900 focus:border-rose-500 dark:border-slate-700 dark:bg-slate-800 dark:text-white font-bold"
                            required
                        >
                            <option value="mixing">1. MIXING (Pengolahan / Ekstraksi)</option>
                            <option value="filling">2. FILLING (Pengisian Kemasan Primer)</option>
                            <option value="packing">3. PACKING (Pengemasan Sekunder)</option>
                        </select>
                        <p class="mt-1 text-[11px] text-slate-400">
                            Aturan Estafet: Filling membutuhkan Mixing, dan Packing membutuhkan Filling.
                        </p>
                    </div>
                </div>

                <div class="border-b border-slate-100 pb-4 pt-2 dark:border-slate-800">
                    <h2 class="text-sm font-bold text-slate-900 dark:text-white uppercase tracking-wider">
                        2. Integrasi Odoo MO & Kapasitas Tenaga Kerja
                    </h2>
                </div>

                <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
                    <div>
                        <label class="text-xs font-semibold text-slate-700 dark:text-slate-300">ID MO Odoo (Opsional)</label>
                        <input
                            v-model="form.odoo_mo_id"
                            type="number"
                            placeholder="Contoh: 1011"
                            class="mt-1 w-full rounded-lg border border-slate-200 p-2.5 text-xs text-slate-900 dark:border-slate-700 dark:bg-slate-800 dark:text-white"
                        />
                    </div>

                    <div>
                        <label class="text-xs font-semibold text-slate-700 dark:text-slate-300">Status MO (Odoo / Internal) *</label>
                        <select
                            v-model="form.mo_status"
                            class="mt-1 w-full rounded-lg border border-slate-200 p-2.5 text-xs text-slate-900 font-semibold text-emerald-600 dark:border-slate-700 dark:bg-slate-800"
                        >
                            <option value="manual">manual (Mandiri Standalone ERP)</option>
                            <option value="confirmed">confirmed (Odoo Sync Valid)</option>
                            <option value="done">done</option>
                            <option value="cancelled">cancelled</option>
                        </select>
                    </div>

                    <div>
                        <label class="text-xs font-semibold text-slate-700 dark:text-slate-300">Tanggal Rencana *</label>
                        <input
                            v-model="form.tanggal"
                            type="date"
                            class="mt-1 w-full rounded-lg border border-slate-200 p-2.5 text-xs text-slate-900 dark:border-slate-700 dark:bg-slate-800 dark:text-white"
                            required
                        />
                    </div>

                    <div>
                        <label class="text-xs font-semibold text-slate-700 dark:text-slate-300">Jumlah Man Power (MP) *</label>
                        <input
                            v-model.number="form.mp_count"
                            type="number"
                            min="1"
                            class="mt-1 w-full rounded-lg border border-slate-200 p-2.5 text-xs text-slate-900 dark:border-slate-700 dark:bg-slate-800 dark:text-white"
                            @input="updateTarget"
                            required
                        />
                    </div>

                    <div>
                        <label class="text-xs font-semibold text-slate-700 dark:text-slate-300">Multiplier per MP *</label>
                        <input
                            v-model.number="form.multiplier"
                            type="number"
                            min="100"
                            class="mt-1 w-full rounded-lg border border-slate-200 p-2.5 text-xs text-slate-900 dark:border-slate-700 dark:bg-slate-800 dark:text-white"
                            @input="updateTarget"
                            required
                        />
                    </div>

                    <div>
                        <label class="text-xs font-semibold text-slate-700 dark:text-slate-300">Target Output Total (pcs) *</label>
                        <input
                            v-model.number="form.target_output"
                            type="number"
                            min="1"
                            class="mt-1 w-full rounded-lg border border-rose-300 bg-rose-50/50 p-2.5 text-xs font-bold text-rose-700 focus:border-rose-500 dark:border-rose-900 dark:bg-rose-950/20 dark:text-rose-400"
                            required
                        />
                    </div>
                </div>

                <!-- Packing Hold Toggle -->
                <div class="rounded-xl border border-slate-200 bg-slate-50 p-4 dark:border-slate-800 dark:bg-slate-800/40">
                    <div class="flex items-center justify-between">
                        <div>
                            <span class="text-xs font-bold text-slate-900 dark:text-white">Aktifkan Packing Hold</span>
                            <p class="text-xs text-slate-500">Tandai jika tahap pengemasan sekunder ditunda (menunggu dus/stiker) tanpa membatalkan batch.</p>
                        </div>
                        <input
                            v-model="form.packing_hold"
                            type="checkbox"
                            class="h-5 w-5 rounded border-slate-300 text-rose-600 focus:ring-rose-500"
                        />
                    </div>
                </div>

                <!-- Notes -->
                <div>
                    <label class="text-xs font-semibold text-slate-700 dark:text-slate-300">Catatan Khusus / Instruksi Produksi</label>
                    <textarea
                        v-model="form.notes"
                        rows="3"
                        placeholder="Contoh: Suhu ekstraksi dijaga stabil pada 60 derajat Celsius. Gunakan saringan 100 mesh..."
                        class="mt-1 w-full rounded-lg border border-slate-200 p-2.5 text-xs text-slate-900 dark:border-slate-700 dark:bg-slate-800 dark:text-white"
                    ></textarea>
                </div>

                <!-- Submit Button -->
                <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-100 dark:border-slate-800">
                    <Link
                        href="/production/schedules"
                        class="rounded-lg border border-slate-200 px-4 py-2 text-xs font-medium text-slate-700 hover:bg-slate-50 dark:border-slate-700 dark:text-slate-300"
                    >
                        Batal
                    </Link>
                    <button
                        type="submit"
                        :disabled="form.processing"
                        class="inline-flex items-center gap-1.5 rounded-lg bg-rose-600 px-5 py-2 text-xs font-semibold text-white shadow-sm hover:bg-rose-700 disabled:opacity-50"
                    >
                        <Save class="h-4 w-4" />
                        {{ form.processing ? 'Menyimpan...' : 'Jadwalkan Rencana Produksi' }}
                    </button>
                </div>
            </form>
        </div>
    </ModuleLayout>
</template>
