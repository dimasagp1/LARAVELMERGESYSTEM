<script setup>
import { computed } from 'vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import ModuleLayout from '@/Layouts/ModuleLayout.vue';
import {
    Timer,
    ArrowLeft,
    Send,
    User,
    Calendar,
    Clock,
    Banknote,
    Layers,
    FileText,
} from 'lucide-vue-next';

const props = defineProps({
    employees: Array,
    shifts: Array,
});

const hrisMenus = [
    { name: 'Overview', route: '/hris', icon: 'LayoutDashboard' },
    { name: 'Master Karyawan', route: '/hris/employees', icon: 'Users' },
    { name: 'Presensi & Geofence', route: '/hris/attendance', icon: 'CalendarCheck' },
    { name: 'Pengajuan Cuti', route: '/hris/leaves', icon: 'CalendarOff' },
    { name: 'Lembur (SPL) & Time Bank', route: '/hris/overtime', icon: 'Timer' },
    { name: 'Kaizen Kizuki & Point', route: '/hris/kizuki', icon: 'Lightbulb' },
    { name: 'Payroll PPh 21 TER', route: '/hris/payroll', icon: 'Banknote' },
    { name: 'Strategic BSC L3 & L4', route: '/hris/strategic', icon: 'Target' },
];

const form = useForm({
    employee_id: props.employees[0]?.id || '',
    date: new Date().toISOString().substring(0, 10),
    start_time: '17:00',
    end_time: '21:00',
    is_holiday: false,
    reason: 'Penyelesaian batch produksi kapsul herbal untuk mengejar jadwal rilis QC',
    work_target: 'Packing sekunder 30.000 blister & sanitasi mesin blister',
    compensation_type: 'cash',
});

const submit = () => {
    form.post('/hris/overtime');
};
</script>

<template>
    <Head title="Buat SPL Lembur Baru — HRISNOW" />

    <ModuleLayout
        module-slug="hris"
        module-name="HRISNOW"
        color-theme="blue"
        :menus="hrisMenus"
    >
        <div class="max-w-3xl mx-auto space-y-6">
            <!-- Header -->
            <div class="flex items-center gap-3">
                <Link
                    href="/hris/overtime"
                    class="p-2 rounded-xl bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-slate-500 hover:text-slate-800 transition"
                >
                    <ArrowLeft class="w-5 h-5" />
                </Link>
                <div>
                    <h1 class="text-2xl font-bold text-slate-900 dark:text-white tracking-tight">
                        Surat Perintah Lembur (SPL) Baru
                    </h1>
                    <p class="text-sm text-slate-500 dark:text-slate-400">
                        Otomatis menerapkan skema pengali Depnaker RI & peruntukan kompensasi.
                    </p>
                </div>
            </div>

            <!-- Form Card -->
            <form @submit.prevent="submit" class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-200 dark:border-slate-700 p-6 shadow-xs space-y-5">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">
                            Karyawan Pelaksana Lembur <span class="text-rose-500">*</span>
                        </label>
                        <select
                            v-model="form.employee_id"
                            required
                            class="w-full px-3.5 py-2 text-sm bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl outline-none focus:ring-2 focus:ring-blue-500 text-slate-900 dark:text-white"
                        >
                            <option v-for="emp in employees" :key="emp.id" :value="emp.id">
                                {{ emp.user?.name }} ({{ emp.nik }} • {{ emp.department?.name }})
                            </option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">
                            Tanggal Pelaksanaan <span class="text-rose-500">*</span>
                        </label>
                        <input
                            v-model="form.date"
                            type="date"
                            required
                            class="w-full px-3.5 py-2 text-sm bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl outline-none focus:ring-2 focus:ring-blue-500 text-slate-900 dark:text-white"
                        />
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">
                            Jam Mulai Lembur <span class="text-rose-500">*</span>
                        </label>
                        <input
                            v-model="form.start_time"
                            type="time"
                            required
                            class="w-full px-3.5 py-2 text-sm bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl outline-none focus:ring-2 focus:ring-blue-500 text-slate-900 dark:text-white font-mono"
                        />
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">
                            Jam Selesai Lembur <span class="text-rose-500">*</span>
                        </label>
                        <input
                            v-model="form.end_time"
                            type="time"
                            required
                            class="w-full px-3.5 py-2 text-sm bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl outline-none focus:ring-2 focus:ring-blue-500 text-slate-900 dark:text-white font-mono"
                        />
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">
                            Skema Kompensasi Lembur <span class="text-rose-500">*</span>
                        </label>
                        <select
                            v-model="form.compensation_type"
                            required
                            class="w-full px-3.5 py-2 text-sm bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl outline-none focus:ring-2 focus:ring-blue-500 text-slate-900 dark:text-white"
                        >
                            <option value="cash">Uang Lembur (Dibayar di Payroll Bulanan)</option>
                            <option value="time_bank">Time Bank (Disimpan jadi Saldo Jam Cuti)</option>
                        </select>
                    </div>

                    <div class="flex items-center pt-5">
                        <label class="flex items-center gap-2 cursor-pointer select-none">
                            <input
                                v-model="form.is_holiday"
                                type="checkbox"
                                class="w-4 h-4 text-blue-600 rounded border-slate-300 focus:ring-blue-500"
                            />
                            <span class="text-xs font-semibold text-slate-700 dark:text-slate-300">
                                Lembur di Hari Libur Resmi / Akhir Pekan (Multiplier 2x/3x/4x)
                            </span>
                        </label>
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">
                        Target Capaian Pekerjaan Lembur
                    </label>
                    <input
                        v-model="form.work_target"
                        type="text"
                        placeholder="Contoh: Menyelesaikan 40.000 botol sirup meniran lini A"
                        class="w-full px-3.5 py-2 text-sm bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl outline-none focus:ring-2 focus:ring-blue-500 text-slate-900 dark:text-white"
                    />
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">
                        Justifikasi / Alasan Kebutuhan Lembur <span class="text-rose-500">*</span>
                    </label>
                    <textarea
                        v-model="form.reason"
                        rows="3"
                        required
                        placeholder="Jelaskan urgensi operasional perlunya lembur..."
                        class="w-full px-3.5 py-2 text-sm bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl outline-none focus:ring-2 focus:ring-blue-500 text-slate-900 dark:text-white"
                    ></textarea>
                </div>

                <!-- Depnaker Explanation Alert -->
                <div class="p-4 bg-amber-50 dark:bg-amber-950/40 rounded-xl border border-amber-200 dark:border-amber-800 text-xs text-amber-800 dark:text-amber-300 space-y-1">
                    <div class="font-bold flex items-center gap-1.5">
                        <Timer class="w-4 h-4" />
                        Ketentuan Perhitungan Upah Lembur Depnaker RI (PP 35/2021)
                    </div>
                    <p class="text-[11px] leading-relaxed">
                        • Hari Kerja Biasa: 1 jam pertama dikalikan 1.5x, jam berikutnya dikalikan 2.0x.<br>
                        • Hari Libur Resmi / Istirahat Mingguan: 8 jam pertama dikalikan 2.0x, jam ke-9 dikalikan 3.0x, jam ke-10 seterusnya dikalikan 4.0x.
                    </p>
                </div>

                <div class="flex items-center justify-end gap-3 pt-3 border-t border-slate-100 dark:border-slate-700">
                    <Link
                        href="/hris/overtime"
                        class="px-5 py-2 text-sm rounded-xl border border-slate-200 dark:border-slate-700 text-slate-600 hover:bg-slate-100 dark:text-slate-300 transition"
                    >
                        Batal
                    </Link>
                    <button
                        type="submit"
                        :disabled="form.processing"
                        class="inline-flex items-center gap-2 px-6 py-2 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-xl text-sm transition shadow-sm disabled:opacity-50"
                    >
                        <Send class="w-4 h-4" />
                        Terbitkan SPL
                    </button>
                </div>
            </form>
        </div>
    </ModuleLayout>
</template>
