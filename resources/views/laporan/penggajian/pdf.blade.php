<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Laporan Penggajian - {{ now()->format('d-m-Y') }}</title>
    <style>
        body { font-family: Arial, sans-serif; font-size: 11px; margin: 10px; }
        .header { text-align: center; margin-bottom: 20px; }
        .header h2 { margin: 0; padding: 0; font-size: 16px; }
        .header p { margin: 3px 0; font-size: 11px; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        th, td { border: 1px solid #000; padding: 6px 4px; font-size: 10px; }
        th { background-color: #e0e0e0; text-align: center; font-weight: bold; }
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .footer { margin-top: 20px; text-align: right; font-size: 10px; }
        .badge { padding: 2px 4px; border-radius: 3px; font-size: 9px; }
        .badge-info { background-color: #d1ecf1; color: #0c5460; }
        .badge-secondary { background-color: #e2e3e5; color: #383d41; }
    </style>
</head>
<body>
    <div class="header">
        <h2>LAPORAN PENGGAJIAN</h2>
        <p>Periode: {{ $bulan ?? 'Semua Data' }}</p>
        <p>Tanggal Cetak: {{ now()->format('d/m/Y H:i:s') }}</p>
    </div>

    <table>
        <thead>
            <tr>
                <th style="width: 3%;">No</th>
                <th style="width: 10%;">Tanggal</th>
                <th style="width: 15%;">Nama Pegawai</th>
                <th style="width: 7%;">Jenis</th>
                <th style="width: 12%;">Gaji Pokok / Tarif</th>
                <th style="width: 8%;">Jam Kerja</th>
                <th style="width: 10%;">Tunjangan</th>
                <th style="width: 10%;">Asuransi</th>
                <th style="width: 8%;">Bonus</th>
                <th style="width: 10%;">Potongan</th>
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
                <td class="text-center">{{ \Carbon\Carbon::parse($penggajian->tanggal_penggajian)->format('d-m-Y') }}</td>
                <td>{{ $penggajian->pegawai->nama ?? '-' }}</td>
                <td class="text-center">
                    <span class="badge {{ $jenis === 'BTKL' ? 'badge-info' : 'badge-secondary' }}">
                        {{ $jenis }}
                    </span>
                </td>
                <td class="text-right">
                    @if($jenis === 'BTKL')
                        {{ number_format($penggajian->tarif_per_jam ?? 0, 0, ',', '.') }}
                    @else
                        {{ number_format($penggajian->gaji_pokok ?? 0, 0, ',', '.') }}
                    @endif
                </td>
                <td class="text-right">
                    @if($jenis === 'BTKL')
                        {{ number_format($penggajian->total_jam_kerja ?? 0, 0, ',', '.') }} jam
                    @else
                        -
                    @endif
                </td>
                <td class="text-right">Rp {{ number_format($penggajian->tunjangan ?? 0, 0, ',', '.') }}</td>
                <td class="text-right">Rp {{ number_format($penggajian->asuransi ?? 0, 0, ',', '.') }}</td>
                <td class="text-right">Rp {{ number_format($penggajian->bonus ?? 0, 0, ',', '.') }}</td>
                <td class="text-right">Rp {{ number_format($penggajian->potongan ?? 0, 0, ',', '.') }}</td>
                <td class="text-right"><strong>Rp {{ number_format($penggajian->total_gaji, 0, ',', '.') }}</strong></td>
            </tr>
            @empty
            <tr>
                <td colspan="11" class="text-center">Tidak ada data penggajian</td>
            </tr>
            @endforelse
        </tbody>
        <tfoot>
            <tr>
                <th colspan="10" class="text-right">Total Keseluruhan</th>
                <th class="text-right">Rp {{ number_format($total, 0, ',', '.') }}</th>
            </tr>
        </tfoot>
    </table>

    <div class="footer">
        <p>Dicetak oleh: {{ Auth::user()->name ?? 'System' }} | {{ now()->format('d/m/Y H:i:s') }}</p>
    </div>
</body>
</html>
