<script setup>
import { ref, watch } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import ModuleLayout from '@/Layouts/ModuleLayout.vue';
import {
    Users,
    UserPlus,
    Search,
    Filter,
    ChevronRight,
    Building2,
    Eye,
    Edit,
    CheckCircle2,
    XCircle,
    BadgePercent,
    Briefcase,
} from 'lucide-vue-next';

const props = defineProps({
    employees: Object,
    filters: Object,
    departments: Array,
    plants: Array,
    golongans: Array,
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

const search = ref(props.filters.search || '');
const departmentId = ref(props.filters.department_id || '');
const status = ref(props.filters.status || '');
const employmentStatus = ref(props.filters.employment_status || '');

const applyFilters = () => {
    router.get('/hris/employees', {
        search: search.value || undefined,
        department_id: departmentId.value || undefined,
        status: status.value || undefined,
        employment_status: employmentStatus.value || undefined,
    }, {
        preserveState: true,
        preserveScroll: true,
    });
};

const formatCurrency = (val) => {
    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        maximumFractionDigits: 0,
    }).format(val || 0);
};
</script>

<template>
    <Head title="Master Karyawan — HRISNOW" />

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
                    <div class="p-3 bg-blue-500/10 text-blue-600 dark:text-blue-400 rounded-xl">
                        <Users class="w-8 h-8" />
                    </div>
                    <div>
                        <h1 class="text-2xl font-bold text-slate-900 dark:text-white tracking-tight">
                            Master Karyawan (Roster PT. Herbatech)
                        </h1>
                        <p class="text-sm text-slate-500 dark:text-slate-400 mt-0.5">
                            Data induk kepegawaian, struktur golongan, penempatan plant, shift, dan saldo cuti.
                        </p>
                    </div>
                </div>

                <Link
                    href="/hris/employees/create"
                    class="inline-flex items-center gap-2 px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-xl text-sm transition shadow-sm"
                >
                    <UserPlus class="w-4 h-4" />
                    Tambah Karyawan Baru
                </Link>
            </div>

            <!-- Filters Bar -->
            <div class="bg-white dark:bg-slate-800 p-4 rounded-2xl border border-slate-200 dark:border-slate-700 shadow-xs flex flex-col md:flex-row items-center gap-4">
                <div class="relative flex-1 w-full">
                    <Search class="w-4 h-4 absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400" />
                    <input
                        v-model="search"
                        @keyup.enter="applyFilters"
                        type="text"
                        placeholder="Cari NIK, nama karyawan, email, atau jabatan..."
                        class="w-full pl-10 pr-4 py-2 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl text-sm focus:ring-2 focus:ring-blue-500 outline-none text-slate-900 dark:text-white"
                    />
                </div>

                <div class="flex flex-wrap items-center gap-3 w-full md:w-auto">
                    <select
                        v-model="departmentId"
                        @change="applyFilters"
                        class="px-3 py-2 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl text-xs text-slate-700 dark:text-slate-300 outline-none"
                    >
                        <option value="">Semua Departemen</option>
                        <option v-for="dept in departments" :key="dept.id" :value="dept.id">
                            {{ dept.name }}
                        </option>
                    </select>

                    <select
                        v-model="employmentStatus"
                        @change="applyFilters"
                        class="px-3 py-2 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl text-xs text-slate-700 dark:text-slate-300 outline-none"
                    >
                        <option value="">Semua Status Kerja</option>
                        <option value="permanent">Tetap (Permanent)</option>
                        <option value="contract">Kontrak (PKWT)</option>
                        <option value="probation">Probation</option>
                        <option value="internship">Magang / PKL</option>
                    </select>

                    <select
                        v-model="status"
                        @change="applyFilters"
                        class="px-3 py-2 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl text-xs text-slate-700 dark:text-slate-300 outline-none"
                    >
                        <option value="">Semua Status Aktif</option>
                        <option value="active">Aktif</option>
                        <option value="inactive">Nonaktif</option>
                        <option value="resigned">Resign</option>
                    </select>
                </div>
            </div>

            <!-- Employee Table -->
            <div class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-200 dark:border-slate-700 shadow-xs overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-xs text-left text-slate-600 dark:text-slate-300">
                        <thead class="text-[11px] uppercase bg-slate-50 dark:bg-slate-900/50 text-slate-400 border-b border-slate-200 dark:border-slate-700">
                            <tr>
                                <th class="px-4 py-3">Karyawan</th>
                                <th class="px-4 py-3">Departemen & Plant</th>
                                <th class="px-4 py-3">Jabatan & Golongan</th>
                                <th class="px-4 py-3">Status Kontrak</th>
                                <th class="px-4 py-3">Gaji Pokok</th>
                                <th class="px-4 py-3">Sisa Cuti</th>
                                <th class="px-4 py-3">Status</th>
                                <th class="px-4 py-3 text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 dark:divide-slate-700">
                            <tr
                                v-for="emp in employees.data"
                                :key="emp.id"
                                class="hover:bg-slate-50/80 dark:hover:bg-slate-900/40 transition"
                            >
                                <td class="px-4 py-3.5">
                                    <div class="flex items-center gap-3">
                                        <div class="w-9 h-9 rounded-full bg-blue-100 dark:bg-blue-900/40 text-blue-600 dark:text-blue-400 flex items-center justify-center font-bold text-sm shrink-0">
                                            {{ emp.user?.name ? emp.user.name.charAt(0) : 'E' }}
                                        </div>
                                        <div>
                                            <div class="font-semibold text-slate-900 dark:text-white">
                                                {{ emp.user?.name || 'Unnamed' }}
                                            </div>
                                            <div class="text-[11px] text-slate-400 font-mono">
                                                {{ emp.nik }} • {{ emp.user?.email }}
                                            </div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-4 py-3.5">
                                    <div class="font-medium text-slate-800 dark:text-slate-200">
                                        {{ emp.department?.name || '-' }}
                                    </div>
                                    <div class="text-[11px] text-slate-400">
                                        {{ emp.plant?.name || 'Sukoharjo Plant' }}
                                    </div>
                                </td>
                                <td class="px-4 py-3.5">
                                    <div class="font-medium text-slate-800 dark:text-slate-200">
                                        {{ emp.position }}
                                    </div>
                                    <div class="text-[11px] text-blue-600 dark:text-blue-400 font-semibold">
                                        Gol. {{ emp.golongan?.code || 'G-1' }} ({{ emp.golongan?.name }})
                                    </div>
                                </td>
                                <td class="px-4 py-3.5">
                                    <span class="px-2 py-0.5 rounded text-[10px] font-semibold bg-slate-100 text-slate-700 dark:bg-slate-700 dark:text-slate-300">
                                        {{ emp.employment_status.toUpperCase() }}
                                    </span>
                                </td>
                                <td class="px-4 py-3.5 font-mono font-medium text-slate-900 dark:text-white">
                                    {{ formatCurrency(emp.basic_salary) }}
                                </td>
                                <td class="px-4 py-3.5">
                                    <span class="inline-flex items-center gap-1 font-semibold text-teal-600">
                                        {{ emp.leave_balance }} Hari
                                    </span>
                                </td>
                                <td class="px-4 py-3.5">
                                    <span
                                        :class="[
                                            'px-2 py-0.5 rounded-full text-[10px] font-semibold',
                                            emp.status === 'active'
                                                ? 'bg-emerald-50 text-emerald-700 dark:bg-emerald-950 dark:text-emerald-300'
                                                : 'bg-rose-50 text-rose-700 dark:bg-rose-950 dark:text-rose-300',
                                        ]"
                                    >
                                        {{ emp.status === 'active' ? 'Aktif' : 'Nonaktif' }}
                                    </span>
                                </td>
                                <td class="px-4 py-3.5 text-right">
                                    <div class="flex items-center justify-end gap-1.5">
                                        <Link
                                            :href="`/hris/employees/${emp.id}`"
                                            class="p-1.5 rounded-lg text-slate-500 hover:text-blue-600 hover:bg-blue-50 dark:hover:bg-slate-700 transition"
                                            title="Lihat Profil Detail"
                                        >
                                            <Eye class="w-4 h-4" />
                                        </Link>
                                        <Link
                                            :href="`/hris/employees/${emp.id}/edit`"
                                            class="p-1.5 rounded-lg text-slate-500 hover:text-amber-600 hover:bg-amber-50 dark:hover:bg-slate-700 transition"
                                            title="Edit Profil Karyawan"
                                        >
                                            <Edit class="w-4 h-4" />
                                        </Link>
                                    </div>
                                </td>
                            </tr>
                            <tr v-if="employees.data.length === 0">
                                <td colspan="8" class="text-center py-10 text-slate-400">
                                    Tidak ada data karyawan yang sesuai dengan kriteria pencarian.
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div v-if="employees.links && employees.links.length > 3" class="p-4 border-t border-slate-200 dark:border-slate-700 flex items-center justify-between">
                    <span class="text-xs text-slate-500">
                        Menampilkan {{ employees.from || 0 }} - {{ employees.to || 0 }} dari {{ employees.total }} data
                    </span>
                    <div class="flex items-center gap-1">
                        <Link
                            v-for="(link, i) in employees.links"
                            :key="i"
                            :href="link.url || '#'"
                            :class="[
                                'px-3 py-1 text-xs rounded-lg transition',
                                link.active
                                    ? 'bg-blue-600 text-white font-bold'
                                    : 'text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-700',
                                !link.url ? 'opacity-40 pointer-events-none' : '',
                            ]"
                            v-html="link.label"
                        />
                    </div>
                </div>
            </div>
        </div>
    </ModuleLayout>
</template>
