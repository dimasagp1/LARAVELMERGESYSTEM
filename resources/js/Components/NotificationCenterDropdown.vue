<script setup>
import { ref, onMounted, onUnmounted } from 'vue';
import { router } from '@inertiajs/vue3';
import {
    Bell,
    Check,
    CheckCheck,
    Clock,
    AlertTriangle,
    ShieldAlert,
    ExternalLink,
    Sparkles,
    Loader2
} from 'lucide-vue-next';
import axios from 'axios';

const isOpen = ref(false);
const notifications = ref([]);
const unreadCount = ref(0);
const loading = ref(false);

async function fetchNotifications() {
    try {
        const res = await axios.get('/notifications');
        notifications.value = res.data.notifications || [];
        unreadCount.value = res.data.unread_count || 0;
    } catch (err) {
        // Fallback / silent catch
    }
}

async function markAsRead(notification) {
    if (!notification.is_read) {
        notification.is_read = true;
        unreadCount.value = Math.max(0, unreadCount.value - 1);
        try {
            await axios.post(`/notifications/${notification.id}/read`);
        } catch (err) {
            // ignore
        }
    }

    isOpen.value = false;

    if (notification.action_url) {
        router.visit(notification.action_url);
    }
}

async function markAllAsRead() {
    loading.value = true;
    try {
        await axios.post('/notifications/mark-all-read');
        notifications.value.forEach(n => n.is_read = true);
        unreadCount.value = 0;
    } catch (err) {
        // ignore
    } finally {
        loading.value = false;
    }
}

function getUrgencyBadge(urgency) {
    switch (urgency) {
        case 'critical':
            return 'bg-rose-500/10 text-rose-600 dark:text-rose-400 border-rose-200 dark:border-rose-800';
        case 'high':
            return 'bg-amber-500/10 text-amber-600 dark:text-amber-400 border-amber-200 dark:border-amber-800';
        default:
            return 'bg-blue-500/10 text-blue-600 dark:text-blue-400 border-blue-200 dark:border-blue-800';
    }
}

function getModuleColor(slug) {
    const map = {
        'rnd': 'bg-cyan-500',
        'procurement': 'bg-violet-500',
        'supply-chain': 'bg-indigo-500',
        'production': 'bg-rose-500',
        'quality': 'bg-teal-500',
        'finance': 'bg-emerald-500',
        'hris': 'bg-blue-500',
        'general-affair': 'bg-amber-500'
    };
    return map[slug] || 'bg-slate-500';
}

function handleClickOutside(event) {
    const dropdown = document.getElementById('notification-center-container');
    if (dropdown && !dropdown.contains(event.target)) {
        isOpen.value = false;
    }
}

onMounted(() => {
    fetchNotifications();
    document.addEventListener('click', handleClickOutside);
});

onUnmounted(() => {
    document.removeEventListener('click', handleClickOutside);
});
</script>

<template>
    <div id="notification-center-container" class="relative">
        <!-- Bell Trigger Button -->
        <button
            @click.stop="isOpen = !isOpen"
            class="relative p-2 rounded-xl text-slate-500 dark:text-slate-400 hover:text-slate-700 dark:hover:text-slate-200 hover:bg-slate-100 dark:hover:bg-slate-800 transition focus:outline-hidden"
            title="Lonceng Notifikasi & Event Cross-Module"
        >
            <Bell class="w-4 h-4" />
            
            <!-- Live Badge Indicator -->
            <span
                v-if="unreadCount > 0"
                class="absolute -top-0.5 -right-0.5 flex items-center justify-center min-w-[18px] h-[18px] px-1 text-[10px] font-bold text-white bg-rose-500 rounded-full ring-2 ring-white dark:ring-slate-900 animate-pulse"
            >
                {{ unreadCount > 9 ? '9+' : unreadCount }}
            </span>
            <span
                v-else
                class="absolute top-1.5 right-1.5 w-2 h-2 rounded-full bg-emerald-500 ring-2 ring-white dark:ring-slate-900"
            />
        </button>

        <!-- Notification Panel Dropdown -->
        <div
            v-if="isOpen"
            class="absolute right-0 mt-2 w-80 sm:w-96 bg-white dark:bg-slate-900 rounded-2xl shadow-2xl border border-slate-200 dark:border-slate-800 z-50 overflow-hidden transform transition-all duration-200"
        >
            <!-- Header -->
            <div class="px-4 py-3 bg-slate-50/80 dark:bg-slate-800/60 border-b border-slate-200 dark:border-slate-800 flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <Sparkles class="w-4 h-4 text-emerald-500" />
                    <span class="font-bold text-xs text-slate-800 dark:text-slate-100">Notification Center</span>
                    <span
                        v-if="unreadCount > 0"
                        class="px-1.5 py-0.5 rounded-full text-[10px] font-bold bg-rose-500 text-white"
                    >
                        {{ unreadCount }} Baru
                    </span>
                </div>
                <button
                    v-if="unreadCount > 0"
                    @click="markAllAsRead"
                    :disabled="loading"
                    class="text-[11px] text-emerald-600 dark:text-emerald-400 hover:underline flex items-center gap-1 font-medium"
                >
                    <Loader2 v-if="loading" class="w-3 h-3 animate-spin" />
                    <CheckCheck v-else class="w-3 h-3" />
                    <span>Tandai Semua Dibaca</span>
                </button>
            </div>

            <!-- List Body -->
            <div class="max-h-[380px] overflow-y-auto divide-y divide-slate-100 dark:divide-slate-800/60">
                <div
                    v-if="notifications.length === 0"
                    class="px-6 py-10 text-center"
                >
                    <div class="w-10 h-10 rounded-full bg-emerald-50 dark:bg-emerald-950/40 text-emerald-500 mx-auto flex items-center justify-center mb-2">
                        <Check class="w-5 h-5" />
                    </div>
                    <p class="text-xs font-semibold text-slate-700 dark:text-slate-300">Semua Terkendali</p>
                    <p class="text-[11px] text-slate-400 dark:text-slate-500 mt-0.5">Tidak ada notifikasi atau alert pending saat ini.</p>
                </div>

                <div
                    v-for="item in notifications"
                    :key="item.id"
                    @click="markAsRead(item)"
                    :class="[
                        'px-4 py-3 cursor-pointer hover:bg-slate-50 dark:hover:bg-slate-800/50 transition flex items-start gap-3',
                        !item.is_read ? 'bg-emerald-50/30 dark:bg-emerald-950/20' : ''
                    ]"
                >
                    <!-- Dot indicator -->
                    <div class="mt-1 shrink-0 flex items-center justify-center">
                        <span
                            :class="['w-2 h-2 rounded-full', getModuleColor(item.module_slug)]"
                        />
                    </div>

                    <div class="flex-1 min-w-0">
                        <div class="flex items-center justify-between gap-2">
                            <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400 dark:text-slate-500">
                                {{ item.module_slug }}
                            </span>
                            <span class="text-[10px] text-slate-400 dark:text-slate-500 flex items-center gap-1 shrink-0">
                                <Clock class="w-2.5 h-2.5" />
                                {{ new Date(item.created_at).toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' }) }}
                            </span>
                        </div>
                        <h4 :class="['text-xs leading-snug mt-0.5 font-semibold text-slate-900 dark:text-slate-100', !item.is_read ? 'text-emerald-900 dark:text-emerald-200' : '']">
                            {{ item.title }}
                        </h4>
                        <p class="text-[11px] text-slate-600 dark:text-slate-400 line-clamp-2 mt-0.5">
                            {{ item.message }}
                        </p>
                    </div>

                    <div v-if="item.action_url" class="shrink-0 text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 mt-1">
                        <ExternalLink class="w-3.5 h-3.5" />
                    </div>
                </div>
            </div>

            <!-- Footer -->
            <div class="px-4 py-2 bg-slate-50/50 dark:bg-slate-800/40 border-t border-slate-200 dark:border-slate-800 text-center">
                <span class="text-[10px] text-slate-400 dark:text-slate-500">
                    Sistem Otomasi Lintas 8 Modul ERP HerbaTech
                </span>
            </div>
        </div>
    </div>
</template>
