<script setup>
import { ref, computed } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { useDeveloperModeStore } from '@/Stores/developerModeStore';
import { useThemeStore } from '@/Stores/themeStore';
import GlobalCommandPalette from '@/Components/GlobalCommandPalette.vue';
import DeveloperModeOverlay from '@/Components/DeveloperModeOverlay.vue';
import NotificationCenterDropdown from '@/Components/NotificationCenterDropdown.vue';
import UserDropdownMenu from '@/Components/UserDropdownMenu.vue';
import {
    LayoutGrid,
    Search,
    Pin,
    Sun,
    Moon,
    Terminal,
    Bell,
    ArrowUpRight,
    GripVertical,
    Clock,
    AlertCircle,
    CheckCircle2,
    Sparkles,
    Check,
    ChevronRight,
    Landmark,
    Building2,
    Users,
    ShoppingCart,
    Factory,
    ShieldCheck,
    FlaskConical,
    Truck,
    Folder,
    Layers,
    FileCheck,
    Settings,
} from 'lucide-vue-next';

const props = defineProps({
    modules: {
        type: Array,
        default: () => [],
    },
    pendingApprovals: {
        type: Array,
        default: () => [],
    },
    searchItems: {
        type: Array,
        default: () => [],
    },
    stats: {
        type: Object,
        default: () => ({}),
    },
});

const devStore = useDeveloperModeStore();
const themeStore = useThemeStore();

const showPalette = ref(false);
const localModules = ref([...props.modules]);
const draggedIndex = ref(null);
const isReordering = ref(false);

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
};

function getIcon(name) {
    return iconMap[name] || Folder;
}

const pinnedModules = computed(() => {
    return localModules.value.filter((m) => m.is_pinned);
});

const unpinnedModules = computed(() => {
    return localModules.value.filter((m) => !m.is_pinned);
});

function getThemeStyles(theme) {
    const map = {
        emerald: {
            cardBorder: 'hover:border-emerald-500/80',
            iconBg: 'bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 group-hover:bg-emerald-600 group-hover:text-white',
            badgeBg: 'bg-emerald-500 text-white',
            accent: 'text-emerald-600 dark:text-emerald-400',
        },
        amber: {
            cardBorder: 'hover:border-amber-500/80',
            iconBg: 'bg-amber-500/10 text-amber-600 dark:text-amber-400 group-hover:bg-amber-600 group-hover:text-white',
            badgeBg: 'bg-amber-500 text-white',
            accent: 'text-amber-600 dark:text-amber-400',
        },
        blue: {
            cardBorder: 'hover:border-blue-500/80',
            iconBg: 'bg-blue-500/10 text-blue-600 dark:text-blue-400 group-hover:bg-blue-600 group-hover:text-white',
            badgeBg: 'bg-blue-500 text-white',
            accent: 'text-blue-600 dark:text-blue-400',
        },
        violet: {
            cardBorder: 'hover:border-violet-500/80',
            iconBg: 'bg-violet-500/10 text-violet-600 dark:text-violet-400 group-hover:bg-violet-600 group-hover:text-white',
            badgeBg: 'bg-violet-500 text-white',
            accent: 'text-violet-600 dark:text-violet-400',
        },
        rose: {
            cardBorder: 'hover:border-rose-500/80',
            iconBg: 'bg-rose-500/10 text-rose-600 dark:text-rose-400 group-hover:bg-rose-600 group-hover:text-white',
            badgeBg: 'bg-rose-500 text-white',
            accent: 'text-rose-600 dark:text-rose-400',
        },
        teal: {
            cardBorder: 'hover:border-teal-500/80',
            iconBg: 'bg-teal-500/10 text-teal-600 dark:text-teal-400 group-hover:bg-teal-600 group-hover:text-white',
            badgeBg: 'bg-teal-500 text-white',
            accent: 'text-teal-600 dark:text-teal-400',
        },
        cyan: {
            cardBorder: 'hover:border-cyan-500/80',
            iconBg: 'bg-cyan-500/10 text-cyan-600 dark:text-cyan-400 group-hover:bg-cyan-600 group-hover:text-white',
            badgeBg: 'bg-cyan-500 text-white',
            accent: 'text-cyan-600 dark:text-cyan-400',
        },
        indigo: {
            cardBorder: 'hover:border-indigo-500/80',
            iconBg: 'bg-indigo-500/10 text-indigo-600 dark:text-indigo-400 group-hover:bg-indigo-600 group-hover:text-white',
            badgeBg: 'bg-indigo-500 text-white',
            accent: 'text-indigo-600 dark:text-indigo-400',
        },
    };
    return map[theme] || map.emerald;
}

// Pin Toggle
function togglePin(module, event) {
    event.preventDefault();
    event.stopPropagation();

    module.is_pinned = !module.is_pinned;

    router.post(
        `/modules/${module.id}/toggle-pin`,
        {},
        {
            preserveScroll: true,
            preserveState: true,
        }
    );
}

// Drag & Drop Handlers
function onDragStart(index, event) {
    draggedIndex.value = index;
    isReordering.value = true;
    event.dataTransfer.effectAllowed = 'move';
}

function onDragOver(index, event) {
    event.preventDefault();
    event.dataTransfer.dropEffect = 'move';
}

function onDrop(targetIndex, event) {
    event.preventDefault();
    if (draggedIndex.value === null || draggedIndex.value === targetIndex) {
        draggedIndex.value = null;
        isReordering.value = false;
        return;
    }

    const movedItem = localModules.value.splice(draggedIndex.value, 1)[0];
    localModules.value.splice(targetIndex, 0, movedItem);

    draggedIndex.value = null;
    isReordering.value = false;

    // Persist new order to backend
    const orderedIds = localModules.value.map((m) => m.id);
    router.post(
        '/modules/reorder',
        { module_ids: orderedIds },
        {
            preserveScroll: true,
            preserveState: true,
        }
    );
}

function onDragEnd() {
    draggedIndex.value = null;
    isReordering.value = false;
}

// Global Ctrl + K listener
if (typeof window !== 'undefined') {
    window.addEventListener('keydown', (e) => {
        if ((e.ctrlKey || e.metaKey) && e.key.toLowerCase() === 'k') {
            e.preventDefault();
            showPalette.value = !showPalette.value;
        }
    });
}
</script>

<template>
    <div class="min-h-screen bg-slate-50/70 dark:bg-slate-950 text-slate-800 dark:text-slate-100 flex flex-col font-sans transition-colors duration-200">
        <Head title="App Launcher - Unified Enterprise ERP" />

        <!-- Top Header Navigation -->
        <header class="sticky top-0 z-40 bg-white/95 dark:bg-slate-900/95 backdrop-blur border-b border-slate-200 dark:border-slate-800 shadow-xs">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex items-center justify-between h-16">
                    <!-- Brand -->
                    <div class="flex items-center gap-3">
                        <div class="w-9 h-9 rounded-xl bg-gradient-to-tr from-emerald-600 to-teal-500 text-white flex items-center justify-center font-bold text-sm shadow-md shadow-emerald-600/20">
                            HT
                        </div>
                        <div>
                            <div class="flex items-center gap-2">
                                <span class="font-bold text-slate-900 dark:text-white tracking-tight text-base">HerbaTech ERP</span>
                                <span class="text-[10px] font-bold px-1.5 py-0.5 rounded bg-emerald-100 dark:bg-emerald-950 text-emerald-800 dark:text-emerald-300 uppercase tracking-wider">
                                    App Launcher
                                </span>
                            </div>
                            <p class="text-[10px] text-slate-400 dark:text-slate-500 -mt-0.5">Platform Enterprise Herbal & Farmasi</p>
                        </div>
                    </div>

                    <!-- Search trigger, Dark Mode, Developer Mode, User -->
                    <div class="flex items-center gap-2 sm:gap-3">
                        <!-- Command Palette Trigger Button (Ctrl + K) -->
                        <button
                            @click="showPalette = true"
                            class="flex items-center gap-2.5 px-3 py-1.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800/80 text-slate-500 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-700 transition text-xs shadow-2xs"
                        >
                            <Search class="w-3.5 h-3.5" />
                            <span class="hidden sm:inline">Cari modul, menu, dokumen...</span>
                            <span class="font-mono text-[10px] px-1.5 py-0.5 rounded bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700">
                                Ctrl + K
                            </span>
                        </button>

                        <!-- Notification Center Lonceng -->
                        <NotificationCenterDropdown />

                        <!-- Dark Mode Toggle -->
                        <button
                            @click="themeStore.toggleTheme"
                            class="p-2 rounded-xl text-slate-500 dark:text-slate-400 hover:text-slate-700 dark:hover:text-slate-200 hover:bg-slate-100 dark:hover:bg-slate-800 transition"
                            :title="themeStore.isDark ? 'Beralih ke Light Mode' : 'Beralih ke Dark Mode'"
                        >
                            <Sun v-if="themeStore.isDark" class="w-4 h-4 text-amber-400" />
                            <Moon v-else class="w-4 h-4 text-slate-600" />
                        </button>

                        <!-- Developer Mode Switch -->
                        <button
                            @click="devStore.toggle"
                            :class="[
                                'hidden sm:flex items-center gap-1.5 px-2.5 py-1.5 rounded-lg text-xs font-mono font-medium border transition duration-200',
                                devStore.isEnabled
                                    ? 'bg-emerald-600 text-white border-emerald-600 shadow-sm'
                                    : 'bg-slate-50 dark:bg-slate-800 text-slate-600 dark:text-slate-400 border-slate-200 dark:border-slate-700 hover:bg-slate-100 dark:hover:bg-slate-700',
                            ]"
                            title="Toggle Developer Mode & Live Inspection Overlay"
                        >
                            <Terminal class="w-3.5 h-3.5" />
                            <span class="hidden lg:inline">Dev Mode</span>
                            <span :class="['w-2 h-2 rounded-full', devStore.isEnabled ? 'bg-white animate-ping' : 'bg-slate-300 dark:bg-slate-600']" />
                        </button>

                        <!-- Settings & Admin Links -->
                        <Link
                            href="/settings"
                            class="p-2 rounded-xl text-slate-500 dark:text-slate-400 hover:text-emerald-700 dark:hover:text-emerald-400 hover:bg-slate-100 dark:hover:bg-slate-800 transition"
                            title="Pengaturan Sistem & Branding"
                        >
                            <Settings class="w-4 h-4" />
                        </Link>

                        <Link
                            href="/settings/users"
                            class="p-2 rounded-xl text-slate-500 dark:text-slate-400 hover:text-emerald-700 dark:hover:text-emerald-400 hover:bg-slate-100 dark:hover:bg-slate-800 transition"
                            title="Manajemen Pengguna & Hak Akses"
                        >
                            <Users class="w-4 h-4" />
                        </Link>

                        <!-- User Profile Menu -->
                        <div class="flex items-center pl-2 border-l border-slate-200 dark:border-slate-800">
                            <UserDropdownMenu />
                        </div>
                    </div>
                </div>
            </div>
        </header>

        <!-- Main Body -->
        <main class="flex-1 max-w-7xl w-full mx-auto px-4 sm:px-6 lg:px-8 py-6 space-y-6">
            <!-- Top Bar: My Pending Approvals Summary Banner (Cross-Module) -->
            <div
                v-if="pendingApprovals.length > 0"
                class="rounded-2xl border border-amber-200/80 dark:border-amber-900/60 bg-gradient-to-r from-amber-50 via-orange-50/40 to-white dark:from-amber-950/30 dark:via-slate-900 dark:to-slate-900 p-4 sm:p-5 shadow-xs"
            >
                <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                    <div class="flex items-start gap-3">
                        <div class="w-10 h-10 rounded-xl bg-amber-500 text-white flex items-center justify-center shrink-0 shadow-sm shadow-amber-500/20">
                            <Clock class="w-5 h-5 animate-pulse" />
                        </div>
                        <div>
                            <div class="flex items-center gap-2">
                                <h3 class="font-bold text-slate-900 dark:text-white text-sm">
                                    My Pending Approvals
                                </h3>
                                <span class="px-2 py-0.5 rounded-full bg-amber-500 text-white text-[10px] font-bold">
                                    {{ pendingApprovals.length }} Item Membutuhkan Aksi Anda
                                </span>
                            </div>
                            <p class="text-xs text-slate-600 dark:text-slate-400 mt-0.5">
                                Persetujuan berjenjang lintas modul (Procurement, QMS, R&D, Finance) menunggu verifikasi dan tanda tangan digital Anda.
                            </p>
                        </div>
                    </div>

                    <!-- Items Quick Badges -->
                    <div class="flex flex-wrap items-center gap-2">
                        <div
                            v-for="item in pendingApprovals.slice(0, 3)"
                            :key="item.id"
                            class="flex items-center gap-2 px-3 py-1.5 rounded-xl bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-xs shadow-2xs"
                        >
                            <span class="w-2 h-2 rounded-full bg-amber-500" />
                            <span class="font-semibold text-slate-800 dark:text-slate-200 truncate max-w-[140px] sm:max-w-[200px]">
                                {{ item.title }}
                            </span>
                            <span class="text-[10px] font-mono text-slate-400">({{ item.module_name }})</span>
                        </div>

                        <Link
                            href="/procurement"
                            class="px-3 py-1.5 rounded-xl bg-amber-600 hover:bg-amber-700 text-white text-xs font-semibold transition flex items-center gap-1 shadow-xs"
                        >
                            <span>Buka Inbox</span>
                            <ChevronRight class="w-3.5 h-3.5" />
                        </Link>
                    </div>
                </div>
            </div>

            <!-- Drag-and-Drop & Pin Information Header -->
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2 pt-2">
                <div>
                    <h2 class="text-lg font-bold text-slate-900 dark:text-white flex items-center gap-2">
                        <span>Katalog Modul ERP</span>
                        <span class="text-xs font-mono font-medium px-2 py-0.5 rounded bg-slate-100 dark:bg-slate-800 text-slate-500 dark:text-slate-400">
                            {{ localModules.length }} Modul Terpasang
                        </span>
                    </h2>
                    <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">
                        Tarik (drag) kartu untuk mengatur urutan kerja Anda. Klik ikon pin untuk menyematkan modul di baris favorit.
                    </p>
                </div>

                <div class="flex items-center gap-2 text-xs text-slate-400 dark:text-slate-500">
                    <span class="flex items-center gap-1">
                        <Pin class="w-3.5 h-3.5 text-amber-500 fill-amber-500" />
                        <span>Pinned Modules</span>
                    </span>
                    <span>•</span>
                    <span class="flex items-center gap-1">
                        <GripVertical class="w-3.5 h-3.5" />
                        <span>Drag & Drop Reorder</span>
                    </span>
                </div>
            </div>

            <!-- Pinned Modules Section (if any pinned) -->
            <div v-if="pinnedModules.length > 0" class="space-y-3">
                <div class="flex items-center gap-2 text-xs font-bold uppercase tracking-wider text-amber-600 dark:text-amber-400">
                    <Pin class="w-3.5 h-3.5 fill-amber-500" />
                    <span>Modul Favorit / Pinned</span>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                    <Link
                        v-for="mod in pinnedModules"
                        :key="'pinned-' + mod.id"
                        :href="mod.route_prefix"
                        :class="[
                            'group relative flex flex-col p-5 bg-white dark:bg-slate-900 rounded-2xl border-2 border-amber-300 dark:border-amber-700/60 shadow-xs hover:shadow-lg transition duration-200',
                            getThemeStyles(mod.color_theme).cardBorder,
                        ]"
                    >
                        <!-- Top Icons & Badges -->
                        <div class="flex items-start justify-between mb-3">
                            <div
                                :class="[
                                    'w-14 h-14 rounded-2xl flex items-center justify-center border border-slate-200/60 dark:border-slate-700 transition duration-200 shadow-2xs',
                                    getThemeStyles(mod.color_theme).iconBg,
                                ]"
                            >
                                <component :is="getIcon(mod.icon)" class="w-7 h-7" />
                            </div>

                            <div class="flex items-center gap-1.5">
                                <!-- Notification Badge -->
                                <span
                                    v-if="mod.badge_count > 0"
                                    :class="[
                                        'px-2 py-0.5 rounded-full text-xs font-bold shadow-xs flex items-center gap-1',
                                        getThemeStyles(mod.color_theme).badgeBg,
                                    ]"
                                    title="Notifikasi & Tindakan Tertunda"
                                >
                                    {{ mod.badge_count }}
                                </span>

                                <!-- Unpin Button -->
                                <button
                                    @click="togglePin(mod, $event)"
                                    class="p-1.5 rounded-lg text-amber-500 hover:text-amber-700 dark:hover:text-amber-300 hover:bg-amber-50 dark:hover:bg-amber-950/40 transition"
                                    title="Lepas sematan (Unpin)"
                                >
                                    <Pin class="w-4 h-4 fill-amber-500" />
                                </button>
                            </div>
                        </div>

                        <!-- Module Name & Route -->
                        <div class="mb-1">
                            <h3 class="font-bold text-slate-900 dark:text-white text-base group-hover:text-emerald-600 dark:group-hover:text-emerald-400 transition flex items-center justify-between">
                                <span>{{ mod.name }}</span>
                                <ArrowUpRight class="w-4 h-4 text-slate-300 group-hover:text-emerald-500 group-hover:translate-x-0.5 group-hover:-translate-y-0.5 transition-transform" />
                            </h3>
                            <span class="text-[11px] font-mono text-slate-400 dark:text-slate-500 font-medium">
                                {{ mod.route_prefix }}
                            </span>
                        </div>

                        <p class="text-xs text-slate-500 dark:text-slate-400 line-clamp-2 leading-relaxed mt-1">
                            {{ mod.description }}
                        </p>

                        <!-- Footer -->
                        <div class="mt-4 pt-3 border-t border-slate-100 dark:border-slate-800 flex items-center justify-between text-[11px] text-slate-400">
                            <span class="text-emerald-600 dark:text-emerald-400 font-medium flex items-center gap-1">
                                <CheckCircle2 class="w-3 h-3" /> Siap Digunakan
                            </span>
                            <span class="font-mono">#{{ mod.sort_order }}</span>
                        </div>
                    </Link>
                </div>
            </div>

            <!-- All Modules (Grid with Drag and Drop) -->
            <div class="space-y-3">
                <div class="flex items-center justify-between text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">
                    <span class="flex items-center gap-1.5">
                        <Layers class="w-3.5 h-3.5 text-emerald-600" />
                        <span>Semua Modul Aktif</span>
                    </span>
                    <span class="text-[10px] lowercase font-normal font-mono text-slate-400">
                        drag kartu ikon untuk mengubah posisi
                    </span>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                    <div
                        v-for="(mod, index) in localModules"
                        :key="mod.id"
                        draggable="true"
                        @dragstart="onDragStart(index, $event)"
                        @dragover="onDragOver(index, $event)"
                        @drop="onDrop(index, $event)"
                        @dragend="onDragEnd"
                        :class="[
                            'group relative flex flex-col p-5 bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 shadow-xs hover:shadow-lg transition duration-200 cursor-grab active:cursor-grabbing select-none',
                            getThemeStyles(mod.color_theme).cardBorder,
                            draggedIndex === index ? 'opacity-40 scale-95 border-dashed border-emerald-500' : '',
                        ]"
                    >
                        <Link :href="mod.route_prefix" class="flex-1 flex flex-col">
                            <!-- Top Bar of Card -->
                            <div class="flex items-start justify-between mb-3">
                                <div
                                    :class="[
                                        'w-14 h-14 rounded-2xl flex items-center justify-center border border-slate-200/60 dark:border-slate-700 transition duration-200 shadow-2xs',
                                        getThemeStyles(mod.color_theme).iconBg,
                                    ]"
                                >
                                    <component :is="getIcon(mod.icon)" class="w-7 h-7" />
                                </div>

                                <div class="flex items-center gap-1.5">
                                    <!-- Notification Badge -->
                                    <span
                                        v-if="mod.badge_count > 0"
                                        :class="[
                                            'px-2 py-0.5 rounded-full text-xs font-bold shadow-xs flex items-center gap-1',
                                            getThemeStyles(mod.color_theme).badgeBg,
                                        ]"
                                        title="Jumlah notifikasi & alert kritis"
                                    >
                                        {{ mod.badge_count }}
                                    </span>

                                    <!-- Pin Button -->
                                    <button
                                        @click="togglePin(mod, $event)"
                                        :class="[
                                            'p-1.5 rounded-lg transition',
                                            mod.is_pinned
                                                ? 'text-amber-500 hover:text-amber-700 dark:hover:text-amber-300'
                                                : 'text-slate-300 dark:text-slate-600 hover:text-slate-600 dark:hover:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800',
                                        ]"
                                        :title="mod.is_pinned ? 'Lepas sematan' : 'Sematkan ke favorit'"
                                    >
                                        <Pin :class="['w-4 h-4', mod.is_pinned ? 'fill-amber-500' : '']" />
                                    </button>

                                    <!-- Drag Handle -->
                                    <span class="text-slate-300 dark:text-slate-600 group-hover:text-slate-500 p-1 cursor-grab" title="Tarik untuk urutkan">
                                        <GripVertical class="w-4 h-4" />
                                    </span>
                                </div>
                            </div>

                            <!-- Name & Prefix -->
                            <div class="mb-1">
                                <h3 class="font-bold text-slate-900 dark:text-white text-base group-hover:text-emerald-600 dark:group-hover:text-emerald-400 transition flex items-center justify-between">
                                    <span>{{ mod.name }}</span>
                                    <ArrowUpRight class="w-4 h-4 text-slate-300 group-hover:text-emerald-500 group-hover:translate-x-0.5 group-hover:-translate-y-0.5 transition-transform" />
                                </h3>
                                <span class="text-[11px] font-mono text-slate-400 dark:text-slate-500 font-medium">
                                    {{ mod.route_prefix }}
                                </span>
                            </div>

                            <!-- Description -->
                            <p class="text-xs text-slate-500 dark:text-slate-400 line-clamp-2 leading-relaxed mt-1 flex-1">
                                {{ mod.description }}
                            </p>

                            <!-- Submenu Preview Quick Pills -->
                            <div v-if="mod.menus && mod.menus.length > 0" class="mt-3 flex flex-wrap gap-1">
                                <span
                                    v-for="(sub, sIdx) in mod.menus.slice(0, 3)"
                                    :key="sIdx"
                                    class="text-[10px] px-1.5 py-0.5 rounded bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-400 font-medium truncate max-w-[90px]"
                                >
                                    {{ sub.name }}
                                </span>
                                <span v-if="mod.menus.length > 3" class="text-[10px] text-slate-400 self-center">
                                    +{{ mod.menus.length - 3 }}
                                </span>
                            </div>

                            <!-- Card Footer -->
                            <div class="mt-4 pt-3 border-t border-slate-100 dark:border-slate-800 flex items-center justify-between text-[11px] text-slate-400">
                                <span class="text-emerald-600 dark:text-emerald-400 font-medium flex items-center gap-1">
                                    <CheckCircle2 class="w-3 h-3" /> Active
                                </span>
                                <span class="font-mono">#{{ mod.sort_order }}</span>
                            </div>
                        </Link>
                    </div>
                </div>
            </div>
        </main>

        <!-- Footer -->
        <footer class="bg-white dark:bg-slate-900 border-t border-slate-200 dark:border-slate-800 py-4 mt-auto">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 flex flex-col sm:flex-row items-center justify-between gap-2 text-xs text-slate-500 dark:text-slate-400">
                <div class="flex items-center gap-2">
                    <span class="font-semibold text-slate-700 dark:text-slate-200">HerbaTech Unified Modular ERP</span>
                    <span>•</span>
                    <span>CPOB / CPOTB & ISO 9001 Compliant</span>
                </div>
                <div class="flex items-center gap-3">
                    <span class="font-mono text-[11px] bg-slate-100 dark:bg-slate-800 px-2 py-0.5 rounded">
                        Press <kbd class="font-bold">Ctrl + K</kbd> to search
                    </span>
                    <span>Version 2.0.0</span>
                </div>
            </div>
        </footer>

        <!-- Global Command Palette Modal (Ctrl + K) -->
        <GlobalCommandPalette
            :show="showPalette"
            :items="searchItems"
            @close="showPalette = false"
        />

        <!-- Developer Mode Overlay Bar (PRD 3.1) -->
        <DeveloperModeOverlay />
    </div>
</template>
