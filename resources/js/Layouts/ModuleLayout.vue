<script setup>
import { ref, computed } from 'vue';
import { Link, usePage } from '@inertiajs/vue3';
import { useDeveloperModeStore } from '@/Stores/developerModeStore';
import { useThemeStore } from '@/Stores/themeStore';
import GlobalCommandPalette from '@/Components/GlobalCommandPalette.vue';
import AppLauncherModal from '@/Components/AppLauncherModal.vue';
import DeveloperModeOverlay from '@/Components/DeveloperModeOverlay.vue';
import {
    LayoutGrid,
    Search,
    Bell,
    Sun,
    Moon,
    Terminal,
    Menu,
    X,
    ChevronRight,
    LayoutDashboard,
    ListTree,
    PieChart,
    FileSpreadsheet,
    Scale,
    Wrench,
    FileText,
    Building2,
    QrCode,
    Users,
    Clock,
    Calendar,
    Banknote,
    Award,
    FileCheck,
    ShoppingCart,
    DollarSign,
    Building,
    Timer,
    Gauge,
    AlertTriangle,
    GitCommit,
    AlertOctagon,
    CheckCircle2,
    Layers,
    Calculator,
    FlaskConical,
    FileCheck2,
    RefreshCw,
    Boxes,
    ClipboardCheck,
    CheckCircle,
    Warehouse,
    Folder,
    Factory,
    ShieldCheck,
    Truck,
    Landmark,
} from 'lucide-vue-next';

const props = defineProps({
    title: String,
    moduleSlug: {
        type: String,
        required: true,
    },
    moduleName: {
        type: String,
        required: true,
    },
    colorTheme: {
        type: String,
        default: 'emerald',
    },
    menus: {
        type: Array,
        default: () => [],
    },
    breadcrumbs: {
        type: Array,
        default: () => [],
    },
});

const page = usePage();
const devStore = useDeveloperModeStore();
const themeStore = useThemeStore();

const showLauncher = ref(false);
const showPalette = ref(false);
const isSidebarOpen = ref(true);
const isMobileSidebarOpen = ref(false);

const iconMap = {
    LayoutDashboard,
    ListTree,
    PieChart,
    FileSpreadsheet,
    Scale,
    Wrench,
    FileText,
    Building2,
    QrCode,
    Users,
    Clock,
    Calendar,
    Banknote,
    Award,
    FileCheck,
    ShoppingCart,
    DollarSign,
    Building,
    Timer,
    Gauge,
    AlertTriangle,
    GitCommit,
    AlertOctagon,
    CheckCircle2,
    Layers,
    Calculator,
    FlaskConical,
    FileCheck2,
    RefreshCw,
    Boxes,
    ClipboardCheck,
    CheckCircle,
    Warehouse,
    Folder,
    Factory,
    ShieldCheck,
    Truck,
    Landmark,
};

function getMenuIcon(iconName) {
    return iconMap[iconName] || Folder;
}

const themeColorStyles = computed(() => {
    const map = {
        emerald: {
            accent: 'bg-emerald-600 text-white',
            lightBadge: 'bg-emerald-50 text-emerald-800 dark:bg-emerald-950/60 dark:text-emerald-300 border-emerald-200 dark:border-emerald-800',
            activeMenu: 'bg-emerald-50 dark:bg-emerald-950/50 text-emerald-800 dark:text-emerald-300 font-semibold border-l-4 border-emerald-600',
            iconColor: 'text-emerald-600 dark:text-emerald-400',
        },
        amber: {
            accent: 'bg-amber-600 text-white',
            lightBadge: 'bg-amber-50 text-amber-800 dark:bg-amber-950/60 dark:text-amber-300 border-amber-200 dark:border-amber-800',
            activeMenu: 'bg-amber-50 dark:bg-amber-950/50 text-amber-800 dark:text-amber-300 font-semibold border-l-4 border-amber-600',
            iconColor: 'text-amber-600 dark:text-amber-400',
        },
        blue: {
            accent: 'bg-blue-600 text-white',
            lightBadge: 'bg-blue-50 text-blue-800 dark:bg-blue-950/60 dark:text-blue-300 border-blue-200 dark:border-blue-800',
            activeMenu: 'bg-blue-50 dark:bg-blue-950/50 text-blue-800 dark:text-blue-300 font-semibold border-l-4 border-blue-600',
            iconColor: 'text-blue-600 dark:text-blue-400',
        },
        violet: {
            accent: 'bg-violet-600 text-white',
            lightBadge: 'bg-violet-50 text-violet-800 dark:bg-violet-950/60 dark:text-violet-300 border-violet-200 dark:border-violet-800',
            activeMenu: 'bg-violet-50 dark:bg-violet-950/50 text-violet-800 dark:text-violet-300 font-semibold border-l-4 border-violet-600',
            iconColor: 'text-violet-600 dark:text-violet-400',
        },
        rose: {
            accent: 'bg-rose-600 text-white',
            lightBadge: 'bg-rose-50 text-rose-800 dark:bg-rose-950/60 dark:text-rose-300 border-rose-200 dark:border-rose-800',
            activeMenu: 'bg-rose-50 dark:bg-rose-950/50 text-rose-800 dark:text-rose-300 font-semibold border-l-4 border-rose-600',
            iconColor: 'text-rose-600 dark:text-rose-400',
        },
        teal: {
            accent: 'bg-teal-600 text-white',
            lightBadge: 'bg-teal-50 text-teal-800 dark:bg-teal-950/60 dark:text-teal-300 border-teal-200 dark:border-teal-800',
            activeMenu: 'bg-teal-50 dark:bg-teal-950/50 text-teal-800 dark:text-teal-300 font-semibold border-l-4 border-teal-600',
            iconColor: 'text-teal-600 dark:text-teal-400',
        },
        cyan: {
            accent: 'bg-cyan-600 text-white',
            lightBadge: 'bg-cyan-50 text-cyan-800 dark:bg-cyan-950/60 dark:text-cyan-300 border-cyan-200 dark:border-cyan-800',
            activeMenu: 'bg-cyan-50 dark:bg-cyan-950/50 text-cyan-800 dark:text-cyan-300 font-semibold border-l-4 border-cyan-600',
            iconColor: 'text-cyan-600 dark:text-cyan-400',
        },
        indigo: {
            accent: 'bg-indigo-600 text-white',
            lightBadge: 'bg-indigo-50 text-indigo-800 dark:bg-indigo-950/60 dark:text-indigo-300 border-indigo-200 dark:border-indigo-800',
            activeMenu: 'bg-indigo-50 dark:bg-indigo-950/50 text-indigo-800 dark:text-indigo-300 font-semibold border-l-4 border-indigo-600',
            iconColor: 'text-indigo-600 dark:text-indigo-400',
        },
    };
    return map[props.colorTheme] || map.emerald;
});

// Global keyboard shortcut for Ctrl + K
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
    <div class="min-h-screen bg-slate-50/80 dark:bg-slate-950 text-slate-800 dark:text-slate-100 flex flex-col font-sans transition-colors duration-200">
        <!-- Top Workspace Header -->
        <header class="sticky top-0 z-40 bg-white/95 dark:bg-slate-900/95 backdrop-blur border-b border-slate-200 dark:border-slate-800 shadow-xs">
            <div class="px-4 sm:px-6 lg:px-8">
                <div class="flex items-center justify-between h-16">
                    <!-- Left: 9-Dots App Launcher, Module Logo & Breadcrumbs -->
                    <div class="flex items-center gap-3">
                        <!-- 9-Dots Launcher Button to open App Launcher or go back -->
                        <button
                            @click="showLauncher = true"
                            class="p-2 rounded-xl text-slate-600 dark:text-slate-300 hover:text-emerald-700 dark:hover:text-emerald-400 hover:bg-slate-100 dark:hover:bg-slate-800 transition border border-transparent hover:border-slate-200 dark:hover:border-slate-700"
                            title="Buka App Launcher (Semua Modul)"
                        >
                            <LayoutGrid class="w-5 h-5" />
                        </button>

                        <div class="h-6 w-px bg-slate-200 dark:bg-slate-700" />

                        <!-- Module Brand & Breadcrumbs -->
                        <div class="flex items-center gap-2">
                            <Link href="/" class="hidden sm:flex items-center gap-1.5 text-xs font-semibold text-slate-500 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white">
                                <span>ERP</span>
                            </Link>

                            <ChevronRight class="hidden sm:block w-3.5 h-3.5 text-slate-400" />

                            <div class="flex items-center gap-2">
                                <span :class="['px-2 py-0.5 rounded text-xs font-bold border', themeColorStyles.lightBadge]">
                                    {{ moduleName }}
                                </span>
                            </div>

                            <template v-if="breadcrumbs.length > 0">
                                <ChevronRight class="w-3.5 h-3.5 text-slate-400" />
                                <span class="text-xs text-slate-700 dark:text-slate-200 font-medium truncate max-w-[150px] sm:max-w-xs">
                                    {{ breadcrumbs[breadcrumbs.length - 1] }}
                                </span>
                            </template>
                        </div>
                    </div>

                    <!-- Right Controls: Global Search (Ctrl + K), Dark Mode, Dev Mode, Notifications, User -->
                    <div class="flex items-center gap-2 sm:gap-3">
                        <!-- Global Search Trigger Button (Ctrl + K) -->
                        <button
                            @click="showPalette = true"
                            class="flex items-center gap-2 px-3 py-1.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 text-slate-500 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-700/60 transition text-xs shadow-2xs"
                        >
                            <Search class="w-3.5 h-3.5" />
                            <span class="hidden md:inline">Cari dokumen/menu...</span>
                            <span class="font-mono text-[10px] px-1.5 py-0.5 rounded bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700">
                                Ctrl + K
                            </span>
                        </button>

                        <!-- Dark / Light Mode Toggle -->
                        <button
                            @click="themeStore.toggleTheme"
                            class="p-2 rounded-xl text-slate-500 dark:text-slate-400 hover:text-slate-700 dark:hover:text-slate-200 hover:bg-slate-100 dark:hover:bg-slate-800 transition"
                            :title="themeStore.isDark ? 'Beralih ke Light Mode' : 'Beralih ke Dark Mode'"
                        >
                            <Sun v-if="themeStore.isDark" class="w-4 h-4 text-amber-400" />
                            <Moon v-else class="w-4 h-4 text-slate-600" />
                        </button>

                        <!-- Developer Mode Button -->
                        <button
                            @click="devStore.toggle"
                            :class="[
                                'hidden sm:flex items-center gap-1.5 px-2.5 py-1.5 rounded-lg text-xs font-mono font-medium border transition',
                                devStore.isEnabled
                                    ? 'bg-emerald-600 text-white border-emerald-600'
                                    : 'bg-slate-50 dark:bg-slate-800 text-slate-600 dark:text-slate-400 border-slate-200 dark:border-slate-700 hover:bg-slate-100 dark:hover:bg-slate-700',
                            ]"
                        >
                            <Terminal class="w-3.5 h-3.5" />
                            <span class="hidden lg:inline">Dev Mode</span>
                        </button>

                        <!-- Notification Bell -->
                        <button
                            class="relative p-2 rounded-xl text-slate-500 dark:text-slate-400 hover:text-slate-700 dark:hover:text-slate-200 hover:bg-slate-100 dark:hover:bg-slate-800 transition"
                            title="Audit Log & Notifications"
                        >
                            <Bell class="w-4 h-4" />
                            <span class="absolute top-1.5 right-1.5 w-2 h-2 rounded-full bg-emerald-500 ring-2 ring-white dark:ring-slate-900" />
                        </button>

                        <!-- Mobile Sidebar Toggle -->
                        <button
                            @click="isMobileSidebarOpen = !isMobileSidebarOpen"
                            class="md:hidden p-2 rounded-xl text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800"
                        >
                            <Menu class="w-5 h-5" />
                        </button>

                        <!-- User Profile Menu -->
                        <div class="hidden sm:flex items-center gap-2 pl-2 border-l border-slate-200 dark:border-slate-800">
                            <div class="w-8 h-8 rounded-full bg-slate-800 dark:bg-emerald-800 text-white flex items-center justify-center font-semibold text-xs shadow-xs">
                                SA
                            </div>
                            <div class="hidden xl:block text-left text-xs leading-tight">
                                <div class="font-semibold text-slate-800 dark:text-slate-200">Super Administrator</div>
                                <div class="text-[10px] text-emerald-600 dark:text-emerald-400 font-medium">System Architect</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </header>

        <!-- Body Workspace with Sidebar & Main Content -->
        <div class="flex-1 flex max-w-7xl w-full mx-auto px-4 sm:px-6 lg:px-8 py-6 gap-6">
            <!-- Contextual Sidebar (Desktop) -->
            <aside
                :class="[
                    'hidden md:block transition-all duration-200 shrink-0',
                    isSidebarOpen ? 'w-64' : 'w-16',
                ]"
            >
                <div class="sticky top-24 bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 shadow-xs p-3">
                    <!-- Module Indicator -->
                    <div class="flex items-center justify-between pb-3 mb-2 border-b border-slate-100 dark:border-slate-800 px-2">
                        <div v-if="isSidebarOpen" class="min-w-0">
                            <h3 class="text-xs font-bold uppercase tracking-wider text-slate-400 dark:text-slate-500">
                                Navigasi Modul
                            </h3>
                            <div class="font-semibold text-slate-800 dark:text-slate-200 text-sm truncate mt-0.5">
                                {{ moduleName }}
                            </div>
                        </div>
                        <button
                            @click="isSidebarOpen = !isSidebarOpen"
                            class="p-1 rounded-md text-slate-400 hover:text-slate-600 dark:hover:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800 text-xs"
                            :title="isSidebarOpen ? 'Persempit Sidebar' : 'Perluas Sidebar'"
                        >
                            <Menu class="w-4 h-4" />
                        </button>
                    </div>

                    <!-- Dynamic Menus -->
                    <nav class="space-y-1">
                        <Link
                            v-for="(menu, idx) in menus"
                            :key="idx"
                            :href="menu.route"
                            :class="[
                                'flex items-center gap-3 px-3 py-2 rounded-xl text-xs transition duration-150',
                                page.url === menu.route
                                    ? themeColorStyles.activeMenu
                                    : 'text-slate-600 dark:text-slate-400 hover:bg-slate-50 dark:hover:bg-slate-800 hover:text-slate-900 dark:hover:text-white',
                            ]"
                            :title="!isSidebarOpen ? menu.name : ''"
                        >
                            <component
                                :is="getMenuIcon(menu.icon)"
                                :class="['w-4 h-4 shrink-0', page.url === menu.route ? themeColorStyles.iconColor : 'text-slate-400 dark:text-slate-500']"
                            />
                            <span v-if="isSidebarOpen" class="font-medium truncate">
                                {{ menu.name }}
                            </span>
                        </Link>
                    </nav>

                    <!-- Back to App Launcher Link -->
                    <div class="mt-6 pt-3 border-t border-slate-100 dark:border-slate-800">
                        <Link
                            href="/"
                            class="flex items-center gap-2.5 px-3 py-2 rounded-xl text-xs font-medium text-slate-500 dark:text-slate-400 hover:text-emerald-700 dark:hover:text-emerald-400 hover:bg-slate-50 dark:hover:bg-slate-800 transition"
                        >
                            <LayoutGrid class="w-4 h-4 shrink-0" />
                            <span v-if="isSidebarOpen">Semua Modul (Launcher)</span>
                        </Link>
                    </div>
                </div>
            </aside>

            <!-- Contextual Sidebar Drawer (Mobile) -->
            <div
                v-if="isMobileSidebarOpen"
                class="fixed inset-0 z-50 bg-slate-900/60 backdrop-blur-xs flex md:hidden"
                @click.self="isMobileSidebarOpen = false"
            >
                <div class="w-64 bg-white dark:bg-slate-900 h-full p-4 border-r border-slate-200 dark:border-slate-800 shadow-2xl flex flex-col justify-between">
                    <div>
                        <div class="flex items-center justify-between pb-3 mb-3 border-b border-slate-100 dark:border-slate-800">
                            <span class="font-bold text-slate-900 dark:text-white text-sm">{{ moduleName }}</span>
                            <button @click="isMobileSidebarOpen = false" class="p-1 text-slate-400">
                                <X class="w-5 h-5" />
                            </button>
                        </div>
                        <nav class="space-y-1">
                            <Link
                                v-for="(menu, idx) in menus"
                                :key="idx"
                                :href="menu.route"
                                @click="isMobileSidebarOpen = false"
                                :class="[
                                    'flex items-center gap-3 px-3 py-2.5 rounded-xl text-xs transition',
                                    page.url === menu.route
                                        ? themeColorStyles.activeMenu
                                        : 'text-slate-600 dark:text-slate-400 hover:bg-slate-50 dark:hover:bg-slate-800',
                                ]"
                            >
                                <component :is="getMenuIcon(menu.icon)" class="w-4 h-4" />
                                <span>{{ menu.name }}</span>
                            </Link>
                        </nav>
                    </div>

                    <div class="pt-3 border-t border-slate-100 dark:border-slate-800">
                        <Link
                            href="/"
                            class="flex items-center gap-2 text-xs font-semibold text-emerald-600 dark:text-emerald-400"
                        >
                            <LayoutGrid class="w-4 h-4" />
                            <span>Kembali ke App Launcher</span>
                        </Link>
                    </div>
                </div>
            </div>

            <!-- Main Module Content Area -->
            <main class="flex-1 min-w-0">
                <slot />
            </main>
        </div>

        <!-- Global Command Palette Modal (Ctrl + K) -->
        <GlobalCommandPalette
            :show="showPalette"
            :items="$page.props.erp?.modules ? [] : []"
            @close="showPalette = false"
        />

        <!-- App Launcher Modal -->
        <AppLauncherModal :show="showLauncher" @close="showLauncher = false" />

        <!-- Developer Mode Overlay Bar (PRD 3.1) -->
        <DeveloperModeOverlay />
    </div>
</template>
