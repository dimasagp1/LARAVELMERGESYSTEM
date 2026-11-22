<script setup>
import { ref } from 'vue';
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import ModuleLayout from '@/Layouts/ModuleLayout.vue';
import {
    CalendarCheck,
    Clock,
    MapPin,
    Search,
    Filter,
    CheckCircle2,
    AlertCircle,
    UserCheck,
    Navigation,
    Camera,
    Building2,
    Calendar,
    LogOut,
} from 'lucide-vue-next';

const props = defineProps({
    attendances: Object,
    selectedDate: String,
    filters: Object,
    summary: Object,
    departments: Array,
    officeLocations: Array,
    shifts: Array,
    employees: Array,
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

const filterDate = ref(props.selectedDate || new Date().toISOString().substring(0, 10));
const filterDept = ref(props.filters.department_id || '');
const filterStatus = ref(props.filters.status || '');

const applyFilters = () => {
    router.get('/hris/attendance', {
        date: filterDate.value,
        department_id: filterDept.value || undefined,
        status: filterStatus.value || undefined,
    }, {
        preserveState: true,
        preserveScroll: true,
    });
};

// Check-in simulator form
const checkInForm = useForm({
    employee_id: props.employees[0]?.id || '',
    shift_id: props.shifts[0]?.id || '',
    office_location_id: props.officeLocations[0]?.id || '',
    latitude: -7.681240, // Sukoharjo Plant coords
    longitude: 110.835420,
    selfie_image_path: 'selfie_sukoharjo.jpg',
    notes: 'Presensi biometrik via mobile ERP',
});

const submitCheckIn = () => {
    checkInForm.post('/hris/attendance/check-in', {
        preserveScroll: true,
        onSuccess: () => {
            checkInForm.reset('notes');
        },
    });
};

const checkOutForm = useForm({
    attendance_id: '',
    latitude: -7.681240,
    longitude: 110.835420,
});

const submitCheckOut = (attId) => {
    checkOutForm.attendance_id = attId;
    checkOutForm.post('/hris/attendance/check-out', {
        preserveScroll: true,
    });
};
</script>

<template>
    <Head title="Presensi & Geofencing — HRISNOW" />

    <ModuleLayout
        module-slug="hris"
        module-name="HRISNOW"
        color-theme="blue"
        :menus="hrisMenus"
    >
        <div class="space-y-6">
            <!-- Header -->
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 bg-white dark:bg-slate-800 p-6 rounded-2xl shadow-xs border border-slate-200 dark:border-slate-700">
                <div class="flex items-center gap-3">
                    <div class="p-3 bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 rounded-xl">
                        <CalendarCheck class="w-8 h-8" />
                    </div>
                    <div>
                        <h1 class="text-2xl font-bold text-slate-900 dark:text-white tracking-tight">
                            Presensi Biometrik & Geofence
                        </h1>
                        <p class="text-sm text-slate-500 dark:text-slate-400 mt-0.5">
                            Validasi koordinat GPS Haversine, deteksi keterlambatan otomatis, dan toleransi check-in multi-plant.
                        </p>
                    </div>
                </div>

                <div class="flex items-center gap-3 text-xs">
                    <div class="px-3 py-1.5 rounded-xl bg-emerald-50 text-emerald-700 border border-emerald-200 font-semibold">
                        Hadir: {{ summary.total_present }}
                    </div>
                    <div class="px-3 py-1.5 rounded-xl bg-amber-50 text-amber-700 border border-amber-200 font-semibold">
                        Terlambat: {{ summary.total_late }}
                    </div>
                    <div class="px-3 py-1.5 rounded-xl bg-blue-50 text-blue-700 border border-blue-200 font-semibold">
                        Cuti/Izin: {{ summary.total_leave }}
                    </div>
                </div>
            </div>

            <!-- Quick Geofenced Simulator Card -->
            <div class="bg-gradient-to-r from-blue-900 via-slate-900 to-indigo-950 text-white rounded-2xl p-6 shadow-md border border-blue-800/40">
                <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-6">
                    <div class="space-y-2 max-w-md">
                        <div class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-semibold bg-blue-500/20 text-blue-300 border border-blue-500/30">
                            <Navigation class="w-3.5 h-3.5" />
                            Simulator Absensi Mobile GPS Haversine
                        </div>
                        <h2 class="text-lg font-bold text-white">
                            Check-In Biometrik Karyawan
                        </h2>
                        <p class="text-xs text-slate-300">
                            Simulasi presensi karyawan dengan validasi geofence radius kantor (toleransi 100 meter dari titik koordinat pabrik Sukoharjo / Boyolali).
                        </p>
                    </div>

                    <!-- Simulator Form Controls -->
                    <div class="bg-white/10 backdrop-blur-md p-4 rounded-xl border border-white/20 flex flex-wrap items-center gap-3">
                        <div class="w-full sm:w-auto">
                            <label class="block text-[10px] text-slate-300 font-semibold uppercase mb-1">Karyawan</label>
                            <select
                                v-model="checkInForm.employee_id"
                                class="px-3 py-1.5 text-xs bg-slate-900/80 border border-slate-700 rounded-lg text-white outline-none"
                            >
                                <option v-for="emp in employees" :key="emp.id" :value="emp.id">
                                    {{ emp.user?.name }} ({{ emp.nik }})
                                </option>
                            </select>
                        </div>

                        <div class="w-full sm:w-auto">
                            <label class="block text-[10px] text-slate-300 font-semibold uppercase mb-1">Shift Kerja</label>
                            <select
                                v-model="checkInForm.shift_id"
                                class="px-3 py-1.5 text-xs bg-slate-900/80 border border-slate-700 rounded-lg text-white outline-none"
                            >
                                <option v-for="sft in shifts" :key="sft.id" :value="sft.id">
                                    {{ sft.name }} ({{ sft.start_time.substring(0,5) }})
                                </option>
                            </select>
                        </div>

                        <div class="w-full sm:w-auto">
                            <label class="block text-[10px] text-slate-300 font-semibold uppercase mb-1">Titik Lokasi</label>
                            <select
                                v-model="checkInForm.office_location_id"
                                class="px-3 py-1.5 text-xs bg-slate-900/80 border border-slate-700 rounded-lg text-white outline-none"
                            >
                                <option v-for="loc in officeLocations" :key="loc.id" :value="loc.id">
                                    {{ loc.name }} (R={{ loc.radius_meters }}m)
                                </option>
                            </select>
                        </div>

                        <div class="w-full sm:w-auto pt-4 sm:pt-0">
                            <button
                                @click="submitCheckIn"
                                :disabled="checkInForm.processing"
                                class="w-full inline-flex items-center justify-center gap-2 px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white font-semibold rounded-lg text-xs transition shadow-sm disabled:opacity-50"
                            >
                                <UserCheck class="w-4 h-4" />
                                Kirim Check-In
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Filters Bar -->
            <div class="bg-white dark:bg-slate-800 p-4 rounded-2xl border border-slate-200 dark:border-slate-700 shadow-xs flex flex-wrap items-center justify-between gap-4">
                <div class="flex items-center gap-3">
                    <div class="flex items-center gap-2">
                        <Calendar class="w-4 h-4 text-slate-400" />
                        <span class="text-xs font-semibold text-slate-600 dark:text-slate-300">Pilih Tanggal:</span>
                    </div>
                    <input
                        v-model="filterDate"
                        @change="applyFilters"
                        type="date"
                        class="px-3 py-1.5 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl text-xs text-slate-900 dark:text-white outline-none"
                    />
                </div>

                <div class="flex items-center gap-3">
                    <select
                        v-model="filterDept"
                        @change="applyFilters"
                        class="px-3 py-1.5 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl text-xs text-slate-700 dark:text-slate-300 outline-none"
                    >
                        <option value="">Semua Departemen</option>
                        <option v-for="dept in departments" :key="dept.id" :value="dept.id">
                            {{ dept.name }}
                        </option>
                    </select>

                    <select
                        v-model="filterStatus"
                        @change="applyFilters"
                        class="px-3 py-1.5 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl text-xs text-slate-700 dark:text-slate-300 outline-none"
                    >
                        <option value="">Semua Status</option>
                        <option value="present">Hadir Tepat Waktu</option>
                        <option value="late">Hadir Terlambat</option>
                        <option value="leave">Izin / Cuti</option>
                        <option value="alpha">Alpha</option>
                    </select>
                </div>
            </div>

            <!-- Attendances Table -->
            <div class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-200 dark:border-slate-700 shadow-xs overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-xs text-left text-slate-600 dark:text-slate-300">
                        <thead class="text-[11px] uppercase bg-slate-50 dark:bg-slate-900/50 text-slate-400 border-b border-slate-200 dark:border-slate-700">
                            <tr>
                                <th class="px-4 py-3">Karyawan</th>
                                <th class="px-4 py-3">Departemen</th>
                                <th class="px-4 py-3">Shift Roster</th>
                                <th class="px-4 py-3">Jam Masuk (Check-In)</th>
                                <th class="px-4 py-3">Jam Pulang (Check-Out)</th>
                                <th class="px-4 py-3">Durasi Kerja</th>
                                <th class="px-4 py-3">Radius Geofence</th>
                                <th class="px-4 py-3">Status</th>
                                <th class="px-4 py-3 text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 dark:divide-slate-700">
                            <tr
                                v-for="att in attendances.data"
                                :key="att.id"
                                class="hover:bg-slate-50/80 dark:hover:bg-slate-900/40 transition"
                            >
                                <td class="px-4 py-3 font-medium text-slate-900 dark:text-white">
                                    {{ att.employee?.user?.name || 'Karyawan' }}
                                    <span class="block text-[10px] text-slate-400 font-mono">{{ att.employee?.nik }}</span>
                                </td>
                                <td class="px-4 py-3">
                                    {{ att.employee?.department?.name || '-' }}
                                </td>
                                <td class="px-4 py-3">
                                    <span class="font-medium text-slate-800 dark:text-slate-200">
                                        {{ att.shift?.name || 'Reguler Non-Shift' }}
                                    </span>
                                    <span class="block text-[10px] text-slate-400">
                                        {{ att.shift ? `${att.shift.start_time.substring(0,5)} - ${att.shift.end_time.substring(0,5)}` : '08:00 - 17:00' }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 font-mono">
                                    <span class="text-emerald-600 font-semibold">
                                        {{ att.check_in_time ? att.check_in_time.substring(11, 16) : '-' }}
                                    </span>
                                    <span v-if="att.is_late" class="ml-1 text-[10px] px-1.5 py-0.5 rounded bg-amber-100 text-amber-700 font-bold">
                                        +{{ att.late_minutes }} menit
                                    </span>
                                </td>
                                <td class="px-4 py-3 font-mono">
                                    <span class="text-blue-600 font-semibold">
                                        {{ att.check_out_time ? att.check_out_time.substring(11, 16) : '-' }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 font-mono">
                                    {{ att.work_hours ? `${att.work_hours} jam` : '-' }}
                                </td>
                                <td class="px-4 py-3">
                                    <span v-if="att.distance_meters !== null" class="inline-flex items-center gap-1 font-mono text-[11px]">
                                        <MapPin class="w-3 h-3 text-blue-500" />
                                        {{ att.distance_meters }}m
                                        <span class="text-[10px] text-slate-400">({{ att.office_location?.name || 'Sukoharjo' }})</span>
                                    </span>
                                    <span v-else class="text-slate-400">-</span>
                                </td>
                                <td class="px-4 py-3">
                                    <span
                                        :class="[
                                            'px-2 py-0.5 rounded text-[10px] font-semibold',
                                            att.status === 'present' ? 'bg-emerald-50 text-emerald-700 dark:bg-emerald-950 dark:text-emerald-300' :
                                            att.status === 'late' ? 'bg-amber-50 text-amber-700 dark:bg-amber-950 dark:text-amber-300' :
                                            att.status === 'leave' ? 'bg-blue-50 text-blue-700 dark:bg-blue-950 dark:text-blue-300' :
                                            'bg-rose-50 text-rose-700 dark:bg-rose-950 dark:text-rose-300'
                                        ]"
                                    >
                                        {{ att.status.toUpperCase() }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-right">
                                    <button
                                        v-if="!att.check_out_time"
                                        @click="submitCheckOut(att.id)"
                                        class="inline-flex items-center gap-1 px-2.5 py-1 text-xs font-medium rounded-lg bg-blue-50 text-blue-700 hover:bg-blue-100 transition"
                                        title="Lakukan Check-Out"
                                    >
                                        <LogOut class="w-3.5 h-3.5" />
                                        Check-Out
                                    </button>
                                    <span v-else class="text-[11px] text-emerald-600 font-medium">Tuntas</span>
                                </td>
                            </tr>
                            <tr v-if="attendances.data.length === 0">
                                <td colspan="9" class="text-center py-10 text-slate-400">
                                    Tidak ada data presensi pada tanggal {{ selectedDate }}.
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </ModuleLayout>
</template>
