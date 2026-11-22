<script setup>
import { ref } from 'vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import ModuleLayout from '@/Layouts/ModuleLayout.vue';
import {
    Users,
    ArrowLeft,
    Save,
    Building2,
    Calendar,
    Briefcase,
    Banknote,
    FileText,
    Shield,
} from 'lucide-vue-next';

const props = defineProps({
    employee: Object,
    departments: Array,
    plants: Array,
    golongans: Array,
    shifts: Array,
    officeLocations: Array,
});

const isEdit = !!props.employee;

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
    name: props.employee?.user?.name || '',
    email: props.employee?.user?.email || '',
    department_id: props.employee?.department_id || '',
    plant_id: props.employee?.plant_id || '',
    golongan_id: props.employee?.golongan_id || '',
    shift_id: props.employee?.shift_id || '',
    office_location_id: props.employee?.office_location_id || '',
    position: props.employee?.position || '',
    employment_status: props.employee?.employment_status || 'permanent',
    status: props.employee?.status || 'active',
    join_date: props.employee?.join_date || new Date().toISOString().substring(0, 10),
    end_date: props.employee?.end_date || '',
    basic_salary: props.employee?.basic_salary || 5000000,
    allowance_fixed: props.employee?.allowance_fixed || 500000,
    tax_ptkp_status: props.employee?.tax_ptkp_status || 'TK/0',
    bank_name: props.employee?.bank_name || 'BCA',
    bank_account_no: props.employee?.bank_account_no || '',
    emergency_contact_name: props.employee?.emergency_contact_name || '',
    emergency_contact_phone: props.employee?.emergency_contact_phone || '',
    leave_balance: props.employee?.leave_balance ?? 12,
});

const submit = () => {
    if (isEdit) {
        form.put(`/hris/employees/${props.employee.id}`);
    } else {
        form.post('/hris/employees');
    }
};
</script>

<template>
    <Head :title="isEdit ? 'Edit Karyawan — HRISNOW' : 'Tambah Karyawan Baru — HRISNOW'" />

    <ModuleLayout
        module-slug="hris"
        module-name="HRISNOW"
        color-theme="blue"
        :menus="hrisMenus"
    >
        <div class="max-w-4xl mx-auto space-y-6">
            <!-- Header -->
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <Link
                        href="/hris/employees"
                        class="p-2 rounded-xl bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-slate-500 hover:text-slate-800 transition"
                    >
                        <ArrowLeft class="w-5 h-5" />
                    </Link>
                    <div>
                        <h1 class="text-2xl font-bold text-slate-900 dark:text-white tracking-tight">
                            {{ isEdit ? 'Perbarui Profil Karyawan' : 'Form Registrasi Karyawan Baru' }}
                        </h1>
                        <p class="text-sm text-slate-500 dark:text-slate-400">
                            {{ isEdit ? `NIK: ${employee.nik}` : 'Sistem otomatis akan membangkitkan NIK unik dan akun ERP' }}
                        </p>
                    </div>
                </div>
            </div>

            <!-- Main Form Card -->
            <form @submit.prevent="submit" class="space-y-6">
                <!-- Section 1: Data Pribadi & Akun Login -->
                <div class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-200 dark:border-slate-700 p-6 shadow-xs space-y-4">
                    <h3 class="font-bold text-base text-slate-900 dark:text-white flex items-center gap-2">
                        <Users class="w-5 h-5 text-blue-600" />
                        1. Informasi Identitas & Akun Login
                    </h3>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">
                                Nama Lengkap Sesuai KTP <span class="text-rose-500">*</span>
                            </label>
                            <input
                                v-model="form.name"
                                type="text"
                                required
                                placeholder="Contoh: Budi Santoso, S.Farm., Apt."
                                class="w-full px-3.5 py-2 text-sm bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl outline-none focus:ring-2 focus:ring-blue-500 text-slate-900 dark:text-white"
                            />
                            <span v-if="form.errors.name" class="text-xs text-rose-500 mt-1 block">{{ form.errors.name }}</span>
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">
                                Alamat Email Perusahaan / Login <span class="text-rose-500">*</span>
                            </label>
                            <input
                                v-model="form.email"
                                type="email"
                                required
                                :disabled="isEdit"
                                placeholder="budi.santoso@herbatech.com"
                                class="w-full px-3.5 py-2 text-sm bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl outline-none focus:ring-2 focus:ring-blue-500 text-slate-900 dark:text-white disabled:opacity-60"
                            />
                            <span v-if="form.errors.email" class="text-xs text-rose-500 mt-1 block">{{ form.errors.email }}</span>
                        </div>
                    </div>
                </div>

                <!-- Section 2: Penempatan Organisasi & Golongan -->
                <div class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-200 dark:border-slate-700 p-6 shadow-xs space-y-4">
                    <h3 class="font-bold text-base text-slate-900 dark:text-white flex items-center gap-2">
                        <Building2 class="w-5 h-5 text-emerald-600" />
                        2. Penempatan Organisasi & Struktur Karir
                    </h3>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">
                                Departemen Kerja <span class="text-rose-500">*</span>
                            </label>
                            <select
                                v-model="form.department_id"
                                required
                                class="w-full px-3.5 py-2 text-sm bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl outline-none focus:ring-2 focus:ring-blue-500 text-slate-900 dark:text-white"
                            >
                                <option value="" disabled>Pilih Departemen</option>
                                <option v-for="dept in departments" :key="dept.id" :value="dept.id">
                                    {{ dept.name }} ({{ dept.code }})
                                </option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">
                                Penempatan Plant Pabrik
                            </label>
                            <select
                                v-model="form.plant_id"
                                class="w-full px-3.5 py-2 text-sm bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl outline-none focus:ring-2 focus:ring-blue-500 text-slate-900 dark:text-white"
                            >
                                <option value="">Kantor Pusat (Default)</option>
                                <option v-for="plant in plants" :key="plant.id" :value="plant.id">
                                    {{ plant.name }}
                                </option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">
                                Master Golongan Gaji & Karir <span class="text-rose-500">*</span>
                            </label>
                            <select
                                v-model="form.golongan_id"
                                required
                                class="w-full px-3.5 py-2 text-sm bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl outline-none focus:ring-2 focus:ring-blue-500 text-slate-900 dark:text-white"
                            >
                                <option value="" disabled>Pilih Golongan</option>
                                <option v-for="gol in golongans" :key="gol.id" :value="gol.id">
                                    Gol. {{ gol.code }} — {{ gol.name }} (Lvl {{ gol.level }})
                                </option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">
                                Nama Jabatan / Posisi <span class="text-rose-500">*</span>
                            </label>
                            <input
                                v-model="form.position"
                                type="text"
                                required
                                placeholder="Contoh: Formulator Solid R&D"
                                class="w-full px-3.5 py-2 text-sm bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl outline-none focus:ring-2 focus:ring-blue-500 text-slate-900 dark:text-white"
                            />
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">
                                Shift Kerja Default
                            </label>
                            <select
                                v-model="form.shift_id"
                                class="w-full px-3.5 py-2 text-sm bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl outline-none focus:ring-2 focus:ring-blue-500 text-slate-900 dark:text-white"
                            >
                                <option value="">Pilih Shift</option>
                                <option v-for="sft in shifts" :key="sft.id" :value="sft.id">
                                    {{ sft.name }} ({{ sft.start_time.substring(0,5) }} - {{ sft.end_time.substring(0,5) }})
                                </option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">
                                Titik Geofence Absensi
                            </label>
                            <select
                                v-model="form.office_location_id"
                                class="w-full px-3.5 py-2 text-sm bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl outline-none focus:ring-2 focus:ring-blue-500 text-slate-900 dark:text-white"
                            >
                                <option value="">Pilih Lokasi Kantor</option>
                                <option v-for="loc in officeLocations" :key="loc.id" :value="loc.id">
                                    {{ loc.name }} (Radius {{ loc.radius_meters }}m)
                                </option>
                            </select>
                        </div>
                    </div>
                </div>

                <!-- Section 3: Status Kontrak & Kompensasi -->
                <div class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-200 dark:border-slate-700 p-6 shadow-xs space-y-4">
                    <h3 class="font-bold text-base text-slate-900 dark:text-white flex items-center gap-2">
                        <Banknote class="w-5 h-5 text-amber-600" />
                        3. Status Hubungan Kerja, Kompensasi & Pajak PPh 21
                    </h3>

                    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">
                                Status Kerja <span class="text-rose-500">*</span>
                            </label>
                            <select
                                v-model="form.employment_status"
                                required
                                class="w-full px-3.5 py-2 text-sm bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl outline-none focus:ring-2 focus:ring-blue-500 text-slate-900 dark:text-white"
                            >
                                <option value="permanent">Karyawan Tetap</option>
                                <option value="contract">Kontrak (PKWT)</option>
                                <option value="probation">Masa Percobaan</option>
                                <option value="internship">Magang / PKL</option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">
                                Tanggal Mulai Bekerja <span class="text-rose-500">*</span>
                            </label>
                            <input
                                v-model="form.join_date"
                                type="date"
                                required
                                class="w-full px-3.5 py-2 text-sm bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl outline-none focus:ring-2 focus:ring-blue-500 text-slate-900 dark:text-white"
                            />
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">
                                Akhir Kontrak (Jika PKWT)
                            </label>
                            <input
                                v-model="form.end_date"
                                type="date"
                                class="w-full px-3.5 py-2 text-sm bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl outline-none focus:ring-2 focus:ring-blue-500 text-slate-900 dark:text-white"
                            />
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">
                                Saldo Cuti Tahunan (Hari)
                            </label>
                            <input
                                v-model="form.leave_balance"
                                type="number"
                                min="0"
                                class="w-full px-3.5 py-2 text-sm bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl outline-none focus:ring-2 focus:ring-blue-500 text-slate-900 dark:text-white"
                            />
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">
                                Gaji Pokok (IDR) <span class="text-rose-500">*</span>
                            </label>
                            <input
                                v-model="form.basic_salary"
                                type="number"
                                min="0"
                                required
                                class="w-full px-3.5 py-2 text-sm bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl outline-none focus:ring-2 focus:ring-blue-500 text-slate-900 dark:text-white font-mono"
                            />
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">
                                Tunjangan Tetap (IDR)
                            </label>
                            <input
                                v-model="form.allowance_fixed"
                                type="number"
                                min="0"
                                class="w-full px-3.5 py-2 text-sm bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl outline-none focus:ring-2 focus:ring-blue-500 text-slate-900 dark:text-white font-mono"
                            />
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">
                                Status PTKP (PPh 21 TER) <span class="text-rose-500">*</span>
                            </label>
                            <select
                                v-model="form.tax_ptkp_status"
                                required
                                class="w-full px-3.5 py-2 text-sm bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl outline-none focus:ring-2 focus:ring-blue-500 text-slate-900 dark:text-white font-mono"
                            >
                                <option value="TK/0">TK/0 (Kategori A)</option>
                                <option value="TK/1">TK/1 (Kategori A)</option>
                                <option value="K/0">K/0 (Kategori A)</option>
                                <option value="TK/2">TK/2 (Kategori B)</option>
                                <option value="K/1">K/1 (Kategori B)</option>
                                <option value="K/2">K/2 (Kategori B)</option>
                                <option value="K/3">K/3 (Kategori C)</option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">
                                Rekening Payroll
                            </label>
                            <div class="flex gap-2">
                                <input
                                    v-model="form.bank_name"
                                    type="text"
                                    placeholder="Bank (BCA)"
                                    class="w-1/3 px-2 py-2 text-xs bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl"
                                />
                                <input
                                    v-model="form.bank_account_no"
                                    type="text"
                                    placeholder="No. Rekening"
                                    class="w-2/3 px-2 py-2 text-xs bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl font-mono"
                                />
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Action Button -->
                <div class="flex items-center justify-end gap-3 pt-2">
                    <Link
                        href="/hris/employees"
                        class="px-5 py-2 text-sm rounded-xl border border-slate-200 dark:border-slate-700 text-slate-600 hover:bg-slate-100 dark:text-slate-300 transition"
                    >
                        Batal
                    </Link>
                    <button
                        type="submit"
                        :disabled="form.processing"
                        class="inline-flex items-center gap-2 px-6 py-2 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-xl text-sm transition shadow-sm disabled:opacity-50"
                    >
                        <Save class="w-4 h-4" />
                        {{ isEdit ? 'Simpan Perubahan' : 'Daftarkan Karyawan Baru' }}
                    </button>
                </div>
            </form>
        </div>
    </ModuleLayout>
</template>
