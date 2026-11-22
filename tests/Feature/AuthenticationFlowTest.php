<?php

namespace Tests\Feature;

use App\Core\Models\ErpModule;
use App\Models\User;
use Database\Seeders\CoreMasterDataSeeder;
use Database\Seeders\DefaultErpModulesSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class AuthenticationFlowTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(DefaultErpModulesSeeder::class);
        $this->seed(CoreMasterDataSeeder::class);

        $superadminRole = Role::firstOrCreate(['name' => 'Superadmin']);
        $this->user = User::firstOrCreate(
            ['email' => 'admin@herbatech.com'],
            [
                'name' => 'Super Administrator',
                'password' => Hash::make('password123'),
                'is_active' => true,
            ]
        );
        $this->user->assignRole($superadminRole);
    }

    public function test_login_screen_renders_with_demo_accounts(): void
    {
        $response = $this->get('/login');

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page
            ->component('Auth/Login')
            ->has('demoAccounts')
        );
    }

    public function test_user_can_login_with_valid_credentials(): void
    {
        $response = $this->post('/login', [
            'email' => 'admin@herbatech.com',
            'password' => 'password123',
            'remember' => true,
        ]);

        $response->assertRedirect('/');
        $this->assertAuthenticatedAs($this->user);
    }

    public function test_user_cannot_login_with_invalid_credentials(): void
    {
        $response = $this->post('/login', [
            'email' => 'admin@herbatech.com',
            'password' => 'wrong-password',
        ]);

        $response->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    public function test_authenticated_user_can_logout(): void
    {
        $response = $this->actingAs($this->user)->post('/logout');

        $response->assertRedirect('/login');
        $this->assertGuest();
    }

    public function test_forgot_password_screen_renders(): void
    {
        $response = $this->get('/forgot-password');

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page
            ->component('Auth/ForgotPassword')
        );
    }

    public function test_user_can_request_password_reset_token(): void
    {
        $response = $this->post('/forgot-password', [
            'email' => 'admin@herbatech.com',
        ]);

        $response->assertSessionHas('status');
        $response->assertSessionHas('resetUrl');

        $this->assertDatabaseHas('password_reset_tokens', [
            'email' => 'admin@herbatech.com',
        ]);
    }

    public function test_user_can_reset_password_with_token(): void
    {
        $token = Str::random(64);
        DB::table('password_reset_tokens')->updateOrInsert(
            ['email' => 'admin@herbatech.com'],
            [
                'token' => Hash::make($token),
                'created_at' => now(),
            ]
        );

        $resetPageResponse = $this->get("/reset-password/{$token}?email=admin@herbatech.com");
        $resetPageResponse->assertStatus(200);
        $resetPageResponse->assertInertia(fn ($page) => $page
            ->component('Auth/ResetPassword')
            ->where('token', $token)
            ->where('email', 'admin@herbatech.com')
        );

        $postResponse = $this->post('/reset-password', [
            'token' => $token,
            'email' => 'admin@herbatech.com',
            'password' => 'newpassword123',
            'password_confirmation' => 'newpassword123',
        ]);

        $postResponse->assertRedirect('/login');
        $postResponse->assertSessionHas('success');

        // Verify password was updated
        $this->user->refresh();
        $this->assertTrue(Hash::check('newpassword123', $this->user->password));

        // Verify token deleted
        $this->assertDatabaseMissing('password_reset_tokens', [
            'email' => 'admin@herbatech.com',
        ]);

        // Verify can login with new password
        $loginResponse = $this->post('/login', [
            'email' => 'admin@herbatech.com',
            'password' => 'newpassword123',
        ]);
        $loginResponse->assertRedirect('/');
        $this->assertAuthenticatedAs($this->user);
    }

    public function test_developer_mode_can_fetch_cross_module_sources(): void
    {
        $response = $this->actingAs($this->user)->getJson('/developer-mode/sources/hris_employees');

        $response->assertStatus(200);
        $this->assertIsArray($response->json());
    }

    public function test_developer_mode_can_save_canvas_layout_and_widgets(): void
    {
        $module = ErpModule::first();

        $payload = [
            'canvas_config' => [
                'columns' => 4,
                'theme' => 'dark',
                'card_radius' => 'rounded-3xl',
                'card_padding' => 'p-5',
                'card_elevation' => 'shadow-lg',
            ],
            'widgets' => [
                [
                    'title' => 'Total Karyawan Aktif',
                    'widget_type' => 'kpi',
                    'grid_span' => 1,
                    'data_source_config_json' => [
                        'metric_key' => 'total_employees',
                        'icon' => 'Users',
                        'color' => 'emerald',
                        'unit' => 'Orang',
                    ],
                ],
                [
                    'title' => 'Grafik Tren Produksi',
                    'widget_type' => 'chart',
                    'grid_span' => 3,
                    'data_source_config_json' => [
                        'chart_type' => 'area',
                    ],
                ],
            ],
        ];

        $response = $this->actingAs($this->user)->post("/developer-mode/modules/{$module->id}/canvas", $payload);

        $response->assertSessionHas('success');

        $module->refresh();
        $this->assertEquals(4, $module->schema_config_json['canvas']['columns']);
        $this->assertEquals('rounded-3xl', $module->schema_config_json['canvas']['card_radius']);
        $this->assertDatabaseHas('custom_dashboard_widgets', [
            'module_id' => $module->id,
            'title' => 'Total Karyawan Aktif',
            'widget_type' => 'kpi',
        ]);
    }
}
