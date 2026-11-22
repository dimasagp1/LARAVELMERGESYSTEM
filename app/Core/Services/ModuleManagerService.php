<?php

namespace App\Core\Services;

use App\Core\Models\ErpModule;
use App\Core\Models\UserModulePreference;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Cache;

class ModuleManagerService
{
    /**
     * @return Collection<int, ErpModule>
     */
    public function getActiveModules(): Collection
    {
        return Cache::remember('erp_active_modules', 3600, function (): Collection {
            return ErpModule::query()
                ->where('is_active', true)
                ->orderBy('sort_order')
                ->get();
        });
    }

    /**
     * @return Collection<int, ErpModule>
     */
    public function getModulesForUser(?User $user): Collection
    {
        $activeModules = $this->getActiveModules();

        $preferences = $user ? UserModulePreference::query()
            ->where('user_id', $user->id)
            ->get()
            ->keyBy('module_id') : collect();

        return $activeModules->map(function (ErpModule $module) use ($preferences) {
            $pref = $preferences->get($module->id);
            $module->is_pinned = $pref ? (bool) $pref->is_pinned : false;
            $module->custom_order = $pref ? (int) $pref->custom_order : (int) $module->sort_order;
            $module->badge_count = $this->getLiveBadgeCount($module->slug);
            $module->menus = $this->getModuleMenus($module->slug, $module->schema_config_json);

            return $module;
        })->sort(function ($a, $b) {
            if ($a->is_pinned !== $b->is_pinned) {
                return $a->is_pinned ? -1 : 1;
            }

            return $a->custom_order <=> $b->custom_order;
        })->values();
    }

    /**
     * Returns contextual menus for a module.
     *
     * @param  array<string, mixed>|null  $schemaConfig
     * @return list<array<string, mixed>>
     */
    public function getModuleMenus(string $slug, ?array $schemaConfig = null): array
    {
        if (! empty($schemaConfig['menus'])) {
            return $schemaConfig['menus'];
        }

        return match ($slug) {
            'finance' => [
                ['name' => 'Overview', 'route' => '/finance', 'icon' => 'LayoutDashboard'],
                ['name' => 'Chart of Accounts', 'route' => '/finance/coa', 'icon' => 'ListTree'],
                ['name' => 'Department Budgets', 'route' => '/finance/budgets', 'icon' => 'PieChart'],
                ['name' => 'Jurnal Penyesuaian (AJE)', 'route' => '/finance/journals', 'icon' => 'FileSpreadsheet'],
                ['name' => 'Trial Balance & Reports', 'route' => '/finance/reports', 'icon' => 'Scale'],
            ],
            'general-affair' => [
                ['name' => 'Overview', 'route' => '/general-affair', 'icon' => 'LayoutDashboard'],
                ['name' => 'Work Orders (Form A-D)', 'route' => '/general-affair/work-orders', 'icon' => 'Wrench'],
                ['name' => 'Permintaan ATK & RT', 'route' => '/general-affair/atk', 'icon' => 'FileText'],
                ['name' => 'Fixed Assets Multi-Plant', 'route' => '/general-affair/assets', 'icon' => 'Building2'],
                ['name' => 'QR Patrol Security', 'route' => '/general-affair/patrols', 'icon' => 'QrCode'],
            ],
            'hris' => [
                ['name' => 'Overview', 'route' => '/hris', 'icon' => 'LayoutDashboard'],
                ['name' => 'Data Karyawan', 'route' => '/hris/employees', 'icon' => 'Users'],
                ['name' => 'Presensi & Geofence', 'route' => '/hris/attendance', 'icon' => 'Clock'],
                ['name' => 'Shift Roster & Time Bank', 'route' => '/hris/time-bank', 'icon' => 'Calendar'],
                ['name' => 'Payroll PPh 21 TER', 'route' => '/hris/payroll', 'icon' => 'Banknote'],
                ['name' => 'Kaizen Kizuki & Point', 'route' => '/hris/kaizen', 'icon' => 'Award'],
            ],
            'procurement' => [
                ['name' => 'Overview', 'route' => '/procurement', 'icon' => 'LayoutDashboard'],
                ['name' => 'Purchase Requests (PR)', 'route' => '/procurement/requests', 'icon' => 'FileCheck'],
                ['name' => 'Purchase Orders (PO)', 'route' => '/procurement/orders', 'icon' => 'ShoppingCart'],
                ['name' => 'Quotation Matrix Bidding', 'route' => '/procurement/bidding', 'icon' => 'DollarSign'],
                ['name' => 'Approved Vendor List (AVL)', 'route' => '/procurement/vendors', 'icon' => 'Building'],
            ],
            'production' => [
                ['name' => 'Overview', 'route' => '/production', 'icon' => 'LayoutDashboard'],
                ['name' => 'Weekly Schedule (WPS)', 'route' => '/production/schedules', 'icon' => 'Calendar'],
                ['name' => 'Estafet Batch Tracking', 'route' => '/production/batches', 'icon' => 'Timer'],
                ['name' => 'OEE & Downtime Monitoring', 'route' => '/production/oee', 'icon' => 'Gauge'],
                ['name' => 'Manajemen Scrap/Reject', 'route' => '/production/scrap', 'icon' => 'AlertTriangle'],
            ],
            'quality' => [
                ['name' => 'Overview', 'route' => '/quality', 'icon' => 'LayoutDashboard'],
                ['name' => 'Master Dokumen Mutu', 'route' => '/quality/documents', 'icon' => 'FileCheck'],
                ['name' => 'Change Request (CRA/CRB)', 'route' => '/quality/change-requests', 'icon' => 'GitCommit'],
                ['name' => 'Investigasi Deviasi (DR)', 'route' => '/quality/deviations', 'icon' => 'AlertOctagon'],
                ['name' => 'CAPA Actions & Verification', 'route' => '/quality/capa', 'icon' => 'CheckCircle2'],
            ],
            'rnd' => [
                ['name' => 'Overview', 'route' => '/rnd', 'icon' => 'LayoutDashboard'],
                ['name' => 'NPD Projects Lifecycle', 'route' => '/rnd/projects', 'icon' => 'Layers'],
                ['name' => 'Formulasi 100% & Simulasi HPP', 'route' => '/rnd/formulations', 'icon' => 'Calculator'],
                ['name' => 'Uji Sensori & Stabilitas', 'route' => '/rnd/stability', 'icon' => 'FlaskConical'],
                ['name' => 'BPOM Dossier Export', 'route' => '/rnd/dossiers', 'icon' => 'FileCheck2'],
            ],
            'supply-chain' => [
                ['name' => 'Overview', 'route' => '/supply-chain', 'icon' => 'LayoutDashboard'],
                ['name' => 'MRP Net Requirement', 'route' => '/supply-chain/mrp', 'icon' => 'RefreshCw'],
                ['name' => 'Goods Receipt (GR)', 'route' => '/supply-chain/receipts', 'icon' => 'Boxes'],
                ['name' => 'Gudang Karantina QC', 'route' => '/supply-chain/quarantine', 'icon' => 'ClipboardCheck'],
                ['name' => '3-Way Matching AP', 'route' => '/supply-chain/matching', 'icon' => 'CheckCircle'],
                ['name' => 'Multi-Warehouse Stock', 'route' => '/supply-chain/stock', 'icon' => 'Warehouse'],
            ],
            default => [
                ['name' => 'Overview', 'route' => "/modules/{$slug}", 'icon' => 'LayoutDashboard'],
            ],
        };
    }

    /**
     * Return live alerts or pending approval count per module.
     */
    public function getLiveBadgeCount(string $slug): int
    {
        return match ($slug) {
            'procurement' => 5,
            'supply-chain' => 4,
            'hris' => 4,
            'quality' => 3,
            'general-affair' => 3,
            'finance' => 2,
            'production' => 2,
            'rnd' => 1,
            default => 0,
        };
    }

    /**
     * Cross-module pending approval list for the active user.
     *
     * @return list<array<string, mixed>>
     */
    public function getPendingApprovalsForUser(?User $user): array
    {
        return [
            [
                'id' => 101,
                'title' => 'Purchase Request PR-2026-0089',
                'description' => 'Pengadaan Ekstrak Curcuma Xanthorrhiza 500 Kg',
                'module_slug' => 'procurement',
                'module_name' => 'Procurement',
                'module_theme' => 'violet',
                'type' => 'Purchase Request',
                'amount' => 'Rp 67.500.000',
                'requester' => 'Budi Santoso (R&D)',
                'urgency' => 'high',
                'date' => '2 jam lalu',
            ],
            [
                'id' => 102,
                'title' => 'Change Request CRA-2026-012',
                'description' => 'Penyesuaian Suhu HVAC Cleanroom Area Mixing Ekstrak',
                'module_slug' => 'quality',
                'module_name' => 'Quality (QMS)',
                'module_theme' => 'teal',
                'type' => 'Change Request Major',
                'amount' => null,
                'requester' => 'Dr. Rina Wati (QA Spv)',
                'urgency' => 'high',
                'date' => '3 jam lalu',
            ],
            [
                'id' => 103,
                'title' => 'Formulasi R&D V3 (100.00%)',
                'description' => 'Kapsul Ekstrak Meniran & Jahe Merah Skala Komersial',
                'module_slug' => 'rnd',
                'module_name' => 'R&D',
                'module_theme' => 'cyan',
                'type' => 'NPD Formulation',
                'amount' => 'HPP: Rp 16.200/btl',
                'requester' => 'Formulator HerbaTech',
                'urgency' => 'medium',
                'date' => '5 jam lalu',
            ],
            [
                'id' => 104,
                'title' => 'Jurnal Penyesuaian AJE-2026-044',
                'description' => 'Akrual Biaya Pemeliharaan Mesin Granulasi Plant 1',
                'module_slug' => 'finance',
                'module_name' => 'Finance',
                'module_theme' => 'emerald',
                'type' => 'Journal Entry',
                'amount' => 'Rp 14.800.000',
                'requester' => 'Staf Akuntansi FAT',
                'urgency' => 'normal',
                'date' => '1 hari lalu',
            ],
        ];
    }

    /**
     * Search index for Global Command Palette (Ctrl + K).
     *
     * @return list<array<string, mixed>>
     */
    public function getGlobalSearchItems(): array
    {
        $items = [];

        // 1. All Active Modules
        foreach ($this->getActiveModules() as $module) {
            $items[] = [
                'type' => 'module',
                'category' => 'Modul ERP',
                'title' => $module->name,
                'subtitle' => $module->description,
                'url' => $module->route_prefix,
                'icon' => $module->icon,
                'theme' => $module->color_theme,
            ];

            // Sub-menus
            foreach ($this->getModuleMenus($module->slug, $module->schema_config_json) as $menu) {
                $items[] = [
                    'type' => 'menu',
                    'category' => "Menu: {$module->name}",
                    'title' => $menu['name'],
                    'subtitle' => "Buka submenu {$menu['name']} di {$module->name}",
                    'url' => $menu['route'],
                    'icon' => $menu['icon'] ?? 'Folder',
                    'theme' => $module->color_theme,
                ];
            }
        }

        // 2. Documents & Sample Entities (PR, PO, Batch, Deviation, Employee, Product)
        $sampleDocuments = [
            ['title' => 'PR-2026-0089', 'category' => 'Purchase Request', 'subtitle' => 'Pengadaan Ekstrak Curcuma Xanthorrhiza (Menunggu Review)', 'url' => '/procurement', 'icon' => 'FileText', 'theme' => 'violet'],
            ['title' => 'PO-2026-0042', 'category' => 'Purchase Order', 'subtitle' => 'PO Kemasan Botol Kaca Amber CV Packindo (Approved)', 'url' => '/procurement', 'icon' => 'ShoppingCart', 'theme' => 'violet'],
            ['title' => 'BATCH-EXT-2026-088', 'category' => 'Batch Produksi', 'subtitle' => 'Ekstrak Temulawak 500 Kg - Line Mixing Cleanroom', 'url' => '/production', 'icon' => 'Factory', 'theme' => 'rose'],
            ['title' => 'DEV-2026-003', 'category' => 'Deviasi Mutu CPOB', 'subtitle' => 'Deviasi Penurunan Suhu Ruang Kering (Fishbone Investigated)', 'url' => '/quality', 'icon' => 'AlertOctagon', 'theme' => 'teal'],
            ['title' => 'NPD-HERBA-PEGAGAN', 'category' => 'Proyek R&D', 'subtitle' => 'Sirup Ekstrak Pegagan - Formulasi 100% Valid', 'url' => '/rnd', 'icon' => 'FlaskConical', 'theme' => 'cyan'],
            ['title' => 'EMP-0142: Joko Susilo', 'category' => 'Karyawan', 'subtitle' => 'Operator Lini 2 Ekstraksi (Hadir - Presensi Valid)', 'url' => '/hris', 'icon' => 'Users', 'theme' => 'blue'],
            ['title' => 'FG-EXT-CURCUMA-100', 'category' => 'Produk Jadi', 'subtitle' => 'Curcuma Extract 100ml Bottle (Stok: 4.800 Pcs)', 'url' => '/supply-chain', 'icon' => 'Boxes', 'theme' => 'indigo'],
            ['title' => 'WO-2026-0019', 'category' => 'Work Order GA', 'subtitle' => 'Perbaikan Kompresor HVAC Cleanroom Boyolali', 'url' => '/general-affair', 'icon' => 'Wrench', 'theme' => 'amber'],
        ];

        return array_merge($items, $sampleDocuments);
    }

    public function togglePin(User $user, int $moduleId): bool
    {
        $preference = UserModulePreference::firstOrNew([
            'user_id' => $user->id,
            'module_id' => $moduleId,
        ]);

        $preference->is_pinned = ! $preference->is_pinned;
        $preference->save();

        return $preference->is_pinned;
    }

    /**
     * @param  list<int>  $moduleIds
     */
    public function reorderModules(User $user, array $moduleIds): void
    {
        foreach ($moduleIds as $order => $moduleId) {
            UserModulePreference::updateOrCreate(
                [
                    'user_id' => $user->id,
                    'module_id' => (int) $moduleId,
                ],
                [
                    'custom_order' => $order,
                ]
            );
        }
    }

    public function clearCache(): void
    {
        Cache::forget('erp_active_modules');
    }
}
