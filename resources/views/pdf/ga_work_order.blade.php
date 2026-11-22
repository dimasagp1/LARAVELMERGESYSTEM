<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Work Order - {{ $wo->wo_number }}</title>
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
        .grid-2 { width: 100%; border-collapse: collapse; margin-bottom: 6px; }
        .grid-2 td { width: 50%; vertical-align: top; padding: 4px; }
        .data-table { width: 100%; border-collapse: collapse; margin-top: 4px; margin-bottom: 8px; font-size: 8.5pt; }
        .data-table th { background: #fef3c7; color: #92400e; border: 1px solid #cbd5e1; padding: 5px; text-align: left; }
        .data-table td { border: 1px solid #cbd5e1; padding: 5px; }
        .badge { display: inline-block; padding: 2px 6px; border-radius: 3px; font-weight: bold; font-size: 8pt; }
        .badge-urgent { background: #fee2e2; color: #991b1b; }
        .badge-normal { background: #e0f2fe; color: #075985; }
        .sign-table { width: 100%; border-collapse: collapse; margin-top: 12px; text-align: center; font-size: 8.5pt; }
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
                <div class="doc-title">LEMBAR WORK ORDER (FPU)<br><span style="font-size: 9pt; font-weight: normal;">(Form A & D - Permintaan & BAST Pemeliharaan)</span></div>
            </td>
            <td style="width: 25%; font-size: 8pt;">
                <strong>No. Dok:</strong> SOP-GA-002-F01<br>
                <strong>No. WO:</strong> {{ $wo->wo_number }}<br>
                <strong>Tgl:</strong> {{ $wo->created_at ? $wo->created_at->format('d/m/Y') : date('d/m/Y') }}
            </td>
        </tr>
    </table>

    <table class="meta-table">
        <tr>
            <td class="meta-label">Nomor WO:</td>
            <td class="meta-val"><strong>{{ $wo->wo_number }}</strong></td>
            <td class="meta-label">Status:</td>
            <td class="meta-val"><strong>{{ $wo->status }}</strong></td>
        </tr>
        <tr>
            <td class="meta-label">Pemohon:</td>
            <td class="meta-val">{{ $wo->user?->name ?? 'User' }} ({{ $wo->department?->name ?? '-' }})</td>
            <td class="meta-label">Prioritas:</td>
            <td class="meta-val">
                <span class="badge {{ in_array($wo->priority, ['Tinggi', 'Darurat']) ? 'badge-urgent' : 'badge-normal' }}">
                    {{ $wo->priority }}
                </span>
            </td>
        </tr>
        <tr>
            <td class="meta-label">Kategori Pekerjaan:</td>
            <td class="meta-val">{{ $wo->category }}</td>
            <td class="meta-label">Lokasi Fasilitas:</td>
            <td class="meta-val">{{ $wo->location?->name ?? '-' }} ({{ $wo->location?->plant?->name ?? 'Plant' }})</td>
        </tr>
        <tr>
            <td class="meta-label">Teknisi Bertugas:</td>
            <td class="meta-val">{{ $wo->assignedEngineer?->name ?? 'Belum Ditugaskan' }}</td>
            <td class="meta-label">Ditugaskan Pada:</td>
            <td class="meta-val">{{ $wo->assigned_at ? \Carbon\Carbon::parse($wo->assigned_at)->format('d/m/Y H:i') : '-' }}</td>
        </tr>
    </table>

    <div class="section-header">1. FORM A: Deskripsi Kerusakan / Keluhan Sarana</div>
    <div class="content-box">
        {{ $wo->complaint_description }}
    </div>

    @if($wo->check_result)
    <div class="section-header">2. FORM B: Hasil Pemeriksaan Awal Teknisi</div>
    <table class="grid-2">
        <tr>
            <td>
                <strong style="font-size: 8pt; color: #475569;">Hasil Diagnosis / Pengecekan:</strong>
                <div class="content-box">{{ $wo->check_result }}</div>
            </td>
            <td>
                <strong style="font-size: 8pt; color: #475569;">Rencana Tindakan / Perbaikan:</strong>
                <div class="content-box">{{ $wo->action_plan }}</div>
            </td>
        </tr>
    </table>
    <div style="font-size: 8.5pt; margin-bottom: 6px;">
        <strong>Kebutuhan Pembelian Material (FPU):</strong> {{ $wo->needs_materials ? 'YA (Form C Diterbitkan)' : 'TIDAK (Menggunakan Stock / Perbaikan Langsung)' }}
        @if($wo->fpuRequest)
            &nbsp;|&nbsp; <strong>No. FPU:</strong> {{ $wo->fpuRequest->fpu_number }} (Status: {{ $wo->fpuRequest->status }})
        @endif
    </div>
    @endif

    @if($wo->progresses && $wo->progresses->count() > 0)
    <div class="section-header">3. Log Perkembangan Pengerjaan</div>
    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 20%;">Waktu</th>
                <th style="width: 15%;">Progres (%)</th>
                <th style="width: 25%;">Teknisi</th>
                <th style="width: 40%;">Catatan Tindakan</th>
            </tr>
        </thead>
        <tbody>
            @foreach($wo->progresses as $prog)
            <tr>
                <td>{{ $prog->logged_at ? \Carbon\Carbon::parse($prog->logged_at)->format('d/m/Y H:i') : '-' }}</td>
                <td><strong>{{ $prog->progress_percentage }}%</strong></td>
                <td>{{ $prog->user?->name ?? '-' }}</td>
                <td>{{ $prog->notes }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
    @endif

    @if($wo->handover)
    <div class="section-header">4. FORM D: Berita Acara Serah Terima (BAST) Pekerjaan</div>
    <table class="meta-table">
        <tr>
            <td class="meta-label">Nomor BAST:</td>
            <td class="meta-val"><strong>{{ $wo->handover->bast_number }}</strong></td>
            <td class="meta-label">Tanggal Selesai:</td>
            <td class="meta-val">{{ $wo->handover->handover_date ? \Carbon\Carbon::parse($wo->handover->handover_date)->format('d/m/Y') : '-' }}</td>
        </tr>
        <tr>
            <td class="meta-label">Rating Kepuasan:</td>
            <td class="meta-val"><strong>{{ $wo->handover->rating ?? '-' }} / 5 Bintang</strong></td>
            <td class="meta-label">Dikonfirmasi Oleh:</td>
            <td class="meta-val">{{ $wo->handover->clientUser?->name ?? 'Pemohon' }}</td>
        </tr>
    </table>
    <table class="grid-2">
        <tr>
            <td>
                <strong style="font-size: 8pt; color: #475569;">Akar Masalah (Root Cause):</strong>
                <div class="content-box">{{ $wo->handover->root_cause }}</div>
            </td>
            <td>
                <strong style="font-size: 8pt; color: #475569;">Solusi yang Diterapkan:</strong>
                <div class="content-box">{{ $wo->handover->action_taken }}</div>
            </td>
        </tr>
    </table>
    @if($wo->handover->feedback)
    <div style="font-size: 8.5pt; margin-bottom: 6px;">
        <strong>Komentar / Feedback Pemohon:</strong> <em>"{{ $wo->handover->feedback }}"</em>
    </div>
    @endif
    @endif

    <div class="section-header">5. Pengesahan & Tanda Tangan</div>
    <table class="sign-table">
        <tr>
            <td>
                <strong>Pemohon Fasilitas</strong><br>
                <span style="font-size: 7.5pt; color: #64748b;">(Form A / Konfirmasi BAST)</span>
                <div class="sign-space"></div>
                <strong>{{ $wo->user?->name ?? '.......................' }}</strong><br>
                <span>Dept. {{ $wo->department?->name ?? '-' }}</span>
            </td>
            <td>
                <strong>Teknisi Bertugas</strong><br>
                <span style="font-size: 7.5pt; color: #64748b;">(Form B & Serah Terima)</span>
                <div class="sign-space"></div>
                <strong>{{ $wo->assignedEngineer?->name ?? '.......................' }}</strong><br>
                <span>Teknisi Fasilitas FPU</span>
            </td>
            <td>
                <strong>Supervisor / Manager GA</strong><br>
                <span style="font-size: 7.5pt; color: #64748b;">(Verifikasi & Penutupan WO)</span>
                <div class="sign-space"></div>
                <strong>...................................</strong><br>
                <span>General Affair Dept</span>
            </td>
        </tr>
    </table>

    <div class="footer">
        Dicetak otomatis oleh Sistem ERP Terintegrasi PT HerbaTech Indo Utama pada {{ $printedAt }}
    </div>
</body>
</html>
