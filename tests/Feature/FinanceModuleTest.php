<?php

namespace Tests\Feature;

use App\Core\Models\Department;
use App\Models\User;
use App\Modules\Finance\Models\FinAdjJournal;
use App\Modules\Finance\Models\FinFsli;
use App\Modules\Finance\Models\FinPeriod;
use App\Modules\Finance\Models\FinRatioConfiguration;
use App\Modules\Finance\Services\BudgetGatekeeperService;
use App\Modules\Finance\Services\FinancialRatioService;
use App\Modules\Finance\Services\FinancialStatementService;
use App\Modules\Finance\Services\SourceReconciliationService;
use App\Modules\Finance\Services\TrialBalanceService;
use Database\Seeders\CoreMasterDataSeeder;
use Database\Seeders\DefaultErpModulesSeeder;
use Database\Seeders\FinanceModuleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FinanceModuleTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected FinPeriod $period;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(DefaultErpModulesSeeder::class);
        $this->seed(CoreMasterDataSeeder::class);
        $this->seed(FinanceModuleSeeder::class);

        $this->user = User::where('email', 'admin@herbatech.com')->first();
        $this->period = FinPeriod::where('month', 11)->where('year', 2026)->first();
    }

    public function test_finance_overview_dashboard_renders_with_kpis_and_reconciliation(): void
    {
        $response = $this->actingAs($this->user)->get('/finance');

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page
            ->component('Modules/Finance/Overview')
            ->has('module')
            ->has('kpis')
            ->has('reconciliationSummary')
            ->has('bscPillars')
            ->has('charts')
        );
    }

    public function test_trial_balance_working_sheet_renders_and_is_balanced(): void
    {
        $response = $this->actingAs($this->user)->get('/finance/trial-balance?period_id='.$this->period->id);

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page
            ->component('Modules/Finance/TrialBalance/Index')
            ->has('trialBalance')
            ->where('trialBalance.is_balanced', true)
        );

        $tbService = app(TrialBalanceService::class);
        $tb = $tbService->getTrialBalance($this->period->id);

        $this->assertTrue($tb['is_balanced']);
        $this->assertGreaterThan(0, $tb['total_adjusted_debit']);
        $this->assertEquals($tb['total_adjusted_debit'], $tb['total_adjusted_credit']);
    }

    public function test_trial_balance_drilldown_api_returns_level_2_and_3_data(): void
    {
        $fsli = FinFsli::where('code', 'REV_SALES')->first();
        $this->assertNotNull($fsli);

        $response = $this->actingAs($this->user)->getJson("/finance/trial-balance/drilldown/{$fsli->id}?period_id={$this->period->id}");

        $response->assertStatus(200);
        $data = $response->json();

        $this->assertArrayHasKey('fsli', $data);
        $this->assertArrayHasKey('accounts', $data);
        $this->assertArrayHasKey('entries', $data);
    }

    public function test_four_standard_financial_statements_render_correctly(): void
    {
        $response = $this->actingAs($this->user)->get('/finance/financial-statements?period_id='.$this->period->id);

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page
            ->component('Modules/Finance/FinancialStatement/Index')
            ->has('statements.income_statement')
            ->has('statements.balance_sheet')
            ->has('statements.equity_change')
            ->has('statements.cash_flow')
        );

        $fsService = app(FinancialStatementService::class);
        $fs = $fsService->getFinancialStatements($this->period->id);

        $this->assertGreaterThan(0, $fs['income_statement']['revenue']);
        $this->assertGreaterThan(0, $fs['income_statement']['net_income']);
        $this->assertTrue($fs['balance_sheet']['is_balanced']);
    }

    public function test_financial_statements_pdf_export_downloads_successfully(): void
    {
        $response = $this->actingAs($this->user)->get('/finance/financial-statements/pdf?period_id='.$this->period->id);

        $response->assertStatus(200);
        $this->assertTrue(str_contains($response->headers->get('content-type') ?? '', 'application/pdf'));
    }

    public function test_aje_maker_checker_workflow_draft_to_submit_to_approve(): void
    {
        $fsliRev = FinFsli::where('code', 'REV_SALES')->first();
        $fsliAr = FinFsli::where('code', 'AST_CURR_AR')->first();
        $dept = Department::first();

        // 1. Maker creates AJE
        $response = $this->actingAs($this->user)->post('/finance/aje', [
            'period_id' => $this->period->id,
            'journal_date' => '2026-11-26',
            'category' => 'accrual',
            'description' => 'Test AJE Maker-Checker Flow',
            'as_draft' => true,
            'lines' => [
                [
                    'account_code' => '1201',
                    'account_name' => 'Piutang Usaha',
                    'fsli_id' => $fsliAr->id,
                    'department_id' => $dept->id,
                    'debit' => 5000000,
                    'credit' => 0,
                    'description' => 'Debet Piutang',
                ],
                [
                    'account_code' => '4101',
                    'account_name' => 'Penjualan Produk',
                    'fsli_id' => $fsliRev->id,
                    'department_id' => $dept->id,
                    'debit' => 0,
                    'credit' => 5000000,
                    'description' => 'Kredit Penjualan',
                ],
            ],
        ]);

        $response->assertRedirect();
        $aje = FinAdjJournal::where('description', 'Test AJE Maker-Checker Flow')->first();
        $this->assertNotNull($aje);
        $this->assertEquals('draft', $aje->status);
        $this->assertTrue($aje->isBalanced());

        // 2. Submit to Checker
        $submitRes = $this->actingAs($this->user)->post("/finance/aje/{$aje->id}/submit");
        $submitRes->assertSessionHas('success');
        $aje->refresh();
        $this->assertEquals('submitted', $aje->status);

        // 3. Checker Approves
        $approveRes = $this->actingAs($this->user)->post("/finance/aje/{$aje->id}/approve");
        $approveRes->assertSessionHas('success');
        $aje->refresh();
        $this->assertEquals('approved', $aje->status);
        $this->assertNotNull($aje->approved_at);
    }

    public function test_aje_rejection_stores_reason_and_updates_status(): void
    {
        $aje = FinAdjJournal::where('status', 'submitted')->first()
            ?? FinAdjJournal::create([
                'journal_number' => 'AJE-TEST-REJECT',
                'fiscal_year_id' => $this->period->fiscal_year_id,
                'period_id' => $this->period->id,
                'journal_date' => '2026-11-26',
                'description' => 'AJE for reject test',
                'total_debit' => 1000,
                'total_credit' => 1000,
                'status' => 'submitted',
                'created_by' => $this->user->id,
            ]);

        $response = $this->actingAs($this->user)->post("/finance/aje/{$aje->id}/reject", [
            'reason' => 'Dokumen lampiran bukti fisik belum terlampir.',
        ]);

        $response->assertSessionHas('success');
        $aje->refresh();
        $this->assertEquals('rejected', $aje->status);
        $this->assertEquals('Dokumen lampiran bukti fisik belum terlampir.', $aje->rejection_reason);
    }

    public function test_budget_vs_actual_pnl_page_renders_with_3_views(): void
    {
        $response = $this->actingAs($this->user)->get('/finance/budget-pnl?period_id='.$this->period->id);

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page
            ->component('Modules/Finance/BudgetPnL/Index')
            ->has('budgetData.pnl_summary')
            ->has('budgetData.department_opex')
            ->has('budgetData.run_rate_trend')
        );
    }

    public function test_budget_vs_actual_pdf_export(): void
    {
        $response = $this->actingAs($this->user)->get('/finance/budget-pnl/pdf?period_id='.$this->period->id);

        $response->assertStatus(200);
        $this->assertTrue(str_contains($response->headers->get('content-type') ?? '', 'application/pdf'));
    }

    public function test_19_financial_ratios_calculated_with_composite_score(): void
    {
        $response = $this->actingAs($this->user)->get('/finance/financial-ratios?period_id='.$this->period->id);

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page
            ->component('Modules/Finance/FinancialRatio/Index')
            ->has('bscData.composite_score')
            ->has('bscData.pillars.profitability')
            ->has('bscData.pillars.activity')
            ->has('bscData.pillars.productivity')
            ->has('bscData.pillars.liquidity')
            ->has('bscData.pillars.solvency')
        );

        $ratioService = app(FinancialRatioService::class);
        $bsc = $ratioService->calculateRatios($this->period->id);

        $this->assertGreaterThan(0, $bsc['composite_score']);
        $this->assertNotEmpty($bsc['pillars']['profitability']['ratios']);
        $this->assertNotEmpty($bsc['pillars']['productivity']['ratios']);
    }

    public function test_update_ratio_configuration_target_and_weight(): void
    {
        $config = FinRatioConfiguration::where('ratio_code', 'GPM')->first();
        $this->assertNotNull($config);

        $response = $this->actingAs($this->user)->post("/finance/financial-ratios/config/{$config->id}", [
            'target_value' => 58.0,
            'weight' => 9.0,
            'is_active' => true,
        ]);

        $response->assertSessionHas('success');
        $config->refresh();
        $this->assertEquals(58.0, (float) $config->target_value);
    }

    public function test_automated_9_point_reconciliation_executes_and_passes(): void
    {
        $response = $this->actingAs($this->user)->get('/finance/reconciliation?period_id='.$this->period->id);

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page
            ->component('Modules/Finance/Reconciliation/Index')
            ->has('auditData.rules')
        );

        $recService = app(SourceReconciliationService::class);
        $result = $recService->runReconciliation($this->period->id);

        $this->assertEquals(9, $result['total_rules']);
        $this->assertGreaterThanOrEqual(8, $result['passed_count']);
    }

    public function test_budget_gatekeeper_api_check_and_book(): void
    {
        $dept = Department::first();
        $gatekeeper = app(BudgetGatekeeperService::class);

        // Check budget
        $check = $gatekeeper->checkBudget($dept->id, 5000000, $this->period->id);
        $this->assertArrayHasKey('allow_proceed', $check);

        // Check API endpoint
        $response = $this->postJson('/api/finance/budget-gatekeeper/check', [
            'department_id' => $dept->id,
            'requested_amount' => 1000000,
            'period_id' => $this->period->id,
        ]);

        $response->assertStatus(200);
        $response->assertJsonStructure(['allow_proceed', 'remaining_budget']);

        // Book budget API endpoint
        $bookRes = $this->postJson('/api/finance/budget-gatekeeper/book', [
            'department_id' => $dept->id,
            'amount' => 500000,
            'period_id' => $this->period->id,
        ]);

        $bookRes->assertStatus(200);
        $bookRes->assertJson(['success' => true]);
    }
}
