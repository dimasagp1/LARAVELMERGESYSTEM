<script setup>
import { ref } from 'vue';
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import ModuleLayout from '@/Layouts/ModuleLayout.vue';
import {
    FileCheck,
    CheckCircle2,
    XCircle,
    ArrowLeft,
    Clock,
    AlertCircle,
    Eye,
    X,
} from 'lucide-vue-next';

const props = defineProps({
    module: Object,
    stage1Formulas: {
        type: Array,
        default: () => [],
    },
    stage1TrialPms: {
        type: Array,
        default: () => [],
    },
    stage2Formulas: {
        type: Array,
        default: () => [],
    },
    stats: Object,
    currentRoleFilter: String,
});

const activeTab = ref('all'); // 'all' | 'stage1' | 'stage2'

// Reject Modal State
const showRejectModal = ref(false);
const rejectTarget = ref({ type: '', id: null, title: '' });
const rejectForm = useForm({
    notes: '',
});

function openRejectModal(type, id, title) {
    rejectTarget.value = { type, id, title };
    rejectForm.reset();
    showRejectModal.value = true;
}

function submitReject() {
    rejectForm.post(`/rnd/approvals/${rejectTarget.value.type}/${rejectTarget.value.id}/reject`, {
        onSuccess: () => {
            showRejectModal.value = false;
            rejectForm.reset();
        },
    });
}

function quickApprove(type, id, title) {
    if (confirm(`Setujui dokumen ${title}?`)) {
        router.post(`/rnd/approvals/${type}/${id}/approve`);
    }
}
</script>

<template>
    <ModuleLayout
        :title="module?.name || 'Approval Center R&D'"
        :module-slug="'rnd'"
        :module-name="'R&D (NPD Management)'"
        :color-theme="'cyan'"
        :menus="module?.menus || []"
        :breadcrumbs="['Approval Center']"
    >
        <Head title="Approval Center (Single Inbox OM & GM) - R&D" />

        <div class="max-w-5xl mx-auto space-y-6">
            <!-- Header Bar -->
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <Link
                        href="/rnd"
                        class="p-2 rounded-xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 text-slate-500 hover:text-slate-900 dark:hover:text-white transition shadow-2xs"
                    >
                        <ArrowLeft class="w-4 h-4" />
                    </Link>
                    <div>
                        <div class="flex items-center gap-2">
                            <h1 class="text-xl font-bold text-slate-900 dark:text-white tracking-tight">
                                Approval Center R&D
                            </h1>
                            <span class="text-xs font-mono font-bold px-2 py-0.5 rounded bg-amber-100 dark:bg-amber-950 text-amber-800 dark:text-amber-300 border border-amber-200 dark:border-amber-800">
                                Single Inbox Gatekeeping
                            </span>
                        </div>
                        <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">
                            Kotak masuk terpadu untuk verifikasi formula, trial kemasan, dan pengesahan direksi.
                        </p>
                    </div>
                </div>
            </div>

            <!-- Tabs: All vs Stage 1 (OM) vs Stage 2 (GM) -->
            <div class="border-b border-slate-200 dark:border-slate-800 flex items-center gap-2">
                <button
                    @click="activeTab = 'all'"
                    :class="[
                        'flex items-center gap-2 px-4 py-3 text-xs font-semibold border-b-2 transition',
                        activeTab === 'all'
                            ? 'border-cyan-600 text-cyan-600 dark:text-cyan-400'
                            : 'border-transparent text-slate-500 hover:text-slate-800 dark:hover:text-slate-200',
                    ]"
                >
                    <span>Semua Antrean</span>
                    <span class="text-[10px] font-mono px-1.5 py-0.5 rounded bg-slate-100 dark:bg-slate-800">
                        {{ stage1Formulas.length + stage1TrialPms.length + stage2Formulas.length }}
                    </span>
                </button>

                <button
                    @click="activeTab = 'stage1'"
                    :class="[
                        'flex items-center gap-2 px-4 py-3 text-xs font-semibold border-b-2 transition',
                        activeTab === 'stage1'
                            ? 'border-cyan-600 text-cyan-600 dark:text-cyan-400'
                            : 'border-transparent text-slate-500 hover:text-slate-800 dark:hover:text-slate-200',
                    ]"
                >
                    <span>Tahap 1: Operational Manager (OM)</span>
                    <span class="text-[10px] font-mono px-1.5 py-0.5 rounded bg-amber-100 dark:bg-amber-950 text-amber-700 dark:text-amber-300 font-bold">
                        {{ stage1Formulas.length + stage1TrialPms.length }}
                    </span>
                </button>

                <button
                    @click="activeTab = 'stage2'"
                    :class="[
                        'flex items-center gap-2 px-4 py-3 text-xs font-semibold border-b-2 transition',
                        activeTab === 'stage2'
                            ? 'border-cyan-600 text-cyan-600 dark:text-cyan-400'
                            : 'border-transparent text-slate-500 hover:text-slate-800 dark:hover:text-slate-200',
                    ]"
                >
                    <span>Tahap 2: General Manager (Final)</span>
                    <span class="text-[10px] font-mono px-1.5 py-0.5 rounded bg-emerald-100 dark:bg-emerald-950 text-emerald-700 dark:text-emerald-300 font-bold">
                        {{ stage2Formulas.length }}
                    </span>
                </button>
            </div>

            <!-- Items List -->
            <div class="space-y-4">
                <!-- TAHAP 1 FORMULAS -->
                <template v-if="activeTab === 'all' || activeTab === 'stage1'">
                    <div
                        v-for="f in stage1Formulas"
                        :key="`f1-${f.id}`"
                        class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-5 flex flex-col md:flex-row md:items-center justify-between gap-4 hover:border-cyan-500/50 transition shadow-xs"
                    >
                        <div class="space-y-1.5">
                            <div class="flex items-center gap-2">
                                <span class="px-2 py-0.5 rounded text-[10px] font-mono font-bold bg-amber-50 dark:bg-amber-950 text-amber-700 dark:text-amber-300 border border-amber-200 dark:border-amber-800">
                                    Tahap 1 (OM Review)
                                </span>
                                <span class="font-mono text-xs font-bold text-slate-900 dark:text-white">{{ f.code }}</span>
                                <span class="text-xs text-slate-400 font-mono">Versi {{ f.version }}</span>
                            </div>
                            <h3 class="font-bold text-sm text-slate-900 dark:text-white">
                                {{ f.product?.name }} &bull; Takaran {{ f.target_serving_size_g }}g ({{ f.servings_per_pack }} {{ f.serving_unit }}/pack)
                            </h3>
                            <p class="text-xs text-slate-500 dark:text-slate-400">
                                Disusun oleh: <strong class="text-slate-700 dark:text-slate-300">{{ f.creator?.name || 'Formulator' }}</strong> &bull; HPP: <strong class="text-emerald-600">Rp {{ Number(f.total_cost_per_pack).toLocaleString('id-ID') }}</strong>
                            </p>
                        </div>

                        <div class="flex items-center gap-2 shrink-0">
                            <Link
                                :href="`/rnd/formulas/${f.id}`"
                                class="px-3 py-2 rounded-xl bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-300 text-xs font-semibold transition"
                            >
                                <Eye class="w-3.5 h-3.5 inline mr-1" />
                                <span>Detail Resep</span>
                            </Link>

                            <button
                                @click="openRejectModal('formula', f.id, f.code)"
                                class="px-3 py-2 rounded-xl bg-rose-50 hover:bg-rose-100 dark:bg-rose-950/60 dark:hover:bg-rose-900 text-rose-600 dark:text-rose-300 text-xs font-semibold transition"
                            >
                                Tolak / Revisi
                            </button>

                            <button
                                @click="quickApprove('formula', f.id, f.code)"
                                class="px-4 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-white text-xs font-semibold transition shadow-xs flex items-center gap-1.5"
                            >
                                <CheckCircle2 class="w-4 h-4" />
                                <span>Setujui Tahap 1</span>
                            </button>
                        </div>
                    </div>

                    <!-- TAHAP 1 TRIAL PM -->
                    <div
                        v-for="tp in stage1TrialPms"
                        :key="`tp-${tp.id}`"
                        class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-5 flex flex-col md:flex-row md:items-center justify-between gap-4 hover:border-cyan-500/50 transition shadow-xs"
                    >
                        <div class="space-y-1.5">
                            <div class="flex items-center gap-2">
                                <span class="px-2 py-0.5 rounded text-[10px] font-mono font-bold bg-cyan-50 dark:bg-cyan-950 text-cyan-700 dark:text-cyan-300 border border-cyan-200 dark:border-cyan-800">
                                    Trial PM (Mesin Kemas)
                                </span>
                                <span class="font-mono text-xs font-bold text-slate-900 dark:text-white">{{ tp.code }}</span>
                                <span class="text-xs text-emerald-600 font-mono font-bold">4/4 Dept Lengkap</span>
                            </div>
                            <h3 class="font-bold text-sm text-slate-900 dark:text-white">
                                {{ tp.product?.name }} &bull; Vendor: {{ tp.vendor?.name }}
                            </h3>
                            <p class="text-xs text-slate-500 dark:text-slate-400">
                                Hasil Uji: Kebocoran {{ tp.leakage_test_result }} &bull; Kekuatan {{ tp.seal_strength_result }}
                            </p>
                        </div>

                        <div class="flex items-center gap-2 shrink-0">
                            <Link
                                :href="`/rnd/trial-pms/${tp.id}`"
                                class="px-3 py-2 rounded-xl bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-300 text-xs font-semibold transition"
                            >
                                <Eye class="w-3.5 h-3.5 inline mr-1" />
                                <span>Detail Uji</span>
                            </Link>

                            <button
                                @click="quickApprove('trial_pm', tp.id, tp.code)"
                                class="px-4 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-white text-xs font-semibold transition shadow-xs flex items-center gap-1.5"
                            >
                                <CheckCircle2 class="w-4 h-4" />
                                <span>Sahkan Final (OM)</span>
                            </button>
                        </div>
                    </div>
                </template>

                <!-- TAHAP 2 (GENERAL MANAGER) -->
                <template v-if="activeTab === 'all' || activeTab === 'stage2'">
                    <div
                        v-for="f in stage2Formulas"
                        :key="`f2-${f.id}`"
                        class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-5 flex flex-col md:flex-row md:items-center justify-between gap-4 hover:border-cyan-500/50 transition shadow-xs"
                    >
                        <div class="space-y-1.5">
                            <div class="flex items-center gap-2">
                                <span class="px-2 py-0.5 rounded text-[10px] font-mono font-bold bg-emerald-50 dark:bg-emerald-950 text-emerald-700 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800">
                                    Tahap 2: Pengesahan Final General Manager
                                </span>
                                <span class="font-mono text-xs font-bold text-slate-900 dark:text-white">{{ f.code }}</span>
                            </div>
                            <h3 class="font-bold text-sm text-slate-900 dark:text-white">
                                {{ f.product?.name }} &bull; Telah Disetujui Tahap 1 oleh OM
                            </h3>
                            <p class="text-xs text-slate-500 dark:text-slate-400">
                                Menunggu persetujuan legal GM untuk mengunci formula resmi dan menerbitkan lembar cetak farmasi.
                            </p>
                        </div>

                        <div class="flex items-center gap-2 shrink-0">
                            <Link
                                :href="`/rnd/formulas/${f.id}`"
                                class="px-3 py-2 rounded-xl bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-300 text-xs font-semibold transition"
                            >
                                <Eye class="w-3.5 h-3.5 inline mr-1" />
                                <span>Detail Resep</span>
                            </Link>

                            <button
                                @click="openRejectModal('formula', f.id, f.code)"
                                class="px-3 py-2 rounded-xl bg-rose-50 hover:bg-rose-100 dark:bg-rose-950/60 dark:hover:bg-rose-900 text-rose-600 dark:text-rose-300 text-xs font-semibold transition"
                            >
                                Kembalikan
                            </button>

                            <button
                                @click="quickApprove('formula', f.id, f.code)"
                                class="px-4 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-white text-xs font-semibold transition shadow-xs flex items-center gap-1.5"
                            >
                                <CheckCircle2 class="w-4 h-4" />
                                <span>Sahkan & Kunci (GM)</span>
                            </button>
                        </div>
                    </div>
                </template>
            </div>
        </div>

        <!-- REJECT MODAL -->
        <div
            v-if="showRejectModal"
            class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/80 backdrop-blur-xs"
        >
            <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-3xl max-w-md w-full p-6 space-y-4 shadow-2xl">
                <div class="flex items-center justify-between border-b border-slate-100 dark:border-slate-800 pb-3">
                    <h3 class="font-bold text-slate-900 dark:text-white text-sm flex items-center gap-2">
                        <XCircle class="w-5 h-5 text-rose-500" />
                        <span>Catatan Penolakan / Revisi: {{ rejectTarget.title }}</span>
                    </h3>
                    <button @click="showRejectModal = false" class="text-slate-400 hover:text-slate-600">
                        <X class="w-5 h-5" />
                    </button>
                </div>

                <form @submit.prevent="submitReject" class="space-y-4">
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">
                            Alasan Penolakan & Instruksi Perbaikan Formulator:
                        </label>
                        <textarea
                            v-model="rejectForm.notes"
                            rows="4"
                            required
                            placeholder="Contoh: Dosis bahan aktif terlalu tinggi, mohon turunkan persentase menjadi 45% dan substitusi eksipien..."
                            class="w-full bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl px-3 py-2 text-xs text-slate-800 dark:text-slate-100 focus:ring-rose-500"
                        ></textarea>
                    </div>

                    <div class="flex items-center justify-end gap-2 pt-2">
                        <button
                            type="button"
                            @click="showRejectModal = false"
                            class="px-4 py-2 rounded-xl text-xs text-slate-500 hover:text-slate-800"
                        >
                            Batal
                        </button>
                        <button
                            type="submit"
                            :disabled="rejectForm.processing"
                            class="px-4 py-2 rounded-xl bg-rose-600 hover:bg-rose-500 text-white font-semibold text-xs transition disabled:opacity-50"
                        >
                            Kirim Penolakan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </ModuleLayout>
</template>
