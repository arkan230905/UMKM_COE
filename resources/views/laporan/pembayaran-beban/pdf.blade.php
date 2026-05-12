<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Laporan Pembayaran Beban - {{ $selectedMonth->format('F Y') }}</title>
    <style>
        body { font-family: Arial, sans-serif; font-size: 12px; }
        .header { text-align: center; margin-bottom: 20px; }
        .header h2 { margin: 0; padding: 0; color: #000; }
        .header p { margin: 5px 0; color: #666; }
        .summary { margin-bottom: 20px; }
        .summary-row { display: flex; justify-content: space-between; margin-bottom: 5px; }
        .summary-label { font-weight: bold; color: #000; }
        .summary-value { text-align: right; color: #000; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        th, td { border: 1px solid #8B4513; padding: 5px; }
        th { background-color: #D2B48C; color: #000; text-align: center; font-weight: bold; }
        td { color: #000; }
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .text-success { color: #28a745; font-weight: bold; }
        .text-danger { color: #dc3545; font-weight: bold; }
        .text-muted { color: #666; }
        .footer { margin-top: 30px; text-align: right; color: #666; }
        .keterangan { margin-top: 20px; font-size: 11px; color: #666; }
        .keterangan strong { color: #000; }
    </style>
</head>
<body>
    <div class="header">
        <h2>LAPORAN PEMBAYARAN BEBAN</h2>
        <p>Periode: {{ $selectedMonth->format('F Y') }}</p>
        <p>Tanggal Cetak: {{ now()->format('d/m/Y H:i:s') }}</p>
    </div>

    <div class="summary">
        <div class="summary-row">
            <span class="summary-label">Total Budget:</span>
            <span class="summary-value">{{ format_rupiah($summary->total_budget) }}</span>
        </div>
        <div class="summary-row">
            <span class="summary-label">Total Aktual:</span>
            <span class="summary-value">{{ format_rupiah($summary->total_aktual) }}</span>
        </div>
        <div class="summary-row">
            <span class="summary-label">Total Selisih:</span>
            <span class="summary-value {{ $summary->total_selisih < 0 ? 'text-danger' : 'text-success' }}">
                {{ format_rupiah($summary->total_selisih) }}
            </span>
        </div>
        <div class="summary-row">
            <span class="summary-label">Status Keseluruhan:</span>
            <span class="summary-value {{ $summary->overall_status_color == 'danger' ? 'text-danger' : 'text-success' }}">
                {{ $summary->overall_status }}
            </span>
        </div>
    </div>

    <table>
        <thead>
            <tr>
                <th width="5%">No</th>
                <th width="15%">Kategori</th>
                <th width="30%">Nama Beban</th>
                <th width="15%">Budget Bulanan</th>
                <th width="15%">Aktual Bulan Ini</th>
                <th width="15%">Selisih</th>
                <th width="10%">Status</th>
            </tr>
        </thead>
        <tbody>
            @forelse($laporanData as $item)
            <tr>
                <td class="text-center">{{ $loop->iteration }}</td>
                <td class="text-muted">{{ $item->kategori }}</td>
                <td>{{ $item->nama_beban }}</td>
                <td class="text-right">{{ format_rupiah($item->budget_bulanan) }}</td>
                <td class="text-right">{{ format_rupiah($item->aktual_bulan_ini) }}</td>
                <td class="text-right {{ $item->selisih < 0 ? 'text-danger' : 'text-success' }}">
                    {{ format_rupiah($item->selisih) }}
                </td>
                <td class="text-center {{ $item->status_color == 'danger' ? 'text-danger' : 'text-success' }}">
                    {{ $item->status }}
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="7" class="text-center text-muted">Tidak ada data beban operasional</td>
            </tr>
            @endforelse
        </tbody>
        <tfoot>
            <tr style="background-color: #D2B48C; font-weight: bold;">
                <td colspan="3" class="text-right">TOTAL</td>
                <td class="text-right">{{ format_rupiah($summary->total_budget) }}</td>
                <td class="text-right">{{ format_rupiah($summary->total_aktual) }}</td>
                <td class="text-right {{ $summary->total_selisih < 0 ? 'text-danger' : 'text-success' }}">
                    {{ format_rupiah($summary->total_selisih) }}
                </td>
                <td class="text-center {{ $summary->overall_status_color == 'danger' ? 'text-danger' : 'text-success' }}">
                    {{ $summary->overall_status }}
                </td>
            </tr>
        </tfoot>
    </table>

    @if($laporanData->count() > 0)
    <div class="keterangan">
        <p><strong>Keterangan:</strong></p>
        <ul>
            <li>Budget Bulanan diambil dari master data Beban Operasional</li>
            <li>Aktual Bulan Ini adalah total transaksi pembayaran beban pada periode terpilih</li>
            <li>Selisih = Budget Bulanan - Aktual Bulan Ini</li>
            <li>Status "Aman" jika Aktual ≤ Budget</li>
            <li>Status "Over Budget" jika Aktual > Budget</li>
        </ul>
    </div>
    @endif

    <div class="footer">
        <p>Generated by UMKM COE System</p>
    </div>
</body>
</html>
