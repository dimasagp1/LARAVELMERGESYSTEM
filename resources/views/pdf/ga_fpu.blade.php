<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Form C FPU - {{ $fpu->fpu_number }}</title>
    <style>
        @page { size: A4 portrait; margin: 12mm; }
        body { font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; font-size: 9.5pt; color: #1e293b; line-height: 1.35; }
        .header-table { width: 100%; border-collapse: collapse; margin-bottom: 12px; border: 1.5px solid #d97706; }
        .header-table td { padding: 6px 10px; border: 1px solid #d97706; vertical-align: middle; }
        .company-title { font-size: 13pt; font-weight: bold; color: #b45309; }
        .doc-title { font-size: 12pt; font-weight: bold; text-align: center; color: #b45309; }
        .meta-table { width: 100%; border-collapse: collapse; margin-bottom: 10px; font-size: 9pt; }
        .meta-table td { padding: 4px 6px; }
        .meta-label { width: 22%; font-weight: bold; color: #475569; }
        .meta-val { width: 28%; }
        .section-header { background: #b45309; color: #ffffff; padding: 4px 8px; font-size: 9pt; font-weight: bold; margin-top: 10px; margin-bottom: 4px; text-transform: uppercase; }
        .content-box { border: 1px solid #cbd5e1; padding: 6px 8px; font-size: 8.5pt; background: #f8fafc; margin-bottom: 6px; }
        .data-table { width: 100%; border-collapse: collapse; margin-top: 4px; margin-bottom: 8px; font-size: 8.5pt; }
        .data-table th { background: #fef3c7; color: #92400e; border: 1px solid #cbd5e1; padding: 5px; text-align: left; }
        .data-table td { border: 1px solid #cbd5e1; padding: 5px; }
        .sign-table { width: 100%; border-collapse: collapse; margin-top: 14px; text-align: center; font-size: 8.5pt; }
        .sign-table td { width: 33.33%; border: 1px solid #cbd5e1; padding: 6px; vertical-align: bottom; }
        .sign-space { height: 50px; }
        .footer { font-size: 7.5pt; color: #94a3b8; text-align: right; margin-top: 10px; }
    </style>
</head>
<body>
    <table class="header-table">
        <tr>
            <td style="width: 25%; text-align: center;">
                <div class="company-title">HERBATECH</div>
                <div style="font-size: 8pt; color: #64748b;">General Affair & FPU Dept</div>
            </td>
            <td style="width: 50%; text-align: center;">
                <div class="doc-title">FORM C: PENGADAAN MATERIAL (FPU)<br><span style="font-size: 9pt; font-weight: normal;">(Fasilitas, Pemeliharaan & Umum)</span></div>
            </td>
            <td style="width: 25%; font-size: 8pt;">
                <strong>No. Dok:</strong> SOP-GA-002-F03<br>
                <strong>No. FPU:</strong> {{ $fpu->fpu_number }}<br>
                <strong>Tgl:</strong> {{ $fpu->created_at ? $fpu->created_at->format('d/m/Y') : date('d/m/Y') }}
            </td>
        </tr>
    </table>

    <table class="meta-table">
        <tr>
            <td class="meta-label">Nomor FPU:</td>
            <td class="meta-val"><strong>{{ $fpu->fpu_number }}</strong></td>
            <td class="meta-label">Work Order Rujukan:</td>
            <td class="meta-val"><strong>{{ $wo->wo_number }}</strong></td>
        </tr>
        <tr>
            <td class="meta-label">Status FPU:</td>
            <td class="meta-val"><strong>{{ $fpu->status }}</strong></td>
            <td class="meta-label">Prioritas WO:</td>
            <td class="meta-val">{{ $wo->priority }}</td>
        </tr>
        <tr>
            <td class="meta-label">Fasilitas / Lokasi:</td>
            <td class="meta-val">{{ $wo->location?->name ?? '-' }} ({{ $wo->location?->plant?->name ?? 'Plant' }})</td>
            <td class="meta-label">Teknisi Pengusul:</td>
            <td class="meta-val">{{ $wo->assignedEngineer?->name ?? 'Teknisi FPU' }}</td>
        </tr>
    </table>

    <div class="section-header">1. Rencana Penggunaan & Masalah Sarana</div>
    <div class="content-box">
        <strong>Uraian Keluhan:</strong> {{ $wo->complaint_description }}<br>
        <strong>Rencana Tindakan:</strong> {{ $wo->action_plan ?? '-' }}
    </div>

    <div class="section-header">2. Rincian Material & Suku Cadang Dibutuhkan</div>
    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 5%;">No</th>
                <th style="width: 45%;">Nama Material / Komponen</th>
                <th style="width: 15%; text-align: right;">Jumlah (Qty)</th>
                <th style="width: 15%; text-align: right;">Estimasi Harga</th>
                <th style="width: 20%; text-align: right;">Total Estimasi</th>
            </tr>
        </thead>
        <tbody>
            @forelse($fpu->materials as $idx => $mat)
            <tr>
                <td style="text-align: center;">{{ $idx + 1 }}</td>
                <td>{{ $mat->material_name }}</td>
                <td style="text-align: right;">{{ number_format($mat->quantity, 0) }} {{ $mat->uom?->code ?? 'Pcs' }}</td>
                <td style="text-align: right;">Rp {{ number_format($mat->estimated_unit_price, 0, ',', '.') }}</td>
                <td style="text-align: right;">Rp {{ number_format($mat->total_estimated_price, 0, ',', '.') }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="5" style="text-align: center; color: #94a3b8;">Tidak ada rincian material.</td>
            </tr>
            @endforelse
        </tbody>
        <tfoot>
            <tr style="font-weight: bold; background: #fef3c7;">
                <td colspan="4" style="text-align: right;">TOTAL ESTIMASI BIAYA MATERIAL:</td>
                <td style="text-align: right; color: #b45309;">Rp {{ number_format($fpu->total_estimated_cost, 0, ',', '.') }}</td>
            </tr>
        </tfoot>
    </table>

    <div class="section-header">3. Otorisasi Persetujuan Berjenjang (Approval Multi-Tier)</div>
    <table class="sign-table">
        <tr>
            <td>
                <strong>Staff HR & GA</strong><br>
                <span style="font-size: 7.5pt; color: #64748b;">(Pemeriksaan Anggaran)</span>
                <div class="sign-space">
                    @if($fpu->approved_by_hrga)
                        <div style="color: #059669; font-weight: bold; margin-top: 15px;">✓ DISETUJUI</div>
                        <div style="font-size: 7.5pt; color: #64748b;">{{ \Carbon\Carbon::parse($fpu->hrga_approved_at)->format('d/m/Y') }}</div>
                    @endif
                </div>
                <strong>{{ $fpu->hrgaApprover?->name ?? '.......................' }}</strong>
            </td>
            <td>
                <strong>Manager GA / Plant</strong><br>
                <span style="font-size: 7.5pt; color: #64748b;">(Otorisasi Teknis & Biaya)</span>
                <div class="sign-space">
                    @if($fpu->approved_by_manager)
                        <div style="color: #059669; font-weight: bold; margin-top: 15px;">✓ DISETUJUI</div>
                        <div style="font-size: 7.5pt; color: #64748b;">{{ \Carbon\Carbon::parse($fpu->manager_approved_at)->format('d/m/Y') }}</div>
                    @endif
                </div>
                <strong>{{ $fpu->managerApprover?->name ?? '.......................' }}</strong>
            </td>
            <td>
                <strong>General Manager</strong><br>
                <span style="font-size: 7.5pt; color: #64748b;">(Persetujuan > Rp 5 Jt)</span>
                <div class="sign-space">
                    @if($fpu->approved_by_gm)
                        <div style="color: #059669; font-weight: bold; margin-top: 15px;">✓ DISETUJUI</div>
                        <div style="font-size: 7.5pt; color: #64748b;">{{ \Carbon\Carbon::parse($fpu->gm_approved_at)->format('d/m/Y') }}</div>
                    @else
                        <div style="font-size: 7.5pt; color: #94a3b8; margin-top: 25px;">(Tidak Perlu jika < Rp 5 Jt)</div>
                    @endif
                </div>
                <strong>{{ $fpu->gmApprover?->name ?? '.......................' }}</strong>
            </td>
        </tr>
    </table>

    <div class="footer">
        Dicetak otomatis oleh Sistem ERP Terintegrasi PT HerbaTech Indo Utama pada {{ $printedAt }}
    </div>
</body>
</html>
