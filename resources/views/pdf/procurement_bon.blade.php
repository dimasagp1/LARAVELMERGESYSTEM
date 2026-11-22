<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Form Bon Sementara - {{ $bon->bon_number }}</title>
    <style>
        body { font-family: Helvetica, Arial, sans-serif; font-size: 11px; color: #1e293b; line-height: 1.4; margin: 20px; }
        .header { border-bottom: 2px solid #d97706; padding-bottom: 12px; margin-bottom: 16px; }
        .company-name { font-size: 16px; font-weight: bold; color: #d97706; text-transform: uppercase; }
        .doc-title { font-size: 14px; font-weight: bold; margin-top: 4px; color: #0f172a; }
        .meta-table { width: 100%; margin-bottom: 16px; border-collapse: collapse; }
        .meta-table td { padding: 4px 6px; font-size: 10px; }
        .meta-label { font-weight: bold; color: #64748b; width: 22%; }
        .data-table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        .data-table th, .data-table td { border: 1px solid #cbd5e1; padding: 6px 8px; text-align: left; font-size: 10px; }
        .data-table th { background-color: #fef3c7; font-weight: bold; color: #92400e; }
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .sign-table { width: 100%; margin-top: 30px; border-collapse: collapse; }
        .sign-table td { width: 33.3%; text-align: center; vertical-align: bottom; height: 75px; font-size: 9px; border: 1px solid #cbd5e1; padding: 8px; }
    </style>
</head>
<body>
    <div class="header">
        <table style="width: 100%;">
            <tr>
                <td>
                    <div class="company-name">PT HERBATECH PHARMACEUTICALS</div>
                    <div style="font-size: 9px; color: #64748b;">Pengadaan Khusus / Bon Sementara Kas Pembelian Lokal</div>
                </td>
                <td style="text-align: right;">
                    <div class="doc-title">BERKAS BON PENGADAAN</div>
                    <div style="font-size: 10px; font-weight: bold; color: #d97706;">{{ $bon->bon_number }}</div>
                </td>
            </tr>
        </table>
    </div>

    <table class="meta-table">
        <tr>
            <td class="meta-label">Vendor / Toko Penyedia:</td>
            <td style="font-weight: bold;">{{ $bon->vendor_name }}</td>
            <td class="meta-label">Tanggal Berkas Bon:</td>
            <td>{{ $bon->bon_date?->format('d/m/Y') }}</td>
        </tr>
        <tr>
            <td class="meta-label">Dibuat Oleh:</td>
            <td>{{ $bon->creator?->name ?? 'Procurement' }}</td>
            <td class="meta-label">Status Persetujuan:</td>
            <td style="font-weight: bold; color: #d97706;">{{ strtoupper(str_replace('_', ' ', $bon->status)) }}</td>
        </tr>
        @if($bon->revision_note)
        <tr>
            <td class="meta-label" style="color: #dc2626;">Catatan Revisi:</td>
            <td colspan="3" style="color: #dc2626; font-size: 9px;">{{ $bon->revision_note }}</td>
        </tr>
        @endif
    </table>

    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 5%;">No</th>
                <th style="width: 25%;">Nomor PR Asal</th>
                <th style="width: 35%;">Nama Item & Keperluan</th>
                <th class="text-center" style="width: 10%;">Jumlah</th>
                <th class="text-center" style="width: 8%;">Satuan</th>
                <th class="text-right" style="width: 17%;">Nominal Bon (Rp)</th>
            </tr>
        </thead>
        <tbody>
            @foreach($bon->items as $idx => $item)
            <tr>
                <td class="text-center">{{ $idx + 1 }}</td>
                <td>{{ $item->purchaseRequest?->pr_number ?? '-' }}</td>
                <td>
                    <strong>{{ $item->item_name }}</strong>
                    @if($item->validation_notes)<br><span style="color: #64748b; font-size: 8px;">Note: {{ $item->validation_notes }}</span>@endif
                </td>
                <td class="text-center">{{ number_format($item->quantity, 0) }}</td>
                <td class="text-center">{{ $item->uom }}</td>
                <td class="text-right">{{ number_format($item->bon_unit_price ?: $item->actual_price, 0, ',', '.') }}</td>
            </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr style="font-weight: bold; background-color: #fffbeb;">
                <td colspan="5" class="text-right">TOTAL PENCAIRAN BON SEMENTARA:</td>
                <td class="text-right" style="color: #d97706; font-size: 11px;">
                    Rp {{ number_format($bon->total_amount, 0, ',', '.') }}
                </td>
            </tr>
        </tfoot>
    </table>

    <table class="sign-table">
        <tr>
            <td>
                Pemohon Bon (Procurement)<br><br><br>
                <strong>{{ $bon->creator?->name ?? 'Buyer Bon' }}</strong><br>
                Staff Procurement
            </td>
            <td>
                Verifikasi Kas / Finance<br><br><br>
                <strong>Finance & Kasir</strong><br>
                Finance & Accounting
            </td>
            <td>
                Disetujui (General Manager)<br><br><br>
                <strong>{{ $bon->approver?->name ?? 'General Manager' }}</strong><br>
                Pimpinan Eksekutif
            </td>
        </tr>
    </table>
</body>
</html>
