<script setup>
import { useDeveloperModeStore } from '@/Stores/developerModeStore';
import { Terminal, Database, Code, Activity, X } from 'lucide-vue-next';

const devStore = useDeveloperModeStore();
</script>

<template>
    <div
        v-if="devStore.isEnabled"
        class="fixed bottom-0 left-0 right-0 z-50 bg-slate-900/95 text-slate-100 border-t border-emerald-500/30 backdrop-blur-md px-4 py-2.5 shadow-2xl flex items-center justify-between text-xs"
    >
        <div class="flex items-center gap-6">
            <div class="flex items-center gap-2 font-mono text-emerald-400">
                <Terminal class="w-4 h-4 animate-pulse" />
                <span class="font-bold tracking-wider uppercase">ERP Developer Mode (PRD 3.1)</span>
            </div>

            <div class="hidden md:flex items-center gap-4 text-slate-300">
                <span class="flex items-center gap-1.5">
                    <Database class="w-3.5 h-3.5 text-blue-400" />
                    <span>Schema: <strong class="text-white">Active (MySQL 8)</strong></span>
                </span>
                <span class="flex items-center gap-1.5">
                    <Code class="w-3.5 h-3.5 text-amber-400" />
                    <span>Architecture: <strong class="text-white">Modular Monolith</strong></span>
                </span>
                <span class="flex items-center gap-1.5">
                    <Activity class="w-3.5 h-3.5 text-rose-400" />
                    <span>Cross-Module Triggers: <strong class="text-white">Enabled</strong></span>
                </span>
            </div>
        </div>

        <div class="flex items-center gap-3">
            <span class="bg-emerald-500/20 text-emerald-300 px-2 py-0.5 rounded border border-emerald-500/40 text-[11px] font-mono">
                Superadmin / Architect
            </span>
            <button
                @click="devStore.toggle"
                class="text-slate-400 hover:text-white p-1 rounded hover:bg-slate-800 transition"
                title="Close Developer Overlay"
            >
                <X class="w-4 h-4" />
            </button>
        </div>
    </div>
</template>
