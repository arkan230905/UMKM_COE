<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Laporan Pelunasan Utang - {{ now()->format('d-m-Y') }}</title>
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
        .badge { padding: 3px 8px; border-radius: 3px; font-size: 11px; }
        .badge-success { background-color: #d4edda; color: #155724; }
        .badge-warning { background-color: #fff3cd; color: #856404; }
    </style>
</head>
<body>
    <div class="header">
        <h2>LAPORAN PELUNASAN UTANG</h2>
        <p>Periode: {{ request('bulan') ? \Carbon\Carbon::parse(request('bulan'))->format('F Y') : 'Semua Data' }}</p>
        <p>Tanggal Cetak: {{ now()->format('d/m/Y H:i:s') }}</p>
    </div>

    <table>
        <thead>
            <tr>
                <th>No</th>
                <th>Tanggal</th>
                <th>No. Pelunasan</th>
                <th>Vendor</th>
                <th>No. Faktur</th>
                <th>Total Tagihan</th>
                <th>Dibayar</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            @forelse($pelunasanUtang as $item)
            <tr>
                <td class="text-center">{{ $loop->iteration }}</td>
                <td>{{ $item->tanggal->format('d/m/Y') }}</td>
                <td>{{ $item->kode_transaksi }}</td>
                <td>{{ $item->pembelian->vendor->nama_vendor ?? '-' }}</td>
                <td>{{ $item->pembelian->nomor_faktur ?? $item->pembelian->nomor_pembelian ?? '-' }}</td>
                <td class="text-right">{{ format_rupiah($item->pembelian->total_harga ?? 0) }}</td>
                <td class="text-right">{{ format_rupiah($item->jumlah) }}</td>
                <td class="text-center">
                    @php
                        $statusPembayaran = $item->pembelian->status_pembayaran;
                    @endphp
                    @if($statusPembayaran === 'Lunas')
                        <span class="badge badge-success">Lunas</span>
                    @elseif($statusPembayaran === 'Sebagian')
                        <span class="badge badge-warning">Sebagian</span>
                    @else
                        <span class="badge badge-danger">Belum Bayar</span>
                    @endif
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="8" class="text-center">Tidak ada data pelunasan utang</td>
            </tr>
            @endforelse
        </tbody>
        <tfoot>
            <tr>
                <th colspan="5" class="text-right">Total</th>
                <th class="text-right">{{ format_rupiah($pelunasanUtang->sum(function($item) { return $item->pembelian->total_harga ?? 0; })) }}</th>
                <th class="text-right">{{ format_rupiah($total) }}</th>
                <th></th>
            </tr>
        </tfoot>
    </table>

    <div class="footer">
        <p>Dicetak oleh: {{ Auth::user()->name }}</p>
    </div>
</body>
</html>
