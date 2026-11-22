<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Laporan Harian Produksi - {{ $report->report_number }}</title>
    <style>
        body { font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; font-size: 11px; color: #1e293b; line-height: 1.4; padding: 20px; }
        .header { border-bottom: 2px solid #0f172a; padding-bottom: 12px; margin-bottom: 20px; }
        .company-name { font-size: 16px; font-weight: bold; color: #0f172a; text-transform: uppercase; }
        .company-sub { font-size: 9px; color: #64748b; margin-top: 2px; }
        .doc-title { font-size: 15px; font-weight: bold; text-align: right; text-transform: uppercase; color: #be123c; margin-top: -30px; }
        .doc-number { font-size: 10px; text-align: right; color: #475569; }
        .meta-grid { width: 100%; margin-bottom: 15px; border-collapse: collapse; }
        .meta-grid td { padding: 4px 6px; vertical-align: top; }
        .label { font-weight: bold; color: #475569; width: 22%; }
        .val { width: 28%; color: #0f172a; }
        .badge { display: inline-block; padding: 2px 6px; border-radius: 4px; font-size: 9px; font-weight: bold; }
        .badge-rose { background: #ffe4e6; color: #be123c; }
        .badge-green { background: #dcfce7; color: #15803d; }
        .badge-amber { background: #fef3c7; color: #b45309; }
        .kpi-container { width: 100%; border: 1px solid #e2e8f0; border-radius: 6px; margin: 15px 0; background: #f8fafc; }
        .kpi-table { width: 100%; border-collapse: collapse; }
        .kpi-cell { width: 20%; padding: 10px; text-align: center; border-right: 1px solid #e2e8f0; }
        .kpi-cell:last-child { border-right: none; }
        .kpi-title { font-size: 9px; color: #64748b; text-transform: uppercase; font-weight: bold; }
        .kpi-val { font-size: 16px; font-weight: bold; color: #0f172a; margin-top: 4px; }
        .kpi-val.highlight { color: #be123c; }
        table.data-table { width: 100%; border-collapse: collapse; margin-top: 10px; margin-bottom: 20px; }
        table.data-table th { background-color: #f1f5f9; color: #334155; font-weight: bold; text-align: left; padding: 6px 8px; border: 1px solid #cbd5e1; font-size: 9px; }
        table.data-table td { padding: 6px 8px; border: 1px solid #e2e8f0; font-size: 9px; }
        .section-title { font-size: 11px; font-weight: bold; color: #0f172a; border-left: 3px solid #be123c; padding-left: 8px; margin-top: 15px; margin-bottom: 8px; text-transform: uppercase; }
        .sign-table { width: 100%; margin-top: 30px; border-collapse: collapse; page-break-inside: avoid; }
        .sign-box { border-top: 1px solid #94a3b8; padding-top: 6px; text-align: center; font-size: 10px; }
        .footer { position: fixed; bottom: 10px; left: 20px; right: 20px; font-size: 8px; color: #94a3b8; text-align: center; border-top: 1px solid #e2e8f0; padding-top: 4px; }
    </style>
</head>
<body>
    <div class="header">
        <div class="company-name">PT. Herbatech Inti Persada</div>
        <div class="company-sub">Industri Farmasi & Ekstrak Herbal Alami | Sukoharjo Plant 1</div>
        <div class="doc-title">Laporan Harian Produksi (LHP)</div>
        <div class="doc-number">No: {{ $report->report_number }} | SPK: {{ $report->weeklyPlan?->plan_number }}</div>
    </div>

    <table class="meta-grid">
        <tr>
            <td class="label">Nama Produk:</td>
            <td class="val" colspan="3"><strong>{{ $report->product?->name }} ({{ $report->product?->sku ?? $report->product?->code }})</strong></td>
        </tr>
        <tr>
            <td class="label">Nomor Batch:</td>
            <td class="val"><strong style="color: #be123c;">{{ $report->batch_number }}</strong></td>
            <td class="label">Tahap Proses:</td>
            <td class="val"><span class="badge badge-rose">{{ strtoupper($report->proses) }}</span></td>
        </tr>
        <tr>
            <td class="label">Jalur / Mesin:</td>
            <td class="val">{{ $report->line?->name }} | {{ $report->machine?->name ?? 'Mesin Standar' }}</td>
            <td class="label">Tanggal & Shift:</td>
            <td class="val">{{ $report->tanggal?->format('d/m/Y') }} | {{ strtoupper($report->shift) }}</td>
        </tr>
        <tr>
            <td class="label">Waktu Operasi:</td>
            <td class="val">{{ $report->start_time ?? '-' }} s/d {{ $report->end_time ?? '-' }}</td>
            <td class="label">Total Gross / Downtime:</td>
            <td class="val">{{ $report->gross_time_menit }} Menit | {{ $report->total_pause_menit }} Menit</td>
        </tr>
        <tr>
            <td class="label">Output Fisik:</td>
            <td class="val"><strong>{{ number_format($report->output_fisik) }} Pcs</strong> (Kapasitas: {{ number_format($report->capacity_fisik) }})</td>
            <td class="label">Total Reject / Cacat:</td>
            <td class="val"><strong style="color: #dc2626;">{{ number_format($report->total_reject) }} Pcs</strong></td>
        </tr>
    </table>

    <!-- KPI Summary Row -->
    <div class="kpi-container">
        <table class="kpi-table">
            <tr>
                <td class="kpi-cell">
                    <div class="kpi-title">Availability</div>
                    <div class="kpi-val">{{ number_format($report->availability_persen, 1) }}%</div>
                </td>
                <td class="kpi-cell">
                    <div class="kpi-title">Performance</div>
                    <div class="kpi-val">{{ number_format($report->performance_persen, 1) }}%</div>
                </td>
                <td class="kpi-cell">
                    <div class="kpi-title">Yield / Mutu</div>
                    <div class="kpi-val">{{ number_format($report->yield_persen, 1) }}%</div>
                </td>
                <td class="kpi-cell">
                    <div class="kpi-title">Overall OEE</div>
                    <div class="kpi-val highlight">{{ number_format($report->oee_persen, 1) }}%</div>
                </td>
                <td class="kpi-cell">
                    <div class="kpi-title">Produktivitas</div>
                    <div class="kpi-val">{{ number_format($report->produktivitas_persen, 1) }}%</div>
                </td>
            </tr>
        </table>
    </div>

    <!-- Downtime Log Table -->
    <div class="section-title">Log Jeda & Alasan Henti Mesin (Downtime Event)</div>
    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 5%;">No</th>
                <th style="width: 20%;">Kategori</th>
                <th style="width: 45%;">Alasan / Deskripsi Kendala</th>
                <th style="width: 15%; text-align: right;">Durasi (Menit)</th>
                <th style="width: 15%;">Waktu Catat</th>
            </tr>
        </thead>
        <tbody>
            @forelse($report->downtimeDetails as $idx => $dt)
                <tr>
                    <td style="text-align: center;">{{ $idx + 1 }}</td>
                    <td><span class="badge badge-amber">{{ strtoupper($dt->kategori) }}</span></td>
                    <td>{{ $dt->alasan_nama }} @if($dt->keterangan) <span style="color:#64748b;">({{ $dt->keterangan }})</span> @endif</td>
                    <td style="text-align: right; font-weight: bold;">{{ number_format($dt->durasi_menit, 1) }}</td>
                    <td>{{ $dt->created_at?->format('H:i') }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="5" style="text-align: center; color: #94a3b8; font-style: italic;">Tidak ada catatan henti mesin (Downtime 0 menit)</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <!-- Reject Detail Table -->
    <div class="section-title">Rincian Reject & Kerugian Material (Scrap Details)</div>
    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 5%;">No</th>
                <th style="width: 30%;">Jenis Cacat / Defect</th>
                <th style="width: 25%;">Material Terkait</th>
                <th style="width: 15%; text-align: right;">Jumlah Reject</th>
                <th style="width: 25%;">Keterangan & Ref Odoo</th>
            </tr>
        </thead>
        <tbody>
            @forelse($report->rejectDetails as $idx => $rj)
                <tr>
                    <td style="text-align: center;">{{ $idx + 1 }}</td>
                    <td><strong style="color: #be123c;">{{ $rj->jenis_reject }}</strong></td>
                    <td>{{ $rj->material_name ?? '-' }}</td>
                    <td style="text-align: right; font-weight: bold;">{{ number_format($rj->jumlah) }} {{ $rj->satuan }}</td>
                    <td>Ref Odoo Scrap #{{ $rj->odoo_scrap_id ?? '-' }} @if($rj->keterangan) - {{ $rj->keterangan }} @endif</td>
                </tr>
            @empty
                <tr>
                    <td colspan="5" style="text-align: center; color: #94a3b8; font-style: italic;">Tidak ada produk reject (100% Good Output)</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <!-- Signature Block -->
    <table class="sign-table">
        <tr>
            <td style="width: 30%;">
                <div class="sign-box">
                    Dibuat Oleh (Operator/Leader)<br><br><br><br>
                    <strong>{{ $report->operator?->name ?? 'Operator Lantai Pabrik' }}</strong><br>
                    Leader Produksi
                </div>
            </td>
            <td style="width: 40%;"></td>
            <td style="width: 30%;">
                <div class="sign-box">
                    Diverifikasi & Dikunci (Supervisor)<br><br><br><br>
                    <strong>{{ $report->lockedBy?->name ?? '( Belum Dikunci )' }}</strong><br>
                    @if($report->locked_at)
                        <span style="font-size: 8px; color: #15803d;">Tervalidasi: {{ $report->locked_at->format('d/m/Y H:i') }}</span>
                    @else
                        <span>SPV Produksi / CPOB</span>
                    @endif
                </div>
            </td>
        </tr>
    </table>

    <div class="footer">
        Dicetak Otomatis dari Sistem ERP Terpadu PT. Herbatech Inti Persada | Dokumen CPOB Sah | {{ now()->format('d/m/Y H:i:s') }}
    </div>
</body>
</html>
