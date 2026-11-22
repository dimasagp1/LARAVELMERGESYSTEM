<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Surat Perintah Lembur - {{ $overtime->spl_number }}</title>
    <style>
        body { font-family: Helvetica, Arial, sans-serif; font-size: 11px; color: #1e293b; line-height: 1.4; margin: 20px; }
        .header { border-bottom: 2px solid #ea580c; padding-bottom: 12px; margin-bottom: 16px; }
        .company-name { font-size: 18px; font-weight: bold; color: #ea580c; text-transform: uppercase; }
        .doc-title { font-size: 14px; font-weight: bold; margin-top: 4px; color: #0f172a; }
        .meta-table { width: 100%; margin-bottom: 16px; border-collapse: collapse; }
        .meta-table td { padding: 5px 8px; font-size: 10px; border: 1px solid #cbd5e1; }
        .meta-label { font-weight: bold; color: #334155; width: 25%; background-color: #f8fafc; }
        .badge { display: inline-block; padding: 2px 6px; border-radius: 4px; font-size: 9px; font-weight: bold; background-color: #ffedd5; color: #c2410c; }
        .sign-table { width: 100%; margin-top: 30px; border-collapse: collapse; }
        .sign-table td { width: 25%; text-align: center; vertical-align: bottom; height: 75px; font-size: 9px; border: 1px solid #cbd5e1; padding: 8px; }
    </style>
</head>
<body>
    <div class="header">
        <table style="width: 100%;">
            <tr>
                <td>
                    <div class="company-name">PT HERBATECH INNOPHARMA INDUSTRY</div>
                    <div style="font-size: 9px; color: #64748b;">HRISNOW — Surat Perintah Kerja Lembur Resmi (Depnaker RI)</div>
                </td>
                <td style="text-align: right;">
                    <div class="doc-title">SURAT PERINTAH LEMBUR (SPL)</div>
                    <div style="font-size: 11px; font-weight: bold; color: #ea580c;">{{ $overtime->spl_number }}</div>
                </td>
            </tr>
        </table>
    </div>

    <table class="meta-table">
        <tr>
            <td class="meta-label">NIK / Nama Karyawan:</td>
            <td style="font-weight: bold;">{{ $overtime->employee?->nik }} — {{ $overtime->employee?->user?->name }}</td>
            <td class="meta-label">Departemen:</td>
            <td>{{ $overtime->employee?->department?->name ?? '-' }}</td>
        </tr>
        <tr>
            <td class="meta-label">Tanggal Lembur:</td>
            <td style="font-weight: bold;">{{ \Carbon\Carbon::parse($overtime->date)->translatedFormat('d F Y') }}</td>
            <td class="meta-label">Jam Pelaksanaan:</td>
            <td>{{ $overtime->start_time }} s/d {{ $overtime->end_time }} WIB</td>
        </tr>
        <tr>
            <td class="meta-label">Total Jam Riil:</td>
            <td style="font-weight: bold; font-size: 11px;">{{ $overtime->total_hours }} Jam</td>
            <td class="meta-label">Jam Perhitungan (Depnaker):</td>
            <td style="font-weight: bold; color: #ea580c;">{{ $overtime->calculated_hours }} Jam Multiplier</td>
        </tr>
        <tr>
            <td class="meta-label">Tipe Kompensasi:</td>
            <td>
                <span class="badge">{{ strtoupper($overtime->compensation_type === 'timebank' ? 'Time Bank Deposit' : 'Bayar Slip Gaji') }}</span>
            </td>
            <td class="meta-label">Status Otorisasi:</td>
            <td><span class="badge">{{ strtoupper($overtime->status) }}</span></td>
        </tr>
        <tr>
            <td class="meta-label">Urgensi & Alasan Lembur:</td>
            <td colspan="3" style="color: #475569;">{{ $overtime->reason }}</td>
        </tr>
    </table>

    <div style="font-size: 9px; color: #64748b; margin-top: 10px;">
        * Sesuai ketentuan PP No. 35 Tahun 2021 dan Kepmenakertrans RI, perintah lembur telah disetujui bertingkat oleh Atasan Langsung, Operational Manager, dan General Manager.
    </div>

    <table class="sign-table">
        <tr>
            <td>
                Pelaksana Lembur:<br><br><br><br>
                <strong>( {{ $overtime->employee?->user?->name }} )</strong>
            </td>
            <td>
                Supervisor / SPV:<br><br><br><br>
                <strong>( Atasan Langsung )</strong>
            </td>
            <td>
                Operational Manager:<br><br><br><br>
                <strong>( Operational Head )</strong>
            </td>
            <td>
                General Manager (GM):<br><br><br><br>
                <strong>( General Manager )</strong>
            </td>
        </tr>
    </table>
</body>
</html>
