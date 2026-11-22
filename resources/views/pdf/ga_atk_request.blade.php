<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Form Permintaan {{ $request->type }} - {{ $request->request_number }}</title>
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
        .sign-table td { width: 25%; border: 1px solid #cbd5e1; padding: 6px; vertical-align: bottom; }
        .sign-space { height: 50px; }
        .footer { font-size: 7.5pt; color: #94a3b8; text-align: right; margin-top: 10px; }
    </style>
</head>
<body>
    <table class="header-table">
        <tr>
            <td style="width: 25%; text-align: center;">
                <div class="company-title">HERBATECH</div>
                <div style="font-size: 8pt; color: #64748b;">General Affair Dept</div>
            </td>
            <td style="width: 50%; text-align: center;">
                <div class="doc-title">FORMULIR PERMINTAAN {{ $request->type === 'RT' ? 'RUMAH TANGGA (RT)' : 'ALAT TULIS KANTOR (ATK)' }}<br><span style="font-size: 9pt; font-weight: normal;">(Pengajuan & Distribusi Perlengkapan Kantor)</span></div>
            </td>
            <td style="width: 25%; font-size: 8pt;">
                <strong>No. Dok:</strong> SOP-GA-003-F01<br>
                <strong>No. Req:</strong> {{ $request->request_number }}<br>
                <strong>Tgl:</strong> {{ $request->tanggal_pengajuan ? $request->tanggal_pengajuan->format('d/m/Y') : date('d/m/Y') }}
            </td>
        </tr>
    </table>

    <table class="meta-table">
        <tr>
            <td class="meta-label">Nomor Permintaan:</td>
            <td class="meta-val"><strong>{{ $request->request_number }}</strong></td>
            <td class="meta-label">Status:</td>
            <td class="meta-val"><strong>{{ $request->status }}</strong></td>
        </tr>
        <tr>
            <td class="meta-label">Pemohon:</td>
            <td class="meta-val">{{ $request->user?->name ?? 'User' }}</td>
            <td class="meta-label">Departemen:</td>
            <td class="meta-val">{{ $request->department?->name ?? '-' }}</td>
        </tr>
        <tr>
            <td class="meta-label">Jenis Permintaan:</td>
            <td class="meta-val">{{ $request->type === 'RT' ? 'Barang Rumah Tangga / Kebersihan' : 'Alat Tulis Kantor (ATK)' }}</td>
            <td class="meta-label">Tgl Penyerahan:</td>
            <td class="meta-val">{{ $request->diberikan_pada ? $request->diberikan_pada->format('d/m/Y') : '-' }}</td>
        </tr>
    </table>

    <div class="section-header">1. Alasan / Kebutuhan Pengajuan</div>
    <div class="content-box">
        {{ $request->alasan_pengajuan }}
    </div>

    <div class="section-header">2. Daftar Rincian Barang Diminta</div>
    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 5%;">No</th>
                <th style="width: 40%;">Nama Barang</th>
                <th style="width: 15%; text-align: right;">Jumlah Diajukan</th>
                <th style="width: 15%; text-align: right;">Jumlah Disetujui</th>
                <th style="width: 10%;">Satuan</th>
                <th style="width: 15%;">Keterangan</th>
            </tr>
        </thead>
        <tbody>
            @forelse($request->items as $idx => $item)
            <tr>
                <td style="text-align: center;">{{ $idx + 1 }}</td>
                <td>{{ $item->item_display_name }}</td>
                <td style="text-align: right;">{{ $item->quantity }}</td>
                <td style="text-align: right; font-weight: bold; color: {{ $item->approved_quantity > 0 ? '#059669' : '#dc2626' }};">
                    {{ $item->approved_quantity }}
                </td>
                <td>{{ $item->uom?->code ?? 'Pcs' }}</td>
                <td>{{ $item->rejection_note ?? ($item->status === 'approved' ? 'Disetujui' : '-') }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="6" style="text-align: center; color: #94a3b8;">Tidak ada item barang diajukan.</td>
            </tr>
            @endforelse
        </tbody>
    </table>

    @if($request->manager_notes || $request->admin_notes)
    <div class="section-header">3. Catatan Pemeriksaan</div>
    <div class="content-box">
        @if($request->manager_notes)
            <strong>Catatan Manager:</strong> {{ $request->manager_notes }}<br>
        @endif
        @if($request->admin_notes)
            <strong>Catatan Admin GA:</strong> {{ $request->admin_notes }}
        @endif
    </div>
    @endif

    <div class="section-header">4. Pengesahan & Bukti Penyerahan</div>
    <table class="sign-table">
        <tr>
            <td>
                <strong>Pemohon</strong><br>
                <span style="font-size: 7.5pt; color: #64748b;">(Diajukan)</span>
                <div class="sign-space"></div>
                <strong>{{ $request->user?->name ?? '.......................' }}</strong>
            </td>
            <td>
                <strong>Manager Departemen</strong><br>
                <span style="font-size: 7.5pt; color: #64748b;">(Disetujui)</span>
                <div class="sign-space">
                    @if($request->manager_approved_by)
                        <div style="color: #059669; font-weight: bold; margin-top: 15px;">✓ APPROVED</div>
                    @endif
                </div>
                <strong>{{ $request->managerApprover?->name ?? '.......................' }}</strong>
            </td>
            <td>
                <strong>Admin GA</strong><br>
                <span style="font-size: 7.5pt; color: #64748b;">(Diverifikasi Stock)</span>
                <div class="sign-space">
                    @if($request->admin_approved_by)
                        <div style="color: #059669; font-weight: bold; margin-top: 15px;">✓ APPROVED</div>
                    @endif
                </div>
                <strong>{{ $request->adminApprover?->name ?? '.......................' }}</strong>
            </td>
            <td>
                <strong>Penerima Barang</strong><br>
                <span style="font-size: 7.5pt; color: #64748b;">(Diterima Sesuai)</span>
                <div class="sign-space"></div>
                <strong>{{ $request->receiver_name ?? '.......................' }}</strong>
            </td>
        </tr>
    </table>

    <div class="footer">
        Dicetak otomatis oleh Sistem ERP Terintegrasi PT HerbaTech Indo Utama pada {{ $printedAt }}
    </div>
</body>
</html>
