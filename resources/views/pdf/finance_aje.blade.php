<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Jurnal Penyesuaian - {{ $journal->journal_number }}</title>
    <style>
        body { font-family: Helvetica, Arial, sans-serif; font-size: 10px; color: #1e293b; line-height: 1.4; margin: 15px; }
        .header { border-bottom: 2px solid #059669; padding-bottom: 10px; margin-bottom: 15px; }
        .company-name { font-size: 16px; font-weight: bold; color: #059669; text-transform: uppercase; }
        .doc-title { font-size: 13px; font-weight: bold; margin-top: 3px; color: #0f172a; }
        .meta-table { width: 100%; margin-bottom: 15px; border-collapse: collapse; }
        .meta-table td { padding: 4px 6px; font-size: 9.5px; }
        .meta-label { font-weight: bold; color: #64748b; width: 22%; }
        .data-table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        .data-table th, .data-table td { border: 1px solid #cbd5e1; padding: 6px 8px; font-size: 9.5px; }
        .data-table th { background-color: #ecfdf5; color: #065f46; font-weight: bold; }
        .text-right { text-align: right; }
        .text-center { text-align: center; }
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
                    <div style="font-size: 8.5px; color: #64748b;">Kawasan Industri Cikarang &bull; Divisi Finance, Accounting & Tax</div>
                </td>
                <td style="text-align: right;">
                    <div class="doc-title">BUKTI JURNAL PENYESUAIAN (AJE)</div>
                    <div style="font-size: 9.5px; font-weight: bold; color: #059669;">{{ $journal->journal_number }}</div>
                </td>
            </tr>
        </table>
    </div>

    <table class="meta-table">
        <tr>
            <td class="meta-label">Tanggal Jurnal:</td>
            <td>{{ $journal->journal_date?->format('d/m/Y') }}</td>
            <td class="meta-label">Kategori Penyesuaian:</td>
            <td style="font-weight: bold; text-transform: uppercase;">{{ $journal->category }}</td>
        </tr>
        <tr>
            <td class="meta-label">Periode Fiskal:</td>
            <td>{{ $journal->period?->period_name }}</td>
            <td class="meta-label">Status Otorisasi:</td>
            <td style="font-weight: bold; color: #059669;">{{ strtoupper($journal->status) }}</td>
        </tr>
        <tr>
            <td class="meta-label">Deskripsi / Alasan:</td>
            <td colspan="3">{{ $journal->description }}</td>
        </tr>
    </table>

    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 5%;">No</th>
                <th style="width: 15%;">Kode Akun</th>
                <th style="width: 35%;">Nama Akun & Departemen</th>
                <th class="text-right" style="width: 22%;">Debet (Rp)</th>
                <th class="text-right" style="width: 23%;">Kredit (Rp)</th>
            </tr>
        </thead>
        <tbody>
            @foreach($journal->lines as $idx => $line)
            <tr>
                <td class="text-center">{{ $idx + 1 }}</td>
                <td style="font-family: monospace; font-weight: bold;">{{ $line->account_code }}</td>
                <td>
                    <strong>{{ $line->account_name }}</strong>
                    @if($line->department) <br><span style="color: #64748b; font-size: 8.5px;">Dept: {{ $line->department->name }}</span> @endif
                </td>
                <td class="text-right">{{ number_format($line->debit, 0, ',', '.') }}</td>
                <td class="text-right">{{ number_format($line->credit, 0, ',', '.') }}</td>
            </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr style="font-weight: bold; background-color: #f8fafc;">
                <td colspan="3" class="text-right">TOTAL JURNAL PENYESUAIAN (BERIMBANG):</td>
                <td class="text-right" style="color: #059669;">Rp {{ number_format($journal->total_debit, 0, ',', '.') }}</td>
                <td class="text-right" style="color: #059669;">Rp {{ number_format($journal->total_credit, 0, ',', '.') }}</td>
            </tr>
        </tfoot>
    </table>

    <table class="sign-table">
        <tr>
            <td>
                Dibuat Oleh (Maker)<br><br><br>
                <strong>{{ $journal->creator?->name ?? 'Staf Akuntansi' }}</strong><br>
                Staf Finance & Accounting
            </td>
            <td>
                Diperiksa Oleh (Checker)<br><br><br>
                <strong>{{ $journal->approver?->name ?? 'FAT Supervisor' }}</strong><br>
                Supervisor Akuntansi
            </td>
            <td>
                Disetujui Oleh (Sign-off)<br><br><br>
                <strong>Manager FAT</strong><br>
                Head of Finance & Accounting
            </td>
        </tr>
    </table>
</body>
</html>
