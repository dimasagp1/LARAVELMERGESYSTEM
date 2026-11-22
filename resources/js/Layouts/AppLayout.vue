<script setup>
import { ref } from 'vue';
import { Link, usePage } from '@inertiajs/vue3';
import { useDeveloperModeStore } from '@/Stores/developerModeStore';
import AppLauncherModal from '@/Components/AppLauncherModal.vue';
import DeveloperModeOverlay from '@/Components/DeveloperModeOverlay.vue';
import {
    LayoutGrid,
    LayoutDashboard,
    Terminal,
    Bell,
    ChevronDown,
    Building2,
    Users,
    ShoppingCart,
    Factory,
    ShieldCheck,
    FlaskConical,
    Truck,
    Landmark,
    Folder,
    ExternalLink,
    Sparkles,
} from 'lucide-vue-next';

defineProps({
    title: String,
});

const page = usePage();
const devStore = useDeveloperModeStore();
const showLauncher = ref(false);

const iconMap = {
    Landmark,
    Building2,
    Users,
    ShoppingCart,
    Factory,
    ShieldCheck,
    FlaskConical,
    Truck,
};

function getIcon(iconName) {
    return iconMap[iconName] || Folder;
}

function getIconColorClass(theme) {
    const colors = {
        emerald: 'text-emerald-600 bg-emerald-50',
        amber: 'text-amber-600 bg-amber-50',
        blue: 'text-blue-600 bg-blue-50',
        violet: 'text-violet-600 bg-violet-50',
        rose: 'text-rose-600 bg-rose-50',
        teal: 'text-teal-600 bg-teal-50',
        cyan: 'text-cyan-600 bg-cyan-50',
        indigo: 'text-indigo-600 bg-indigo-50',
    };
    return colors[theme] || 'text-slate-600 bg-slate-50';
}
</script>

<template>
    <div class="min-h-screen bg-slate-50/70 text-slate-800 flex flex-col font-sans">
        <!-- Top Navigation Bar -->
        <header class="sticky top-0 z-40 bg-white/95 backdrop-blur border-b border-slate-200 shadow-xs">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex items-center justify-between h-16">
                    <!-- Left: App Launcher & Brand -->
                    <div class="flex items-center gap-3">
                        <button
                            @click="showLauncher = true"
                            class="p-2 rounded-xl text-slate-600 hover:text-emerald-700 hover:bg-emerald-50 transition border border-transparent hover:border-emerald-200"
                            title="Buka App Launcher (Semua Modul)"
                        >
                            <LayoutGrid class="w-5 h-5" />
                        </button>

                        <div class="h-6 w-px bg-slate-200" />

                        <Link href="/" class="flex items-center gap-2.5">
                            <div class="w-9 h-9 rounded-xl bg-gradient-to-tr from-emerald-700 to-teal-500 text-white flex items-center justify-center font-bold text-sm shadow-md shadow-emerald-700/20">
                                HT
                            </div>
                            <div>
                                <div class="flex items-center gap-1.5">
                                    <span class="font-bold text-slate-900 tracking-tight text-base">HerbaTech</span>
                                    <span class="text-[10px] uppercase font-bold tracking-wider px-1.5 py-0.5 rounded bg-emerald-100 text-emerald-800">ERP</span>
                                </div>
                                <p class="text-[10px] text-slate-400 font-medium -mt-0.5">Unified Modular Enterprise</p>
                            </div>
                        </Link>
                    </div>

                    <!-- Center: Modules Quick Nav Links -->
                    <nav class="hidden xl:flex items-center gap-1">
                        <Link
                            href="/"
                            :class="[
                                'px-3 py-1.5 rounded-lg text-xs font-medium transition flex items-center gap-1.5',
                                page.url === '/' ? 'bg-slate-100 text-slate-900 font-semibold' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-50',
                            ]"
                        >
                            <LayoutDashboard class="w-3.5 h-3.5" />
                            Dashboard
                        </Link>

                        <Link
                            v-for="module in (page.props.erp?.modules || []).slice(0, 6)"
                            :key="module.id"
                            :href="module.route_prefix"
                            :class="[
                                'px-3 py-1.5 rounded-lg text-xs font-medium transition flex items-center gap-1.5',
                                page.url.startsWith(module.route_prefix)
                                    ? 'bg-emerald-50 text-emerald-800 font-semibold border border-emerald-200'
                                    : 'text-slate-600 hover:text-slate-900 hover:bg-slate-50',
                            ]"
                        >
                            <component :is="getIcon(module.icon)" class="w-3.5 h-3.5" />
                            {{ module.name.split(' ')[0] }}
                        </Link>
                    </nav>

                    <!-- Right: Developer Switch, Notifications & User -->
                    <div class="flex items-center gap-3">
                        <!-- Developer Mode Switch (PRD 3.1) -->
                        <button
                            @click="devStore.toggle"
                            :class="[
                                'flex items-center gap-1.5 px-2.5 py-1.5 rounded-lg text-xs font-mono font-medium border transition duration-200',
                                devStore.isEnabled
                                    ? 'bg-emerald-600 text-white border-emerald-600 shadow-sm shadow-emerald-500/30'
                                    : 'bg-slate-50 text-slate-600 border-slate-200 hover:bg-slate-100 hover:text-slate-900',
                            ]"
                            title="Toggle Developer Mode & Live Inspection Overlay"
                        >
                            <Terminal class="w-3.5 h-3.5" />
                            <span class="hidden sm:inline">Dev Mode</span>
                            <span
                                :class="[
                                    'w-2 h-2 rounded-full',
                                    devStore.isEnabled ? 'bg-white animate-ping' : 'bg-slate-300',
                                ]"
                            />
                        </button>

                        <!-- Notification / Audit Log Bell -->
                        <button
                            class="relative p-2 rounded-lg text-slate-500 hover:text-slate-700 hover:bg-slate-100 transition"
                            title="Audit Trail & Notifications"
                        >
                            <Bell class="w-4 h-4" />
                            <span class="absolute top-1.5 right-1.5 w-2 h-2 rounded-full bg-emerald-500 ring-2 ring-white" />
                        </button>

                        <!-- User Profile Menu -->
                        <div class="flex items-center gap-2 pl-2 border-l border-slate-200">
                            <div class="w-8 h-8 rounded-full bg-slate-800 text-white flex items-center justify-center font-semibold text-xs shadow-xs">
                                SA
                            </div>
                            <div class="hidden sm:block text-left text-xs leading-tight">
                                <div class="font-semibold text-slate-800">Super Administrator</div>
                                <div class="text-[10px] text-emerald-600 font-medium">System Architect</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </header>

        <!-- Main Content Slot -->
        <main class="flex-1 max-w-7xl w-full mx-auto px-4 sm:px-6 lg:px-8 py-6">
            <slot />
        </main>

        <!-- Footer -->
        <footer class="bg-white border-t border-slate-200 py-4 mt-auto">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 flex flex-col sm:flex-row items-center justify-between gap-2 text-xs text-slate-500">
                <div class="flex items-center gap-2">
                    <span class="font-semibold text-slate-700">HerbaTech Unified Modular ERP</span>
                    <span>•</span>
                    <span>CPOB / CPOTB & ISO 9001 Compliant</span>
                </div>
                <div class="flex items-center gap-3">
                    <span class="font-mono text-[11px] bg-slate-100 px-2 py-0.5 rounded text-slate-600">Laravel 12 • Vue 3 • Inertia v2</span>
                    <span>Version 2.0.0</span>
                </div>
            </div>
        </footer>

        <!-- App Launcher Modal -->
        <AppLauncherModal :show="showLauncher" @close="showLauncher = false" />

        <!-- Developer Mode Overlay Bar (PRD 3.1) -->
        <DeveloperModeOverlay />
    </div>
</template>
