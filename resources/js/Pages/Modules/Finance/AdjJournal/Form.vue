<script setup>
import { ref, computed } from 'vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import ModuleLayout from '@/Layouts/ModuleLayout.vue';
import {
    FileSpreadsheet,
    Plus,
    Trash2,
    CheckCircle2,
    AlertCircle,
    ArrowLeft,
    Save,
    Send
} from 'lucide-vue-next';

const props = defineProps({
    module: Object,
    periods: Array,
    coaAccounts: Array,
    departments: Array,
    fslis: Array,
    categories: Object,
});

const form = useForm({
    period_id: props.periods?.[0]?.id || '',
    journal_date: new Date().toISOString().split('T')[0],
    category: 'accrual',
    description: '',
    as_draft: false,
    lines: [
        {
            coa_account_id: '',
            account_code: '',
            account_name: '',
            fsli_id: props.fslis?.[0]?.id || '',
            department_id: '',
            debit: 0,
            credit: 0,
            description: '',
        },
        {
            coa_account_id: '',
            account_code: '',
            account_name: '',
            fsli_id: props.fslis?.[0]?.id || '',
            department_id: '',
            debit: 0,
            credit: 0,
            description: '',
        },
    ],
});

const onCoaSelect = (index, event) => {
    const coaId = event.target.value;
    const coa = props.coaAccounts.find(c => c.id == coaId);
    if (coa) {
        form.lines[index].coa_account_id = coa.id;
        form.lines[index].account_code = coa.code;
        form.lines[index].account_name = coa.name;
        if (coa.fsli_id) {
            form.lines[index].fsli_id = coa.fsli_id;
        }
    }
};

const addLine = () => {
    form.lines.push({
        coa_account_id: '',
        account_code: '',
        account_name: '',
        fsli_id: props.fslis?.[0]?.id || '',
        department_id: '',
        debit: 0,
        credit: 0,
        description: '',
    });
};

const removeLine = (index) => {
    if (form.lines.length <= 2) {
        alert('Minimal harus ada 2 baris jurnal untuk prinsip pembukuan berpasangan.');
        return;
    }
    form.lines.splice(index, 1);
};

const totalDebit = computed(() => {
    return form.lines.reduce((sum, line) => sum + (parseFloat(line.debit) || 0), 0);
});

const totalCredit = computed(() => {
    return form.lines.reduce((sum, line) => sum + (parseFloat(line.credit) || 0), 0);
});

const difference = computed(() => {
    return Math.abs(totalDebit.value - totalCredit.value);
});

const isBalanced = computed(() => {
    return totalDebit.value > 0 && totalCredit.value > 0 && difference.value < 0.01;
});

const formatCurrency = (val) => {
    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        maximumFractionDigits: 0,
    }).format(val || 0);
};

const submitForm = (asDraft = false) => {
    form.as_draft = asDraft;
    if (!asDraft && !isBalanced.value) {
        alert('Jurnal harus seimbang (Total Debet = Total Kredit) sebelum diajukan ke Checker!');
        return;
    }
    form.post('/finance/aje');
};
</script>

<template>
    <Head title="Buat Jurnal Penyesuaian (AJE)" />

    <ModuleLayout :module="module">
        <form @submit.prevent="submitForm(false)" class="space-y-6">
            <!-- Header -->
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div class="flex items-center gap-3">
                    <Link
                        href="/finance/aje"
                        class="p-2 rounded-xl border border-slate-300 dark:border-slate-700 hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors"
                    >
                        <ArrowLeft class="w-5 h-5 text-slate-600 dark:text-slate-400" />
                    </Link>
                    <div>
                        <h1 class="text-2xl font-bold tracking-tight text-slate-900 dark:text-white flex items-center gap-2">
                            <span>Formulir Jurnal Penyesuaian (AJE Maker)</span>
                        </h1>
                        <p class="text-sm text-slate-500">
                            Masukkan pos penyesuaian akrual, penyusutan, atau koreksi akhir periode dengan prinsip double-entry.
                        </p>
                    </div>
                </div>

                <div class="flex items-center gap-2">
                    <button
                        type="button"
                        :disabled="form.processing"
                        @click="submitForm(true)"
                        class="inline-flex items-center gap-1.5 px-4 py-2 border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 hover:bg-slate-50 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200 text-sm font-semibold rounded-xl transition-colors"
                    >
                        <Save class="w-4 h-4" />
                        <span>Simpan sebagai Draft</span>
                    </button>
                    <button
                        type="button"
                        :disabled="form.processing || !isBalanced"
                        @click="submitForm(false)"
                        class="inline-flex items-center gap-1.5 px-4 py-2 bg-emerald-600 hover:bg-emerald-700 disabled:opacity-50 text-white text-sm font-semibold rounded-xl shadow-md transition-colors"
                    >
                        <Send class="w-4 h-4" />
                        <span>Ajukan ke Checker</span>
                    </button>
                </div>
            </div>

            <!-- General Info Card -->
            <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl p-5 shadow-sm space-y-4">
                <h3 class="font-bold text-slate-900 dark:text-white text-sm border-b pb-2 border-slate-100 dark:border-slate-800">
                    Informasi Header Jurnal
                </h3>

                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">
                            Periode Akuntansi *
                        </label>
                        <select
                            v-model="form.period_id"
                            required
                            class="w-full text-xs rounded-lg border-slate-300 dark:border-slate-700 dark:bg-slate-800"
                        >
                            <option v-for="p in periods" :key="p.id" :value="p.id">
                                {{ p.period_name }} ({{ p.status }})
                            </option>
                        </select>
                        <div v-if="form.errors.period_id" class="text-xs text-rose-500 mt-1">{{ form.errors.period_id }}</div>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">
                            Tanggal Jurnal *
                        </label>
                        <input
                            v-model="form.journal_date"
                            type="date"
                            required
                            class="w-full text-xs rounded-lg border-slate-300 dark:border-slate-700 dark:bg-slate-800"
                        />
                        <div v-if="form.errors.journal_date" class="text-xs text-rose-500 mt-1">{{ form.errors.journal_date }}</div>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">
                            Kategori Penyesuaian *
                        </label>
                        <select
                            v-model="form.category"
                            required
                            class="w-full text-xs rounded-lg border-slate-300 dark:border-slate-700 dark:bg-slate-800"
                        >
                            <option v-for="(label, key) in categories" :key="key" :value="key">
                                {{ label }}
                            </option>
                        </select>
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">
                        Deskripsi / Dasar Penyesuaian *
                    </label>
                    <textarea
                        v-model="form.description"
                        rows="2"
                        required
                        placeholder="Contoh: Penyesuaian beban listrik pabrik bulan berjalan yang belum terbit invoice PLN per 30 November..."
                        class="w-full text-xs rounded-lg border-slate-300 dark:border-slate-700 dark:bg-slate-800"
                    ></textarea>
                    <div v-if="form.errors.description" class="text-xs text-rose-500 mt-1">{{ form.errors.description }}</div>
                </div>
            </div>

            <!-- Lines Table Card -->
            <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl shadow-sm overflow-hidden space-y-4 p-5">
                <div class="flex items-center justify-between">
                    <div>
                        <h3 class="font-bold text-slate-900 dark:text-white text-sm">
                            Pos Akun Jurnal Penyesuaian (Double-Entry Lines)
                        </h3>
                        <p class="text-xs text-slate-500 mt-0.5">
                            Setiap baris wajib dipetakan ke Pos FSLI untuk integrasi ke Kertas Kerja 4-Kolom.
                        </p>
                    </div>
                    <button
                        type="button"
                        @click="addLine"
                        class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-emerald-50 text-emerald-700 dark:bg-emerald-950/40 dark:text-emerald-300 hover:bg-emerald-100 text-xs font-semibold rounded-lg border border-emerald-300 dark:border-emerald-700"
                    >
                        <Plus class="w-3.5 h-3.5" />
                        <span>Tambah Baris</span>
                    </button>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs">
                        <thead class="bg-slate-50 dark:bg-slate-800/60 font-semibold text-slate-600 dark:text-slate-300 border-b">
                            <tr>
                                <th class="p-2 w-48">Pilih Akun COA</th>
                                <th class="p-2 w-44">Kode & Nama Akun</th>
                                <th class="p-2 w-40">Pos FSLI *</th>
                                <th class="p-2 w-32">Departemen</th>
                                <th class="p-2 w-32 text-right">Debet (Rp)</th>
                                <th class="p-2 w-32 text-right">Kredit (Rp)</th>
                                <th class="p-2 w-40">Keterangan</th>
                                <th class="p-2 w-10 text-center"></th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-200 dark:divide-slate-800">
                            <tr v-for="(line, index) in form.lines" :key="index">
                                <td class="p-2">
                                    <select
                                        @change="onCoaSelect(index, $event)"
                                        class="w-full text-xs rounded border-slate-300 dark:border-slate-700 dark:bg-slate-800 py-1"
                                    >
                                        <option value="">-- Pilih dari COA --</option>
                                        <option v-for="c in coaAccounts" :key="c.id" :value="c.id">
                                            {{ c.code }} - {{ c.name }}
                                        </option>
                                    </select>
                                </td>
                                <td class="p-2 space-y-1">
                                    <input
                                        v-model="line.account_code"
                                        placeholder="Kode"
                                        required
                                        class="w-full text-xs rounded border-slate-300 dark:border-slate-700 dark:bg-slate-800 py-1"
                                    />
                                    <input
                                        v-model="line.account_name"
                                        placeholder="Nama Akun"
                                        required
                                        class="w-full text-xs rounded border-slate-300 dark:border-slate-700 dark:bg-slate-800 py-1"
                                    />
                                </td>
                                <td class="p-2">
                                    <select
                                        v-model="line.fsli_id"
                                        required
                                        class="w-full text-xs rounded border-slate-300 dark:border-slate-700 dark:bg-slate-800 py-1"
                                    >
                                        <option v-for="f in fslis" :key="f.id" :value="f.id">
                                            {{ f.code }} - {{ f.name }}
                                        </option>
                                    </select>
                                </td>
                                <td class="p-2">
                                    <select
                                        v-model="line.department_id"
                                        class="w-full text-xs rounded border-slate-300 dark:border-slate-700 dark:bg-slate-800 py-1"
                                    >
                                        <option value="">- Shared / General -</option>
                                        <option v-for="d in departments" :key="d.id" :value="d.id">
                                            {{ d.name }}
                                        </option>
                                    </select>
                                </td>
                                <td class="p-2">
                                    <input
                                        v-model.number="line.debit"
                                        type="number"
                                        min="0"
                                        step="any"
                                        class="w-full text-xs text-right rounded border-slate-300 dark:border-slate-700 dark:bg-slate-800 py-1 font-semibold"
                                    />
                                </td>
                                <td class="p-2">
                                    <input
                                        v-model.number="line.credit"
                                        type="number"
                                        min="0"
                                        step="any"
                                        class="w-full text-xs text-right rounded border-slate-300 dark:border-slate-700 dark:bg-slate-800 py-1 font-semibold"
                                    />
                                </td>
                                <td class="p-2">
                                    <input
                                        v-model="line.description"
                                        placeholder="Keterangan baris"
                                        class="w-full text-xs rounded border-slate-300 dark:border-slate-700 dark:bg-slate-800 py-1"
                                    />
                                </td>
                                <td class="p-2 text-center">
                                    <button
                                        type="button"
                                        @click="removeLine(index)"
                                        class="text-slate-400 hover:text-rose-500 p-1"
                                        title="Hapus baris"
                                    >
                                        <Trash2 class="w-4 h-4" />
                                    </button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Balancing Footer -->
                <div class="bg-slate-50 dark:bg-slate-800/80 p-4 rounded-xl flex flex-col sm:flex-row items-center justify-between gap-4 border border-slate-200 dark:border-slate-700">
                    <div class="flex items-center gap-3">
                        <div
                            :class="[
                                'w-8 h-8 rounded-full flex items-center justify-center shrink-0',
                                isBalanced ? 'bg-emerald-500 text-white' : 'bg-rose-500 text-white'
                            ]"
                        >
                            <CheckCircle2 v-if="isBalanced" class="w-5 h-5" />
                            <AlertCircle v-else class="w-5 h-5" />
                        </div>
                        <div>
                            <div class="font-bold text-xs" :class="isBalanced ? 'text-emerald-700 dark:text-emerald-300' : 'text-rose-700 dark:text-rose-300'">
                                {{ isBalanced ? 'JURNAL SEIMBANG (BALANCED)' : 'JURNAL BELUM SEIMBANG (UNBALANCED)' }}
                            </div>
                            <div class="text-[11px] text-slate-500">
                                Selisih Debet - Kredit: <span class="font-bold font-mono">{{ formatCurrency(difference) }}</span>
                            </div>
                        </div>
                    </div>

                    <div class="flex items-center gap-6 text-xs text-right">
                        <div>
                            <span class="text-slate-500 block">Total Debet:</span>
                            <span class="text-sm font-bold font-mono text-slate-900 dark:text-white">{{ formatCurrency(totalDebit) }}</span>
                        </div>
                        <div>
                            <span class="text-slate-500 block">Total Kredit:</span>
                            <span class="text-sm font-bold font-mono text-slate-900 dark:text-white">{{ formatCurrency(totalCredit) }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </ModuleLayout>
</template>
