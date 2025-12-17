<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ringkasan Presensi - {{ $bulanLabel }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: Arial, sans-serif;
            color: #333;
            line-height: 1.6;
        }

        .container {
            max-width: 900px;
            margin: 0 auto;
            padding: 20px;
        }

        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 3px solid #333;
            padding-bottom: 20px;
        }

        .header h1 {
            font-size: 22px;
            font-weight: bold;
            margin-bottom: 5px;
        }

        .header p {
            font-size: 12px;
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
            font-size: 12px;
        }

        .info-label {
            font-weight: bold;
            display: inline-block;
            width: 120px;
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
            padding: 10px;
            text-align: left;
            font-weight: 600;
            font-size: 12px;
            border: 1px solid #ddd;
        }

        td {
            padding: 8px 10px;
            border: 1px solid #ddd;
            font-size: 12px;
        }

        tbody tr:nth-child(even) {
            background-color: #f9f9f9;
        }

        .text-right {
            text-align: right;
        }

        .text-center {
            text-align: center;
        }

        .footer {
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #ddd;
            text-align: center;
            font-size: 11px;
            color: #999;
        }

        .no-data {
            text-align: center;
            padding: 40px;
            color: #999;
            font-size: 12px;
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Header -->
        <div class="header">
            <h1>RINGKASAN PRESENSI BULANAN</h1>
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
                <strong>{{ count($ringkasan) }}</strong>
            </p>
        </div>

        <!-- Table -->
        @if(count($ringkasan) > 0)
            <table>
                <thead>
                    <tr>
                        <th style="width: 5%;">No</th>
                        <th style="width: 25%;">Nama Pegawai</th>
                        <th style="width: 12%;" class="text-center">Hadir</th>
                        <th style="width: 15%;" class="text-center">Total Jam Kerja</th>
                        <th style="width: 12%;" class="text-center">Sakit</th>
                        <th style="width: 12%;" class="text-center">Izin</th>
                        <th style="width: 12%;" class="text-center">Alpha</th>
                    </tr>
                </thead>
                <tbody>
                    @php $no = 1; @endphp
                    @foreach($ringkasan as $item)
                        <tr>
                            <td class="text-center">{{ $no++ }}</td>
                            <td>
                                <strong>{{ $item['nama_pegawai'] }}</strong>
                                <br>
                                <small style="color: #999;">NIP: {{ $item['kode_pegawai'] }}</small>
                            </td>
                            <td class="text-center">{{ $item['total_hadir'] }}</td>
                            <td class="text-center">{{ number_format($item['total_jam_kerja'], 2) }} jam</td>
                            <td class="text-center">{{ $item['total_sakit'] }}</td>
                            <td class="text-center">{{ $item['total_izin'] }}</td>
                            <td class="text-center">{{ $item['total_alpha'] }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @else
            <div class="no-data">
                <p>Tidak ada data presensi untuk bulan {{ $bulanLabel }}</p>
            </div>
        @endif

        <!-- Footer -->
        <div class="footer">
            <p>Dokumen ini dicetak secara otomatis oleh sistem UMKM COE</p>
            <p>{{ now()->format('d F Y H:i:s') }}</p>
        </div>
    </div>
</body>
</html>
