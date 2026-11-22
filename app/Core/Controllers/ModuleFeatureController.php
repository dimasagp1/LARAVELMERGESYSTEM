<?php

namespace App\Core\Controllers;

use App\Core\Services\ModuleManagerService;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ModuleFeatureController extends Controller
{
    /**
     * Render the feature workspace for a registered menu route.
     */
    public function render(Request $request, ModuleManagerService $moduleManager): Response
    {
        $path = '/'.trim($request->path(), '/');
        $segments = explode('/', trim($request->path(), '/'));
        $moduleSlug = $segments[0] ?? '';
        if ($moduleSlug === 'produksi') {
            $moduleSlug = 'production';
        }

        $allMenus = $moduleManager->getModuleMenus($moduleSlug);
        $currentMenu = collect($allMenus)->firstWhere('route', $path) ?? [
            'name' => ucwords(str_replace(['-', '/'], [' ', ' '], end($segments))),
            'route' => $path,
            'icon' => 'Layers',
            'category' => 'Operasional',
            'description' => 'Workspace & Manajemen Operasional Fitur',
        ];

        return Inertia::render('Modules/Common/ModuleFeatureWorkspace', [
            'module' => [
                'name' => ucwords(str_replace('-', ' ', $moduleSlug)),
                'slug' => $moduleSlug,
                'menus' => $allMenus,
            ],
            'feature' => $currentMenu,
            'category' => $currentMenu['category'] ?? 'Operasional',
            'breadcrumbs' => [
                ['name' => 'Launcher', 'url' => '/'],
                ['name' => ucwords(str_replace('-', ' ', $moduleSlug)), 'url' => "/{$moduleSlug}"],
                ['name' => $currentMenu['category'] ?? 'Operasional', 'url' => '#'],
                ['name' => $currentMenu['name'], 'url' => $path],
            ],
        ]);
    }
}
