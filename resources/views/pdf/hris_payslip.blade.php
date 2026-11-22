<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Slip Gaji - {{ $salary->salary_number }}</title>
    <style>
        body { font-family: Helvetica, Arial, sans-serif; font-size: 11px; color: #1e293b; line-height: 1.4; margin: 20px; }
        .header { border-bottom: 2px solid #0284c7; padding-bottom: 12px; margin-bottom: 16px; }
        .company-name { font-size: 18px; font-weight: bold; color: #0284c7; text-transform: uppercase; }
        .doc-title { font-size: 14px; font-weight: bold; margin-top: 4px; color: #0f172a; }
        .meta-table { width: 100%; margin-bottom: 16px; border-collapse: collapse; }
        .meta-table td { padding: 4px 6px; font-size: 10px; }
        .meta-label { font-weight: bold; color: #64748b; width: 22%; }
        .salary-table { width: 100%; border-collapse: collapse; margin-bottom: 16px; }
        .salary-table th, .salary-table td { border: 1px solid #cbd5e1; padding: 6px 8px; font-size: 10px; }
        .salary-table th { background-color: #f1f5f9; font-weight: bold; color: #334155; }
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .total-box { background-color: #f0fdf4; border: 2px solid #22c55e; padding: 10px; border-radius: 6px; margin-bottom: 20px; }
        .sign-table { width: 100%; margin-top: 25px; border-collapse: collapse; }
        .sign-table td { width: 50%; text-align: center; vertical-align: bottom; height: 70px; font-size: 9px; border: 1px solid #cbd5e1; padding: 8px; }
    </style>
</head>
<body>
    <div class="header">
        <table style="width: 100%;">
            <tr>
                <td>
                    <div class="company-name">PT HERBATECH INNOPHARMA INDUSTRY</div>
                    <div style="font-size: 9px; color: #64748b;">Human Resource Information System — HRISNOW Enterprise</div>
                </td>
                <td style="text-align: right;">
                    <div class="doc-title">SLIP GAJI KARYAWAN</div>
                    <div style="font-size: 11px; font-weight: bold; color: #0284c7;">{{ $salary->salary_number }}</div>
                    <div style="font-size: 9px; color: #64748b;">Periode: {{ \Carbon\Carbon::createFromDate($salary->period_year, $salary->period_month, 1)->translatedFormat('F Y') }}</div>
                </td>
            </tr>
        </table>
    </div>

    <table class="meta-table">
        <tr>
            <td class="meta-label">NIK Karyawan:</td>
            <td style="font-weight: bold;">{{ $salary->employee?->nik }}</td>
            <td class="meta-label">Departemen:</td>
            <td>{{ $salary->employee?->department?->name ?? '-' }}</td>
        </tr>
        <tr>
            <td class="meta-label">Nama Lengkap:</td>
            <td style="font-weight: bold; font-size: 11px;">{{ $salary->employee?->user?->name }}</td>
            <td class="meta-label">Jabatan:</td>
            <td>{{ $salary->employee?->position }}</td>
        </tr>
        <tr>
            <td class="meta-label">Status Kepegawaian:</td>
            <td>{{ strtoupper($salary->employee?->employment_status ?? 'PKWTT') }}</td>
            <td class="meta-label">Golongan / Grade:</td>
            <td>{{ $salary->employee?->grade?->grade_name ?? 'Staff' }} ({{ $salary->employee?->grade?->code ?? 'G1' }})</td>
        </tr>
        <tr>
            <td class="meta-label">Rekening Bank:</td>
            <td>{{ $salary->employee?->bank_name ?? 'BCA' }} - {{ $salary->employee?->bank_account_no ?? '-' }}</td>
            <td class="meta-label">Tanggal Bayar:</td>
            <td>{{ $salary->payment_date ? \Carbon\Carbon::parse($salary->payment_date)->translatedFormat('d F Y') : '-' }}</td>
        </tr>
    </table>

    <table class="salary-table">
        <thead>
            <tr>
                <th style="width: 50%;">KOMPONEN PENGHASILAN (EARNINGS)</th>
                <th style="width: 50%;">KOMPONEN POTONGAN (DEDUCTIONS)</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td style="vertical-align: top; padding: 0;">
                    <table style="width: 100%; border-collapse: collapse;">
                        <tr>
                            <td style="border: none; padding: 4px 6px;">Gaji Pokok</td>
                            <td style="border: none; padding: 4px 6px;" class="text-right">Rp {{ number_format($salary->basic_salary, 0, ',', '.') }}</td>
                        </tr>
                        <tr>
                            <td style="border: none; padding: 4px 6px;">Tunjangan Tetap (Jabatan/Masa Kerja)</td>
                            <td style="border: none; padding: 4px 6px;" class="text-right">Rp {{ number_format($salary->fixed_allowances, 0, ',', '.') }}</td>
                        </tr>
                        <tr>
                            <td style="border: none; padding: 4px 6px;">Tunjangan Kehadiran (Uang Makan & Transport)</td>
                            <td style="border: none; padding: 4px 6px;" class="text-right">Rp {{ number_format($salary->attendance_allowances, 0, ',', '.') }}</td>
                        </tr>
                        <tr>
                            <td style="border: none; padding: 4px 6px;">Upah Lembur Resmi (SPL Depnaker)</td>
                            <td style="border: none; padding: 4px 6px;" class="text-right">Rp {{ number_format($salary->overtime_pay, 0, ',', '.') }}</td>
                        </tr>
                    </table>
                </td>
                <td style="vertical-align: top; padding: 0;">
                    <table style="width: 100%; border-collapse: collapse;">
                        <tr>
                            <td style="border: none; padding: 4px 6px;">Potongan Keterlambatan Absen</td>
                            <td style="border: none; padding: 4px 6px;" class="text-right">Rp {{ number_format($salary->late_deductions, 0, ',', '.') }}</td>
                        </tr>
                        <tr>
                            <td style="border: none; padding: 4px 6px;">BPJS Ketenagakerjaan (JHT 2% + JP 1%)</td>
                            <td style="border: none; padding: 4px 6px;" class="text-right">Rp {{ number_format($salary->bpjs_tk_deduction, 0, ',', '.') }}</td>
                        </tr>
                        <tr>
                            <td style="border: none; padding: 4px 6px;">BPJS Kesehatan (1%)</td>
                            <td style="border: none; padding: 4px 6px;" class="text-right">Rp {{ number_format($salary->bpjs_kes_deduction, 0, ',', '.') }}</td>
                        </tr>
                        <tr>
                            <td style="border: none; padding: 4px 6px;">PPh 21 TER (Tarif Efektif Rata-Rata)</td>
                            <td style="border: none; padding: 4px 6px;" class="text-right">Rp {{ number_format($salary->pph21_ter_deduction, 0, ',', '.') }}</td>
                        </tr>
                    </table>
                </td>
            </tr>
            <tr style="background-color: #f8fafc; font-weight: bold;">
                @php
                    $gross = $salary->basic_salary + $salary->fixed_allowances + $salary->attendance_allowances + $salary->overtime_pay;
                    $deductions = $salary->late_deductions + $salary->bpjs_tk_deduction + $salary->bpjs_kes_deduction + $salary->pph21_ter_deduction;
                @endphp
                <td>
                    <div style="display: flex; justify-content: space-between;">
                        <span>Total Penghasilan Kotor:</span>
                        <span class="text-right">Rp {{ number_format($gross, 0, ',', '.') }}</span>
                    </div>
                </td>
                <td>
                    <div style="display: flex; justify-content: space-between;">
                        <span>Total Potongan:</span>
                        <span class="text-right">Rp {{ number_format($deductions, 0, ',', '.') }}</span>
                    </div>
                </td>
            </tr>
        </tbody>
    </table>

    <div class="total-box">
        <table style="width: 100%;">
            <tr>
                <td style="font-size: 12px; font-weight: bold; color: #166534;">GAJI BERSIH DITERIMA (TAKE HOME PAY):</td>
                <td style="font-size: 15px; font-weight: bold; color: #166534; text-align: right;">
                    Rp {{ number_format($salary->net_salary, 0, ',', '.') }}
                </td>
            </tr>
        </table>
    </div>

    <div style="font-size: 9px; color: #64748b;">
        * Dokumen slip gaji ini diterbitkan secara otomatis oleh sistem HRISNOW PT Herbatech Innopharma Industry dan sah tanpa tanda tangan basah. Rahasia (*Confidential*).
    </div>

    <table class="sign-table">
        <tr>
            <td>
                Diterima Oleh Karyawan:<br><br><br><br>
                <strong>( {{ $salary->employee?->user?->name }} )</strong>
            </td>
            <td>
                Disetujui Oleh (HR & GA Head):<br><br><br><br>
                <strong>( HRD & Payroll Manager )</strong>
            </td>
        </tr>
    </table>
</body>
</html>
