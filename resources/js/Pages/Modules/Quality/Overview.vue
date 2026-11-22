<script setup>
import { ref } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import ModuleLayout from '@/Layouts/ModuleLayout.vue';
import KpiStatCard from '@/Components/Dashboard/KpiStatCard.vue';
import InteractiveChartCard from '@/Components/Dashboard/InteractiveChartCard.vue';
import ApprovalInboxWidget from '@/Components/Dashboard/ApprovalInboxWidget.vue';
import ActivityFeedWidget from '@/Components/Dashboard/ActivityFeedWidget.vue';
import {
  FileCheck,
  GitCommit,
  AlertOctagon,
  CheckCircle2,
  FolderTree,
  AlertTriangle,
  ShieldCheck,
  PlusCircle,
  FileText,
  Search,
  ExternalLink,
  ChevronRight,
  Sparkles,
} from 'lucide-vue-next';

const props = defineProps({
  module: {
    type: Object,
    required: true,
  },
  kpis: {
    type: Object,
    default: () => ({
      active_qms_docs: 0,
      open_change_requests: 0,
      open_deviations: 0,
      active_capas: 0,
      overdue_capa_actions: 0,
      avg_fmea_rpn: 0,
    }),
  },
  deviationsBySeverity: {
    type: Object,
    default: () => ({ kritis: 0, mayor: 0, minor: 0 }),
  },
  capaStatus: {
    type: Object,
    default: () => ({ draft: 0, in_progress: 0, approved: 0, close: 0 }),
  },
  docsByLevel: {
    type: Object,
    default: () => ({ level_1: 0, level_2: 0, level_3: 0, level_4: 0 }),
  },
  fishboneCounts: {
    type: Object,
    default: () => ({
      machine: 0,
      man: 0,
      method: 0,
      materials: 0,
      milieu: 0,
      measurement: 0,
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

// Chart 1: Deviations by Severity (Donut Chart)
const severityChartOptions = ref({
  chart: { type: 'donut', fontFamily: 'inherit' },
  labels: ['Kritis', 'Mayor', 'Minor'],
  colors: ['#ef4444', '#f59e0b', '#06b6d4'],
  legend: { position: 'bottom' },
  dataLabels: { enabled: true },
  stroke: { width: 0 },
  plotOptions: {
    pie: {
      donut: {
        size: '65%',
        labels: {
          show: true,
          total: {
            show: true,
            label: 'Total Deviasi',
            formatter: (w) => {
              return w.globals.seriesTotals.reduce((a, b) => a + b, 0);
            },
          },
        },
      },
    },
  },
});
const severityChartSeries = ref([
  props.deviationsBySeverity.kritis || 0,
  props.deviationsBySeverity.mayor || 0,
  props.deviationsBySeverity.minor || 0,
]);

// Chart 2: 6M Fishbone Root Cause Categories (Bar Chart)
const fishboneChartOptions = ref({
  chart: { type: 'bar', toolbar: { show: false } },
  colors: ['#0d9488'],
  plotOptions: {
    bar: {
      borderRadius: 4,
      horizontal: true,
      distributed: false,
    },
  },
  dataLabels: { enabled: true },
  xaxis: {
    categories: ['Machine', 'Man', 'Method', 'Materials', 'Milieu', 'Measurement'],
  },
  grid: { borderColor: '#f1f5f9' },
});
const fishboneChartSeries = ref([
  {
    name: 'Temuan Akar Masalah',
    data: [
      props.fishboneCounts.machine || 0,
      props.fishboneCounts.man || 0,
      props.fishboneCounts.method || 0,
      props.fishboneCounts.materials || 0,
      props.fishboneCounts.milieu || 0,
      props.fishboneCounts.measurement || 0,
    ],
  },
]);

// Quick Action links
const quickActions = [
  {
    title: 'Input Master Dokumen Baru',
    desc: 'Registrasi MM, SOP, IK, atau Formulir Mutu 4-Tingkat',
    href: '/quality/documents/create',
    icon: FileCheck,
    color: 'emerald',
  },
  {
    title: 'Ajukan Change Request (CRA/CRB)',
    desc: 'Usulan perubahan sistem, formula, atau mesin dengan FMEA RPN',
    href: '/quality/change-requests/create',
    icon: GitCommit,
    color: 'teal',
  },
  {
    title: 'Laporkan Deviasi (DR / PK)',
    desc: 'Laporan penyimpangan proses/alat & mitigasi immediate action',
    href: '/quality/deviations/create',
    icon: AlertOctagon,
    color: 'rose',
  },
  {
    title: 'Daftar Monitoring CAPA',
    desc: 'Pantau tindakan perbaikan & verifikasi efektivitas penutupan',
    href: '/quality/capa',
    icon: CheckCircle2,
    color: 'blue',
  },
];
</script>

<template>
  <Head title="Quality Management System (QMS) - Overview" />

  <ModuleLayout :module="module">
    <div class="space-y-6">
      <!-- Hero Top Banner -->
      <div class="relative overflow-hidden rounded-2xl bg-gradient-to-r from-teal-900 via-teal-800 to-emerald-900 p-6 text-white shadow-xl">
        <div class="relative z-10 flex flex-col justify-between gap-4 md:flex-row md:items-center">
          <div>
            <div class="flex items-center gap-2">
              <span class="inline-flex items-center gap-1.5 rounded-full bg-teal-500/20 px-3 py-1 text-xs font-semibold tracking-wide text-teal-200 backdrop-blur-sm border border-teal-400/30">
                <ShieldCheck class="h-3.5 w-3.5 text-teal-300" />
                CPOB / GMP & ISO 9001 Compliant
              </span>
              <span v-if="kpis.overdue_capa_actions > 0" class="inline-flex items-center gap-1 rounded-full bg-rose-500/30 px-2.5 py-0.5 text-xs font-bold text-rose-200 border border-rose-400/30">
                <AlertTriangle class="h-3 w-3" />
                {{ kpis.overdue_capa_actions }} CAPA Overdue
              </span>
            </div>
            <h1 class="mt-2 text-2xl font-bold tracking-tight text-white md:text-3xl">
              Quality Assurance & Management System
            </h1>
            <p class="mt-1 text-sm text-teal-100/90 max-w-2xl">
              Pusat kendali mutu terpadu: repositori piramida dokumen 4-tingkat, pengkajian risiko Change Request CRA/CRB, investigasi deviasi 6M Ishikawa & 5-Why, serta otomasi siklus CAPA.
            </p>
          </div>

          <div class="flex flex-wrap items-center gap-2.5">
            <Link
              href="/quality/documents"
              class="inline-flex items-center gap-2 rounded-xl bg-white/10 px-4 py-2.5 text-sm font-semibold text-white shadow-sm backdrop-blur-md transition hover:bg-white/20 border border-white/15"
            >
              <FolderTree class="h-4 w-4" />
              Piramida Dokumen
            </Link>
            <Link
              href="/quality/deviations/create"
              class="inline-flex items-center gap-2 rounded-xl bg-teal-500 px-4 py-2.5 text-sm font-semibold text-white shadow-lg transition hover:bg-teal-400"
            >
              <PlusCircle class="h-4 w-4" />
              Lapor Deviasi
            </Link>
          </div>
        </div>

        <div class="absolute -right-10 -bottom-10 h-64 w-64 rounded-full bg-teal-500/10 blur-3xl pointer-events-none"></div>
      </div>

      <!-- Core 4 KPI Stat Cards -->
      <div class="grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-4">
        <KpiStatCard
          title="Dokumen Mutu Aktif"
          :value="kpis.active_qms_docs.toString()"
          change="+3 revisi bulan ini"
          trend="up"
          color-theme="teal"
          icon="FileCheck"
        />

        <KpiStatCard
          title="Change Request Aktif"
          :value="kpis.open_change_requests.toString()"
          :change="`Rata-rata RPN: ${kpis.avg_fmea_rpn}`"
          trend="neutral"
          color-theme="emerald"
          icon="GitCommit"
        />

        <KpiStatCard
          title="Deviasi Terbuka (DR)"
          :value="kpis.open_deviations.toString()"
          :change="`${deviationsBySeverity.kritis} Kritis &bull; ${deviationsBySeverity.mayor} Mayor`"
          trend="down"
          color-theme="rose"
          icon="AlertOctagon"
        />

        <KpiStatCard
          title="Siklus CAPA Berjalan"
          :value="kpis.active_capas.toString()"
          :change="`${capaStatus.close} Selesai & Ditutup`"
          trend="up"
          color-theme="cyan"
          icon="CheckCircle2"
        />
      </div>

      <!-- Interactive Charts Grid -->
      <div class="grid grid-cols-1 gap-6 lg:grid-cols-12">
        <!-- Chart 1: Deviasi Berdasarkan Tingkat Keparahan -->
        <div class="lg:col-span-5">
          <InteractiveChartCard
            title="Sebaran Deviasi Berdasarkan Tingkat CPOB"
            subtitle="Klasifikasi Kritis, Mayor, dan Minor"
            chart-type="donut"
            :options="severityChartOptions"
            :series="severityChartSeries"
            :height="280"
          />
        </div>

        <!-- Chart 2: Analisis 6M Fishbone Pareto -->
        <div class="lg:col-span-7">
          <InteractiveChartCard
            title="Pareto 6M Diagram Tulang Ikan (Ishikawa)"
            subtitle="Distribusi akar penyebab ketidaksesuaian pabrik"
            chart-type="bar"
            :options="fishboneChartOptions"
            :series="fishboneChartSeries"
            :height="280"
          />
        </div>
      </div>

      <!-- Quick Action Cards Section -->
      <div>
        <h2 class="text-base font-bold text-slate-900 dark:text-slate-100 mb-3">
          Aksi Cepat Manajemen Mutu
        </h2>
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
          <Link
            v-for="action in quickActions"
            :key="action.title"
            :href="action.href"
            class="group flex flex-col justify-between rounded-xl border border-slate-200/80 bg-white p-5 shadow-xs transition hover:-translate-y-0.5 hover:border-teal-500/50 hover:shadow-md dark:border-slate-800 dark:bg-slate-900"
          >
            <div>
              <div class="inline-flex h-11 w-11 items-center justify-center rounded-xl bg-teal-50 text-teal-600 transition group-hover:bg-teal-600 group-hover:text-white dark:bg-teal-950/50 dark:text-teal-400">
                <component :is="action.icon" class="h-5 w-5" />
              </div>
              <h3 class="mt-3.5 text-sm font-semibold text-slate-900 group-hover:text-teal-600 dark:text-white dark:group-hover:text-teal-400">
                {{ action.title }}
              </h3>
              <p class="mt-1 text-xs text-slate-500 dark:text-slate-400 leading-relaxed">
                {{ action.desc }}
              </p>
            </div>
            <div class="mt-4 flex items-center gap-1 text-xs font-semibold text-teal-600 dark:text-teal-400">
              Buka menu
              <ChevronRight class="h-3.5 w-3.5 transition group-hover:translate-x-1" />
            </div>
          </Link>
        </div>
      </div>

      <!-- Pending Approvals & Activity Feed Grid -->
      <div class="grid grid-cols-1 gap-6 lg:grid-cols-12">
        <div class="lg:col-span-7">
          <ApprovalInboxWidget
            :items="pendingApprovals"
            title="Antrean Persetujuan Mutu (QA & Manajemen)"
            module-slug="quality"
          />
        </div>

        <div class="lg:col-span-5">
          <ActivityFeedWidget
            :activities="recentActivities"
            title="Log Aktivitas & Riwayat Audit Mutu"
          />
        </div>
      </div>
    </div>
  </ModuleLayout>
</template>
