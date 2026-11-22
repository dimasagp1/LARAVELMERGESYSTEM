<script setup>
import { ref, computed, watch } from 'vue';
import axios from 'axios';
import {
    X,
    Shield,
    Check,
    CheckCheck,
    ChevronDown,
    ChevronRight,
    Search,
    SlidersHorizontal,
    Sparkles,
    UserCheck,
    Users,
    AlertCircle,
    LayoutDashboard,
    FileSpreadsheet,
    Scale,
    Wrench,
    FileText,
    Building2,
    CalendarCheck,
    CalendarOff,
    Timer,
    Banknote,
    Target,
    FileCheck,
    Receipt,
    Layers,
    Truck,
    Wallet,
    Tag,
    Calendar,
    Gauge,
    AlertTriangle,
    GitCommit,
    AlertOctagon,
    CheckCircle2,
    Calculator,
    FlaskConical,
    Boxes,
    Award,
    Cpu,
    ShieldCheck,
    ArrowRightCircle,
    CornerUpLeft,
    FileCheck2,
    Warehouse,
    Lightbulb,
    Package,
} from 'lucide-vue-next';

const props = defineProps({
    show: Boolean,
    userId: [Number, String],
});

const emit = defineEmits(['close', 'saved']);

const iconMap = {
    LayoutDashboard,
    FileSpreadsheet,
    Scale,
    Wrench,
    FileText,
    Building2,
    CalendarCheck,
    CalendarOff,
    Timer,
    Banknote,
    Target,
    FileCheck,
    Receipt,
    Layers,
    Truck,
    Wallet,
    Tag,
    Calendar,
    Gauge,
    AlertTriangle,
    GitCommit,
    AlertOctagon,
    CheckCircle2,
    Calculator,
    FlaskConical,
    Boxes,
    Award,
    Cpu,
    ShieldCheck,
    ArrowRightCircle,
    CornerUpLeft,
    FileCheck2,
    Warehouse,
    Lightbulb,
    Package,
};

function getMenuIcon(iconName) {
    return iconMap[iconName] || FileText;
}

const loading = ref(false);
const saving = ref(false);
const searchQuery = ref('');
const userData = ref(null);
const modulesList = ref([]);
const expandedModules = ref({});

// Map of slug -> boolean
const moduleChecked = ref({});
// Map of slug -> { route: boolean }
const menuChecked = ref({});

watch(() => props.userId, (newId) => {
    if (props.show && newId) {
        fetchPermissions(newId);
    }
});

watch(() => props.show, (isOpen) => {
    if (isOpen && props.userId) {
        fetchPermissions(props.userId);
    }
});

async function fetchPermissions(id) {
    loading.value = true;
    try {
        const res = await axios.get(`/settings/users/${id}/permissions`);
        userData.value = res.data.user;
        modulesList.value = res.data.modules;

        // Populate initial state
        const modState = {};
        const menuState = {};
        const expState = {};

        res.data.modules.forEach(mod => {
            modState[mod.slug] = Boolean(mod.is_allowed);
            menuState[mod.slug] = {};
            expState[mod.slug] = true; // Expanded by default

            mod.menus.forEach(menu => {
                menuState[mod.slug][menu.route] = Boolean(menu.is_allowed);
            });
        });

        moduleChecked.value = modState;
        menuChecked.value = menuState;
        expandedModules.value = expState;
    } catch (err) {
        console.error('Gagal mengambil data hak akses user:', err);
    } finally {
        loading.value = false;
    }
}

function toggleModule(slug) {
    const nextVal = !moduleChecked.value[slug];
    moduleChecked.value[slug] = nextVal;

    // Toggle all sub-menus within this module
    if (menuChecked.value[slug]) {
        Object.keys(menuChecked.value[slug]).forEach(route => {
            menuChecked.value[slug][route] = nextVal;
        });
    }
}

function toggleMenu(slug, route) {
    const nextVal = !menuChecked.value[slug][route];
    menuChecked.value[slug][route] = nextVal;

    // If menu is checked, auto-check the parent module
    if (nextVal) {
        moduleChecked.value[slug] = true;
    } else {
        // If all menus in this module are now unchecked, optionally uncheck module
        const anyChecked = Object.values(menuChecked.value[slug]).some(Boolean);
        if (!anyChecked) {
            moduleChecked.value[slug] = false;
        }
    }
}

function checkAll() {
    modulesList.value.forEach(mod => {
        moduleChecked.value[mod.slug] = true;
        if (menuChecked.value[mod.slug]) {
            Object.keys(menuChecked.value[mod.slug]).forEach(route => {
                menuChecked.value[mod.slug][route] = true;
            });
        }
    });
}

function uncheckAll() {
    modulesList.value.forEach(mod => {
        moduleChecked.value[mod.slug] = false;
        if (menuChecked.value[mod.slug]) {
            Object.keys(menuChecked.value[mod.slug]).forEach(route => {
                menuChecked.value[mod.slug][route] = false;
            });
        }
    });
}

async function applyPresetRole(preset) {
    if (!props.userId) return;
    loading.value = true;
    try {
        await axios.post(`/settings/users/${props.userId}/preset`, { preset });
        await fetchPermissions(props.userId);
    } catch (err) {
        console.error('Gagal menerapkan preset:', err);
    } finally {
        loading.value = false;
    }
}

const filteredModules = computed(() => {
    if (!searchQuery.value) return modulesList.value;
    const q = searchQuery.value.toLowerCase();

    return modulesList.value.filter(mod => {
        const modMatch = mod.name.toLowerCase().includes(q) || mod.slug.toLowerCase().includes(q);
        const menuMatch = mod.menus.some(m => m.name.toLowerCase().includes(q) || m.route.toLowerCase().includes(q));
        return modMatch || menuMatch;
    });
});

const summaryStats = computed(() => {
    let allowedModules = 0;
    let allowedMenus = 0;
    let totalMenus = 0;

    modulesList.value.forEach(mod => {
        if (moduleChecked.value[mod.slug]) allowedModules++;
        if (menuChecked.value[mod.slug]) {
            Object.values(menuChecked.value[mod.slug]).forEach(checked => {
                totalMenus++;
                if (checked) allowedMenus++;
            });
        }
    });

    return {
        allowedModules,
        totalModules: modulesList.value.length,
        allowedMenus,
        totalMenus,
    };
});

async function save() {
    saving.value = true;
    try {
        const payload = {
            modules: {},
            menus: {},
        };

        modulesList.value.forEach(mod => {
            payload.modules[mod.slug] = {
                is_allowed: Boolean(moduleChecked.value[mod.slug]),
                can_create: true,
                can_edit: true,
                can_delete: false,
                can_approve: false,
            };

            payload.menus[mod.slug] = {};
            if (menuChecked.value[mod.slug]) {
                Object.keys(menuChecked.value[mod.slug]).forEach(route => {
                    payload.menus[mod.slug][route] = {
                        is_allowed: Boolean(menuChecked.value[mod.slug][route]),
                        can_create: true,
                        can_edit: true,
                        can_delete: false,
                        can_approve: false,
                    };
                });
            }
        });

        await axios.post(`/settings/users/${props.userId}/permissions`, payload);
        emit('saved');
        emit('close');
    } catch (err) {
        console.error('Gagal menyimpan hak akses:', err);
    } finally {
        saving.value = false;
    }
}
</script>

<template>
    <div
        v-if="show"
        class="fixed inset-0 z-50 flex items-center justify-center p-4 sm:p-6 bg-slate-950/70 backdrop-blur-xs"
        @click.self="emit('close')"
    >
        <div class="bg-white dark:bg-slate-900 w-full max-w-4xl max-h-[92vh] rounded-3xl border border-slate-200 dark:border-slate-800 shadow-2xl flex flex-col overflow-hidden animate-in fade-in zoom-in-95 duration-150">
            <!-- Modal Header -->
            <div class="px-6 py-5 border-b border-slate-200 dark:border-slate-800 flex items-center justify-between bg-slate-50/50 dark:bg-slate-800/40">
                <div class="flex items-center gap-3.5">
                    <div class="w-11 h-11 rounded-2xl bg-emerald-600/10 text-emerald-600 dark:text-emerald-400 flex items-center justify-center border border-emerald-500/20 shadow-xs">
                        <Shield class="w-6 h-6" />
                    </div>
                    <div>
                        <div class="flex items-center gap-2">
                            <h2 class="text-lg font-bold text-slate-900 dark:text-white">
                                Atur Hak Akses Modul & Menu
                            </h2>
                            <span v-if="userData?.is_superadmin" class="px-2 py-0.5 rounded-full text-[11px] font-bold bg-amber-500/20 text-amber-700 dark:text-amber-300 border border-amber-500/30">
                                Super Administrator
                            </span>
                        </div>
                        <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">
                            Pengguna: <span class="font-semibold text-slate-700 dark:text-slate-200">{{ userData?.name || 'Memuat...' }}</span> ({{ userData?.email }}) &bull; Dept: {{ userData?.department }}
                        </p>
                    </div>
                </div>

                <button
                    @click="emit('close')"
                    class="p-2 rounded-xl text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 hover:bg-slate-100 dark:hover:bg-slate-800 transition"
                >
                    <X class="w-5 h-5" />
                </button>
            </div>

            <!-- Toolbar: Quick Presets & Search Bar -->
            <div class="px-6 py-3.5 bg-slate-100/60 dark:bg-slate-950/40 border-b border-slate-200 dark:border-slate-800 flex flex-col sm:flex-row items-stretch sm:items-center justify-between gap-3">
                <!-- Search Filter -->
                <div class="relative flex-1 max-w-xs">
                    <Search class="w-4 h-4 absolute left-3 top-1/2 -translate-y-1/2 text-slate-400" />
                    <input
                        v-model="searchQuery"
                        type="text"
                        placeholder="Cari modul atau menu..."
                        class="w-full pl-9 pr-3 py-1.5 text-xs rounded-xl bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-slate-900 dark:text-white placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-emerald-500"
                    />
                </div>

                <!-- Presets Button Group -->
                <div class="flex flex-wrap items-center gap-1.5">
                    <span class="text-[11px] font-semibold text-slate-400 mr-1 hidden lg:inline">Preset:</span>
                    <button
                        @click="checkAll"
                        type="button"
                        class="px-2.5 py-1 text-[11px] font-medium rounded-lg bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 hover:bg-slate-50 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-300 transition"
                    >
                        Pilih Semua
                    </button>
                    <button
                        @click="uncheckAll"
                        type="button"
                        class="px-2.5 py-1 text-[11px] font-medium rounded-lg bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 hover:bg-slate-50 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-300 transition"
                    >
                        Kosongkan
                    </button>
                    <button
                        @click="applyPresetRole('operator')"
                        type="button"
                        class="px-2.5 py-1 text-[11px] font-medium rounded-lg bg-rose-50 dark:bg-rose-950/40 border border-rose-200 dark:border-rose-800/60 text-rose-700 dark:text-rose-300 hover:bg-rose-100 transition"
                    >
                        Operator Produksi
                    </button>
                    <button
                        @click="applyPresetRole('finance')"
                        type="button"
                        class="px-2.5 py-1 text-[11px] font-medium rounded-lg bg-emerald-50 dark:bg-emerald-950/40 border border-emerald-200 dark:border-emerald-800/60 text-emerald-700 dark:text-emerald-300 hover:bg-emerald-100 transition"
                    >
                        Finance & FAT
                    </button>
                    <button
                        @click="applyPresetRole('qc_qa')"
                        type="button"
                        class="px-2.5 py-1 text-[11px] font-medium rounded-lg bg-teal-50 dark:bg-teal-950/40 border border-teal-200 dark:border-teal-800/60 text-teal-700 dark:text-teal-300 hover:bg-teal-100 transition"
                    >
                        QC / QA Lab
                    </button>
                </div>
            </div>

            <!-- Modal Content (Module & Menu Checklist Matrix) -->
            <div class="flex-1 overflow-y-auto p-6 space-y-4">
                <div v-if="loading" class="py-16 text-center text-slate-400">
                    <div class="inline-block w-8 h-8 border-3 border-emerald-600 border-t-transparent rounded-full animate-spin"></div>
                    <p class="text-xs mt-3 font-medium">Memuat matriks hak akses pengguna...</p>
                </div>

                <template v-else>
                    <div
                        v-for="mod in filteredModules"
                        :key="mod.slug"
                        class="rounded-2xl border transition duration-150 overflow-hidden"
                        :class="[
                            moduleChecked[mod.slug]
                                ? 'border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-900/90 shadow-xs'
                                : 'border-slate-200 dark:border-slate-800/80 bg-slate-50/50 dark:bg-slate-900/40 opacity-75'
                        ]"
                    >
                        <!-- Module Header with Master Checkbox -->
                        <div class="px-4 py-3.5 flex items-center justify-between bg-slate-50/80 dark:bg-slate-800/50 border-b border-slate-100 dark:border-slate-800/80">
                            <div class="flex items-center gap-3">
                                <!-- Checkbox Module -->
                                <label class="relative flex items-center cursor-pointer">
                                    <input
                                        type="checkbox"
                                        :checked="moduleChecked[mod.slug]"
                                        @change="toggleModule(mod.slug)"
                                        class="sr-only peer"
                                    />
                                    <div class="w-5 h-5 rounded-md border-2 border-slate-300 dark:border-slate-600 peer-checked:bg-emerald-600 peer-checked:border-emerald-600 flex items-center justify-center transition">
                                        <Check v-if="moduleChecked[mod.slug]" class="w-3.5 h-3.5 text-white stroke-[3]" />
                                    </div>
                                </label>

                                <div class="flex items-center gap-2.5">
                                    <span class="font-bold text-sm text-slate-900 dark:text-white">
                                        {{ mod.name }}
                                    </span>
                                    <span class="font-mono text-[10px] uppercase px-2 py-0.5 rounded-md bg-slate-200/70 dark:bg-slate-700/60 text-slate-600 dark:text-slate-300">
                                        {{ mod.slug }}
                                    </span>
                                </div>
                            </div>

                            <div class="flex items-center gap-3">
                                <span class="text-xs font-semibold text-slate-500 dark:text-slate-400">
                                    {{ Object.values(menuChecked[mod.slug] || {}).filter(Boolean).length }} dari {{ mod.menus.length }} menu aktif
                                </span>

                                <button
                                    @click="expandedModules[mod.slug] = !expandedModules[mod.slug]"
                                    type="button"
                                    class="p-1 rounded-lg text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 hover:bg-slate-200/50 dark:hover:bg-slate-700 transition"
                                >
                                    <ChevronDown
                                        class="w-4 h-4 transition-transform duration-200"
                                        :class="{ 'rotate-180': expandedModules[mod.slug] }"
                                    />
                                </button>
                            </div>
                        </div>

                        <!-- Menu Checklist Items -->
                        <div
                            v-if="expandedModules[mod.slug]"
                            class="p-4 grid grid-cols-1 sm:grid-cols-2 gap-2.5 bg-white dark:bg-slate-900"
                        >
                            <label
                                v-for="menu in mod.menus"
                                :key="menu.route"
                                class="flex items-center justify-between p-2.5 rounded-xl border transition cursor-pointer"
                                :class="[
                                    menuChecked[mod.slug]?.[menu.route]
                                        ? 'border-emerald-200 dark:border-emerald-900/60 bg-emerald-50/40 dark:bg-emerald-950/20 text-slate-900 dark:text-white'
                                        : 'border-slate-100 dark:border-slate-800/80 bg-slate-50/30 dark:bg-slate-800/20 text-slate-400 hover:bg-slate-50 dark:hover:bg-slate-800/40'
                                ]"
                            >
                                <div class="flex items-center gap-2.5 min-w-0 pr-2">
                                    <component
                                        :is="getMenuIcon(menu.icon)"
                                        class="w-4 h-4 shrink-0"
                                        :class="menuChecked[mod.slug]?.[menu.route] ? 'text-emerald-600 dark:text-emerald-400' : 'text-slate-400'"
                                    />
                                    <div class="truncate">
                                        <div class="flex items-center gap-1.5">
                                            <span class="text-xs font-semibold truncate">{{ menu.name }}</span>
                                            <span v-if="menu.category" class="text-[9px] px-1.5 py-0.2 rounded-md bg-slate-100 dark:bg-slate-800 text-slate-500 shrink-0 font-medium">
                                                {{ menu.category }}
                                            </span>
                                        </div>
                                        <div class="text-[10px] text-slate-400 font-mono truncate">{{ menu.route }}</div>
                                    </div>
                                </div>

                                <input
                                    type="checkbox"
                                    :checked="Boolean(menuChecked[mod.slug]?.[menu.route])"
                                    @change="toggleMenu(mod.slug, menu.route)"
                                    class="w-4 h-4 rounded text-emerald-600 focus:ring-emerald-500 border-slate-300 dark:border-slate-600 shrink-0"
                                />
                            </label>
                        </div>
                    </div>
                </template>
            </div>

            <!-- Modal Footer with Summary & Action Buttons -->
            <div class="px-6 py-4 bg-slate-50 dark:bg-slate-800/60 border-t border-slate-200 dark:border-slate-800 flex items-center justify-between">
                <div class="text-xs text-slate-500 dark:text-slate-400">
                    Total Diizinkan: <strong class="text-emerald-600 dark:text-emerald-400">{{ summaryStats.allowedModules }}</strong> modul, <strong class="text-emerald-600 dark:text-emerald-400">{{ summaryStats.allowedMenus }}</strong> menu
                </div>

                <div class="flex items-center gap-2.5">
                    <button
                        @click="emit('close')"
                        type="button"
                        class="px-4 py-2 rounded-xl text-xs font-semibold text-slate-600 dark:text-slate-300 hover:bg-slate-200 dark:hover:bg-slate-700 transition"
                    >
                        Batal
                    </button>
                    <button
                        @click="save"
                        :disabled="saving || loading"
                        type="button"
                        class="px-5 py-2 rounded-xl text-xs font-semibold bg-emerald-600 hover:bg-emerald-700 text-white shadow-md hover:shadow-lg disabled:opacity-50 transition flex items-center gap-2"
                    >
                        <CheckCheck v-if="!saving" class="w-4 h-4" />
                        <span v-if="saving">Menyimpan...</span>
                        <span v-else>Simpan Hak Akses</span>
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>
