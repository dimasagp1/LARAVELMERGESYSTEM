<script setup>
import { Link, usePage } from '@inertiajs/vue3';
import {
    X,
    Pin,
    Landmark,
    Building2,
    Users,
    ShoppingCart,
    Factory,
    ShieldCheck,
    FlaskConical,
    Truck,
    Folder,
} from 'lucide-vue-next';

defineProps({
    show: Boolean,
});

const emit = defineEmits(['close']);

const page = usePage();

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

function getColorBadge(theme) {
    const colors = {
        emerald: 'bg-emerald-50 text-emerald-700 border-emerald-200 group-hover:bg-emerald-600 group-hover:text-white',
        amber: 'bg-amber-50 text-amber-700 border-amber-200 group-hover:bg-amber-600 group-hover:text-white',
        blue: 'bg-blue-50 text-blue-700 border-blue-200 group-hover:bg-blue-600 group-hover:text-white',
        violet: 'bg-violet-50 text-violet-700 border-violet-200 group-hover:bg-violet-600 group-hover:text-white',
        rose: 'bg-rose-50 text-rose-700 border-rose-200 group-hover:bg-rose-600 group-hover:text-white',
        teal: 'bg-teal-50 text-teal-700 border-teal-200 group-hover:bg-teal-600 group-hover:text-white',
        cyan: 'bg-cyan-50 text-cyan-700 border-cyan-200 group-hover:bg-cyan-600 group-hover:text-white',
        indigo: 'bg-indigo-50 text-indigo-700 border-indigo-200 group-hover:bg-indigo-600 group-hover:text-white',
    };
    return colors[theme] || 'bg-slate-50 text-slate-700 border-slate-200 group-hover:bg-slate-600 group-hover:text-white';
}
</script>

<template>
    <div
        v-if="show"
        class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4"
        @click.self="emit('close')"
    >
        <div class="relative w-full max-w-4xl bg-white rounded-2xl shadow-2xl border border-slate-200 overflow-hidden transform transition-all">
            <!-- Header -->
            <div class="flex items-center justify-between px-6 py-4 border-b border-slate-100 bg-slate-50/50">
                <div class="flex items-center gap-3">
                    <div class="w-8 h-8 rounded-lg bg-emerald-600 text-white flex items-center justify-center font-bold text-sm">
                        HT
                    </div>
                    <div>
                        <h3 class="font-semibold text-slate-800 text-base">HerbaTech App Launcher</h3>
                        <p class="text-xs text-slate-500">Pilih modul bisnis atau navigasi cepat antar-departemen</p>
                    </div>
                </div>
                <button
                    @click="emit('close')"
                    class="p-2 text-slate-400 hover:text-slate-600 hover:bg-slate-100 rounded-lg transition"
                >
                    <X class="w-5 h-5" />
                </button>
            </div>

            <!-- Modules Grid -->
            <div class="p-6">
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                    <Link
                        v-for="module in page.props.erp?.modules || []"
                        :key="module.id"
                        :href="module.route_prefix"
                        @click="emit('close')"
                        class="group relative flex flex-col p-4 rounded-xl border border-slate-200/80 hover:border-emerald-500 hover:shadow-lg transition duration-200 bg-white"
                    >
                        <div class="flex items-center justify-between mb-3">
                            <div
                                :class="['w-11 h-11 rounded-xl flex items-center justify-center border transition duration-200', getColorBadge(module.color_theme)]"
                            >
                                <component :is="getIcon(module.icon)" class="w-6 h-6" />
                            </div>
                            <span v-if="module.is_core" class="text-[10px] font-medium text-slate-400 uppercase tracking-wider">
                                Core
                            </span>
                        </div>

                        <h4 class="font-semibold text-slate-900 group-hover:text-emerald-700 text-sm mb-1 line-clamp-1">
                            {{ module.name }}
                        </h4>
                        <p class="text-xs text-slate-500 line-clamp-2 leading-relaxed">
                            {{ module.description }}
                        </p>
                    </Link>
                </div>
            </div>

            <!-- Footer -->
            <div class="px-6 py-3.5 bg-slate-50 border-t border-slate-100 flex flex-wrap items-center justify-between gap-3 text-xs text-slate-500">
                <div class="flex items-center gap-3">
                    <Link
                        href="/settings"
                        @click="emit('close')"
                        class="inline-flex items-center gap-1.5 font-semibold text-slate-700 hover:text-emerald-700 hover:underline"
                    >
                        <span>⚙️ Pengaturan Sistem</span>
                    </Link>
                    <span class="text-slate-300">•</span>
                    <Link
                        href="/settings/users"
                        @click="emit('close')"
                        class="inline-flex items-center gap-1.5 font-semibold text-slate-700 hover:text-emerald-700 hover:underline"
                    >
                        <span>👥 Manajemen Pengguna</span>
                    </Link>
                </div>
                <div class="flex items-center gap-2">
                    <span>HerbaTech Enterprise Modular ERP</span>
                    <span class="font-mono text-slate-400">• 8 Modules Active</span>
                </div>
            </div>
        </div>
    </div>
</template>
