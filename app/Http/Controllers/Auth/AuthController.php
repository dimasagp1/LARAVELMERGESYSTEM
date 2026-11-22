<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;

class AuthController extends Controller
{
    /**
     * Display the login view.
     */
    public function showLogin(Request $request): Response|RedirectResponse
    {
        if (Auth::check()) {
            return redirect()->intended('/');
        }

        // Demo accounts for fast testing
        $demoUsers = User::with('department')
            ->whereIn('email', [
                'admin@herbatech.com',
                'finance.manager@herbatech.com',
                'budi.hr@herbatech.com',
                'hendra.scm@herbatech.com',
                'farhan.rnd@herbatech.com',
                'siti.qc@herbatech.com',
            ])
            ->get(['id', 'name', 'email', 'department_id'])
            ->map(function ($u) {
                return [
                    'name' => $u->name,
                    'email' => $u->email,
                    'role' => $u->getRoleNames()->first() ?? 'Staff',
                ];
            });

        return Inertia::render('Auth/Login', [
            'status' => session('status'),
            'demoAccounts' => $demoUsers,
        ]);
    }

    /**
     * Handle an incoming authentication request.
     */
    public function login(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'string', 'email'],
            'password' => ['required', 'string'],
        ]);

        $throttleKey = Str::transliterate(Str::lower($request->input('email')).'|'.$request->ip());

        if (RateLimiter::tooManyAttempts($throttleKey, 5)) {
            $seconds = RateLimiter::availableIn($throttleKey);
            throw ValidationException::withMessages([
                'email' => trans('auth.throttle', [
                    'seconds' => $seconds,
                    'minutes' => ceil($seconds / 60),
                ]),
            ]);
        }

        $remember = (bool) $request->boolean('remember');

        if (! Auth::attempt($credentials, $remember)) {
            RateLimiter::hit($throttleKey);

            throw ValidationException::withMessages([
                'email' => 'Email atau kata sandi yang Anda masukkan salah.',
            ]);
        }

        RateLimiter::clear($throttleKey);

        /** @var User $user */
        $user = Auth::user();

        // Check if user is active
        if (isset($user->is_active) && ! $user->is_active) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            throw ValidationException::withMessages([
                'email' => 'Akun Anda dinonaktifkan oleh Administrator sistem.',
            ]);
        }

        $request->session()->regenerate();

        if (function_exists('activity')) {
            activity('auth')
                ->causedBy($user)
                ->log("Pengguna {$user->name} berhasil masuk ke sistem");
        }

        return redirect()->intended('/')->with('success', "Selamat datang kembali, {$user->name}!");
    }

    /**
     * Destroy an authenticated session.
     */
    public function logout(Request $request): RedirectResponse
    {
        $user = Auth::user();

        if ($user && function_exists('activity')) {
            activity('auth')
                ->causedBy($user)
                ->log("Pengguna {$user->name} keluar dari sistem");
        }

        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')->with('success', 'Anda telah berhasil keluar dari sistem HerbaTech ERP.');
    }

    /**
     * Display the forgot password view.
     */
    public function showForgotPassword(): Response
    {
        return Inertia::render('Auth/ForgotPassword', [
            'status' => session('status'),
            'resetUrl' => session('resetUrl'),
        ]);
    }

    /**
     * Handle an incoming password reset link request.
     */
    public function sendResetLinkEmail(Request $request): RedirectResponse
    {
        $request->validate([
            'email' => ['required', 'email', 'exists:users,email'],
        ], [
            'email.exists' => 'Alamat email tidak terdaftar di sistem.',
        ]);

        $email = $request->input('email');
        $token = Str::random(64);

        // Store into password_reset_tokens
        DB::table('password_reset_tokens')->updateOrInsert(
            ['email' => $email],
            [
                'token' => Hash::make($token),
                'created_at' => now(),
            ]
        );

        $resetUrl = route('password.reset', ['token' => $token, 'email' => $email]);

        if (function_exists('activity')) {
            $user = User::where('email', $email)->first();
            activity('auth')
                ->causedBy($user)
                ->log("Permintaan reset kata sandi diajukan untuk {$email}");
        }

        return back()
            ->with('status', 'Tautan untuk reset kata sandi telah berhasil dibuat.')
            ->with('resetUrl', $resetUrl)
            ->with('success', 'Tautan reset kata sandi telah dibuat. Klik tombol di bawah untuk melanjutkan penggantian password.');
    }

    /**
     * Display the password reset view.
     */
    public function showResetPassword(Request $request, string $token): Response
    {
        return Inertia::render('Auth/ResetPassword', [
            'token' => $token,
            'email' => $request->query('email', ''),
        ]);
    }

    /**
     * Handle an incoming new password request.
     */
    public function resetPassword(Request $request): RedirectResponse
    {
        $request->validate([
            'token' => ['required', 'string'],
            'email' => ['required', 'email', 'exists:users,email'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ], [
            'password.min' => 'Kata sandi minimal 8 karakter.',
            'password.confirmed' => 'Konfirmasi kata sandi tidak cocok.',
        ]);

        $record = DB::table('password_reset_tokens')
            ->where('email', $request->input('email'))
            ->first();

        if (! $record) {
            throw ValidationException::withMessages([
                'email' => 'Permintaan reset kata sandi tidak valid atau telah kedaluwarsa.',
            ]);
        }

        // Validate token hash
        if (! Hash::check($request->input('token'), $record->token)) {
            throw ValidationException::withMessages([
                'email' => 'Token reset kata sandi tidak valid.',
            ]);
        }

        // Check if token created within 60 minutes
        if ($record->created_at && now()->diffInMinutes($record->created_at) > 60) {
            DB::table('password_reset_tokens')->where('email', $request->input('email'))->delete();
            throw ValidationException::withMessages([
                'email' => 'Token reset kata sandi telah kedaluwarsa. Silakan minta tautan baru.',
            ]);
        }

        // Update password
        /** @var User $user */
        $user = User::where('email', $request->input('email'))->first();
        $user->forceFill([
            'password' => Hash::make($request->input('password')),
            'remember_token' => Str::random(60),
        ])->save();

        DB::table('password_reset_tokens')->where('email', $request->input('email'))->delete();

        if (function_exists('activity')) {
            activity('auth')
                ->causedBy($user)
                ->log("Kata sandi berhasil diperbarui untuk {$user->email}");
        }

        return redirect()->route('login')->with('success', 'Kata sandi Anda berhasil diperbarui! Silakan masuk dengan kata sandi baru.');
    }
}
