<script setup>
import { ref } from 'vue';
import { Head, Link, useForm, usePage } from '@inertiajs/vue3';
import {
    Lock,
    Mail,
    Eye,
    EyeOff,
    LogIn,
    ShieldCheck,
    Building2,
    CheckCircle2,
    AlertCircle,
    UserCheck,
    Sparkles,
} from 'lucide-vue-next';

const props = defineProps({
    status: {
        type: String,
        default: null,
    },
    demoAccounts: {
        type: Array,
        default: () => [],
    },
});

const page = usePage();
const showPassword = ref(false);

const form = useForm({
    email: 'admin@herbatech.com',
    password: 'password123',
    remember: true,
});

const selectDemoAccount = (account) => {
    form.email = account.email;
    form.password = 'password123';
};

const submit = () => {
    form.post('/login', {
        onFinish: () => form.reset('password'),
    });
};
</script>

<template>
    <Head title="Masuk ke Sistem - HerbaTech Unified ERP" />

    <div class="min-h-screen bg-slate-950 flex flex-col justify-center items-center p-4 sm:p-6 lg:p-8 relative overflow-hidden selection:bg-emerald-500 selection:text-white">
        <!-- Background Ambient Glows -->
        <div class="absolute -top-40 -left-40 w-96 h-96 bg-emerald-500/10 rounded-full blur-3xl pointer-events-none"></div>
        <div class="absolute -bottom-40 -right-40 w-96 h-96 bg-teal-500/10 rounded-full blur-3xl pointer-events-none"></div>

        <div class="w-full max-w-md relative z-10 space-y-6">
            <!-- Brand Header -->
            <div class="text-center space-y-2">
                <div class="inline-flex items-center justify-center w-14 h-14 rounded-2xl bg-gradient-to-tr from-emerald-600 to-teal-500 shadow-xl shadow-emerald-500/20 text-white mb-2">
                    <Building2 class="w-7 h-7" />
                </div>
                <h1 class="text-2xl font-black tracking-tight text-white">
                    HerbaTech <span class="text-emerald-400">ERP</span>
                </h1>
                <p class="text-xs text-slate-400 font-medium">
                    Unified Enterprise Resource Planning Platform
                </p>
                <div class="flex items-center justify-center gap-2 pt-1">
                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-semibold bg-emerald-950/80 text-emerald-300 border border-emerald-800/50">
                        <ShieldCheck class="w-3 h-3 text-emerald-400" /> Enterprise Edition v2.0
                    </span>
                </div>
            </div>

            <!-- Login Card -->
            <div class="bg-slate-900/90 backdrop-blur-xl border border-slate-800 rounded-3xl p-6 sm:p-8 shadow-2xl space-y-6">
                <!-- Status/Flash alerts -->
                <div v-if="status" class="p-3.5 rounded-xl bg-emerald-950/60 border border-emerald-800 text-xs text-emerald-300 flex items-center gap-2">
                    <CheckCircle2 class="w-4 h-4 shrink-0 text-emerald-400" />
                    <span>{{ status }}</span>
                </div>

                <div v-if="form.errors.email || form.errors.password" class="p-3.5 rounded-xl bg-rose-950/60 border border-rose-800 text-xs text-rose-300 flex items-center gap-2">
                    <AlertCircle class="w-4 h-4 shrink-0 text-rose-400" />
                    <span>{{ form.errors.email || form.errors.password }}</span>
                </div>

                <form @submit.prevent="submit" class="space-y-4">
                    <!-- Email -->
                    <div class="space-y-1.5">
                        <label class="block text-xs font-semibold text-slate-300">Alamat Email</label>
                        <div class="relative">
                            <Mail class="w-4 h-4 absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-500" />
                            <input
                                v-model="form.email"
                                type="email"
                                required
                                autofocus
                                autocomplete="username"
                                placeholder="nama@herbatech.com"
                                class="w-full pl-10 pr-4 py-2.5 rounded-xl bg-slate-950 border border-slate-700/80 text-white text-xs placeholder:text-slate-500 focus:outline-none focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500 transition"
                            />
                        </div>
                    </div>

                    <!-- Password -->
                    <div class="space-y-1.5">
                        <div class="flex items-center justify-between">
                            <label class="block text-xs font-semibold text-slate-300">Kata Sandi</label>
                            <Link
                                href="/forgot-password"
                                class="text-[11px] text-emerald-400 hover:text-emerald-300 hover:underline transition font-medium"
                            >
                                Lupa kata sandi?
                            </Link>
                        </div>
                        <div class="relative">
                            <Lock class="w-4 h-4 absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-500" />
                            <input
                                v-model="form.password"
                                :type="showPassword ? 'text' : 'password'"
                                required
                                autocomplete="current-password"
                                placeholder="••••••••"
                                class="w-full pl-10 pr-10 py-2.5 rounded-xl bg-slate-950 border border-slate-700/80 text-white text-xs placeholder:text-slate-500 focus:outline-none focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500 transition"
                            />
                            <button
                                type="button"
                                @click="showPassword = !showPassword"
                                class="absolute right-3.5 top-1/2 -translate-y-1/2 text-slate-500 hover:text-slate-300 transition"
                            >
                                <EyeOff v-if="showPassword" class="w-4 h-4" />
                                <Eye v-else class="w-4 h-4" />
                            </button>
                        </div>
                    </div>

                    <!-- Remember Me -->
                    <div class="flex items-center justify-between pt-1">
                        <label class="flex items-center gap-2 cursor-pointer select-none">
                            <input
                                v-model="form.remember"
                                type="checkbox"
                                class="w-4 h-4 rounded border-slate-700 bg-slate-950 text-emerald-600 focus:ring-emerald-500 focus:ring-offset-slate-900"
                            />
                            <span class="text-xs text-slate-400">Ingat sesi saya</span>
                        </label>
                    </div>

                    <!-- Submit Button -->
                    <button
                        type="submit"
                        :disabled="form.processing"
                        class="w-full py-3 px-4 rounded-xl font-bold text-xs bg-emerald-600 hover:bg-emerald-500 text-white shadow-lg shadow-emerald-600/30 flex items-center justify-center gap-2 transition disabled:opacity-60 cursor-pointer"
                    >
                        <LogIn class="w-4 h-4" />
                        <span>{{ form.processing ? 'Memproses Masuk...' : 'Masuk ke Sistem' }}</span>
                    </button>
                </form>

                <!-- Fast Demo Account Selector -->
                <div class="pt-4 border-t border-slate-800 space-y-3">
                    <div class="flex items-center justify-between">
                        <span class="text-[11px] font-bold uppercase tracking-wider text-slate-400 flex items-center gap-1.5">
                            <Sparkles class="w-3.5 h-3.5 text-amber-400" />
                            Pilih Akun Demo Cepat
                        </span>
                        <span class="text-[10px] text-slate-400">Password: password123</span>
                    </div>

                    <div class="grid grid-cols-2 gap-2">
                        <button
                            v-for="acc in demoAccounts"
                            :key="acc.email"
                            type="button"
                            @click="selectDemoAccount(acc)"
                            :class="[
                                'text-left p-2 rounded-xl border text-[11px] transition flex flex-col justify-between cursor-pointer',
                                form.email === acc.email
                                    ? 'bg-emerald-950/60 border-emerald-500/70 text-emerald-300'
                                    : 'bg-slate-950/70 border-slate-800 text-slate-300 hover:border-slate-700'
                            ]"
                        >
                            <span class="font-bold truncate">{{ acc.name.split(' ')[0] }}</span>
                            <span class="text-[9px] text-slate-400 truncate">{{ acc.role }}</span>
                        </button>
                    </div>
                </div>
            </div>

            <!-- Footer note -->
            <p class="text-center text-[11px] text-slate-400">
                &copy; {{ new Date().getFullYear() }} HerbaTech Unified ERP &bull; Keamanan Terenkripsi
            </p>
        </div>
    </div>
</template>
