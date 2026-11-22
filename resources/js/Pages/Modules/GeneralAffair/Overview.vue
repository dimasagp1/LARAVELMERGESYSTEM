<script setup>
import { ref } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import ModuleLayout from '@/Layouts/ModuleLayout.vue';
import KpiStatCard from '@/Components/Dashboard/KpiStatCard.vue';
import InteractiveChartCard from '@/Components/Dashboard/InteractiveChartCard.vue';
import ApprovalInboxWidget from '@/Components/Dashboard/ApprovalInboxWidget.vue';
import ActivityFeedWidget from '@/Components/Dashboard/ActivityFeedWidget.vue';
import {
  Wrench,
  Package,
  Building2,
  ShieldCheck,
  AlertTriangle,
  PlusCircle,
  FileText,
  Scan,
  MapPin,
  ClipboardList,
  ChevronRight,
  TrendingUp,
} from 'lucide-vue-next';

const props = defineProps({
  module: {
    type: Object,
    required: true,
  },
  kpis: {
    type: Object,
    default: () => ({
      open_work_orders: 0,
      pending_atk_requests: 0,
      total_asset_value: 'Rp 0',
      security_anomalies: 0,
    }),
  },
  woStatusCounts: {
    type: Object,
    default: () => ({}),
  },
  woPriorityCounts: {
    type: Object,
    default: () => ({}),
  },
  assetsByPlant: {
    type: Array,
    default: () => [],
  },
  patrolSummary: {
    type: Object,
    default: () => ({
      security: { compliance_percent: 100, anomalies_count: 0 },
      cleaning: { compliance_percent: 100, sanitized_rate: 100 },
    }),
  },
  pendingApprovals: {
    type: Array,
    default: () => [],
  },
  recentActivities: {
    type: Array,
    default: () => [],
  },
});

// Chart 1: Status Work Order
const woStatusKeys = Object.keys(props.woStatusCounts);
const woStatusValues = Object.values(props.woStatusCounts);

const woStatusChartOptions = ref({
  chart: { type: 'donut', fontFamily: 'inherit' },
  labels: woStatusKeys.length ? woStatusKeys : ['Menunggu Penugasan', 'Dalam Pengerjaan', 'Selesai'],
  colors: ['#f59e0b', '#3b82f6', '#10b981', '#ef4444', '#8b5cf6'],
  legend: { position: 'bottom' },
  dataLabels: { enabled: true },
  stroke: { width: 0 },
});
const woStatusChartSeries = ref(woStatusValues.length ? woStatusValues : [3, 4, 8]);

// Chart 2: Nilai Buku Aset per Plant
const plantNames = props.assetsByPlant.map((p) => p.plant_name);
const plantValues = props.assetsByPlant.map((p) => Math.round(p.total_book_value / 1000000)); // Dalam Juta Rp

const assetPlantChartOptions = ref({
  chart: { type: 'bar', fontFamily: 'inherit', toolbar: { show: false } },
  xaxis: { categories: plantNames.length ? plantNames : ['Plant Sukoharjo', 'Plant Boyolali'] },
  yaxis: { title: { text: 'Juta Rupiah (Rp)' } },
  colors: ['#d97706'],
  plotOptions: { bar: { borderRadius: 4, columnWidth: '45%' } },
  dataLabels: { enabled: false },
});
const assetPlantChartSeries = ref([
  {
    name: 'Total Nilai Buku',
    data: plantValues.length ? plantValues : [4200, 3100],
  },
]);
</script>

<template>
  <Head title="General Affair (FPUGA) - Dashboard" />

  <ModuleLayout :module="module">
    <div class="space-y-6">
      <!-- Top Banner / Title -->
      <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 bg-gradient-to-r from-amber-600 to-amber-700 rounded-xl p-6 text-white shadow-lg">
        <div>
          <h1 class="text-2xl font-bold tracking-tight">Fasilitas, Pemeliharaan & Umum (FPUGA)</h1>
          <p class="text-amber-100 text-sm mt-1 max-w-2xl">
            Pusat manajemen terpadu perawatan sarana pabrik (WO Form A-D), pengadaan material FPU, logistik ATK/RT, inventaris aset tetap multi-plant, dan patroli keamanan QR.
          </p>
        </div>
        <div class="flex flex-wrap items-center gap-2">
          <Link
            href="/general-affair/work-orders/create"
            class="inline-flex items-center gap-2 px-4 py-2 bg-white text-amber-700 font-semibold rounded-lg text-sm hover:bg-amber-50 shadow-sm transition"
          >
            <PlusCircle class="w-4 h-4" />
            Buat Work Order
          </Link>
          <Link
            href="/general-affair/atk/create"
            class="inline-flex items-center gap-2 px-4 py-2 bg-amber-800 text-white font-medium rounded-lg text-sm hover:bg-amber-900 transition"
          >
            <Package class="w-4 h-4" />
            Permintaan ATK
          </Link>
        </div>
      </div>

      <!-- KPI Stat Cards -->
      <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <KpiStatCard
          title="Work Order Aktif"
          :value="kpis.open_work_orders"
          subtitle="Form A-C dalam proses"
          :icon="Wrench"
          color="amber"
          badge-text="Operasional"
        />

        <KpiStatCard
          title="Permintaan ATK / RT"
          :value="kpis.pending_atk_requests"
          subtitle="Menunggu persetujuan / verifikasi"
          :icon="Package"
          color="blue"
          badge-text="Logistik"
        />

        <KpiStatCard
          title="Total Nilai Buku Aset"
          :value="kpis.total_asset_value"
          subtitle="Depresiasi garis lurus aktif"
          :icon="Building2"
          color="emerald"
          badge-text="Multi-Plant"
        />

        <KpiStatCard
          title="Anomali Keamanan"
          :value="kpis.security_anomalies"
          :subtitle="'Kepatuhan Patroli: ' + patrolSummary.security.compliance_percent + '%'"
          :icon="ShieldCheck"
          :color="kpis.security_anomalies > 0 ? 'red' : 'indigo'"
          badge-text="Patroli QR"
        />
      </div>

      <!-- Quick Actions Grid -->
      <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-3">
        <Link
          href="/general-affair/work-orders"
          class="flex flex-col items-center justify-center p-4 bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 hover:border-amber-400 dark:hover:border-amber-500 hover:shadow-md transition text-center group"
        >
          <div class="w-10 h-10 rounded-lg bg-amber-50 dark:bg-amber-900/40 text-amber-600 dark:text-amber-400 flex items-center justify-center mb-2 group-hover:scale-110 transition">
            <Wrench class="w-5 h-5" />
          </div>
          <span class="text-xs font-semibold text-slate-800 dark:text-slate-200">Work Orders</span>
          <span class="text-[11px] text-slate-500 dark:text-slate-400">Form A-D FPU</span>
        </Link>

        <Link
          href="/general-affair/atk"
          class="flex flex-col items-center justify-center p-4 bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 hover:border-amber-400 dark:hover:border-amber-500 hover:shadow-md transition text-center group"
        >
          <div class="w-10 h-10 rounded-lg bg-sky-50 dark:bg-sky-900/40 text-sky-600 dark:text-sky-400 flex items-center justify-center mb-2 group-hover:scale-110 transition">
            <Package class="w-5 h-5" />
          </div>
          <span class="text-xs font-semibold text-slate-800 dark:text-slate-200">ATK & RT</span>
          <span class="text-[11px] text-slate-500 dark:text-slate-400">Permintaan & Stok</span>
        </Link>

        <Link
          href="/general-affair/assets"
          class="flex flex-col items-center justify-center p-4 bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 hover:border-amber-400 dark:hover:border-amber-500 hover:shadow-md transition text-center group"
        >
          <div class="w-10 h-10 rounded-lg bg-emerald-50 dark:bg-emerald-900/40 text-emerald-600 dark:text-emerald-400 flex items-center justify-center mb-2 group-hover:scale-110 transition">
            <Building2 class="w-5 h-5" />
          </div>
          <span class="text-xs font-semibold text-slate-800 dark:text-slate-200">Aset Tetap</span>
          <span class="text-[11px] text-slate-500 dark:text-slate-400">Penyusutan & Mutasi</span>
        </Link>

        <Link
          href="/general-affair/assets/dir"
          class="flex flex-col items-center justify-center p-4 bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 hover:border-amber-400 dark:hover:border-amber-500 hover:shadow-md transition text-center group"
        >
          <div class="w-10 h-10 rounded-lg bg-purple-50 dark:bg-purple-900/40 text-purple-600 dark:text-purple-400 flex items-center justify-center mb-2 group-hover:scale-110 transition">
            <ClipboardList class="w-5 h-5" />
          </div>
          <span class="text-xs font-semibold text-slate-800 dark:text-slate-200">DIR Lampiran C</span>
          <span class="text-[11px] text-slate-500 dark:text-slate-400">Inventaris Ruangan</span>
        </Link>

        <Link
          href="/general-affair/patrols/security"
          class="flex flex-col items-center justify-center p-4 bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 hover:border-amber-400 dark:hover:border-amber-500 hover:shadow-md transition text-center group"
        >
          <div class="w-10 h-10 rounded-lg bg-indigo-50 dark:bg-indigo-900/40 text-indigo-600 dark:text-indigo-400 flex items-center justify-center mb-2 group-hover:scale-110 transition">
            <Scan class="w-5 h-5" />
          </div>
          <span class="text-xs font-semibold text-slate-800 dark:text-slate-200">Patroli Keamanan</span>
          <span class="text-[11px] text-slate-500 dark:text-slate-400">Scan QR Checkpoint</span>
        </Link>

        <Link
          href="/general-affair/patrols/cleaning"
          class="flex flex-col items-center justify-center p-4 bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 hover:border-amber-400 dark:hover:border-amber-500 hover:shadow-md transition text-center group"
        >
          <div class="w-10 h-10 rounded-lg bg-teal-50 dark:bg-teal-900/40 text-teal-600 dark:text-teal-400 flex items-center justify-center mb-2 group-hover:scale-110 transition">
            <MapPin class="w-5 h-5" />
          </div>
          <span class="text-xs font-semibold text-slate-800 dark:text-slate-200">Kebersihan 5R</span>
          <span class="text-[11px] text-slate-500 dark:text-slate-400">Checklist Sanitasi</span>
        </Link>
      </div>

      <!-- Charts Section -->
      <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <InteractiveChartCard
          title="Distribusi Status Work Order"
          subtitle="Pemantauan progres perbaikan sarana pabrik"
          :chart-options="woStatusChartOptions"
          :chart-series="woStatusChartSeries"
          chart-type="donut"
        />

        <InteractiveChartCard
          title="Valuasi Buku Aset Tetap per Plant (Juta Rp)"
          subtitle="Nilai sisa buku setelah perhitungan depresiasi garis lurus"
          :chart-options="assetPlantChartOptions"
          :chart-series="assetPlantChartSeries"
          chart-type="bar"
        />
      </div>

      <!-- Approval Inbox & Recent Activities -->
      <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="lg:col-span-2">
          <ApprovalInboxWidget
            title="Antrean Persetujuan GA (Work Order FPU & ATK/RT)"
            :items="pendingApprovals"
            module-slug="general-affair"
          />
        </div>

        <div>
          <ActivityFeedWidget
            title="Aktivitas Terkini GA & Patroli"
            :activities="recentActivities"
          />
        </div>
      </div>
    </div>
  </ModuleLayout>
</template>
