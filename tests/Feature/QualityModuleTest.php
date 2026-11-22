<?php

namespace Tests\Feature;

use App\Models\User;
use App\Modules\Quality\Models\Capa;
use App\Modules\Quality\Models\CapaAction;
use App\Modules\Quality\Models\ChangeRequest;
use App\Modules\Quality\Models\Deviation;
use App\Modules\Quality\Models\MasterDocument;
use Database\Seeders\CoreMasterDataSeeder;
use Database\Seeders\DefaultErpModulesSeeder;
use Database\Seeders\QualityModuleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class QualityModuleTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(DefaultErpModulesSeeder::class);
        $this->seed(CoreMasterDataSeeder::class);
        $this->seed(QualityModuleSeeder::class);

        $this->user = User::where('email', 'admin@herbatech.com')->first() ?? User::factory()->create();
        $this->actingAs($this->user);
    }

    public function test_quality_overview_dashboard_is_accessible(): void
    {
        $response = $this->get('/quality');

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page
            ->component('Modules/Quality/Overview')
            ->has('module')
            ->has('kpis')
            ->has('deviationsBySeverity')
            ->has('capaStatus')
            ->has('fishboneCounts')
            ->has('pendingApprovals')
        );
    }

    public function test_master_documents_lifecycle_and_revisions(): void
    {
        // 1. Create Document
        $response = $this->post('/quality/documents', [
            'document_number' => 'SOP-QA-099',
            'title' => 'Prosedur Sampling Bahan Baku Herbal Kritis',
            'level' => 2,
            'document_code' => 'SOP',
            'scope' => 'Pengawasan Mutu (QC)',
            'department' => 'QA',
            'tempat_penyimpanan' => 'Lemari QA 02',
            'effective_date' => now()->toDateString(),
            'revision' => '00',
        ]);

        $response->assertRedirect('/quality/documents');

        $doc = MasterDocument::where('document_number', 'SOP-QA-099')->first();
        $this->assertNotNull($doc);
        $this->assertEquals('Prosedur Sampling Bahan Baku Herbal Kritis', $doc->title);
        $this->assertCount(1, $doc->revisions);

        // 2. Update to new Revision
        $responseUpdate = $this->put("/quality/documents/{$doc->id}", [
            'document_number' => 'SOP-QA-099',
            'title' => 'Prosedur Sampling Bahan Baku Herbal Kritis & Simplisia',
            'level' => 2,
            'document_code' => 'SOP',
            'department' => 'QA',
            'revision' => '01',
            'change_notes' => 'Penambahan protokol sampling simplisia temulawak.',
            'status' => 'Active',
        ]);

        $responseUpdate->assertRedirect('/quality/documents');

        $doc->refresh();
        $this->assertEquals('01', $doc->revision);
        $this->assertCount(2, $doc->revisions);
    }

    public function test_change_request_cra_fmea_rpn_calculation_and_approvals(): void
    {
        // 1. Create CRA with FMEA parameters
        $response = $this->post('/quality/change-requests', [
            'type' => 'CRA',
            'sifat_perubahan' => 'Proses Pengolahan',
            'nama_produk' => 'Ekstrak Meniran Tablet',
            'awal_sebelum_perubahan' => 'Metode granulasi basah konvensional.',
            'usulan_perubahan' => 'Peralihan ke fluid bed granulator modern.',
            'severity' => 5,
            'occurrence' => 4,
            'detection' => 3,
            'risk_mitigation' => 'Validasi suhu inlet air 3 bets berturut-turut.',
            'action' => 'submit',
        ]);

        $cr = ChangeRequest::where('nama_produk', 'Ekstrak Meniran Tablet')->first();
        $this->assertNotNull($cr);
        $this->assertStringStartsWith('CRA-', $cr->cr_number);
        $this->assertEquals(60, $cr->rpn); // 5 * 4 * 3 = 60
        $this->assertEquals('OPEN', $cr->status);

        // 2. Department Assessment
        $this->post("/quality/change-requests/{$cr->id}/submit-assessment", [
            'risk_analysis' => 'Tidak ada dampak terhadap disolusi zat aktif.',
            'recommendation' => 'Disetujui untuk uji coba komersial.',
            'decision' => 'APPROVED',
        ])->assertRedirect();

        // 3. Approval Chain (HoQ, OM, GM)
        $this->post("/quality/change-requests/{$cr->id}/approve", [
            'approval_level' => 'head_of_quality',
            'decision' => 'APPROVED',
            'notes' => 'Lanjutkan.',
        ])->assertRedirect();

        $this->post("/quality/change-requests/{$cr->id}/approve", [
            'approval_level' => 'operational_manager',
            'decision' => 'APPROVED',
            'notes' => 'Peralatan siap dipasang.',
        ])->assertRedirect();

        $this->post("/quality/change-requests/{$cr->id}/approve", [
            'approval_level' => 'general_manager',
            'decision' => 'APPROVED',
            'notes' => 'Disetujui penuh.',
        ])->assertRedirect();

        $cr->refresh();
        $this->assertEquals('IN PROGRESS', $cr->status);
    }

    public function test_deviation_registration_and_dual_numbering(): void
    {
        $response = $this->post('/quality/deviations', [
            'topik' => 'Suhu Chiller Ruang Penimbangan Meningkat',
            'department' => 'Produksi',
            'pic' => 'Ahmad Fauzi',
            'nomor_bets' => 'EXT-2026-095',
            'nomor_alat' => 'CHILLER-01',
            'jenis_penyimpangan' => 'Peralatan / Mesin',
            'identifikasi_penyimpangan' => 'Kritis',
            'tanggal_temuan' => now()->toDateString(),
            'is_production_stopped' => true,
            'frekuensi_kejadian' => 'Jarang',
            'immediate_action_details' => 'Bahan dipindahkan ke cold storage darurat.',
            'action' => 'submit',
        ]);

        $deviation = Deviation::where('topik', 'Suhu Chiller Ruang Penimbangan Meningkat')->first();
        $this->assertNotNull($deviation);
        $this->assertStringStartsWith('DR/', $deviation->deviation_number);
        $this->assertStringStartsWith('PK/', $deviation->pk_number);
        $this->assertEquals('Kritis', $deviation->identifikasi_penyimpangan);
        $this->assertTrue($deviation->is_production_stopped);
    }

    public function test_investigation_6m_fishbone_and_five_why_saving(): void
    {
        $deviation = Deviation::first();

        $response = $this->post("/quality/deviations/{$deviation->id}/investigation", [
            'fishbone_6m' => [
                'machine' => ['Kompresor pendingin tripped akibat lonjakan voltase'],
                'man' => ['Operator terlambat merespon alarm audio'],
                'method' => ['SOP kalibrasi alarm belum terupdate'],
                'materials' => [],
                'milieu' => ['Suhu ruangan naik 3 derajat'],
                'measurement' => ['Sensor display terlambat refresh'],
            ],
            'five_why' => [
                'Why 1: Kompresor mati',
                'Why 2: Voltase naik',
                'Why 3: Genset backup switchover lambat',
            ],
            'root_cause' => 'Relay switchover otomatis genset aus dan perlu penggantian kapasitor.',
            'evaluasi_tindakan' => [
                [
                    'tindakan' => 'Penggantian relay modul ATS genset',
                    'severity' => 4,
                    'occurrence' => 1,
                    'detection' => 2,
                    'rpn' => 8,
                    'evaluasi' => 'Pencegahan lonjakan voltase permanen',
                ],
            ],
        ]);

        $response->assertRedirect();

        $deviation->refresh();
        $this->assertEquals('Relay switchover otomatis genset aus dan perlu penggantian kapasitor.', $deviation->root_cause);
        $this->assertCount(1, $deviation->fishbone_6m['machine']);
        $this->assertCount(3, $deviation->five_why);
        $this->assertEquals('IN REVIEW', $deviation->status);
    }

    public function test_qa_approve_deviation_automatically_spawns_capa(): void
    {
        $deviation = Deviation::where('status', '!=', 'APPROVED')->first();
        if (! $deviation) {
            $deviation = Deviation::create([
                'initiator_id' => $this->user->id,
                'deviation_number' => 'DR/999/2026',
                'pk_number' => 'PK/999/2026',
                'topik' => 'Tutup Botol Tidak Kedap',
                'department' => 'Produksi',
                'jenis_penyimpangan' => 'Proses',
                'identifikasi_penyimpangan' => 'Mayor',
                'tanggal_temuan' => now()->toDateString(),
                'status' => 'IN REVIEW',
            ]);
        }

        $response = $this->post("/quality/deviations/{$deviation->id}/decide", [
            'decision' => 'APPROVE',
            'notes' => 'Investigasi memadai, lanjutkan ke pembentukan CAPA.',
        ]);

        $deviation->refresh();
        $this->assertEquals('APPROVED', $deviation->status);

        // Verify CAPA is automatically spawned
        $capa = Capa::where('deviation_id', $deviation->id)->first();
        $this->assertNotNull($capa);
        $this->assertStringStartsWith('CAPA/', $capa->capa_number);
        $this->assertEquals($deviation->deviation_number, $capa->deviation_number_ref);
    }

    public function test_capa_multi_actions_proof_upload_and_closing_verification(): void
    {
        $capa = Capa::where('status', '!=', 'CLOSE')->first();
        $this->assertNotNull($capa);

        // 1. Add Sub-Action
        $this->post("/quality/capa/{$capa->id}/actions", [
            'tindakan' => 'Penggantian sealing ring dan uji kebocoran vakum',
            'jenis_tindakan' => 'korektif',
            'department' => 'Engineering',
            'deadline' => now()->addDays(7)->toDateString(),
        ])->assertRedirect();

        $action = CapaAction::where('capa_id', $capa->id)->latest('id')->first();
        $this->assertNotNull($action);
        $this->assertEquals('PENDING', $action->status);

        // 2. Submit Proof
        $this->post("/quality/capa/actions/{$action->id}/proof", [
            'bukti_file_path' => 'https://drive.google.com/herbatech/bukti-seal.pdf',
            'catatan_pelaksanaan' => 'Sealing ring baru telah terpasang dengan torsi standar.',
        ])->assertRedirect();

        $action->refresh();
        $this->assertEquals('SUBMITTED', $action->status);

        // 3. QA Closes CAPA
        $this->post("/quality/capa/{$capa->id}/verify", [
            'decision' => 'CLOSE',
            'verification_notes' => 'Seluruh tindakan telah diverifikasi efektif tanpa ada temuan kebocoran pada 3 bets komersial.',
        ])->assertRedirect();

        $capa->refresh();
        $action->refresh();
        $this->assertEquals('CLOSE', $capa->status);
        $this->assertEquals('VERIFIED', $action->status);
    }

    public function test_quality_official_pdf_generation(): void
    {
        $cr = ChangeRequest::first();
        $dev = Deviation::first();
        $capa = Capa::first();

        // 1. CR PDF
        $resCr = $this->get("/quality/change-requests/{$cr->id}/pdf");
        $resCr->assertStatus(200);
        $this->assertEquals('application/pdf', $resCr->headers->get('Content-Type'));

        // 2. Deviation PDF
        $resDev = $this->get("/quality/deviations/{$dev->id}/pdf");
        $resDev->assertStatus(200);
        $this->assertEquals('application/pdf', $resDev->headers->get('Content-Type'));

        // 3. CAPA PDF
        $resCapa = $this->get("/quality/capa/{$capa->id}/pdf");
        $resCapa->assertStatus(200);
        $this->assertEquals('application/pdf', $resCapa->headers->get('Content-Type'));
    }

    public function test_quality_api_endpoints(): void
    {
        $resMetrics = $this->getJson('/api/quality/metrics');
        $resMetrics->assertStatus(200);
        $resMetrics->assertJsonPath('success', true);
        $this->assertNotNull($resMetrics->json('data.kpis'));

        $resSuggestions = $this->getJson('/api/quality/fishbone-suggestions');
        $resSuggestions->assertStatus(200);
        $resSuggestions->assertJsonPath('success', true);
        $this->assertNotEmpty($resSuggestions->json('suggestions.machine'));
    }
}
