<?php

namespace Database\Seeders;

use App\Core\Models\ErpModule;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;

class DefaultErpModulesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $modules = [
            [
                'slug' => 'finance',
                'name' => 'Finance & Budgeting',
                'description' => 'Akuntansi keuangan terpusat, penganggaran departemen, rekonsiliasi kas/bank, dan Real-Time Budget Gatekeeper API.',
                'icon' => 'Landmark',
                'color_theme' => 'emerald',
                'route_prefix' => '/finance',
                'is_active' => true,
                'is_core' => true,
                'is_custom' => false,
                'sort_order' => 1,
                'schema_config_json' => [
                    'tables' => ['chart_of_accounts', 'cost_centers', 'budgets', 'journal_entries', 'journal_items', 'bsc_metrics'],
                    'api_endpoints' => ['/api/finance/budget-check', '/api/finance/journals'],
                ],
            ],
            [
                'slug' => 'general-affair',
                'name' => 'General Affair (FPUGA)',
                'description' => 'Pemeliharaan sarana/fasilitas pabrik, pengelolaan ATK/RT, manajemen aset tetap multi-plant, keamanan patroli QR, dan IT Helpdesk.',
                'icon' => 'Building2',
                'color_theme' => 'amber',
                'route_prefix' => '/general-affair',
                'is_active' => true,
                'is_core' => true,
                'is_custom' => false,
                'sort_order' => 2,
                'schema_config_json' => [
                    'tables' => ['ga_work_orders', 'ga_atk_requests', 'ga_atk_items', 'assets', 'asset_depreciations', 'patrol_logs'],
                ],
            ],
            [
                'slug' => 'hris',
                'name' => 'HRIS (HRISNOW)',
                'description' => 'Presensi biometrik & geofencing, shift roster, Time Bank, kalkulasi payroll PPh 21 TER, Kaizen Kizuki, dan sasaran mutu BSC L3/L4.',
                'icon' => 'Users',
                'color_theme' => 'blue',
                'route_prefix' => '/hris',
                'is_active' => true,
                'is_core' => true,
                'is_custom' => false,
                'sort_order' => 3,
                'schema_config_json' => [
                    'tables' => ['employees', 'attendances', 'time_banks', 'payrolls', 'bsc_l3_targets'],
                ],
            ],
            [
                'slug' => 'procurement',
                'name' => 'Procurement',
                'description' => 'Manajemen rantai pengadaan barang/jasa, granular 8-step PR item workflow, vendor bidding matrix, integrasi Odoo PO, dan evaluasi AVL.',
                'icon' => 'ShoppingCart',
                'color_theme' => 'violet',
                'route_prefix' => '/procurement',
                'is_active' => true,
                'is_core' => true,
                'is_custom' => false,
                'sort_order' => 4,
                'schema_config_json' => [
                    'tables' => ['purchase_requests', 'pr_items', 'purchase_orders', 'po_items'],
                ],
            ],
            [
                'slug' => 'production',
                'name' => 'Produksi & Manufaktur',
                'description' => 'Perencanaan WPS, estafet batch tracking (Mixing-Filling-Packing), timer digital, OEE realtime, dan integrasi scrap limbah.',
                'icon' => 'Factory',
                'color_theme' => 'rose',
                'route_prefix' => '/production',
                'is_active' => true,
                'is_core' => true,
                'is_custom' => false,
                'sort_order' => 5,
                'schema_config_json' => [
                    'tables' => ['production_schedules', 'production_batches', 'batch_downtimes', 'batch_outputs'],
                ],
            ],
            [
                'slug' => 'quality',
                'name' => 'Quality Management System (QMS)',
                'description' => 'Tata kelola dokumen CPOB 4-tingkat, Change Request CRA/CRB, investigasi deviasi Fishbone 6M & 5-Why, auto-spawn CAPA, dan digital signature 21 CFR Part 11.',
                'icon' => 'ShieldCheck',
                'color_theme' => 'teal',
                'route_prefix' => '/quality',
                'is_active' => true,
                'is_core' => true,
                'is_custom' => false,
                'sort_order' => 6,
                'schema_config_json' => [
                    'tables' => ['qms_documents', 'change_requests', 'deviation_reports', 'capa_actions'],
                ],
            ],
            [
                'slug' => 'rnd',
                'name' => 'R&D (NPD Management)',
                'description' => 'Siklus hidup produk baru (NPD), formulasi presisi 100%, simulasi HPP, uji sensori, uji stabilitas, dan export Dossier BPOM.',
                'icon' => 'FlaskConical',
                'color_theme' => 'cyan',
                'route_prefix' => '/rnd',
                'is_active' => true,
                'is_core' => true,
                'is_custom' => false,
                'sort_order' => 7,
                'schema_config_json' => [
                    'tables' => ['npd_projects', 'npd_formulations', 'formulation_items', 'npd_stability_tests', 'npd_dossiers'],
                ],
            ],
            [
                'slug' => 'supply-chain',
                'name' => 'Supply Chain (LogiFlow SCM)',
                'description' => 'Perencanaan MRP bahan, penerimaan gudang multi-warehouse, isolasi karantina QC, 3-Way Matching AP, dan pelacakan HPP aktual.',
                'icon' => 'Truck',
                'color_theme' => 'indigo',
                'route_prefix' => '/supply-chain',
                'is_active' => true,
                'is_core' => true,
                'is_custom' => false,
                'sort_order' => 8,
                'schema_config_json' => [
                    'tables' => ['warehouses', 'goods_receipts', 'qc_inspections', 'mrp_runs', 'vendor_invoices'],
                ],
            ],
        ];

        foreach ($modules as $data) {
            ErpModule::updateOrCreate(
                ['slug' => $data['slug']],
                $data
            );

            // Auto-generate standard CRUD permissions per PRD 3.2
            $actions = ['view', 'create', 'edit', 'delete', 'approve'];
            foreach ($actions as $action) {
                Permission::firstOrCreate(['name' => "{$action}_{$data['slug']}"]);
            }
        }
    }
}
