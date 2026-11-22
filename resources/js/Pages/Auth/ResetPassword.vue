<script setup>
import { ref } from 'vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import {
    Lock,
    Mail,
    Eye,
    EyeOff,
    CheckCircle2,
    AlertCircle,
    KeyRound,
    ArrowLeft,
} from 'lucide-vue-next';

const props = defineProps({
    token: {
        type: String,
        required: true,
    },
    email: {
        type: String,
        default: '',
    },
});

const showPassword = ref(false);

const form = useForm({
    token: props.token,
    email: props.email,
    password: '',
    password_confirmation: '',
});

const submit = () => {
    form.post('/reset-password', {
        onFinish: () => form.reset('password', 'password_confirmation'),
    });
};
</script>

<template>
    <Head title="Reset Kata Sandi - HerbaTech Unified ERP" />

    <div class="min-h-screen bg-slate-950 flex flex-col justify-center items-center p-4 sm:p-6 lg:p-8 relative overflow-hidden selection:bg-emerald-500 selection:text-white">
        <!-- Background Ambient Glow -->
        <div class="absolute -top-40 -left-40 w-96 h-96 bg-emerald-500/10 rounded-full blur-3xl pointer-events-none"></div>

        <div class="w-full max-w-md relative z-10 space-y-6">
            <!-- Brand Header -->
            <div class="text-center space-y-2">
                <div class="inline-flex items-center justify-center w-14 h-14 rounded-2xl bg-gradient-to-tr from-emerald-600 to-teal-500 shadow-xl shadow-emerald-500/20 text-white mb-2">
                    <KeyRound class="w-7 h-7" />
                </div>
                <h1 class="text-2xl font-black tracking-tight text-white">
                    Atur Ulang <span class="text-emerald-400">Kata Sandi</span>
                </h1>
                <p class="text-xs text-slate-400 max-w-sm mx-auto">
                    Masukkan kata sandi baru untuk akun Anda. Minimal 8 karakter.
                </p>
            </div>

            <!-- Card -->
            <div class="bg-slate-900/90 backdrop-blur-xl border border-slate-800 rounded-3xl p-6 sm:p-8 shadow-2xl space-y-6">
                <div v-if="Object.keys(form.errors).length > 0" class="p-3.5 rounded-xl bg-rose-950/60 border border-rose-800 text-xs text-rose-300 space-y-1">
                    <div class="flex items-center gap-1.5 font-bold">
                        <AlertCircle class="w-4 h-4 text-rose-400 shrink-0" />
                        <span>Terjadi kesalahan:</span>
                    </div>
                    <ul class="list-disc list-inside space-y-0.5 text-[11px] pl-1">
                        <li v-for="(msg, field) in form.errors" :key="field">{{ msg }}</li>
                    </ul>
                </div>

                <form @submit.prevent="submit" class="space-y-4">
                    <!-- Email (read-only or editable) -->
                    <div class="space-y-1.5">
                        <label class="block text-xs font-semibold text-slate-300">Alamat Email</label>
                        <div class="relative">
                            <Mail class="w-4 h-4 absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-500" />
                            <input
                                v-model="form.email"
                                type="email"
                                required
                                placeholder="nama@herbatech.com"
                                class="w-full pl-10 pr-4 py-2.5 rounded-xl bg-slate-950 border border-slate-700/80 text-white text-xs placeholder:text-slate-500 focus:outline-none focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500 transition"
                            />
                        </div>
                    </div>

                    <!-- New Password -->
                    <div class="space-y-1.5">
                        <label class="block text-xs font-semibold text-slate-300">Kata Sandi Baru</label>
                        <div class="relative">
                            <Lock class="w-4 h-4 absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-500" />
                            <input
                                v-model="form.password"
                                :type="showPassword ? 'text' : 'password'"
                                required
                                autocomplete="new-password"
                                placeholder="Minimal 8 karakter"
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

                    <!-- Password Confirmation -->
                    <div class="space-y-1.5">
                        <label class="block text-xs font-semibold text-slate-300">Konfirmasi Kata Sandi</label>
                        <div class="relative">
                            <Lock class="w-4 h-4 absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-500" />
                            <input
                                v-model="form.password_confirmation"
                                :type="showPassword ? 'text' : 'password'"
                                required
                                autocomplete="new-password"
                                placeholder="Ulangi kata sandi baru"
                                class="w-full pl-10 pr-4 py-2.5 rounded-xl bg-slate-950 border border-slate-700/80 text-white text-xs placeholder:text-slate-500 focus:outline-none focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500 transition"
                            />
                        </div>
                    </div>

                    <!-- Submit Button -->
                    <button
                        type="submit"
                        :disabled="form.processing"
                        class="w-full py-3 px-4 rounded-xl font-bold text-xs bg-emerald-600 hover:bg-emerald-500 text-white shadow-lg shadow-emerald-600/30 flex items-center justify-center gap-2 transition disabled:opacity-60 cursor-pointer"
                    >
                        <CheckCircle2 class="w-4 h-4" />
                        <span>{{ form.processing ? 'Menyimpan...' : 'Simpan Kata Sandi Baru' }}</span>
                    </button>
                </form>

                <div class="pt-2 text-center">
                    <Link
                        href="/login"
                        class="inline-flex items-center gap-2 text-xs font-medium text-slate-400 hover:text-white transition"
                    >
                        <ArrowLeft class="w-3.5 h-3.5" />
                        <span>Batalkan dan Kembali ke Login</span>
                    </Link>
                </div>
            </div>
        </div>
    </div>
</template>
