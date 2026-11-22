<?php

namespace Database\Seeders;

use App\Core\Models\Department;
use App\Core\Models\Location;
use App\Core\Models\Plant;
use App\Core\Models\Uom;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class CoreMasterDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Departments
        $departments = [
            ['code' => 'FAT', 'name' => 'Finance, Accounting & Tax', 'description' => 'Departemen Keuangan dan Perpajakan'],
            ['code' => 'GA', 'name' => 'General Affair & Facility', 'description' => 'Fasilitas Umum dan Pemeliharaan Pabrik'],
            ['code' => 'HR', 'name' => 'Human Resources & General Affairs', 'description' => 'Pengembangan SDM dan Personalia'],
            ['code' => 'PROC', 'name' => 'Procurement & Purchasing', 'description' => 'Pengadaan Bahan Baku, Kemas, dan Jasa'],
            ['code' => 'PROD', 'name' => 'Production & Manufacturing', 'description' => 'Eksekusi Produksi dan Pengolahan Ekstrak Herbal'],
            ['code' => 'QA', 'name' => 'Quality Assurance', 'description' => 'Penjaminan Mutu dan Kepatuhan CPOB/CPOTB'],
            ['code' => 'QC', 'name' => 'Quality Control', 'description' => 'Pengujian Laboratorium dan Karantina Bahan'],
            ['code' => 'RND', 'name' => 'Research & Development', 'description' => 'Riset Formulasi dan Registrasi BPOM (NPD)'],
            ['code' => 'SCM', 'name' => 'Supply Chain Management', 'description' => 'Manajemen Gudang dan Distribusi Persediaan'],
        ];

        $deptModels = [];
        foreach ($departments as $dept) {
            $deptModels[$dept['code']] = Department::updateOrCreate(
                ['code' => $dept['code']],
                $dept
            );
        }

        // 2. Roles & Permissions
        $adminRole = Role::firstOrCreate(['name' => 'Superadmin']);
        $architectRole = Role::firstOrCreate(['name' => 'System Architect']);
        $managerRole = Role::firstOrCreate(['name' => 'Department Manager']);
        $staffRole = Role::firstOrCreate(['name' => 'Staff']);

        // Give all permissions to Superadmin & Architect
        $allPermissions = Permission::all();
        $adminRole->syncPermissions($allPermissions);
        $architectRole->syncPermissions($allPermissions);

        // 3. Superadmin User
        $superadmin = User::updateOrCreate(
            ['email' => 'admin@herbatech.com'],
            [
                'name' => 'Super Administrator',
                'password' => Hash::make('password123'),
                'department_id' => $deptModels['FAT']->id ?? null,
                'status' => 'active',
                'signature_path' => 'signatures/superadmin.png',
            ]
        );
        $superadmin->assignRole('Superadmin');

        // 4. Plants & Locations
        $plant1 = Plant::updateOrCreate(
            ['code' => 'PLANT-01'],
            [
                'name' => 'Pabrik Ekstrak & Formulasi Herbal Sukoharjo',
                'address' => 'Kawasan Industri Herbal No. 12',
                'city' => 'Sukoharjo',
                'country' => 'Indonesia',
                'phone' => '+62 271 889900',
                'head_of_plant_id' => $superadmin->id,
                'is_active' => true,
            ]
        );

        $plant2 = Plant::updateOrCreate(
            ['code' => 'PLANT-02'],
            [
                'name' => 'Pabrik Solid Dosage & Packaging CPOB Boyolali',
                'address' => 'Jl. Raya Industri Farmasi Km 8',
                'city' => 'Boyolali',
                'country' => 'Indonesia',
                'phone' => '+62 276 334455',
                'head_of_plant_id' => $superadmin->id,
                'is_active' => true,
            ]
        );

        // Locations for Plant 1
        $locations = [
            ['plant_id' => $plant1->id, 'code' => 'WH-RAW', 'name' => 'Gudang Utama Raw Material', 'type' => 'warehouse', 'is_quarantine' => false],
            ['plant_id' => $plant1->id, 'code' => 'WH-QRN', 'name' => 'Gudang Karantina Masuk QC', 'type' => 'quarantine', 'is_quarantine' => true],
            ['plant_id' => $plant1->id, 'code' => 'PROD-EXT', 'name' => 'Lantai Ekstraksi & Evaporasi', 'type' => 'production_floor', 'is_quarantine' => false],
            ['plant_id' => $plant1->id, 'code' => 'PROD-MIX', 'name' => 'Cleanroom Mixing & Formulasi', 'type' => 'cleanroom', 'is_quarantine' => false],
            ['plant_id' => $plant1->id, 'code' => 'LAB-RND', 'name' => 'Laboratorium Formulasi R&D', 'type' => 'lab', 'is_quarantine' => false],
            ['plant_id' => $plant1->id, 'code' => 'WH-FG', 'name' => 'Gudang Produk Jadi (FG)', 'type' => 'warehouse', 'is_quarantine' => false],
        ];

        foreach ($locations as $loc) {
            Location::updateOrCreate(
                ['plant_id' => $loc['plant_id'], 'code' => $loc['code']],
                $loc
            );
        }

        // 5. Units of Measure (UOM)
        $kg = Uom::updateOrCreate(
            ['code' => 'KG'],
            ['name' => 'Kilogram', 'category' => 'weight', 'conversion_factor' => 1.000000, 'is_active' => true]
        );

        Uom::updateOrCreate(
            ['code' => 'GRAM'],
            ['name' => 'Gram', 'category' => 'weight', 'base_uom_id' => $kg->id, 'conversion_factor' => 0.001000, 'is_active' => true]
        );

        Uom::updateOrCreate(
            ['code' => 'MG'],
            ['name' => 'Milligram', 'category' => 'weight', 'base_uom_id' => $kg->id, 'conversion_factor' => 0.000001, 'is_active' => true]
        );

        $liter = Uom::updateOrCreate(
            ['code' => 'LITER'],
            ['name' => 'Liter', 'category' => 'volume', 'conversion_factor' => 1.000000, 'is_active' => true]
        );

        Uom::updateOrCreate(
            ['code' => 'ML'],
            ['name' => 'Milliliter', 'category' => 'volume', 'base_uom_id' => $liter->id, 'conversion_factor' => 0.001000, 'is_active' => true]
        );

        Uom::updateOrCreate(
            ['code' => 'PCS'],
            ['name' => 'Pieces', 'category' => 'quantity', 'conversion_factor' => 1.000000, 'is_active' => true]
        );

        Uom::updateOrCreate(
            ['code' => 'BOX'],
            ['name' => 'Box', 'category' => 'quantity', 'conversion_factor' => 1.000000, 'is_active' => true]
        );

        Uom::updateOrCreate(
            ['code' => 'DRUM'],
            ['name' => 'Drum 200L', 'category' => 'quantity', 'conversion_factor' => 1.000000, 'is_active' => true]
        );
    }
}
