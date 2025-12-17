<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rekap Presensi - {{ $bulanLabel }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            color: #333;
            line-height: 1.6;
            background-color: #f5f5f5;
        }

        .container {
            max-width: 900px;
            margin: 0 auto;
            padding: 20px;
            background-color: white;
        }

        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 3px solid #333;
            padding-bottom: 20px;
        }

        .header h1 {
            font-size: 24px;
            font-weight: bold;
            margin-bottom: 5px;
            color: #333;
        }

        .header p {
            font-size: 14px;
            color: #666;
            margin: 3px 0;
        }

        .info-section {
            margin-bottom: 20px;
            padding: 10px;
            background-color: #f9f9f9;
            border-left: 4px solid #007bff;
        }

        .info-section p {
            margin: 5px 0;
            font-size: 13px;
        }

        .info-label {
            font-weight: bold;
            color: #333;
            display: inline-block;
            width: 150px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }

        thead {
            background-color: #007bff;
            color: white;
        }

        th {
            padding: 12px;
            text-align: left;
            font-weight: 600;
            font-size: 13px;
            border: 1px solid #ddd;
        }

        td {
            padding: 10px 12px;
            border: 1px solid #ddd;
            font-size: 13px;
        }

        tbody tr:nth-child(even) {
            background-color: #f9f9f9;
        }

        tbody tr:hover {
            background-color: #f0f0f0;
        }

        .status-hadir {
            background-color: #d4edda;
            color: #155724;
            padding: 4px 8px;
            border-radius: 3px;
            font-weight: 500;
            display: inline-block;
        }

        .status-sakit {
            background-color: #fff3cd;
            color: #856404;
            padding: 4px 8px;
            border-radius: 3px;
            font-weight: 500;
            display: inline-block;
        }

        .status-izin {
            background-color: #cfe2ff;
            color: #084298;
            padding: 4px 8px;
            border-radius: 3px;
            font-weight: 500;
            display: inline-block;
        }

        .status-absen {
            background-color: #f8d7da;
            color: #721c24;
            padding: 4px 8px;
            border-radius: 3px;
            font-weight: 500;
            display: inline-block;
        }

        .waktu-presensi {
            font-weight: 500;
            color: #007bff;
        }

        .footer {
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #ddd;
            text-align: center;
            font-size: 12px;
            color: #999;
        }

        .no-data {
            text-align: center;
            padding: 40px;
            color: #999;
            font-size: 14px;
        }

        @media print {
            body {
                background-color: white;
            }
            .container {
                padding: 0;
                margin: 0;
            }
            .footer {
                display: none;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Header -->
        <div class="header">
            <h1>REKAP PRESENSI BULANAN</h1>
            <p>Periode: <strong>{{ $bulanLabel }}</strong></p>
            <p>Tanggal Cetak: {{ now()->format('d F Y H:i:s') }}</p>
        </div>

        <!-- Info Section -->
        <div class="info-section">
            <p>
                <span class="info-label">Bulan:</span>
                <strong>{{ $bulanLabel }}</strong>
            </p>
            <p>
                <span class="info-label">Total Pegawai:</span>
                <strong>{{ $presensis->groupBy('pegawai_id')->count() }}</strong>
            </p>
            <p>
                <span class="info-label">Total Presensi:</span>
                <strong>{{ $presensis->count() }}</strong>
            </p>
        </div>

        <!-- Table -->
        @if($presensis->count() > 0)
            <table>
                <thead>
                    <tr>
                        <th style="width: 5%;">No</th>
                        <th style="width: 25%;">Nama Pegawai</th>
                        <th style="width: 15%;">Tanggal</th>
                        <th style="width: 20%;">Waktu Presensi</th>
                        <th style="width: 15%;">Status</th>
                    </tr>
                </thead>
                <tbody>
                    @php $no = 1; @endphp
                    @foreach($presensis as $presensi)
                        <tr>
                            <td>{{ $no++ }}</td>
                            <td>
                                <strong>{{ $presensi->pegawai->nama ?? '-' }}</strong>
                                <br>
                                <small style="color: #999;">NIP: {{ $presensi->pegawai->kode_pegawai ?? '-' }}</small>
                            </td>
                            <td>
                                {{ \Carbon\Carbon::parse($presensi->tgl_presensi)->isoFormat('dddd, D MMMM YYYY') }}
                            </td>
                            <td>
                                @if($presensi->status === 'Hadir')
                                    <span class="waktu-presensi">
                                        {{ \Carbon\Carbon::parse($presensi->jam_masuk)->format('H:i') }} - 
                                        {{ \Carbon\Carbon::parse($presensi->jam_keluar)->format('H:i') }}
                                    </span>
                                @else
                                    <span style="color: #999;">-</span>
                                @endif
                            </td>
                            <td>
                                @if($presensi->status === 'Hadir')
                                    <span class="status-hadir">✓ Hadir</span>
                                @elseif($presensi->status === 'Sakit')
                                    <span class="status-sakit">Sakit</span>
                                @elseif($presensi->status === 'Izin')
                                    <span class="status-izin">Izin</span>
                                @else
                                    <span class="status-absen">✗ Absen</span>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @else
            <div class="no-data">
                <p>📭 Tidak ada data presensi untuk bulan {{ $bulanLabel }}</p>
            </div>
        @endif

        <!-- Footer -->
        <div class="footer">
            <p>Dokumen ini dicetak secara otomatis oleh sistem UMKM COE</p>
            <p>{{ now()->format('d F Y H:i:s') }}</p>
        </div>
    </div>

    <script>
        // Auto print when page loads
        window.addEventListener('load', function() {
            window.print();
        });
    </script>
</body>
</html>
