<?php

namespace Tests\Feature;

use App\Core\Models\Product;
use App\Core\Models\Vendor;
use App\Models\User;
use App\Modules\Rnd\Models\Formula;
use App\Modules\Rnd\Models\TrialPm;
use Database\Seeders\CoreMasterDataSeeder;
use Database\Seeders\DefaultErpModulesSeeder;
use Database\Seeders\RndModuleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RndModuleTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected User $omUser;

    protected User $gmUser;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(DefaultErpModulesSeeder::class);
        $this->seed(CoreMasterDataSeeder::class);
        $this->seed(RndModuleSeeder::class);

        $this->user = User::where('email', 'formulator@herbatech.com')->first();
        $this->omUser = User::where('email', 'om@herbatech.com')->first();
        $this->gmUser = User::where('email', 'gm@herbatech.com')->first();
    }

    public function test_rnd_overview_dashboard_renders_for_user(): void
    {
        $response = $this->actingAs($this->user)->get('/rnd');

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page
            ->component('Modules/Rnd/Overview')
            ->has('kpis')
            ->has('pendingQueue')
            ->has('productCards')
            ->has('charts')
        );
    }

    public function test_formula_creation_validates_strict_100_percent_rule(): void
    {
        $curcuma = Product::where('type', 'fg')->first();
        $rm1 = Product::where('type', 'rm')->first();
        $rm2 = Product::where('type', 'rm')->skip(1)->first();
        $vendor = Vendor::first();

        // 1. Should fail when percentage != 100% (e.g. 60 + 35 = 95%)
        $invalidPayload = [
            'product_id' => $curcuma->id,
            'formula_type' => 'new_product',
            'stage' => 'lab_trial',
            'target_serving_size_g' => 2.0,
            'serving_unit' => 'sachet',
            'servings_per_pack' => 30,
            'status' => 'draft',
            'items' => [
                [
                    'material_product_id' => $rm1->id,
                    'vendor_id' => $vendor->id,
                    'percentage' => 60.0,
                    'price_per_kg' => 100000,
                ],
                [
                    'material_product_id' => $rm2->id,
                    'vendor_id' => $vendor->id,
                    'percentage' => 35.0, // 95% total
                    'price_per_kg' => 20000,
                ],
            ],
        ];

        $resInvalid = $this->actingAs($this->user)->post('/rnd/formulas', $invalidPayload);
        $resInvalid->assertSessionHasErrors(['total_percentage']);

        // 2. Should pass when percentage is exactly 100.00%
        $validPayload = $invalidPayload;
        $validPayload['items'][1]['percentage'] = 40.0; // 60 + 40 = 100%

        $resValid = $this->actingAs($this->user)->post('/rnd/formulas', $validPayload);
        $resValid->assertRedirect('/rnd/formulas');

        $formula = Formula::where('product_id', $curcuma->id)->latest()->first();
        $this->assertNotNull($formula);
        $this->assertEquals(100.0000, (float) $formula->total_percentage);
        $this->assertEquals(2, $formula->items()->count());
        $this->assertGreaterThan(0, (float) $formula->total_cost_per_pack);
    }

    public function test_formula_approval_gate_and_immutability(): void
    {
        $formula = Formula::where('status', 'draft')->first();
        if (! $formula) {
            $formula = Formula::create([
                'code' => 'FRM-TEST-001',
                'product_id' => Product::where('type', 'fg')->first()->id,
                'target_serving_size_g' => 2.0,
                'total_percentage' => 100.0,
                'status' => 'draft',
                'created_by' => $this->user->id,
            ]);
        }

        // 1. Staff Submits for Approval
        $resSubmit = $this->actingAs($this->user)->post("/rnd/formulas/{$formula->id}/submit");
        $resSubmit->assertRedirect();
        $formula->refresh();
        $this->assertEquals('pending_stage_1', $formula->status);

        // 2. Operational Manager Approves Stage 1
        $resOm = $this->actingAs($this->omUser)->post("/rnd/approvals/formula/{$formula->id}/approve");
        $resOm->assertRedirect();
        $formula->refresh();
        $this->assertEquals('pending_stage_2', $formula->status);
        $this->assertEquals($this->omUser->id, $formula->approved_by_om);

        // 3. General Manager Approves Stage 2 (Final Approved & Locked)
        $resGm = $this->actingAs($this->gmUser)->post("/rnd/approvals/formula/{$formula->id}/approve");
        $resGm->assertRedirect();
        $formula->refresh();
        $this->assertEquals('approved', $formula->status);
        $this->assertEquals($this->gmUser->id, $formula->approved_by_gm);
        $this->assertNotNull($formula->locked_at);
        $this->assertTrue($formula->isLocked());
    }

    public function test_trial_rm_reformulasi_triggers_auto_clone_versioning(): void
    {
        $parentFormula = Formula::with('items')->where('status', 'approved')->first();

        // Trigger Auto-Clone Reformulation
        $resClone = $this->actingAs($this->user)->post("/rnd/formulas/{$parentFormula->id}/reformula");
        $resClone->assertRedirect();

        $newFormula = Formula::where('parent_formula_id', $parentFormula->id)->latest()->first();
        $this->assertNotNull($newFormula);
        $this->assertEquals($parentFormula->version + 1, $newFormula->version);
        $this->assertEquals('draft', $newFormula->status);
        $this->assertEquals($parentFormula->items->count(), $newFormula->items->count());
        $this->assertStringContainsString("-V{$newFormula->version}", $newFormula->code);
    }

    public function test_trial_pm_requires_four_departments_consensus_before_om_final_approval(): void
    {
        $curcuma = Product::where('type', 'fg')->first();
        $vendor = Vendor::first();

        $payload = [
            'product_id' => $curcuma->id,
            'vendor_id' => $vendor->id,
            'leakage_test_result' => 'Lolos 100%',
            'seal_strength_result' => 'Kuat (15N)',
            'barcode_readability' => 'Grade A',
        ];

        // 1. Create Trial PM
        $resCreate = $this->actingAs($this->user)->post('/rnd/trial-pms', $payload);
        $resCreate->assertRedirect();

        $trialPm = TrialPm::latest('id')->first();
        $this->assertNotNull($trialPm);
        $this->assertEquals(4, $trialPm->approvals()->count());
        $this->assertFalse($trialPm->isFullyDepartmentApproved());

        // 2. OM Final approval should fail before all 4 depts sign
        $resEarlyApprove = $this->actingAs($this->omUser)->post("/rnd/trial-pms/{$trialPm->id}/approve-final");
        $resEarlyApprove->assertSessionHasErrors(['approvals']);

        // 3. Sign all 4 departments (R&D, QC, Production, Engineering)
        $departments = ['R&D', 'QC', 'Production', 'Engineering'];
        foreach ($departments as $dept) {
            $this->actingAs($this->user)->post("/rnd/trial-pms/{$trialPm->id}/sign", [
                'department_name' => $dept,
                'status' => 'approved',
                'notes' => 'OK',
            ]);
        }

        $trialPm->refresh();
        $this->assertTrue($trialPm->isFullyDepartmentApproved());
        $this->assertEquals('pending_approval', $trialPm->status);

        // 4. Now OM final approval should succeed!
        $resFinal = $this->actingAs($this->omUser)->post("/rnd/trial-pms/{$trialPm->id}/approve-final");
        $resFinal->assertRedirect();

        $trialPm->refresh();
        $this->assertEquals('approved', $trialPm->status);
        $this->assertEquals($this->omUser->id, $trialPm->om_approved_by);
    }

    public function test_formula_pdf_export_returns_valid_pdf(): void
    {
        $formula = Formula::first();

        $resPdf = $this->actingAs($this->user)->get("/rnd/formulas/{$formula->id}/export/pdf");

        $resPdf->assertStatus(200);
        $this->assertEquals('application/pdf', $resPdf->headers->get('content-type'));
    }
}
