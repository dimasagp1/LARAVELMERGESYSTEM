<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Material Return Delivery Note - {{ $return->return_number }}</title>
    <style>
        body { font-family: Helvetica, Arial, sans-serif; font-size: 11px; color: #1e293b; line-height: 1.4; margin: 20px; }
        .header { border-bottom: 2px solid #ef4444; padding-bottom: 12px; margin-bottom: 16px; }
        .company-name { font-size: 18px; font-weight: bold; color: #ef4444; text-transform: uppercase; }
        .doc-title { font-size: 14px; font-weight: bold; margin-top: 4px; color: #0f172a; }
        .meta-table { width: 100%; margin-bottom: 16px; border-collapse: collapse; }
        .meta-table td { padding: 4px 6px; font-size: 10px; }
        .meta-label { font-weight: bold; color: #64748b; width: 22%; }
        .data-table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        .data-table th, .data-table td { border: 1px solid #cbd5e1; padding: 6px 8px; text-align: left; font-size: 10px; }
        .data-table th { background-color: #fef2f2; font-weight: bold; color: #991b1b; }
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .badge { display: inline-block; padding: 2px 6px; border-radius: 4px; font-size: 9px; font-weight: bold; background-color: #fee2e2; color: #991b1b; }
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
                    <div style="font-size: 9px; color: #64748b;">LogiFlow SCM — Memo Pengembalian Material Cacat / Ditolak QC ke Vendor</div>
                </td>
                <td style="text-align: right;">
                    <div class="doc-title">SURAT JALAN RETUR BARANG</div>
                    <div style="font-size: 11px; font-weight: bold; color: #ef4444;">{{ $return->return_number }}</div>
                </td>
            </tr>
        </table>
    </div>

    <table class="meta-table">
        <tr>
            <td class="meta-label">Vendor / Rekanan:</td>
            <td style="font-weight: bold; font-size: 11px;">{{ $return->vendor?->name ?? '-' }}</td>
            <td class="meta-label">Tanggal Retur:</td>
            <td>{{ \Carbon\Carbon::parse($return->return_date)->translatedFormat('d F Y') }}</td>
        </tr>
        <tr>
            <td class="meta-label">Ref. Penerimaan (GRN):</td>
            <td>{{ $return->goodsReceipt?->grn_number ?? 'Inbound GRN' }} (PO: {{ $return->goodsReceipt?->po_number ?? '-' }})</td>
            <td class="meta-label">Status Otorisasi:</td>
            <td><span class="badge">{{ strtoupper($return->status) }}</span></td>
        </tr>
        <tr>
            <td class="meta-label">Alasan Penolakan:</td>
            <td colspan="3" style="color: #b91c1c; font-weight: 500;">{{ $return->reason }}</td>
        </tr>
    </table>

    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 5%;" class="text-center">No</th>
                <th style="width: 35%;">Deskripsi Material / Produk</th>
                <th style="width: 15%;">No. Batch Vendor</th>
                <th style="width: 12%;" class="text-right">Qty Retur</th>
                <th style="width: 8%;" class="text-center">Satuan</th>
                <th style="width: 25%;">Alasan Cacat / Deviasi</th>
            </tr>
        </thead>
        <tbody>
            @foreach($return->items as $index => $item)
                <tr>
                    <td class="text-center">{{ $index + 1 }}</td>
                    <td>
                        <strong>{{ $item->product?->code ?? '-' }}</strong> - {{ $item->product?->name ?? 'Material' }}
                    </td>
                    <td>{{ $item->batch_number ?? '-' }}</td>
                    <td class="text-right" style="font-weight: bold; color: #b91c1c;">{{ number_format($item->quantity, 2, ',', '.') }}</td>
                    <td class="text-center">{{ $item->uom }}</td>
                    <td style="font-size: 9px; color: #64748b;">{{ $item->reason ?? 'Tidak lolos spesifikasi mutu CPOTB' }}</td>
                </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr style="background-color: #fef2f2; font-weight: bold;">
                <td colspan="3" class="text-right">Total Kuantitas Dikembalikan:</td>
                <td class="text-right" style="color: #b91c1c;">{{ number_format($return->items->sum('quantity'), 2, ',', '.') }}</td>
                <td colspan="2"></td>
            </tr>
        </tfoot>
    </table>

    <div style="font-size: 9px; color: #64748b; margin-top: 5px;">
        * Dokumen ini sah sebagai tanda pengembalian fisik barang cacat/rejek kepada rekanan. Vendor wajib menerbitkan Nota Kredit (*Credit Note*) atau mengirimkan penggantian barang (*Replacement Lot*) sesuai perjanjian.
    </div>

    <table class="sign-table">
        <tr>
            <td>
                Diverifikasi Oleh (QC):<br><br><br><br>
                <strong>( Quality Control Inspector )</strong>
            </td>
            <td>
                Disetujui Oleh (Ka. Gudang):<br><br><br><br>
                <strong>( {{ $return->creator?->name ?? 'Head of Warehouse' }} )</strong>
            </td>
            <td>
                Diterima Oleh (Ekspedisi/Vendor):<br><br><br><br>
                <strong>( Driver / Perwakilan Vendor )</strong>
            </td>
        </tr>
    </table>
</body>
</html>
