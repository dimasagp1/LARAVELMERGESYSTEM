<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Monitoring Budget vs Actual P&L - {{ $period['name'] }}</title>
    <style>
        body { font-family: Helvetica, Arial, sans-serif; font-size: 10px; color: #1e293b; line-height: 1.4; margin: 15px; }
        .header { border-bottom: 2px solid #059669; padding-bottom: 10px; margin-bottom: 15px; }
        .company-name { font-size: 16px; font-weight: bold; color: #059669; text-transform: uppercase; }
        .doc-title { font-size: 13px; font-weight: bold; margin-top: 3px; color: #0f172a; }
        .data-table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        .data-table th, .data-table td { border: 1px solid #cbd5e1; padding: 5px 8px; font-size: 9.5px; }
        .data-table th { background-color: #ecfdf5; color: #065f46; font-weight: bold; }
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .badge-fav { color: #16a34a; font-weight: bold; }
        .badge-unfav { color: #dc2626; font-weight: bold; }
        .sign-table { width: 100%; margin-top: 25px; border-collapse: collapse; }
        .sign-table td { width: 33.3%; text-align: center; vertical-align: bottom; height: 70px; font-size: 9px; border: 1px solid #cbd5e1; padding: 6px; }
    </style>
</head>
<body>
    <div class="header">
        <table style="width: 100%;">
            <tr>
                <td>
                    <div class="company-name">PT HERBATECH INNOPHARMA INDUSTRY</div>
                    <div style="font-size: 8.5px; color: #64748b;">Evaluasi Kinerja Finansial &bull; Budget vs Actual P&L</div>
                </td>
                <td style="text-align: right;">
                    <div class="doc-title">MONITORING BUDGET VS ACTUAL P&L</div>
                    <div style="font-size: 9.5px; font-weight: bold; color: #059669;">Periode: {{ $period['name'] }}</div>
                </td>
            </tr>
        </table>
    </div>

    <!-- Ringkasan P&L Komparatif -->
    <h3 style="font-size: 11px; color: #065f46; margin-bottom: 5px; text-transform: uppercase;">
        Ringkasan Komparasi Laba Rugi Berdampingan
    </h3>
    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 35%;">Pos Laba Rugi</th>
                <th class="text-right" style="width: 20%;">Target Anggaran (Rp)</th>
                <th class="text-right" style="width: 20%;">Realisasi Aktual (Rp)</th>
                <th class="text-right" style="width: 15%;">Varians (Rp)</th>
                <th class="text-center" style="width: 10%;">Capaian (%)</th>
            </tr>
        </thead>
        <tbody>
            @foreach($budgetVsActual['pnl_summary'] as $label => $row)
            <tr>
                <td style="text-transform: capitalize;"><strong>{{ str_replace('_', ' ', $label) }}</strong></td>
                <td class="text-right">{{ number_format($row['budget'], 0, ',', '.') }}</td>
                <td class="text-right font-bold">{{ number_format($row['actual'], 0, ',', '.') }}</td>
                <td class="text-right {{ $row['is_favorable'] ? 'badge-fav' : 'badge-unfav' }}">
                    {{ number_format($row['variance_rp'], 0, ',', '.') }}
                </td>
                <td class="text-center font-bold {{ $row['is_favorable'] ? 'badge-fav' : 'badge-unfav' }}">
                    {{ $row['variance_pct'] }}%
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <!-- Beban Operasional 11 Departemen -->
    <h3 style="font-size: 11px; color: #065f46; margin-bottom: 5px; text-transform: uppercase; margin-top: 15px;">
        Alokasi Beban Operasional (OPEX) per 11 Departemen
    </h3>
    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 5%;">No</th>
                <th style="width: 35%;">Nama Departemen</th>
                <th class="text-right" style="width: 20%;">Pagu Budget (Rp)</th>
                <th class="text-right" style="width: 20%;">Realisasi Beban (Rp)</th>
                <th class="text-center" style="width: 10%;">Burn Rate (%)</th>
                <th class="text-center" style="width: 10%;">Status</th>
            </tr>
        </thead>
        <tbody>
            @foreach($budgetVsActual['department_opex'] as $idx => $d)
            <tr>
                <td class="text-center">{{ $idx + 1 }}</td>
                <td><strong>{{ $d['department_name'] }}</strong></td>
                <td class="text-right">{{ number_format($d['budget'], 0, ',', '.') }}</td>
                <td class="text-right font-bold">{{ number_format($d['actual'], 0, ',', '.') }}</td>
                <td class="text-center {{ $d['is_overbudget'] ? 'badge-unfav' : 'badge-fav' }}">
                    {{ $d['burn_rate_pct'] }}%
                </td>
                <td class="text-center">
                    <span class="{{ $d['is_overbudget'] ? 'badge-unfav' : 'badge-fav' }}">
                        {{ $d['is_overbudget'] ? 'OVERBUDGET' : 'AMAN' }}
                    </span>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <table class="sign-table">
        <tr>
            <td>
                Disiapkan Oleh<br><br><br>
                <strong>Budgeting & Planning Analyst</strong><br>
                Tim Anggaran FAT
            </td>
            <td>
                Diperiksa Oleh<br><br><br>
                <strong>Finance & Accounting Manager</strong><br>
                Head of FAT
            </td>
            <td>
                Disetujui Oleh<br><br><br>
                <strong>General Manager</strong><br>
                Executive Board
            </td>
        </tr>
    </table>
</body>
</html>
