<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';
import ModuleLayout from '@/Layouts/ModuleLayout.vue';
import {
    Lightbulb,
    ArrowLeft,
    Send,
    User,
    Building2,
    FileText,
    Sparkles,
} from 'lucide-vue-next';

const props = defineProps({
    employees: Array,
    departments: Array,
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
    department_id: props.departments[0]?.id || '',
    title: '',
    category: 'quality',
    current_condition: '',
    proposed_idea: '',
    expected_benefit: '',
    before_photo_path: '',
    after_photo_path: '',
});

const submit = () => {
    form.post('/hris/kizuki');
};
</script>

<template>
    <Head title="Ajukan Usulan Kaizen Kizuki — HRISNOW" />

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
                    href="/hris/kizuki"
                    class="p-2 rounded-xl bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-slate-500 hover:text-slate-800 transition"
                >
                    <ArrowLeft class="w-5 h-5" />
                </Link>
                <div>
                    <h1 class="text-2xl font-bold text-slate-900 dark:text-white tracking-tight">
                        Form Usulan Ide Kaizen Kizuki
                    </h1>
                    <p class="text-sm text-slate-500 dark:text-slate-400">
                        Setiap ide perbaikan yang diimplementasikan akan mendapatkan Poin Reward Komite.
                    </p>
                </div>
            </div>

            <!-- Form Card -->
            <form @submit.prevent="submit" class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-200 dark:border-slate-700 p-6 shadow-xs space-y-5">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">
                            Inisiator Karyawan <span class="text-rose-500">*</span>
                        </label>
                        <select
                            v-model="form.employee_id"
                            required
                            class="w-full px-3.5 py-2 text-sm bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl outline-none focus:ring-2 focus:ring-blue-500 text-slate-900 dark:text-white"
                        >
                            <option v-for="emp in employees" :key="emp.id" :value="emp.id">
                                {{ emp.user?.name }} (NIK: {{ emp.nik }})
                            </option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">
                            Departemen Terkait <span class="text-rose-500">*</span>
                        </label>
                        <select
                            v-model="form.department_id"
                            required
                            class="w-full px-3.5 py-2 text-sm bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl outline-none focus:ring-2 focus:ring-blue-500 text-slate-900 dark:text-white"
                        >
                            <option v-for="dept in departments" :key="dept.id" :value="dept.id">
                                {{ dept.name }}
                            </option>
                        </select>
                    </div>

                    <div class="md:col-span-2">
                        <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">
                            Judul Usulan Kaizen <span class="text-rose-500">*</span>
                        </label>
                        <input
                            v-model="form.title"
                            type="text"
                            required
                            placeholder="Contoh: Modifikasi Chute Corong Pengisian Meniran untuk Mencegah Debu Terbang"
                            class="w-full px-3.5 py-2 text-sm bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl outline-none focus:ring-2 focus:ring-blue-500 text-slate-900 dark:text-white"
                        />
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">
                            Fokus Kategori Perbaikan <span class="text-rose-500">*</span>
                        </label>
                        <select
                            v-model="form.category"
                            required
                            class="w-full px-3.5 py-2 text-sm bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl outline-none focus:ring-2 focus:ring-blue-500 text-slate-900 dark:text-white"
                        >
                            <option value="quality">Mutu & Kualitas (CPOTB Standard)</option>
                            <option value="safety">Keselamatan & Kesehatan Kerja (K3)</option>
                            <option value="5s">5S (Ringkas, Rapi, Resik, Rawat, Rajin)</option>
                            <option value="cost">Efisiensi Biaya (Cost Reduction)</option>
                            <option value="productivity">Produktivitas & Efisiensi Waktu</option>
                        </select>
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">
                        1. Kondisi Saat Ini (Masalah / Ketidakefisienan yang Ditemukan) <span class="text-rose-500">*</span>
                    </label>
                    <textarea
                        v-model="form.current_condition"
                        rows="3"
                        required
                        placeholder="Deskripsikan kondisi sebelum perbaikan, bottleneck, atau potensi bahaya/pemborosan..."
                        class="w-full px-3.5 py-2 text-sm bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl outline-none focus:ring-2 focus:ring-blue-500 text-slate-900 dark:text-white"
                    ></textarea>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">
                        2. Ide / Solusi Perbaikan yang Diusulkan <span class="text-rose-500">*</span>
                    </label>
                    <textarea
                        v-model="form.proposed_idea"
                        rows="3"
                        required
                        placeholder="Jelaskan langkah solusi atau modifikasi alat/metode yang Anda rancang..."
                        class="w-full px-3.5 py-2 text-sm bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl outline-none focus:ring-2 focus:ring-blue-500 text-slate-900 dark:text-white"
                    ></textarea>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">
                        3. Manfaat & Dampak Positif yang Diharapkan <span class="text-rose-500">*</span>
                    </label>
                    <textarea
                        v-model="form.expected_benefit"
                        rows="2"
                        required
                        placeholder="Contoh: Mengurangi debu bahan aktif 80%, menghemat waktu cleaning 20 menit per batch..."
                        class="w-full px-3.5 py-2 text-sm bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl outline-none focus:ring-2 focus:ring-blue-500 text-slate-900 dark:text-white"
                    ></textarea>
                </div>

                <div class="flex items-center justify-end gap-3 pt-3 border-t border-slate-100 dark:border-slate-700">
                    <Link
                        href="/hris/kizuki"
                        class="px-5 py-2 text-sm rounded-xl border border-slate-200 dark:border-slate-700 text-slate-600 hover:bg-slate-100 dark:text-slate-300 transition"
                    >
                        Batal
                    </Link>
                    <button
                        type="submit"
                        :disabled="form.processing"
                        class="inline-flex items-center gap-2 px-6 py-2 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-xl text-sm transition shadow-sm disabled:opacity-50"
                    >
                        <Sparkles class="w-4 h-4" />
                        Kirim Ide Kaizen
                    </button>
                </div>
            </form>
        </div>
    </ModuleLayout>
</template>
