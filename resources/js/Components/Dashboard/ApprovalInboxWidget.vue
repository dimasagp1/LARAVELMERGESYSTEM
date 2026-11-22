<script setup>
import { ref } from 'vue';
import { router } from '@inertiajs/vue3';
import {
    CheckCircle2,
    XCircle,
    RotateCcw,
    Clock,
    AlertCircle,
    FileCheck,
    ArrowUpRight,
    Check,
} from 'lucide-vue-next';

const props = defineProps({
    title: {
        type: String,
        default: 'Antrean Persetujuan (Approval Inbox)',
    },
    items: {
        type: Array,
        default: () => [],
    },
    moduleSlug: {
        type: String,
        default: '',
    },
});

const isProcessing = ref(null);
const selectedItem = ref(null);
const actionComment = ref('');
const activeAction = ref(null); // 'approve', 'reject', 'review'

function submitAction(item, action) {
    if (action === 'reject' || action === 'review') {
        selectedItem.value = item;
        activeAction.value = action;
        actionComment.value = '';
        return;
    }

    // Direct approve
    executePost(item.id, 'approve');
}

function confirmAction() {
    if (!selectedItem.value || !activeAction.value) return;
    executePost(selectedItem.value.id, activeAction.value, actionComment.value);
    selectedItem.value = null;
    activeAction.value = null;
}

function executePost(id, action, comments = '') {
    isProcessing.value = id;
    router.post(
        `/approvals/${id}/action`,
        {
            action,
            comments,
        },
        {
            preserveScroll: true,
            onFinish: () => {
                isProcessing.value = null;
            },
        }
    );
}

function getUrgencyBadge(urgency) {
    if (urgency === 'high') {
        return 'text-rose-700 dark:text-rose-400 bg-rose-50 dark:bg-rose-950/50 border-rose-200 dark:border-rose-800';
    }
    if (urgency === 'medium') {
        return 'text-amber-700 dark:text-amber-400 bg-amber-50 dark:bg-amber-950/50 border-amber-200 dark:border-amber-800';
    }
    return 'text-slate-600 dark:text-slate-400 bg-slate-100 dark:bg-slate-800 border-slate-200 dark:border-slate-700';
}
</script>

<template>
    <div class="bg-white dark:bg-slate-900 rounded-xl border border-slate-200 dark:border-slate-800 p-5 shadow-xs flex flex-col justify-between">
        <div>
            <!-- Header -->
            <div class="flex items-center justify-between pb-3 border-b border-slate-100 dark:border-slate-800 mb-4">
                <div class="flex items-center gap-2">
                    <FileCheck class="w-4 h-4 text-emerald-600 dark:text-emerald-400" />
                    <h3 class="font-bold text-slate-900 dark:text-white text-sm">
                        {{ title }}
                    </h3>
                </div>
                <span class="text-xs font-mono px-2 py-0.5 rounded-full bg-emerald-50 dark:bg-emerald-950 text-emerald-700 dark:text-emerald-400 font-semibold border border-emerald-200 dark:border-emerald-800">
                    {{ items.length }} Pending
                </span>
            </div>

            <!-- List of items -->
            <div v-if="items.length === 0" class="py-8 text-center text-slate-400 dark:text-slate-500 text-xs">
                <CheckCircle2 class="w-8 h-8 mx-auto text-emerald-500 mb-2 opacity-80" />
                Semua item persetujuan telah diproses. Tidak ada antrean.
            </div>

            <div v-else class="space-y-3">
                <div
                    v-for="item in items"
                    :key="item.id"
                    class="p-3 rounded-xl border border-slate-200 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-800/40 hover:border-slate-300 dark:hover:border-slate-700 transition"
                >
                    <div class="flex items-start justify-between gap-2">
                        <div class="min-w-0">
                            <div class="flex items-center gap-2">
                                <h4 class="font-semibold text-slate-900 dark:text-white text-xs truncate">
                                    {{ item.title }}
                                </h4>
                                <span
                                    v-if="item.urgency"
                                    :class="['text-[10px] font-mono px-1.5 py-0.2 rounded border uppercase font-medium', getUrgencyBadge(item.urgency)]"
                                >
                                    {{ item.urgency }}
                                </span>
                            </div>
                            <p class="text-[11px] text-slate-500 dark:text-slate-400 truncate mt-0.5">
                                {{ item.description }}
                            </p>
                        </div>

                        <div v-if="item.amount" class="text-right shrink-0">
                            <span class="text-xs font-bold text-slate-800 dark:text-slate-200">
                                {{ item.amount }}
                            </span>
                        </div>
                    </div>

                    <!-- Meta info & 1-click action buttons -->
                    <div class="mt-3 pt-2.5 border-t border-slate-200/60 dark:border-slate-700/60 flex items-center justify-between text-[11px]">
                        <span class="text-slate-400 dark:text-slate-500 flex items-center gap-1">
                            <Clock class="w-3 h-3" />
                            {{ item.requester }} • {{ item.date }}
                        </span>

                        <div class="flex items-center gap-1">
                            <button
                                @click="submitAction(item, 'review')"
                                :disabled="isProcessing === item.id"
                                class="px-2 py-1 rounded-md text-amber-700 dark:text-amber-400 bg-amber-50 dark:bg-amber-950/40 hover:bg-amber-100 dark:hover:bg-amber-900/40 font-medium transition disabled:opacity-50"
                                title="Kembalikan untuk revisi"
                            >
                                Revisi
                            </button>
                            <button
                                @click="submitAction(item, 'reject')"
                                :disabled="isProcessing === item.id"
                                class="px-2 py-1 rounded-md text-rose-700 dark:text-rose-400 bg-rose-50 dark:bg-rose-950/40 hover:bg-rose-100 dark:hover:bg-rose-900/40 font-medium transition disabled:opacity-50"
                                title="Tolak pengajuan"
                            >
                                Tolak
                            </button>
                            <button
                                @click="submitAction(item, 'approve')"
                                :disabled="isProcessing === item.id"
                                class="px-2.5 py-1 rounded-md text-white bg-emerald-600 hover:bg-emerald-700 font-medium transition shadow-xs disabled:opacity-50 flex items-center gap-1"
                                title="Setujui langsung (Dual-Control Signature)"
                            >
                                <Check class="w-3 h-3" />
                                Approve
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Action Comment Modal for Reject/Revise -->
        <div
            v-if="selectedItem"
            class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 dark:bg-black/80 backdrop-blur-xs flex items-center justify-center p-4"
        >
            <div class="bg-white dark:bg-slate-900 rounded-2xl max-w-md w-full p-5 border border-slate-200 dark:border-slate-800 shadow-2xl">
                <h4 class="font-bold text-slate-900 dark:text-white text-sm mb-1">
                    {{ activeAction === 'reject' ? 'Tolak Pengajuan' : 'Kembalikan untuk Revisi' }}
                </h4>
                <p class="text-xs text-slate-500 dark:text-slate-400 mb-3">
                    Berikan alasan atau instruksi koreksi untuk <strong>{{ selectedItem.title }}</strong>:
                </p>
                <textarea
                    v-model="actionComment"
                    rows="3"
                    class="w-full text-xs rounded-lg border border-slate-300 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 p-2 text-slate-900 dark:text-white focus:ring-1 focus:ring-emerald-500 outline-hidden"
                    placeholder="Tuliskan catatan detail review di sini..."
                />
                <div class="mt-4 flex items-center justify-end gap-2">
                    <button
                        @click="selectedItem = null; activeAction = null;"
                        class="px-3 py-1.5 rounded-lg border border-slate-300 dark:border-slate-700 text-xs text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800"
                    >
                        Batal
                    </button>
                    <button
                        @click="confirmAction"
                        :class="[
                            'px-3 py-1.5 rounded-lg text-xs font-semibold text-white transition',
                            activeAction === 'reject' ? 'bg-rose-600 hover:bg-rose-700' : 'bg-amber-600 hover:bg-amber-700',
                        ]"
                    >
                        Konfirmasi {{ activeAction === 'reject' ? 'Penolakan' : 'Revisi' }}
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>
