<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';
import ModuleLayout from '@/Layouts/ModuleLayout.vue';
import {
    CalendarOff,
    ArrowLeft,
    Send,
    User,
    Calendar,
    Phone,
    FileText,
} from 'lucide-vue-next';

const props = defineProps({
    employees: Array,
    categories: Array,
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
    leave_category_id: props.categories[0]?.id || '',
    start_date: new Date().toISOString().substring(0, 10),
    end_date: new Date().toISOString().substring(0, 10),
    reason: '',
    emergency_phone: '',
    substitute_employee_id: '',
});

const submit = () => {
    form.post('/hris/leaves');
};
</script>

<template>
    <Head title="Ajukan Cuti Baru — HRISNOW" />

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
                    href="/hris/leaves"
                    class="p-2 rounded-xl bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-slate-500 hover:text-slate-800 transition"
                >
                    <ArrowLeft class="w-5 h-5" />
                </Link>
                <div>
                    <h1 class="text-2xl font-bold text-slate-900 dark:text-white tracking-tight">
                        Form Pengajuan Cuti & Izin Kerja
                    </h1>
                    <p class="text-sm text-slate-500 dark:text-slate-400">
                        Pengajuan cuti otomatis akan masuk ke antrean persetujuan SPV & HRD.
                    </p>
                </div>
            </div>

            <!-- Form Card -->
            <form @submit.prevent="submit" class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-200 dark:border-slate-700 p-6 shadow-xs space-y-5">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">
                            Nama Karyawan Pemohon <span class="text-rose-500">*</span>
                        </label>
                        <select
                            v-model="form.employee_id"
                            required
                            class="w-full px-3.5 py-2 text-sm bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl outline-none focus:ring-2 focus:ring-blue-500 text-slate-900 dark:text-white"
                        >
                            <option v-for="emp in employees" :key="emp.id" :value="emp.id">
                                {{ emp.user?.name }} (NIK: {{ emp.nik }} • Sisa Cuti: {{ emp.leave_balance }} Hari)
                            </option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">
                            Jenis / Kategori Cuti <span class="text-rose-500">*</span>
                        </label>
                        <select
                            v-model="form.leave_category_id"
                            required
                            class="w-full px-3.5 py-2 text-sm bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl outline-none focus:ring-2 focus:ring-blue-500 text-slate-900 dark:text-white"
                        >
                            <option v-for="cat in categories" :key="cat.id" :value="cat.id">
                                {{ cat.name }} ({{ cat.is_deduct_annual ? 'Potong Saldo Tahunan' : 'Izin Khusus' }})
                            </option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">
                            Tanggal Mulai Cuti <span class="text-rose-500">*</span>
                        </label>
                        <input
                            v-model="form.start_date"
                            type="date"
                            required
                            class="w-full px-3.5 py-2 text-sm bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl outline-none focus:ring-2 focus:ring-blue-500 text-slate-900 dark:text-white"
                        />
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">
                            Tanggal Selesai Cuti <span class="text-rose-500">*</span>
                        </label>
                        <input
                            v-model="form.end_date"
                            type="date"
                            required
                            class="w-full px-3.5 py-2 text-sm bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl outline-none focus:ring-2 focus:ring-blue-500 text-slate-900 dark:text-white"
                        />
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">
                            Karyawan Pengganti (Shift Handover)
                        </label>
                        <select
                            v-model="form.substitute_employee_id"
                            class="w-full px-3.5 py-2 text-sm bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl outline-none focus:ring-2 focus:ring-blue-500 text-slate-900 dark:text-white"
                        >
                            <option value="">Pilih Karyawan Pengganti</option>
                            <option v-for="emp in employees" :key="emp.id" :value="emp.id">
                                {{ emp.user?.name }} ({{ emp.position }})
                            </option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">
                            Nomor Kontak Darurat Selama Cuti
                        </label>
                        <input
                            v-model="form.emergency_phone"
                            type="text"
                            placeholder="0812-xxxx-xxxx"
                            class="w-full px-3.5 py-2 text-sm bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl outline-none focus:ring-2 focus:ring-blue-500 text-slate-900 dark:text-white"
                        />
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">
                        Alasan / Keperluan Cuti <span class="text-rose-500">*</span>
                    </label>
                    <textarea
                        v-model="form.reason"
                        rows="3"
                        required
                        placeholder="Uraikan alasan permohonan cuti secara jelas..."
                        class="w-full px-3.5 py-2 text-sm bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl outline-none focus:ring-2 focus:ring-blue-500 text-slate-900 dark:text-white"
                    ></textarea>
                </div>

                <div class="flex items-center justify-end gap-3 pt-3 border-t border-slate-100 dark:border-slate-700">
                    <Link
                        href="/hris/leaves"
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
                        Kirim Permohonan Cuti
                    </button>
                </div>
            </form>
        </div>
    </ModuleLayout>
</template>
