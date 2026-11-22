<script setup>
import { ref, computed, watch, onMounted, onUnmounted, nextTick } from 'vue';
import { router } from '@inertiajs/vue3';
import {
    Search,
    X,
    Folder,
    FileText,
    ShoppingCart,
    Factory,
    AlertOctagon,
    FlaskConical,
    Users,
    Boxes,
    Wrench,
    CornerDownLeft,
    Command,
    Landmark,
    Building2,
    ShieldCheck,
    Truck,
} from 'lucide-vue-next';

const props = defineProps({
    show: Boolean,
    items: {
        type: Array,
        default: () => [],
    },
});

const emit = defineEmits(['close']);

const query = ref('');
const selectedIndex = ref(0);
const inputRef = ref(null);

const iconMap = {
    Landmark,
    Building2,
    Users,
    ShoppingCart,
    Factory,
    ShieldCheck,
    FlaskConical,
    Truck,
    Folder,
    FileText,
    Boxes,
    Wrench,
    AlertOctagon,
};

function getIcon(name) {
    return iconMap[name] || Folder;
}

const filteredItems = computed(() => {
    const q = query.value.toLowerCase().trim();
    if (!q) {
        return props.items.slice(0, 15);
    }
    return props.items.filter((item) => {
        return (
            item.title.toLowerCase().includes(q) ||
            (item.subtitle && item.subtitle.toLowerCase().includes(q)) ||
            (item.category && item.category.toLowerCase().includes(q))
        );
    });
});

watch(
    () => props.show,
    (val) => {
        if (val) {
            query.value = '';
            selectedIndex.value = 0;
            nextTick(() => {
                inputRef.value?.focus();
            });
        }
    }
);

watch(filteredItems, () => {
    selectedIndex.value = 0;
});

function handleKeydown(e) {
    if (!props.show) return;

    if (e.key === 'ArrowDown') {
        e.preventDefault();
        if (selectedIndex.value < filteredItems.value.length - 1) {
            selectedIndex.value++;
        }
    } else if (e.key === 'ArrowUp') {
        e.preventDefault();
        if (selectedIndex.value > 0) {
            selectedIndex.value--;
        }
    } else if (e.key === 'Enter') {
        e.preventDefault();
        const selected = filteredItems.value[selectedIndex.value];
        if (selected) {
            selectItem(selected);
        }
    } else if (e.key === 'Escape') {
        emit('close');
    }
}

function selectItem(item) {
    emit('close');
    if (item.url) {
        router.visit(item.url);
    }
}

function handleGlobalKeydown(e) {
    if ((e.ctrlKey || e.metaKey) && e.key.toLowerCase() === 'k') {
        e.preventDefault();
        if (props.show) {
            emit('close');
        } else {
            // Emitting to open will be handled by parent or local state
        }
    }
}

onMounted(() => {
    window.addEventListener('keydown', handleKeydown);
});

onUnmounted(() => {
    window.removeEventListener('keydown', handleKeydown);
});
</script>

<template>
    <div
        v-if="show"
        class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 dark:bg-black/80 backdrop-blur-sm flex items-start justify-center pt-20 p-4"
        @click.self="emit('close')"
    >
        <div
            class="relative w-full max-w-2xl bg-white dark:bg-slate-900 rounded-2xl shadow-2xl border border-slate-200 dark:border-slate-800 overflow-hidden transform transition-all text-slate-800 dark:text-slate-100"
        >
            <!-- Search Input Bar -->
            <div class="flex items-center gap-3 px-4 py-3.5 border-b border-slate-200 dark:border-slate-800">
                <Search class="w-5 h-5 text-slate-400 dark:text-slate-500 shrink-0" />
                <input
                    ref="inputRef"
                    v-model="query"
                    type="text"
                    placeholder="Ketik modul, sub-menu, atau nomor dokumen (PR, PO, Batch, Deviasi)..."
                    class="w-full bg-transparent border-0 outline-hidden focus:ring-0 text-sm placeholder:text-slate-400 dark:placeholder:text-slate-500 text-slate-900 dark:text-white"
                />
                <div class="flex items-center gap-1.5 shrink-0">
                    <span class="text-[11px] font-mono px-1.5 py-0.5 rounded bg-slate-100 dark:bg-slate-800 text-slate-500 dark:text-slate-400 border border-slate-200 dark:border-slate-700">
                        ESC
                    </span>
                    <button
                        @click="emit('close')"
                        class="p-1 text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 rounded"
                    >
                        <X class="w-4 h-4" />
                    </button>
                </div>
            </div>

            <!-- Search Results List -->
            <div class="max-h-[60vh] overflow-y-auto p-2 space-y-1">
                <div
                    v-if="filteredItems.length === 0"
                    class="py-12 text-center text-slate-400 dark:text-slate-500 text-sm"
                >
                    Tidak ditemukan hasil untuk "<strong class="text-slate-600 dark:text-slate-300">{{ query }}</strong>"
                </div>

                <button
                    v-for="(item, idx) in filteredItems"
                    :key="idx"
                    @click="selectItem(item)"
                    @mouseenter="selectedIndex = idx"
                    :class="[
                        'w-full flex items-center justify-between gap-3 px-3.5 py-2.5 rounded-xl text-left text-xs transition duration-150',
                        selectedIndex === idx
                            ? 'bg-emerald-50 dark:bg-emerald-950/40 text-emerald-950 dark:text-emerald-200'
                            : 'hover:bg-slate-50 dark:hover:bg-slate-800/60 text-slate-700 dark:text-slate-300',
                    ]"
                >
                    <div class="flex items-center gap-3 min-w-0">
                        <div
                            :class="[
                                'w-8 h-8 rounded-lg flex items-center justify-center shrink-0 border',
                                selectedIndex === idx
                                    ? 'bg-emerald-600 text-white border-emerald-600'
                                    : 'bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-400 border-slate-200 dark:border-slate-700',
                            ]"
                        >
                            <component :is="getIcon(item.icon)" class="w-4 h-4" />
                        </div>

                        <div class="min-w-0">
                            <div class="flex items-center gap-2">
                                <span class="font-semibold truncate text-slate-900 dark:text-white">
                                    {{ item.title }}
                                </span>
                                <span
                                    class="text-[10px] px-1.5 py-0.5 rounded font-mono font-medium bg-slate-100 dark:bg-slate-800 text-slate-500 dark:text-slate-400 shrink-0"
                                >
                                    {{ item.category }}
                                </span>
                            </div>
                            <p class="text-[11px] text-slate-500 dark:text-slate-400 truncate mt-0.5">
                                {{ item.subtitle }}
                            </p>
                        </div>
                    </div>

                    <div v-if="selectedIndex === idx" class="shrink-0 flex items-center text-emerald-600 dark:text-emerald-400 gap-1 text-[11px]">
                        <span>Buka</span>
                        <CornerDownLeft class="w-3.5 h-3.5" />
                    </div>
                </button>
            </div>

            <!-- Footer Keyboard Hints -->
            <div
                class="px-4 py-2.5 bg-slate-50 dark:bg-slate-950/60 border-t border-slate-200 dark:border-slate-800 flex items-center justify-between text-[11px] text-slate-500 dark:text-slate-400"
            >
                <div class="flex items-center gap-3">
                    <span class="flex items-center gap-1">
                        <span class="px-1 py-0.5 rounded bg-slate-200 dark:bg-slate-800 font-mono text-[10px]">↑</span>
                        <span class="px-1 py-0.5 rounded bg-slate-200 dark:bg-slate-800 font-mono text-[10px]">↓</span>
                        Navigasi
                    </span>
                    <span class="flex items-center gap-1">
                        <span class="px-1.5 py-0.5 rounded bg-slate-200 dark:bg-slate-800 font-mono text-[10px]">Enter</span>
                        Pilih
                    </span>
                </div>
                <span class="font-mono text-[10px]">HerbaTech Unified Search</span>
            </div>
        </div>
    </div>
</template>
