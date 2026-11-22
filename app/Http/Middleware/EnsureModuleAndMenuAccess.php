<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureModuleAndMenuAccess
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next, ?string $requiredModule = null): Response
    {
        $user = $request->user();

        // If not authenticated or superadmin, bypass
        if (! $user || $user->isSuperAdmin()) {
            return $next($request);
        }

        // Determine module slug
        $moduleSlug = $requiredModule;
        if (! $moduleSlug) {
            $path = trim($request->getPathInfo(), '/');
            $segments = explode('/', $path);
            $moduleSlug = $segments[0] ?? '';
        }

        // Normalize production vs produksi
        if ($moduleSlug === 'production') {
            $moduleSlug = 'produksi';
        }

        $coreModules = [
            'finance',
            'general-affair',
            'hris',
            'procurement',
            'production',
            'produksi',
            'quality',
            'rnd',
            'supply-chain',
            'settings',
            'developer-mode',
        ];

        // Settings and developer mode restricted to superadmin
        if (in_array($moduleSlug, ['settings', 'developer-mode']) && ! $user->isSuperAdmin()) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Hanya Super Administrator yang berhak mengakses pengaturan sistem.'], 403);
            }

            return redirect('/')->with('error', 'Akses ditolak: Menu Pengaturan khusus Super Administrator.');
        }

        if (in_array($moduleSlug, $coreModules)) {
            // Check module level access
            if (! $user->hasModuleAccess($moduleSlug)) {
                if ($request->expectsJson()) {
                    return response()->json(['message' => 'Anda tidak memiliki hak akses untuk modul ini.'], 403);
                }

                return redirect('/')->with('error', "Akses Ditolak: Anda tidak memiliki izin untuk modul [{$moduleSlug}].");
            }

            // Check menu level access
            $currentRoute = '/'.trim($request->getPathInfo(), '/');
            if (! $user->hasMenuAccess($moduleSlug, $currentRoute)) {
                if ($request->expectsJson()) {
                    return response()->json(['message' => 'Anda tidak memiliki hak akses untuk menu ini.'], 403);
                }

                // Redirect to module overview
                $overviewUrl = "/{$moduleSlug}";

                return redirect($overviewUrl)->with('warning', 'Akses Ditolak: Akun Anda tidak memiliki izin untuk membuka halaman menu tersebut.');
            }
        }

        return $next($request);
    }
}
