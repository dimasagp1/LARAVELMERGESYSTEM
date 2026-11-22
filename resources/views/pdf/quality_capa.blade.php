<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Formulir CAPA - {{ $capa->capa_number }}</title>
    <style>
        @page { size: A4 portrait; margin: 15mm; }
        body { font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; font-size: 10pt; color: #1e293b; line-height: 1.35; }
        .header-table { width: 100%; border-collapse: collapse; margin-bottom: 12px; border: 1.5px solid #0f766e; }
        .header-table td { padding: 6px 10px; border: 1px solid #0f766e; vertical-align: middle; }
        .company-title { font-size: 13pt; font-weight: bold; color: #0f766e; }
        .doc-title { font-size: 12pt; font-weight: bold; text-align: center; color: #0f766e; }
        .meta-table { width: 100%; border-collapse: collapse; margin-bottom: 12px; font-size: 9pt; }
        .meta-table td { padding: 4px 6px; }
        .meta-label { width: 22%; font-weight: bold; color: #475569; }
        .meta-val { width: 28%; }
        .section-header { background: #0f766e; color: #ffffff; padding: 4px 8px; font-size: 9.5pt; font-weight: bold; margin-top: 10px; margin-bottom: 4px; text-transform: uppercase; }
        .content-box { border: 1px solid #cbd5e1; padding: 6px 8px; font-size: 9pt; background: #f8fafc; margin-bottom: 8px; }
        .data-table { width: 100%; border-collapse: collapse; margin-top: 4px; margin-bottom: 10px; font-size: 8.5pt; }
        .data-table th { background: #f1f5f9; color: #334155; border: 1px solid #cbd5e1; padding: 5px; text-align: left; }
        .data-table td { border: 1px solid #cbd5e1; padding: 5px; }
        .badge { display: inline-block; padding: 2px 6px; border-radius: 3px; font-weight: bold; font-size: 8pt; }
        .badge-close { background: #dcfce7; color: #15803d; }
        .badge-progress { background: #fef3c7; color: #92400e; }
        .badge-approved { background: #e0f2fe; color: #075985; }
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
                <div class="doc-title">TINDAKAN KOREKTIF & PENCEGAHAN (CAPA)<br><span style="font-size: 10pt; font-weight: normal;">(CORRECTIVE AND PREVENTIVE ACTION FORM)</span></div>
            </td>
            <td style="width: 25%; font-size: 8pt;">
                <strong>No. Dok:</strong> SOP-QA-006-F01<br>
                <strong>No. CAPA:</strong> {{ $capa->capa_number }}<br>
                <strong>Ref Deviasi:</strong> {{ $capa->deviation_number_ref }}<br>
                <strong>Tgl:</strong> {{ \Carbon\Carbon::parse($capa->tanggal_penyimpangan)->format('d/m/Y') }}
            </td>
        </tr>
    </table>

    <table class="meta-table">
        <tr>
            <td class="meta-label">Nomor CAPA:</td>
            <td class="meta-val"><strong>{{ $capa->capa_number }}</strong></td>
            <td class="meta-label">Status CAPA:</td>
            <td class="meta-val">
                <span class="badge {{ $capa->status === 'CLOSE' ? 'badge-close' : ($capa->status === 'APPROVED' ? 'badge-approved' : 'badge-progress') }}">
                    {{ $capa->status }} (Progres: {{ $capa->progress_percentage }}%)
                </span>
            </td>
        </tr>
        <tr>
            <td class="meta-label">Referensi Deviasi:</td>
            <td class="meta-val"><strong>{{ $capa->deviation_number_ref }}</strong></td>
            <td class="meta-label">Inisiator Pelapor:</td>
            <td class="meta-val">{{ $capa->initiator->name ?? '-' }} ({{ $capa->deviation->department ?? 'QA' }})</td>
        </tr>
        <tr>
            <td class="meta-label">Topik Masalah:</td>
            <td class="meta-val" colspan="3">{{ $capa->deviation->topik ?? $capa->tindakan_capa }}</td>
        </tr>
    </table>

    <div class="section-header">1. Akar Masalah Berdasarkan Investigasi (Root Cause)</div>
    <div class="content-box">
        <strong>Akar Penyebab (Root Cause):</strong><br>
        {{ $capa->deviation->root_cause ?? 'Teridentifikasi pada proses investigasi deviasi terkait.' }}
    </div>

    <div class="section-header">2. Rencana Multi-Tindakan Korektif & Preventif (CAPA Actions)</div>
    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 15%;">No. Aksi</th>
                <th style="width: 12%;">Tipe</th>
                <th style="width: 40%;">Rincian Tindakan</th>
                <th style="width: 15%;">Dept & PIC</th>
                <th style="width: 10%;">Tenggat</th>
                <th style="width: 8%; text-align: center;">Status</th>
            </tr>
        </thead>
        <tbody>
            @forelse($capa->actions as $action)
            <tr>
                <td><strong>{{ $action->action_number ?? '-' }}</strong></td>
                <td>
                    <span style="font-size: 7.5pt; font-weight: bold; text-transform: uppercase; color: {{ $action->jenis_tindakan === 'korektif' ? '#0f766e' : '#2563eb' }};">
                        {{ $action->jenis_tindakan }}
                    </span>
                </td>
                <td>
                    {{ $action->tindakan }}
                    @if($action->catatan_pelaksanaan)
                        <div style="font-size: 7.5pt; color: #475569; margin-top: 2px;">
                            <em>Catatan Lapangan: {{ $action->catatan_pelaksanaan }}</em>
                        </div>
                    @endif
                </td>
                <td>{{ $action->department }}<br><span style="font-size: 7.5pt; color: #64748b;">{{ $action->pic->name ?? '-' }}</span></td>
                <td>{{ $action->deadline ? \Carbon\Carbon::parse($action->deadline)->format('d/m/Y') : '-' }}</td>
                <td style="text-align: center; font-weight: bold; font-size: 7.5pt; color: {{ $action->status === 'VERIFIED' ? '#16a34a' : '#d97706' }};">
                    {{ $action->status }}
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="6" style="text-align: center; color: #94a3b8; font-style: italic;">Belum ada sub-tindakan yang ditambahkan.</td>
            </tr>
            @endforelse
        </tbody>
    </table>

    <div class="section-header">3. Verifikasi Efektivitas & Penutupan oleh QA (Verification & Closure)</div>
    <div class="content-box">
        <strong>Hasil Verifikasi Efektivitas QA:</strong><br>
        {{ $capa->hasil_verifikasi_qa ?? ($capa->status === 'CLOSE' ? 'Seluruh tindakan perbaikan dan pencegahan telah diverifikasi efektif di lapangan dan tidak ada temuan keberulangan.' : 'Menunggu penyelesaian tindakan lapangan dan pengunggahan bukti fisik.') }}
    </div>

    <table class="sign-table">
        <tr>
            <td>
                <strong>Koordinator Tindakan (PIC)</strong>
                <div class="sign-space"></div>
                <strong>{{ $capa->pic->name ?? ($capa->initiator->name ?? 'Pelaksana Lapangan') }}</strong><br>
                <span style="font-size: 7.5pt; color: #64748b;">Tgl: {{ date('d/m/Y') }}</span>
            </td>
            <td>
                <strong>Manajer Departemen Terkait</strong>
                <div class="sign-space">
                    <span style="color: #0f766e; font-size: 8pt; font-weight: bold;">ACTION VERIFIED</span>
                </div>
                <strong>Manager Terkait</strong><br>
                <span style="font-size: 7.5pt; color: #64748b;">Tgl: {{ date('d/m/Y') }}</span>
            </td>
            <td>
                <strong>Penjaminan Mutu (Head of QA)</strong>
                <div class="sign-space">
                    @if($capa->status === 'CLOSE')
                        <span style="color: #16a34a; font-size: 8pt; font-weight: bold;">CLOSED & EFFECTIVE</span>
                    @endif
                </div>
                <strong>Head of Quality</strong><br>
                <span style="font-size: 7.5pt; color: #64748b;">Tgl: {{ $capa->status === 'CLOSE' ? ($capa->updated_at ? $capa->updated_at->format('d/m/Y') : date('d/m/Y')) : '-' }}</span>
            </td>
        </tr>
    </table>

    <div style="margin-top: 15px; font-size: 7.5pt; color: #94a3b8; text-align: right;">
        Dokumen Resmi Penutupan CAPA HerbaTech ERP &bullet; Dicetak pada {{ $printedAt }}
    </div>
</body>
</html>
