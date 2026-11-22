<script setup>
import {
    Plus,
    FileSpreadsheet,
    Download,
    UploadCloud,
    Play,
    Filter,
    Calculator,
    Shield,
    Sparkles,
} from 'lucide-vue-next';

defineProps({
    actions: {
        type: Array,
        default: () => [
            { label: 'Transaksi Baru', icon: 'Plus', action: 'create', primary: true },
            { label: 'Export Excel/PDF', icon: 'Download', action: 'export' },
            { label: 'Import Batch', icon: 'UploadCloud', action: 'import' },
        ],
    },
});

const emit = defineEmits(['action']);

const iconMap = {
    Plus,
    FileSpreadsheet,
    Download,
    UploadCloud,
    Play,
    Filter,
    Calculator,
    Shield,
    Sparkles,
};

function getIcon(name) {
    return iconMap[name] || Plus;
}
</script>

<template>
    <div class="flex flex-wrap items-center gap-2">
        <button
            v-for="(act, idx) in actions"
            :key="idx"
            @click="emit('action', act.action)"
            :class="[
                'px-3 py-1.5 rounded-lg text-xs font-semibold transition duration-150 flex items-center gap-1.5 shadow-2xs',
                act.primary
                    ? 'bg-emerald-600 hover:bg-emerald-700 text-white shadow-emerald-600/20'
                    : 'bg-white dark:bg-slate-800 hover:bg-slate-50 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200 border border-slate-200 dark:border-slate-700',
            ]"
        >
            <component :is="getIcon(act.icon)" class="w-3.5 h-3.5" />
            <span>{{ act.label }}</span>
        </button>
    </div>
</template>
