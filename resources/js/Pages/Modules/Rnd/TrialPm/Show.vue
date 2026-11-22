<script setup>
import { ref, computed } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import ModuleLayout from '@/Layouts/ModuleLayout.vue';
import {
    Boxes,
    ArrowLeft,
    CheckCircle2,
    Clock,
    Lock,
    ShieldCheck,
    Check,
    AlertCircle,
} from 'lucide-vue-next';

const props = defineProps({
    module: Object,
    trialPm: Object,
});

const requiredDepts = ['R&D', 'QC', 'Production', 'Engineering'];

const approvedCount = computed(() => {
    return props.trialPm.approvals?.filter((a) => a.status === 'approved').length || 0;
});

const isFullyApprovedByDepts = computed(() => {
    return approvedCount.value === 4;
});

function signDepartment(deptName) {
    if (confirm(`Bubuhkan paraf atas nama Departemen ${deptName}?`)) {
        router.post(`/rnd/trial-pms/${props.trialPm.id}/sign`, {
            department_name: deptName,
            status: 'approved',
            notes: 'Spesifikasi kemasan memenuhi syarat uji mesin pabrik.',
        });
    }
}

function approveFinalOM() {
    if (confirm(`Sahkan Trial PM ${props.trialPm.code} secara final?`)) {
        router.post(`/rnd/trial-pms/${props.trialPm.id}/approve-final`);
    }
}
</script>

<template>
    <ModuleLayout
        :title="`${trialPm.code} - Trial PM`"
        :module-slug="'rnd'"
        :module-name="'R&D (NPD Management)'"
        :color-theme="'cyan'"
        :menus="module?.menus || []"
        :breadcrumbs="['Trial Packaging Material', trialPm.code]"
    >
        <Head :title="`${trialPm.code} - ${trialPm.product?.name}`" />

        <div class="max-w-4xl mx-auto space-y-6">
            <!-- Header Bar -->
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <Link
                        href="/rnd/trial-pms"
                        class="p-2 rounded-xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 text-slate-500 hover:text-slate-900 dark:hover:text-white transition shadow-2xs"
                    >
                        <ArrowLeft class="w-4 h-4" />
                    </Link>
                    <div>
                        <div class="flex items-center gap-2">
                            <h1 class="text-xl font-bold text-slate-900 dark:text-white tracking-tight">
                                {{ trialPm.code }}
                            </h1>
                            <span
                                :class="[
                                    'px-2 py-0.5 rounded text-[10px] font-bold uppercase tracking-wider border',
                                    trialPm.status === 'approved'
                                        ? 'bg-emerald-50 text-emerald-700 border-emerald-200'
                                        : 'bg-amber-50 text-amber-700 border-amber-200',
                                ]"
                            >
                                {{ trialPm.status }}
                            </span>
                        </div>
                        <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">
                            Produk: {{ trialPm.product?.name }} &bull; Vendor: {{ trialPm.vendor?.name }}
                        </p>
                    </div>
                </div>

                <!-- OM Final Approval Button -->
                <div>
                    <button
                        v-if="trialPm.status !== 'approved'"
                        @click="approveFinalOM"
                        :disabled="!isFullyApprovedByDepts"
                        :class="[
                            'px-4 py-2 rounded-xl font-semibold text-xs flex items-center gap-1.5 transition shadow-sm',
                            isFullyApprovedByDepts
                                ? 'bg-emerald-600 hover:bg-emerald-500 text-white cursor-pointer shadow-emerald-600/20'
                                : 'bg-slate-200 dark:bg-slate-800 text-slate-400 cursor-not-allowed',
                        ]"
                        :title="!isFullyApprovedByDepts ? 'Menunggu paraf seluruh 4 departemen' : 'Sahkan Trial PM'"
                    >
                        <Lock v-if="!isFullyApprovedByDepts" class="w-4 h-4" />
                        <CheckCircle2 v-else class="w-4 h-4" />
                        <span>Sahkan Final (OM)</span>
                    </button>
                </div>
            </div>

            <!-- COLLECTIVE 4-DEPARTMENT CONSENSUS CARD -->
            <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 shadow-xs p-6 space-y-4">
                <div class="flex items-center justify-between border-b border-slate-100 dark:border-slate-800 pb-3">
                    <div>
                        <h2 class="text-xs font-bold uppercase tracking-wider text-slate-900 dark:text-white">
                            Matriks Verifikasi Kolektif 4 Departemen
                        </h2>
                        <p class="text-[11px] text-slate-500">
                            Seluruh 4 departemen wajib membubuhkan paraf sebelum Operational Manager dapat mengesahkan bahan kemas ini.
                        </p>
                    </div>
                    <span class="text-xs font-mono font-bold px-2 py-0.5 rounded bg-cyan-100 dark:bg-cyan-950 text-cyan-800 dark:text-cyan-300">
                        {{ approvedCount }} / 4 Departemen Setuju
                    </span>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                    <div
                        v-for="dept in requiredDepts"
                        :key="dept"
                        class="border rounded-xl p-4 flex flex-col justify-between space-y-3"
                        :class="[
                            trialPm.approvals?.find((a) => a.department_name === dept && a.status === 'approved')
                                ? 'bg-emerald-50/50 dark:bg-emerald-950/20 border-emerald-300 dark:border-emerald-800'
                                : 'bg-slate-50 dark:bg-slate-800/40 border-slate-200 dark:border-slate-700',
                        ]"
                    >
                        <div>
                            <div class="flex items-center justify-between">
                                <span class="font-bold text-xs text-slate-900 dark:text-white uppercase">{{ dept }}</span>
                                <span
                                    v-if="trialPm.approvals?.find((a) => a.department_name === dept && a.status === 'approved')"
                                    class="text-emerald-600 dark:text-emerald-400 font-bold text-xs flex items-center gap-0.5"
                                >
                                    <Check class="w-3.5 h-3.5" /> Disetujui
                                </span>
                                <span v-else class="text-slate-400 text-[10px] font-mono">Pending</span>
                            </div>

                            <p class="text-[10px] text-slate-500 mt-1">
                                {{
                                    dept === 'R&D'
                                        ? 'Kompatibilitas Formula'
                                        : dept === 'QC'
                                        ? 'Standar Mutu & Kebocoran'
                                        : dept === 'Production'
                                        ? 'Kelancaran Line Mesin'
                                        : 'Tekanan & Suhu Sealer'
                                }}
                            </p>
                        </div>

                        <div>
                            <button
                                v-if="!trialPm.approvals?.find((a) => a.department_name === dept && a.status === 'approved')"
                                @click="signDepartment(dept)"
                                class="w-full px-2.5 py-1.5 rounded-lg bg-cyan-600 hover:bg-cyan-500 text-white text-[11px] font-semibold transition"
                            >
                                Paraf {{ dept }}
                            </button>
                            <div v-else class="text-[10px] text-slate-400 font-mono">
                                Disetujui &bull; {{ trialPm.approvals.find((a) => a.department_name === dept)?.user?.name || 'Perwakilan Dept' }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Specs & Machine Test Results -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 p-5 space-y-3 text-xs">
                    <h3 class="font-bold text-slate-900 dark:text-white uppercase tracking-wider text-[11px]">
                        Spesifikasi Material Kemasan
                    </h3>
                    <div class="space-y-1.5 divide-y divide-slate-100 dark:divide-slate-800 text-slate-600 dark:text-slate-300">
                        <div class="pt-1 flex justify-between">
                            <span class="text-slate-400">Material:</span>
                            <span class="font-semibold">{{ trialPm.packaging_specs_json?.material || 'Aluminium Foil Laminated' }}</span>
                        </div>
                        <div class="pt-1 flex justify-between">
                            <span class="text-slate-400">Ketebalan:</span>
                            <span class="font-mono">{{ trialPm.packaging_specs_json?.thickness_micron || 65 }} mikron</span>
                        </div>
                        <div class="pt-1 flex justify-between">
                            <span class="text-slate-400">Dimensi:</span>
                            <span class="font-mono">{{ trialPm.packaging_specs_json?.dimension_mm || '80 x 120 mm' }}</span>
                        </div>
                    </div>
                </div>

                <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 p-5 space-y-3 text-xs">
                    <h3 class="font-bold text-slate-900 dark:text-white uppercase tracking-wider text-[11px]">
                        Hasil Uji Mesin Pengemas
                    </h3>
                    <div class="space-y-1.5 divide-y divide-slate-100 dark:divide-slate-800 text-slate-600 dark:text-slate-300">
                        <div class="pt-1 flex justify-between">
                            <span class="text-slate-400">Kebocoran (Dye Test):</span>
                            <span class="font-semibold text-emerald-600 dark:text-emerald-400">{{ trialPm.leakage_test_result }}</span>
                        </div>
                        <div class="pt-1 flex justify-between">
                            <span class="text-slate-400">Kekuatan Rekat:</span>
                            <span>{{ trialPm.seal_strength_result }}</span>
                        </div>
                        <div class="pt-1 flex justify-between">
                            <span class="text-slate-400">Barcode Readability:</span>
                            <span class="font-mono font-bold">{{ trialPm.barcode_readability }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </ModuleLayout>
</template>
