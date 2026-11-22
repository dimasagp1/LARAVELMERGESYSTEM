<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan Penyelidikan Ketidaksesuaian - {{ $deviation->deviation_number }}</title>
    <style>
        @page { size: A4 portrait; margin: 12mm; }
        body { font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; font-size: 9.5pt; color: #1e293b; line-height: 1.35; }
        .header-table { width: 100%; border-collapse: collapse; margin-bottom: 10px; border: 1.5px solid #0f766e; }
        .header-table td { padding: 6px 10px; border: 1px solid #0f766e; vertical-align: middle; }
        .company-title { font-size: 13pt; font-weight: bold; color: #0f766e; }
        .doc-title { font-size: 11pt; font-weight: bold; text-align: center; color: #0f766e; }
        .meta-table { width: 100%; border-collapse: collapse; margin-bottom: 10px; font-size: 8.5pt; }
        .meta-table td { padding: 3px 6px; }
        .meta-label { width: 20%; font-weight: bold; color: #475569; }
        .meta-val { width: 30%; }
        .section-header { background: #0f766e; color: #ffffff; padding: 4px 8px; font-size: 9pt; font-weight: bold; margin-top: 8px; margin-bottom: 4px; text-transform: uppercase; }
        .content-box { border: 1px solid #cbd5e1; padding: 5px 8px; font-size: 8.5pt; background: #f8fafc; margin-bottom: 6px; }
        .data-table { width: 100%; border-collapse: collapse; margin-top: 4px; margin-bottom: 8px; font-size: 8pt; }
        .data-table th { background: #f1f5f9; color: #334155; border: 1px solid #cbd5e1; padding: 4px; text-align: left; }
        .data-table td { border: 1px solid #cbd5e1; padding: 4px; }
        .badge { display: inline-block; padding: 2px 6px; border-radius: 3px; font-weight: bold; font-size: 7.5pt; }
        .badge-kritis { background: #fee2e2; color: #991b1b; }
        .badge-mayor { background: #fef3c7; color: #92400e; }
        .badge-minor { background: #e0f2fe; color: #075985; }
        .fishbone-grid { width: 100%; border-collapse: collapse; margin-bottom: 8px; font-size: 8pt; }
        .fishbone-grid td { width: 33.33%; border: 1px solid #cbd5e1; padding: 5px; vertical-align: top; background: #fafafa; }
        .fishbone-cat { font-weight: bold; color: #0f766e; border-bottom: 1px solid #cbd5e1; padding-bottom: 2px; margin-bottom: 4px; }
        .sign-table { width: 100%; border-collapse: collapse; margin-top: 10px; text-align: center; font-size: 8pt; }
        .sign-table td { width: 33.33%; border: 1px solid #cbd5e1; padding: 6px; vertical-align: bottom; }
        .sign-space { height: 45px; }
    </style>
</head>
<body>
    <table class="header-table">
        <tr>
            <td style="width: 25%; text-align: center;">
                <div class="company-title">HERBATECH</div>
                <div style="font-size: 7.5pt; color: #64748b;">Quality Assurance Dept</div>
            </td>
            <td style="width: 50%; text-align: center;">
                <div class="doc-title">LAPORAN PENYELIDIKAN KETIDAKSESUAIAN<br><span style="font-size: 9pt; font-weight: normal;">(DEVIATION & ROOT CAUSE INVESTIGATION REPORT)</span></div>
            </td>
            <td style="width: 25%; font-size: 7.5pt;">
                <strong>Formulir:</strong> No. CM-09/QA/003D.01<br>
                <strong>No. DR:</strong> {{ $deviation->deviation_number }}<br>
                <strong>No. PK:</strong> {{ $deviation->pk_number }}<br>
                <strong>Tgl Temuan:</strong> {{ \Carbon\Carbon::parse($deviation->tanggal_temuan)->format('d/m/Y') }}
            </td>
        </tr>
    </table>

    <table class="meta-table">
        <tr>
            <td class="meta-label">Topik Deviasi:</td>
            <td class="meta-val" colspan="3"><strong>{{ $deviation->topik }}</strong></td>
        </tr>
        <tr>
            <td class="meta-label">Departemen:</td>
            <td class="meta-val">{{ $deviation->department }} (PIC: {{ $deviation->pic ?? $deviation->initiator->name }})</td>
            <td class="meta-label">Identifikasi CPOB:</td>
            <td class="meta-val">
                <span class="badge {{ $deviation->identifikasi_penyimpangan === 'Kritis' ? 'badge-kritis' : ($deviation->identifikasi_penyimpangan === 'Mayor' ? 'badge-mayor' : 'badge-minor') }}">
                    {{ $deviation->identifikasi_penyimpangan }}
                </span>
            </td>
        </tr>
        <tr>
            <td class="meta-label">Nomor Bets:</td>
            <td class="meta-val">{{ $deviation->nomor_bets ?? '-' }}</td>
            <td class="meta-label">Mesin / Alat:</td>
            <td class="meta-val">{{ $deviation->nomor_alat ?? '-' }}</td>
        </tr>
        <tr>
            <td class="meta-label">Objek Terdampak:</td>
            <td class="meta-val">{{ $deviation->objek_terdampak ?? '-' }}</td>
            <td class="meta-label">Produksi Dihentikan:</td>
            <td class="meta-val">{{ $deviation->is_production_stopped ? 'YA (Dihentikan Sementara)' : 'TIDAK' }}</td>
        </tr>
    </table>

    <div class="section-header">1. Tindakan Segera di Lapangan (Immediate Action)</div>
    <div class="content-box">
        {{ $deviation->immediate_action_details ?? 'Telah dilakukan isolasi bets terdampak dan penandaan status Karantina QC.' }}
    </div>

    <div class="section-header">2. Investigasi Diagram Tulang Ikan (6M Ishikawa Fishbone)</div>
    <table class="fishbone-grid">
        <tr>
            <td>
                <div class="fishbone-cat">1. Man (Manusia)</div>
                @php $man = $deviation->fishbone_6m['man'] ?? []; @endphp
                @forelse($man as $m)
                    <div>&bull; {{ $m }}</div>
                @empty
                    <div style="color: #94a3b8; font-style: italic;">Tidak ada temuan signifikan.</div>
                @endforelse
            </td>
            <td>
                <div class="fishbone-cat">2. Machine (Mesin & Alat)</div>
                @php $machine = $deviation->fishbone_6m['machine'] ?? []; @endphp
                @forelse($machine as $m)
                    <div>&bull; {{ $m }}</div>
                @empty
                    <div style="color: #94a3b8; font-style: italic;">Tidak ada temuan signifikan.</div>
                @endforelse
            </td>
            <td>
                <div class="fishbone-cat">3. Method (Metode & SOP)</div>
                @php $method = $deviation->fishbone_6m['method'] ?? []; @endphp
                @forelse($method as $m)
                    <div>&bull; {{ $m }}</div>
                @empty
                    <div style="color: #94a3b8; font-style: italic;">Tidak ada temuan signifikan.</div>
                @endforelse
            </td>
        </tr>
        <tr>
            <td>
                <div class="fishbone-cat">4. Material (Bahan & Pengemas)</div>
                @php $materials = $deviation->fishbone_6m['materials'] ?? []; @endphp
                @forelse($materials as $m)
                    <div>&bull; {{ $m }}</div>
                @empty
                    <div style="color: #94a3b8; font-style: italic;">Tidak ada temuan signifikan.</div>
                @endforelse
            </td>
            <td>
                <div class="fishbone-cat">5. Milieu (Lingkungan & Fasilitas)</div>
                @php $milieu = $deviation->fishbone_6m['milieu'] ?? []; @endphp
                @forelse($milieu as $m)
                    <div>&bull; {{ $m }}</div>
                @empty
                    <div style="color: #94a3b8; font-style: italic;">Tidak ada temuan signifikan.</div>
                @endforelse
            </td>
            <td>
                <div class="fishbone-cat">6. Measurement (Pengukuran/Metrologi)</div>
                @php $meas = $deviation->fishbone_6m['measurement'] ?? []; @endphp
                @forelse($meas as $m)
                    <div>&bull; {{ $m }}</div>
                @empty
                    <div style="color: #94a3b8; font-style: italic;">Tidak ada temuan signifikan.</div>
                @endforelse
            </td>
        </tr>
    </table>

    <div class="section-header">3. Analisis Akar Masalah (5-Why Analysis)</div>
    <div class="content-box">
        @php $fiveWhys = $deviation->five_why ?? []; @endphp
        @forelse($fiveWhys as $idx => $why)
            <div style="margin-bottom: 2px;"><strong>Why {{ $idx + 1 }}:</strong> {{ $why }}</div>
        @empty
            <div>Penelusuran akar masalah berdasarkan investigasi teknis mendalam.</div>
        @endforelse
        <div style="margin-top: 4px; padding-top: 4px; border-top: 1px dashed #cbd5e1;">
            <strong style="color: #0f766e;">Akar Penyebab Utama (Root Cause):</strong> {{ $deviation->root_cause ?? 'Dalam proses konfirmasi teknis.' }}
        </div>
    </div>

    <div class="section-header">4. Umpan Balik Atasan & Keputusan QA (Auto-Spawn CAPA)</div>
    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 25%;">Atasan Mengetahui</th>
                <th style="width: 50%;">Tanggapan & Verifikasi Lapangan</th>
                <th style="width: 25%; text-align: center;">Tanda Tangan & Tanggal</th>
            </tr>
        </thead>
        <tbody>
            @php $mengetahui = $deviation->mengetahui_data ?? []; @endphp
            @forelse($mengetahui as $m)
            <tr>
                <td><strong>{{ $m['name'] ?? 'Atasan' }}</strong><br><span style="font-size: 7.5pt; color: #64748b;">{{ $m['role'] ?? '-' }}</span></td>
                <td>{{ $m['feedback'] ?? 'Telah diperiksa dan diverifikasi di area kerja.' }}</td>
                <td style="text-align: center; font-size: 7.5pt;">
                    <span style="color: #0f766e; font-weight: bold;">SIGNED</span><br>
                    {{ $m['feedback_at'] ?? date('d/m/Y') }}
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="3" style="text-align: center; color: #94a3b8; font-style: italic;">Tidak ada catatan atasan tambahan.</td>
            </tr>
            @endforelse
        </tbody>
    </table>

    <table class="sign-table">
        <tr>
            <td>
                <strong>Pelapor (Inisiator)</strong>
                <div class="sign-space"></div>
                <strong>{{ $deviation->initiator->name ?? 'Inisiator' }}</strong><br>
                <span style="font-size: 7.5pt; color: #64748b;">Tgl: {{ \Carbon\Carbon::parse($deviation->tanggal_temuan)->format('d/m/Y') }}</span>
            </td>
            <td>
                <strong>Tim Investigasi (QA/QC)</strong>
                <div class="sign-space">
                    <span style="color: #0f766e; font-size: 8pt; font-weight: bold;">INVESTIGATION VERIFIED</span>
                </div>
                <strong>QA Lead Inspector</strong><br>
                <span style="font-size: 7.5pt; color: #64748b;">Status: {{ $deviation->status }}</span>
            </td>
            <td>
                <strong>Keputusan Akhir (Head of QA)</strong>
                <div class="sign-space">
                    @if($deviation->status === 'APPROVED')
                        <span style="color: #0f766e; font-size: 8pt; font-weight: bold;">APPROVED &bullet; CAPA SPAWNED</span>
                    @endif
                </div>
                <strong>Head of Quality</strong><br>
                <span style="font-size: 7.5pt; color: #64748b;">Ref CAPA: {{ $deviation->capa->capa_number ?? 'Auto-Spawned' }}</span>
            </td>
        </tr>
    </table>

    <div style="margin-top: 10px; font-size: 7pt; color: #94a3b8; text-align: right;">
        Dokumen Resmi Penyelidikan Mutu CPOB HerbaTech &bullet; Dicetak pada {{ $printedAt }}
    </div>
</body>
</html>
