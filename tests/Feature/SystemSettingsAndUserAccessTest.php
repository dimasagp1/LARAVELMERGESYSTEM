<?php

namespace Tests\Feature;

use App\Core\Services\ModuleManagerService;
use App\Core\Services\SystemSettingService;
use App\Core\Services\UserPermissionService;
use App\Models\User;
use Database\Seeders\CoreMasterDataSeeder;
use Database\Seeders\DefaultErpModulesSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class SystemSettingsAndUserAccessTest extends TestCase
{
    use RefreshDatabase;

    protected User $superAdmin;

    protected User $staffUser;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(DefaultErpModulesSeeder::class);
        $this->seed(CoreMasterDataSeeder::class);

        // Setup Roles
        $superRole = Role::firstOrCreate(['name' => 'superadmin']);
        $staffRole = Role::firstOrCreate(['name' => 'staff']);

        // Super Administrator
        $this->superAdmin = User::firstOrCreate(
            ['email' => 'admin@herbatech.com'],
            [
                'name' => 'Super Administrator',
                'password' => bcrypt('password'),
                'status' => 'active',
            ]
        );
        $this->superAdmin->assignRole($superRole);

        // Regular Staff User
        $this->staffUser = User::firstOrCreate(
            ['email' => 'budi.santoso@herbatech.com'],
            [
                'name' => 'Budi Santoso',
                'password' => bcrypt('password'),
                'status' => 'active',
            ]
        );
        $this->staffUser->assignRole($staffRole);
    }

    public function test_settings_index_page_loads_with_default_configurations(): void
    {
        $response = $this->actingAs($this->superAdmin)->get('/settings');

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page
            ->component('Settings/Index')
            ->has('settings')
            ->has('numberingPreviews')
            ->has('colorOptions')
            ->has('radiusOptions')
        );
    }

    public function test_update_general_and_branding_settings(): void
    {
        $payload = [
            'app_name' => 'HerbaPharma ERP Enterprise',
            'app_subtitle' => 'Integrated Smart Pharma 4.0 Platform',
            'company_name' => 'PT Herba Medika Farma Internasional',
            'company_tagline' => 'Scientific Phytomedicine for Modern Life',
            'company_address' => 'Kawasan Industri Solo Blok A1',
            'company_phone' => '+62 271 999888',
            'company_email' => 'corporate@herbapharma.id',
            'company_npwp' => '01.999.888.7-528.000',
            'company_bpom_permit' => 'CPOTB-2026/PROD/9999-HMI',
            'app_logo_type' => 'icon',
            'app_logo_icon' => 'FlaskConical',
        ];

        $response = $this->actingAs($this->superAdmin)->post('/settings/general', $payload);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('system_settings', [
            'group' => 'general',
            'key' => 'app_name',
            'value' => 'HerbaPharma ERP Enterprise',
        ]);

        $this->assertDatabaseHas('system_settings', [
            'group' => 'general',
            'key' => 'app_logo_icon',
            'value' => 'FlaskConical',
        ]);

        /** @var SystemSettingService $service */
        $service = app(SystemSettingService::class);
        $this->assertEquals('HerbaPharma ERP Enterprise', $service->get('app_name'));
        $this->assertEquals('FlaskConical', $service->get('app_logo_icon'));
    }

    public function test_update_theme_engine_settings(): void
    {
        $payload = [
            'theme_color' => 'teal',
            'theme_mode' => 'dark',
            'theme_radius' => 'rounded-xl',
            'theme_density' => 'compact',
            'theme_navbar_style' => 'glass',
        ];

        $response = $this->actingAs($this->superAdmin)->post('/settings/theme', $payload);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('system_settings', [
            'group' => 'theme',
            'key' => 'theme_color',
            'value' => 'teal',
        ]);
    }

    public function test_update_document_numbering_and_preview(): void
    {
        // 1. Update Numbering
        $payload = [
            'schemes' => [
                'purchase_request' => [
                    'prefix' => 'PR-HMI',
                    'pattern' => '{PREFIX}/{YEAR}/{NUMBER:4}',
                    'reset_period' => 'monthly',
                ],
                'weekly_plan' => [
                    'prefix' => 'WPS-HMI',
                    'pattern' => '{PREFIX}/{YEAR}/{MONTH}/{NUMBER:4}',
                    'reset_period' => 'yearly',
                ],
            ],
        ];

        $response = $this->actingAs($this->superAdmin)->post('/settings/numbering', $payload);
        $response->assertRedirect();
        $response->assertSessionHas('success');

        // 2. Test Preview
        $previewResponse = $this->actingAs($this->superAdmin)->postJson('/settings/numbering/preview', [
            'doc_type' => 'purchase_request',
            'prefix' => 'PR-EXP',
            'pattern' => '{PREFIX}/{YEAR}/{NUMBER:4}',
        ]);

        $previewResponse->assertStatus(200);
        $previewResponse->assertJsonStructure(['preview']);
        $preview = $previewResponse->json('preview');
        $this->assertStringStartsWith('PR-EXP/'.date('Y').'/', $preview);
    }

    public function test_api_token_creation_and_revocation(): void
    {
        // 1. Create API Token
        $response = $this->actingAs($this->superAdmin)->post('/settings/api-tokens', [
            'token_name' => 'WMS-Integration-Sync',
            'abilities' => ['read', 'write'],
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('plainTextToken');
        $this->assertDatabaseHas('personal_access_tokens', [
            'tokenable_id' => $this->superAdmin->id,
            'name' => 'WMS-Integration-Sync',
        ]);

        $tokenRecord = $this->superAdmin->tokens()->where('name', 'WMS-Integration-Sync')->first();
        $this->assertNotNull($tokenRecord);

        // 2. Revoke API Token
        $revokeResponse = $this->actingAs($this->superAdmin)->delete("/settings/api-tokens/{$tokenRecord->id}");
        $revokeResponse->assertRedirect();
        $revokeResponse->assertSessionHas('success');

        $this->assertDatabaseMissing('personal_access_tokens', [
            'id' => $tokenRecord->id,
        ]);
    }

    public function test_user_management_crud_and_status_toggle(): void
    {
        // 1. List Users
        $response = $this->actingAs($this->superAdmin)->get('/settings/users');
        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page
            ->component('Settings/Users/Index')
            ->has('users.data')
            ->has('stats')
        );

        // 2. Store new User
        $createResponse = $this->actingAs($this->superAdmin)->post('/settings/users', [
            'name' => 'Siti Rahmawati',
            'email' => 'siti.rahmawati@herbatech.com',
            'password' => 'SecurePass2026!',
            'role' => 'staff',
            'status' => 'active',
        ]);

        $createResponse->assertRedirect();
        $this->assertDatabaseHas('users', [
            'email' => 'siti.rahmawati@herbatech.com',
            'status' => 'active',
        ]);

        $newUser = User::where('email', 'siti.rahmawati@herbatech.com')->first();

        // 3. Update User
        $updateResponse = $this->actingAs($this->superAdmin)->put("/settings/users/{$newUser->id}", [
            'name' => 'Siti Rahmawati S.Farm',
            'email' => 'siti.rahmawati@herbatech.com',
            'status' => 'active',
        ]);

        $updateResponse->assertRedirect();
        $this->assertDatabaseHas('users', [
            'id' => $newUser->id,
            'name' => 'Siti Rahmawati S.Farm',
        ]);

        // 4. Toggle Status
        $toggleResponse = $this->actingAs($this->superAdmin)->post("/settings/users/{$newUser->id}/toggle-status");
        $toggleResponse->assertRedirect();
        $this->assertDatabaseHas('users', [
            'id' => $newUser->id,
            'status' => 'inactive',
        ]);
    }

    public function test_user_permission_checklist_sync_and_presets(): void
    {
        // 1. Get Permission Matrix
        $matrixResponse = $this->actingAs($this->superAdmin)->getJson("/settings/users/{$this->staffUser->id}/permissions");
        $matrixResponse->assertStatus(200);
        $matrixResponse->assertJsonStructure([
            'user' => ['id', 'name', 'email', 'is_superadmin'],
            'modules',
        ]);

        // 2. Apply Preset 'qc_qa'
        $presetResponse = $this->actingAs($this->superAdmin)->post("/settings/users/{$this->staffUser->id}/preset", [
            'preset' => 'qc_qa',
        ]);

        $presetResponse->assertRedirect();
        $this->staffUser->refresh();

        $this->assertTrue($this->staffUser->hasModuleAccess('quality'));
        $this->assertTrue($this->staffUser->hasMenuAccess('quality', '/quality/documents'));
        $this->assertFalse($this->staffUser->hasModuleAccess('finance'));

        // 3. Custom Checklist Sync
        $customPayload = [
            'modules' => [
                'procurement' => [
                    'is_allowed' => true,
                    'can_create' => true,
                    'can_edit' => true,
                    'can_delete' => false,
                    'can_approve' => false,
                ],
            ],
            'menus' => [
                'procurement' => [
                    '/procurement' => ['is_allowed' => true],
                    '/procurement/purchase-requests' => ['is_allowed' => true],
                ],
            ],
        ];

        $syncResponse = $this->actingAs($this->superAdmin)->post("/settings/users/{$this->staffUser->id}/permissions", $customPayload);

        $syncResponse->assertRedirect();
        $this->staffUser->refresh();

        $this->assertTrue($this->staffUser->hasModuleAccess('procurement'));
        $this->assertTrue($this->staffUser->hasMenuAccess('procurement', '/procurement/purchase-requests'));
        $this->assertFalse($this->staffUser->hasModuleAccess('quality'));
    }

    public function test_restricted_user_only_sees_permitted_modules_and_menus(): void
    {
        /** @var UserPermissionService $permService */
        $permService = app(UserPermissionService::class);

        // Grant only Quality module and 2 menus to staffUser
        $permService->syncUserPermissions($this->staffUser, [
            'modules' => [
                'quality' => [
                    'is_allowed' => true,
                    'can_create' => true,
                    'can_edit' => true,
                ],
            ],
            'menus' => [
                'quality' => [
                    '/quality' => ['is_allowed' => true],
                    '/quality/documents' => ['is_allowed' => true],
                ],
            ],
        ]);

        $this->staffUser->refresh();

        /** @var ModuleManagerService $moduleManager */
        $moduleManager = app(ModuleManagerService::class);
        $userModules = $moduleManager->getModulesForUser($this->staffUser);

        // User should ONLY receive 1 module (Quality)
        $this->assertCount(1, $userModules);
        $qualityModule = $userModules->first();
        $this->assertEquals('quality', $qualityModule->slug);

        // And ONLY 2 menus inside Quality
        $menuRoutes = collect($qualityModule->menus)->pluck('route')->toArray();
        $this->assertContains('/quality', $menuRoutes);
        $this->assertContains('/quality/documents', $menuRoutes);
        $this->assertNotContains('/quality/capa', $menuRoutes);
    }

    public function test_rest_api_v1_endpoints(): void
    {
        Sanctum::actingAs($this->superAdmin, ['*']);

        // 1. Settings API
        $settingsResponse = $this->getJson('/api/v1/system/settings');
        $settingsResponse->assertStatus(200);
        $settingsResponse->assertJsonStructure(['success', 'data' => ['app_name', 'theme_color']]);

        // 2. Modules API
        $modulesResponse = $this->getJson('/api/v1/modules');
        $modulesResponse->assertStatus(200);
        $modulesResponse->assertJsonStructure(['success', 'count', 'data']);

        // 3. Products API
        $productsResponse = $this->getJson('/api/v1/products');
        $productsResponse->assertStatus(200);
        $productsResponse->assertJsonStructure(['success', 'data']);

        // 4. Inbound Webhook API
        $webhookResponse = $this->postJson('/api/v1/webhooks/inbound', [
            'event' => 'stock_movement.synced',
            'payload' => [
                'batch' => 'BATCH-TEST-2026',
                'qty' => 100,
            ],
        ]);
        $webhookResponse->assertStatus(200);
        $webhookResponse->assertJson(['success' => true]);
    }
}
