<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Goods Receipt Note - {{ $receipt->grn_number }}</title>
    <style>
        body { font-family: Helvetica, Arial, sans-serif; font-size: 11px; color: #1e293b; line-height: 1.4; margin: 20px; }
        .header { border-bottom: 2px solid #0d9488; padding-bottom: 12px; margin-bottom: 16px; }
        .company-name { font-size: 18px; font-weight: bold; color: #0d9488; text-transform: uppercase; }
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
        .badge-qrn { background-color: #fef3c7; color: #b45309; }
        .badge-pass { background-color: #dcfce7; color: #15803d; }
        .sign-table { width: 100%; margin-top: 30px; border-collapse: collapse; }
        .sign-table td { width: 33.3%; text-align: center; vertical-align: bottom; height: 75px; font-size: 9px; border: 1px solid #cbd5e1; padding: 8px; }
    </style>
</head>
<body>
    <div class="header">
        <table style="width: 100%;">
            <tr>
                <td>
                    <div class="company-name">PT HERBATECH INNOPHARMA INDUSTRY</div>
                    <div style="font-size: 9px; color: #64748b;">LogiFlow SCM — Divisi Pergudangan & Logistik Pabrik Farmasi & Herbal</div>
                </td>
                <td style="text-align: right;">
                    <div class="doc-title">BUKTI PENERIMAAN BARANG (GRN)</div>
                    <div style="font-size: 11px; font-weight: bold; color: #0d9488;">{{ $receipt->grn_number }}</div>
                </td>
            </tr>
        </table>
    </div>

    <table class="meta-table">
        <tr>
            <td class="meta-label">Nomor PO Terkait:</td>
            <td style="font-weight: bold;">{{ $receipt->po_number ?? 'PO Tanpa Kontrak' }}</td>
            <td class="meta-label">Tanggal Penerimaan:</td>
            <td>{{ \Carbon\Carbon::parse($receipt->receipt_date)->translatedFormat('d F Y') }}</td>
        </tr>
        <tr>
            <td class="meta-label">Nama Vendor / Pemasok:</td>
            <td style="font-weight: bold;">{{ $receipt->vendor?->name ?? '-' }}</td>
            <td class="meta-label">No. Surat Jalan (DO):</td>
            <td>{{ $receipt->delivery_order_number }}</td>
        </tr>
        <tr>
            <td class="meta-label">Pabrik & Gudang Tujuan:</td>
            <td>{{ $receipt->plant?->name ?? 'Pabrik Sukoharjo' }} (Gudang Karantina QC)</td>
            <td class="meta-label">Diterima Oleh:</td>
            <td>{{ $receipt->receiver?->name ?? 'Petugas Gudang' }}</td>
        </tr>
        <tr>
            <td class="meta-label">Status Karantina/Inspeksi:</td>
            <td colspan="3">
                <span class="badge {{ $receipt->status === 'completed' ? 'badge-pass' : 'badge-qrn' }}">
                    {{ strtoupper($receipt->status) }}
                </span>
                @if($receipt->notes)
                    <span style="margin-left: 10px; color: #64748b; font-style: italic;">Catatan: {{ $receipt->notes }}</span>
                @endif
            </td>
        </tr>
    </table>

    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 5%;" class="text-center">No</th>
                <th style="width: 35%;">Nama Item Material / Bahan</th>
                <th style="width: 15%;">No. Batch Vendor</th>
                <th style="width: 12%;">Tgl Kedaluwarsa</th>
                <th style="width: 11%;" class="text-right">Qty Tiba</th>
                <th style="width: 11%;" class="text-right">Qty Lulus QC</th>
                <th style="width: 11%;" class="text-center">Satuan</th>
            </tr>
        </thead>
        <tbody>
            @foreach($receipt->items as $index => $item)
                <tr>
                    <td class="text-center">{{ $index + 1 }}</td>
                    <td>
                        <strong>{{ $item->product?->code ?? '-' }}</strong> - {{ $item->item_name }}
                    </td>
                    <td>{{ $item->batch_number ?? '-' }}</td>
                    <td>{{ $item->expiry_date ? \Carbon\Carbon::parse($item->expiry_date)->format('d/m/Y') : '-' }}</td>
                    <td class="text-right">{{ number_format($item->received_qty, 2, ',', '.') }}</td>
                    <td class="text-right">{{ number_format($item->passed_qty, 2, ',', '.') }}</td>
                    <td class="text-center">{{ $item->uom }}</td>
                </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr style="background-color: #f8fafc; font-weight: bold;">
                <td colspan="4" class="text-right">Total Kuantitas Tiba:</td>
                <td class="text-right">{{ number_format($receipt->items->sum('received_qty'), 2, ',', '.') }}</td>
                <td class="text-right">{{ number_format($receipt->items->sum('passed_qty'), 2, ',', '.') }}</td>
                <td></td>
            </tr>
        </tfoot>
    </table>

    <div style="font-size: 9px; color: #64748b; margin-top: 5px;">
        * Sesuai regulasi CPOTB/cGMP, seluruh material yang baru tiba wajib melalui 4 parameter pengujian QC (Visual, Lab, Kuantitas, Dokumen CoA) sebelum dialihkan ke stok aktif gudang utama.
    </div>

    <table class="sign-table">
        <tr>
            <td>
                Diterima Oleh (Gudang):<br><br><br><br>
                <strong>( {{ $receipt->receiver?->name ?? 'Staff Inbound Gudang' }} )</strong>
            </td>
            <td>
                Diperiksa Oleh (QC Gatekeeper):<br><br><br><br>
                <strong>( Quality Control Inspector )</strong>
            </td>
            <td>
                Disetujui Oleh (Ka. Gudang):<br><br><br><br>
                <strong>( Warehouse Supervisor )</strong>
            </td>
        </tr>
    </table>
</body>
</html>
