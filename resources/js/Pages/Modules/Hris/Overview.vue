<script setup>
import { ref } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import ModuleLayout from '@/Layouts/ModuleLayout.vue';
import KpiStatCard from '@/Components/Dashboard/KpiStatCard.vue';
import InteractiveChartCard from '@/Components/Dashboard/InteractiveChartCard.vue';
import {
    Users,
    CalendarCheck,
    CalendarOff,
    Clock,
    Timer,
    Lightbulb,
    Banknote,
    Target,
    Award,
    CheckCircle2,
    XCircle,
    ArrowUpRight,
    Building2,
    MapPin,
    AlertCircle,
    UserCheck,
    UserPlus,
} from 'lucide-vue-next';

const props = defineProps({
    metrics: {
        type: Object,
        required: true,
    },
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

const formatCurrency = (val) => {
    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        maximumFractionDigits: 0,
    }).format(val || 0);
};

const quickActions = [
    { title: 'Master Karyawan', desc: 'Kelola NIK, golongan, shift & kontrak', href: '/hris/employees', icon: Users, color: 'bg-blue-50 text-blue-700 hover:border-blue-300' },
    { title: 'Presensi & Geofence', desc: 'Simulasi absen koordinat Haversine', href: '/hris/attendance', icon: CalendarCheck, color: 'bg-emerald-50 text-emerald-700 hover:border-emerald-300' },
    { title: 'Pengajuan Cuti', desc: 'Input izin & cuti tahunan terpotong', href: '/hris/leaves/create', icon: CalendarOff, color: 'bg-teal-50 text-teal-700 hover:border-teal-300' },
    { title: 'Buat SPL Lembur', desc: 'Kalkulasi Depnaker 1.5x/2x/3x/4x & Time Bank', href: '/hris/overtime/create', icon: Timer, color: 'bg-amber-50 text-amber-700 hover:border-amber-300' },
    { title: 'Kaizen Kizuki', desc: 'Usulan perbaikan QCDSM & reward point', href: '/hris/kizuki/create', icon: Lightbulb, color: 'bg-purple-50 text-purple-700 hover:border-purple-300' },
    { title: 'Payroll PPh 21 TER', desc: 'Hitung gaji neto, BPJS TK/Kes & TER', href: '/hris/payroll', icon: Banknote, color: 'bg-indigo-50 text-indigo-700 hover:border-indigo-300' },
    { title: 'Strategic BSC L3/L4', desc: 'Sasaran mutu departemen & program kerja', href: '/hris/strategic', icon: Target, color: 'bg-rose-50 text-rose-700 hover:border-rose-300' },
];

const approveLeave = (leaveId, action) => {
    router.post(`/hris/leaves/${leaveId}/approve`, {
        action: action,
    }, {
        preserveScroll: true,
    });
};

const approveOvertime = (overtimeId, action) => {
    router.post(`/hris/overtime/${overtimeId}/approve`, {
        action: action,
    }, {
        preserveScroll: true,
    });
};
</script>

<template>
    <Head title="HRISNOW — Human Resources Management Overview" />

    <ModuleLayout
        module-slug="hris"
        module-name="HRISNOW"
        color-theme="blue"
        :menus="hrisMenus"
    >
        <div class="space-y-6">
            <!-- Header Banner -->
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 bg-white dark:bg-slate-800 p-6 rounded-2xl shadow-xs border border-slate-200 dark:border-slate-700">
                <div class="flex items-center gap-3">
                    <div class="p-3 bg-blue-500/10 text-blue-600 dark:text-blue-400 rounded-xl">
                        <Users class="w-8 h-8" />
                    </div>
                    <div>
                        <h1 class="text-2xl font-bold text-slate-900 dark:text-white tracking-tight flex items-center gap-2">
                            HRISNOW — Human Resource Information System
                        </h1>
                        <p class="text-sm text-slate-500 dark:text-slate-400 mt-0.5">
                            PT. Herbatech Innopharma Industry • Multi-Plant Sukoharjo & Boyolali • Standar CPOTB & Depnaker RI
                        </p>
                    </div>
                </div>

                <div class="flex items-center gap-2">
                    <Link
                        href="/hris/employees/create"
                        class="inline-flex items-center gap-2 px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-xl text-sm transition shadow-sm"
                    >
                        <UserPlus class="w-4 h-4" />
                        Tambah Karyawan Baru
                    </Link>
                </div>
            </div>

            <!-- KPI Cards Grid -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-6 gap-4">
                <KpiStatCard
                    title="Total Karyawan"
                    :value="metrics.kpis.total_headcount"
                    trend="Aktif di Roster"
                    trend-type="neutral"
                    status="info"
                    subtitle="Seluruh Departemen"
                    icon="Users"
                />

                <KpiStatCard
                    title="Kehadiran Hari Ini"
                    :value="metrics.kpis.attendance_rate + '%'"
                    trend="Realtime Geofence"
                    trend-type="positive"
                    status="success"
                    subtitle="Check-in valid"
                    icon="CalendarCheck"
                />

                <KpiStatCard
                    title="Pending Cuti"
                    :value="metrics.kpis.pending_leaves_count"
                    trend="Menunggu Approval"
                    trend-type="warning"
                    status="warning"
                    subtitle="SPV & HRD Queue"
                    icon="CalendarOff"
                />

                <KpiStatCard
                    title="Pending SPL Lembur"
                    :value="metrics.kpis.pending_overtime_count"
                    trend="Menunggu Approval"
                    trend-type="warning"
                    status="warning"
                    subtitle="SPV / OM / GM"
                    icon="Timer"
                />

                <KpiStatCard
                    title="Kaizen Kizuki"
                    :value="metrics.kpis.pending_kizuki_count"
                    trend="Ide Baru Diajukan"
                    trend-type="positive"
                    status="info"
                    subtitle="Komite Penilai"
                    icon="Lightbulb"
                />

                <KpiStatCard
                    title="Estimasi Payroll"
                    :value="formatCurrency(metrics.kpis.monthly_payroll_est)"
                    trend="Bulan Berjalan"
                    trend-type="neutral"
                    status="info"
                    subtitle="Netto Karyawan"
                    icon="Banknote"
                />
            </div>

            <!-- Quick Action Shortcuts -->
            <div>
                <h2 class="text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400 mb-3">
                    Aksi Cepat & Navigasi Operasional HRIS
                </h2>
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 xl:grid-cols-7 gap-3">
                    <Link
                        v-for="act in quickActions"
                        :key="act.title"
                        :href="act.href"
                        class="p-4 bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 hover:shadow-md transition-all group flex flex-col justify-between"
                    >
                        <div class="flex items-center justify-between mb-3">
                            <div :class="['p-2.5 rounded-lg border border-transparent transition-colors', act.color]">
                                <component :is="act.icon" class="w-5 h-5" />
                            </div>
                            <ArrowUpRight class="w-4 h-4 text-slate-400 group-hover:text-blue-600 transition-colors" />
                        </div>
                        <div>
                            <div class="font-semibold text-sm text-slate-800 dark:text-slate-100 group-hover:text-blue-600 dark:group-hover:text-blue-400">
                                {{ act.title }}
                            </div>
                            <div class="text-xs text-slate-500 dark:text-slate-400 mt-0.5 line-clamp-1">
                                {{ act.desc }}
                            </div>
                        </div>
                    </Link>
                </div>
            </div>

            <!-- Charts Section -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <!-- Headcount by Department -->
                <div class="bg-white dark:bg-slate-800 p-5 rounded-2xl border border-slate-200 dark:border-slate-700 shadow-xs">
                    <div class="flex items-center justify-between mb-4">
                        <div>
                            <h3 class="font-bold text-slate-900 dark:text-white text-base">Komposisi Karyawan per Departemen</h3>
                            <p class="text-xs text-slate-500 dark:text-slate-400">Distribusi Headcount Lintas Unit Kerja</p>
                        </div>
                        <span class="px-2.5 py-1 text-xs font-semibold rounded-full bg-blue-50 text-blue-700 dark:bg-blue-900/30 dark:text-blue-300">
                            Aktif: {{ metrics.kpis.total_headcount }} Orang
                        </span>
                    </div>
                    <InteractiveChartCard
                        title="Headcount"
                        type="donut"
                        :height="300"
                        :series="metrics.charts.headcount_by_department.series"
                        :options="{
                            labels: metrics.charts.headcount_by_department.labels,
                            legend: { position: 'bottom' },
                            colors: ['#3b82f6', '#10b981', '#f59e0b', '#8b5cf6', '#ec4899', '#06b6d4', '#64748b'],
                        }"
                    />
                </div>

                <!-- Attendance Distribution Today -->
                <div class="bg-white dark:bg-slate-800 p-5 rounded-2xl border border-slate-200 dark:border-slate-700 shadow-xs">
                    <div class="flex items-center justify-between mb-4">
                        <div>
                            <h3 class="font-bold text-slate-900 dark:text-white text-base">Distribusi Presensi Hari Ini</h3>
                            <p class="text-xs text-slate-500 dark:text-slate-400">Status Kehadiran Realtime Biometrik & Geofence</p>
                        </div>
                        <span class="px-2.5 py-1 text-xs font-semibold rounded-full bg-emerald-50 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-300">
                            {{ metrics.kpis.attendance_rate }}% Hadir
                        </span>
                    </div>
                    <InteractiveChartCard
                        title="Distribusi Presensi"
                        type="donut"
                        :height="300"
                        :series="metrics.charts.attendance_distribution.series"
                        :options="{
                            labels: metrics.charts.attendance_distribution.labels,
                            legend: { position: 'bottom' },
                            colors: ['#10b981', '#f59e0b', '#3b82f6', '#ef4444'],
                        }"
                    />
                </div>

                <!-- Overtime Trend 6 Months -->
                <div class="bg-white dark:bg-slate-800 p-5 rounded-2xl border border-slate-200 dark:border-slate-700 shadow-xs">
                    <div class="flex items-center justify-between mb-4">
                        <div>
                            <h3 class="font-bold text-slate-900 dark:text-white text-base">Tren Jam Lembur (6 Bulan Terakhir)</h3>
                            <p class="text-xs text-slate-500 dark:text-slate-400">Total Akumulasi Jam SPL Disetujui (Jam Kerja Fisik)</p>
                        </div>
                        <span class="px-2.5 py-1 text-xs font-semibold rounded-full bg-amber-50 text-amber-700 dark:bg-amber-900/30 dark:text-amber-300">
                            Depnaker Multiplier
                        </span>
                    </div>
                    <InteractiveChartCard
                        title="Jam Lembur"
                        type="bar"
                        :height="300"
                        :series="[{ name: 'Total Jam Lembur', data: metrics.charts.overtime_trend.series }]"
                        :options="{
                            xaxis: { categories: metrics.charts.overtime_trend.months },
                            colors: ['#f59e0b'],
                            plotOptions: { bar: { borderRadius: 6, columnWidth: '45%' } },
                        }"
                    />
                </div>

                <!-- Kizuki Kaizen by Category -->
                <div class="bg-white dark:bg-slate-800 p-5 rounded-2xl border border-slate-200 dark:border-slate-700 shadow-xs">
                    <div class="flex items-center justify-between mb-4">
                        <div>
                            <h3 class="font-bold text-slate-900 dark:text-white text-base">Ide Kaizen Kizuki per Kategori</h3>
                            <p class="text-xs text-slate-500 dark:text-slate-400">Safety, 5S, Mutu CPOTB, Biaya, dan Produktivitas</p>
                        </div>
                        <span class="px-2.5 py-1 text-xs font-semibold rounded-full bg-purple-50 text-purple-700 dark:bg-purple-900/30 dark:text-purple-300">
                            Budaya Kaizen
                        </span>
                    </div>
                    <InteractiveChartCard
                        title="Kaizen Kizuki"
                        type="bar"
                        :height="300"
                        :series="[{ name: 'Jumlah Ide', data: metrics.charts.kizuki_by_category.series }]"
                        :options="{
                            xaxis: { categories: metrics.charts.kizuki_by_category.labels },
                            colors: ['#8b5cf6'],
                            plotOptions: { bar: { borderRadius: 6, horizontal: true } },
                        }"
                    />
                </div>
            </div>

            <!-- Pending Approvals & Live Attendance Feed -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <!-- Pending Leaves Queue -->
                <div class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-200 dark:border-slate-700 shadow-xs p-5">
                    <div class="flex items-center justify-between pb-3 mb-4 border-b border-slate-100 dark:border-slate-700">
                        <div class="flex items-center gap-2">
                            <CalendarOff class="w-5 h-5 text-teal-600" />
                            <h3 class="font-bold text-slate-900 dark:text-white text-sm">
                                Antrean Approval Cuti & Izin
                            </h3>
                        </div>
                        <Link href="/hris/leaves" class="text-xs text-blue-600 hover:underline">
                            Lihat Semua
                        </Link>
                    </div>

                    <div v-if="metrics.pending_leaves && metrics.pending_leaves.length > 0" class="divide-y divide-slate-100 dark:divide-slate-700">
                        <div
                            v-for="leave in metrics.pending_leaves"
                            :key="leave.id"
                            class="py-3 flex items-center justify-between gap-4"
                        >
                            <div>
                                <div class="font-semibold text-sm text-slate-900 dark:text-white flex items-center gap-2">
                                    {{ leave.employee?.user?.name || 'Karyawan' }}
                                    <span class="text-xs font-normal text-slate-400">({{ leave.leave_number }})</span>
                                </div>
                                <div class="text-xs text-slate-500 mt-0.5">
                                    {{ leave.category?.name }} • {{ leave.total_days }} Hari ({{ leave.start_date }} s/d {{ leave.end_date }})
                                </div>
                                <div class="text-xs text-slate-400 italic mt-0.5">
                                    "{{ leave.reason }}"
                                </div>
                            </div>
                            <div class="flex items-center gap-1.5 shrink-0">
                                <button
                                    v-if="leave.status === 'pending_spv'"
                                    @click="approveLeave(leave.id, 'approve_spv')"
                                    class="px-2.5 py-1 text-xs font-medium bg-emerald-50 text-emerald-700 hover:bg-emerald-100 dark:bg-emerald-950 dark:text-emerald-300 rounded-lg transition"
                                >
                                    Approve SPV
                                </button>
                                <button
                                    v-else-if="leave.status === 'pending_hr'"
                                    @click="approveLeave(leave.id, 'approve_hr')"
                                    class="px-2.5 py-1 text-xs font-medium bg-blue-50 text-blue-700 hover:bg-blue-100 dark:bg-blue-950 dark:text-blue-300 rounded-lg transition"
                                >
                                    Approve HR
                                </button>
                                <button
                                    @click="approveLeave(leave.id, 'reject')"
                                    class="px-2.5 py-1 text-xs font-medium bg-rose-50 text-rose-700 hover:bg-rose-100 dark:bg-rose-950 dark:text-rose-300 rounded-lg transition"
                                >
                                    Tolak
                                </button>
                            </div>
                        </div>
                    </div>
                    <div v-else class="text-center py-8 text-xs text-slate-400">
                        Tidak ada antrean approval cuti saat ini.
                    </div>
                </div>

                <!-- Pending Overtime Queue -->
                <div class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-200 dark:border-slate-700 shadow-xs p-5">
                    <div class="flex items-center justify-between pb-3 mb-4 border-b border-slate-100 dark:border-slate-700">
                        <div class="flex items-center gap-2">
                            <Timer class="w-5 h-5 text-amber-600" />
                            <h3 class="font-bold text-slate-900 dark:text-white text-sm">
                                Antrean Approval SPL Lembur
                            </h3>
                        </div>
                        <Link href="/hris/overtime" class="text-xs text-blue-600 hover:underline">
                            Lihat Semua
                        </Link>
                    </div>

                    <div v-if="metrics.pending_overtimes && metrics.pending_overtimes.length > 0" class="divide-y divide-slate-100 dark:divide-slate-700">
                        <div
                            v-for="ot in metrics.pending_overtimes"
                            :key="ot.id"
                            class="py-3 flex items-center justify-between gap-4"
                        >
                            <div>
                                <div class="font-semibold text-sm text-slate-900 dark:text-white flex items-center gap-2">
                                    {{ ot.employee?.user?.name || 'Karyawan' }}
                                    <span class="text-xs font-normal text-slate-400">({{ ot.spl_number }})</span>
                                </div>
                                <div class="text-xs text-slate-500 mt-0.5">
                                    {{ ot.date }} • {{ ot.start_time }} - {{ ot.end_time }} ({{ ot.total_hours }} Jam Fisik / {{ ot.multiplier_hours }} Jam Bayar)
                                </div>
                                <div class="text-xs text-amber-600 font-medium mt-0.5">
                                    Target: {{ ot.work_target || ot.reason }}
                                </div>
                            </div>
                            <div class="flex items-center gap-1.5 shrink-0">
                                <button
                                    v-if="ot.status === 'pending_spv'"
                                    @click="approveOvertime(ot.id, 'approve_spv')"
                                    class="px-2.5 py-1 text-xs font-medium bg-emerald-50 text-emerald-700 hover:bg-emerald-100 dark:bg-emerald-950 dark:text-emerald-300 rounded-lg transition"
                                >
                                    Approve SPV
                                </button>
                                <button
                                    v-else-if="ot.status === 'pending_om'"
                                    @click="approveOvertime(ot.id, 'approve_om')"
                                    class="px-2.5 py-1 text-xs font-medium bg-blue-50 text-blue-700 hover:bg-blue-100 dark:bg-blue-950 dark:text-blue-300 rounded-lg transition"
                                >
                                    Approve OM
                                </button>
                                <button
                                    @click="approveOvertime(ot.id, 'reject')"
                                    class="px-2.5 py-1 text-xs font-medium bg-rose-50 text-rose-700 hover:bg-rose-100 dark:bg-rose-950 dark:text-rose-300 rounded-lg transition"
                                >
                                    Tolak
                                </button>
                            </div>
                        </div>
                    </div>
                    <div v-else class="text-center py-8 text-xs text-slate-400">
                        Tidak ada antrean approval SPL lembur saat ini.
                    </div>
                </div>
            </div>

            <!-- Recent Attendance Feed -->
            <div class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-200 dark:border-slate-700 shadow-xs p-5">
                <div class="flex items-center justify-between pb-3 mb-4 border-b border-slate-100 dark:border-slate-700">
                    <div class="flex items-center gap-2">
                        <Clock class="w-5 h-5 text-blue-600" />
                        <h3 class="font-bold text-slate-900 dark:text-white text-sm">
                            Presensi Masuk & Keluar Terkini (Live Feed Geofence)
                        </h3>
                    </div>
                    <Link href="/hris/attendance" class="text-xs text-blue-600 hover:underline">
                        Buka Log Presensi Lengkap
                    </Link>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-xs text-left text-slate-600 dark:text-slate-300">
                        <thead class="text-[11px] uppercase bg-slate-50 dark:bg-slate-900/50 text-slate-400">
                            <tr>
                                <th class="px-3 py-2">Karyawan</th>
                                <th class="px-3 py-2">Tanggal / Shift</th>
                                <th class="px-3 py-2">Check In</th>
                                <th class="px-3 py-2">Check Out</th>
                                <th class="px-3 py-2">Jarak Geofence</th>
                                <th class="px-3 py-2">Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 dark:divide-slate-700">
                            <tr v-for="att in metrics.recent_attendances" :key="att.id" class="hover:bg-slate-50 dark:hover:bg-slate-900/30">
                                <td class="px-3 py-2.5 font-medium text-slate-900 dark:text-white">
                                    {{ att.employee?.user?.name || 'Karyawan' }}
                                    <span class="block text-[10px] text-slate-400">{{ att.employee?.nik }}</span>
                                </td>
                                <td class="px-3 py-2.5">
                                    {{ att.date }}
                                    <span class="block text-[10px] text-slate-400">{{ att.shift?.name || 'Reguler' }}</span>
                                </td>
                                <td class="px-3 py-2.5 font-mono text-emerald-600 font-medium">
                                    {{ att.check_in_time ? att.check_in_time.substring(11, 16) : '-' }}
                                    <span v-if="att.is_late" class="ml-1 text-[10px] px-1.5 py-0.2 rounded bg-amber-100 text-amber-700">
                                        +{{ att.late_minutes }}m
                                    </span>
                                </td>
                                <td class="px-3 py-2.5 font-mono text-blue-600 font-medium">
                                    {{ att.check_out_time ? att.check_out_time.substring(11, 16) : '-' }}
                                </td>
                                <td class="px-3 py-2.5">
                                    <span v-if="att.distance_meters !== null" class="inline-flex items-center gap-1 font-mono">
                                        <MapPin class="w-3 h-3 text-slate-400" />
                                        {{ att.distance_meters }}m
                                    </span>
                                    <span v-else class="text-slate-400">-</span>
                                </td>
                                <td class="px-3 py-2.5">
                                    <span
                                        :class="[
                                            'px-2 py-0.5 rounded text-[10px] font-semibold',
                                            att.status === 'present' ? 'bg-emerald-50 text-emerald-700' :
                                            att.status === 'late' ? 'bg-amber-50 text-amber-700' :
                                            att.status === 'leave' ? 'bg-blue-50 text-blue-700' : 'bg-rose-50 text-rose-700'
                                        ]"
                                    >
                                        {{ att.status.toUpperCase() }}
                                    </span>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </ModuleLayout>
</template>
