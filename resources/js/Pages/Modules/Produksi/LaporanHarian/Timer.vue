<script setup>
import { ref, computed } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import ModuleLayout from '@/Layouts/ModuleLayout.vue';
import {
    Timer,
    Play,
    Pause,
    Square,
    RotateCcw,
    AlertTriangle,
    ShieldCheck,
    Lock,
    Unlock,
    FileDown,
    ArrowLeft,
    CheckCircle2,
    Clock,
    Factory,
    Gauge,
    Save,
    Plus,
    X,
} from 'lucide-vue-next';

const props = defineProps({
    report: {
        type: Object,
        required: true,
    },
    downtimeReasons: {
        type: Array,
        default: () => [],
    },
});

// Modal Mandatory Downtime on Resume
const showResumeModal = ref(false);
const resumeForm = ref({
    alasan_downtime_id: '',
    kategori: 'mesin',
    alasan_nama: '',
    durasi_menit: '',
    keterangan: '',
});

// Output Form
const outputForm = ref({
    capacity_fisik: props.report.capacity_fisik || 20000,
    output_fisik: props.report.output_fisik || 19500,
    total_reject: props.report.total_reject || 150,
    notes: props.report.notes || '',
});

// Timer Action Triggers
const handleStart = () => {
    router.post(`/production/daily-reports/${props.report.id}/start`, {}, {
        preserveScroll: true,
    });
};

const handlePause = () => {
    router.post(`/production/daily-reports/${props.report.id}/pause`, {}, {
        preserveScroll: true,
    });
};

const openResumeModal = () => {
    resumeForm.value = {
        alasan_downtime_id: props.downtimeReasons[0]?.id || '',
        kategori: props.downtimeReasons[0]?.category || 'mesin',
        alasan_nama: props.downtimeReasons[0]?.name || '',
        durasi_menit: '',
        keterangan: '',
    };
    showResumeModal.value = true;
};

const onSelectReason = () => {
    const selected = props.downtimeReasons.find(r => r.id === parseInt(resumeForm.value.alasan_downtime_id));
    if (selected) {
        resumeForm.value.kategori = selected.category;
        resumeForm.value.alasan_nama = selected.name;
    }
};

const submitResume = () => {
    router.post(`/production/daily-reports/${props.report.id}/resume`, resumeForm.value, {
        preserveScroll: true,
        onSuccess: () => {
            showResumeModal.value = false;
        },
    });
};

const handleEnd = () => {
    if (confirm('Hentikan timer produksi? Durasi kotor dan bersih akan dihitung, dan draf proses selanjutnya akan disiapkan.')) {
        router.post(`/production/daily-reports/${props.report.id}/end`, {}, {
            preserveScroll: true,
        });
    }
};

const submitOutput = () => {
    router.post(`/production/daily-reports/${props.report.id}/submit`, outputForm.value, {
        preserveScroll: true,
        onSuccess: () => {
            alert('Hasil output & OEE berhasil dihitung dan disubmit.');
        },
    });
};

const handleLock = () => {
    if (confirm('Kunci laporan harian ini secara resmi? Setelah dikunci, data tidak dapat diubah lagi oleh operator.')) {
        router.post(`/production/daily-reports/${props.report.id}/lock`, {}, {
            preserveScroll: true,
            onSuccess: () => {
                alert('Laporan resmi dikunci dan disinkronkan ke Finance & HRIS.');
            },
        });
    }
};

const handleSpawnDeviation = () => {
    if (confirm('Buat draf Deviation Report di modul Quality (QMS) untuk penyimpangan / reject batch ini?')) {
        router.post(`/production/daily-reports/${props.report.id}/spawn-deviation`, {
            topik: `Deviasi Batch ${props.report.batch_number}: Reject Kritis / Masalah Operasional`,
            jenis_penyimpangan: 'Proses / Mesin',
            identifikasi_penyimpangan: 'Tinggi',
        }, {
            preserveScroll: true,
            onSuccess: () => {
                alert('Draf Deviation Report berhasil dibuat di modul Quality (QMS).');
            },
        });
    }
};
</script>

<template>
    <Head :title="`Konsol Timer LHP ${report.report_number} - Lantai Pabrik`" />

    <ModuleLayout module-slug="production">
        <div class="space-y-6">
            <!-- Header Bar -->
            <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                <div class="flex items-center gap-3">
                    <Link
                        href="/production/daily-reports"
                        class="rounded-lg border border-slate-200 p-2 text-slate-500 hover:bg-slate-50 dark:border-slate-800 dark:text-slate-400"
                    >
                        <ArrowLeft class="h-4 w-4" />
                    </Link>
                    <div>
                        <div class="flex items-center gap-2">
                            <span class="font-mono text-xs font-bold text-rose-600 dark:text-rose-400">{{ report.report_number }}</span>
                            <span class="rounded bg-rose-100 px-2 py-0.5 text-[10px] font-bold text-rose-800 uppercase dark:bg-rose-950/60 dark:text-rose-300">
                                TAHAP: {{ report.proses }}
                            </span>
                            <span v-if="report.is_locked" class="inline-flex items-center gap-1 rounded bg-emerald-100 px-2 py-0.5 text-[10px] font-bold text-emerald-800">
                                <Lock class="h-2.5 w-2.5" /> LOCKED
                            </span>
                        </div>
                        <h1 class="text-xl font-bold text-slate-900 dark:text-white sm:text-2xl">
                            Konsol Eksekusi & Timer Lantai Produksi
                        </h1>
                    </div>
                </div>

                <div class="flex items-center gap-2">
                    <a
                        :href="`/production/daily-reports/${report.id}/pdf`"
                        target="_blank"
                        class="inline-flex items-center gap-1.5 rounded-lg border border-slate-200 bg-white px-3 py-2 text-xs font-semibold text-slate-700 shadow-sm hover:bg-slate-50 dark:border-slate-800 dark:bg-slate-900 dark:text-slate-300"
                    >
                        <FileDown class="h-4 w-4 text-rose-600" /> Unduh LHP PDF
                    </a>
                </div>
            </div>

            <!-- Batch & Line Status Card -->
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
                <div class="rounded-xl border border-slate-200 bg-white p-4 shadow-sm dark:border-slate-800 dark:bg-slate-900">
                    <span class="text-xs font-medium text-slate-500">Nomor Batch Estafet</span>
                    <div class="mt-1 text-lg font-mono font-bold text-rose-600 dark:text-rose-400">{{ report.batch_number }}</div>
                    <div class="text-xs text-slate-500 truncate">{{ report.product?.name }}</div>
                </div>
                <div class="rounded-xl border border-slate-200 bg-white p-4 shadow-sm dark:border-slate-800 dark:bg-slate-900">
                    <span class="text-xs font-medium text-slate-500">Jalur / Lini & Mesin</span>
                    <div class="mt-1 font-semibold text-slate-900 dark:text-white">{{ report.line?.name }}</div>
                    <div class="text-xs text-slate-500">{{ report.machine?.name || 'Mesin Standar Lini' }} (CT: {{ report.ct }}s)</div>
                </div>
                <div class="rounded-xl border border-slate-200 bg-white p-4 shadow-sm dark:border-slate-800 dark:bg-slate-900">
                    <span class="text-xs font-medium text-slate-500">Jadwal & Shift</span>
                    <div class="mt-1 font-semibold text-slate-900 dark:text-white">{{ report.tanggal }} | {{ report.shift?.toUpperCase() }}</div>
                    <div class="text-xs text-slate-500">Operator: {{ report.operator?.name || 'Leader Shift' }}</div>
                </div>
                <div class="rounded-xl border border-slate-200 bg-white p-4 shadow-sm dark:border-slate-800 dark:bg-slate-900">
                    <span class="text-xs font-medium text-slate-500">Status Timer</span>
                    <div class="mt-1 flex items-center gap-1.5">
                        <span :class="[
                            'inline-flex items-center gap-1 rounded-full px-2.5 py-1 text-xs font-bold',
                            report.timer_status === 'start' ? 'bg-emerald-100 text-emerald-700 animate-pulse' :
                            report.timer_status === 'pause' ? 'bg-amber-100 text-amber-700' :
                            report.timer_status === 'end' ? 'bg-blue-100 text-blue-700' :
                            'bg-slate-100 text-slate-700'
                        ]">
                            <Play v-if="report.timer_status === 'start'" class="h-3 w-3 fill-current" />
                            <Pause v-else-if="report.timer_status === 'pause'" class="h-3 w-3" />
                            <Clock v-else class="h-3 w-3" />
                            {{ report.timer_status?.toUpperCase() }}
                        </span>
                    </div>
                </div>
            </div>

            <!-- Digital Stopwatch & Timer Control Panel -->
            <div class="rounded-2xl border-2 border-slate-200 bg-slate-900 p-6 text-white shadow-xl dark:border-slate-800">
                <div class="flex flex-col items-center justify-between gap-6 md:flex-row">
                    <!-- Times Display -->
                    <div class="flex flex-wrap items-center gap-8">
                        <div>
                            <span class="text-xs font-medium text-slate-400 uppercase tracking-wider">Jam Mulai</span>
                            <div class="text-xl font-mono font-bold text-emerald-400">{{ report.start_time || '--:--:--' }}</div>
                        </div>
                        <div class="border-l border-slate-700 pl-8">
                            <span class="text-xs font-medium text-slate-400 uppercase tracking-wider">Jam Selesai</span>
                            <div class="text-xl font-mono font-bold text-blue-400">{{ report.end_time || '--:--:--' }}</div>
                        </div>
                        <div class="border-l border-slate-700 pl-8">
                            <span class="text-xs font-medium text-slate-400 uppercase tracking-wider">Gross Time</span>
                            <div class="text-xl font-mono font-bold text-white">{{ report.gross_time_menit }} Menit</div>
                        </div>
                        <div class="border-l border-slate-700 pl-8">
                            <span class="text-xs font-medium text-slate-400 uppercase tracking-wider">Total Downtime (Jeda)</span>
                            <div class="text-xl font-mono font-bold text-rose-400">{{ report.total_pause_menit }} Menit</div>
                        </div>
                        <div class="border-l border-slate-700 pl-8">
                            <span class="text-xs font-medium text-slate-400 uppercase tracking-wider">Waktu Bersih</span>
                            <div class="text-xl font-mono font-bold text-emerald-300">{{ report.waktu_bersih_menit }} Menit</div>
                        </div>
                    </div>

                    <!-- Push Buttons -->
                    <div v-if="!report.is_locked" class="flex flex-wrap items-center gap-3">
                        <!-- START BUTTON -->
                        <button
                            v-if="report.timer_status === 'draft' || report.timer_status === 'end'"
                            type="button"
                            @click="handleStart"
                            class="inline-flex items-center gap-2 rounded-xl bg-emerald-600 px-6 py-3.5 text-sm font-bold text-white shadow-lg hover:bg-emerald-500 active:scale-95 transition-all"
                        >
                            <Play class="h-5 w-5 fill-current" /> START TIMER
                        </button>

                        <!-- PAUSE BUTTON -->
                        <button
                            v-if="report.timer_status === 'start'"
                            type="button"
                            @click="handlePause"
                            class="inline-flex items-center gap-2 rounded-xl bg-amber-500 px-6 py-3.5 text-sm font-bold text-slate-950 shadow-lg hover:bg-amber-400 active:scale-95 transition-all"
                        >
                            <Pause class="h-5 w-5" /> PAUSE (JEDA)
                        </button>

                        <!-- RESUME BUTTON -->
                        <button
                            v-if="report.timer_status === 'pause'"
                            type="button"
                            @click="openResumeModal"
                            class="inline-flex items-center gap-2 rounded-xl bg-blue-600 px-6 py-3.5 text-sm font-bold text-white shadow-lg hover:bg-blue-500 active:scale-95 transition-all animate-bounce"
                        >
                            <Play class="h-5 w-5 fill-current" /> RESUME + ISI DOWNTIME
                        </button>

                        <!-- END BUTTON -->
                        <button
                            v-if="report.timer_status === 'start' || report.timer_status === 'pause'"
                            type="button"
                            @click="handleEnd"
                            class="inline-flex items-center gap-2 rounded-xl bg-rose-600 px-6 py-3.5 text-sm font-bold text-white shadow-lg hover:bg-rose-500 active:scale-95 transition-all"
                        >
                            <Square class="h-5 w-5 fill-current" /> SELESAI (END)
                        </button>
                    </div>
                    <div v-else class="text-xs font-semibold text-emerald-400">
                        Laporan ini telah dikunci resmi oleh Supervisor.
                    </div>
                </div>
            </div>

            <!-- Calculated OEE Dashboard Strip -->
            <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-900">
                <div class="flex items-center justify-between pb-3 border-b border-slate-100 dark:border-slate-800">
                    <div>
                        <h2 class="text-base font-semibold text-slate-900 dark:text-white">Kalkulasi Otomatis OEE & Mutu Batch</h2>
                        <p class="text-xs text-slate-500">Berdasarkan rumus Sasaran Mutu 2026 PT. Herbatech Inti Persada</p>
                    </div>
                    <span class="text-xs font-bold font-mono text-slate-500">Standar CT: {{ report.ct }}s/unit</span>
                </div>

                <div class="mt-4 grid grid-cols-2 gap-4 sm:grid-cols-5 text-center">
                    <div class="rounded-xl bg-slate-50 p-3 dark:bg-slate-800/40">
                        <span class="text-[11px] font-bold text-slate-500 uppercase">Availability</span>
                        <div class="mt-1 text-2xl font-bold text-slate-900 dark:text-white">{{ report.availability_persen }}%</div>
                        <span class="text-[10px] text-slate-400">Waktu Bersih / Gross</span>
                    </div>
                    <div class="rounded-xl bg-slate-50 p-3 dark:bg-slate-800/40">
                        <span class="text-[11px] font-bold text-slate-500 uppercase">Performance</span>
                        <div class="mt-1 text-2xl font-bold text-slate-900 dark:text-white">{{ report.performance_persen }}%</div>
                        <span class="text-[10px] text-slate-400">Output Teoritis / Bersih</span>
                    </div>
                    <div class="rounded-xl bg-slate-50 p-3 dark:bg-slate-800/40">
                        <span class="text-[11px] font-bold text-slate-500 uppercase">Yield / Mutu</span>
                        <div class="mt-1 text-2xl font-bold text-slate-900 dark:text-white">{{ report.yield_persen }}%</div>
                        <span class="text-[10px] text-slate-400">Good Output Ratio</span>
                    </div>
                    <div class="rounded-xl bg-rose-50 p-3 dark:bg-rose-950/20 border border-rose-200 dark:border-rose-900">
                        <span class="text-[11px] font-bold text-rose-700 uppercase dark:text-rose-400">Overall OEE</span>
                        <div class="mt-1 text-2xl font-bold text-rose-600 dark:text-rose-400">{{ report.oee_persen }}%</div>
                        <span class="text-[10px] text-rose-500">Target: ≥ 85.0%</span>
                    </div>
                    <div class="rounded-xl bg-slate-50 p-3 dark:bg-slate-800/40">
                        <span class="text-[11px] font-bold text-slate-500 uppercase">Produktivitas</span>
                        <div class="mt-1 text-2xl font-bold text-slate-900 dark:text-white">{{ report.produktivitas_persen }}%</div>
                        <span class="text-[10px] text-slate-400">vs Target Man Power</span>
                    </div>
                </div>
            </div>

            <!-- Two Columns: Output Input Form & Downtime Log -->
            <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
                <!-- Physical Output & Reject Form -->
                <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-900">
                    <div class="border-b border-slate-100 pb-3 dark:border-slate-800">
                        <h2 class="text-base font-semibold text-slate-900 dark:text-white">Input Output Fisik & Reject</h2>
                        <p class="text-xs text-slate-500">Masukkan unit counter mesin dan jumlah barang jadi yang lolos inspeksi</p>
                    </div>

                    <form @submit.prevent="submitOutput" class="mt-4 space-y-4">
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="text-xs font-semibold text-slate-700 dark:text-slate-300">Counter Kapasitas Fisik</label>
                                <input
                                    v-model.number="outputForm.capacity_fisik"
                                    type="number"
                                    class="mt-1 w-full rounded-lg border border-slate-200 p-2.5 text-xs text-slate-900 dark:border-slate-700 dark:bg-slate-800 dark:text-white"
                                    :disabled="report.is_locked"
                                    required
                                />
                            </div>
                            <div>
                                <label class="text-xs font-semibold text-slate-700 dark:text-slate-300">Output Fisik Lolos (Pcs)</label>
                                <input
                                    v-model.number="outputForm.output_fisik"
                                    type="number"
                                    class="mt-1 w-full rounded-lg border border-slate-200 p-2.5 text-xs font-bold text-emerald-600 dark:border-slate-700 dark:bg-slate-800 dark:text-emerald-400"
                                    :disabled="report.is_locked"
                                    required
                                />
                            </div>
                        </div>

                        <div>
                            <label class="text-xs font-semibold text-slate-700 dark:text-slate-300">Total Reject Terbuang (Pcs)</label>
                            <input
                                v-model.number="outputForm.total_reject"
                                type="number"
                                class="mt-1 w-full rounded-lg border border-slate-200 p-2.5 text-xs font-bold text-rose-600 dark:border-slate-700 dark:bg-slate-800 dark:text-rose-400"
                                :disabled="report.is_locked"
                            />
                        </div>

                        <div>
                            <label class="text-xs font-semibold text-slate-700 dark:text-slate-300">Catatan Pelaksanaan Shift</label>
                            <textarea
                                v-model="outputForm.notes"
                                rows="3"
                                placeholder="Catatan kebersihan cleanroom, spesifikasi CPOB, atau kendala teknis..."
                                class="mt-1 w-full rounded-lg border border-slate-200 p-2.5 text-xs text-slate-900 dark:border-slate-700 dark:bg-slate-800 dark:text-white"
                                :disabled="report.is_locked"
                            ></textarea>
                        </div>

                        <div v-if="!report.is_locked" class="flex justify-end gap-2 pt-2">
                            <button
                                type="submit"
                                class="inline-flex items-center gap-1.5 rounded-lg bg-rose-600 px-4 py-2 text-xs font-semibold text-white shadow-sm hover:bg-rose-700"
                            >
                                <Save class="h-4 w-4" /> Hitung & Ajukan Hasil
                            </button>
                        </div>
                    </form>
                </div>

                <!-- Downtime Details Log -->
                <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-900">
                    <div class="flex items-center justify-between pb-3 border-b border-slate-100 dark:border-slate-800">
                        <div>
                            <h2 class="text-base font-semibold text-slate-900 dark:text-white">Log Jeda & Alasan Henti Mesin</h2>
                            <p class="text-xs text-slate-500">Tercatat {{ report.downtime_details?.length || 0 }} event jeda waktu henti</p>
                        </div>
                    </div>

                    <div class="mt-4 space-y-3">
                        <div
                            v-for="(dt, i) in report.downtime_details"
                            :key="i"
                            class="flex items-start justify-between rounded-xl border border-slate-100 bg-slate-50/70 p-3 text-xs dark:border-slate-800 dark:bg-slate-800/40"
                        >
                            <div>
                                <div class="flex items-center gap-2">
                                    <span class="rounded bg-rose-100 px-1.5 py-0.5 text-[10px] font-bold text-rose-700 uppercase">
                                        {{ dt.kategori }}
                                    </span>
                                    <span class="font-bold text-slate-900 dark:text-white">{{ dt.alasan_nama }}</span>
                                </div>
                                <p v-if="dt.keterangan" class="mt-1 text-slate-500">{{ dt.keterangan }}</p>
                            </div>
                            <div class="text-right">
                                <span class="font-bold text-rose-600 dark:text-rose-400">{{ dt.durasi_menit }} m</span>
                                <div class="text-[10px] text-slate-400">{{ dt.created_at ? new Date(dt.created_at).toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' }) : '' }}</div>
                            </div>
                        </div>

                        <div v-if="!report.downtime_details || report.downtime_details.length === 0" class="py-12 text-center text-xs text-slate-400">
                            Tidak ada event jeda henti mesin pada laporan ini (0 Downtime).
                        </div>
                    </div>

                    <!-- Supervisor Lock Section -->
                    <div class="mt-6 rounded-xl border border-dashed border-slate-300 p-4 dark:border-slate-700">
                        <div class="flex items-center justify-between">
                            <div>
                                <div class="text-xs font-bold text-slate-900 dark:text-white">Otorisasi Supervisor Produksi</div>
                                <p class="text-[11px] text-slate-500">Verifikasi kepatuhan CPOB & kunci laporan agar data valid untuk audit</p>
                            </div>
                            <div class="flex items-center gap-2">
                                <button
                                    type="button"
                                    @click="handleSpawnDeviation"
                                    class="inline-flex items-center gap-1.5 rounded-lg bg-teal-600 px-3.5 py-2 text-xs font-bold text-white hover:bg-teal-700 shadow-sm"
                                    title="Eskalasi penyimpangan/reject ke modul Quality (QMS)"
                                >
                                    <AlertTriangle class="h-4 w-4" /> Buat Deviation Report
                                </button>
                                <button
                                    v-if="!report.is_locked"
                                    type="button"
                                    @click="handleLock"
                                    class="inline-flex items-center gap-1.5 rounded-lg bg-emerald-600 px-3.5 py-2 text-xs font-bold text-white hover:bg-emerald-700 shadow-sm"
                                >
                                    <Lock class="h-4 w-4" /> Verifikasi & Lock
                                </button>
                                <span v-else class="text-xs font-bold text-emerald-600 flex items-center gap-1">
                                    <CheckCircle2 class="h-4 w-4" /> Terkunci Resmi
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Mandatory Downtime Modal upon Resume -->
        <div v-if="showResumeModal" class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/60 p-4 backdrop-blur-sm">
            <div class="w-full max-w-lg rounded-2xl bg-white p-6 shadow-2xl dark:bg-slate-900">
                <div class="flex items-center justify-between border-b border-slate-100 pb-3 dark:border-slate-800">
                    <div class="flex items-center gap-2">
                        <span class="rounded-lg bg-rose-100 p-1.5 text-rose-700 dark:bg-rose-950/60">
                            <AlertTriangle class="h-5 w-5" />
                        </span>
                        <div>
                            <h3 class="text-base font-bold text-slate-900 dark:text-white">Input Alasan Henti Mesin (Wajib)</h3>
                            <p class="text-xs text-slate-500">Sistem mewajibkan alasan jeda sebelum timer dapat dilanjutkan (Resume).</p>
                        </div>
                    </div>
                    <button type="button" @click="showResumeModal = false" class="text-slate-400 hover:text-slate-600">
                        <X class="h-5 w-5" />
                    </button>
                </div>

                <form @submit.prevent="submitResume" class="mt-4 space-y-4">
                    <div>
                        <label class="text-xs font-semibold text-slate-700 dark:text-slate-300">Pilih Kamus Alasan Downtime</label>
                        <select
                            v-model="resumeForm.alasan_downtime_id"
                            class="mt-1 w-full rounded-lg border border-slate-200 p-2.5 text-xs text-slate-900 dark:border-slate-700 dark:bg-slate-800 dark:text-white"
                            @change="onSelectReason"
                        >
                            <option v-for="r in downtimeReasons" :key="r.id" :value="r.id">
                                [{{ r.category.toUpperCase() }}] {{ r.name }} (Std: {{ r.default_duration_minutes }}m)
                            </option>
                        </select>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="text-xs font-semibold text-slate-700 dark:text-slate-300">Kategori Hambatan *</label>
                            <select
                                v-model="resumeForm.kategori"
                                class="mt-1 w-full rounded-lg border border-slate-200 p-2.5 text-xs text-slate-900 dark:border-slate-700 dark:bg-slate-800 dark:text-white"
                                required
                            >
                                <option value="mesin">Mesin & Utilitas</option>
                                <option value="bahan">Keterlambatan Bahan Baku / Kemas</option>
                                <option value="vendor">Kendala Material Vendor</option>
                                <option value="operasional">Operasional / Briefing / Istirahat</option>
                            </select>
                        </div>
                        <div>
                            <label class="text-xs font-semibold text-slate-700 dark:text-slate-300">Durasi Riil (Menit Opsional)</label>
                            <input
                                v-model.number="resumeForm.durasi_menit"
                                type="number"
                                step="0.1"
                                placeholder="Auto hitung detik jeda"
                                class="mt-1 w-full rounded-lg border border-slate-200 p-2.5 text-xs text-slate-900 dark:border-slate-700 dark:bg-slate-800 dark:text-white"
                            />
                        </div>
                    </div>

                    <div>
                        <label class="text-xs font-semibold text-slate-700 dark:text-slate-300">Nama / Judul Kendala *</label>
                        <input
                            v-model="resumeForm.alasan_nama"
                            type="text"
                            placeholder="Contoh: Chiller Suhu Tinggi / Tunggu Dus Sekunder..."
                            class="mt-1 w-full rounded-lg border border-slate-200 p-2.5 text-xs text-slate-900 dark:border-slate-700 dark:bg-slate-800 dark:text-white font-medium"
                            required
                        />
                    </div>

                    <div>
                        <label class="text-xs font-semibold text-slate-700 dark:text-slate-300">Keterangan Teknis Tambahan</label>
                        <textarea
                            v-model="resumeForm.keterangan"
                            rows="2"
                            placeholder="Jelaskan tindakan perbaikan yang dilakukan..."
                            class="mt-1 w-full rounded-lg border border-slate-200 p-2.5 text-xs text-slate-900 dark:border-slate-700 dark:bg-slate-800 dark:text-white"
                        ></textarea>
                    </div>

                    <div class="flex justify-end gap-2 pt-2 border-t border-slate-100 dark:border-slate-800">
                        <button
                            type="button"
                            @click="showResumeModal = false"
                            class="rounded-lg border border-slate-200 px-4 py-2 text-xs font-medium text-slate-700 hover:bg-slate-50 dark:border-slate-700 dark:text-slate-300"
                        >
                            Batal
                        </button>
                        <button
                            type="submit"
                            class="inline-flex items-center gap-1.5 rounded-lg bg-blue-600 px-5 py-2 text-xs font-semibold text-white shadow-sm hover:bg-blue-700"
                        >
                            <Play class="h-4 w-4 fill-current" /> Simpan Downtime & Lanjutkan Kerja
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </ModuleLayout>
</template>
