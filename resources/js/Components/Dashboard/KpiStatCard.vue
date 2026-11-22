<script setup>
import { computed } from 'vue';
import { TrendingUp, TrendingDown, Minus, ArrowUpRight } from 'lucide-vue-next';

const props = defineProps({
    title: {
        type: String,
        required: true,
    },
    value: {
        type: [String, Number],
        required: true,
    },
    subtitle: {
        type: String,
        default: '',
    },
    trend: {
        type: Number, // e.g. +12.4 or -5.2
        default: null,
    },
    trendLabel: {
        type: String,
        default: 'vs periode lalu',
    },
    status: {
        type: String,
        default: 'emerald', // emerald, blue, rose, amber, violet, cyan
    },
    sparklineData: {
        type: Array,
        default: () => [30, 45, 38, 52, 48, 65, 72],
    },
    clickable: {
        type: Boolean,
        default: true,
    },
});

const emit = defineEmits(['filter', 'click']);

const trendType = computed(() => {
    if (props.trend === null || props.trend === undefined) return 'neutral';
    if (props.trend > 0) return 'positive';
    if (props.trend < 0) return 'negative';
    return 'neutral';
});

const colorThemeClasses = computed(() => {
    const map = {
        emerald: {
            border: 'hover:border-emerald-500/80',
            bgGlow: 'from-emerald-500/5 to-transparent',
            badge: 'text-emerald-700 dark:text-emerald-400 bg-emerald-50 dark:bg-emerald-950/50 border-emerald-200 dark:border-emerald-800',
            sparkline: '#10b981',
        },
        rose: {
            border: 'hover:border-rose-500/80',
            bgGlow: 'from-rose-500/5 to-transparent',
            badge: 'text-rose-700 dark:text-rose-400 bg-rose-50 dark:bg-rose-950/50 border-rose-200 dark:border-rose-800',
            sparkline: '#f43f5e',
        },
        blue: {
            border: 'hover:border-blue-500/80',
            bgGlow: 'from-blue-500/5 to-transparent',
            badge: 'text-blue-700 dark:text-blue-400 bg-blue-50 dark:bg-blue-950/50 border-blue-200 dark:border-blue-800',
            sparkline: '#3b82f6',
        },
        amber: {
            border: 'hover:border-amber-500/80',
            bgGlow: 'from-amber-500/5 to-transparent',
            badge: 'text-amber-700 dark:text-amber-400 bg-amber-50 dark:bg-amber-950/50 border-amber-200 dark:border-amber-800',
            sparkline: '#f59e0b',
        },
        violet: {
            border: 'hover:border-violet-500/80',
            bgGlow: 'from-violet-500/5 to-transparent',
            badge: 'text-violet-700 dark:text-violet-400 bg-violet-50 dark:bg-violet-950/50 border-violet-200 dark:border-violet-800',
            sparkline: '#8b5cf6',
        },
        cyan: {
            border: 'hover:border-cyan-500/80',
            bgGlow: 'from-cyan-500/5 to-transparent',
            badge: 'text-cyan-700 dark:text-cyan-400 bg-cyan-50 dark:bg-cyan-950/50 border-cyan-200 dark:border-cyan-800',
            sparkline: '#06b6d4',
        },
    };
    return map[props.status] || map.emerald;
});

// SVG Sparkline path generator
const sparklinePoints = computed(() => {
    const data = props.sparklineData;
    if (!data || data.length < 2) return '';
    const min = Math.min(...data);
    const max = Math.max(...data);
    const range = max - min || 1;
    const width = 80;
    const height = 24;

    return data
        .map((val, idx) => {
            const x = (idx / (data.length - 1)) * width;
            const y = height - ((val - min) / range) * height;
            return `${x},${y}`;
        })
        .join(' ');
});

function handleClick() {
    emit('click', { title: props.title, value: props.value });
    emit('filter', props.title);
}
</script>

<template>
    <div
        @click="handleClick"
        :class="[
            'group relative p-4 rounded-xl border bg-white dark:bg-slate-900 border-slate-200 dark:border-slate-800 shadow-xs transition duration-200 select-none overflow-hidden',
            colorThemeClasses.border,
            clickable ? 'cursor-pointer hover:shadow-md' : '',
        ]"
    >
        <!-- Background Gradient Glow -->
        <div
            :class="['absolute inset-0 bg-gradient-to-br opacity-50 pointer-events-none', colorThemeClasses.bgGlow]"
        />

        <div class="relative z-10 flex items-start justify-between">
            <div class="min-w-0 flex-1">
                <span class="text-xs font-medium text-slate-500 dark:text-slate-400 uppercase tracking-wider block truncate">
                    {{ title }}
                </span>
                <div class="text-xl sm:text-2xl font-bold text-slate-900 dark:text-white mt-1 tracking-tight truncate">
                    {{ value }}
                </div>
            </div>

            <!-- Mini Sparkline SVG -->
            <div class="shrink-0 flex items-center justify-end pl-2">
                <svg width="72" height="24" class="overflow-visible">
                    <polyline
                        fill="none"
                        :stroke="colorThemeClasses.sparkline"
                        stroke-width="2"
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        :points="sparklinePoints"
                    />
                </svg>
            </div>
        </div>

        <div class="relative z-10 mt-3 pt-2.5 border-t border-slate-100 dark:border-slate-800/80 flex items-center justify-between text-xs">
            <!-- Trend badge -->
            <div class="flex items-center gap-1.5 min-w-0">
                <span
                    v-if="trend !== null"
                    :class="[
                        'inline-flex items-center gap-0.5 px-1.5 py-0.5 rounded text-[11px] font-semibold border',
                        trendType === 'positive'
                            ? 'text-emerald-700 dark:text-emerald-400 bg-emerald-50 dark:bg-emerald-950/40 border-emerald-200 dark:border-emerald-800'
                            : trendType === 'negative'
                            ? 'text-rose-700 dark:text-rose-400 bg-rose-50 dark:bg-rose-950/40 border-rose-200 dark:border-rose-800'
                            : 'text-slate-600 dark:text-slate-400 bg-slate-50 dark:bg-slate-800 border-slate-200 dark:border-slate-700',
                    ]"
                >
                    <component
                        :is="trendType === 'positive' ? TrendingUp : trendType === 'negative' ? TrendingDown : Minus"
                        class="w-3 h-3"
                    />
                    {{ trend > 0 ? `+${trend}%` : `${trend}%` }}
                </span>

                <span class="text-[11px] text-slate-400 dark:text-slate-500 truncate">
                    {{ subtitle || trendLabel }}
                </span>
            </div>

            <span
                v-if="clickable"
                class="text-slate-300 dark:text-slate-600 group-hover:text-slate-600 dark:group-hover:text-slate-300 transition shrink-0 ml-1"
                title="Klik untuk filter data"
            >
                <ArrowUpRight class="w-3.5 h-3.5" />
            </span>
        </div>
    </div>
</template>
