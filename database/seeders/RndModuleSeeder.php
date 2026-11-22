<?php

namespace Database\Seeders;

use App\Core\Models\Product;
use App\Core\Models\Uom;
use App\Core\Models\Vendor;
use App\Models\User;
use App\Modules\Rnd\Models\Formula;
use App\Modules\Rnd\Models\NieApproval;
use App\Modules\Rnd\Models\NpdProposal;
use App\Modules\Rnd\Models\PreformulationStudy;
use App\Modules\Rnd\Models\Prf;
use App\Modules\Rnd\Models\SampleEvaluation;
use App\Modules\Rnd\Models\StabilityTest;
use App\Modules\Rnd\Models\TrialPm;
use App\Modules\Rnd\Models\TrialRm;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RndModuleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Spatie Permissions & Roles
        $permissions = [
            'rnd.view',
            'rnd.create',
            'rnd.formula.create',
            'rnd.formula.submit',
            'rnd.formula.approve_stage_1',
            'rnd.formula.approve_stage_2',
            'rnd.trial_rm.create',
            'rnd.trial_rm.approve',
            'rnd.trial_pm.create',
            'rnd.trial_pm.sign_dept',
            'rnd.trial_pm.approve_final',
            'rnd.nie.manage',
        ];

        foreach ($permissions as $p) {
            Permission::firstOrCreate(['name' => $p]);
        }

        $superadminRole = Role::firstOrCreate(['name' => 'Superadmin']);
        $omRole = Role::firstOrCreate(['name' => 'Operational Manager']);
        $gmRole = Role::firstOrCreate(['name' => 'General Manager']);
        $staffRndRole = Role::firstOrCreate(['name' => 'Staff R&D']);

        $omRole->syncPermissions([
            'rnd.view',
            'rnd.formula.approve_stage_1',
            'rnd.trial_rm.approve',
            'rnd.trial_pm.sign_dept',
            'rnd.trial_pm.approve_final',
        ]);

        $gmRole->syncPermissions([
            'rnd.view',
            'rnd.formula.approve_stage_2',
            'rnd.nie.manage',
        ]);

        $staffRndRole->syncPermissions([
            'rnd.view',
            'rnd.create',
            'rnd.formula.create',
            'rnd.formula.submit',
            'rnd.trial_rm.create',
            'rnd.trial_pm.create',
            'rnd.trial_pm.sign_dept',
        ]);

        // 2. Demo Users
        $omUser = User::firstOrCreate(
            ['email' => 'om@herbatech.com'],
            [
                'name' => 'Dr. Hendra Gunawan, M.Farm',
                'password' => Hash::make('password'),
                'status' => 'active',
            ]
        );
        $omUser->assignRole($omRole);

        $gmUser = User::firstOrCreate(
            ['email' => 'gm@herbatech.com'],
            [
                'name' => 'Ir. Bambang Soetrisno',
                'password' => Hash::make('password'),
                'status' => 'active',
            ]
        );
        $gmUser->assignRole($gmRole);

        $staffUser = User::firstOrCreate(
            ['email' => 'formulator@herbatech.com'],
            [
                'name' => 'Siti Nurhaliza, S.Si',
                'password' => Hash::make('password'),
                'status' => 'active',
            ]
        );
        $staffUser->assignRole($staffRndRole);

        // 3. Products & Vendors seeding
        $uomKg = Uom::where('code', 'KG')->first() ?? Uom::create([
            'code' => 'KG',
            'name' => 'Kilogram',
            'category' => 'weight',
            'conversion_factor' => 1.0,
            'is_active' => true,
        ]);

        $uomBox = Uom::where('code', 'BOX')->first() ?? Uom::create([
            'code' => 'BOX',
            'name' => 'Box',
            'category' => 'quantity',
            'conversion_factor' => 1.0,
            'is_active' => true,
        ]);

        $vendor1 = Vendor::firstOrCreate(
            ['code' => 'VND-EXT-001'],
            [
                'name' => 'PT Sumber Ekstrak Alam Nusantara',
                'category' => 'Raw Material',
                'email' => 'sales@sumberekstrak.co.id',
                'phone' => '+62 21 556677',
                'avl_rating' => 4.8,
                'payment_term_days' => 30,
                'is_active' => true,
            ]
        );

        $curcuma = Product::firstOrCreate(
            ['sku' => 'FG-CUR-001'],
            [
                'name' => 'Kapsul Curcuma Plus Imuno Herbal',
                'type' => 'fg',
                'category' => 'Herbal Ekstrak Standardized',
                'uom_id' => $uomBox->id,
                'standard_cost' => 15200.00,
                'selling_price' => 45000.00,
                'is_active' => true,
            ]
        );

        $pegagan = Product::firstOrCreate(
            ['sku' => 'FG-PGG-002'],
            [
                'name' => 'Sirup Herbal Pegagan Daya Ingat Anak',
                'type' => 'fg',
                'category' => 'Sirup Herbal Fungsional',
                'uom_id' => $uomBox->id,
                'standard_cost' => 12500.00,
                'selling_price' => 38000.00,
                'is_active' => true,
            ]
        );

        $rm1 = Product::firstOrCreate(
            ['sku' => 'RM-EXT-TMU'],
            [
                'name' => 'Ekstrak Temulawak Curcuminoid 25%',
                'type' => 'rm',
                'category' => 'Ekstrak Murni',
                'uom_id' => $uomKg->id,
                'standard_cost' => 110000.00,
                'selling_price' => 0,
                'is_active' => true,
            ]
        );

        $rm2 = Product::firstOrCreate(
            ['sku' => 'RM-EKS-MAL'],
            [
                'name' => 'Maltodekstrin DE 10-12 Farmasi',
                'type' => 'rm',
                'category' => 'Eksipien Pengisi',
                'uom_id' => $uomKg->id,
                'standard_cost' => 35000.00,
                'selling_price' => 0,
                'is_active' => true,
            ]
        );

        // 4. Seed PRF
        $prf1 = Prf::firstOrCreate(
            ['code' => 'PRF-202611-001'],
            [
                'product_id' => $curcuma->id,
                'target_market' => 'Dewasa & Lansia, Penderita Gangguan Hati & Imunitas',
                'product_category' => 'Herbal Ekstrak Standardized',
                'concept' => 'Kapsul ekstrak temulawak terstandarisasi curcuminoid > 25% dengan penambahan piperin untuk bioavailabilitas.',
                'target_launch_date' => '2027-02-28',
                'status' => 'approved',
                'created_by' => $staffUser->id,
            ]
        );

        $prf2 = Prf::firstOrCreate(
            ['code' => 'PRF-202611-002'],
            [
                'product_id' => $pegagan->id,
                'target_market' => 'Anak-anak & Remaja (Konsentrasi & Memori)',
                'product_category' => 'Sirup Herbal Fungsional',
                'concept' => 'Sirup herbal pegagan rasa madu manis alami tanpa perisa sintesis.',
                'target_launch_date' => '2027-05-15',
                'status' => 'submitted',
                'created_by' => $staffUser->id,
            ]
        );

        // 5. Seed NPD Proposal
        $npd1 = NpdProposal::firstOrCreate(
            ['code' => 'NPD-202611-001'],
            [
                'prf_id' => $prf1->id,
                'product_id' => $curcuma->id,
                'target_cogs' => 15200.00,
                'target_selling_price' => 45000.00,
                'development_start' => '2026-11-01',
                'development_end' => '2027-02-15',
                'pic_user_id' => $staffUser->id,
                'status' => 'in_progress',
                'notes' => 'Fokus pada uji stabilitas dipercepat 6 bulan dan optimasi disolusi kapsul.',
            ]
        );

        // 6. Preformulation Study
        PreformulationStudy::firstOrCreate(
            ['code' => 'PFS-202611-001'],
            [
                'npd_proposal_id' => $npd1->id,
                'product_id' => $curcuma->id,
                'study_type' => 'physicochemical',
                'start_date' => '2026-11-05',
                'end_date' => '2026-11-18',
                'pic_user_id' => $staffUser->id,
                'results_notes' => 'Kelarutan curcuminoid meningkat 3.2x dengan kombinasi surfaktan alami lesitin kedelai.',
                'status' => 'approved',
            ]
        );

        // 7. Seed Formulas (Strict 100.00% Precision)
        // Formula 1: Approved & Locked (V1)
        $formula1 = Formula::firstOrCreate(
            ['code' => 'FRM-202611-001'],
            [
                'product_id' => $curcuma->id,
                'version' => 1,
                'formula_type' => 'new_product',
                'stage' => 'plant_trial',
                'target_serving_size_g' => 0.5000,
                'serving_unit' => 'kapsul',
                'servings_per_pack' => 60,
                'total_percentage' => 100.0000,
                'total_cost_per_kg' => 84000.00,
                'total_cost_per_pack' => 2520.00,
                'notes' => 'Formula skala komersial CPOB batch 100.000 kapsul.',
                'status' => 'approved',
                'created_by' => $staffUser->id,
                'approved_by_om' => $omUser->id,
                'approved_by_gm' => $gmUser->id,
                'locked_at' => now(),
            ]
        );

        if ($formula1->items()->count() === 0) {
            $formula1->items()->createMany([
                [
                    'material_product_id' => $rm1->id,
                    'vendor_id' => $vendor1?->id,
                    'percentage' => 65.0000,
                    'weight_per_serving_g' => 0.3250,
                    'price_per_kg' => 110000.00,
                    'price_per_serving' => 35.7500,
                    'notes' => 'Ekstrak Temulawak Curcuminoid 25%',
                ],
                [
                    'material_product_id' => $rm2->id,
                    'vendor_id' => $vendor1?->id,
                    'percentage' => 35.0000,
                    'weight_per_serving_g' => 0.1750,
                    'price_per_kg' => 35000.00,
                    'price_per_serving' => 6.1250,
                    'notes' => 'Maltodekstrin DE 10-12 (Eksipien)',
                ],
            ]);
        }

        // Formula 2: Pending Stage 1 (Waiting for OM)
        $formula2 = Formula::firstOrCreate(
            ['code' => 'FRM-202611-002'],
            [
                'product_id' => $pegagan->id,
                'version' => 1,
                'formula_type' => 'new_product',
                'stage' => 'lab_trial',
                'target_serving_size_g' => 15.0000,
                'serving_unit' => 'sendok',
                'servings_per_pack' => 10,
                'total_percentage' => 100.0000,
                'total_cost_per_kg' => 45000.00,
                'total_cost_per_pack' => 6750.00,
                'notes' => 'Formula sirup pegagan madu untuk evaluasi uji lab.',
                'status' => 'pending_stage_1',
                'created_by' => $staffUser->id,
            ]
        );

        if ($formula2->items()->count() === 0) {
            $formula2->items()->createMany([
                [
                    'material_product_id' => $rm1->id,
                    'vendor_id' => $vendor1?->id,
                    'percentage' => 40.0000,
                    'weight_per_serving_g' => 6.0000,
                    'price_per_kg' => 75000.00,
                    'price_per_serving' => 450.0000,
                    'notes' => 'Ekstrak Pegagan Terstandar',
                ],
                [
                    'material_product_id' => $rm2->id,
                    'vendor_id' => $vendor1?->id,
                    'percentage' => 60.0000,
                    'weight_per_serving_g' => 9.0000,
                    'price_per_kg' => 25000.00,
                    'price_per_serving' => 225.0000,
                    'notes' => 'Sirup Madu Murni (Pelarut & Pemanis Alami)',
                ],
            ]);
        }

        // Formula 3: Pending Stage 2 (Waiting for GM)
        $formula3 = Formula::firstOrCreate(
            ['code' => 'FRM-202611-003'],
            [
                'product_id' => $curcuma->id,
                'version' => 1,
                'formula_type' => 'substitution',
                'stage' => 'sensory_test',
                'target_serving_size_g' => 2.0000,
                'serving_unit' => 'sachet',
                'servings_per_pack' => 30,
                'total_percentage' => 100.0000,
                'total_cost_per_kg' => 52000.00,
                'total_cost_per_pack' => 3120.00,
                'notes' => 'Telah lolos uji OM Tahap 1. Menunggu pengesahan akhir GM.',
                'status' => 'pending_stage_2',
                'created_by' => $staffUser->id,
                'approved_by_om' => $omUser->id,
            ]
        );

        if ($formula3->items()->count() === 0) {
            $formula3->items()->createMany([
                [
                    'material_product_id' => $rm1->id,
                    'vendor_id' => $vendor1?->id,
                    'percentage' => 50.0000,
                    'weight_per_serving_g' => 1.0000,
                    'price_per_kg' => 60000.00,
                    'price_per_serving' => 60.0000,
                    'notes' => 'Bahan Ekstrak Jahe Merah',
                ],
                [
                    'material_product_id' => $rm2->id,
                    'vendor_id' => $vendor1?->id,
                    'percentage' => 50.0000,
                    'weight_per_serving_g' => 1.0000,
                    'price_per_kg' => 44000.00,
                    'price_per_serving' => 44.0000,
                    'notes' => 'Gula Kelapa Kristal Organik',
                ],
            ]);
        }

        // 8. Seed Trial RM
        $trialRm1 = TrialRm::firstOrCreate(
            ['code' => 'TRM-202611-001-A'],
            [
                'formula_id' => $formula1->id,
                'product_id' => $curcuma->id,
                'batch_qty_kg' => 5.0,
                'process_steps' => "1. Dry mixing ekstrak temulawak dan maltodekstrin.\n2. Pengisian kapsul otomatis mesin Zanasi.\n3. Uji bobot rata-rata kapsul dan waktu hancur.",
                'decision' => 'lulus',
                'status' => 'approved',
                'created_by' => $staffUser->id,
                'approved_by_om' => $omUser->id,
                'approved_by_gm' => $gmUser->id,
            ]
        );

        if ($trialRm1->verifications()->count() === 0) {
            $trialRm1->verifications()->createMany([
                ['parameter' => 'Kadar Air Granul', 'target_spec' => '< 5.0%', 'actual_result' => '3.2%', 'is_compliant' => true],
                ['parameter' => 'Waktu Hancur Kapsul', 'target_spec' => '< 15 menit', 'actual_result' => '8 menit 40 detik', 'is_compliant' => true],
                ['parameter' => 'Keseragaman Bobot', 'target_spec' => 'Deviasi < 5%', 'actual_result' => 'Deviasi 1.8%', 'is_compliant' => true],
                ['parameter' => 'Kadar Kurkuminoid', 'target_spec' => '> 25.0 mg/kapsul', 'actual_result' => '27.4 mg/kapsul', 'is_compliant' => true],
            ]);
        }

        // 9. Seed Trial PM with 4-Dept Consensus
        $trialPm1 = TrialPm::firstOrCreate(
            ['code' => 'TPM-202611-001'],
            [
                'product_id' => $curcuma->id,
                'vendor_id' => $vendor1?->id,
                'packaging_specs_json' => [
                    'material' => 'Aluminium Foil Laminated',
                    'thickness_micron' => 65,
                    'dimension_mm' => '80 x 120 mm',
                ],
                'machine_params_json' => [
                    'speed_cpm' => 50,
                    'temp_horizontal_c' => 148,
                    'pressure_bar' => 3.8,
                ],
                'leakage_test_result' => 'Lolos Dye Test 100%',
                'seal_strength_result' => 'Kuat (18N)',
                'barcode_readability' => 'Grade A',
                'status' => 'approved',
                'om_approved_by' => $omUser->id,
                'om_approved_at' => now(),
            ]
        );

        if ($trialPm1->approvals()->count() === 0) {
            $depts = ['R&D', 'QC', 'Production', 'Engineering'];
            foreach ($depts as $d) {
                $trialPm1->approvals()->create([
                    'department_name' => $d,
                    'user_id' => $omUser->id,
                    'status' => 'approved',
                    'notes' => "Verifikasi departemen {$d} disetujui tanpa kendala teknis.",
                    'signed_at' => now(),
                ]);
            }
        }

        // 10. Sample Evaluation (Sensory)
        SampleEvaluation::firstOrCreate(
            ['sample_code' => 'SMPL-202611-01'],
            [
                'npd_proposal_id' => $npd1->id,
                'product_id' => $curcuma->id,
                'session_date' => '2026-11-15',
                'batch_code' => 'BATCH-LAB-01A',
                'panelist_count' => 25,
                'average_taste' => 4.60,
                'average_color' => 4.40,
                'average_aroma' => 4.50,
                'average_texture' => 4.30,
                'average_aftertaste' => 4.40,
                'overall_score' => 4.44,
                'status' => 'completed',
                'conclusion' => 'Panelis menyukai profil rasa dan aroma herbal segar.',
            ]
        );

        // 11. Stability Test
        StabilityTest::firstOrCreate(
            ['batch_number' => 'STB-BATCH-202611'],
            [
                'product_id' => $curcuma->id,
                'test_type' => 'accelerated',
                'condition' => '40°C ± 2°C / 75% RH ± 5% RH',
                'start_date' => '2026-11-01',
                'intervals_json' => [
                    ['month' => 0, 'status' => 'compliant', 'date' => '2026-11-01'],
                    ['month' => 1, 'status' => 'compliant', 'date' => '2026-12-01'],
                    ['month' => 3, 'status' => 'pending', 'date' => '2027-02-01'],
                    ['month' => 6, 'status' => 'pending', 'date' => '2027-05-01'],
                ],
                'status' => 'ongoing',
                'notes' => 'Uji stabilitas dipercepat di Climate Chamber Lab R&D.',
            ]
        );

        // 12. NIE Approval (Badan POM RI)
        NieApproval::firstOrCreate(
            ['nie_number' => 'POM TR243012341'],
            [
                'product_id' => $curcuma->id,
                'registration_type' => 'TR',
                'issue_date' => '2026-10-15',
                'expiry_date' => '2031-10-15',
                'status' => 'active',
                'notes' => 'Nomor Izin Edar resmi diterbitkan Badan POM RI untuk klaim pemeliharaan kesehatan fungsi hati.',
            ]
        );
    }
}
