<script setup>
import { ref } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import PermissionChecklistModal from '@/Pages/Settings/Users/PermissionChecklistModal.vue';
import NotificationCenterDropdown from '@/Components/NotificationCenterDropdown.vue';
import {
    Users,
    UserPlus,
    Shield,
    ShieldCheck,
    Search,
    Filter,
    ArrowLeft,
    Check,
    X,
    KeyRound,
    Edit3,
    ToggleLeft,
    ToggleRight,
    Building2,
    Lock,
    Unlock,
    Settings,
    MoreHorizontal,
    ChevronRight,
} from 'lucide-vue-next';

const props = defineProps({
    users: Object,
    departments: Array,
    roles: Array,
    filters: Object,
    stats: Object,
});

const searchQuery = ref(props.filters?.search || '');
const departmentFilter = ref(props.filters?.department_id || '');
const statusFilter = ref(props.filters?.status || '');

function handleFilter() {
    router.get('/settings/users', {
        search: searchQuery.value,
        department_id: departmentFilter.value,
        status: statusFilter.value,
    }, {
        preserveState: true,
        replace: true,
    });
}

function resetFilter() {
    searchQuery.value = '';
    departmentFilter.value = '';
    statusFilter.value = '';
    handleFilter();
}

// User Create / Edit Modal state
const showUserModal = ref(false);
const isEditing = ref(false);
const editingUserId = ref(null);
const userForm = ref({
    name: '',
    email: '',
    password: '',
    department_id: '',
    role: 'operator',
    status: 'active',
});

function openCreateModal() {
    isEditing.value = false;
    editingUserId.value = null;
    userForm.value = {
        name: '',
        email: '',
        password: '',
        department_id: '',
        role: 'operator',
        status: 'active',
    };
    showUserModal.value = true;
}

function openEditModal(user) {
    isEditing.value = true;
    editingUserId.value = user.id;
    userForm.value = {
        name: user.name,
        email: user.email,
        password: '',
        department_id: user.department_id || '',
        role: user.roles[0] || '',
        status: user.status,
    };
    showUserModal.value = true;
}

function submitUser() {
    if (isEditing.value) {
        router.put(`/settings/users/${editingUserId.value}`, userForm.value, {
            onSuccess: () => {
                showUserModal.value = false;
            },
        });
    } else {
        router.post('/settings/users', userForm.value, {
            onSuccess: () => {
                showUserModal.value = false;
            },
        });
    }
}

function toggleStatus(userId) {
    router.post(`/settings/users/${userId}/toggle-status`, {}, {
        preserveScroll: true,
    });
}

// Permission Checklist Modal state
const showPermissionModal = ref(false);
const selectedUserId = ref(null);

function openPermissions(userId) {
    selectedUserId.value = userId;
    showPermissionModal.value = true;
}
</script>

<template>
    <Head title="Manajemen Pengguna & Hak Akses - HerbaTech ERP" />

    <div class="min-h-screen bg-slate-50 dark:bg-slate-950 text-slate-800 dark:text-slate-100 flex flex-col font-sans transition-colors duration-200">
        <!-- Top Workspace Bar -->
        <header class="sticky top-0 z-40 bg-white/95 dark:bg-slate-900/95 backdrop-blur border-b border-slate-200 dark:border-slate-800 shadow-xs">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex items-center justify-between h-16">
                    <div class="flex items-center gap-3">
                        <Link
                            href="/settings"
                            class="p-2 rounded-xl text-slate-500 hover:text-slate-900 dark:hover:text-white hover:bg-slate-100 dark:hover:bg-slate-800 transition"
                            title="Kembali ke Pengaturan Sistem"
                        >
                            <ArrowLeft class="w-5 h-5" />
                        </Link>

                        <div class="h-6 w-px bg-slate-200 dark:bg-slate-700" />

                        <div class="flex items-center gap-2">
                            <span class="text-xs font-bold px-2 py-0.5 rounded bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 border border-emerald-500/20">
                                Sistem & Keamanan
                            </span>
                            <ChevronRight class="w-3.5 h-3.5 text-slate-400" />
                            <h1 class="text-sm font-bold text-slate-900 dark:text-white">
                                Manajemen Pengguna & Hak Akses
                            </h1>
                        </div>
                    </div>

                    <div class="flex items-center gap-3">
                        <NotificationCenterDropdown />
                        <Link
                            href="/settings"
                            class="flex items-center gap-1.5 px-3 py-1.5 rounded-xl text-xs font-semibold bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 transition"
                        >
                            <Settings class="w-3.5 h-3.5" />
                            <span>Pengaturan Umum</span>
                        </Link>
                    </div>
                </div>
            </div>
        </header>

        <!-- Main Workspace -->
        <main class="flex-1 max-w-7xl w-full mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-6">
            <!-- Header Title & Quick Action -->
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div>
                    <h2 class="text-2xl font-black tracking-tight text-slate-900 dark:text-white flex items-center gap-2.5">
                        <Users class="w-7 h-7 text-emerald-600 dark:text-emerald-400" />
                        <span>Pengguna & Hak Akses Modul / Menu</span>
                    </h2>
                    <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">
                        Kendali akses granular berbasis perceklistan modul dan menu untuk seluruh karyawan dan auditor sistem.
                    </p>
                </div>

                <button
                    @click="openCreateModal"
                    class="inline-flex items-center gap-2 px-4 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white font-semibold text-xs rounded-xl shadow-md hover:shadow-lg transition self-start sm:self-auto"
                >
                    <UserPlus class="w-4 h-4" />
                    <span>Tambah Pengguna Baru</span>
                </button>
            </div>

            <!-- Stats Bar -->
            <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
                <div class="bg-white dark:bg-slate-900 p-4 rounded-2xl border border-slate-200 dark:border-slate-800 shadow-xs flex items-center gap-3.5">
                    <div class="w-10 h-10 rounded-xl bg-blue-500/10 text-blue-600 dark:text-blue-400 flex items-center justify-center shrink-0">
                        <Users class="w-5 h-5" />
                    </div>
                    <div>
                        <div class="text-2xl font-bold text-slate-900 dark:text-white">{{ stats.total_users }}</div>
                        <div class="text-xs text-slate-500 dark:text-slate-400">Total Pengguna Terdaftar</div>
                    </div>
                </div>

                <div class="bg-white dark:bg-slate-900 p-4 rounded-2xl border border-slate-200 dark:border-slate-800 shadow-xs flex items-center gap-3.5">
                    <div class="w-10 h-10 rounded-xl bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 flex items-center justify-center shrink-0">
                        <UserCheck class="w-5 h-5" />
                    </div>
                    <div>
                        <div class="text-2xl font-bold text-slate-900 dark:text-white">{{ stats.active_users }}</div>
                        <div class="text-xs text-slate-500 dark:text-slate-400">Pengguna Aktif</div>
                    </div>
                </div>

                <div class="bg-white dark:bg-slate-900 p-4 rounded-2xl border border-slate-200 dark:border-slate-800 shadow-xs flex items-center gap-3.5">
                    <div class="w-10 h-10 rounded-xl bg-amber-500/10 text-amber-600 dark:text-amber-400 flex items-center justify-center shrink-0">
                        <ShieldCheck class="w-5 h-5" />
                    </div>
                    <div>
                        <div class="text-2xl font-bold text-slate-900 dark:text-white">{{ stats.superadmins }}</div>
                        <div class="text-xs text-slate-500 dark:text-slate-400">Super Administrator</div>
                    </div>
                </div>

                <div class="bg-white dark:bg-slate-900 p-4 rounded-2xl border border-slate-200 dark:border-slate-800 shadow-xs flex items-center gap-3.5">
                    <div class="w-10 h-10 rounded-xl bg-purple-500/10 text-purple-600 dark:text-purple-400 flex items-center justify-center shrink-0">
                        <Building2 class="w-5 h-5" />
                    </div>
                    <div>
                        <div class="text-2xl font-bold text-slate-900 dark:text-white">{{ stats.total_departments }}</div>
                        <div class="text-xs text-slate-500 dark:text-slate-400">Departemen Kerja</div>
                    </div>
                </div>
            </div>

            <!-- Filter & Search Toolbar -->
            <div class="bg-white dark:bg-slate-900 p-4 rounded-2xl border border-slate-200 dark:border-slate-800 shadow-xs flex flex-col sm:flex-row items-stretch sm:items-center justify-between gap-3">
                <div class="flex-1 flex flex-col sm:flex-row items-center gap-3">
                    <div class="relative w-full sm:w-72">
                        <Search class="w-4 h-4 absolute left-3 top-1/2 -translate-y-1/2 text-slate-400" />
                        <input
                            v-model="searchQuery"
                            @keyup.enter="handleFilter"
                            type="text"
                            placeholder="Cari nama atau email..."
                            class="w-full pl-9 pr-3 py-2 text-xs rounded-xl bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-slate-900 dark:text-white placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-emerald-500"
                        />
                    </div>

                    <select
                        v-model="departmentFilter"
                        @change="handleFilter"
                        class="w-full sm:w-52 py-2 px-3 text-xs rounded-xl bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-emerald-500"
                    >
                        <option value="">Semua Departemen</option>
                        <option v-for="dept in departments" :key="dept.id" :value="dept.id">
                            {{ dept.name }}
                        </option>
                    </select>

                    <select
                        v-model="statusFilter"
                        @change="handleFilter"
                        class="w-full sm:w-36 py-2 px-3 text-xs rounded-xl bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-emerald-500"
                    >
                        <option value="">Semua Status</option>
                        <option value="active">Aktif</option>
                        <option value="inactive">Nonaktif</option>
                    </select>
                </div>

                <div class="flex items-center gap-2">
                    <button
                        @click="handleFilter"
                        class="px-4 py-2 bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-300 font-semibold text-xs rounded-xl transition"
                    >
                        Filter
                    </button>
                    <button
                        v-if="searchQuery || departmentFilter || statusFilter"
                        @click="resetFilter"
                        class="px-3 py-2 text-rose-600 hover:bg-rose-50 dark:hover:bg-rose-950/30 text-xs font-semibold rounded-xl transition"
                    >
                        Reset
                    </button>
                </div>
            </div>

            <!-- Users Table -->
            <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 shadow-xs overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs">
                        <thead class="bg-slate-50 dark:bg-slate-800/60 text-slate-500 dark:text-slate-400 font-semibold uppercase tracking-wider border-b border-slate-200 dark:border-slate-800">
                            <tr>
                                <th class="px-6 py-3.5">Nama & Email</th>
                                <th class="px-6 py-3.5">Departemen</th>
                                <th class="px-6 py-3.5">Jabatan / Role</th>
                                <th class="px-6 py-3.5">Hak Akses Modul</th>
                                <th class="px-6 py-3.5">Status</th>
                                <th class="px-6 py-3.5 text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 dark:divide-slate-800 text-slate-700 dark:text-slate-200">
                            <tr
                                v-for="user in users.data"
                                :key="user.id"
                                class="hover:bg-slate-50/60 dark:hover:bg-slate-800/40 transition"
                            >
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-3">
                                        <div class="w-9 h-9 rounded-full bg-emerald-600/10 text-emerald-700 dark:text-emerald-400 font-bold flex items-center justify-center border border-emerald-500/20 text-xs">
                                            {{ user.name.split(' ').map(n => n[0]).slice(0, 2).join('') }}
                                        </div>
                                        <div>
                                            <div class="font-bold text-slate-900 dark:text-white flex items-center gap-1.5">
                                                <span>{{ user.name }}</span>
                                                <ShieldCheck v-if="user.is_superadmin" class="w-3.5 h-3.5 text-amber-500" title="Super Administrator" />
                                            </div>
                                            <div class="text-[11px] text-slate-400 font-mono">{{ user.email }}</div>
                                        </div>
                                    </div>
                                </td>

                                <td class="px-6 py-4">
                                    <span class="inline-flex items-center gap-1.5 text-xs text-slate-600 dark:text-slate-300">
                                        <Building2 class="w-3.5 h-3.5 text-slate-400" />
                                        {{ user.department }}
                                    </span>
                                </td>

                                <td class="px-6 py-4">
                                    <div class="flex flex-wrap gap-1">
                                        <span
                                            v-for="role in user.roles"
                                            :key="role"
                                            class="px-2 py-0.5 rounded-md text-[11px] font-semibold"
                                            :class="[
                                                role === 'superadmin'
                                                    ? 'bg-amber-50 dark:bg-amber-950/40 text-amber-700 dark:text-amber-300 border border-amber-300 dark:border-amber-700'
                                                    : 'bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 border border-slate-200 dark:border-slate-700'
                                            ]"
                                        >
                                            {{ role }}
                                        </span>
                                    </div>
                                </td>

                                <td class="px-6 py-4">
                                    <button
                                        @click="openPermissions(user.id)"
                                        class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg text-xs font-semibold transition"
                                        :class="[
                                            user.is_superadmin || !user.has_restrictions
                                                ? 'bg-emerald-50 dark:bg-emerald-950/40 text-emerald-700 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800 hover:bg-emerald-100'
                                                : 'bg-blue-50 dark:bg-blue-950/40 text-blue-700 dark:text-blue-300 border border-blue-200 dark:border-blue-800 hover:bg-blue-100'
                                        ]"
                                        title="Klik untuk membuka checklist hak akses"
                                    >
                                        <Shield class="w-3.5 h-3.5" />
                                        <span>{{ user.allowed_module_count }} / {{ user.all_modules_count }} Modul</span>
                                    </button>
                                </td>

                                <td class="px-6 py-4">
                                    <button
                                        @click="toggleStatus(user.id)"
                                        class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[11px] font-bold border transition cursor-pointer"
                                        :class="[
                                            user.status === 'active'
                                                ? 'bg-emerald-50 text-emerald-700 border-emerald-200 dark:bg-emerald-950/50 dark:text-emerald-300'
                                                : 'bg-slate-100 text-slate-500 border-slate-200 dark:bg-slate-800 dark:text-slate-400'
                                        ]"
                                    >
                                        <span class="w-1.5 h-1.5 rounded-full" :class="user.status === 'active' ? 'bg-emerald-500 animate-pulse' : 'bg-slate-400'"></span>
                                        <span>{{ user.status === 'active' ? 'Aktif' : 'Nonaktif' }}</span>
                                    </button>
                                </td>

                                <td class="px-6 py-4 text-right">
                                    <div class="inline-flex items-center gap-1.5">
                                        <button
                                            @click="openPermissions(user.id)"
                                            class="px-2.5 py-1.5 text-xs font-semibold rounded-lg bg-emerald-600/10 hover:bg-emerald-600/20 text-emerald-700 dark:text-emerald-400 transition"
                                            title="Atur Hak Akses Checklist"
                                        >
                                            Hak Akses
                                        </button>
                                        <button
                                            @click="openEditModal(user)"
                                            class="p-1.5 text-slate-400 hover:text-slate-700 dark:hover:text-slate-200 hover:bg-slate-100 dark:hover:bg-slate-800 rounded-lg transition"
                                            title="Edit Profil User"
                                        >
                                            <Edit3 class="w-4 h-4" />
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Pagination Footer -->
                <div
                    v-if="users.links && users.links.length > 3"
                    class="px-6 py-4 border-t border-slate-200 dark:border-slate-800 flex items-center justify-between text-xs"
                >
                    <div class="text-slate-500 dark:text-slate-400">
                        Menampilkan {{ users.from }} sampai {{ users.to }} dari {{ users.total }} pengguna
                    </div>
                    <div class="flex items-center gap-1">
                        <Link
                            v-for="(link, i) in users.links"
                            :key="i"
                            :href="link.url || '#'"
                            :class="[
                                'px-3 py-1.5 rounded-lg font-medium transition',
                                link.active
                                    ? 'bg-emerald-600 text-white font-bold'
                                    : (link.url ? 'hover:bg-slate-100 dark:hover:bg-slate-800 text-slate-600 dark:text-slate-300' : 'text-slate-300 dark:text-slate-600 cursor-not-allowed')
                            ]"
                            v-html="link.label"
                        />
                    </div>
                </div>
            </div>
        </main>

        <!-- User Create / Edit Modal -->
        <div
            v-if="showUserModal"
            class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/70 backdrop-blur-xs"
            @click.self="showUserModal = false"
        >
            <div class="bg-white dark:bg-slate-900 w-full max-w-lg rounded-3xl border border-slate-200 dark:border-slate-800 shadow-2xl p-6 space-y-5 animate-in fade-in zoom-in-95 duration-150">
                <div class="flex items-center justify-between border-b border-slate-100 dark:border-slate-800 pb-3">
                    <h3 class="text-base font-bold text-slate-900 dark:text-white">
                        {{ isEditing ? 'Edit Data Pengguna' : 'Tambah Pengguna Baru' }}
                    </h3>
                    <button @click="showUserModal = false" class="p-1 text-slate-400 hover:text-slate-600">
                        <X class="w-5 h-5" />
                    </button>
                </div>

                <form @submit.prevent="submitUser" class="space-y-4 text-xs">
                    <div>
                        <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1">Nama Lengkap</label>
                        <input
                            v-model="userForm.name"
                            type="text"
                            required
                            placeholder="e.g. Dr. Rina Wati, M.Farm"
                            class="w-full px-3 py-2 rounded-xl bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-emerald-500"
                        />
                    </div>

                    <div>
                        <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1">Alamat Email</label>
                        <input
                            v-model="userForm.email"
                            type="email"
                            required
                            placeholder="nama@herbatech.com"
                            class="w-full px-3 py-2 rounded-xl bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-emerald-500"
                        />
                    </div>

                    <div>
                        <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1">
                            {{ isEditing ? 'Kata Sandi Baru (Kosongkan jika tidak diubah)' : 'Kata Sandi' }}
                        </label>
                        <input
                            v-model="userForm.password"
                            type="password"
                            :required="!isEditing"
                            placeholder="Minimal 6 karakter"
                            class="w-full px-3 py-2 rounded-xl bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-emerald-500"
                        />
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1">Departemen</label>
                            <select
                                v-model="userForm.department_id"
                                class="w-full px-3 py-2 rounded-xl bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-emerald-500"
                            >
                                <option value="">Lintas Departemen</option>
                                <option v-for="dept in departments" :key="dept.id" :value="dept.id">
                                    {{ dept.name }}
                                </option>
                            </select>
                        </div>

                        <div>
                            <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1">Role / Jabatan</label>
                            <select
                                v-model="userForm.role"
                                class="w-full px-3 py-2 rounded-xl bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-emerald-500"
                            >
                                <option v-for="role in roles" :key="role" :value="role">{{ role }}</option>
                            </select>
                        </div>
                    </div>

                    <div>
                        <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1">Status Akun</label>
                        <select
                            v-model="userForm.status"
                            class="w-full px-3 py-2 rounded-xl bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-emerald-500"
                        >
                            <option value="active">Aktif (Dapat Login)</option>
                            <option value="inactive">Nonaktif (Akses Dibekukan)</option>
                        </select>
                    </div>

                    <div class="pt-3 border-t border-slate-100 dark:border-slate-800 flex justify-end gap-2.5">
                        <button
                            @click="showUserModal = false"
                            type="button"
                            class="px-4 py-2 rounded-xl text-xs font-semibold text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800 transition"
                        >
                            Batal
                        </button>
                        <button
                            type="submit"
                            class="px-5 py-2 rounded-xl text-xs font-semibold bg-emerald-600 hover:bg-emerald-700 text-white shadow-md transition"
                        >
                            {{ isEditing ? 'Simpan Perubahan' : 'Buat Pengguna' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Permission Checklist Modal Drawer -->
        <PermissionChecklistModal
            :show="showPermissionModal"
            :user-id="selectedUserId"
            @close="showPermissionModal = false"
            @saved="router.reload({ preserveScroll: true })"
        />
    </div>
</template>
