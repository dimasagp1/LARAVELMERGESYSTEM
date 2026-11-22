<script setup>
import { ref } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import ModuleLayout from '@/Layouts/ModuleLayout.vue';
import {
    BookOpen,
    Download,
    CheckCircle2,
    TrendingUp,
    FileText,
    ArrowUpRight,
    ArrowDownRight,
    Layers,
    DollarSign,
    Scale
} from 'lucide-vue-next';

const props = defineProps({
    module: Object,
    statements: Object,
    periods: Array,
    selectedPeriodId: Number,
});

const currentPeriod = ref(props.selectedPeriodId || props.periods?.[0]?.id || 1);
const activeTab = ref('pnl'); // 'pnl' | 'balance_sheet' | 'equity' | 'cash_flow'

const changePeriod = () => {
    router.get('/finance/financial-statements', {
        period_id: currentPeriod.value,
    }, { preserveState: true });
};

const formatCurrency = (val) => {
    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        maximumFractionDigits: 0,
    }).format(val || 0);
};

const formatPct = (val) => {
    return (val || 0).toFixed(1) + '%';
};
</script>

<template>
    <Head title="Laporan Keuangan Standar SAK" />

    <ModuleLayout :module="module">
        <div class="space-y-6">
            <!-- Header & Period Filter -->
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div>
                    <h1 class="text-2xl font-bold tracking-tight text-slate-900 dark:text-white flex items-center gap-2">
                        <BookOpen class="w-6 h-6 text-emerald-500" />
                        <span>4 Laporan Keuangan Standar (SAK Compliant)</span>
                    </h1>
                    <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">
                        Laba Rugi Komprehensif, Posisi Keuangan (Neraca), Perubahan Ekuitas, dan Arus Kas bersumber dari Kertas Kerja 4-Kolom.
                    </p>
                </div>

                <div class="flex items-center gap-3">
                    <select
                        v-model="currentPeriod"
                        @change="changePeriod"
                        class="text-xs font-semibold rounded-xl border border-slate-300 dark:border-slate-700 dark:bg-slate-800 text-slate-800 dark:text-slate-200 py-2.5 px-3"
                    >
                        <option v-for="p in periods" :key="p.id" :value="p.id">
                            Periode: {{ p.period_name }}
                        </option>
                    </select>

                    <a
                        :href="`/finance/financial-statements/pdf?period_id=${currentPeriod}`"
                        class="inline-flex items-center gap-1.5 px-3.5 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-semibold rounded-xl shadow-sm transition-colors"
                    >
                        <Download class="w-4 h-4" />
                        <span>Unduh PDF Resmi SAK</span>
                    </a>
                </div>
            </div>

            <!-- Tab Buttons -->
            <div class="border-b border-slate-200 dark:border-slate-800 flex flex-wrap gap-2">
                <button
                    @click="activeTab = 'pnl'"
                    :class="[
                        'pb-3 px-2 text-xs sm:text-sm font-semibold flex items-center gap-2 border-b-2 transition-all',
                        activeTab === 'pnl'
                            ? 'border-emerald-500 text-emerald-600 dark:text-emerald-400'
                            : 'border-transparent text-slate-500 hover:text-slate-800 dark:hover:text-slate-200'
                    ]"
                >
                    <TrendingUp class="w-4 h-4" />
                    <span>1. Laba Rugi Komprehensif</span>
                </button>
                <button
                    @click="activeTab = 'balance_sheet'"
                    :class="[
                        'pb-3 px-2 text-xs sm:text-sm font-semibold flex items-center gap-2 border-b-2 transition-all',
                        activeTab === 'balance_sheet'
                            ? 'border-emerald-500 text-emerald-600 dark:text-emerald-400'
                            : 'border-transparent text-slate-500 hover:text-slate-800 dark:hover:text-slate-200'
                    ]"
                >
                    <Scale class="w-4 h-4" />
                    <span>2. Posisi Keuangan (Neraca)</span>
                </button>
                <button
                    @click="activeTab = 'equity'"
                    :class="[
                        'pb-3 px-2 text-xs sm:text-sm font-semibold flex items-center gap-2 border-b-2 transition-all',
                        activeTab === 'equity'
                            ? 'border-emerald-500 text-emerald-600 dark:text-emerald-400'
                            : 'border-transparent text-slate-500 hover:text-slate-800 dark:hover:text-slate-200'
                    ]"
                >
                    <Layers class="w-4 h-4" />
                    <span>3. Perubahan Ekuitas</span>
                </button>
                <button
                    @click="activeTab = 'cash_flow'"
                    :class="[
                        'pb-3 px-2 text-xs sm:text-sm font-semibold flex items-center gap-2 border-b-2 transition-all',
                        activeTab === 'cash_flow'
                            ? 'border-emerald-500 text-emerald-600 dark:text-emerald-400'
                            : 'border-transparent text-slate-500 hover:text-slate-800 dark:hover:text-slate-200'
                    ]"
                >
                    <DollarSign class="w-4 h-4" />
                    <span>4. Arus Kas (Metode Langsung/Tidak Langsung)</span>
                </button>
            </div>

            <!-- TAB 1: Income Statement -->
            <div v-show="activeTab === 'pnl'" class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl shadow-sm p-6 max-w-4xl mx-auto space-y-6 font-sans">
                <div class="text-center border-b pb-4 border-slate-200 dark:border-slate-800">
                    <h2 class="text-lg font-bold text-slate-900 dark:text-white uppercase tracking-wider">
                        LAPORAN LABA RUGI DAN PENGHASILAN KOMPREHENSIF LAIN
                    </h2>
                    <p class="text-xs text-slate-500 mt-1">
                        Untuk Periode yang Berakhir pada {{ statements?.period?.name || '30 November 2026' }}
                    </p>
                </div>

                <div class="space-y-3 text-sm divide-y divide-slate-100 dark:divide-slate-800">
                    <!-- Revenue -->
                    <div class="flex items-center justify-between pt-2">
                        <span class="font-bold text-slate-900 dark:text-white">PENDAPATAN USAHA / PENJUALAN</span>
                        <span class="font-mono font-bold text-slate-900 dark:text-white">{{ formatCurrency(statements?.income_statement?.revenue) }}</span>
                    </div>

                    <!-- COGS -->
                    <div class="flex items-center justify-between pt-2">
                        <span class="text-slate-700 dark:text-slate-300">Beban Pokok Penjualan (HPP / COGS)</span>
                        <span class="font-mono text-rose-600 dark:text-rose-400">({{ formatCurrency(statements?.income_statement?.cogs) }})</span>
                    </div>

                    <!-- Gross Profit -->
                    <div class="flex items-center justify-between pt-3 pb-2 bg-emerald-50/50 dark:bg-emerald-950/20 px-3 rounded-lg font-bold">
                        <span class="text-emerald-900 dark:text-emerald-200">LABA KOTOR (GROSS PROFIT) [Margin: {{ formatPct(statements?.income_statement?.gross_margin_pct) }}]</span>
                        <span class="font-mono text-emerald-700 dark:text-emerald-300">{{ formatCurrency(statements?.income_statement?.gross_profit) }}</span>
                    </div>

                    <!-- OPEX Section -->
                    <div class="pt-3 space-y-2">
                        <span class="font-semibold text-xs text-slate-500 uppercase tracking-wider block">Beban Operasional (OPEX):</span>
                        <div class="flex items-center justify-between pl-4 text-xs text-slate-600 dark:text-slate-400">
                            <span>Beban Gaji & Upah Karyawan</span>
                            <span class="font-mono">({{ formatCurrency(statements?.income_statement?.opex?.salaries_wages) }})</span>
                        </div>
                        <div class="flex items-center justify-between pl-4 text-xs text-slate-600 dark:text-slate-400">
                            <span>Beban Pemasaran & Penjualan</span>
                            <span class="font-mono">({{ formatCurrency(statements?.income_statement?.opex?.marketing_selling) }})</span>
                        </div>
                        <div class="flex items-center justify-between pl-4 text-xs text-slate-600 dark:text-slate-400">
                            <span>Beban Umum & Administrasi</span>
                            <span class="font-mono">({{ formatCurrency(statements?.income_statement?.opex?.general_administrative) }})</span>
                        </div>
                        <div class="flex items-center justify-between pl-4 text-xs text-slate-600 dark:text-slate-400">
                            <span>Beban Utilitas, Pemeliharaan & Operasional Pabrik</span>
                            <span class="font-mono">({{ formatCurrency(statements?.income_statement?.opex?.utilities_maintenance) }})</span>
                        </div>
                        <div class="flex items-center justify-between pl-4 text-xs font-semibold text-slate-800 dark:text-slate-200 border-t pt-1">
                            <span>Total Beban Operasional</span>
                            <span class="font-mono text-rose-600">({{ formatCurrency(statements?.income_statement?.opex?.total) }})</span>
                        </div>
                    </div>

                    <!-- Operating Income -->
                    <div class="flex items-center justify-between pt-3 pb-2 bg-slate-50 dark:bg-slate-800/50 px-3 rounded-lg font-bold">
                        <span>LABA USAHA (OPERATING INCOME / EBIT)</span>
                        <span class="font-mono">{{ formatCurrency(statements?.income_statement?.operating_income) }}</span>
                    </div>

                    <!-- Other / Tax -->
                    <div class="pt-3 space-y-2">
                        <div class="flex items-center justify-between pl-4 text-xs text-slate-600 dark:text-slate-400">
                            <span>Beban Bunga & Keuangan</span>
                            <span class="font-mono">({{ formatCurrency(statements?.income_statement?.other_income_expense?.interest_expense) }})</span>
                        </div>
                        <div class="flex items-center justify-between pl-4 text-xs text-slate-600 dark:text-slate-400">
                            <span>Taksiran Beban Pajak Penghasilan (PPh Badan)</span>
                            <span class="font-mono">({{ formatCurrency(statements?.income_statement?.other_income_expense?.tax_expense) }})</span>
                        </div>
                    </div>

                    <!-- Net Income -->
                    <div class="flex items-center justify-between pt-4 pb-3 bg-emerald-600 text-white px-4 rounded-xl font-bold text-base shadow-md">
                        <span>LABA BERSIH TAHUN BERJALAN (NET INCOME) [NPM: {{ formatPct(statements?.income_statement?.net_margin_pct) }}]</span>
                        <span class="font-mono text-lg">{{ formatCurrency(statements?.income_statement?.net_income) }}</span>
                    </div>
                </div>
            </div>

            <!-- TAB 2: Balance Sheet -->
            <div v-show="activeTab === 'balance_sheet'" class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl shadow-sm p-6 max-w-5xl mx-auto space-y-6">
                <div class="text-center border-b pb-4 border-slate-200 dark:border-slate-800">
                    <h2 class="text-lg font-bold text-slate-900 dark:text-white uppercase tracking-wider">
                        LAPORAN POSISI KEUANGAN (NERACA)
                    </h2>
                    <p class="text-xs text-slate-500 mt-1">
                        Per {{ statements?.period?.name || '30 November 2026' }} &bull; SAK Terstandarisasi
                    </p>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-8 text-xs">
                    <!-- Assets Column -->
                    <div class="space-y-4">
                        <h3 class="font-bold text-sm text-emerald-700 dark:text-emerald-400 border-b pb-2">
                            ASET (ASSETS)
                        </h3>

                        <!-- Current Assets -->
                        <div class="space-y-2">
                            <span class="font-bold text-slate-700 dark:text-slate-300">ASET LANCAR:</span>
                            <div class="flex justify-between pl-3 text-slate-600 dark:text-slate-400">
                                <span>Kas dan Setara Kas</span>
                                <span class="font-mono">{{ formatCurrency(statements?.balance_sheet?.assets?.current?.cash) }}</span>
                            </div>
                            <div class="flex justify-between pl-3 text-slate-600 dark:text-slate-400">
                                <span>Piutang Usaha</span>
                                <span class="font-mono">{{ formatCurrency(statements?.balance_sheet?.assets?.current?.accounts_receivable) }}</span>
                            </div>
                            <div class="flex justify-between pl-3 text-slate-600 dark:text-slate-400">
                                <span>Persediaan (Bahan Baku, WIP, Barang Jadi)</span>
                                <span class="font-mono">{{ formatCurrency(statements?.balance_sheet?.assets?.current?.inventory) }}</span>
                            </div>
                            <div class="flex justify-between pl-3 text-slate-600 dark:text-slate-400">
                                <span>Biaya Dibayar Dimuka & Aset Lancar Lainnya</span>
                                <span class="font-mono">{{ formatCurrency(statements?.balance_sheet?.assets?.current?.prepaid_expenses) }}</span>
                            </div>
                            <div class="flex justify-between font-bold border-t pt-1 text-slate-900 dark:text-white">
                                <span>Jumlah Aset Lancar</span>
                                <span class="font-mono">{{ formatCurrency(statements?.balance_sheet?.assets?.current?.total) }}</span>
                            </div>
                        </div>

                        <!-- Non Current Assets -->
                        <div class="space-y-2 pt-2">
                            <span class="font-bold text-slate-700 dark:text-slate-300">ASET TIDAK LANCAR:</span>
                            <div class="flex justify-between pl-3 text-slate-600 dark:text-slate-400">
                                <span>Aset Tetap (Pabrik, Mesin, Kendaraan)</span>
                                <span class="font-mono">{{ formatCurrency(statements?.balance_sheet?.assets?.non_current?.property_plant_equipment) }}</span>
                            </div>
                            <div class="flex justify-between pl-3 text-slate-600 dark:text-slate-400">
                                <span>Akumulasi Penyusutan</span>
                                <span class="font-mono text-rose-600">({{ formatCurrency(statements?.balance_sheet?.assets?.non_current?.accumulated_depreciation) }})</span>
                            </div>
                            <div class="flex justify-between pl-3 text-slate-600 dark:text-slate-400">
                                <span>Aset Tak Berwujud & Lain-lain</span>
                                <span class="font-mono">{{ formatCurrency(statements?.balance_sheet?.assets?.non_current?.intangible_assets) }}</span>
                            </div>
                            <div class="flex justify-between font-bold border-t pt-1 text-slate-900 dark:text-white">
                                <span>Jumlah Aset Tidak Lancar</span>
                                <span class="font-mono">{{ formatCurrency(statements?.balance_sheet?.assets?.non_current?.total) }}</span>
                            </div>
                        </div>

                        <!-- Total Assets -->
                        <div class="flex justify-between font-bold text-sm bg-emerald-50 dark:bg-emerald-950/40 p-3 rounded-lg text-emerald-900 dark:text-emerald-200 border border-emerald-200 dark:border-emerald-800">
                            <span>TOTAL ASET</span>
                            <span class="font-mono">{{ formatCurrency(statements?.balance_sheet?.assets?.total_assets) }}</span>
                        </div>
                    </div>

                    <!-- Liabilities & Equity Column -->
                    <div class="space-y-4">
                        <h3 class="font-bold text-sm text-teal-700 dark:text-teal-400 border-b pb-2">
                            LIABILITAS & EKUITAS
                        </h3>

                        <!-- Liabilities -->
                        <div class="space-y-2">
                            <span class="font-bold text-slate-700 dark:text-slate-300">LIABILITAS JANGKA PENDEK:</span>
                            <div class="flex justify-between pl-3 text-slate-600 dark:text-slate-400">
                                <span>Utang Usaha (Trade AP)</span>
                                <span class="font-mono">{{ formatCurrency(statements?.balance_sheet?.liabilities?.current?.accounts_payable) }}</span>
                            </div>
                            <div class="flex justify-between pl-3 text-slate-600 dark:text-slate-400">
                                <span>Beban Akrual & Utang Pajak</span>
                                <span class="font-mono">{{ formatCurrency(statements?.balance_sheet?.liabilities?.current?.accrued_expenses) }}</span>
                            </div>
                            <div class="flex justify-between font-bold border-t pt-1 text-slate-900 dark:text-white">
                                <span>Jumlah Liabilitas Jangka Pendek</span>
                                <span class="font-mono">{{ formatCurrency(statements?.balance_sheet?.liabilities?.current?.total) }}</span>
                            </div>
                        </div>

                        <div class="space-y-2 pt-2">
                            <span class="font-bold text-slate-700 dark:text-slate-300">LIABILITAS JANGKA PANJANG:</span>
                            <div class="flex justify-between pl-3 text-slate-600 dark:text-slate-400">
                                <span>Utang Bank Jangka Panjang</span>
                                <span class="font-mono">{{ formatCurrency(statements?.balance_sheet?.liabilities?.non_current?.long_term_debt) }}</span>
                            </div>
                            <div class="flex justify-between font-bold border-t pt-1 text-slate-900 dark:text-white">
                                <span>Total Liabilitas</span>
                                <span class="font-mono">{{ formatCurrency(statements?.balance_sheet?.liabilities?.total_liabilities) }}</span>
                            </div>
                        </div>

                        <!-- Equity -->
                        <div class="space-y-2 pt-2">
                            <span class="font-bold text-slate-700 dark:text-slate-300">EKUITAS (EQUITY):</span>
                            <div class="flex justify-between pl-3 text-slate-600 dark:text-slate-400">
                                <span>Modal Disetor (Paid-in Capital)</span>
                                <span class="font-mono">{{ formatCurrency(statements?.balance_sheet?.equity?.paid_in_capital) }}</span>
                            </div>
                            <div class="flex justify-between pl-3 text-slate-600 dark:text-slate-400">
                                <span>Saldo Laba Ditahan (Retained Earnings)</span>
                                <span class="font-mono">{{ formatCurrency(statements?.balance_sheet?.equity?.retained_earnings_beg) }}</span>
                            </div>
                            <div class="flex justify-between pl-3 text-emerald-600 dark:text-emerald-400 font-semibold">
                                <span>Laba Bersih Tahun Berjalan</span>
                                <span class="font-mono">{{ formatCurrency(statements?.balance_sheet?.equity?.current_net_income) }}</span>
                            </div>
                            <div class="flex justify-between font-bold border-t pt-1 text-slate-900 dark:text-white">
                                <span>Total Ekuitas</span>
                                <span class="font-mono">{{ formatCurrency(statements?.balance_sheet?.equity?.total_equity) }}</span>
                            </div>
                        </div>

                        <!-- Total Liabilities and Equity -->
                        <div class="flex justify-between font-bold text-sm bg-teal-50 dark:bg-teal-950/40 p-3 rounded-lg text-teal-900 dark:text-teal-200 border border-teal-200 dark:border-teal-800">
                            <span>TOTAL LIABILITAS & EKUITAS</span>
                            <span class="font-mono">{{ formatCurrency(statements?.balance_sheet?.total_liabilities_and_equity) }}</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- TAB 3: Equity Changes -->
            <div v-show="activeTab === 'equity'" class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl shadow-sm p-6 max-w-3xl mx-auto space-y-6">
                <div class="text-center border-b pb-4 border-slate-200 dark:border-slate-800">
                    <h2 class="text-lg font-bold text-slate-900 dark:text-white uppercase tracking-wider">
                        LAPORAN PERUBAHAN EKUITAS
                    </h2>
                    <p class="text-xs text-slate-500 mt-1">
                        Untuk Periode yang Berakhir pada {{ statements?.period?.name || '30 November 2026' }}
                    </p>
                </div>

                <div class="space-y-3 text-xs divide-y divide-slate-100 dark:divide-slate-800">
                    <div class="flex justify-between pt-2">
                        <span class="font-medium text-slate-700 dark:text-slate-300">Saldo Ekuitas Awal Periode</span>
                        <span class="font-mono font-bold">{{ formatCurrency(statements?.equity_change?.beginning_equity) }}</span>
                    </div>
                    <div class="flex justify-between pt-2 text-slate-600 dark:text-slate-400">
                        <span>Penambahan Setoran Modal</span>
                        <span class="font-mono">{{ formatCurrency(statements?.equity_change?.capital_contributions) }}</span>
                    </div>
                    <div class="flex justify-between pt-2 text-emerald-600 font-semibold">
                        <span>Laba Bersih Periode Berjalan</span>
                        <span class="font-mono">+ {{ formatCurrency(statements?.equity_change?.net_income) }}</span>
                    </div>
                    <div class="flex justify-between pt-2 text-rose-600">
                        <span>Distribusi Dividen / Prive</span>
                        <span class="font-mono">({{ formatCurrency(statements?.equity_change?.dividends) }})</span>
                    </div>
                    <div class="flex justify-between pt-3 pb-2 bg-emerald-50 dark:bg-emerald-950/40 p-3 rounded-lg font-bold text-sm text-emerald-900 dark:text-emerald-200">
                        <span>Saldo Ekuitas Akhir Periode</span>
                        <span class="font-mono">{{ formatCurrency(statements?.equity_change?.ending_equity) }}</span>
                    </div>
                </div>
            </div>

            <!-- TAB 4: Cash Flow -->
            <div v-show="activeTab === 'cash_flow'" class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl shadow-sm p-6 max-w-3xl mx-auto space-y-6">
                <div class="text-center border-b pb-4 border-slate-200 dark:border-slate-800">
                    <h2 class="text-lg font-bold text-slate-900 dark:text-white uppercase tracking-wider">
                        LAPORAN ARUS KAS (STATEMENT OF CASH FLOWS)
                    </h2>
                    <p class="text-xs text-slate-500 mt-1">
                        Untuk Periode yang Berakhir pada {{ statements?.period?.name || '30 November 2026' }}
                    </p>
                </div>

                <div class="space-y-4 text-xs">
                    <!-- Operating -->
                    <div class="space-y-2">
                        <h4 class="font-bold text-slate-800 dark:text-slate-200">ARUS KAS DARI AKTIVITAS OPERASI</h4>
                        <div class="flex justify-between pl-3 text-slate-600 dark:text-slate-400">
                            <span>Laba Bersih Setelah Pajak</span>
                            <span class="font-mono">{{ formatCurrency(statements?.cash_flow?.operating_activities?.net_income) }}</span>
                        </div>
                        <div class="flex justify-between pl-3 text-slate-600 dark:text-slate-400">
                            <span>Penyesuaian: Beban Penyusutan Aset Tetap</span>
                            <span class="font-mono">+ {{ formatCurrency(statements?.cash_flow?.operating_activities?.depreciation) }}</span>
                        </div>
                        <div class="flex justify-between pl-3 text-slate-600 dark:text-slate-400">
                            <span>Perubahan Modal Kerja (Piutang, Persediaan, Utang Dagang)</span>
                            <span class="font-mono">{{ formatCurrency((statements?.cash_flow?.operating_activities?.ar_change || 0) + (statements?.cash_flow?.operating_activities?.inventory_change || 0) + (statements?.cash_flow?.operating_activities?.ap_change || 0)) }}</span>
                        </div>
                        <div class="flex justify-between font-bold border-t pt-1">
                            <span>Arus Kas Bersih dari Aktivitas Operasi</span>
                            <span class="font-mono text-emerald-600">{{ formatCurrency(statements?.cash_flow?.operating_activities?.total) }}</span>
                        </div>
                    </div>

                    <!-- Investing -->
                    <div class="space-y-2 pt-2">
                        <h4 class="font-bold text-slate-800 dark:text-slate-200">ARUS KAS DARI AKTIVITAS INVESTASI</h4>
                        <div class="flex justify-between pl-3 text-slate-600 dark:text-slate-400">
                            <span>Pembelian Mesin & Peralatan Pabrik (CAPEX)</span>
                            <span class="font-mono text-rose-600">({{ formatCurrency(statements?.cash_flow?.investing_activities?.capex) }})</span>
                        </div>
                        <div class="flex justify-between font-bold border-t pt-1">
                            <span>Arus Kas Bersih dari Aktivitas Investasi</span>
                            <span class="font-mono text-rose-600">({{ formatCurrency(statements?.cash_flow?.investing_activities?.total) }})</span>
                        </div>
                    </div>

                    <!-- Financing -->
                    <div class="space-y-2 pt-2">
                        <h4 class="font-bold text-slate-800 dark:text-slate-200">ARUS KAS DARI AKTIVITAS PENDANAAN</h4>
                        <div class="flex justify-between pl-3 text-slate-600 dark:text-slate-400">
                            <span>Penerimaan Utang Bank / Penambahan Modal</span>
                            <span class="font-mono">{{ formatCurrency(statements?.cash_flow?.financing_activities?.debt_change) }}</span>
                        </div>
                        <div class="flex justify-between font-bold border-t pt-1">
                            <span>Arus Kas Bersih dari Aktivitas Pendanaan</span>
                            <span class="font-mono">{{ formatCurrency(statements?.cash_flow?.financing_activities?.total) }}</span>
                        </div>
                    </div>

                    <!-- Net Cash -->
                    <div class="bg-slate-50 dark:bg-slate-800/80 p-3 rounded-xl border space-y-1.5 font-bold">
                        <div class="flex justify-between text-slate-900 dark:text-white">
                            <span>KENAIKAN / (PENURUNAN) BERSIH KAS</span>
                            <span class="font-mono text-emerald-600">{{ formatCurrency(statements?.cash_flow?.net_change_in_cash) }}</span>
                        </div>
                        <div class="flex justify-between text-slate-500 font-normal">
                            <span>Saldo Kas Awal Periode</span>
                            <span class="font-mono">{{ formatCurrency(statements?.cash_flow?.beginning_cash) }}</span>
                        </div>
                        <div class="flex justify-between text-slate-900 dark:text-white border-t pt-1 text-sm">
                            <span>SALDO KAS AKHIR PERIODE</span>
                            <span class="font-mono text-emerald-700 dark:text-emerald-300">{{ formatCurrency(statements?.cash_flow?.ending_cash) }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </ModuleLayout>
</template>
