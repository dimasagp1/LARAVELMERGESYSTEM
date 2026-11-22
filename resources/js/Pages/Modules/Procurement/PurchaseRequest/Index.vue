<script setup>
import { ref } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import ModuleLayout from '@/Layouts/ModuleLayout.vue';
import {
    Plus,
    Search,
    Filter,
    FileText,
    ArrowUpRight,
    CheckCircle2,
    Clock,
    AlertCircle,
    Eye,
} from 'lucide-vue-next';

const props = defineProps({
    module: Object,
    purchaseRequests: Object,
    departments: Array,
    filters: Object,
});

const search = ref(props.filters?.search || '');
const prType = ref(props.filters?.pr_type || '');
const status = ref(props.filters?.status || '');
const departmentId = ref(props.filters?.department_id || '');

const applyFilter = () => {
    router.get('/procurement/purchase-requests', {
        search: search.value || undefined,
        pr_type: prType.value || undefined,
        status: status.value || undefined,
        department_id: departmentId.value || undefined,
    }, { preserveState: true, replace: true });
};

const resetFilter = () => {
    search.value = '';
    prType.value = '';
    status.value = '';
    departmentId.value = '';
    applyFilter();
};

const getStatusBadge = (st) => {
    switch (st) {
        case 'draft': return { label: 'Draft', class: 'bg-slate-100 text-slate-700 dark:bg-slate-800 dark:text-slate-300' };
        case 'pending': return { label: 'Proses Verifikasi', class: 'bg-amber-100 text-amber-800 dark:bg-amber-950 dark:text-amber-300' };
        case 'ordered': return { label: 'PO Terbit', class: 'bg-blue-100 text-blue-800 dark:bg-blue-950 dark:text-blue-300' };
        case 'delivered': return { label: 'Tiba di Pabrik', class: 'bg-purple-100 text-purple-800 dark:bg-purple-950 dark:text-purple-300' };
        case 'completed': return { label: 'Selesai', class: 'bg-emerald-100 text-emerald-800 dark:bg-emerald-950 dark:text-emerald-300' };
        default: return { label: st, class: 'bg-slate-100 text-slate-700' };
    }
};
</script>

<template>
    <Head title="Purchase Requests (PR) - ERP" />

    <ModuleLayout :module="module">
        <div class="space-y-6">
            <!-- Header -->
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-6 shadow-xs">
                <div>
                    <h1 class="text-xl font-bold text-slate-900 dark:text-white">
                        Daftar Purchase Request (PR)
                    </h1>
                    <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">
                        Pengajuan pengadaan barang/jasa internal dengan granular item-level tracking
                    </p>
                </div>
                <Link
                    href="/procurement/purchase-requests/create"
                    class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-violet-600 hover:bg-violet-700 text-white text-xs font-semibold shadow-xs transition-colors self-start sm:self-auto"
                >
                    <Plus class="w-4 h-4" />
                    Buat PR Baru
                </Link>
            </div>

            <!-- Filters -->
            <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-4 shadow-xs">
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-3">
                    <div class="relative">
                        <Search class="w-4 h-4 text-slate-400 absolute left-3 top-3" />
                        <input
                            v-model="search"
                            @keyup.enter="applyFilter"
                            type="text"
                            placeholder="Cari No PR / Item / Purpose..."
                            class="w-full pl-9 pr-3 py-2 text-xs rounded-xl border border-slate-300 dark:border-slate-700 dark:bg-slate-800 focus:ring-2 focus:ring-violet-500"
                        />
                    </div>
                    <div>
                        <select
                            v-model="departmentId"
                            @change="applyFilter"
                            class="w-full py-2 px-3 text-xs rounded-xl border border-slate-300 dark:border-slate-700 dark:bg-slate-800 focus:ring-2 focus:ring-violet-500"
                        >
                            <option value="">Semua Departemen</option>
                            <option v-for="d in departments" :key="d.id" :value="d.id">{{ d.name }}</option>
                        </select>
                    </div>
                    <div>
                        <select
                            v-model="prType"
                            @change="applyFilter"
                            class="w-full py-2 px-3 text-xs rounded-xl border border-slate-300 dark:border-slate-700 dark:bg-slate-800 focus:ring-2 focus:ring-violet-500"
                        >
                            <option value="">Semua Tipe Pengadaan</option>
                            <option value="operational">Operasional (Pabrik / OM)</option>
                            <option value="non_operational">Non-Operasional (FAT)</option>
                        </select>
                    </div>
                    <div>
                        <select
                            v-model="status"
                            @change="applyFilter"
                            class="w-full py-2 px-3 text-xs rounded-xl border border-slate-300 dark:border-slate-700 dark:bg-slate-800 focus:ring-2 focus:ring-violet-500"
                        >
                            <option value="">Semua Status</option>
                            <option value="draft">Draft</option>
                            <option value="pending">Pending Verifikasi</option>
                            <option value="ordered">PO Terbit</option>
                            <option value="delivered">Tiba di Pabrik</option>
                            <option value="completed">Selesai</option>
                        </select>
                    </div>
                    <div class="flex items-center gap-2">
                        <button
                            @click="applyFilter"
                            class="flex-1 py-2 rounded-xl bg-slate-900 text-white dark:bg-white dark:text-slate-900 text-xs font-semibold hover:opacity-90 transition-opacity"
                        >
                            Terapkan
                        </button>
                        <button
                            @click="resetFilter"
                            class="px-3 py-2 rounded-xl border border-slate-200 dark:border-slate-700 text-xs font-medium text-slate-500 hover:bg-slate-100 dark:hover:bg-slate-800"
                        >
                            Reset
                        </button>
                    </div>
                </div>
            </div>

            <!-- Table -->
            <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl overflow-hidden shadow-xs">
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="border-b border-slate-200 dark:border-slate-800 text-[11px] font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider bg-slate-50/50 dark:bg-slate-800/40">
                                <th class="py-3.5 px-4">No PR & Tanggal</th>
                                <th class="py-3.5 px-4">Departemen / Pemohon</th>
                                <th class="py-3.5 px-4">Tujuan (Purpose)</th>
                                <th class="py-3.5 px-4">Tipe</th>
                                <th class="py-3.5 px-4 text-center">Jml Item</th>
                                <th class="py-3.5 px-4 text-right">Total Nilai</th>
                                <th class="py-3.5 px-4">Status</th>
                                <th class="py-3.5 px-4 text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 dark:divide-slate-800 text-xs text-slate-700 dark:text-slate-300">
                            <tr
                                v-for="pr in purchaseRequests.data"
                                :key="pr.id"
                                class="hover:bg-slate-50/60 dark:hover:bg-slate-800/30 transition-colors"
                            >
                                <td class="py-3.5 px-4">
                                    <div class="font-bold text-slate-900 dark:text-white flex items-center gap-1.5">
                                        {{ pr.pr_number }}
                                        <span v-if="pr.revision_count > 0" class="px-1.5 py-0.5 rounded text-[9px] font-bold bg-amber-100 text-amber-800">
                                            Rev-{{ pr.revision_count }}
                                        </span>
                                    </div>
                                    <div class="text-[11px] text-slate-400">{{ pr.request_date }}</div>
                                </td>
                                <td class="py-3.5 px-4">
                                    <div class="font-semibold text-slate-800 dark:text-slate-200">{{ pr.department?.name }}</div>
                                    <div class="text-[11px] text-slate-500">Oleh: {{ pr.user?.name }}</div>
                                </td>
                                <td class="py-3.5 px-4 max-w-xs truncate font-medium">
                                    {{ pr.purpose }}
                                    <span v-if="pr.bypass_budget_check" class="block text-[10px] text-rose-600 font-bold">
                                        Budget Bypass Aktif
                                    </span>
                                </td>
                                <td class="py-3.5 px-4">
                                    <span :class="['px-2 py-0.5 rounded-full text-[10px] font-bold', pr.pr_type === 'operational' ? 'bg-violet-50 text-violet-700 dark:bg-violet-950/60 dark:text-violet-300' : 'bg-sky-50 text-sky-700 dark:bg-sky-950/60 dark:text-sky-300']">
                                        {{ pr.pr_type === 'operational' ? 'Operasional' : 'Non-Operasional' }}
                                    </span>
                                </td>
                                <td class="py-3.5 px-4 text-center font-bold">
                                    {{ pr.items?.length || 0 }}
                                </td>
                                <td class="py-3.5 px-4 text-right font-extrabold text-slate-900 dark:text-white">
                                    Rp {{ Number(pr.total_amount).toLocaleString('id-ID') }}
                                </td>
                                <td class="py-3.5 px-4">
                                    <span :class="['px-2.5 py-1 rounded-full text-[10px] font-bold', getStatusBadge(pr.status).class]">
                                        {{ getStatusBadge(pr.status).label }}
                                    </span>
                                </td>
                                <td class="py-3.5 px-4 text-right">
                                    <div class="flex items-center justify-end gap-1.5">
                                        <Link
                                            :href="`/procurement/purchase-requests/${pr.id}`"
                                            class="p-1.5 rounded-lg text-slate-600 hover:text-violet-600 hover:bg-slate-100 dark:text-slate-400 dark:hover:bg-slate-800 transition-colors"
                                            title="Buka Detail Item"
                                        >
                                            <Eye class="w-4 h-4" />
                                        </Link>
                                        <a
                                            :href="`/procurement/purchase-requests/${pr.id}/pdf`"
                                            target="_blank"
                                            class="p-1.5 rounded-lg text-slate-600 hover:text-emerald-600 hover:bg-slate-100 dark:text-slate-400 dark:hover:bg-slate-800 transition-colors"
                                            title="Unduh PDF Resmi"
                                        >
                                            <FileText class="w-4 h-4" />
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div v-if="purchaseRequests.links?.length > 3" class="p-4 border-t border-slate-200 dark:border-slate-800 flex justify-end gap-1">
                    <Link
                        v-for="(link, idx) in purchaseRequests.links"
                        :key="idx"
                        :href="link.url || '#'"
                        v-html="link.label"
                        :class="[
                            'px-3 py-1.5 rounded-lg text-xs font-semibold',
                            link.active ? 'bg-violet-600 text-white' : 'text-slate-600 hover:bg-slate-100 dark:text-slate-400 dark:hover:bg-slate-800',
                            !link.url && 'opacity-40 cursor-not-allowed'
                        ]"
                    />
                </div>
            </div>
        </div>
    </ModuleLayout>
</template>
