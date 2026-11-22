<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Surat Perintah Kerja (SPK) - {{ $plan->plan_number }}</title>
    <style>
        body { font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; font-size: 11px; color: #1e293b; line-height: 1.4; padding: 20px; }
        .header { border-bottom: 2px solid #0f172a; padding-bottom: 12px; margin-bottom: 20px; }
        .company-name { font-size: 16px; font-weight: bold; color: #0f172a; text-transform: uppercase; letter-spacing: 0.5px; }
        .company-sub { font-size: 9px; color: #64748b; margin-top: 2px; }
        .doc-title { font-size: 15px; font-weight: bold; text-align: right; text-transform: uppercase; color: #be123c; margin-top: -30px; }
        .doc-number { font-size: 10px; text-align: right; color: #475569; }
        .meta-grid { width: 100%; margin-bottom: 20px; border-collapse: collapse; }
        .meta-grid td { padding: 4px 6px; vertical-align: top; }
        .label { font-weight: bold; color: #475569; width: 22%; }
        .val { width: 28%; color: #0f172a; }
        .badge { display: inline-block; padding: 2px 6px; border-radius: 4px; font-size: 9px; font-weight: bold; }
        .badge-rose { background: #ffe4e6; color: #be123c; }
        .badge-green { background: #dcfce7; color: #15803d; }
        .badge-amber { background: #fef3c7; color: #b45309; }
        table.data-table { width: 100%; border-collapse: collapse; margin-top: 15px; margin-bottom: 25px; }
        table.data-table th { background-color: #f1f5f9; color: #334155; font-weight: bold; text-align: left; padding: 8px; border: 1px solid #cbd5e1; font-size: 10px; }
        table.data-table td { padding: 8px; border: 1px solid #e2e8f0; font-size: 10px; }
        .section-title { font-size: 11px; font-weight: bold; color: #0f172a; border-left: 3px solid #be123c; padding-left: 8px; margin-bottom: 10px; text-transform: uppercase; }
        .sign-table { width: 100%; margin-top: 40px; border-collapse: collapse; page-break-inside: avoid; }
        .sign-box { border-top: 1px solid #94a3b8; padding-top: 6px; text-align: center; font-size: 10px; }
        .footer { position: fixed; bottom: 10px; left: 20px; right: 20px; font-size: 8px; color: #94a3b8; text-align: center; border-top: 1px solid #e2e8f0; padding-top: 4px; }
    </style>
</head>
<body>
    <div class="header">
        <div class="company-name">PT. Herbatech Inti Persada</div>
        <div class="company-sub">Industri Farmasi & Ekstrak Herbal Alami | Kawasan Industri Sukoharjo Plant 1</div>
        <div class="doc-title">Surat Perintah Kerja (SPK)</div>
        <div class="doc-number">No: {{ $plan->plan_number }} | Ref Odoo MO: #{{ $plan->odoo_mo_id ?? 'MO-AUTO' }}</div>
    </div>

    <table class="meta-grid">
        <tr>
            <td class="label">Nama Produk:</td>
            <td class="val" colspan="3"><strong>{{ $plan->product?->name }} ({{ $plan->product?->sku ?? $plan->product?->code }})</strong></td>
        </tr>
        <tr>
            <td class="label">Nomor Batch:</td>
            <td class="val"><strong style="color: #be123c;">{{ $plan->batch_number }}</strong></td>
            <td class="label">Tahap Proses:</td>
            <td class="val">
                <span class="badge badge-rose">{{ strtoupper($plan->proses) }}</span>
                @if($plan->packing_hold)
                    <span class="badge badge-amber">PACKING HOLD</span>
                @endif
            </td>
        </tr>
        <tr>
            <td class="label">Jalur / Lini:</td>
            <td class="val">{{ $plan->line?->name }} ({{ $plan->line?->line_number }})</td>
            <td class="label">Shift & Tanggal:</td>
            <td class="val">{{ strtoupper($plan->shift) }} | {{ $plan->tanggal?->format('d/m/Y') }}</td>
        </tr>
        <tr>
            <td class="label">Target Output:</td>
            <td class="val"><strong>{{ number_format($plan->target_output) }} Unit</strong></td>
            <td class="label">Alokasi Man Power:</td>
            <td class="val">{{ $plan->mp_count }} Orang (Multiplier: {{ number_format($plan->multiplier) }}/MP)</td>
        </tr>
        <tr>
            <td class="label">Status MO Odoo:</td>
            <td class="val"><span class="badge badge-green">{{ strtoupper($plan->mo_status) }}</span></td>
            <td class="label">Status Rencana:</td>
            <td class="val"><span class="badge badge-rose">{{ strtoupper($plan->status) }}</span></td>
        </tr>
    </table>

    <div class="section-title">Instruksi Kerja & Parameter Eksekusi CPOB</div>
    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 5%;">No</th>
                <th style="width: 35%;">Parameter Kerja</th>
                <th style="width: 30%;">Spesifikasi / Standar</th>
                <th style="width: 30%;">Verifikasi Awal Operator</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td style="text-align: center;">1</td>
                <td>Line Clearance Area</td>
                <td>Bebas dari sisa produk & dokumen batch sebelumnya</td>
                <td>[ &nbsp; ] Terverifikasi Bersih</td>
            </tr>
            <tr>
                <td style="text-align: center;">2</td>
                <td>Cycle Time (CT) Standar</td>
                <td>1.20 - 1.50 detik / pcs</td>
                <td>[ &nbsp; ] Setting Mesin Sesuai</td>
            </tr>
            <tr>
                <td style="text-align: center;">3</td>
                <td>Kesiapan Bahan Baku / Kemas</td>
                <td>Rilis QC Pass & Nomor Lot Terverifikasi</td>
                <td>[ &nbsp; ] Batch Label Sesuai</td>
            </tr>
            <tr>
                <td style="text-align: center;">4</td>
                <td>Target Efisiensi OEE</td>
                <td>Minimum 85.0% (Standar Sasaran Mutu)</td>
                <td>[ &nbsp; ] Timer Aktif</td>
            </tr>
        </tbody>
    </table>

    @if(!empty($plan->notes))
        <div style="background-color: #fff1f2; border: 1px solid #fecdd3; border-radius: 4px; padding: 8px 12px; margin-bottom: 20px;">
            <strong style="color: #be123c;">Catatan Khusus PPIC / Supervisor:</strong><br>
            <span style="font-size: 10px; color: #475569;">{{ $plan->notes }}</span>
        </div>
    @endif

    <table class="sign-table">
        <tr>
            <td style="width: 30%;">
                <div class="sign-box">
                    Direncanakan Oleh (PPIC)<br><br><br><br>
                    <strong>{{ $plan->creator?->name ?? 'Staf PPIC' }}</strong><br>
                    Planner Produksi
                </div>
            </td>
            <td style="width: 5%;"></td>
            <td style="width: 30%;">
                <div class="sign-box">
                    Diterima Oleh (Leader Lini)<br><br><br><br>
                    <strong>( ........................................ )</strong><br>
                    Leader / Operator Shift
                </div>
            </td>
            <td style="width: 5%;"></td>
            <td style="width: 30%;">
                <div class="sign-box">
                    Disetujui Oleh (Supervisor)<br><br><br><br>
                    <strong>( ........................................ )</strong><br>
                    SPV Produksi & CPOB
                </div>
            </td>
        </tr>
    </table>

    <div class="footer">
        Dicetak Otomatis melalui Sistem ERP Terintegrasi PT. Herbatech Inti Persada | Dokumen Sah Pabrikasi | {{ now()->format('d/m/Y H:i:s') }}
    </div>
</body>
</html>
