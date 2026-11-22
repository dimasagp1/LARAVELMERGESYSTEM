<script setup>
import { ref } from 'vue';
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import ModuleLayout from '@/Layouts/ModuleLayout.vue';
import {
    Lightbulb,
    Plus,
    Search,
    Filter,
    Award,
    Trophy,
    CheckCircle2,
    Clock,
    Sparkles,
    Shield,
    TrendingUp,
    Star,
    DollarSign,
    Zap,
} from 'lucide-vue-next';

const props = defineProps({
    kizukis: Object,
    leaderboard: Array,
    filters: Object,
    departments: Array,
    summary: Object,
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
const category = ref(props.filters.category || '');
const status = ref(props.filters.status || '');

const applyFilters = () => {
    router.get('/hris/kizuki', {
        search: search.value || undefined,
        category: category.value || undefined,
        status: status.value || undefined,
    }, {
        preserveState: true,
        preserveScroll: true,
    });
};

// Review Modal State
const selectedKizuki = ref(null);
const isReviewModalOpen = ref(false);

const reviewForm = useForm({
    status: 'implemented',
    quality_score: 85,
    cost_saving_score: 80,
    delivery_score: 75,
    safety_score: 90,
    morale_score: 85,
    reward_points: 50,
    review_notes: '',
});

const openReviewModal = (kizuki) => {
    selectedKizuki.value = kizuki;
    reviewForm.quality_score = kizuki.quality_score || 80;
    reviewForm.cost_saving_score = kizuki.cost_saving_score || 80;
    reviewForm.delivery_score = kizuki.delivery_score || 80;
    reviewForm.safety_score = kizuki.safety_score || 80;
    reviewForm.morale_score = kizuki.morale_score || 80;
    reviewForm.reward_points = kizuki.reward_points || 50;
    reviewForm.review_notes = kizuki.review_notes || '';
    isReviewModalOpen.value = true;
};

const submitReview = () => {
    if (!selectedKizuki.value) return;
    reviewForm.post(`/hris/kizuki/${selectedKizuki.value.id}/review`, {
        preserveScroll: true,
        onSuccess: () => {
            isReviewModalOpen.value = false;
            selectedKizuki.value = null;
        },
    });
};
</script>

<template>
    <Head title="Kaizen Kizuki & Gamification — HRISNOW" />

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
                    <div class="p-3 bg-purple-500/10 text-purple-600 dark:text-purple-400 rounded-xl">
                        <Lightbulb class="w-8 h-8" />
                    </div>
                    <div>
                        <h1 class="text-2xl font-bold text-slate-900 dark:text-white tracking-tight">
                            Kaizen Kizuki & Gamification Reward
                        </h1>
                        <p class="text-sm text-slate-500 dark:text-slate-400 mt-0.5">
                            Budaya continuous improvement, evaluasi QCDSM, dan akumulasi poin reward karyawan.
                        </p>
                    </div>
                </div>

                <div class="flex items-center gap-3">
                    <div class="flex items-center gap-2 text-xs">
                        <span class="px-3 py-1.5 rounded-xl bg-purple-50 text-purple-700 border border-purple-200 font-semibold">
                            Total Ide: {{ summary.total_submitted }}
                        </span>
                        <span class="px-3 py-1.5 rounded-xl bg-emerald-50 text-emerald-700 border border-emerald-200 font-semibold">
                            Diimplementasikan: {{ summary.implemented }}
                        </span>
                        <span class="px-3 py-1.5 rounded-xl bg-amber-50 text-amber-700 border border-amber-200 font-semibold">
                            Poin: {{ summary.total_points_distributed }} pts
                        </span>
                    </div>

                    <Link
                        href="/hris/kizuki/create"
                        class="inline-flex items-center gap-2 px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-xl text-sm transition shadow-sm"
                    >
                        <Plus class="w-4 h-4" />
                        Ajukan Ide Kaizen
                    </Link>
                </div>
            </div>

            <!-- Top Gamification Leaderboard Banner -->
            <div class="bg-gradient-to-r from-purple-900 via-indigo-900 to-slate-900 text-white rounded-2xl p-6 shadow-md border border-purple-800/40">
                <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-6">
                    <div>
                        <div class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-semibold bg-purple-500/20 text-purple-300 border border-purple-500/30 mb-2">
                            <Trophy class="w-3.5 h-3.5" />
                            Top 5 Inovator Kaizen Kizuki
                        </div>
                        <h2 class="text-lg font-bold text-white">
                            Peringkat Juara Perbaikan Berkelanjutan (Kaizen)
                        </h2>
                        <p class="text-xs text-slate-300 max-w-md mt-0.5">
                            Karyawan dengan kontribusi ide perbaikan implementatif tertinggi yang telah disetujui Komite.
                        </p>
                    </div>

                    <!-- Leaderboard Cards -->
                    <div class="flex flex-wrap items-center gap-3">
                        <div
                            v-for="(item, idx) in leaderboard"
                            :key="idx"
                            class="bg-white/10 backdrop-blur-md px-4 py-2.5 rounded-xl border border-white/10 flex items-center gap-3"
                        >
                            <div
                                :class="[
                                    'w-7 h-7 rounded-full flex items-center justify-center font-bold text-xs',
                                    idx === 0 ? 'bg-amber-400 text-slate-900' :
                                    idx === 1 ? 'bg-slate-300 text-slate-900' :
                                    idx === 2 ? 'bg-amber-700 text-white' : 'bg-white/20 text-white'
                                ]"
                            >
                                #{{ idx + 1 }}
                            </div>
                            <div>
                                <div class="font-semibold text-xs text-white">
                                    {{ item.name }}
                                </div>
                                <div class="text-[10px] text-purple-200">
                                    {{ item.total_points }} Poin • {{ item.total_proposals }} Usulan
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Filters Bar -->
            <div class="bg-white dark:bg-slate-800 p-4 rounded-2xl border border-slate-200 dark:border-slate-700 shadow-xs flex flex-col md:flex-row items-center gap-4">
                <div class="relative flex-1 w-full">
                    <Search class="w-4 h-4 absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400" />
                    <input
                        v-model="search"
                        @keyup.enter="applyFilters"
                        type="text"
                        placeholder="Cari ide kaizen, nama karyawan, nomor usulan..."
                        class="w-full pl-10 pr-4 py-2 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl text-sm focus:ring-2 focus:ring-blue-500 outline-none text-slate-900 dark:text-white"
                    />
                </div>

                <div class="flex items-center gap-3 w-full md:w-auto">
                    <select
                        v-model="category"
                        @change="applyFilters"
                        class="px-3 py-2 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl text-xs text-slate-700 dark:text-slate-300 outline-none"
                    >
                        <option value="">Semua Kategori</option>
                        <option value="safety">Keselamatan Kerja (K3)</option>
                        <option value="5s">5S / Ringkas Rapi Resik</option>
                        <option value="quality">Mutu & Standar CPOTB</option>
                        <option value="cost">Efisiensi Biaya</option>
                        <option value="productivity">Produktivitas Kerja</option>
                    </select>

                    <select
                        v-model="status"
                        @change="applyFilters"
                        class="px-3 py-2 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl text-xs text-slate-700 dark:text-slate-300 outline-none"
                    >
                        <option value="">Semua Status</option>
                        <option value="submitted">Baru Diajukan</option>
                        <option value="under_review">Sedang Dinilai</option>
                        <option value="implemented">Diimplementasikan</option>
                        <option value="rejected">Ditolak</option>
                    </select>
                </div>
            </div>

            <!-- Table of Kizuki Proposals -->
            <div class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-200 dark:border-slate-700 shadow-xs overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-xs text-left text-slate-600 dark:text-slate-300">
                        <thead class="text-[11px] uppercase bg-slate-50 dark:bg-slate-900/50 text-slate-400 border-b border-slate-200 dark:border-slate-700">
                            <tr>
                                <th class="px-4 py-3">No. Usulan</th>
                                <th class="px-4 py-3">Inisiator</th>
                                <th class="px-4 py-3">Kategori</th>
                                <th class="px-4 py-3">Judul Ide & Perbaikan</th>
                                <th class="px-4 py-3">Skor QCDSM</th>
                                <th class="px-4 py-3">Poin Reward</th>
                                <th class="px-4 py-3">Status</th>
                                <th class="px-4 py-3 text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 dark:divide-slate-700">
                            <tr
                                v-for="kiz in kizukis.data"
                                :key="kiz.id"
                                class="hover:bg-slate-50/80 dark:hover:bg-slate-900/40 transition"
                            >
                                <td class="px-4 py-3.5 font-mono font-semibold text-purple-600">
                                    {{ kiz.kizuki_number }}
                                </td>
                                <td class="px-4 py-3.5">
                                    <div class="font-semibold text-slate-900 dark:text-white">
                                        {{ kiz.employee?.user?.name }}
                                    </div>
                                    <div class="text-[10px] text-slate-400">
                                        {{ kiz.department?.name }}
                                    </div>
                                </td>
                                <td class="px-4 py-3.5">
                                    <span class="px-2 py-0.5 rounded text-[10px] font-semibold bg-purple-50 text-purple-700 uppercase">
                                        {{ kiz.category }}
                                    </span>
                                </td>
                                <td class="px-4 py-3.5 max-w-sm">
                                    <div class="font-semibold text-slate-900 dark:text-white truncate">
                                        {{ kiz.title }}
                                    </div>
                                    <div class="text-[11px] text-slate-400 line-clamp-1">
                                        {{ kiz.proposed_idea }}
                                    </div>
                                </td>
                                <td class="px-4 py-3.5 font-mono">
                                    <span v-if="kiz.quality_score" class="text-xs font-semibold text-emerald-600">
                                        Q:{{ kiz.quality_score }} C:{{ kiz.cost_saving_score }} S:{{ kiz.safety_score }}
                                    </span>
                                    <span v-else class="text-slate-400 italic">Belum Dinilai</span>
                                </td>
                                <td class="px-4 py-3.5 font-bold font-mono text-amber-600">
                                    +{{ kiz.reward_points }} pts
                                </td>
                                <td class="px-4 py-3.5">
                                    <span
                                        :class="[
                                            'px-2 py-0.5 rounded text-[10px] font-semibold',
                                            kiz.status === 'implemented' ? 'bg-emerald-50 text-emerald-700 dark:bg-emerald-950 dark:text-emerald-300' :
                                            kiz.status === 'under_review' ? 'bg-blue-50 text-blue-700 dark:bg-blue-950 dark:text-blue-300' :
                                            kiz.status === 'submitted' ? 'bg-amber-50 text-amber-700 dark:bg-amber-950 dark:text-amber-300' :
                                            'bg-rose-50 text-rose-700 dark:bg-rose-950 dark:text-rose-300',
                                        ]"
                                    >
                                        {{ kiz.status.replace('_', ' ').toUpperCase() }}
                                    </span>
                                </td>
                                <td class="px-4 py-3.5 text-right">
                                    <button
                                        @click="openReviewModal(kiz)"
                                        class="px-2.5 py-1 text-xs font-medium rounded-lg bg-blue-50 text-blue-700 hover:bg-blue-100 transition"
                                    >
                                        Nilai QCDSM
                                    </button>
                                </td>
                            </tr>
                            <tr v-if="kizukis.data.length === 0">
                                <td colspan="8" class="text-center py-10 text-slate-400">
                                    Tidak ada usulan Kaizen Kizuki yang ditemukan.
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Review / QCDSM Scoring Modal -->
            <div
                v-if="isReviewModalOpen"
                class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/50 backdrop-blur-xs"
            >
                <div class="bg-white dark:bg-slate-800 rounded-2xl max-w-lg w-full p-6 shadow-xl border border-slate-200 dark:border-slate-700 space-y-4">
                    <div class="flex items-center justify-between pb-3 border-b border-slate-100 dark:border-slate-700">
                        <div>
                            <h3 class="font-bold text-base text-slate-900 dark:text-white">
                                Penilaian Komite Kaizen: {{ selectedKizuki?.kizuki_number }}
                            </h3>
                            <p class="text-xs text-slate-500">
                                "{{ selectedKizuki?.title }}" oleh {{ selectedKizuki?.employee?.user?.name }}
                            </p>
                        </div>
                        <button @click="isReviewModalOpen = false" class="text-slate-400 hover:text-slate-600">✕</button>
                    </div>

                    <div class="grid grid-cols-2 gap-3 text-xs">
                        <div>
                            <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1">Skor Mutu (Quality 0-100)</label>
                            <input v-model="reviewForm.quality_score" type="number" min="0" max="100" class="w-full px-3 py-1.5 bg-slate-50 dark:bg-slate-900 border rounded-lg" />
                        </div>
                        <div>
                            <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1">Skor Hemat Biaya (Cost 0-100)</label>
                            <input v-model="reviewForm.cost_saving_score" type="number" min="0" max="100" class="w-full px-3 py-1.5 bg-slate-50 dark:bg-slate-900 border rounded-lg" />
                        </div>
                        <div>
                            <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1">Skor Kecepatan (Delivery 0-100)</label>
                            <input v-model="reviewForm.delivery_score" type="number" min="0" max="100" class="w-full px-3 py-1.5 bg-slate-50 dark:bg-slate-900 border rounded-lg" />
                        </div>
                        <div>
                            <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1">Skor K3 (Safety 0-100)</label>
                            <input v-model="reviewForm.safety_score" type="number" min="0" max="100" class="w-full px-3 py-1.5 bg-slate-50 dark:bg-slate-900 border rounded-lg" />
                        </div>
                        <div>
                            <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1">Skor Moral (Morale 0-100)</label>
                            <input v-model="reviewForm.morale_score" type="number" min="0" max="100" class="w-full px-3 py-1.5 bg-slate-50 dark:bg-slate-900 border rounded-lg" />
                        </div>
                        <div>
                            <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1">Poin Reward (Points)</label>
                            <input v-model="reviewForm.reward_points" type="number" min="0" class="w-full px-3 py-1.5 bg-slate-50 dark:bg-slate-900 border rounded-lg font-bold text-amber-600" />
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Status Keputusan</label>
                        <select v-model="reviewForm.status" class="w-full px-3 py-1.5 text-xs bg-slate-50 dark:bg-slate-900 border rounded-lg">
                            <option value="implemented">Implementasikan & Berikan Poin</option>
                            <option value="under_review">Dalam Kajian Lanjutan</option>
                            <option value="rejected">Ditolak</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Catatan Evaluasi Komite</label>
                        <textarea v-model="reviewForm.review_notes" rows="2" placeholder="Tuliskan umpan balik untuk inisiator..." class="w-full px-3 py-1.5 text-xs bg-slate-50 dark:bg-slate-900 border rounded-lg"></textarea>
                    </div>

                    <div class="flex items-center justify-end gap-2 pt-3 border-t border-slate-100 dark:border-slate-700">
                        <button @click="isReviewModalOpen = false" class="px-4 py-1.5 text-xs rounded-lg border text-slate-600">Batal</button>
                        <button @click="submitReview" :disabled="reviewForm.processing" class="px-5 py-1.5 text-xs rounded-lg bg-blue-600 text-white font-semibold">Simpan Penilaian</button>
                    </div>
                </div>
            </div>
        </div>
    </ModuleLayout>
</template>
