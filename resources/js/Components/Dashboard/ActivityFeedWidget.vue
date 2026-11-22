<script setup>
import { Activity, Clock, ShieldCheck, CheckCircle2, User } from 'lucide-vue-next';

defineProps({
    title: {
        type: String,
        default: 'Aktivitas & Audit Trail CPOB',
    },
    activities: {
        type: Array,
        default: () => [
            {
                user: 'Super Administrator',
                action: 'Memvalidasi Jurnal Penyesuaian AJE-2026-042',
                module: 'Finance',
                theme: 'emerald',
                time: '12 menit lalu',
            },
            {
                user: 'Dr. Rina Wati (QA Spv)',
                action: 'Menyetujui Release Bahan Baku Curcuma #RM-091',
                module: 'Quality',
                theme: 'teal',
                time: '35 menit lalu',
            },
            {
                user: 'Budi Santoso',
                action: 'Membuat Purchase Request PR-2026-0089',
                module: 'Procurement',
                theme: 'violet',
                time: '1 jam lalu',
            },
            {
                user: 'Joko Susilo',
                action: 'Presensi Biometrik Wajah Lini 2 Ekstraksi (Sukoharjo)',
                module: 'HRIS',
                theme: 'blue',
                time: '2 jam lalu',
            },
        ],
    },
});

function getBadgeTheme(theme) {
    const map = {
        emerald: 'bg-emerald-50 dark:bg-emerald-950/40 text-emerald-700 dark:text-emerald-400 border-emerald-200 dark:border-emerald-800',
        teal: 'bg-teal-50 dark:bg-teal-950/40 text-teal-700 dark:text-teal-400 border-teal-200 dark:border-teal-800',
        violet: 'bg-violet-50 dark:bg-violet-950/40 text-violet-700 dark:text-violet-400 border-violet-200 dark:border-violet-800',
        blue: 'bg-blue-50 dark:bg-blue-950/40 text-blue-700 dark:text-blue-400 border-blue-200 dark:border-blue-800',
        amber: 'bg-amber-50 dark:bg-amber-950/40 text-amber-700 dark:text-amber-400 border-amber-200 dark:border-amber-800',
        rose: 'bg-rose-50 dark:bg-rose-950/40 text-rose-700 dark:text-rose-400 border-rose-200 dark:border-rose-800',
    };
    return map[theme] || 'bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 border-slate-200 dark:border-slate-700';
}
</script>

<template>
    <div class="bg-white dark:bg-slate-900 rounded-xl border border-slate-200 dark:border-slate-800 p-5 shadow-xs">
        <div class="flex items-center justify-between pb-3 border-b border-slate-100 dark:border-slate-800 mb-4">
            <div class="flex items-center gap-2">
                <Activity class="w-4 h-4 text-emerald-600 dark:text-emerald-400" />
                <h3 class="font-bold text-slate-900 dark:text-white text-sm">
                    {{ title }}
                </h3>
            </div>
            <span class="text-[11px] font-mono text-slate-400 dark:text-slate-500">
                Unalterable Log (21 CFR Part 11)
            </span>
        </div>

        <div class="relative pl-4 space-y-4 before:absolute before:left-1.5 before:top-2 before:bottom-2 before:w-0.5 before:bg-slate-200 dark:before:bg-slate-800">
            <div
                v-for="(act, idx) in activities"
                :key="idx"
                class="relative text-xs group"
            >
                <div class="absolute -left-4 top-1 w-2.5 h-2.5 rounded-full bg-emerald-500 ring-4 ring-white dark:ring-slate-900" />

                <div class="flex items-center justify-between gap-2">
                    <span class="font-semibold text-slate-800 dark:text-slate-200">
                        {{ act.user }}
                    </span>
                    <span :class="['text-[10px] px-1.5 py-0.2 rounded font-mono border font-medium', getBadgeTheme(act.theme)]">
                        {{ act.module }}
                    </span>
                </div>

                <p class="text-[11px] text-slate-600 dark:text-slate-400 mt-0.5 leading-relaxed">
                    {{ act.action }}
                </p>

                <div class="text-[10px] text-slate-400 dark:text-slate-500 mt-1 flex items-center gap-1">
                    <Clock class="w-2.5 h-2.5" />
                    {{ act.time }}
                </div>
            </div>
        </div>
    </div>
</template>
