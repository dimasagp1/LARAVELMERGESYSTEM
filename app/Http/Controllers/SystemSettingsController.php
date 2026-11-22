<?php

namespace App\Http\Controllers;

use App\Core\Services\SystemSettingService;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Laravel\Sanctum\PersonalAccessToken;

class SystemSettingsController extends Controller
{
    public function __construct(
        protected SystemSettingService $settingService
    ) {}

    /**
     * Display the System Settings & Engine Configuration Dashboard.
     */
    public function index(Request $request): Response
    {
        $grouped = $this->settingService->getGrouped();
        $user = $request->user() ?? User::where('email', 'admin@herbatech.com')->first();

        // Get personal access tokens
        $tokens = $user ? $user->tokens()->orderBy('created_at', 'desc')->get()->map(function (PersonalAccessToken $token) {
            return [
                'id' => $token->id,
                'name' => $token->name,
                'abilities' => $token->abilities,
                'last_used_at' => $token->last_used_at?->diffForHumans() ?? 'Belum pernah digunakan',
                'created_at' => $token->created_at?->translatedFormat('d M Y, H:i') ?? '-',
                'expires_at' => $token->expires_at?->translatedFormat('d M Y') ?? 'Tidak kedaluwarsa',
            ];
        }) : [];

        // Numbering sample preview
        $numberingSchemes = $grouped['numbering']['numbering_schemes'] ?? $this->settingService->getDefaultNumberingSchemes();
        $numberingPreviews = [];
        foreach ($numberingSchemes as $key => $scheme) {
            $numberingPreviews[$key] = $this->settingService->generateDocumentNumber($key, 1);
        }

        return Inertia::render('Settings/Index', [
            'settings' => $grouped,
            'numberingPreviews' => $numberingPreviews,
            'tokens' => $tokens,
            'colorOptions' => [
                ['id' => 'emerald', 'name' => 'Herbal Emerald', 'bg' => 'bg-emerald-600', 'desc' => 'Tema hijau segar industri fitofarmaka'],
                ['id' => 'amber', 'name' => 'Golden Amber', 'bg' => 'bg-amber-600', 'desc' => 'Tema hangat operasional & GA'],
                ['id' => 'blue', 'name' => 'Ocean Blue', 'bg' => 'bg-blue-600', 'desc' => 'Tema korporat HRIS & manajemen'],
                ['id' => 'violet', 'name' => 'Amethyst Violet', 'bg' => 'bg-violet-600', 'desc' => 'Tema modern procurement & purchasing'],
                ['id' => 'rose', 'name' => 'Crimson Rose', 'bg' => 'bg-rose-600', 'desc' => 'Tema dinamis manufaktur & shop floor'],
                ['id' => 'teal', 'name' => 'Deep Teal', 'bg' => 'bg-teal-600', 'desc' => 'Tema higienis laboratorium QA & QMS'],
                ['id' => 'cyan', 'name' => 'Cyber Cyan', 'bg' => 'bg-cyan-600', 'desc' => 'Tema presisi riset & formulasi R&D'],
                ['id' => 'indigo', 'name' => 'Royal Indigo', 'bg' => 'bg-indigo-600', 'desc' => 'Tema enterprise terstruktur'],
                ['id' => 'slate', 'name' => 'Midnight Slate', 'bg' => 'bg-slate-700', 'desc' => 'Tema monokrom minimalis modern'],
            ],
            'radiusOptions' => [
                ['id' => 'rounded-none', 'name' => 'Tajam (Sharp / None)', 'class' => 'rounded-none'],
                ['id' => 'rounded-md', 'name' => 'Ringan (Medium - 6px)', 'class' => 'rounded-md'],
                ['id' => 'rounded-lg', 'name' => 'Standar (Large - 8px)', 'class' => 'rounded-lg'],
                ['id' => 'rounded-xl', 'name' => 'Halus (Extra Large - 12px)', 'class' => 'rounded-xl'],
                ['id' => 'rounded-2xl', 'name' => 'Modern Lembut (2XL - 16px)', 'class' => 'rounded-2xl'],
            ],
            'iconOptions' => [
                'Sparkles', 'FlaskConical', 'Leaf', 'Boxes', 'ShieldCheck', 'Award', 'Building2', 'Factory', 'Activity', 'Layers',
            ],
        ]);
    }

    /**
     * Update General & Branding Settings.
     */
    public function updateGeneral(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'app_name' => 'required|string|max:100',
            'app_subtitle' => 'nullable|string|max:200',
            'company_name' => 'required|string|max:150',
            'company_tagline' => 'nullable|string|max:250',
            'company_address' => 'nullable|string|max:500',
            'company_phone' => 'nullable|string|max:50',
            'company_email' => 'nullable|email|max:100',
            'company_npwp' => 'nullable|string|max:50',
            'company_bpom_permit' => 'nullable|string|max:100',
            'app_logo_type' => 'required|in:icon,image',
            'app_logo_icon' => 'nullable|string|max:50',
        ]);

        $this->settingService->updateBulk($validated, 'general');

        return back()->with('success', 'Pengaturan umum & identitas perusahaan berhasil diperbarui.');
    }

    /**
     * Update Theme Engine Settings.
     */
    public function updateTheme(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'theme_color' => 'required|string|in:emerald,amber,blue,violet,rose,teal,cyan,indigo,slate',
            'theme_mode' => 'required|string|in:dark,light,system',
            'theme_radius' => 'required|string|in:rounded-none,rounded-md,rounded-lg,rounded-xl,rounded-2xl',
            'theme_density' => 'required|string|in:compact,comfortable',
            'theme_navbar_style' => 'required|string|in:glass,solid,minimal',
        ]);

        $this->settingService->updateBulk($validated, 'theme');

        return back()->with('success', 'Kustomisasi tema sistem berhasil disimpan.');
    }

    /**
     * Update Document Numbering Schemes.
     */
    public function updateNumbering(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'schemes' => 'required|array',
            'schemes.*.prefix' => 'required|string|max:20',
            'schemes.*.pattern' => 'required|string|max:100',
            'schemes.*.reset_period' => 'required|string|in:monthly,yearly,never',
        ]);

        $currentSchemes = $this->settingService->get('numbering_schemes', $this->settingService->getDefaultNumberingSchemes());

        foreach ($validated['schemes'] as $code => $cfg) {
            if (isset($currentSchemes[$code])) {
                $currentSchemes[$code]['prefix'] = strtoupper($cfg['prefix']);
                $currentSchemes[$code]['pattern'] = $cfg['pattern'];
                $currentSchemes[$code]['reset_period'] = $cfg['reset_period'];
            }
        }

        $this->settingService->set('numbering_schemes', $currentSchemes, 'numbering', 'json', false);

        return back()->with('success', 'Format penomoran dokumen transaksi berhasil diperbarui.');
    }

    /**
     * Generate Live Numbering Preview.
     */
    public function previewNumbering(Request $request)
    {
        $validated = $request->validate([
            'doc_type' => 'required|string',
            'prefix' => 'required|string',
            'pattern' => 'required|string',
        ]);

        $formatted = str_replace(
            ['{PREFIX}', '{YEAR}', '{YEAR2}', '{MONTH}', '{ROMAN_MONTH}'],
            [strtoupper($validated['prefix']), date('Y'), date('y'), date('m'), 'XI'],
            $validated['pattern']
        );

        $formatted = preg_replace_callback('/\{NUMBER(?::(\d+))?\}/', function ($matches) {
            $digits = isset($matches[1]) ? (int) $matches[1] : 4;

            return str_pad('1', $digits, '0', STR_PAD_LEFT);
        }, $formatted);

        return response()->json([
            'preview' => $formatted,
        ]);
    }

    /**
     * Create Sanctum API Token.
     */
    public function createApiToken(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'token_name' => 'required|string|max:100',
            'abilities' => 'nullable|array',
            'expires_days' => 'nullable|integer|min:1|max:365',
        ]);

        $user = $request->user() ?? User::where('email', 'admin@herbatech.com')->first();
        if (! $user) {
            return back()->with('error', 'User tidak ditemukan.');
        }

        $abilities = $validated['abilities'] ?? ['read:all', 'write:all'];
        $expiresAt = ! empty($validated['expires_days']) ? now()->addDays($validated['expires_days']) : null;

        $token = $user->createToken($validated['token_name'], $abilities, $expiresAt);

        return back()
            ->with('success', "API Token [{$validated['token_name']}] berhasil diterbitkan.")
            ->with('plainTextToken', $token->plainTextToken);
    }

    /**
     * Revoke Sanctum API Token.
     */
    public function revokeApiToken(int $tokenId, Request $request): RedirectResponse
    {
        $user = $request->user() ?? User::where('email', 'admin@herbatech.com')->first();
        if ($user) {
            $user->tokens()->where('id', $tokenId)->delete();
        }

        return back()->with('success', 'API Token berhasil dicabut / dinonaktifkan.');
    }
}
