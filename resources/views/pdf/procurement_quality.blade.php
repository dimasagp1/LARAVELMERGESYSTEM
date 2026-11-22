<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan Sasaran Mutu Procurement {{ $fiscalYear }}</title>
    <style>
        body { font-family: Helvetica, Arial, sans-serif; font-size: 11px; color: #1e293b; line-height: 1.4; margin: 20px; }
        .header { border-bottom: 2px solid #0284c7; padding-bottom: 12px; margin-bottom: 16px; }
        .company-name { font-size: 16px; font-weight: bold; color: #0284c7; text-transform: uppercase; }
        .doc-title { font-size: 14px; font-weight: bold; margin-top: 4px; color: #0f172a; }
        .data-table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        .data-table th, .data-table td { border: 1px solid #cbd5e1; padding: 6px 8px; text-align: left; font-size: 10px; }
        .data-table th { background-color: #f0f9ff; font-weight: bold; color: #0369a1; }
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
                    <div style="font-size: 9px; color: #64748b;">Departemen Pengadaan & Supply Chain - ISO 9001:2015</div>
                </td>
                <td style="text-align: right;">
                    <div class="doc-title">EVALUASI SASARAN MUTU PROCUREMENT</div>
                    <div style="font-size: 10px; font-weight: bold; color: #0284c7;">TAHUN ANGGARAN {{ $fiscalYear }}</div>
                </td>
            </tr>
        </table>
    </div>

    <table style="width: 100%; margin-bottom: 15px; border-collapse: collapse;">
        <tr>
            <td style="width: 25%; font-weight: bold; font-size: 10px;">Skor Komposit Mutu:</td>
            <td style="font-size: 14px; font-weight: bold; color: #0284c7;">{{ $summary['composite_score'] }} / 100</td>
            <td style="width: 25%; font-weight: bold; font-size: 10px;">Predikat Pencapaian:</td>
            <td style="font-size: 12px; font-weight: bold; color: #16a34a;">{{ $summary['grade'] }}</td>
        </tr>
    </table>

    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 5%;">No</th>
                <th style="width: 35%;">Indikator Sasaran Mutu 2026</th>
                <th class="text-center" style="width: 10%;">Bobot</th>
                <th style="width: 22%;">Target Standar</th>
                <th style="width: 18%;">Realisasi Aktual</th>
                <th class="text-center" style="width: 10%;">Skor</th>
            </tr>
        </thead>
        <tbody>
            @foreach($summary['kpis'] as $kpi)
            <tr>
                <td class="text-center">{{ $kpi['id'] }}</td>
                <td><strong>{{ $kpi['title'] }}</strong></td>
                <td class="text-center">{{ $kpi['weight'] }}%</td>
                <td>{{ $kpi['target'] }}</td>
                <td>{{ $kpi['actual'] }}</td>
                <td class="text-center" style="font-weight: bold; color: {{ $kpi['score'] >= 90 ? '#16a34a' : '#ea580c' }};">
                    {{ $kpi['score'] }}
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <table class="sign-table">
        <tr>
            <td>
                Disiapkan Oleh<br><br><br>
                <strong>Supervisor Procurement</strong><br>
                Staff Sasaran Mutu
            </td>
            <td>
                Ditinjau (Quality Assurance)<br><br><br>
                <strong>QA Manager</strong><br>
                Manajemen Mutu ISO
            </td>
            <td>
                Disetujui (General Manager)<br><br><br>
                <strong>General Manager</strong><br>
                Pimpinan Eksekutif
            </td>
        </tr>
    </table>
</body>
</html>
