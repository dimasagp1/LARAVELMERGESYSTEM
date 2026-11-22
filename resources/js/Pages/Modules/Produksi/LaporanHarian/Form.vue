<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';
import ModuleLayout from '@/Layouts/ModuleLayout.vue';
import {
    Timer,
    ArrowLeft,
    Save,
    Calendar,
    Layers,
    Factory,
} from 'lucide-vue-next';

const props = defineProps({
    weeklyPlans: {
        type: Array,
        default: () => [],
    },
    lines: {
        type: Array,
        default: () => [],
    },
    machines: {
        type: Array,
        default: () => [],
    },
    selectedPlanId: {
        type: [String, Number],
        default: '',
    },
});

const form = useForm({
    weekly_plan_id: props.selectedPlanId || (props.weeklyPlans[0]?.id || ''),
    mesin_id: props.machines[0]?.id || '',
    tanggal: new Date().toISOString().split('T')[0],
    shift: 'shift1',
    notes: '',
});

const submit = () => {
    form.post('/production/daily-reports');
};
</script>

<template>
    <Head title="Buka Laporan Harian Produksi Baru" />

    <ModuleLayout module-slug="production">
        <div class="max-w-2xl space-y-6">
            <div class="flex items-center gap-3">
                <Link
                    href="/production/daily-reports"
                    class="rounded-lg border border-slate-200 p-2 text-slate-500 hover:bg-slate-50 dark:border-slate-800 dark:text-slate-400"
                >
                    <ArrowLeft class="h-4 w-4" />
                </Link>
                <div>
                    <h1 class="text-xl font-bold text-slate-900 dark:text-white sm:text-2xl">
                        Inisiasi Laporan Harian Produksi (LHP)
                    </h1>
                    <p class="text-xs text-slate-500">
                        Pilih jadwal rencana WPS aktif untuk dieksekusi di konsol timer digital
                    </p>
                </div>
            </div>

            <form @submit.prevent="submit" class="space-y-5 rounded-2xl border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900">
                <div>
                    <label class="text-xs font-semibold text-slate-700 dark:text-slate-300">Pilih Rencana WPS Aktif *</label>
                    <select
                        v-model="form.weekly_plan_id"
                        class="mt-1 w-full rounded-lg border border-slate-200 p-2.5 text-xs text-slate-900 dark:border-slate-700 dark:bg-slate-800 dark:text-white font-medium"
                        required
                    >
                        <option v-for="wp in weeklyPlans" :key="wp.id" :value="wp.id">
                            {{ wp.plan_number }} | Batch: {{ wp.batch_number }} | {{ wp.product?.name }} [{{ wp.proses.toUpperCase() }}]
                        </option>
                    </select>
                </div>

                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <div>
                        <label class="text-xs font-semibold text-slate-700 dark:text-slate-300">Mesin Produksi Utama</label>
                        <select
                            v-model="form.mesin_id"
                            class="mt-1 w-full rounded-lg border border-slate-200 p-2.5 text-xs text-slate-900 dark:border-slate-700 dark:bg-slate-800 dark:text-white"
                        >
                            <option value="">Gunakan Standar Lini</option>
                            <option v-for="m in machines" :key="m.id" :value="m.id">
                                {{ m.name }} (CT: {{ m.cycle_time_seconds }}s)
                            </option>
                        </select>
                    </div>

                    <div>
                        <label class="text-xs font-semibold text-slate-700 dark:text-slate-300">Shift Kerja *</label>
                        <select
                            v-model="form.shift"
                            class="mt-1 w-full rounded-lg border border-slate-200 p-2.5 text-xs text-slate-900 dark:border-slate-700 dark:bg-slate-800 dark:text-white"
                            required
                        >
                            <option value="shift1">Shift 1 (Pagi - 07:00 s/d 15:30)</option>
                            <option value="shift2">Shift 2 (Siang - 15:30 s/d 23:30)</option>
                            <option value="shift3">Shift 3 (Malam - 23:30 s/d 07:00)</option>
                        </select>
                    </div>
                </div>

                <div>
                    <label class="text-xs font-semibold text-slate-700 dark:text-slate-300">Tanggal Pelaksanaan *</label>
                    <input
                        v-model="form.tanggal"
                        type="date"
                        class="mt-1 w-full rounded-lg border border-slate-200 p-2.5 text-xs text-slate-900 dark:border-slate-700 dark:bg-slate-800 dark:text-white"
                        required
                    />
                </div>

                <div>
                    <label class="text-xs font-semibold text-slate-700 dark:text-slate-300">Catatan Awal Line Clearance</label>
                    <textarea
                        v-model="form.notes"
                        rows="2"
                        placeholder="Contoh: Area bersih, disinfeksi alkohol 70% selesai dilakukan..."
                        class="mt-1 w-full rounded-lg border border-slate-200 p-2.5 text-xs text-slate-900 dark:border-slate-700 dark:bg-slate-800 dark:text-white"
                    ></textarea>
                </div>

                <div class="flex items-center justify-end gap-3 pt-3 border-t border-slate-100 dark:border-slate-800">
                    <Link
                        href="/production/daily-reports"
                        class="rounded-lg border border-slate-200 px-4 py-2 text-xs font-medium text-slate-700 hover:bg-slate-50 dark:border-slate-700 dark:text-slate-300"
                    >
                        Batal
                    </Link>
                    <button
                        type="submit"
                        :disabled="form.processing"
                        class="inline-flex items-center gap-1.5 rounded-lg bg-rose-600 px-5 py-2 text-xs font-semibold text-white shadow-sm hover:bg-rose-700 disabled:opacity-50"
                    >
                        <Save class="h-4 w-4" /> Buka Konsol Timer
                    </button>
                </div>
            </form>
        </div>
    </ModuleLayout>
</template>
