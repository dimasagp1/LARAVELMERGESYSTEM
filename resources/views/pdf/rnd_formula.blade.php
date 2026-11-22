<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Formulasi R&D - {{ $formula->code }}</title>
    <style>
        body {
            font-family: Arial, Helvetica, sans-serif;
            font-size: 11px;
            color: #1e293b;
            line-height: 1.4;
        }
        .header {
            border-bottom: 2px solid #0891b2;
            padding-bottom: 10px;
            margin-bottom: 15px;
        }
        .company-name {
            font-size: 16px;
            font-weight: bold;
            color: #0e7490;
        }
        .title {
            font-size: 14px;
            font-weight: bold;
            text-transform: uppercase;
            margin-top: 5px;
        }
        .badge {
            display: inline-block;
            padding: 3px 8px;
            border-radius: 4px;
            font-size: 10px;
            font-weight: bold;
            text-transform: uppercase;
            background: #cffafe;
            color: #0e7490;
            border: 1px solid #a5f3fc;
        }
        table.meta {
            width: 100%;
            margin-bottom: 15px;
            border-collapse: collapse;
        }
        table.meta td {
            padding: 4px 6px;
            vertical-align: top;
        }
        table.data {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
        }
        table.data th, table.data td {
            border: 1px solid #cbd5e1;
            padding: 6px 8px;
            text-align: left;
        }
        table.data th {
            background-color: #f1f5f9;
            font-weight: bold;
            color: #334155;
            text-transform: uppercase;
            font-size: 10px;
        }
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .font-mono { font-family: Courier, monospace; }
        .signatures {
            margin-top: 30px;
            width: 100%;
            border-collapse: collapse;
        }
        .signatures td {
            width: 33.33%;
            text-align: center;
            vertical-align: top;
            padding: 10px;
        }
        .sig-box {
            height: 60px;
            margin: 10px 0;
            border-bottom: 1px dashed #94a3b8;
        }
        .watermark {
            position: absolute;
            top: 40%;
            left: 20%;
            font-size: 55px;
            font-weight: bold;
            color: rgba(14, 116, 144, 0.08);
            transform: rotate(-35deg);
            text-transform: uppercase;
        }
    </style>
</head>
<body>
    @if($formula->status === 'approved')
        <div class="watermark">FORMULA RESMI TERKUNCI</div>
    @endif

    <div class="header">
        <table style="width: 100%;">
            <tr>
                <td>
                    <div class="company-name">PT HERBATECH INNOPHARMA INDUSTRY</div>
                    <div style="font-size: 10px; color: #64748b;">Plant Cikarang &bull; Research & Development Division</div>
                    <div class="title">LEMBAR FORMULASI RESEP RAW MATERIAL (R&D)</div>
                </td>
                <td class="text-right" style="vertical-align: top;">
                    <div class="badge">{{ $formula->status }}</div>
                    <div style="font-size: 9px; color: #64748b; margin-top: 4px;">Doc: {{ $formula->code }}</div>
                </td>
            </tr>
        </table>
    </div>

    <table class="meta">
        <tr>
            <td style="width: 20%; font-weight: bold;">Kode Formula</td>
            <td style="width: 30%;" class="font-mono">{{ $formula->code }}</td>
            <td style="width: 20%; font-weight: bold;">Bentuk Sediaan</td>
            <td style="width: 30%;">{{ ucfirst($formula->serving_unit) }}</td>
        </tr>
        <tr>
            <td style="font-weight: bold;">Nama Produk</td>
            <td style="font-weight: bold; color: #0e7490;">{{ $formula->product?->name }}</td>
            <td style="font-weight: bold;">Ukuran Takaran (Serving)</td>
            <td>{{ number_format((float) $formula->target_serving_size_g, 2) }} gram</td>
        </tr>
        <tr>
            <td style="font-weight: bold;">Versi Formula</td>
            <td>Versi {{ $formula->version }} @if($formula->parent_formula_id) (Turunan dari #{{ $formula->parent_formula_id }}) @endif</td>
            <td style="font-weight: bold;">Jumlah per Kemasan</td>
            <td>{{ $formula->servings_per_pack }} {{ $formula->serving_unit }} / pack</td>
        </tr>
        <tr>
            <td style="font-weight: bold;">Tahap Riset</td>
            <td>{{ strtoupper(str_replace('_', ' ', $formula->stage)) }}</td>
            <td style="font-weight: bold;">Estimasi HPP RM / Pack</td>
            <td style="font-weight: bold; color: #059669;">Rp {{ number_format((float) $formula->total_cost_per_pack, 0, ',', '.') }}</td>
        </tr>
    </table>

    <table class="data">
        <thead>
            <tr>
                <th style="width: 5%;" class="text-center">No</th>
                <th style="width: 35%;">Nama Bahan Baku (Raw Material)</th>
                <th style="width: 20%;">Pemasok / Supplier</th>
                <th style="width: 10%;" class="text-right">Rasio (%)</th>
                <th style="width: 15%;" class="text-right">Dosis (g)</th>
                <th style="width: 15%;" class="text-right">HPP / Serving</th>
            </tr>
        </thead>
        <tbody>
            @foreach($formula->items as $idx => $item)
            <tr>
                <td class="text-center">{{ $idx + 1 }}</td>
                <td>
                    <strong>{{ $item->material?->name }}</strong>
                    <div style="font-size: 9px; color: #64748b;">SKU: {{ $item->material?->sku }}</div>
                </td>
                <td>{{ $item->vendor?->name ?? 'Acuan Katalog R&D' }}</td>
                <td class="text-right font-mono">{{ number_format((float) $item->percentage, 2) }}%</td>
                <td class="text-right font-mono">{{ number_format((float) $item->weight_per_serving_g, 4) }} g</td>
                <td class="text-right font-mono">Rp {{ number_format((float) $item->price_per_serving, 2, ',', '.') }}</td>
            </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr style="background: #f8fafc; font-weight: bold;">
                <td colspan="3" class="text-right">TOTAL KOMPOSISI (WAJIB 100.00%):</td>
                <td class="text-right font-mono" style="color: #059669;">{{ number_format((float) $formula->total_percentage, 2) }}%</td>
                <td class="text-right font-mono">{{ number_format((float) $formula->target_serving_size_g, 2) }} g</td>
                <td class="text-right font-mono" style="color: #059669;">
                    Rp {{ number_format((float) ($formula->total_cost_per_pack / ($formula->servings_per_pack ?: 1)), 2, ',', '.') }}
                </td>
            </tr>
        </tfoot>
    </table>

    @if($formula->notes)
    <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 4px; padding: 8px; margin-bottom: 15px;">
        <strong style="font-size: 10px;">Catatan Formulator:</strong>
        <p style="margin: 3px 0 0 0; font-size: 10px; color: #475569;">{{ $formula->notes }}</p>
    </div>
    @endif

    <table class="signatures">
        <tr>
            <td>
                <div>Disusun Oleh,</div>
                <div style="font-size: 9px; color: #64748b;">Formulator R&D</div>
                <div class="sig-box">
                    <br><span style="color: #0e7490; font-weight: bold;">DIGITALLY SIGNED</span>
                </div>
                <strong>{{ $formula->creator?->name ?? 'Staff Formulator' }}</strong>
            </td>
            <td>
                <div>Diperiksa Oleh (Tahap 1),</div>
                <div style="font-size: 9px; color: #64748b;">Operational Manager</div>
                <div class="sig-box">
                    @if($formula->approved_by_om)
                        <br><span style="color: #059669; font-weight: bold;">APPROVED TAHAP 1</span>
                    @else
                        <br><span style="color: #94a3b8;">Pending Review</span>
                    @endif
                </div>
                <strong>{{ $formula->omApprover?->name ?? 'Operational Manager' }}</strong>
            </td>
            <td>
                <div>Disahkan Oleh (Tahap 2 Final),</div>
                <div style="font-size: 9px; color: #64748b;">General Manager</div>
                <div class="sig-box">
                    @if($formula->approved_by_gm)
                        <br><span style="color: #059669; font-weight: bold;">APPROVED & LOCKED</span>
                    @else
                        <br><span style="color: #94a3b8;">Pending Approval</span>
                    @endif
                </div>
                <strong>{{ $formula->gmApprover?->name ?? 'General Manager' }}</strong>
            </td>
        </tr>
    </table>
</body>
</html>
