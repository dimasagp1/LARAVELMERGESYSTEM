<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue';
import { Link, usePage, router } from '@inertiajs/vue3';
import {
    User,
    LogOut,
    LogIn,
    Settings,
    Code2,
    Building2,
    ShieldCheck,
    ChevronDown,
    Sparkles,
} from 'lucide-vue-next';

const page = usePage();
const isOpen = ref(false);
const dropdownRef = ref(null);

const authUser = computed(() => page.props.auth?.user || null);

const userInitials = computed(() => {
    if (!authUser.value?.name) return 'U';
    const parts = authUser.value.name.trim().split(/\s+/);
    if (parts.length >= 2) return (parts[0][0] + parts[1][0]).toUpperCase();
    return parts[0].substring(0, 2).toUpperCase();
});

const userRole = computed(() => {
    if (!authUser.value) return 'Tamu';
    if (authUser.value.is_superadmin) return 'Super Administrator';
    return authUser.value.roles?.[0] || authUser.value.department?.name || 'Staff Pengguna';
});

const handleLogout = () => {
    isOpen.value = false;
    router.post('/logout');
};

const handleClickOutside = (e) => {
    if (dropdownRef.value && !dropdownRef.value.contains(e.target)) {
        isOpen.value = false;
    }
};

const handleKeyDown = (e) => {
    if (e.key === 'Escape') {
        isOpen.value = false;
    }
};

onMounted(() => {
    document.addEventListener('click', handleClickOutside);
    document.addEventListener('keydown', handleKeyDown);
});

onUnmounted(() => {
    document.removeEventListener('click', handleClickOutside);
    document.removeEventListener('keydown', handleKeyDown);
});
</script>

<template>
    <div ref="dropdownRef" class="relative">
        <!-- Logged In User Button -->
        <button
            v-if="authUser"
            type="button"
            @click="isOpen = !isOpen"
            class="flex items-center gap-2.5 p-1 rounded-2xl hover:bg-slate-100 dark:hover:bg-slate-800 transition text-left cursor-pointer focus:outline-none"
            :title="authUser.name"
        >
            <div class="relative">
                <div class="w-8 h-8 rounded-full bg-gradient-to-tr from-emerald-600 to-teal-500 text-white flex items-center justify-center font-bold text-xs shadow-xs ring-2 ring-emerald-500/30">
                    {{ userInitials }}
                </div>
                <span class="absolute -bottom-0.5 -right-0.5 w-2.5 h-2.5 rounded-full bg-emerald-500 ring-2 ring-white dark:ring-slate-900"></span>
            </div>
            <div class="hidden xl:block text-left text-xs leading-tight">
                <div class="font-semibold text-slate-800 dark:text-slate-200 truncate max-w-[130px]">
                    {{ authUser.name }}
                </div>
                <div class="text-[10px] text-emerald-600 dark:text-emerald-400 font-medium truncate max-w-[130px]">
                    {{ userRole }}
                </div>
            </div>
            <ChevronDown class="w-3.5 h-3.5 text-slate-400 hidden xl:block transition-transform duration-200" :class="{ 'rotate-180': isOpen }" />
        </button>

        <!-- Guest / Not Logged In Button -->
        <Link
            v-else
            href="/login"
            class="flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-white font-semibold text-xs transition shadow-sm"
        >
            <LogIn class="w-3.5 h-3.5" />
            <span>Masuk</span>
        </Link>

        <!-- Dropdown Popover -->
        <Transition
            enter-active-class="transition ease-out duration-150"
            enter-from-class="transform opacity-0 scale-95 -translate-y-1"
            enter-to-class="transform opacity-100 scale-100 translate-y-0"
            leave-active-class="transition ease-in duration-100"
            leave-from-class="transform opacity-100 scale-100 translate-y-0"
            leave-to-class="transform opacity-0 scale-95 -translate-y-1"
        >
            <div
                v-if="isOpen && authUser"
                class="absolute right-0 mt-2 w-72 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl shadow-xl p-2 z-50 text-slate-700 dark:text-slate-200 space-y-1.5"
            >
                <!-- Profile Header -->
                <div class="p-3 bg-slate-50 dark:bg-slate-950/70 rounded-xl space-y-1 border border-slate-100 dark:border-slate-800/60">
                    <div class="flex items-center gap-2.5">
                        <div class="w-10 h-10 rounded-full bg-emerald-600 text-white flex items-center justify-center font-bold text-sm shrink-0">
                            {{ userInitials }}
                        </div>
                        <div class="min-w-0 flex-1">
                            <div class="font-bold text-slate-900 dark:text-white text-xs truncate">
                                {{ authUser.name }}
                            </div>
                            <div class="text-[11px] text-slate-500 dark:text-slate-400 truncate">
                                {{ authUser.email }}
                            </div>
                        </div>
                    </div>

                    <div class="pt-2 flex flex-wrap gap-1.5 items-center">
                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md text-[10px] font-semibold bg-emerald-100 dark:bg-emerald-950/80 text-emerald-800 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800">
                            <ShieldCheck class="w-3 h-3 text-emerald-600 dark:text-emerald-400" />
                            {{ userRole }}
                        </span>
                        <span v-if="authUser.department" class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md text-[10px] font-medium bg-slate-200 dark:bg-slate-800 text-slate-700 dark:text-slate-300">
                            <Building2 class="w-3 h-3 text-slate-400" />
                            {{ authUser.department.code || authUser.department.name }}
                        </span>
                    </div>
                </div>

                <!-- Navigation Links -->
                <div class="space-y-0.5 py-1">
                    <Link
                        href="/settings"
                        @click="isOpen = false"
                        class="flex items-center gap-2.5 px-3 py-2 rounded-xl text-xs font-medium text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800 hover:text-slate-900 dark:hover:text-white transition"
                    >
                        <Settings class="w-4 h-4 text-slate-400" />
                        <span>Pengaturan & Hak Akses</span>
                    </Link>

                    <Link
                        href="/developer-mode"
                        @click="isOpen = false"
                        class="flex items-center gap-2.5 px-3 py-2 rounded-xl text-xs font-medium text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800 hover:text-slate-900 dark:hover:text-white transition"
                    >
                        <Code2 class="w-4 h-4 text-emerald-500" />
                        <span>Mode Pengembang & Kanvas</span>
                    </Link>
                </div>

                <!-- Logout Button -->
                <div class="pt-1 border-t border-slate-100 dark:border-slate-800">
                    <button
                        type="button"
                        @click="handleLogout"
                        class="w-full flex items-center gap-2.5 px-3 py-2 rounded-xl text-xs font-semibold text-rose-600 dark:text-rose-400 hover:bg-rose-50 dark:hover:bg-rose-950/40 transition cursor-pointer"
                    >
                        <LogOut class="w-4 h-4" />
                        <span>Keluar dari Sistem (Logout)</span>
                    </button>
                </div>
            </div>
        </Transition>
    </div>
</template>
