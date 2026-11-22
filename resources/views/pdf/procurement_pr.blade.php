<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Purchase Request - {{ $pr->pr_number }}</title>
    <style>
        body { font-family: Helvetica, Arial, sans-serif; font-size: 11px; color: #1e293b; line-height: 1.4; margin: 20px; }
        .header { border-bottom: 2px solid #7c3aed; padding-bottom: 12px; margin-bottom: 16px; }
        .company-name { font-size: 18px; font-weight: bold; color: #7c3aed; text-transform: uppercase; }
        .doc-title { font-size: 14px; font-weight: bold; margin-top: 4px; color: #0f172a; }
        .meta-table { width: 100%; margin-bottom: 16px; border-collapse: collapse; }
        .meta-table td { padding: 4px 6px; font-size: 10px; }
        .meta-label { font-weight: bold; color: #64748b; width: 22%; }
        .data-table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        .data-table th, .data-table td { border: 1px solid #cbd5e1; padding: 6px 8px; text-align: left; font-size: 10px; }
        .data-table th { background-color: #f1f5f9; font-weight: bold; color: #334155; }
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .badge { display: inline-block; padding: 2px 6px; border-radius: 4px; font-size: 9px; font-weight: bold; }
        .badge-op { background-color: #ede9fe; color: #6d28d9; }
        .badge-non { background-color: #e0f2fe; color: #0369a1; }
        .sign-table { width: 100%; margin-top: 30px; border-collapse: collapse; }
        .sign-table td { width: 25%; text-align: center; vertical-align: bottom; height: 75px; font-size: 9px; border: 1px solid #cbd5e1; padding: 8px; }
    </style>
</head>
<body>
    <div class="header">
        <table style="width: 100%;">
            <tr>
                <td>
                    <div class="company-name">PT HERBATECH PHARMACEUTICALS</div>
                    <div style="font-size: 9px; color: #64748b;">Kawasan Industri Herbal Cikarang, Jawa Barat - Indonesia</div>
                </td>
                <td style="text-align: right;">
                    <div class="doc-title">PURCHASE REQUEST</div>
                    <div style="font-size: 10px; font-weight: bold; color: #7c3aed;">{{ $pr->pr_number }}</div>
                </td>
            </tr>
        </table>
    </div>

    <table class="meta-table">
        <tr>
            <td class="meta-label">Departemen:</td>
            <td>{{ $pr->department?->name ?? '-' }}</td>
            <td class="meta-label">Tanggal Pengajuan:</td>
            <td>{{ $pr->request_date?->format('d/m/Y') }}</td>
        </tr>
        <tr>
            <td class="meta-label">Diajukan Oleh:</td>
            <td>{{ $pr->user?->name ?? '-' }}</td>
            <td class="meta-label">Tipe Pengadaan:</td>
            <td>
                <span class="badge {{ $pr->pr_type === 'operational' ? 'badge-op' : 'badge-non' }}">
                    {{ strtoupper(str_replace('_', ' ', $pr->pr_type)) }}
                </span>
            </td>
        </tr>
        <tr>
            <td class="meta-label">Tujuan Belanja (Purpose):</td>
            <td>{{ $pr->purpose }}</td>
            <td class="meta-label">Status Dokumen:</td>
            <td style="font-weight: bold;">{{ strtoupper($pr->status) }}</td>
        </tr>
        @if($pr->bypass_budget_check)
        <tr>
            <td class="meta-label" style="color: #dc2626;">Budget Bypass:</td>
            <td colspan="3" style="color: #dc2626; font-size: 9px;">YA - Alasan: {{ $pr->bypass_budget_reason }}</td>
        </tr>
        @endif
    </table>

    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 5%;">No</th>
                <th style="width: 35%;">Nama Item & Spesifikasi</th>
                <th class="text-center" style="width: 10%;">Jumlah</th>
                <th class="text-center" style="width: 8%;">Satuan</th>
                <th class="text-right" style="width: 18%;">Estimasi (Rp)</th>
                <th class="text-right" style="width: 18%;">Harga Aktual (Rp)</th>
                <th class="text-center" style="width: 6%;">Bon?</th>
            </tr>
        </thead>
        <tbody>
            @foreach($pr->items as $idx => $item)
            <tr>
                <td class="text-center">{{ $idx + 1 }}</td>
                <td>
                    <strong>{{ $item->item_name }}</strong>
                    @if($item->item_code) <br><span style="color: #64748b; font-size: 8px;">Kode: {{ $item->item_code }}</span> @endif
                    @if($item->po_number) <br><span style="color: #7c3aed; font-size: 8px;">PO: {{ $item->po_number }}</span> @endif
                </td>
                <td class="text-center">{{ number_format($item->quantity, 0) }}</td>
                <td class="text-center">{{ $item->uom }}</td>
                <td class="text-right">{{ number_format($item->estimated_price, 0, ',', '.') }}</td>
                <td class="text-right">{{ number_format($item->actual_price ?: $item->estimated_price, 0, ',', '.') }}</td>
                <td class="text-center">{{ $item->is_bon ? 'Ya' : '-' }}</td>
            </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr style="font-weight: bold; background-color: #f8fafc;">
                <td colspan="5" class="text-right">TOTAL ESTIMASI / REALISASI PENGADAAN:</td>
                <td colspan="2" class="text-right" style="color: #7c3aed; font-size: 11px;">
                    Rp {{ number_format($pr->total_amount, 0, ',', '.') }}
                </td>
            </tr>
        </tfoot>
    </table>

    <table class="sign-table">
        <tr>
            <td>
                Diajukan Oleh (Requester)<br><br><br>
                <strong>{{ $pr->user?->name ?? 'Requester' }}</strong><br>
                Staff Departemen
            </td>
            <td>
                Diperiksa (Procurement)<br><br><br>
                <strong>Procurement Buyer</strong><br>
                Tim Pengadaan
            </td>
            <td>
                Disetujui L1 (OM / FAT)<br><br><br>
                <strong>Operational / FAT Manager</strong><br>
                Head of Division
            </td>
            <td>
                Disetujui L2 (General Manager)<br><br><br>
                <strong>General Manager</strong><br>
                Executive Board
            </td>
        </tr>
    </table>
</body>
</html>
