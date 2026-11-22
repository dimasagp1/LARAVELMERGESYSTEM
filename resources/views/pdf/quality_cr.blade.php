<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Formulir Usulan Perubahan - {{ $cr->cr_number }}</title>
    <style>
        @page { size: A4 portrait; margin: 15mm; }
        body { font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; font-size: 10pt; color: #1e293b; line-height: 1.35; }
        .header-table { width: 100%; border-collapse: collapse; margin-bottom: 12px; border: 1.5px solid #0f766e; }
        .header-table td { padding: 6px 10px; border: 1px solid #0f766e; vertical-align: middle; }
        .company-title { font-size: 13pt; font-weight: bold; color: #0f766e; }
        .doc-title { font-size: 12pt; font-weight: bold; text-align: center; color: #0f766e; }
        .meta-table { width: 100%; border-collapse: collapse; margin-bottom: 12px; font-size: 9.5pt; }
        .meta-table td { padding: 4px 6px; }
        .meta-label { width: 22%; font-weight: bold; color: #475569; }
        .meta-val { width: 28%; }
        .section-header { background: #0f766e; color: #ffffff; padding: 4px 8px; font-size: 9.5pt; font-weight: bold; margin-top: 10px; margin-bottom: 4px; text-transform: uppercase; }
        .content-box { border: 1px solid #cbd5e1; padding: 6px 8px; font-size: 9pt; background: #f8fafc; margin-bottom: 8px; }
        .grid-2 { width: 100%; border-collapse: collapse; margin-bottom: 8px; }
        .grid-2 td { width: 50%; vertical-align: top; padding: 4px; }
        .data-table { width: 100%; border-collapse: collapse; margin-top: 4px; margin-bottom: 10px; font-size: 8.5pt; }
        .data-table th { background: #f1f5f9; color: #334155; border: 1px solid #cbd5e1; padding: 5px; text-align: left; }
        .data-table td { border: 1px solid #cbd5e1; padding: 5px; }
        .badge { display: inline-block; padding: 2px 6px; border-radius: 3px; font-weight: bold; font-size: 8pt; }
        .badge-cra { background: #fee2e2; color: #991b1b; }
        .badge-crb { background: #e0f2fe; color: #075985; }
        .sign-table { width: 100%; border-collapse: collapse; margin-top: 14px; text-align: center; font-size: 8.5pt; }
        .sign-table td { width: 33.33%; border: 1px solid #cbd5e1; padding: 6px; vertical-align: bottom; }
        .sign-space { height: 50px; }
    </style>
</head>
<body>
    <table class="header-table">
        <tr>
            <td style="width: 25%; text-align: center;">
                <div class="company-title">HERBATECH</div>
                <div style="font-size: 8pt; color: #64748b;">Quality Assurance Dept</div>
            </td>
            <td style="width: 50%; text-align: center;">
                <div class="doc-title">FORMULIR USULAN PERUBAHAN<br><span style="font-size: 10pt; font-weight: normal;">(CHANGE REQUEST FORM - {{ $cr->type }})</span></div>
            </td>
            <td style="width: 25%; font-size: 8pt;">
                <strong>No. Dok:</strong> SOP-QA-004-F01<br>
                <strong>No. CR:</strong> {{ $cr->cr_number }}<br>
                <strong>Tgl:</strong> {{ $cr->created_at ? $cr->created_at->format('d/m/Y') : date('d/m/Y') }}
            </td>
        </tr>
    </table>

    <table class="meta-table">
        <tr>
            <td class="meta-label">Nomor CR:</td>
            <td class="meta-val"><strong>{{ $cr->cr_number }}</strong></td>
            <td class="meta-label">Klasifikasi:</td>
            <td class="meta-val">
                <span class="badge {{ $cr->type === 'CRA' ? 'badge-cra' : 'badge-crb' }}">{{ $cr->type }} ({{ $cr->type === 'CRA' ? 'Mayor / Berdampak Mutu' : 'Minor / Non-Kritis' }})</span>
            </td>
        </tr>
        <tr>
            <td class="meta-label">Inisiator:</td>
            <td class="meta-val">{{ $cr->initiator->name ?? '-' }} ({{ $cr->initiator->department->name ?? 'QA' }})</td>
            <td class="meta-label">Sifat Perubahan:</td>
            <td class="meta-val">{{ $cr->sifat_perubahan }}</td>
        </tr>
        <tr>
            <td class="meta-label">Produk / Sistem:</td>
            <td class="meta-val">{{ $cr->nama_produk }}</td>
            <td class="meta-label">Target Pelaksanaan:</td>
            <td class="meta-val">{{ $cr->target_pelaksanaan ? \Carbon\Carbon::parse($cr->target_pelaksanaan)->format('d/m/Y') : '-' }}</td>
        </tr>
    </table>

    <div class="section-header">1. Rincian Usulan Perubahan</div>
    <table class="grid-2">
        <tr>
            <td>
                <strong style="font-size: 8.5pt; color: #475569;">Kondisi Awal (Sebelum Perubahan):</strong>
                <div class="content-box">{{ $cr->awal_sebelum_perubahan }}</div>
            </td>
            <td>
                <strong style="font-size: 8.5pt; color: #0f766e;">Usulan Perubahan:</strong>
                <div class="content-box" style="background: #f0fdfa; border-color: #99f6e4;">{{ $cr->usulan_perubahan }}</div>
            </td>
        </tr>
    </table>

    <div style="margin-bottom: 6px;">
        <strong style="font-size: 8.5pt; color: #475569;">Alasan & Latar Belakang Perubahan:</strong>
        <div class="content-box">{{ $cr->alasan_perubahan ?? 'Peningkatan efisiensi proses dan pemenuhan standar CPOB.' }}</div>
    </div>

    @if($cr->isCRA())
    <div class="section-header">2. Analisis Risiko Kualitatif & Kuantitatif (FMEA CPOB)</div>
    <table class="data-table">
        <thead>
            <tr>
                <th>Severity (S: 1-10)</th>
                <th>Occurrence (O: 1-10)</th>
                <th>Detection (D: 1-10)</th>
                <th>RPN Score (S x O x D)</th>
                <th>Kategori Tingkat Risiko</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td style="text-align: center; font-weight: bold;">{{ $cr->severity }}</td>
                <td style="text-align: center; font-weight: bold;">{{ $cr->occurrence }}</td>
                <td style="text-align: center; font-weight: bold;">{{ $cr->detection }}</td>
                <td style="text-align: center; font-size: 11pt; font-weight: bold; color: {{ $cr->rpn > 50 ? '#dc2626' : '#0f766e' }};">{{ $cr->rpn }}</td>
                <td style="text-align: center;">
                    @if($cr->rpn >= 80)
                        <span style="color: #dc2626; font-weight: bold;">Tinggi (Kritis)</span>
                    @elseif($cr->rpn >= 40)
                        <span style="color: #d97706; font-weight: bold;">Sedang (Moderat)</span>
                    @else
                        <span style="color: #16a34a; font-weight: bold;">Rendah (Dapat Diterima)</span>
                    @endif
                </td>
            </tr>
        </tbody>
    </table>
    <div style="margin-bottom: 6px;">
        <strong style="font-size: 8.5pt; color: #475569;">Tindakan Pengendalian & Mitigasi Risiko:</strong>
        <div class="content-box">{{ $cr->risk_mitigation ?? 'Tidak ada mitigasi khusus yang diperlukan.' }}</div>
    </div>
    @endif

    <div class="section-header">3. Pengkajian Departemen Terkait & Verifikasi QA</div>
    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 20%;">Departemen / PIC</th>
                <th style="width: 45%;">Kajian Dampak & Konsekuensi Teknis</th>
                <th style="width: 25%;">Rekomendasi</th>
                <th style="width: 10%; text-align: center;">Status</th>
            </tr>
        </thead>
        <tbody>
            @php
                $assessments = $cr->qa_verification_data['stage_1']['assessments'] ?? [];
            @endphp
            @forelse($assessments as $ass)
            <tr>
                <td><strong>{{ $ass['department'] ?? 'Terkait' }}</strong><br><span style="font-size: 7.5pt; color: #64748b;">{{ $ass['pic_name'] ?? '-' }}</span></td>
                <td>{{ $ass['risk_analysis'] ?? 'Telah dikaji dan tidak berdampak negatif terhadap spesifikasi mutu.' }}</td>
                <td>{{ $ass['recommendation'] ?? 'Dapat dilanjutkan dengan pemantauan.' }}</td>
                <td style="text-align: center; font-weight: bold; color: #0f766e;">{{ $ass['status'] ?? 'APPROVED' }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="4" style="text-align: center; color: #94a3b8; font-style: italic;">Tidak ada catatan pengkajian departemen tambahan.</td>
            </tr>
            @endforelse
        </tbody>
    </table>

    <div class="section-header">4. Lembar Pengesahan & Tanda Tangan Persetujuan</div>
    <table class="sign-table">
        <tr>
            <td>
                <strong>Diajukan Oleh (Inisiator)</strong>
                <div class="sign-space"></div>
                <strong>{{ $cr->initiator->name ?? 'Inisiator' }}</strong><br>
                <span style="font-size: 7.5pt; color: #64748b;">Tgl: {{ $cr->created_at ? $cr->created_at->format('d/m/Y') : date('d/m/Y') }}</span>
            </td>
            <td>
                <strong>Disetujui Level 1 (Head of QA)</strong>
                <div class="sign-space">
                    @if(!empty($cr->qa_verification_data['stage_1']['approvals']['head_of_quality']))
                        <span style="color: #0f766e; font-size: 8pt; font-weight: bold;">DIGITALLY SIGNED</span>
                    @endif
                </div>
                <strong>{{ $cr->qa_verification_data['stage_1']['approvals']['head_of_quality']['name'] ?? 'Head of Quality' }}</strong><br>
                <span style="font-size: 7.5pt; color: #64748b;">Tgl: {{ $cr->qa_verification_data['stage_1']['approvals']['head_of_quality']['signed_at'] ?? '-' }}</span>
            </td>
            <td>
                <strong>Disetujui Akhir ({{ $cr->isCRA() ? 'Operational / General Manager' : 'QA Management' }})</strong>
                <div class="sign-space">
                    @if(!empty($cr->qa_verification_data['stage_1']['approvals']['general_manager']))
                        <span style="color: #0f766e; font-size: 8pt; font-weight: bold;">DIGITALLY SIGNED</span>
                    @endif
                </div>
                <strong>{{ $cr->qa_verification_data['stage_1']['approvals']['general_manager']['name'] ?? ($cr->qa_verification_data['stage_1']['approvals']['operational_manager']['name'] ?? 'Direktur Operasional') }}</strong><br>
                <span style="font-size: 7.5pt; color: #64748b;">Tgl: {{ $cr->qa_verification_data['stage_1']['approvals']['general_manager']['signed_at'] ?? ($cr->qa_verification_data['stage_1']['approvals']['operational_manager']['signed_at'] ?? '-') }}</span>
            </td>
        </tr>
    </table>

    <div style="margin-top: 15px; font-size: 7.5pt; color: #94a3b8; text-align: right;">
        Dokumen ini diterbitkan secara elektronik melalui QMS HerbaTech ERP &bullet; Dicetak pada {{ $printedAt }}
    </div>
</body>
</html>
