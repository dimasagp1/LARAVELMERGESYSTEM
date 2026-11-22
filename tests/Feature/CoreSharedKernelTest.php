<?php

namespace Tests\Feature;

use App\Core\Enums\ApprovalAction;
use App\Core\Models\ApprovalWorkflow;
use App\Core\Models\Bom;
use App\Core\Models\Department;
use App\Core\Models\ErpModule;
use App\Core\Models\Plant;
use App\Core\Models\Product;
use App\Core\Models\Uom;
use App\Core\Models\Vendor;
use App\Core\Services\ApprovalEngineService;
use App\Core\Services\ModuleManagerService;
use App\Models\User;
use Database\Seeders\CoreMasterDataSeeder;
use Database\Seeders\DefaultErpModulesSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CoreSharedKernelTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(DefaultErpModulesSeeder::class);
        $this->seed(CoreMasterDataSeeder::class);
    }

    public function test_default_eight_erp_modules_are_seeded(): void
    {
        $this->assertDatabaseCount('erp_modules', 8);

        $expectedSlugs = [
            'finance',
            'general-affair',
            'hris',
            'procurement',
            'production',
            'quality',
            'rnd',
            'supply-chain',
        ];

        foreach ($expectedSlugs as $slug) {
            $this->assertDatabaseHas('erp_modules', [
                'slug' => $slug,
                'is_active' => true,
                'is_core' => true,
            ]);
        }
    }

    public function test_dashboard_returns_successful_inertia_response(): void
    {
        $response = $this->get('/');

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page
            ->component('Dashboard')
            ->has('modules', 8)
            ->has('stats')
        );
    }

    public function test_all_eight_module_index_routes_are_accessible(): void
    {
        $routes = [
            '/finance' => 'Modules/Finance/Index',
            '/general-affair' => 'Modules/GeneralAffair/Index',
            '/hris' => 'Modules/Hris/Index',
            '/procurement' => 'Modules/Procurement/Index',
            '/production' => 'Modules/Production/Index',
            '/quality' => 'Modules/Quality/Index',
            '/rnd' => 'Modules/Rnd/Index',
            '/supply-chain' => 'Modules/SupplyChain/Index',
        ];

        foreach ($routes as $url => $component) {
            $response = $this->get($url);
            $response->assertStatus(200);
            $response->assertInertia(fn ($page) => $page->component($component));
        }
    }

    public function test_user_has_custom_erp_columns_and_roles(): void
    {
        $user = User::where('email', 'admin@herbatech.com')->first();

        $this->assertNotNull($user);
        $this->assertEquals('active', $user->status);
        $this->assertNotNull($user->department_id);
        $this->assertNotNull($user->signature_path);
        $this->assertTrue($user->hasRole('Superadmin'));
    }

    public function test_shared_kernel_master_data_relationships(): void
    {
        $plant = Plant::where('code', 'PLANT-01')->first();
        $this->assertNotNull($plant);
        $this->assertNotEmpty($plant->locations);

        $department = Department::where('code', 'FAT')->first();
        $this->assertNotNull($department);

        $kg = Uom::where('code', 'KG')->first();
        $gram = Uom::where('code', 'GRAM')->first();
        $this->assertNotNull($kg);
        $this->assertNotNull($gram);
        $this->assertEquals($kg->id, $gram->base_uom_id);

        $product = Product::create([
            'sku' => 'FG-EXT-CURCUMA-100',
            'name' => 'Ekstrak Temulawak Curcuma 100ml',
            'type' => 'fg',
            'uom_id' => $kg->id,
            'standard_cost' => 25000,
            'selling_price' => 45000,
        ]);

        $this->assertDatabaseHas('products', ['sku' => 'FG-EXT-CURCUMA-100']);

        $vendor = Vendor::create([
            'code' => 'VND-001',
            'name' => 'PT Mitra Simplisia Herbal',
            'is_avl' => true,
            'avl_rating' => 95.0,
        ]);

        $this->assertDatabaseHas('vendors', ['code' => 'VND-001']);

        $bom = Bom::create([
            'bom_number' => 'BOM-CURCUMA-01',
            'product_id' => $product->id,
            'batch_size' => 100,
            'batch_uom_id' => $kg->id,
            'status' => 'approved',
        ]);

        $bom->items()->create([
            'product_id' => $product->id,
            'quantity' => 50,
            'percentage' => 50.00,
            'uom_id' => $kg->id,
            'stage' => 'mixing',
        ]);

        $this->assertDatabaseHas('boms', ['bom_number' => 'BOM-CURCUMA-01']);
        $this->assertDatabaseHas('bom_items', ['bom_id' => $bom->id]);
    }

    public function test_approval_workflow_and_dual_control_signature_logging(): void
    {
        $financeModule = ErpModule::where('slug', 'finance')->first();
        $user = User::where('email', 'admin@herbatech.com')->first();

        $workflow = ApprovalWorkflow::create([
            'name' => 'FAT High-Value PR Approval',
            'code' => 'WF_FAT_50M',
            'module_id' => $financeModule->id,
            'target_model' => Product::class,
            'min_amount' => 50000000,
            'is_active' => true,
        ]);

        $step = $workflow->steps()->create([
            'step_order' => 1,
            'step_name' => 'FAT Manager Gatekeeper',
            'approver_type' => 'user',
            'approver_user_id' => $user->id,
            'requires_digital_signature' => true,
        ]);

        $kg = Uom::where('code', 'KG')->first();
        $product = Product::create([
            'sku' => 'TEST-PROD-01',
            'name' => 'Test Item',
            'type' => 'rm',
            'uom_id' => $kg->id,
        ]);

        /** @var ApprovalEngineService $approvalService */
        $approvalService = app(ApprovalEngineService::class);

        $log = $approvalService->logAction(
            workflow: $workflow,
            step: $step,
            approvable: $product,
            user: $user,
            action: ApprovalAction::Approved,
            comments: 'Budget checked and validated against Q4 allocation',
            signatureSnapshot: 'SIG_SHA256_HASH_VALIDATED'
        );

        $this->assertDatabaseHas('approval_logs', [
            'id' => $log->id,
            'workflow_id' => $workflow->id,
            'action' => 'approved',
            'signature_snapshot' => 'SIG_SHA256_HASH_VALIDATED',
        ]);
    }

    public function test_user_can_toggle_module_pin(): void
    {
        $user = User::where('email', 'admin@herbatech.com')->first();
        $module = ErpModule::where('slug', 'finance')->first();

        /** @var ModuleManagerService $moduleManager */
        $moduleManager = app(ModuleManagerService::class);

        $isPinned = $moduleManager->togglePin($user, $module->id);
        $this->assertTrue($isPinned);

        $this->assertDatabaseHas('user_module_preferences', [
            'user_id' => $user->id,
            'module_id' => $module->id,
            'is_pinned' => true,
        ]);

        $isPinnedAfter = $moduleManager->togglePin($user, $module->id);
        $this->assertFalse($isPinnedAfter);
    }
}
