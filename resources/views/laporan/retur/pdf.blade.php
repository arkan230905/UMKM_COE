<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Laporan Retur - {{ now()->format('d-m-Y') }}</title>
    <style>
        body { font-family: Arial, sans-serif; font-size: 12px; }
        .header { text-align: center; margin-bottom: 20px; }
        .header h2 { margin: 0; padding: 0; }
        .header p { margin: 5px 0; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        th, td { border: 1px solid #000; padding: 5px; }
        th { background-color: #f2f2f2; text-align: center; }
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .footer { margin-top: 30px; text-align: right; }
    </style>
</head>
<body>
    <div class="header">
        <h2>LAPORAN RETUR</h2>
        <p>Periode: {{ request('bulan') ? \Carbon\Carbon::parse(request('bulan'))->format('F Y') : 'Semua Data' }}</p>
        <p>Tanggal Cetak: {{ now()->format('d/m/Y H:i:s') }}</p>
    </div>

    <table>
        <thead>
            <tr>
                <th>No</th>
                <th>Tanggal</th>
                <th>No. Retur</th>
                <th>Customer</th>
                <th>No. Penjualan</th>
                <th>Total</th>
            </tr>
        </thead>
        <tbody>
            @forelse($returs as $retur)
            <tr>
                <td class="text-center">{{ $loop->iteration }}</td>
                <td>{{ $retur->tanggal->format('d/m/Y') }}</td>
                <td>{{ $retur->no_retur }}</td>
                <td>{{ $retur->customer->nama_customer ?? '-' }}</td>
                <td>{{ $retur->penjualan->no_penjualan ?? '-' }}</td>
                <td class="text-right">{{ format_rupiah($retur->total) }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="6" class="text-center">Tidak ada data retur</td>
            </tr>
            @endforelse
        </tbody>
        <tfoot>
            <tr>
                <th colspan="5" class="text-right">Total</th>
                <th class="text-right">{{ format_rupiah($total) }}</th>
            </tr>
        </tfoot>
    </table>

    <div class="footer">
        <p>Dicetak oleh: {{ Auth::user()->name }}</p>
    </div>
</body>
</html>
