<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';
import {
    Mail,
    ArrowLeft,
    CheckCircle2,
    AlertCircle,
    Building2,
    KeyRound,
    ExternalLink,
} from 'lucide-vue-next';

const props = defineProps({
    status: {
        type: String,
        default: null,
    },
    resetUrl: {
        type: String,
        default: null,
    },
});

const form = useForm({
    email: '',
});

const submit = () => {
    form.post('/forgot-password');
};
</script>

<template>
    <Head title="Lupa Kata Sandi - HerbaTech Unified ERP" />

    <div class="min-h-screen bg-slate-950 flex flex-col justify-center items-center p-4 sm:p-6 lg:p-8 relative overflow-hidden selection:bg-emerald-500 selection:text-white">
        <!-- Background Ambient Glow -->
        <div class="absolute -top-40 -right-40 w-96 h-96 bg-emerald-500/10 rounded-full blur-3xl pointer-events-none"></div>

        <div class="w-full max-w-md relative z-10 space-y-6">
            <!-- Brand Header -->
            <div class="text-center space-y-2">
                <div class="inline-flex items-center justify-center w-14 h-14 rounded-2xl bg-gradient-to-tr from-emerald-600 to-teal-500 shadow-xl shadow-emerald-500/20 text-white mb-2">
                    <KeyRound class="w-7 h-7" />
                </div>
                <h1 class="text-2xl font-black tracking-tight text-white">
                    Pemulihan <span class="text-emerald-400">Kata Sandi</span>
                </h1>
                <p class="text-xs text-slate-400 max-w-sm mx-auto">
                    Masukkan alamat email akun ERP Anda. Sistem akan menghasilkan tautan aman untuk mengatur ulang kata sandi.
                </p>
            </div>

            <!-- Card -->
            <div class="bg-slate-900/90 backdrop-blur-xl border border-slate-800 rounded-3xl p-6 sm:p-8 shadow-2xl space-y-6">
                <!-- Status/Success Message -->
                <div v-if="status" class="p-4 rounded-2xl bg-emerald-950/60 border border-emerald-800 text-xs text-emerald-300 space-y-2">
                    <div class="flex items-center gap-2 font-bold">
                        <CheckCircle2 class="w-4 h-4 text-emerald-400 shrink-0" />
                        <span>{{ status }}</span>
                    </div>
                    <p class="text-slate-400 text-[11px]">
                        Tautan reset kata sandi telah aktif selama 60 menit.
                    </p>
                    <div v-if="resetUrl" class="pt-2">
                        <a
                            :href="resetUrl"
                            class="inline-flex items-center gap-1.5 px-3 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-white font-bold text-xs transition"
                        >
                            <span>Buka Form Reset Password</span>
                            <ExternalLink class="w-3.5 h-3.5" />
                        </a>
                    </div>
                </div>

                <div v-if="form.errors.email" class="p-3.5 rounded-xl bg-rose-950/60 border border-rose-800 text-xs text-rose-300 flex items-center gap-2">
                    <AlertCircle class="w-4 h-4 shrink-0 text-rose-400" />
                    <span>{{ form.errors.email }}</span>
                </div>

                <form @submit.prevent="submit" class="space-y-4">
                    <div class="space-y-1.5">
                        <label class="block text-xs font-semibold text-slate-300">Alamat Email Terdaftar</label>
                        <div class="relative">
                            <Mail class="w-4 h-4 absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-500" />
                            <input
                                v-model="form.email"
                                type="email"
                                required
                                autofocus
                                placeholder="nama@herbatech.com"
                                class="w-full pl-10 pr-4 py-2.5 rounded-xl bg-slate-950 border border-slate-700/80 text-white text-xs placeholder:text-slate-500 focus:outline-none focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500 transition"
                            />
                        </div>
                    </div>

                    <button
                        type="submit"
                        :disabled="form.processing"
                        class="w-full py-3 px-4 rounded-xl font-bold text-xs bg-emerald-600 hover:bg-emerald-500 text-white shadow-lg shadow-emerald-600/30 flex items-center justify-center gap-2 transition disabled:opacity-60 cursor-pointer"
                    >
                        <KeyRound class="w-4 h-4" />
                        <span>{{ form.processing ? 'Membuat Tautan...' : 'Kirim Tautan Reset Kata Sandi' }}</span>
                    </button>
                </form>

                <div class="pt-2 text-center">
                    <Link
                        href="/login"
                        class="inline-flex items-center gap-2 text-xs font-medium text-slate-400 hover:text-white transition"
                    >
                        <ArrowLeft class="w-3.5 h-3.5" />
                        <span>Kembali ke Halaman Masuk</span>
                    </Link>
                </div>
            </div>
        </div>
    </div>
</template>
