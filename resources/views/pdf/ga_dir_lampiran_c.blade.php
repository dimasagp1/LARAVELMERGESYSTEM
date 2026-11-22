<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>DIR Lampiran C - {{ $report['location']->name }}</title>
    <style>
        @page { size: A4 landscape; margin: 12mm; }
        body { font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; font-size: 9pt; color: #1e293b; line-height: 1.35; }
        .header-table { width: 100%; border-collapse: collapse; margin-bottom: 12px; border: 1.5px solid #d97706; }
        .header-table td { padding: 6px 10px; border: 1px solid #d97706; vertical-align: middle; }
        .company-title { font-size: 13pt; font-weight: bold; color: #b45309; }
        .doc-title { font-size: 12pt; font-weight: bold; text-align: center; color: #b45309; }
        .meta-table { width: 100%; border-collapse: collapse; margin-bottom: 10px; font-size: 9pt; }
        .meta-table td { padding: 4px 6px; }
        .meta-label { width: 18%; font-weight: bold; color: #475569; }
        .meta-val { width: 32%; }
        .data-table { width: 100%; border-collapse: collapse; margin-top: 4px; margin-bottom: 8px; font-size: 8pt; }
        .data-table th { background: #fef3c7; color: #92400e; border: 1px solid #cbd5e1; padding: 5px; text-align: left; }
        .data-table td { border: 1px solid #cbd5e1; padding: 5px; }
        .sign-table { width: 100%; border-collapse: collapse; margin-top: 14px; text-align: center; font-size: 8.5pt; }
        .sign-table td { width: 33.33%; border: 1px solid #cbd5e1; padding: 6px; vertical-align: bottom; }
        .sign-space { height: 45px; }
        .footer { font-size: 7.5pt; color: #94a3b8; text-align: right; margin-top: 10px; }
    </style>
</head>
<body>
    <table class="header-table">
        <tr>
            <td style="width: 25%; text-align: center;">
                <div class="company-title">HERBATECH</div>
                <div style="font-size: 8pt; color: #64748b;">Asset Management & GA</div>
            </td>
            <td style="width: 50%; text-align: center;">
                <div class="doc-title">DAFTAR INVENTARIS RUANGAN (DIR)<br><span style="font-size: 9pt; font-weight: normal;">(LAMPIRAN C - KONTROL ASET TETAP PER RUANGAN)</span></div>
            </td>
            <td style="width: 25%; font-size: 8pt;">
                <strong>No. Dok:</strong> SOP-GA-001-LPC<br>
                <strong>Plant:</strong> {{ $report['location']->plant?->name ?? 'Plant' }}<br>
                <strong>Tgl Cetak:</strong> {{ $printedAt }}
            </td>
        </tr>
    </table>

    <table class="meta-table">
        <tr>
            <td class="meta-label">Nama Ruangan / Fasilitas:</td>
            <td class="meta-val"><strong>{{ $report['location']->name }}</strong> (Kode: {{ $report['location']->code ?? '-' }})</td>
            <td class="meta-label">Tipe Fasilitas:</td>
            <td class="meta-val">{{ $report['location']->type ?? 'Ruangan Fasilitas Pabrik' }}</td>
        </tr>
        <tr>
            <td class="meta-label">Lokasi Pabrik:</td>
            <td class="meta-val">{{ $report['location']->plant?->name ?? '-' }} ({{ $report['location']->plant?->code ?? '-' }})</td>
            <td class="meta-label">Jumlah Aset Terdaftar:</td>
            <td class="meta-val"><strong>{{ $report['summary']['total_items'] }} Unit</strong></td>
        </tr>
    </table>

    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 4%; text-align: center;">No</th>
                <th style="width: 18%;">Kode Aset</th>
                <th style="width: 22%;">Nama Aset & Spesifikasi</th>
                <th style="width: 14%;">Kategori</th>
                <th style="width: 9%; text-align: center;">Tgl Perolehan</th>
                <th style="width: 8%; text-align: center;">Kondisi</th>
                <th style="width: 11%;">Penanggung Jawab (PIC)</th>
                <th style="width: 14%; text-align: right;">Nilai Buku (Rp)</th>
            </tr>
        </thead>
        <tbody>
            @forelse($report['assets'] as $idx => $asset)
            <tr>
                <td style="text-align: center;">{{ $idx + 1 }}</td>
                <td><strong>{{ $asset->asset_code }}</strong></td>
                <td>{{ $asset->name }}</td>
                <td>{{ $asset->category?->name ?? '-' }}</td>
                <td style="text-align: center;">{{ $asset->purchase_date ? $asset->purchase_date->format('d/m/Y') : '-' }}</td>
                <td style="text-align: center;">
                    <span style="font-weight: bold; color: {{ $asset->condition === 'BAIK' ? '#059669' : ($asset->condition === 'RUSAK_RINGAN' ? '#d97706' : '#dc2626') }};">
                        {{ $asset->condition }}
                    </span>
                </td>
                <td>{{ $asset->pic_name ?? '-' }}</td>
                <td style="text-align: right;">Rp {{ number_format($asset->book_value, 0, ',', '.') }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="8" style="text-align: center; color: #94a3b8; padding: 12px;">Tidak ada inventaris aset terdaftar di ruangan ini.</td>
            </tr>
            @endforelse
        </tbody>
        <tfoot>
            <tr style="font-weight: bold; background: #fef3c7;">
                <td colspan="7" style="text-align: right;">TOTAL NILAI BUKU ASET RUANGAN:</td>
                <td style="text-align: right; color: #b45309;">Rp {{ number_format($report['summary']['total_book_value'], 0, ',', '.') }}</td>
            </tr>
        </tfoot>
    </table>

    <div style="font-size: 8pt; color: #475569; margin-top: 4px;">
        * Nilai Buku dihitung secara otomatis menggunakan metode Garis Lurus (Straight-line Depreciation) berdasarkan masa manfaat aset.
    </div>

    <table class="sign-table">
        <tr>
            <td>
                <strong>Penanggung Jawab Ruangan (PIC)</strong><br>
                <span style="font-size: 7.5pt; color: #64748b;">(Pemeriksaan Keberadaan Fisik)</span>
                <div class="sign-space"></div>
                <strong>...................................</strong>
            </td>
            <td>
                <strong>Staff Pengelola Aset GA</strong><br>
                <span style="font-size: 7.5pt; color: #64748b;">(Verifikasi & Labelisasi Barcode/QR)</span>
                <div class="sign-space"></div>
                <strong>...................................</strong>
            </td>
            <td>
                <strong>Head of General Affair</strong><br>
                <span style="font-size: 7.5pt; color: #64748b;">(Otorisasi & Pengesahan DIR)</span>
                <div class="sign-space"></div>
                <strong>...................................</strong>
            </td>
        </tr>
    </table>

    <div class="footer">
        Dicetak otomatis oleh Sistem ERP Terintegrasi PT HerbaTech Indo Utama pada {{ $printedAt }}
    </div>
</body>
</html>
