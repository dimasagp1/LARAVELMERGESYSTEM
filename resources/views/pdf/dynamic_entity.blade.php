<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>{{ $entity->name }} Report</title>
    <style>
        body { font-family: sans-serif; font-size: 11px; color: #334155; }
        .header { border-bottom: 2px solid #059669; padding-bottom: 8px; margin-bottom: 12px; }
        .title { font-size: 16px; font-weight: bold; color: #0f172a; }
        .subtitle { font-size: 10px; color: #64748b; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th { background: #f8fafc; border: 1px solid #cbd5e1; padding: 6px; text-align: left; font-size: 10px; }
        td { border: 1px solid #cbd5e1; padding: 6px; font-size: 10px; }
        .badge { display: inline-block; padding: 2px 6px; border-radius: 4px; font-size: 9px; font-weight: bold; }
    </style>
</head>
<body>
    <div class="header">
        <div class="title">{{ config('app.name', 'HerbaTech ERP') }} • {{ $module->name }}</div>
        <div class="subtitle">Laporan Data Entitas: {{ $entity->name }} • Dicetak pada {{ now()->format('d M Y H:i:s') }}</div>
    </div>

    <table>
        <thead>
            <tr>
                <th>No. Dokumen</th>
                <th>Status</th>
                @foreach($fields->where('show_in_table', true) as $f)
                    <th>{{ $f->label }}</th>
                @endforeach
                <th>Tanggal</th>
            </tr>
        </thead>
        <tbody>
            @forelse($records as $rec)
                <tr>
                    <td><strong>{{ $rec->record_number }}</strong></td>
                    <td>{{ strtoupper($rec->status) }}</td>
                    @foreach($fields->where('show_in_table', true) as $f)
                        <td>{{ $rec->data_json[$f->field_key] ?? '-' }}</td>
                    @endforeach
                    <td>{{ $rec->created_at->format('Y-m-d') }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="{{ $fields->where('show_in_table', true)->count() + 3 }}" style="text-align: center; color: #94a3b8;">
                        Tidak ada catatan data.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</body>
</html>
