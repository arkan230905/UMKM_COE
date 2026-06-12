<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Laporan Penggajian - {{ now()->format('d-m-Y') }}</title>
    <style>
        body { font-family: Arial, sans-serif; font-size: 10px; }
        .header { text-align: center; margin-bottom: 20px; }
        .header h2 { margin: 0; padding: 0; }
        .header p { margin: 5px 0; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 20px; font-size: 9px; }
        th, td { border: 1px solid #000; padding: 4px; }
        th { background-color: #f2f2f2; text-align: center; font-weight: bold; }
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .footer { margin-top: 30px; text-align: right; }
        .badge { padding: 2px 6px; border-radius: 3px; font-size: 8px; }
        .badge-info { background-color: #17a2b8; color: white; }
        .badge-secondary { background-color: #6c757d; color: white; }
    </style>
</head>
<body>
    <div class="header">
        <h2>LAPORAN PENGGAJIAN</h2>
        <p>Periode: {{ request('bulan') ? \Carbon\Carbon::parse(request('bulan'))->format('F Y') : 'Semua Data' }}</p>
        <p>Tanggal Cetak: {{ now()->format('d/m/Y H:i:s') }}</p>
    </div>

    <table>
        <thead>
            <tr>
                <th style="width: 3%;">No</th>
                <th style="width: 11%;">Periode</th>
                <th style="width: 16%;">Nama Pegawai</th>
                <th style="width: 6%;">Kategori</th>
                <th style="width: 9%;">Tanggal</th>
                <th style="width: 10%;">Total Produksi</th>
                <th style="width: 12%;">Gaji Pokok</th>
                <th style="width: 10%;">Tunjangan</th>
                <th style="width: 9%;">Asuransi</th>
                <th style="width: 8%;">Bonus</th>
                <th style="width: 9%;">Potongan</th>
                <th style="width: 12%;">Total Gaji</th>
            </tr>
        </thead>
        <tbody>
            @forelse($penggajians as $penggajian)
            @php
                $jenis = strtoupper($penggajian->pegawai->jenis_pegawai ?? 'BTKTL');
            @endphp
            <tr>
                <td class="text-center">{{ $loop->iteration }}</td>
                <td>{{ $penggajian->tanggal_penggajian ? \Carbon\Carbon::parse($penggajian->tanggal_penggajian)->format('F Y') : '-' }}</td>
                <td>{{ $penggajian->pegawai->nama ?? '-' }}</td>
                <td class="text-center">
                    <span class="badge {{ $jenis === 'BTKL' ? 'badge-info' : 'badge-secondary' }}">
                        {{ $jenis }}
                    </span>
                </td>
                <td class="text-center">{{ $penggajian->tanggal_penggajian ? \Carbon\Carbon::parse($penggajian->tanggal_penggajian)->format('d-m-Y') : '-' }}</td>
                <td class="text-right">
                    {{ number_format($penggajian->total_produk_bulan ?? 0, 0) }} produk
                </td>
                <td class="text-right">
                    Rp {{ number_format($penggajian->gaji_pokok ?? 0, 0, ',', '.') }}
                </td>
                <td class="text-right">Rp {{ number_format($penggajian->total_tunjangan ?? 0, 0, ',', '.') }}</td>
                <td class="text-right">Rp {{ number_format($penggajian->asuransi ?? 0, 0, ',', '.') }}</td>
                <td class="text-right">Rp {{ number_format($penggajian->bonus ?? 0, 0, ',', '.') }}</td>
                <td class="text-right">Rp {{ number_format($penggajian->potongan ?? 0, 0, ',', '.') }}</td>
                <td class="text-right"><strong>Rp {{ number_format($penggajian->total_gaji, 0, ',', '.') }}</strong></td>
            </tr>
            @empty
            <tr>
                <td colspan="12" class="text-center">Tidak ada data penggajian</td>
            </tr>
            @endforelse
        </tbody>
        <tfoot>
            <tr>
                <th colspan="11" class="text-right">Total Keseluruhan</th>
                <th class="text-right">Rp {{ number_format($total, 0, ',', '.') }}</th>
            </tr>
        </tfoot>
    </table>

    <div class="footer">
        <p>Dicetak oleh: {{ Auth::user()->name ?? 'Admin' }}</p>
    </div>
</body>
</html>
