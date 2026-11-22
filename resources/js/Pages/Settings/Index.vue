<script setup>
import { ref, computed } from 'vue';
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import NotificationCenterDropdown from '@/Components/NotificationCenterDropdown.vue';
import {
    Settings,
    Building2,
    Palette,
    Hash,
    Key,
    Shield,
    Users,
    ArrowLeft,
    Check,
    Copy,
    CheckCheck,
    Save,
    RotateCcw,
    Sparkles,
    FlaskConical,
    Leaf,
    Boxes,
    ShieldCheck,
    Award,
    Factory,
    Activity,
    Layers,
    ChevronRight,
    Terminal,
    Globe,
    Code2,
    RefreshCw,
    Trash2,
    ExternalLink,
    AlertCircle,
    Info,
} from 'lucide-vue-next';

const props = defineProps({
    settings: Object,
    numberingPreviews: Object,
    tokens: Array,
    colorOptions: Array,
    radiusOptions: Array,
    iconOptions: Array,
});

const page = usePage();
const activeTab = ref('general');

// 1. General & Branding Form
const generalForm = ref({
    app_name: props.settings?.general?.app_name || 'HerbaTech ERP',
    app_subtitle: props.settings?.general?.app_subtitle || 'Integrated Herbal Pharmaceutical ERP System',
    company_name: props.settings?.general?.company_name || 'PT Herba Medika Industri',
    company_tagline: props.settings?.general?.company_tagline || 'Excellence in Herbal Science & Phytopharmaceuticals',
    company_address: props.settings?.general?.company_address || 'Kawasan Industri Sukoharjo Blok B-12, Solo Baru, Jawa Tengah 57552',
    company_phone: props.settings?.general?.company_phone || '+62 271 7890123',
    company_email: props.settings?.general?.company_email || 'info@herbatech.co.id',
    company_npwp: props.settings?.general?.company_npwp || '01.234.567.8-528.000',
    company_bpom_permit: props.settings?.general?.company_bpom_permit || 'CPOTB-2026/PROD/0921-HMI',
    app_logo_type: props.settings?.general?.app_logo_type || 'icon',
    app_logo_icon: props.settings?.general?.app_logo_icon || 'Sparkles',
});

function submitGeneral() {
    router.post('/settings/general', generalForm.value, {
        preserveScroll: true,
    });
}

// 2. Theme Engine Form
const themeForm = ref({
    theme_color: props.settings?.theme?.theme_color || 'emerald',
    theme_mode: props.settings?.theme?.theme_mode || 'dark',
    theme_radius: props.settings?.theme?.theme_radius || 'rounded-xl',
    theme_density: props.settings?.theme?.theme_density || 'comfortable',
    theme_navbar_style: props.settings?.theme?.theme_navbar_style || 'glass',
});

function submitTheme() {
    router.post('/settings/theme', themeForm.value, {
        preserveScroll: true,
    });
}

// 3. Document Numbering Form
const numberingSchemes = ref(JSON.parse(JSON.stringify(props.settings?.numbering?.numbering_schemes || {})));
const activeEditingCode = ref('PR');

function insertPatternToken(token) {
    if (numberingSchemes.value[activeEditingCode.value]) {
        numberingSchemes.value[activeEditingCode.value].pattern += token;
    }
}

function submitNumbering() {
    router.post('/settings/numbering', { schemes: numberingSchemes.value }, {
        preserveScroll: true,
    });
}

function computePreview(pattern, prefix) {
    if (!pattern) return '';
    return pattern
        .replace('{PREFIX}', prefix || 'DOC')
        .replace('{YEAR}', new Date().getFullYear())
        .replace('{YEAR2}', String(new Date().getFullYear()).slice(-2))
        .replace('{MONTH}', String(new Date().getMonth() + 1).padStart(2, '0'))
        .replace('{ROMAN_MONTH}', 'XI')
        .replace(/\{NUMBER(?::(\d+))?\}/g, (m, digits) => '1'.padStart(Number(digits || 4), '0'));
}

// 4. API & Integration Form
const tokenForm = ref({
    token_name: '',
    expires_days: 90,
});

function submitToken() {
    router.post('/settings/api-tokens', tokenForm.value, {
        preserveScroll: true,
        onSuccess: () => {
            tokenForm.value.token_name = '';
        },
    });
}

function revokeToken(id) {
    if (confirm('Yakin ingin mencabut API Token ini? Aplikasi eksternal yang menggunakannya tidak akan dapat mengakses data lagi.')) {
        router.delete(`/settings/api-tokens/${id}`, {
            preserveScroll: true,
        });
    }
}

const copiedToken = ref(false);
function copyToken(val) {
    navigator.clipboard.writeText(val);
    copiedToken.value = true;
    setTimeout(() => copiedToken.value = false, 2500);
}

// Icon mapper for logo icon selection
const logoIcons = {
    Sparkles,
    FlaskConical,
    Leaf,
    Boxes,
    ShieldCheck,
    Award,
    Factory,
    Activity,
    Layers,
    Building2,
};
</script>

<template>
    <Head title="Pengaturan Sistem & Kustomisasi ERP - HerbaTech ERP" />

    <div class="min-h-screen bg-slate-50 dark:bg-slate-950 text-slate-800 dark:text-slate-100 flex flex-col font-sans transition-colors duration-200">
        <!-- Top Workspace Bar -->
        <header class="sticky top-0 z-40 bg-white/95 dark:bg-slate-900/95 backdrop-blur border-b border-slate-200 dark:border-slate-800 shadow-xs">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex items-center justify-between h-16">
                    <div class="flex items-center gap-3">
                        <Link
                            href="/"
                            class="p-2 rounded-xl text-slate-500 hover:text-slate-900 dark:hover:text-white hover:bg-slate-100 dark:hover:bg-slate-800 transition"
                            title="Kembali ke App Launcher"
                        >
                            <ArrowLeft class="w-5 h-5" />
                        </Link>

                        <div class="h-6 w-px bg-slate-200 dark:bg-slate-700" />

                        <div class="flex items-center gap-2">
                            <span class="text-xs font-bold px-2 py-0.5 rounded bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 border border-emerald-500/20">
                                Super Administrator
                            </span>
                            <ChevronRight class="w-3.5 h-3.5 text-slate-400" />
                            <h1 class="text-sm font-bold text-slate-900 dark:text-white">
                                Pengaturan Sistem & Mesin Kustomisasi
                            </h1>
                        </div>
                    </div>

                    <div class="flex items-center gap-3">
                        <NotificationCenterDropdown />
                        <Link
                            href="/settings/users"
                            class="flex items-center gap-2 px-3.5 py-1.5 rounded-xl text-xs font-semibold bg-emerald-600 hover:bg-emerald-700 text-white shadow-xs transition"
                        >
                            <Users class="w-3.5 h-3.5" />
                            <span>Hak Akses Pengguna</span>
                        </Link>
                    </div>
                </div>
            </div>
        </header>

        <!-- Main Workspace -->
        <main class="flex-1 max-w-7xl w-full mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-6">
            <!-- Header Section -->
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div>
                    <h2 class="text-2xl font-black tracking-tight text-slate-900 dark:text-white flex items-center gap-2.5">
                        <Settings class="w-7 h-7 text-emerald-600 dark:text-emerald-400" />
                        <span>Pengaturan Umum & Kustomisasi ERP</span>
                    </h2>
                    <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">
                        Konfigurasi identitas perusahaan, format penomoran dokumen transaksi, palet tema warna, dan API gateway integrasi data.
                    </p>
                </div>
            </div>

            <!-- Tab Navigation Bar -->
            <div class="flex border-b border-slate-200 dark:border-slate-800 overflow-x-auto gap-2">
                <button
                    @click="activeTab = 'general'"
                    class="flex items-center gap-2 px-4 py-3 text-xs font-bold border-b-2 transition whitespace-nowrap"
                    :class="[
                        activeTab === 'general'
                            ? 'border-emerald-600 text-emerald-600 dark:text-emerald-400'
                            : 'border-transparent text-slate-500 hover:text-slate-700 dark:hover:text-slate-300'
                    ]"
                >
                    <Building2 class="w-4 h-4" />
                    <span>Umum & Branding</span>
                </button>

                <button
                    @click="activeTab = 'numbering'"
                    class="flex items-center gap-2 px-4 py-3 text-xs font-bold border-b-2 transition whitespace-nowrap"
                    :class="[
                        activeTab === 'numbering'
                            ? 'border-emerald-600 text-emerald-600 dark:text-emerald-400'
                            : 'border-transparent text-slate-500 hover:text-slate-700 dark:hover:text-slate-300'
                    ]"
                >
                    <Hash class="w-4 h-4" />
                    <span>Format Penomoran Dokumen</span>
                </button>

                <button
                    @click="activeTab = 'theme'"
                    class="flex items-center gap-2 px-4 py-3 text-xs font-bold border-b-2 transition whitespace-nowrap"
                    :class="[
                        activeTab === 'theme'
                            ? 'border-emerald-600 text-emerald-600 dark:text-emerald-400'
                            : 'border-transparent text-slate-500 hover:text-slate-700 dark:hover:text-slate-300'
                    ]"
                >
                    <Palette class="w-4 h-4" />
                    <span>Engine Tema & Visual</span>
                </button>

                <button
                    @click="activeTab = 'api'"
                    class="flex items-center gap-2 px-4 py-3 text-xs font-bold border-b-2 transition whitespace-nowrap"
                    :class="[
                        activeTab === 'api'
                            ? 'border-emerald-600 text-emerald-600 dark:text-emerald-400'
                            : 'border-transparent text-slate-500 hover:text-slate-700 dark:hover:text-slate-300'
                    ]"
                >
                    <Key class="w-4 h-4" />
                    <span>Integrasi API & Tokens</span>
                </button>
            </div>

            <!-- TAB 1: UMUM & BRANDING -->
            <div v-if="activeTab === 'general'" class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- Left: Form -->
                <div class="lg:col-span-2 bg-white dark:bg-slate-900 p-6 rounded-3xl border border-slate-200 dark:border-slate-800 shadow-xs space-y-6">
                    <div class="border-b border-slate-100 dark:border-slate-800 pb-4">
                        <h3 class="text-base font-bold text-slate-900 dark:text-white">Identitas Sistem & Profil Perusahaan</h3>
                        <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">Informasi ini dicantumkan pada kop surat, laporan PDF, dan navbar aplikasi.</p>
                    </div>

                    <form @submit.prevent="submitGeneral" class="space-y-4 text-xs">
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1">Nama Judul Aplikasi ERP</label>
                                <input
                                    v-model="generalForm.app_name"
                                    type="text"
                                    required
                                    class="w-full px-3 py-2 rounded-xl bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-emerald-500"
                                />
                            </div>

                            <div>
                                <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1">Subjudul / Deskripsi Sistem</label>
                                <input
                                    v-model="generalForm.app_subtitle"
                                    type="text"
                                    class="w-full px-3 py-2 rounded-xl bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-emerald-500"
                                />
                            </div>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1">Nama Resmi Perusahaan</label>
                                <input
                                    v-model="generalForm.company_name"
                                    type="text"
                                    required
                                    class="w-full px-3 py-2 rounded-xl bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-emerald-500"
                                />
                            </div>

                            <div>
                                <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1">Slogan / Tagline Perusahaan</label>
                                <input
                                    v-model="generalForm.company_tagline"
                                    type="text"
                                    class="w-full px-3 py-2 rounded-xl bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-emerald-500"
                                />
                            </div>
                        </div>

                        <div>
                            <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1">Alamat Kantor & Pabrik Utama</label>
                            <textarea
                                v-model="generalForm.company_address"
                                rows="2"
                                class="w-full px-3 py-2 rounded-xl bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-emerald-500"
                            ></textarea>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1">Nomor Telepon Kantor</label>
                                <input
                                    v-model="generalForm.company_phone"
                                    type="text"
                                    class="w-full px-3 py-2 rounded-xl bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-emerald-500"
                                />
                            </div>

                            <div>
                                <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1">Email Kontak Resmi</label>
                                <input
                                    v-model="generalForm.company_email"
                                    type="email"
                                    class="w-full px-3 py-2 rounded-xl bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-emerald-500"
                                />
                            </div>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1">NPWP Perusahaan</label>
                                <input
                                    v-model="generalForm.company_npwp"
                                    type="text"
                                    class="w-full px-3 py-2 rounded-xl bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-emerald-500"
                                />
                            </div>

                            <div>
                                <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1">No. Izin Industri / CPOTB BPOM</label>
                                <input
                                    v-model="generalForm.company_bpom_permit"
                                    type="text"
                                    class="w-full px-3 py-2 rounded-xl bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-emerald-500"
                                />
                            </div>
                        </div>

                        <!-- Logo Icon Selector -->
                        <div class="pt-2">
                            <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-2">Pilihan Ikon Logo Navbar</label>
                            <div class="flex flex-wrap gap-2.5">
                                <button
                                    v-for="icon in iconOptions"
                                    :key="icon"
                                    type="button"
                                    @click="generalForm.app_logo_icon = icon"
                                    class="p-2.5 rounded-xl border flex items-center gap-2 transition"
                                    :class="[
                                        generalForm.app_logo_icon === icon
                                            ? 'border-emerald-600 bg-emerald-50 dark:bg-emerald-950/40 text-emerald-600 dark:text-emerald-400 shadow-xs'
                                            : 'border-slate-200 dark:border-slate-700 text-slate-600 dark:text-slate-400 hover:bg-slate-50 dark:hover:bg-slate-800'
                                    ]"
                                >
                                    <component :is="logoIcons[icon] || Sparkles" class="w-4 h-4" />
                                    <span class="text-xs font-medium">{{ icon }}</span>
                                </button>
                            </div>
                        </div>

                        <div class="pt-4 border-t border-slate-100 dark:border-slate-800 flex justify-end">
                            <button
                                type="submit"
                                class="inline-flex items-center gap-2 px-5 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white font-semibold text-xs rounded-xl shadow-md transition"
                            >
                                <Save class="w-4 h-4" />
                                <span>Simpan Pengaturan Umum</span>
                            </button>
                        </div>
                    </form>
                </div>

                <!-- Right: Live Visual Preview Card -->
                <div class="space-y-6">
                    <div class="bg-white dark:bg-slate-900 p-6 rounded-3xl border border-slate-200 dark:border-slate-800 shadow-xs space-y-4">
                        <div class="border-b border-slate-100 dark:border-slate-800 pb-3">
                            <h3 class="text-sm font-bold text-slate-900 dark:text-white flex items-center gap-2">
                                <Sparkles class="w-4 h-4 text-emerald-500" />
                                <span>Pratinjau Langsung (Live Preview)</span>
                            </h3>
                            <p class="text-[11px] text-slate-400">Tampilan logo dan identitas di header dan dokumen.</p>
                        </div>

                        <!-- Mini Header Mockup -->
                        <div class="p-3.5 rounded-2xl bg-slate-100 dark:bg-slate-800/80 border border-slate-200 dark:border-slate-700 space-y-2">
                            <div class="text-[10px] uppercase font-bold text-slate-400 tracking-wider">Navbar Header</div>
                            <div class="flex items-center gap-2.5 bg-white dark:bg-slate-900 p-2 rounded-xl border border-slate-200 dark:border-slate-800">
                                <div class="w-8 h-8 rounded-lg bg-emerald-600 text-white flex items-center justify-center shadow-xs">
                                    <component :is="logoIcons[generalForm.app_logo_icon] || Sparkles" class="w-4 h-4" />
                                </div>
                                <div class="leading-tight truncate">
                                    <div class="text-xs font-bold text-slate-900 dark:text-white truncate">{{ generalForm.app_name }}</div>
                                    <div class="text-[10px] text-slate-400 truncate">{{ generalForm.app_subtitle }}</div>
                                </div>
                            </div>
                        </div>

                        <!-- Mini Kop Surat Mockup -->
                        <div class="p-3.5 rounded-2xl bg-slate-100 dark:bg-slate-800/80 border border-slate-200 dark:border-slate-700 space-y-2 text-center">
                            <div class="text-[10px] uppercase font-bold text-slate-400 tracking-wider text-left">Kop Dokumen Resmi</div>
                            <div class="bg-white dark:bg-slate-900 p-3 rounded-xl border border-slate-200 dark:border-slate-800 leading-tight space-y-1">
                                <div class="text-xs font-bold text-slate-900 dark:text-white uppercase">{{ generalForm.company_name }}</div>
                                <div class="text-[10px] text-emerald-600 dark:text-emerald-400 font-medium italic">{{ generalForm.company_tagline }}</div>
                                <div class="text-[9px] text-slate-400">{{ generalForm.company_address }}</div>
                                <div class="text-[9px] text-slate-400">Telp: {{ generalForm.company_phone }} &bull; NPWP: {{ generalForm.company_npwp }}</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- TAB 2: FORMAT PENOMORAN DOKUMEN -->
            <div v-if="activeTab === 'numbering'" class="space-y-6">
                <div class="bg-white dark:bg-slate-900 p-6 rounded-3xl border border-slate-200 dark:border-slate-800 shadow-xs space-y-6">
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 border-b border-slate-100 dark:border-slate-800 pb-4">
                        <div>
                            <h3 class="text-base font-bold text-slate-900 dark:text-white">Skema Format Penomoran Dokumen Otomatis</h3>
                            <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">
                                Kustomisasi prefix, struktur segmen, pemisah tanggal, dan nomor urut transaksi per jenis dokumen.
                            </p>
                        </div>

                        <!-- Token Insert Toolbar -->
                        <div class="flex flex-wrap items-center gap-1.5 bg-slate-50 dark:bg-slate-800 p-2 rounded-xl border border-slate-200 dark:border-slate-700">
                            <span class="text-[11px] font-semibold text-slate-400 mr-1">Klik Tag:</span>
                            <button
                                v-for="tag in ['{PREFIX}', '{YEAR}', '{YEAR2}', '{MONTH}', '{ROMAN_MONTH}', '{NUMBER:4}', '{NUMBER:5}']"
                                :key="tag"
                                type="button"
                                @click="insertPatternToken(tag)"
                                class="px-2 py-0.5 rounded text-[11px] font-mono bg-white dark:bg-slate-900 text-emerald-600 dark:text-emerald-400 border border-slate-200 dark:border-slate-700 hover:border-emerald-500 transition"
                            >
                                {{ tag }}
                            </button>
                        </div>
                    </div>

                    <form @submit.prevent="submitNumbering" class="space-y-6">
                        <div class="overflow-x-auto">
                            <table class="w-full text-left text-xs">
                                <thead class="bg-slate-50 dark:bg-slate-800/60 text-slate-500 dark:text-slate-400 font-semibold uppercase tracking-wider border-b border-slate-200 dark:border-slate-800">
                                    <tr>
                                        <th class="px-4 py-3">Dokumen Transaksi</th>
                                        <th class="px-4 py-3 w-32">Prefix</th>
                                        <th class="px-4 py-3">Pola Format Penomoran</th>
                                        <th class="px-4 py-3 w-40">Siklus Reset</th>
                                        <th class="px-4 py-3">Hasil Contoh Nomor</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-100 dark:divide-slate-800 text-slate-700 dark:text-slate-200">
                                    <tr
                                        v-for="(scheme, code) in numberingSchemes"
                                        :key="code"
                                        class="hover:bg-slate-50/60 dark:hover:bg-slate-800/40 transition"
                                        @click="activeEditingCode = code"
                                    >
                                        <td class="px-4 py-3">
                                            <div class="font-bold text-slate-900 dark:text-white">{{ scheme.name || code }}</div>
                                            <div class="text-[11px] text-slate-400">{{ scheme.description }}</div>
                                        </td>

                                        <td class="px-4 py-3">
                                            <input
                                                v-model="scheme.prefix"
                                                type="text"
                                                class="w-full px-2.5 py-1.5 rounded-lg bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 font-mono text-xs uppercase text-slate-900 dark:text-white"
                                            />
                                        </td>

                                        <td class="px-4 py-3">
                                            <input
                                                v-model="scheme.pattern"
                                                type="text"
                                                class="w-full px-2.5 py-1.5 rounded-lg bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 font-mono text-xs text-slate-900 dark:text-white"
                                            />
                                        </td>

                                        <td class="px-4 py-3">
                                            <select
                                                v-model="scheme.reset_period"
                                                class="w-full px-2.5 py-1.5 rounded-lg bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-xs text-slate-900 dark:text-white"
                                            >
                                                <option value="monthly">Bulanan (0001 tiap bln)</option>
                                                <option value="yearly">Tahunan (0001 tiap thn)</option>
                                                <option value="never">Berkelanjutan (Never)</option>
                                            </select>
                                        </td>

                                        <td class="px-4 py-3 font-mono font-bold text-emerald-600 dark:text-emerald-400">
                                            {{ computePreview(scheme.pattern, scheme.prefix) }}
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <div class="flex justify-end pt-4 border-t border-slate-100 dark:border-slate-800">
                            <button
                                type="submit"
                                class="inline-flex items-center gap-2 px-5 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white font-semibold text-xs rounded-xl shadow-md transition"
                            >
                                <Save class="w-4 h-4" />
                                <span>Simpan Format Penomoran</span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- TAB 3: ENGINE TEMA & VISUAL -->
            <div v-if="activeTab === 'theme'" class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- Left: Controls -->
                <div class="lg:col-span-2 bg-white dark:bg-slate-900 p-6 rounded-3xl border border-slate-200 dark:border-slate-800 shadow-xs space-y-6">
                    <div class="border-b border-slate-100 dark:border-slate-800 pb-4">
                        <h3 class="text-base font-bold text-slate-900 dark:text-white">Theme & UI Appearance Engine</h3>
                        <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">Pilih palet warna aksen, kelengkungan border radius, kerapatan densitas, dan gaya navbar.</p>
                    </div>

                    <form @submit.prevent="submitTheme" class="space-y-6 text-xs">
                        <!-- Color Palette Picker -->
                        <div>
                            <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-2.5">
                                Palet Warna Aksen Utama
                            </label>
                            <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                                <label
                                    v-for="color in colorOptions"
                                    :key="color.id"
                                    class="flex items-center gap-3 p-3 rounded-2xl border cursor-pointer transition"
                                    :class="[
                                        themeForm.theme_color === color.id
                                            ? 'border-emerald-600 bg-emerald-50/50 dark:bg-emerald-950/30 shadow-xs'
                                            : 'border-slate-200 dark:border-slate-800 hover:bg-slate-50 dark:hover:bg-slate-800/40'
                                    ]"
                                >
                                    <input
                                        type="radio"
                                        name="theme_color"
                                        :value="color.id"
                                        v-model="themeForm.theme_color"
                                        class="sr-only"
                                    />
                                    <div :class="['w-6 h-6 rounded-full shadow-xs shrink-0', color.bg]"></div>
                                    <div class="min-w-0">
                                        <div class="font-bold text-slate-900 dark:text-white text-xs truncate">{{ color.name }}</div>
                                        <div class="text-[10px] text-slate-400 truncate">{{ color.desc }}</div>
                                    </div>
                                </label>
                            </div>
                        </div>

                        <!-- Border Radius Selector -->
                        <div>
                            <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-2.5">
                                Tingkat Kelengkungan Border Radius Elemen
                            </label>
                            <div class="grid grid-cols-2 sm:grid-cols-3 gap-2.5">
                                <label
                                    v-for="rad in radiusOptions"
                                    :key="rad.id"
                                    class="flex items-center justify-between p-3 rounded-xl border cursor-pointer transition text-xs"
                                    :class="[
                                        themeForm.theme_radius === rad.id
                                            ? 'border-emerald-600 bg-emerald-50/50 dark:bg-emerald-950/30 font-bold text-emerald-700 dark:text-emerald-300'
                                            : 'border-slate-200 dark:border-slate-800 hover:bg-slate-50 dark:hover:bg-slate-800/40 text-slate-600 dark:text-slate-400'
                                    ]"
                                >
                                    <input
                                        type="radio"
                                        name="theme_radius"
                                        :value="rad.id"
                                        v-model="themeForm.theme_radius"
                                        class="sr-only"
                                    />
                                    <span>{{ rad.name }}</span>
                                    <div :class="['w-5 h-5 bg-slate-300 dark:bg-slate-700 border border-slate-400/50', rad.class]"></div>
                                </label>
                            </div>
                        </div>

                        <!-- Density & Navbar Style -->
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1">Kerapatan Tampilan (Density)</label>
                                <select
                                    v-model="themeForm.theme_density"
                                    class="w-full px-3 py-2 rounded-xl bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-slate-900 dark:text-white text-xs"
                                >
                                    <option value="comfortable">Comfortable (Standar Luas & Lega)</option>
                                    <option value="compact">Compact (Tinggi Baris Padat & Rapat)</option>
                                </select>
                            </div>

                            <div>
                                <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1">Gaya Bilah Navigasi (Navbar Style)</label>
                                <select
                                    v-model="themeForm.theme_navbar_style"
                                    class="w-full px-3 py-2 rounded-xl bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-slate-900 dark:text-white text-xs"
                                >
                                    <option value="glass">Glass Blur (Modern Translucent)</option>
                                    <option value="solid">Solid (Warna Penuh Bergaris)</option>
                                    <option value="minimal">Minimal (Ramping & Polos)</option>
                                </select>
                            </div>
                        </div>

                        <div class="pt-4 border-t border-slate-100 dark:border-slate-800 flex justify-end">
                            <button
                                type="submit"
                                class="inline-flex items-center gap-2 px-5 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white font-semibold text-xs rounded-xl shadow-md transition"
                            >
                                <Save class="w-4 h-4" />
                                <span>Terapkan Kustomisasi Tema</span>
                            </button>
                        </div>
                    </form>
                </div>

                <!-- Right: Theme Live Preview -->
                <div class="space-y-6">
                    <div class="bg-white dark:bg-slate-900 p-6 rounded-3xl border border-slate-200 dark:border-slate-800 shadow-xs space-y-4">
                        <div class="border-b border-slate-100 dark:border-slate-800 pb-3">
                            <h3 class="text-sm font-bold text-slate-900 dark:text-white flex items-center gap-2">
                                <Palette class="w-4 h-4 text-emerald-500" />
                                <span>Pratinjau Komponen Tema</span>
                            </h3>
                            <p class="text-[11px] text-slate-400">Simulasi elemen UI dengan palet warna dan radius terpilih.</p>
                        </div>

                        <div class="p-4 rounded-2xl bg-slate-100/70 dark:bg-slate-800/60 border border-slate-200 dark:border-slate-700 space-y-4">
                            <!-- Button preview -->
                            <div class="space-y-1.5">
                                <div class="text-[10px] font-bold text-slate-400 uppercase">Tombol Aksi</div>
                                <div class="flex gap-2">
                                    <button class="px-4 py-2 bg-emerald-600 text-white font-bold text-xs shadow-xs" :class="themeForm.theme_radius">
                                        Tombol Utama
                                    </button>
                                    <button class="px-3 py-2 bg-white dark:bg-slate-900 border border-slate-300 dark:border-slate-700 text-xs font-semibold" :class="themeForm.theme_radius">
                                        Sekunder
                                    </button>
                                </div>
                            </div>

                            <!-- Badge & Indicators preview -->
                            <div class="space-y-1.5">
                                <div class="text-[10px] font-bold text-slate-400 uppercase">Badge & Label</div>
                                <div class="flex flex-wrap gap-2">
                                    <span class="px-2.5 py-1 text-[11px] font-bold bg-emerald-500/20 text-emerald-700 dark:text-emerald-300 border border-emerald-500/30" :class="themeForm.theme_radius">
                                        Approved CPOTB
                                    </span>
                                    <span class="px-2.5 py-1 text-[11px] font-bold bg-amber-500/20 text-amber-700 dark:text-amber-300 border border-amber-500/30" :class="themeForm.theme_radius">
                                        Review GM
                                    </span>
                                </div>
                            </div>

                            <!-- Input form preview -->
                            <div class="space-y-1.5">
                                <div class="text-[10px] font-bold text-slate-400 uppercase">Input Formulir</div>
                                <input
                                    type="text"
                                    readonly
                                    value="Batch No. EXT-202611-001"
                                    class="w-full px-3 py-1.5 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-xs font-mono"
                                    :class="themeForm.theme_radius"
                                />
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- TAB 4: INTEGRASI API & TOKENS -->
            <div v-if="activeTab === 'api'" class="space-y-6">
                <!-- Status & Token Generator -->
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                    <!-- Left: Token Generator -->
                    <div class="lg:col-span-2 bg-white dark:bg-slate-900 p-6 rounded-3xl border border-slate-200 dark:border-slate-800 shadow-xs space-y-6">
                        <div class="border-b border-slate-100 dark:border-slate-800 pb-4 flex items-center justify-between">
                            <div>
                                <h3 class="text-base font-bold text-slate-900 dark:text-white">Penerbitan API Token (Sanctum)</h3>
                                <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">Token otentikasi Bearer untuk integrasi WMS, timbangan IoT, atau Odoo/SAP eksternal.</p>
                            </div>
                            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-emerald-500/20 text-emerald-700 dark:text-emerald-300 border border-emerald-500/30">
                                <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                                REST API v1 Aktif
                            </span>
                        </div>

                        <!-- Newly Generated Plain Token Notification -->
                        <div
                            v-if="$page.props.flash?.plainTextToken"
                            class="p-4 rounded-2xl bg-emerald-500/10 border-2 border-emerald-500/40 space-y-2 animate-in fade-in"
                        >
                            <div class="flex items-center justify-between text-xs font-bold text-emerald-700 dark:text-emerald-300">
                                <span>Simpan Token Ini Sekarang! (Hanya ditampilkan sekali):</span>
                                <button
                                    @click="copyToken($page.props.flash.plainTextToken)"
                                    class="inline-flex items-center gap-1.5 px-3 py-1 bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg transition"
                                >
                                    <Check v-if="copiedToken" class="w-3.5 h-3.5" />
                                    <Copy v-else class="w-3.5 h-3.5" />
                                    <span>{{ copiedToken ? 'Tersalin!' : 'Salin Token' }}</span>
                                </button>
                            </div>
                            <div class="p-2.5 rounded-xl bg-white dark:bg-slate-950 font-mono text-xs text-slate-800 dark:text-slate-200 break-all select-all border border-emerald-300 dark:border-emerald-800">
                                {{ $page.props.flash.plainTextToken }}
                            </div>
                        </div>

                        <form @submit.prevent="submitToken" class="space-y-4 text-xs">
                            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                                <div class="sm:col-span-2">
                                    <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1">Nama Perangkat / Integrasi</label>
                                    <input
                                        v-model="tokenForm.token_name"
                                        type="text"
                                        required
                                        placeholder="e.g. Scanner IoT Gudang Sukoharjo atau Odoo Sync"
                                        class="w-full px-3 py-2 rounded-xl bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-slate-900 dark:text-white"
                                    />
                                </div>

                                <div>
                                    <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1">Masa Berlaku (Hari)</label>
                                    <select
                                        v-model="tokenForm.expires_days"
                                        class="w-full px-3 py-2 rounded-xl bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-slate-900 dark:text-white"
                                    >
                                        <option :value="30">30 Hari</option>
                                        <option :value="90">90 Hari (3 Bulan)</option>
                                        <option :value="365">1 Tahun</option>
                                        <option :value="null">Selamanya (Tidak Kedaluwarsa)</option>
                                    </select>
                                </div>
                            </div>

                            <div class="flex justify-end pt-2">
                                <button
                                    type="submit"
                                    class="inline-flex items-center gap-2 px-5 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white font-semibold text-xs rounded-xl shadow-md transition"
                                >
                                    <Key class="w-4 h-4" />
                                    <span>Terbitkan API Token Baru</span>
                                </button>
                            </div>
                        </form>
                    </div>

                    <!-- Right: Quick API Info -->
                    <div class="bg-white dark:bg-slate-900 p-6 rounded-3xl border border-slate-200 dark:border-slate-800 shadow-xs space-y-4 text-xs">
                        <h4 class="font-bold text-slate-900 dark:text-white flex items-center gap-2">
                            <Code2 class="w-4 h-4 text-emerald-500" />
                            <span>Cara Menggunakan API Token</span>
                        </h4>
                        <p class="text-slate-500 dark:text-slate-400">
                            Sertakan header HTTP berikut pada setiap request ke endpoint `/api/v1/*`:
                        </p>
                        <div class="p-3 rounded-xl bg-slate-100 dark:bg-slate-800 font-mono text-[11px] space-y-1 border border-slate-200 dark:border-slate-700">
                            <div><strong class="text-emerald-600">Authorization:</strong> Bearer &lt;YOUR_TOKEN&gt;</div>
                            <div><strong class="text-emerald-600">Accept:</strong> application/json</div>
                        </div>
                    </div>
                </div>

                <!-- Active Tokens List -->
                <div class="bg-white dark:bg-slate-900 p-6 rounded-3xl border border-slate-200 dark:border-slate-800 shadow-xs space-y-4">
                    <h3 class="text-base font-bold text-slate-900 dark:text-white">Daftar API Token Aktif</h3>

                    <div v-if="tokens.length === 0" class="py-8 text-center text-slate-400 text-xs">
                        Belum ada API Token aktif yang diterbitkan. Buat token di atas untuk memulai integrasi data eksternal.
                    </div>

                    <div v-else class="overflow-x-auto">
                        <table class="w-full text-left text-xs">
                            <thead class="bg-slate-50 dark:bg-slate-800/60 text-slate-500 dark:text-slate-400 font-semibold uppercase tracking-wider border-b border-slate-200 dark:border-slate-800">
                                <tr>
                                    <th class="px-4 py-3">Nama Token</th>
                                    <th class="px-4 py-3">Diterbitkan</th>
                                    <th class="px-4 py-3">Terakhir Dipakai</th>
                                    <th class="px-4 py-3">Kedaluwarsa</th>
                                    <th class="px-4 py-3 text-right">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                                <tr v-for="tok in tokens" :key="tok.id">
                                    <td class="px-4 py-3 font-bold text-slate-900 dark:text-white">{{ tok.name }}</td>
                                    <td class="px-4 py-3 text-slate-500">{{ tok.created_at }}</td>
                                    <td class="px-4 py-3 text-slate-500">{{ tok.last_used_at }}</td>
                                    <td class="px-4 py-3 text-slate-500">{{ tok.expires_at }}</td>
                                    <td class="px-4 py-3 text-right">
                                        <button
                                            @click="revokeToken(tok.id)"
                                            class="inline-flex items-center gap-1 px-2.5 py-1 text-xs font-semibold rounded-lg bg-rose-50 text-rose-700 hover:bg-rose-100 dark:bg-rose-950/40 dark:text-rose-300 transition"
                                        >
                                            <Trash2 class="w-3.5 h-3.5" />
                                            <span>Cabut</span>
                                        </button>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- API Endpoints Catalog & cURL Examples -->
                <div class="bg-white dark:bg-slate-900 p-6 rounded-3xl border border-slate-200 dark:border-slate-800 shadow-xs space-y-4">
                    <h3 class="text-base font-bold text-slate-900 dark:text-white flex items-center gap-2">
                        <Globe class="w-4 h-4 text-emerald-500" />
                        <span>Katalog Endpoint REST API v1</span>
                    </h3>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-xs font-mono">
                        <div class="p-3.5 rounded-xl bg-slate-50 dark:bg-slate-800/80 border border-slate-200 dark:border-slate-700 space-y-1.5">
                            <div class="flex items-center justify-between">
                                <span class="px-2 py-0.5 rounded font-bold bg-blue-500/20 text-blue-700 dark:text-blue-300 text-[10px]">GET</span>
                                <span class="text-slate-400 text-[10px]">Katalog Produk & Master</span>
                            </div>
                            <div class="font-bold text-slate-800 dark:text-slate-200">/api/v1/products?search=curcuma</div>
                        </div>

                        <div class="p-3.5 rounded-xl bg-slate-50 dark:bg-slate-800/80 border border-slate-200 dark:border-slate-700 space-y-1.5">
                            <div class="flex items-center justify-between">
                                <span class="px-2 py-0.5 rounded font-bold bg-blue-500/20 text-blue-700 dark:text-blue-300 text-[10px]">GET</span>
                                <span class="text-slate-400 text-[10px]">Stok Multi-Gudang</span>
                            </div>
                            <div class="font-bold text-slate-800 dark:text-slate-200">/api/v1/inventory?plant_id=1</div>
                        </div>

                        <div class="p-3.5 rounded-xl bg-slate-50 dark:bg-slate-800/80 border border-slate-200 dark:border-slate-700 space-y-1.5">
                            <div class="flex items-center justify-between">
                                <span class="px-2 py-0.5 rounded font-bold bg-blue-500/20 text-blue-700 dark:text-blue-300 text-[10px]">GET</span>
                                <span class="text-slate-400 text-[10px]">Daftar Modul & Menu Aktif</span>
                            </div>
                            <div class="font-bold text-slate-800 dark:text-slate-200">/api/v1/modules</div>
                        </div>

                        <div class="p-3.5 rounded-xl bg-slate-50 dark:bg-slate-800/80 border border-slate-200 dark:border-slate-700 space-y-1.5">
                            <div class="flex items-center justify-between">
                                <span class="px-2 py-0.5 rounded font-bold bg-emerald-500/20 text-emerald-700 dark:text-emerald-300 text-[10px]">POST</span>
                                <span class="text-slate-400 text-[10px]">Inbound Webhook IoT / ERP</span>
                            </div>
                            <div class="font-bold text-slate-800 dark:text-slate-200">/api/v1/webhooks/inbound</div>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>
</template>
