<script setup>
import { ref, computed } from 'vue';
import { Head, router, useForm } from '@inertiajs/vue3';
import ModuleLayout from '@/Layouts/ModuleLayout.vue';
import {
    Plus,
    Search,
    Download,
    Edit2,
    Trash2,
    CheckCircle2,
    XCircle,
    Building2,
    Wallet,
    DollarSign,
    Scale,
    TrendingUp,
    TrendingDown,
    X,
    FolderPlus,
    Tag,
} from 'lucide-vue-next';

const props = defineProps({
    module: {
        type: Object,
        required: true,
    },
    accounts: {
        type: Array,
        default: () => [],
    },
    fslis: {
        type: Array,
        default: () => [],
    },
    headers: {
        type: Array,
        default: () => [],
    },
    summary: {
        type: Object,
        default: () => ({
            total_accounts: 0,
            active_accounts: 0,
            total_assets: 0,
            total_liabilities: 0,
            total_equity: 0,
            total_revenue: 0,
            total_expenses: 0,
        }),
    },
    filters: {
        type: Object,
        default: () => ({
            search: '',
            type: 'all',
            status: 'all',
        }),
    },
});

const searchQuery = ref(props.filters.search || '');
const selectedType = ref(props.filters.type || 'all');
const selectedStatus = ref(props.filters.status || 'all');

const isModalOpen = ref(false);
const isEditing = ref(false);
const editingId = ref(null);

const form = useForm({
    account_code: '',
    account_name: '',
    account_type: 'asset',
    normal_balance: 'debit',
    fsli_id: props.fslis[0]?.id || '',
    parent_id: '',
    is_header: false,
    opening_balance: 0,
    description: '',
    is_active: true,
});

const applyFilters = () => {
    router.get('/finance/coa', {
        search: searchQuery.value,
        type: selectedType.value,
        status: selectedStatus.value,
    }, {
        preserveState: true,
        preserveScroll: true,
    });
};

const openCreateModal = (parent = null) => {
    isEditing.value = false;
    editingId.value = null;
    form.reset();
    form.clearErrors();
    form.is_active = true;
    form.is_header = false;
    form.opening_balance = 0;

    if (parent) {
        form.parent_id = parent.id;
        form.account_type = parent.account_type;
        form.normal_balance = parent.normal_balance;
        form.account_code = parent.account_code + '';
    } else {
        form.account_type = 'asset';
        form.normal_balance = 'debit';
        form.fsli_id = props.fslis[0]?.id || '';
    }

    isModalOpen.value = true;
};

const openEditModal = (account) => {
    isEditing.value = true;
    editingId.value = account.id;
    form.clearErrors();
    form.account_code = account.account_code;
    form.account_name = account.account_name;
    form.account_type = account.account_type;
    form.normal_balance = account.normal_balance;
    form.fsli_id = account.fsli_id;
    form.parent_id = account.parent_id || '';
    form.is_header = account.is_header;
    form.opening_balance = account.opening_balance;
    form.description = account.description || '';
    form.is_active = account.is_active;

    isModalOpen.value = true;
};

const closeModal = () => {
    isModalOpen.value = false;
    form.reset();
};

const submitForm = () => {
    if (isEditing.value) {
        form.put(`/finance/coa/${editingId.value}`, {
            onSuccess: () => closeModal(),
        });
    } else {
        form.post('/finance/coa', {
            onSuccess: () => closeModal(),
        });
    }
};

const deleteAccount = (account) => {
    if (confirm(`Yakin ingin menghapus atau menonaktifkan akun ${account.account_code} - ${account.account_name}?`)) {
        router.delete(`/finance/coa/${account.id}`, {
            preserveScroll: true,
        });
    }
};

const toggleAccountStatus = (account) => {
    router.post(`/finance/coa/${account.id}/toggle-status`, {}, {
        preserveScroll: true,
    });
};

const formatCurrency = (val) => {
    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        maximumFractionDigits: 0,
    }).format(val || 0);
};

const typeLabels = {
    asset: { label: 'Aset / Harta', color: 'bg-emerald-50 text-emerald-700 dark:bg-emerald-950/50 dark:text-emerald-300 border-emerald-200 dark:border-emerald-800' },
    liability: { label: 'Kewajiban / Hutang', color: 'bg-amber-50 text-amber-700 dark:bg-amber-950/50 dark:text-amber-300 border-amber-200 dark:border-amber-800' },
    equity: { label: 'Ekuitas / Modal', color: 'bg-blue-50 text-blue-700 dark:bg-blue-950/50 dark:text-blue-300 border-blue-200 dark:border-blue-800' },
    income: { label: 'Pendapatan / Omset', color: 'bg-purple-50 text-purple-700 dark:bg-purple-950/50 dark:text-purple-300 border-purple-200 dark:border-purple-800' },
    expense: { label: 'Beban / Biaya', color: 'bg-rose-50 text-rose-700 dark:bg-rose-950/50 dark:text-rose-300 border-rose-200 dark:border-rose-800' },
};

// When user changes account type, adjust recommended normal balance
const onTypeChange = () => {
    if (form.account_type === 'asset' || form.account_type === 'expense') {
        form.normal_balance = 'debit';
    } else {
        form.normal_balance = 'credit';
    }
};
</script>

<template>
    <Head title="Master Chart of Accounts (COA) - Finance & Budgeting" />

    <ModuleLayout :module="module" :breadcrumbs="['Master Data', 'Chart of Accounts (COA)']">
        <div class="space-y-6">
            <!-- Header Section -->
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 bg-white dark:bg-slate-900 p-6 rounded-2xl border border-slate-200 dark:border-slate-800 shadow-xs">
                <div>
                    <div class="flex items-center gap-2">
                        <span class="px-2.5 py-0.5 rounded-md text-xs font-bold uppercase tracking-wider bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 border border-emerald-500/20">
                            Bagan Akun Mandiri (Non-Odoo)
                        </span>
                        <span class="text-xs text-slate-400">| SAK Entitas Manufaktur</span>
                    </div>
                    <h1 class="text-2xl font-bold text-slate-900 dark:text-white mt-1">
                        Master Chart of Accounts (COA)
                    </h1>
                    <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">
                        Kelola struktur kode akun, saldo awal, saldo berjalan, hierarki akun induk/sub-akun, dan pos laporan keuangan.
                    </p>
                </div>

                <div class="flex items-center gap-2.5">
                    <a
                        href="/finance/coa/export"
                        class="flex items-center gap-1.5 px-3 py-2 rounded-xl text-xs font-semibold text-slate-600 dark:text-slate-300 bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 transition"
                    >
                        <Download class="w-4 h-4" />
                        <span>Ekspor CSV</span>
                    </a>

                    <button
                        @click="openCreateModal()"
                        class="flex items-center gap-1.5 px-4 py-2 rounded-xl text-xs font-semibold text-white bg-emerald-600 hover:bg-emerald-700 shadow-xs transition"
                    >
                        <Plus class="w-4 h-4" />
                        <span>Tambah Akun Baru</span>
                    </button>
                </div>
            </div>

            <!-- Summary KPI Cards -->
            <div class="grid grid-cols-2 lg:grid-cols-5 gap-4">
                <div class="bg-white dark:bg-slate-900 p-4 rounded-xl border border-slate-200 dark:border-slate-800 shadow-2xs">
                    <div class="flex items-center justify-between text-xs text-slate-500">
                        <span>Total Akun</span>
                        <Building2 class="w-4 h-4 text-slate-400" />
                    </div>
                    <div class="mt-2 text-xl font-bold text-slate-900 dark:text-white">
                        {{ summary.total_accounts }}
                    </div>
                    <div class="text-[11px] text-emerald-600 font-medium mt-0.5">
                        {{ summary.active_accounts }} aktif digunakan
                    </div>
                </div>

                <div class="bg-white dark:bg-slate-900 p-4 rounded-xl border border-slate-200 dark:border-slate-800 shadow-2xs">
                    <div class="flex items-center justify-between text-xs text-slate-500">
                        <span>Total Harta (Aset)</span>
                        <Wallet class="w-4 h-4 text-emerald-500" />
                    </div>
                    <div class="mt-2 text-lg font-bold text-emerald-600 dark:text-emerald-400 truncate">
                        {{ formatCurrency(summary.total_assets) }}
                    </div>
                    <div class="text-[11px] text-slate-400 mt-0.5">Saldo Posisi Keuangan</div>
                </div>

                <div class="bg-white dark:bg-slate-900 p-4 rounded-xl border border-slate-200 dark:border-slate-800 shadow-2xs">
                    <div class="flex items-center justify-between text-xs text-slate-500">
                        <span>Kewajiban (Hutang)</span>
                        <Scale class="w-4 h-4 text-amber-500" />
                    </div>
                    <div class="mt-2 text-lg font-bold text-amber-600 dark:text-amber-400 truncate">
                        {{ formatCurrency(summary.total_liabilities) }}
                    </div>
                    <div class="text-[11px] text-slate-400 mt-0.5">Kewajiban Lancar & Non-Lancar</div>
                </div>

                <div class="bg-white dark:bg-slate-900 p-4 rounded-xl border border-slate-200 dark:border-slate-800 shadow-2xs">
                    <div class="flex items-center justify-between text-xs text-slate-500">
                        <span>Pendapatan (Revenue)</span>
                        <TrendingUp class="w-4 h-4 text-purple-500" />
                    </div>
                    <div class="mt-2 text-lg font-bold text-purple-600 dark:text-purple-400 truncate">
                        {{ formatCurrency(summary.total_revenue) }}
                    </div>
                    <div class="text-[11px] text-slate-400 mt-0.5">Akumulasi Penjualan</div>
                </div>

                <div class="bg-white dark:bg-slate-900 p-4 rounded-xl border border-slate-200 dark:border-slate-800 shadow-2xs">
                    <div class="flex items-center justify-between text-xs text-slate-500">
                        <span>Beban Operasional</span>
                        <TrendingDown class="w-4 h-4 text-rose-500" />
                    </div>
                    <div class="mt-2 text-lg font-bold text-rose-600 dark:text-rose-400 truncate">
                        {{ formatCurrency(summary.total_expenses) }}
                    </div>
                    <div class="text-[11px] text-slate-400 mt-0.5">HPP & Biaya Operasional</div>
                </div>
            </div>

            <!-- Filter & Search Toolbar -->
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 bg-white dark:bg-slate-900 p-3 rounded-xl border border-slate-200 dark:border-slate-800">
                <div class="flex items-center gap-2 flex-1 max-w-md">
                    <div class="relative w-full">
                        <Search class="w-4 h-4 absolute left-3 top-3 text-slate-400" />
                        <input
                            v-model="searchQuery"
                            type="text"
                            placeholder="Cari kode akun atau nama akun..."
                            class="w-full pl-9 pr-3 py-2 text-xs rounded-lg border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 text-slate-900 dark:text-white focus:outline-none focus:ring-1 focus:ring-emerald-500"
                            @keyup.enter="applyFilters"
                        />
                    </div>
                    <button
                        @click="applyFilters"
                        class="px-3 py-2 text-xs font-semibold rounded-lg bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 hover:bg-slate-200 transition"
                    >
                        Filter
                    </button>
                </div>

                <div class="flex items-center gap-2 overflow-x-auto pb-1 sm:pb-0">
                    <button
                        v-for="t in [
                            { key: 'all', label: 'Semua Tipe' },
                            { key: 'asset', label: 'Aset' },
                            { key: 'liability', label: 'Kewajiban' },
                            { key: 'equity', label: 'Ekuitas' },
                            { key: 'income', label: 'Pendapatan' },
                            { key: 'expense', label: 'Beban' },
                        ]"
                        :key="t.key"
                        @click="selectedType = t.key; applyFilters()"
                        :class="[
                            'px-2.5 py-1.5 rounded-lg text-xs font-medium transition shrink-0',
                            selectedType === t.key
                                ? 'bg-emerald-600 text-white font-semibold'
                                : 'bg-slate-50 dark:bg-slate-800 text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-700',
                        ]"
                    >
                        {{ t.label }}
                    </button>
                </div>
            </div>

            <!-- Table of Accounts -->
            <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 shadow-xs overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse text-xs">
                        <thead>
                            <tr class="bg-slate-50 dark:bg-slate-800/80 border-b border-slate-200 dark:border-slate-700 text-slate-500 uppercase tracking-wider text-[10px]">
                                <th class="py-3 px-4 font-semibold">Kode Akun</th>
                                <th class="py-3 px-4 font-semibold">Nama Akun & Hierarki</th>
                                <th class="py-3 px-3 font-semibold">Tipe Akun</th>
                                <th class="py-3 px-3 font-semibold">Normal</th>
                                <th class="py-3 px-3 font-semibold">Pos Laporan (FSLI)</th>
                                <th class="py-3 px-3 text-right font-semibold">Saldo Awal</th>
                                <th class="py-3 px-4 text-right font-semibold">Saldo Berjalan</th>
                                <th class="py-3 px-3 text-center font-semibold">Status</th>
                                <th class="py-3 px-4 text-center font-semibold">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                            <tr
                                v-for="a in accounts"
                                :key="a.id"
                                :class="[
                                    'transition hover:bg-slate-50/60 dark:hover:bg-slate-800/40',
                                    a.is_header ? 'bg-slate-50/40 dark:bg-slate-800/20 font-semibold' : '',
                                ]"
                            >
                                <td class="py-3 px-4 font-mono font-bold text-slate-900 dark:text-white whitespace-nowrap">
                                    <span :class="a.is_header ? 'text-emerald-700 dark:text-emerald-400' : ''">
                                        {{ a.account_code }}
                                    </span>
                                </td>

                                <td class="py-3 px-4">
                                    <div :class="['flex items-center gap-1.5', a.parent_id ? 'pl-5' : '']">
                                        <span v-if="a.parent_id" class="text-slate-300 dark:text-slate-600 font-mono">└─</span>
                                        <div>
                                            <span :class="[a.is_header ? 'text-slate-900 dark:text-white font-bold' : 'text-slate-700 dark:text-slate-200 font-medium']">
                                                {{ a.account_name }}
                                            </span>
                                            <span v-if="a.is_header" class="ml-2 px-1.5 py-0.2 rounded text-[9px] bg-slate-200 dark:bg-slate-700 text-slate-600 dark:text-slate-300">
                                                Header Induk
                                            </span>
                                            <div v-if="a.description" class="text-[10px] text-slate-400 truncate max-w-xs">
                                                {{ a.description }}
                                            </div>
                                        </div>
                                    </div>
                                </td>

                                <td class="py-3 px-3 whitespace-nowrap">
                                    <span :class="['px-2 py-0.5 rounded-md text-[10px] font-bold border', typeLabels[a.account_type]?.color || '']">
                                        {{ typeLabels[a.account_type]?.label || a.account_type }}
                                    </span>
                                </td>

                                <td class="py-3 px-3 whitespace-nowrap">
                                    <span :class="[
                                        'px-1.5 py-0.5 rounded font-mono text-[10px] font-bold uppercase',
                                        a.normal_balance === 'debit' ? 'bg-blue-50 text-blue-700 dark:bg-blue-950/60 dark:text-blue-300' : 'bg-amber-50 text-amber-700 dark:bg-amber-950/60 dark:text-amber-300'
                                    ]">
                                        {{ a.normal_balance === 'debit' ? 'D (Debit)' : 'K (Kredit)' }}
                                    </span>
                                </td>

                                <td class="py-3 px-3 text-slate-600 dark:text-slate-400 whitespace-nowrap">
                                    <span class="inline-flex items-center gap-1 text-[11px]">
                                        <Tag class="w-3 h-3 text-slate-400" />
                                        {{ a.fsli_name || '-' }}
                                    </span>
                                </td>

                                <td class="py-3 px-3 text-right font-mono text-slate-600 dark:text-slate-400 whitespace-nowrap">
                                    {{ formatCurrency(a.opening_balance) }}
                                </td>

                                <td class="py-3 px-4 text-right font-mono font-bold whitespace-nowrap">
                                    <span :class="[
                                        a.current_balance < 0 ? 'text-rose-600 dark:text-rose-400' : 'text-slate-900 dark:text-white',
                                    ]">
                                        {{ formatCurrency(a.current_balance) }}
                                    </span>
                                </td>

                                <td class="py-3 px-3 text-center whitespace-nowrap">
                                    <button
                                        @click="toggleAccountStatus(a)"
                                        :title="a.is_active ? 'Klik untuk nonaktifkan' : 'Klik untuk aktifkan'"
                                        class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-semibold transition"
                                        :class="a.is_active ? 'bg-emerald-50 text-emerald-700 dark:bg-emerald-950/60 dark:text-emerald-300' : 'bg-slate-100 text-slate-400 dark:bg-slate-800'"
                                    >
                                        <CheckCircle2 v-if="a.is_active" class="w-3 h-3 text-emerald-500" />
                                        <XCircle v-else class="w-3 h-3 text-slate-400" />
                                        <span>{{ a.is_active ? 'Aktif' : 'Nonaktif' }}</span>
                                    </button>
                                </td>

                                <td class="py-3 px-4 text-center whitespace-nowrap">
                                    <div class="flex items-center justify-center gap-1.5">
                                        <button
                                            v-if="a.is_header"
                                            @click="openCreateModal(a)"
                                            class="p-1 rounded-md text-emerald-600 hover:bg-emerald-50 dark:hover:bg-emerald-950/50"
                                            title="Tambah Sub-Akun di bawah akun ini"
                                        >
                                            <FolderPlus class="w-3.5 h-3.5" />
                                        </button>

                                        <button
                                            @click="openEditModal(a)"
                                            class="p-1 rounded-md text-slate-500 hover:text-slate-700 dark:hover:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800"
                                            title="Ubah Akun"
                                        >
                                            <Edit2 class="w-3.5 h-3.5" />
                                        </button>

                                        <button
                                            @click="deleteAccount(a)"
                                            class="p-1 rounded-md text-rose-500 hover:bg-rose-50 dark:hover:bg-rose-950/50"
                                            title="Hapus / Nonaktifkan Akun"
                                        >
                                            <Trash2 class="w-3.5 h-3.5" />
                                        </button>
                                    </div>
                                </td>
                            </tr>

                            <tr v-if="accounts.length === 0">
                                <td colspan="9" class="py-8 text-center text-slate-400">
                                    Tidak ada data akun COA ditemukan. Silakan tambahkan akun pertama Anda.
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Create / Edit Modal -->
        <div
            v-if="isModalOpen"
            class="fixed inset-0 z-50 bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4 overflow-y-auto"
            @click.self="closeModal"
        >
            <div class="bg-white dark:bg-slate-900 w-full max-w-lg rounded-2xl border border-slate-200 dark:border-slate-800 shadow-2xl p-6 space-y-4">
                <div class="flex items-center justify-between pb-3 border-b border-slate-100 dark:border-slate-800">
                    <h3 class="font-bold text-slate-900 dark:text-white text-base">
                        {{ isEditing ? 'Ubah Akun COA' : 'Tambah Akun COA Baru' }}
                    </h3>
                    <button @click="closeModal" class="p-1 rounded-md text-slate-400 hover:text-slate-600">
                        <X class="w-4 h-4" />
                    </button>
                </div>

                <form @submit.prevent="submitForm" class="space-y-4">
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">
                                Kode Akun <span class="text-rose-500">*</span>
                            </label>
                            <input
                                v-model="form.account_code"
                                type="text"
                                placeholder="Contoh: 1103"
                                class="w-full px-3 py-2 text-xs font-mono rounded-lg border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 text-slate-900 dark:text-white focus:outline-none focus:ring-1 focus:ring-emerald-500"
                                required
                            />
                            <div v-if="form.errors.account_code" class="text-rose-500 text-[10px] mt-0.5">
                                {{ form.errors.account_code }}
                            </div>
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">
                                Tipe Akun <span class="text-rose-500">*</span>
                            </label>
                            <select
                                v-model="form.account_type"
                                @change="onTypeChange"
                                class="w-full px-3 py-2 text-xs rounded-lg border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 text-slate-900 dark:text-white focus:outline-none focus:ring-1 focus:ring-emerald-500"
                                required
                            >
                                <option value="asset">Aset / Harta</option>
                                <option value="liability">Kewajiban / Hutang</option>
                                <option value="equity">Ekuitas / Modal</option>
                                <option value="income">Pendapatan (Revenue)</option>
                                <option value="expense">Beban (Expense / HPP)</option>
                            </select>
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">
                            Nama Akun <span class="text-rose-500">*</span>
                        </label>
                        <input
                            v-model="form.account_name"
                            type="text"
                            placeholder="Contoh: Kas Operasional Bank Mandiri"
                            class="w-full px-3 py-2 text-xs rounded-lg border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 text-slate-900 dark:text-white focus:outline-none focus:ring-1 focus:ring-emerald-500"
                            required
                        />
                        <div v-if="form.errors.account_name" class="text-rose-500 text-[10px] mt-0.5">
                            {{ form.errors.account_name }}
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">
                                Saldo Normal <span class="text-rose-500">*</span>
                            </label>
                            <select
                                v-model="form.normal_balance"
                                class="w-full px-3 py-2 text-xs rounded-lg border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 text-slate-900 dark:text-white focus:outline-none focus:ring-1 focus:ring-emerald-500"
                                required
                            >
                                <option value="debit">Debit (D)</option>
                                <option value="credit">Kredit (K)</option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">
                                Pos Laporan (FSLI) <span class="text-rose-500">*</span>
                            </label>
                            <select
                                v-model="form.fsli_id"
                                class="w-full px-3 py-2 text-xs rounded-lg border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 text-slate-900 dark:text-white focus:outline-none focus:ring-1 focus:ring-emerald-500"
                                required
                            >
                                <option v-for="f in fslis" :key="f.id" :value="f.id">
                                    {{ f.name }} ({{ f.code }})
                                </option>
                            </select>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">
                                Akun Induk (Parent)
                            </label>
                            <select
                                v-model="form.parent_id"
                                class="w-full px-3 py-2 text-xs rounded-lg border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 text-slate-900 dark:text-white focus:outline-none focus:ring-1 focus:ring-emerald-500"
                            >
                                <option value="">-- Tanpa Induk (Akun Utama) --</option>
                                <option v-for="h in headers" :key="h.id" :value="h.id">
                                    {{ h.account_code }} - {{ h.account_name }}
                                </option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">
                                Saldo Awal (Opening)
                            </label>
                            <input
                                v-model="form.opening_balance"
                                type="number"
                                step="any"
                                placeholder="0"
                                class="w-full px-3 py-2 text-xs font-mono rounded-lg border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 text-slate-900 dark:text-white focus:outline-none focus:ring-1 focus:ring-emerald-500"
                            />
                        </div>
                    </div>

                    <div class="flex items-center gap-2 pt-1">
                        <input
                            v-model="form.is_header"
                            type="checkbox"
                            id="is_header"
                            class="rounded text-emerald-600 focus:ring-emerald-500"
                        />
                        <label for="is_header" class="text-xs text-slate-700 dark:text-slate-300">
                            Jadikan sebagai <strong>Akun Header / Grup Induk</strong> (tidak menerima transaksi langsung)
                        </label>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">
                            Keterangan / Deskripsi Pos
                        </label>
                        <textarea
                            v-model="form.description"
                            rows="2"
                            placeholder="Catatan fungsi pos akuntansi atau batasan penggunaan..."
                            class="w-full px-3 py-2 text-xs rounded-lg border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 text-slate-900 dark:text-white focus:outline-none focus:ring-1 focus:ring-emerald-500"
                        ></textarea>
                    </div>

                    <div class="flex items-center justify-end gap-2 pt-3 border-t border-slate-100 dark:border-slate-800">
                        <button
                            type="button"
                            @click="closeModal"
                            class="px-4 py-2 text-xs font-semibold rounded-xl text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800 transition"
                        >
                            Batal
                        </button>
                        <button
                            type="submit"
                            :disabled="form.processing"
                            class="px-5 py-2 text-xs font-semibold rounded-xl text-white bg-emerald-600 hover:bg-emerald-700 shadow-xs transition disabled:opacity-50"
                        >
                            {{ isEditing ? 'Simpan Perubahan' : 'Buat Akun' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </ModuleLayout>
</template>
