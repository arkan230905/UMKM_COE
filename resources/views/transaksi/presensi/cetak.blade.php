<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan Presensi</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            line-height: 1.4;
            color: #333;
            padding: 20px;
        }

        .header {
            text-align: center;
            margin-bottom: 20px;
            border-bottom: 2px solid #8B7355;
            padding-bottom: 15px;
        }

        .company-name {
            font-size: 20px;
            font-weight: bold;
            color: #8B7355;
            margin-bottom: 5px;
        }

        .document-title {
            font-size: 16px;
            font-weight: bold;
            color: #333;
        }

        .report-info {
            margin-bottom: 15px;
            font-size: 11px;
        }

        .report-info table {
            width: 100%;
        }

        .report-info td {
            padding: 3px 0;
        }

        table.data-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }

        table.data-table thead th {
            background-color: #8B7355;
            color: white;
            padding: 8px;
            text-align: left;
            font-weight: bold;
            border: 1px solid #8B7355;
        }

        table.data-table thead th.text-center {
            text-align: center;
        }

        table.data-table tbody td {
            padding: 6px 8px;
            border: 1px solid #ddd;
            vertical-align: middle;
        }

        table.data-table tbody tr:nth-child(even) {
            background-color: #f9f9f9;
        }

        table.data-table tbody tr:hover {
            background-color: #f0f0f0;
        }

        .text-center {
            text-align: center;
        }

        .badge {
            display: inline-block;
            padding: 3px 8px;
            border-radius: 3px;
            font-size: 10px;
            font-weight: bold;
            text-transform: uppercase;
        }

        .badge-hadir {
            background-color: #28a745;
            color: white;
        }

        .badge-terlambat {
            background-color: #ffc107;
            color: #333;
        }

        .badge-izin {
            background-color: #17a2b8;
            color: white;
        }

        .badge-sakit {
            background-color: #6c757d;
            color: white;
        }

        .badge-alpha {
            background-color: #dc3545;
            color: white;
        }

        .footer {
            margin-top: 30px;
            text-align: center;
            font-size: 10px;
            color: #666;
            border-top: 1px solid #ddd;
            padding-top: 15px;
        }

        .signature-section {
            margin-top: 40px;
            display: flex;
            justify-content: space-between;
        }

        .signature-box {
            width: 200px;
            text-align: center;
        }

        .signature-line {
            border-top: 1px solid #333;
            margin-top: 50px;
            padding-top: 5px;
        }

        .summary-box {
            background-color: #f8f9fa;
            border: 1px solid #ddd;
            padding: 10px;
            margin-bottom: 15px;
            border-radius: 4px;
        }

        .summary-title {
            font-weight: bold;
            margin-bottom: 5px;
            color: #8B7355;
        }

        @media print {
            body {
                padding: 0;
            }

            .no-print {
                display: none !important;
            }

            .header {
                border-bottom: 2px solid #000;
            }

            table.data-table thead th {
                background-color: #ddd !important;
                color: #000 !important;
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }

            .badge-hadir { background-color: #ddd !important; color: #000 !important; }
            .badge-terlambat { background-color: #ddd !important; color: #000 !important; }
            .badge-izin { background-color: #ddd !important; color: #000 !important; }
            .badge-sakit { background-color: #ddd !important; color: #000 !important; }
            .badge-alpha { background-color: #ddd !important; color: #000 !important; }
        }
    </style>
</head>
<body>
    <!-- Print Button -->
    <div class="no-print" style="text-align: right; margin-bottom: 15px;">
        <button onclick="window.print()" style="padding: 8px 16px; background-color: #8B7355; color: white; border: none; border-radius: 4px; cursor: pointer;">
            <i class="fas fa-print"></i> Cetak Laporan
        </button>
        <button onclick="window.close()" style="padding: 8px 16px; background-color: #6c757d; color: white; border: none; border-radius: 4px; cursor: pointer; margin-left: 5px;">
            Tutup
        </button>
    </div>

    <!-- Header -->
    <div class="header">
        <div class="company-name">TIM COE PROSES COSTING</div>
        <div class="document-title">LAPORAN PRESENSI PEGAWAI</div>
    </div>

    <!-- Report Info -->
    <div class="report-info">
        <table>
            <tr>
                <td width="15%"><strong>Periode</strong></td>
                <td width="35%">: {{ $dateFilter ? \Carbon\Carbon::parse($dateFilter)->format('d F Y') : 'Semua Tanggal' }}</td>
                <td width="15%"><strong>Tanggal Cetak</strong></td>
                <td width="35%">: {{ now()->format('d F Y H:i') }}</td>
            </tr>
            @if($search)
            <tr>
                <td><strong>Filter Pegawai</strong></td>
                <td colspan="3">: {{ $search }}</td>
            </tr>
            @endif
        </table>
    </div>

    <!-- Summary -->
    <div class="summary-box">
        <div class="summary-title">Ringkasan:</div>
        <table style="width: 100%; font-size: 11px;">
            <tr>
                @php
                    $total = $presensis->count();
                    $hadir = $presensis->where('status', 'hadir')->count();
                    $terlambat = $presensis->where('status', 'terlambat')->count();
                    $izin = $presensis->where('status', 'izin')->count();
                    $sakit = $presensis->where('status', 'sakit')->count();
                    $alpha = $presensis->where('status', 'alpha')->count();
                @endphp
                <td><strong>Total Presensi:</strong> {{ $total }}</td>
                <td><strong>Hadir:</strong> {{ $hadir }}</td>
                <td><strong>Terlambat:</strong> {{ $terlambat }}</td>
                <td><strong>Izin:</strong> {{ $izin }}</td>
                <td><strong>Sakit:</strong> {{ $sakit }}</td>
                <td><strong>Alpha:</strong> {{ $alpha }}</td>
            </tr>
        </table>
    </div>

    <!-- Data Table -->
    <table class="data-table">
        <thead>
            <tr>
                <th class="text-center" width="5%">No</th>
                <th width="25%">Nama Pegawai</th>
                <th class="text-center" width="12%">Tanggal</th>
                <th class="text-center" width="10%">Jam Masuk</th>
                <th class="text-center" width="10%">Jam Keluar</th>
                <th class="text-center" width="10%">Jumlah Jam</th>
                <th class="text-center" width="10%">Status</th>
                <th width="18%">Keterangan</th>
            </tr>
        </thead>
        <tbody>
            @forelse($presensis as $presensi)
            <tr>
                <td class="text-center">{{ $loop->iteration }}</td>
                <td>
                    <strong>{{ $presensi->pegawai->nama ?? 'Pegawai Tidak Ditemukan' }}</strong><br>
                    <small style="color: #666;">{{ $presensi->pegawai->kode_pegawai ?? '-' }}</small>
                </td>
                <td class="text-center">{{ $presensi->tgl_presensi ? $presensi->tgl_presensi->format('d/m/Y') : '-' }}</td>
                <td class="text-center">{{ $presensi->jam_masuk ? date('H:i', strtotime($presensi->jam_masuk)) : '-' }}</td>
                <td class="text-center">{{ $presensi->jam_keluar ? date('H:i', strtotime($presensi->jam_keluar)) : '-' }}</td>
                <td class="text-center">{{ $presensi->jumlah_jam ? $presensi->jumlah_jam . ' jam' : '-' }}</td>
                <td class="text-center">
                    <span class="badge badge-{{ $presensi->status }}">{{ ucfirst($presensi->status) }}</span>
                </td>
                <td>{{ $presensi->keterangan ?? '-' }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="8" class="text-center" style="padding: 20px;">
                    Tidak ada data presensi untuk periode yang dipilih.
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>

    <!-- Footer -->
    <div class="footer">
        <p>Dokumen ini dicetak secara otomatis dari sistem presensi.</p>
        <p>TIM COE PROSES COSTING &copy; {{ now()->year }}</p>
    </div>

    <!-- Signature Section -->
    <div class="signature-section no-print" style="display: none;">
        <div class="signature-box">
            <div>Mengetahui,</div>
            <div class="signature-line">(_________________)</div>
            <div style="margin-top: 5px;">Manager HRD</div>
        </div>
        <div class="signature-box">
            <div>Dibuat oleh,</div>
            <div class="signature-line">(_________________)</div>
            <div style="margin-top: 5px;">{{ auth()->user()->name ?? 'Admin' }}</div>
        </div>
    </div>
</body>
</html>
