<?php

namespace Database\Seeders;

use App\Models\User;
use App\Modules\Quality\Models\Capa;
use App\Modules\Quality\Models\CapaAction;
use App\Modules\Quality\Models\ChangeRequest;
use App\Modules\Quality\Models\Deviation;
use App\Modules\Quality\Models\DocumentFolder;
use App\Modules\Quality\Models\DocumentRevision;
use App\Modules\Quality\Models\MasterDocument;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

class QualityModuleSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::first();
        $qaSpv = User::where('email', 'admin@herbatech.com')->first() ?? $admin;

        // 1. Seed Document Folders
        $fManualMutu = DocumentFolder::updateOrCreate(
            ['name' => 'Level 1 - Manual Mutu'],
            ['color' => '#0f766e', 'icon' => 'BookOpen', 'description' => 'Kebijakan mutu tertinggi dan pedoman sistem mutu perusahaan.']
        );

        $fSop = DocumentFolder::updateOrCreate(
            ['name' => 'Level 2 - Prosedur Tetap (SOP)'],
            ['color' => '#0d9488', 'icon' => 'FileText', 'description' => 'SOP 12 Ruang Lingkup CPOB / ISO 9001.']
        );

        $fIk = DocumentFolder::updateOrCreate(
            ['name' => 'Level 3 - Instruksi Kerja (IK)'],
            ['color' => '#0284c7', 'icon' => 'Layers', 'description' => 'Instruksi teknis operasional per departemen.']
        );

        $fFr = DocumentFolder::updateOrCreate(
            ['name' => 'Level 4 - Formulir & Rekaman Mutu (FR)'],
            ['color' => '#16a34a', 'icon' => 'CheckSquare', 'description' => 'Formulir kendali mutu, checklist, dan rekaman bets CPOB.']
        );

        // 2. Seed Master Documents & Revisions
        $docsData = [
            [
                'folder_id' => $fManualMutu->id,
                'level' => 1,
                'document_code' => 'MM',
                'document_number' => 'MM-QA-001',
                'title' => 'Manual Mutu Industri Ekstrak & Herbal PT HerbaTech Indo Utama',
                'scope' => 'Sistem Manajemen Mutu',
                'department' => 'QA',
                'tempat_penyimpanan' => 'Lemari Arsip QA 01 Rak A',
                'effective_date' => '2026-01-01',
                'revision' => '03',
                'status' => 'Active',
                'document_links' => [
                    ['label' => 'Google Drive Master MM', 'url' => 'https://drive.google.com/herbatech/mm-001'],
                ],
            ],
            [
                'folder_id' => $fSop->id,
                'level' => 2,
                'document_code' => 'SOP',
                'document_number' => 'SOP-QA-004',
                'title' => 'Prosedur Manajemen Usulan Perubahan (Change Request CRA & CRB)',
                'scope' => 'Sistem Manajemen Mutu',
                'department' => 'QA',
                'tempat_penyimpanan' => 'Lemari Arsip QA 01 Rak B',
                'effective_date' => '2026-02-15',
                'revision' => '02',
                'status' => 'Active',
                'document_links' => [
                    ['label' => 'SharePoint SOP-QA-004', 'url' => 'https://sharepoint.herbatech.com/qa/sop-004'],
                ],
            ],
            [
                'folder_id' => $fSop->id,
                'level' => 2,
                'document_code' => 'SOP',
                'document_number' => 'SOP-QA-006',
                'title' => 'Prosedur Penyelidikan Ketidaksesuaian & Siklus Tindakan Korektif (CAPA)',
                'scope' => 'Inspeksi Diri & Audit Mutu',
                'department' => 'QA',
                'tempat_penyimpanan' => 'Lemari Arsip QA 01 Rak B',
                'effective_date' => '2026-03-01',
                'revision' => '02',
                'status' => 'Active',
                'document_links' => [
                    ['label' => 'Cloud SOP CAPA', 'url' => 'https://drive.google.com/herbatech/sop-capa'],
                ],
            ],
            [
                'folder_id' => $fSop->id,
                'level' => 2,
                'document_code' => 'SOP',
                'document_number' => 'SOP-PRD-012',
                'title' => 'Prosedur Pengolahan Ekstraksi Vakum Suhu Terkendali Temulawak & Meniran',
                'scope' => 'Produksi & Pengolahan',
                'department' => 'Produksi',
                'tempat_penyimpanan' => 'Lemari Staging Produksi Plant 1',
                'effective_date' => '2026-04-10',
                'revision' => '01',
                'status' => 'Active',
                'document_links' => [],
            ],
            [
                'folder_id' => $fIk->id,
                'level' => 3,
                'document_code' => 'IK',
                'document_number' => 'IK-QC-008',
                'title' => 'Instruksi Kerja Penetapan Kadar Kurkuminoid Ekstrak Menggunakan HPLC',
                'scope' => 'Pengawasan Mutu (QC)',
                'department' => 'QC',
                'tempat_penyimpanan' => 'Laboratorium Kimia Fisika QC',
                'effective_date' => '2026-05-01',
                'revision' => '01',
                'status' => 'Active',
                'document_links' => [],
            ],
            [
                'folder_id' => $fFr->id,
                'level' => 4,
                'document_code' => 'FR',
                'document_number' => 'FR-QA-015',
                'title' => 'Catatan Pengolahan Bets (Batch Manufacturing Record - BMR) Sirup Herbal 100ml',
                'scope' => 'Dokumentasi & Integritas Data',
                'department' => 'QA',
                'tempat_penyimpanan' => 'Arsip Batch Record QA',
                'effective_date' => '2026-06-01',
                'revision' => '04',
                'status' => 'Active',
                'document_links' => [],
            ],
        ];

        foreach ($docsData as $d) {
            $doc = MasterDocument::updateOrCreate(
                ['document_number' => $d['document_number']],
                $d
            );

            DocumentRevision::firstOrCreate(
                [
                    'master_document_id' => $doc->id,
                    'revision' => $doc->revision,
                ],
                [
                    'created_by_user_id' => $admin?->id,
                    'title' => $doc->title,
                    'effective_date' => $doc->effective_date,
                    'change_notes' => 'Pembaruan dokumen berkala sesuai regulasi CPOB.',
                ]
            );
        }

        // 3. Seed Change Requests (CRA & CRB)
        ChangeRequest::updateOrCreate(
            ['cr_number' => 'CRA-2026-0001'],
            [
                'initiator_id' => $admin?->id,
                'type' => 'CRA',
                'sifat_perubahan' => 'Mesin / Peralatan Produksi',
                'nama_produk' => 'Ekstrak Temulawak Vakum (MSN-EXT-01)',
                'awal_sebelum_perubahan' => 'Agitator ekstraktor vakum menggunakan blade standar 4-daun putaran 45 RPM.',
                'usulan_perubahan' => 'Modifikasi blade pengaduk menjadi helical ribbon 60 RPM untuk meningkatkan efisiensi ekstraksi zat aktif.',
                'alasan_perubahan' => 'Peningkatan rendemen zat aktif kurkuminoid sebesar 12% dan penghematan waktu ekstraksi.',
                'target_pelaksanaan' => Carbon::now()->addDays(20)->toDateString(),
                'severity' => 4,
                'occurrence' => 2,
                'detection' => 3,
                'rpn' => 24,
                'risk_mitigation' => 'Dilakukan uji coba validasi proses 3 bets berurutan dan pengujian stabilitas zat aktif.',
                'qa_verification_data' => [
                    'stage_1' => [
                        'submitted_at' => Carbon::now()->subDays(5)->toDateTimeString(),
                        'assessments' => [
                            [
                                'user_id' => $admin?->id,
                                'department' => 'Produksi',
                                'pic_name' => 'Budi Santoso',
                                'risk_analysis' => 'Tidak ada peningkatan gesekan panas berlebih pada tangki vakum.',
                                'recommendation' => 'Dapat dilanjutkan dengan pemantauan suhu ekstraksi kontinu.',
                                'status' => 'APPROVED',
                                'signed_at' => Carbon::now()->subDays(4)->toDateTimeString(),
                            ],
                        ],
                        'approvals' => [
                            'head_of_quality' => [
                                'name' => 'Dr. Rina Wati (Head of QA)',
                                'status' => 'APPROVED',
                                'notes' => 'Lanjutkan ke uji validasi 3 bets.',
                                'signed_at' => Carbon::now()->subDays(3)->toDateTimeString(),
                            ],
                            'operational_manager' => [
                                'name' => 'Ahmad Fauzi (Operational Manager)',
                                'status' => 'APPROVED',
                                'notes' => 'Disetujui untuk pemasangan pada maintenance window.',
                                'signed_at' => Carbon::now()->subDays(2)->toDateTimeString(),
                            ],
                            'general_manager' => [
                                'name' => 'Ir. Hendro Wijaya (General Manager)',
                                'status' => 'APPROVED',
                                'notes' => 'Persetujuan akhir disahkan.',
                                'signed_at' => Carbon::now()->subDays(1)->toDateTimeString(),
                            ],
                        ],
                    ],
                ],
                'status' => 'IN PROGRESS',
            ]
        );

        ChangeRequest::updateOrCreate(
            ['cr_number' => 'CRA-2026-0002'],
            [
                'initiator_id' => $admin?->id,
                'type' => 'CRA',
                'sifat_perubahan' => 'Fasilitas / Cleanroom',
                'nama_produk' => 'Cleanroom Kelas E Area Mixing Ekstrak',
                'awal_sebelum_perubahan' => 'Suhu ruang diatur pada 24°C ± 2°C dengan RH maksimal 65%.',
                'usulan_perubahan' => 'Penyesuaian target suhu menjadi 21°C ± 1°C dan penambahan dehumidifier untuk menjaga RH < 55%.',
                'alasan_perubahan' => 'Mencegah bahan serbuk ekstrak higroskopis menggumpal saat staging dan penimbangan.',
                'target_pelaksanaan' => Carbon::now()->addDays(14)->toDateString(),
                'severity' => 6,
                'occurrence' => 3,
                'detection' => 4,
                'rpn' => 72,
                'risk_mitigation' => 'Pengujian kualifikasi ulang HVAC (re-kualifikasi laju udara, partikel, dan mikroba).',
                'status' => 'OPEN',
            ]
        );

        ChangeRequest::updateOrCreate(
            ['cr_number' => 'CRB-2026-0001'],
            [
                'initiator_id' => $admin?->id,
                'type' => 'CRB',
                'sifat_perubahan' => 'Bahan Pengemas Primer / Sekunder',
                'nama_produk' => 'Sirup Temulawak Plus 100ml',
                'awal_sebelum_perubahan' => 'Nomor izin edar BPOM dicetak di bagian samping label stiker botol.',
                'usulan_perubahan' => 'Pergeseran posisi nomor izin edar BPOM ke bagian depan bawah label agar lebih mudah terbaca konsumen.',
                'alasan_perubahan' => 'Penyesuaian standar kepatuhan label BPOM 2026.',
                'target_pelaksanaan' => Carbon::now()->subDays(2)->toDateString(),
                'severity' => 1,
                'occurrence' => 1,
                'detection' => 1,
                'rpn' => 1,
                'qa_verification_data' => [
                    'stage_1' => [
                        'submitted_at' => Carbon::now()->subDays(10)->toDateTimeString(),
                        'approvals' => [
                            'head_of_quality' => [
                                'name' => 'Dr. Rina Wati',
                                'status' => 'APPROVED',
                                'signed_at' => Carbon::now()->subDays(8)->toDateTimeString(),
                            ],
                        ],
                    ],
                ],
                'stage_2_data' => [
                    'submitted_by' => 'Staff Pengemasan',
                    'submitted_at' => Carbon::now()->subDays(2)->toDateTimeString(),
                    'implementation_summary' => 'Pencetakan label baru 10.000 lembar telah diterima dan diverifikasi sesuai mock-up.',
                    'verification' => [
                        'verified_by' => 'Head of QA',
                        'verified_at' => Carbon::now()->subDays(1)->toDateTimeString(),
                        'status' => 'VERIFIED',
                    ],
                ],
                'status' => 'COMPLETE',
            ]
        );

        // 4. Seed Deviations (DR / PK)
        $dev1 = Deviation::updateOrCreate(
            ['deviation_number' => 'DR/001/2026'],
            [
                'initiator_id' => $admin?->id,
                'pk_number' => 'PK/001/2026',
                'topik' => 'Fluktuasi Suhu Ekstraksi Vakum Bets #EXT-2026-088 Melebihi Toleransi 5°C',
                'department' => 'Produksi',
                'pic' => 'Ahmad Fauzi (Spv Ekstraksi)',
                'nomor_bets' => 'EXT-2026-088',
                'nomor_alat' => 'MSN-EXT-01',
                'nomor_dokumen' => 'SOP-PRD-012',
                'objek_terdampak' => 'Sirup Ekstrak Temulawak Plus 100ml',
                'jenis_penyimpangan' => 'Peralatan / Mesin',
                'identifikasi_penyimpangan' => 'Kritis',
                'tanggal_temuan' => Carbon::now()->subDays(3)->toDateString(),
                'is_other_batch_affected' => false,
                'frekuensi_kejadian' => 'Tidak Pernah Sebelumnya',
                'is_production_stopped' => true,
                'immediate_action_details' => 'Operasi ekstraksi dihentikan sementara, tangki distabilkan pada suhu 45°C dan produk diberi label Karantina QC.',
                'root_cause' => 'Sensor thermocouple pada jaket pemanas mengalami penyimpangan kalibrasi +6.5°C dan kerak pemanas menumpuk.',
                'fishbone_6m' => [
                    'machine' => [
                        'Sensor thermocouple jaket pemanas drift +6.5°C',
                        'Terdapat kerak isolator panas pada elemen uap',
                    ],
                    'man' => [
                        'Operator terlambat memeriksa display recorder analog',
                    ],
                    'method' => [
                        'SOP pembersihan kerak kerak boiler belum terjadwal mingguan',
                    ],
                    'materials' => [
                        'Bahan pelarut air RO berada dalam batas normal',
                    ],
                    'milieu' => [
                        'Suhu ruang staging normal 23°C',
                    ],
                    'measurement' => [
                        'Jadwal re-kalibrasi berkala thermocouple terlewati 12 hari',
                    ],
                ],
                'five_why' => [
                    'Mengapa suhu ekstraksi melampaui batas toleransi 55°C? -> Katup uap pemanas membuka terus menerus.',
                    'Mengapa katup uap membuka terus? -> Kontroler PLC membaca suhu masih 48°C (padahal suhu riil 58°C).',
                    'Mengapa PLC membaca suhu lebih rendah? -> Sensor thermocouple aus dan bergeser kalibrasi.',
                    'Mengapa pergeseran kalibrasi tidak terdeteksi sebelum proses? -> Tidak ada verifikasi termometer pembanding harian sebelum pengolahan bets.',
                    'Akar Masalah -> Ketiadaan checklist verifikasi kalibrasi harian pada instrumen suhu kritis.',
                ],
                'evaluasi_tindakan' => [
                    [
                        'tindakan' => 'Penggantian sensor thermocouple dan re-kalibrasi independen terakreditasi KAN',
                        'severity' => 3,
                        'occurrence' => 1,
                        'detection' => 2,
                        'rpn' => 6,
                        'evaluasi' => 'Risiko kerusakan suhu dapat dicegah secara definitif.',
                    ],
                    [
                        'tindakan' => 'Penyusunan checklist verifikasi termometer ganda sebelum pemanasan bets dimulai',
                        'severity' => 2,
                        'occurrence' => 1,
                        'detection' => 1,
                        'rpn' => 2,
                        'evaluasi' => 'Pencegahan redundan efektif operasional.',
                    ],
                ],
                'mengetahui_data' => [
                    [
                        'user_id' => $admin?->id,
                        'name' => 'Budi Santoso',
                        'role' => 'Manager Produksi',
                        'assigned_at' => Carbon::now()->subDays(3)->toDateTimeString(),
                        'feedback' => 'Telah dilakukan isolasi bets dan mesin sedang dibersihkan dari kerak uap.',
                        'feedback_at' => Carbon::now()->subDays(2)->toDateTimeString(),
                        'signature_path' => 'signatures/admin.png',
                    ],
                ],
                'status' => 'APPROVED',
            ]
        );

        $dev2 = Deviation::updateOrCreate(
            ['deviation_number' => 'DR/002/2026'],
            [
                'initiator_id' => $admin?->id,
                'pk_number' => 'PK/002/2026',
                'topik' => 'Kebocoran Induksi Seal Botol Sirup Temulawak Pada Line Liquid 2',
                'department' => 'Produksi',
                'pic' => 'Siti Nurhaliza (Operator Capping)',
                'nomor_bets' => 'EXT-2026-089',
                'nomor_alat' => 'MSN-FL-02',
                'objek_terdampak' => 'Sirup Temulawak 100ml',
                'jenis_penyimpangan' => 'Pengemasan Primer / Sekunder',
                'identifikasi_penyimpangan' => 'Mayor',
                'tanggal_temuan' => Carbon::now()->subDays(1)->toDateString(),
                'is_other_batch_affected' => false,
                'frekuensi_kejadian' => 'Jarang',
                'is_production_stopped' => false,
                'immediate_action_details' => 'Sebanyak 150 botol yang tidak rapat di-hold di area karantina dan induction sealer disetel ulang.',
                'status' => 'IN REVIEW',
            ]
        );

        // 5. Seed CAPA & CAPA Actions (Auto-Spawned from Deviasi 1)
        $capa1 = Capa::updateOrCreate(
            ['capa_number' => 'CAPA/2026/001'],
            [
                'deviation_id' => $dev1->id,
                'initiator_id' => $dev1->initiator_id,
                'deviation_number_ref' => $dev1->deviation_number,
                'tanggal_penyimpangan' => $dev1->tanggal_temuan,
                'tindakan_capa' => 'Penggantian sensor thermocouple, pembersihan kerak uap, dan verifikasi termometer ganda.',
                'status' => 'IN PROGRESS',
            ]
        );

        CapaAction::updateOrCreate(
            ['capa_id' => $capa1->id, 'action_number' => 'CAPA-S1-001'],
            [
                'pic_id' => $admin?->id,
                'tindakan' => 'Penggantian sensor thermocouple suhu jaket pemanas dan re-kalibrasi kalibrator KAN',
                'jenis_tindakan' => 'korektif',
                'department' => 'Engineering',
                'deadline' => Carbon::now()->addDays(7)->toDateString(),
                'status' => 'SUBMITTED',
                'bukti_file_path' => 'https://drive.google.com/herbatech/bukti-kalibrasi-kan.pdf',
                'catatan_pelaksanaan' => 'Sensor baru telah dipasang dan bersertifikat kalibrasi KAN No. CAL/2026/099.',
            ]
        );

        CapaAction::updateOrCreate(
            ['capa_id' => $capa1->id, 'action_number' => 'CAPA-S2-001'],
            [
                'pic_id' => $admin?->id,
                'tindakan' => 'Pemasangan batas toleransi interlock cut-off otomatis pada PLC dan pembuatan checklist verifikasi harian',
                'jenis_tindakan' => 'preventif',
                'department' => 'QA',
                'deadline' => Carbon::now()->addDays(14)->toDateString(),
                'status' => 'PENDING',
            ]
        );

        // CAPA 2 (Closed Example)
        $capa2 = Capa::updateOrCreate(
            ['capa_number' => 'CAPA/2026/002'],
            [
                'deviation_id' => $dev2->id,
                'initiator_id' => $dev2->initiator_id,
                'deviation_number_ref' => $dev2->deviation_number,
                'tanggal_penyimpangan' => Carbon::now()->subDays(15)->toDateString(),
                'tindakan_capa' => 'Penyesuaian parameter daya induksi sealing dan penggantian kumparan coil.',
                'hasil_verifikasi_qa' => 'Verifikasi efektivitas telah dilakukan pada 5 bets berikutnya. Tidak ditemukan kebocoran seal induction.',
                'status' => 'CLOSE',
            ]
        );

        CapaAction::updateOrCreate(
            ['capa_id' => $capa2->id, 'action_number' => 'CAPA-S1-002'],
            [
                'pic_id' => $admin?->id,
                'tindakan' => 'Pembersihan dan penggantian kumparan coil sealer induksi',
                'jenis_tindakan' => 'korektif',
                'department' => 'Engineering',
                'deadline' => Carbon::now()->subDays(10)->toDateString(),
                'status' => 'VERIFIED',
                'bukti_file_path' => 'bukti-coil.pdf',
                'catatan_pelaksanaan' => 'Coil baru terpasang.',
                'verified_at' => Carbon::now()->subDays(5),
                'verified_by_user_id' => $admin?->id,
            ]
        );
    }
}
