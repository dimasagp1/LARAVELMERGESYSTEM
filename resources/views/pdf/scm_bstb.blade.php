<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Bukti Serah Terima Bahan - {{ $usage->usage_number }}</title>
    <style>
        body { font-family: Helvetica, Arial, sans-serif; font-size: 11px; color: #1e293b; line-height: 1.4; margin: 20px; }
        .header { border-bottom: 2px solid #0284c7; padding-bottom: 12px; margin-bottom: 16px; }
        .company-name { font-size: 18px; font-weight: bold; color: #0284c7; text-transform: uppercase; }
        .doc-title { font-size: 14px; font-weight: bold; margin-top: 4px; color: #0f172a; }
        .meta-table { width: 100%; margin-bottom: 16px; border-collapse: collapse; }
        .meta-table td { padding: 4px 6px; font-size: 10px; }
        .meta-label { font-weight: bold; color: #64748b; width: 22%; }
        .data-table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        .data-table th, .data-table td { border: 1px solid #cbd5e1; padding: 6px 8px; text-align: left; font-size: 10px; }
        .data-table th { background-color: #f1f5f9; font-weight: bold; color: #334155; }
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
                    <div class="company-name">PT HERBATECH INNOPHARMA INDUSTRY</div>
                    <div style="font-size: 9px; color: #64748b;">LogiFlow SCM — Penyerahan Bahan Baku & Kemas ke Lantai Produksi</div>
                </td>
                <td style="text-align: right;">
                    <div class="doc-title">BUKTI SERAH TERIMA BAHAN (BSTB)</div>
                    <div style="font-size: 11px; font-weight: bold; color: #0284c7;">{{ $usage->usage_number }}</div>
                </td>
            </tr>
        </table>
    </div>

    <table class="meta-table">
        <tr>
            <td class="meta-label">No. SPK / Work Order:</td>
            <td style="font-weight: bold; font-size: 11px;">{{ $usage->work_order_number }}</td>
            <td class="meta-label">Tanggal Penyerahan:</td>
            <td>{{ \Carbon\Carbon::parse($usage->usage_date)->translatedFormat('d F Y') }}</td>
        </tr>
        <tr>
            <td class="meta-label">Pabrik / Fasilitas:</td>
            <td>{{ $usage->plant?->name ?? 'Pabrik Sukoharjo' }}</td>
            <td class="meta-label">Departemen Penerima:</td>
            <td>{{ $usage->department?->name ?? 'Produksi Manufaktur' }}</td>
        </tr>
        <tr>
            <td class="meta-label">Diserahkan Oleh:</td>
            <td>{{ $usage->issuer?->name ?? 'Petugas Gudang' }}</td>
            <td class="meta-label">Diterima Oleh:</td>
            <td>{{ $usage->recipient?->name ?? 'Operator Produksi' }}</td>
        </tr>
        @if($usage->notes)
        <tr>
            <td class="meta-label">Catatan Tambahan:</td>
            <td colspan="3" style="color: #475569;">{{ $usage->notes }}</td>
        </tr>
        @endif
    </table>

    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 5%;" class="text-center">No</th>
                <th style="width: 40%;">Deskripsi Bahan Material</th>
                <th style="width: 15%;">No. Batch Asal</th>
                <th style="width: 12%;" class="text-right">Kuantitas</th>
                <th style="width: 10%;" class="text-center">Satuan</th>
                <th style="width: 18%;" class="text-right">Estimasi Biaya (IDR)</th>
            </tr>
        </thead>
        <tbody>
            @php $totalEstimatedCost = 0; @endphp
            @foreach($usage->items as $index => $item)
                @php 
                    $subCost = $item->quantity * $item->unit_cost; 
                    $totalEstimatedCost += $subCost;
                @endphp
                <tr>
                    <td class="text-center">{{ $index + 1 }}</td>
                    <td>
                        <strong>{{ $item->product?->code ?? '-' }}</strong> - {{ $item->product?->name ?? 'Material' }}
                    </td>
                    <td>{{ $item->batch_number ?? '-' }}</td>
                    <td class="text-right">{{ number_format($item->quantity, 2, ',', '.') }}</td>
                    <td class="text-center">{{ $item->uom }}</td>
                    <td class="text-right">Rp {{ number_format($subCost, 0, ',', '.') }}</td>
                </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr style="background-color: #f8fafc; font-weight: bold;">
                <td colspan="3" class="text-right">Total Penyerahan Material:</td>
                <td class="text-right">{{ number_format($usage->items->sum('quantity'), 2, ',', '.') }}</td>
                <td></td>
                <td class="text-right">Rp {{ number_format($totalEstimatedCost, 0, ',', '.') }}</td>
            </tr>
        </tfoot>
    </table>

    <div style="font-size: 9px; color: #64748b; margin-top: 5px;">
        * Pengeluaran material ini telah memotong stok gudang secara otomatis dan mengunci alokasi bahan pada Work Order terkait.
    </div>

    <table class="sign-table">
        <tr>
            <td>
                Diserahkan Oleh (Gudang):<br><br><br><br>
                <strong>( {{ $usage->issuer?->name ?? 'Staff Gudang' }} )</strong>
            </td>
            <td>
                Diterima Oleh (Lantai Produksi):<br><br><br><br>
                <strong>( {{ $usage->recipient?->name ?? 'Operator / Spv Produksi' }} )</strong>
            </td>
            <td>
                Diketahui Oleh (PPIC):<br><br><br><br>
                <strong>( PPIC Planner )</strong>
            </td>
        </tr>
    </table>
</body>
</html>
