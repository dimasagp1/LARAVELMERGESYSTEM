<script setup>
import { ref, computed } from 'vue';
import { useThemeStore } from '@/Stores/themeStore';
import { Download, RefreshCw, Maximize2 } from 'lucide-vue-next';

const props = defineProps({
    title: {
        type: String,
        required: true,
    },
    subtitle: {
        type: String,
        default: '',
    },
    type: {
        type: String,
        default: 'area', // area, line, bar, donut, radialBar, heatmap
    },
    height: {
        type: [Number, String],
        default: 300,
    },
    options: {
        type: Object,
        default: () => ({}),
    },
    series: {
        type: Array,
        required: true,
    },
    periods: {
        type: Array,
        default: () => ['Hari Ini', 'MTD', 'YTD', '12 Bulan'],
    },
    selectedPeriod: {
        type: String,
        default: 'MTD',
    },
});

const emit = defineEmits(['period-change', 'drill-down', 'refresh']);

const themeStore = useThemeStore();
const currentPeriod = ref(props.selectedPeriod);

const mergedOptions = computed(() => {
    const isDark = themeStore.isDark;

    const baseOptions = {
        chart: {
            fontFamily: 'inherit',
            toolbar: { show: false },
            events: {
                dataPointSelection: (event, chartContext, config) => {
                    emit('drill-down', {
                        seriesIndex: config.seriesIndex,
                        dataPointIndex: config.dataPointIndex,
                        w: config.w,
                    });
                },
            },
        },
        theme: {
            mode: isDark ? 'dark' : 'light',
        },
        tooltip: {
            theme: isDark ? 'dark' : 'light',
        },
        grid: {
            borderColor: isDark ? '#334155' : '#f1f5f9',
        },
    };

    return {
        ...baseOptions,
        ...props.options,
        chart: {
            ...baseOptions.chart,
            ...(props.options?.chart || {}),
        },
    };
});

function handlePeriodChange(p) {
    currentPeriod.value = p;
    emit('period-change', p);
}
</script>

<template>
    <div class="bg-white dark:bg-slate-900 rounded-xl border border-slate-200 dark:border-slate-800 p-5 shadow-xs transition duration-200">
        <!-- Card Header -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-4">
            <div>
                <h3 class="font-bold text-slate-900 dark:text-white text-sm">
                    {{ title }}
                </h3>
                <p v-if="subtitle" class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">
                    {{ subtitle }}
                </p>
            </div>

            <!-- Period Switcher & Actions -->
            <div class="flex items-center gap-2">
                <div class="inline-flex rounded-lg bg-slate-100 dark:bg-slate-800 p-0.5 text-xs font-medium">
                    <button
                        v-for="period in periods"
                        :key="period"
                        @click="handlePeriodChange(period)"
                        :class="[
                            'px-2.5 py-1 rounded-md transition duration-150',
                            currentPeriod === period
                                ? 'bg-white dark:bg-slate-700 text-slate-900 dark:text-white font-semibold shadow-xs'
                                : 'text-slate-500 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white',
                        ]"
                    >
                        {{ period }}
                    </button>
                </div>

                <button
                    @click="emit('refresh')"
                    class="p-1.5 rounded-lg border border-slate-200 dark:border-slate-700 text-slate-400 hover:text-slate-700 dark:hover:text-slate-200 hover:bg-slate-50 dark:hover:bg-slate-800 transition"
                    title="Refresh Chart Data"
                >
                    <RefreshCw class="w-3.5 h-3.5" />
                </button>
            </div>
        </div>

        <!-- ApexChart Element -->
        <div class="w-full">
            <apexchart
                :type="type"
                :height="height"
                :options="mergedOptions"
                :series="series"
            />
        </div>
    </div>
</template>
