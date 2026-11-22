<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan Keuangan - {{ $period['name'] }}</title>
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
        .font-bold { font-weight: bold; }
        .bg-subtotal { background-color: #f8fafc; font-weight: bold; }
        .bg-total { background-color: #f1f5f9; font-weight: bold; color: #059669; font-size: 10px; }
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
                    <div style="font-size: 8.5px; color: #64748b;">Laporan Keuangan Resmi &bull; Standar Akuntansi Keuangan (SAK)</div>
                </td>
                <td style="text-align: right;">
                    <div class="doc-title">LAPORAN KEUANGAN LENGKAP</div>
                    <div style="font-size: 9.5px; font-weight: bold; color: #059669;">Periode: {{ $period['name'] }}</div>
                </td>
            </tr>
        </table>
    </div>

    <!-- 1. Laba Rugi Komprehensif -->
    <h3 style="font-size: 11px; color: #065f46; margin-bottom: 5px; text-transform: uppercase;">
        1. Laporan Laba Rugi Komprehensif
    </h3>
    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 65%;">Keterangan Pos Laba Rugi</th>
                <th class="text-right" style="width: 35%;">Jumlah (Rupiah)</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td><strong>Pendapatan Penjualan Bersih (Revenue)</strong></td>
                <td class="text-right font-bold">{{ number_format($statements['income_statement']['revenue'], 0, ',', '.') }}</td>
            </tr>
            <tr>
                <td style="padding-left: 20px;">Beban Pokok Penjualan (HPP / COGS)</td>
                <td class="text-right" style="color: #dc2626;">({{ number_format($statements['income_statement']['cogs'], 0, ',', '.') }})</td>
            </tr>
            <tr class="bg-subtotal">
                <td>LABA KOTOR (GROSS PROFIT)</td>
                <td class="text-right">{{ number_format($statements['income_statement']['gross_profit'], 0, ',', '.') }}</td>
            </tr>
            <tr>
                <td colspan="2" style="background-color: #fafafa; font-weight: bold;">Beban Operasional (OPEX):</td>
            </tr>
            <tr>
                <td style="padding-left: 20px;">Beban Penjualan & Pemasaran</td>
                <td class="text-right">{{ number_format($statements['income_statement']['opex']['sales_marketing'], 0, ',', '.') }}</td>
            </tr>
            <tr>
                <td style="padding-left: 20px;">Beban Gaji & Tunjangan Karyawan</td>
                <td class="text-right">{{ number_format($statements['income_statement']['opex']['salaries_wages'], 0, ',', '.') }}</td>
            </tr>
            <tr>
                <td style="padding-left: 20px;">Beban Administrasi & Umum</td>
                <td class="text-right">{{ number_format($statements['income_statement']['opex']['general_admin'], 0, ',', '.') }}</td>
            </tr>
            <tr>
                <td style="padding-left: 20px;">Beban Penyusutan & Amortisasi</td>
                <td class="text-right">{{ number_format($statements['income_statement']['opex']['depreciation'], 0, ',', '.') }}</td>
            </tr>
            <tr class="bg-subtotal">
                <td>Total Beban Operasional</td>
                <td class="text-right" style="color: #dc2626;">({{ number_format($statements['income_statement']['opex']['total'], 0, ',', '.') }})</td>
            </tr>
            <tr class="bg-total">
                <td>LABA BERSIH TAHUN BERJALAN (NET INCOME)</td>
                <td class="text-right">{{ number_format($statements['income_statement']['net_income'], 0, ',', '.') }}</td>
            </tr>
        </tbody>
    </table>

    <!-- 2. Laporan Neraca -->
    <h3 style="font-size: 11px; color: #065f46; margin-bottom: 5px; text-transform: uppercase; margin-top: 15px;">
        2. Laporan Posisi Keuangan (Neraca)
    </h3>
    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 50%;">ASET</th>
                <th style="width: 50%;">LIABILITAS & EKUITAS</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td style="vertical-align: top; padding: 0;">
                    <table style="width: 100%; border-collapse: collapse;">
                        <tr class="bg-subtotal"><td colspan="2">Aset Lancar:</td></tr>
                        <tr><td>Kas & Setara Kas</td><td class="text-right">{{ number_format($statements['balance_sheet']['assets']['current']['cash'], 0, ',', '.') }}</td></tr>
                        <tr><td>Piutang Usaha</td><td class="text-right">{{ number_format($statements['balance_sheet']['assets']['current']['accounts_receivable'], 0, ',', '.') }}</td></tr>
                        <tr><td>Persediaan Bahan & Produk</td><td class="text-right">{{ number_format($statements['balance_sheet']['assets']['current']['inventory'], 0, ',', '.') }}</td></tr>
                        <tr class="bg-subtotal"><td>Total Aset Lancar</td><td class="text-right">{{ number_format($statements['balance_sheet']['assets']['current']['total'], 0, ',', '.') }}</td></tr>
                        <tr class="bg-subtotal"><td colspan="2">Aset Tidak Lancar:</td></tr>
                        <tr><td>Aset Tetap Pabrik</td><td class="text-right">{{ number_format($statements['balance_sheet']['assets']['non_current']['fixed_assets'], 0, ',', '.') }}</td></tr>
                        <tr><td>Akumulasi Penyusutan</td><td class="text-right">({{ number_format($statements['balance_sheet']['assets']['non_current']['accumulated_depreciation'], 0, ',', '.') }})</td></tr>
                        <tr class="bg-total"><td>TOTAL ASET</td><td class="text-right">{{ number_format($statements['balance_sheet']['assets']['total_assets'], 0, ',', '.') }}</td></tr>
                    </table>
                </td>
                <td style="vertical-align: top; padding: 0;">
                    <table style="width: 100%; border-collapse: collapse;">
                        <tr class="bg-subtotal"><td colspan="2">Liabilitas Lancar:</td></tr>
                        <tr><td>Utang Usaha</td><td class="text-right">{{ number_format($statements['balance_sheet']['liabilities']['current']['accounts_payable'], 0, ',', '.') }}</td></tr>
                        <tr><td>Beban Akrual & Utang Pajak</td><td class="text-right">{{ number_format($statements['balance_sheet']['liabilities']['current']['accrued_expenses'], 0, ',', '.') }}</td></tr>
                        <tr class="bg-subtotal"><td colspan="2">Ekuitas Pemilik:</td></tr>
                        <tr><td>Modal Disetor</td><td class="text-right">{{ number_format($statements['balance_sheet']['equity']['paid_in_capital'], 0, ',', '.') }}</td></tr>
                        <tr><td>Saldo Laba Ditahan</td><td class="text-right">{{ number_format($statements['balance_sheet']['equity']['retained_earnings'], 0, ',', '.') }}</td></tr>
                        <tr><td>Laba Bersih Berjalan</td><td class="text-right">{{ number_format($statements['balance_sheet']['equity']['current_net_income'], 0, ',', '.') }}</td></tr>
                        <tr class="bg-total"><td>TOTAL LIABILITAS & EKUITAS</td><td class="text-right">{{ number_format($statements['balance_sheet']['total_liabilities_and_equity'], 0, ',', '.') }}</td></tr>
                    </table>
                </td>
            </tr>
        </tbody>
    </table>

    <table class="sign-table">
        <tr>
            <td>
                Disiapkan Oleh<br><br><br>
                <strong>Staf Akuntansi / FAT</strong><br>
                Finance & Accounting
            </td>
            <td>
                Diperiksa Oleh<br><br><br>
                <strong>Finance & Accounting Manager</strong><br>
                Head of FAT
            </td>
            <td>
                Disetujui Oleh<br><br><br>
                <strong>General Manager / Direktur</strong><br>
                Executive Board
            </td>
        </tr>
    </table>
</body>
</html>
