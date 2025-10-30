<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Laporan Penggajian - {{ now()->format('d-m-Y') }}</title>
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
        <h2>LAPORAN PENGAJIAN</h2>
        <p>Periode: {{ request('bulan') ? \Carbon\Carbon::parse(request('bulan'))->format('F Y') : 'Semua Data' }}</p>
        <p>Tanggal Cetak: {{ now()->format('d/m/Y H:i:s') }}</p>
    </div>

    <table>
        <thead>
            <tr>
                <th>No</th>
                <th>Periode</th>
                <th>Nama Pegawai</th>
                <th>Gaji Pokok</th>
                <th>Tunjangan</th>
                <th>Bonus</th>
                <th>Potongan</th>
                <th>Total</th>
            </tr>
        </thead>
        <tbody>
            @forelse($penggajians as $penggajian)
            <tr>
                <td class="text-center">{{ $loop->iteration }}</td>
                <td>{{ $penggajian->periode->format('F Y') }}</td>
                <td>{{ $penggajian->pegawai->nama_pegawai }}</td>
                <td class="text-right">{{ format_rupiah($penggajian->gaji_pokok) }}</td>
                <td class="text-right">{{ format_rupiah($penggajian->tunjangan) }}</td>
                <td class="text-right">{{ format_rupiah($penggajian->bonus) }}</td>
                <td class="text-right">{{ format_rupiah($penggajian->potongan) }}</td>
                <td class="text-right">{{ format_rupiah($penggajian->total_gaji) }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="8" class="text-center">Tidak ada data penggajian</td>
            </tr>
            @endforelse
        </tbody>
        <tfoot>
            <tr>
                <th colspan="7" class="text-right">Total</th>
                <th class="text-right">{{ format_rupiah($total) }}</th>
            </tr>
        </tfoot>
    </table>

    <div class="footer">
        <p>Dicetak oleh: {{ Auth::user()->name }}</p>
    </div>
</body>
</html>
