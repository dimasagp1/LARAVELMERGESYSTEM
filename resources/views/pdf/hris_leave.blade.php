<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Permohonan Cuti - {{ $leave->leave_number }}</title>
    <style>
        body { font-family: Helvetica, Arial, sans-serif; font-size: 11px; color: #1e293b; line-height: 1.4; margin: 20px; }
        .header { border-bottom: 2px solid #10b981; padding-bottom: 12px; margin-bottom: 16px; }
        .company-name { font-size: 18px; font-weight: bold; color: #10b981; text-transform: uppercase; }
        .doc-title { font-size: 14px; font-weight: bold; margin-top: 4px; color: #0f172a; }
        .meta-table { width: 100%; margin-bottom: 16px; border-collapse: collapse; }
        .meta-table td { padding: 5px 8px; font-size: 10px; border: 1px solid #cbd5e1; }
        .meta-label { font-weight: bold; color: #334155; width: 25%; background-color: #f8fafc; }
        .badge { display: inline-block; padding: 2px 6px; border-radius: 4px; font-size: 9px; font-weight: bold; background-color: #d1fae5; color: #047857; }
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
                    <div style="font-size: 9px; color: #64748b;">HRISNOW — Pengajuan Hak Cuti & Izin Resmi Karyawan</div>
                </td>
                <td style="text-align: right;">
                    <div class="doc-title">SURAT PERMOHONAN CUTI</div>
                    <div style="font-size: 11px; font-weight: bold; color: #10b981;">{{ $leave->leave_number }}</div>
                </td>
            </tr>
        </table>
    </div>

    <table class="meta-table">
        <tr>
            <td class="meta-label">NIK / Nama Karyawan:</td>
            <td style="font-weight: bold;">{{ $leave->employee?->nik }} — {{ $leave->employee?->user?->name }}</td>
            <td class="meta-label">Departemen:</td>
            <td>{{ $leave->employee?->department?->name ?? '-' }}</td>
        </tr>
        <tr>
            <td class="meta-label">Kategori Cuti:</td>
            <td><strong>{{ $leave->category?->name }}</strong></td>
            <td class="meta-label">Total Durasi:</td>
            <td style="font-weight: bold; color: #047857;">{{ $leave->total_days }} Hari Kerja</td>
        </tr>
        <tr>
            <td class="meta-label">Tanggal Mulai Cuti:</td>
            <td>{{ \Carbon\Carbon::parse($leave->start_date)->translatedFormat('d F Y') }}</td>
            <td class="meta-label">Tanggal Selesai:</td>
            <td>{{ \Carbon\Carbon::parse($leave->end_date)->translatedFormat('d F Y') }}</td>
        </tr>
        <tr>
            <td class="meta-label">Status Pengajuan:</td>
            <td colspan="3"><span class="badge">{{ strtoupper($leave->status) }}</span></td>
        </tr>
        <tr>
            <td class="meta-label">Alasan Permohonan:</td>
            <td colspan="3" style="color: #475569;">{{ $leave->reason }}</td>
        </tr>
    </table>

    <table class="sign-table">
        <tr>
            <td>
                Pemohon:<br><br><br><br>
                <strong>( {{ $leave->employee?->user?->name }} )</strong>
            </td>
            <td>
                Atasan Langsung (SPV/Manager):<br><br><br><br>
                <strong>( Supervisor Departemen )</strong>
            </td>
            <td>
                Disetujui Oleh (HRD):<br><br><br><br>
                <strong>( HR & GA Manager )</strong>
            </td>
        </tr>
    </table>
</body>
</html>
