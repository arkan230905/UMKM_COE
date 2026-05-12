<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Slip Gaji - {{ $penggajian->pegawai->nama }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Arial', sans-serif;
            background-color: #f5f5f5;
            padding: 20px;
        }

        .container {
            max-width: 800px;
            margin: 0 auto;
            background-color: white;
            padding: 40px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }

        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 3px solid #8B6F47;
            padding-bottom: 20px;
        }

        .header h1 {
            font-size: 24px;
            color: #333;
            margin-bottom: 5px;
        }

        .header p {
            color: #666;
            font-size: 14px;
        }

        .employee-info {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            margin-bottom: 30px;
            padding: 15px;
            background-color: #f9f9f9;
            border-radius: 5px;
        }

        .info-item {
            font-size: 13px;
        }

        .info-item strong {
            display: block;
            color: #8B6F47;
            margin-bottom: 3px;
        }

        .info-item span {
            color: #333;
        }

        .section {
            margin-bottom: 25px;
        }

        .section-title {
            background-color: #8B6F47;
            color: white;
            padding: 10px 15px;
            font-weight: bold;
            margin-bottom: 10px;
            border-radius: 3px;
        }

        .attendance-summary {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 15px;
            margin-bottom: 20px;
        }

        .attendance-box {
            text-align: center;
            padding: 15px;
            background-color: #f0f0f0;
            border-radius: 5px;
            border-left: 4px solid #8B6F47;
        }

        .attendance-box h3 {
            font-size: 24px;
            color: #8B6F47;
            margin-bottom: 5px;
        }

        .attendance-box p {
            font-size: 12px;
            color: #666;
        }

        .salary-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }

        .salary-table th {
            background-color: #f0f0f0;
            padding: 10px;
            text-align: left;
            font-weight: bold;
            border-bottom: 2px solid #8B6F47;
            font-size: 13px;
        }

        .salary-table td {
            padding: 10px;
            border-bottom: 1px solid #ddd;
            font-size: 13px;
        }

        .salary-table tr:last-child td {
            border-bottom: 2px solid #8B6F47;
        }

        .text-right {
            text-align: right;
        }

        .amount {
            font-weight: bold;
            color: #333;
        }

        .total-row {
            background-color: #f9f9f9;
            font-weight: bold;
        }

        .total-row td {
            padding: 12px 10px;
            font-size: 14px;
        }

        .breakdown {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            margin-bottom: 30px;
        }

        .breakdown-item {
            display: flex;
            justify-content: space-between;
            padding: 8px 0;
            border-bottom: 1px solid #eee;
            font-size: 13px;
        }

        .breakdown-item strong {
            color: #333;
        }

        .breakdown-item.total {
            border-bottom: 2px solid #8B6F47;
            padding: 12px 0;
            font-size: 14px;
        }

        .breakdown-item.total strong {
            color: #8B6F47;
        }

        .footer {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 40px;
            margin-top: 40px;
            padding-top: 20px;
            border-top: 1px solid #ddd;
        }

        .signature {
            text-align: center;
        }

        .signature p {
            font-size: 12px;
            color: #666;
            margin-bottom: 50px;
        }

        .signature-line {
            border-top: 1px solid #333;
            margin-top: 10px;
            padding-top: 5px;
            font-size: 12px;
            font-weight: bold;
        }

        .notes {
            margin-top: 30px;
            padding: 15px;
            background-color: #fffbea;
            border-left: 4px solid #ffc107;
            font-size: 12px;
            color: #666;
        }

        .notes strong {
            display: block;
            margin-bottom: 5px;
            color: #333;
        }

        @media print {
            body {
                background-color: white;
                padding: 0;
            }

            .container {
                box-shadow: none;
                max-width: 100%;
            }

            .no-print {
                display: none;
            }
        }

        .print-button {
            text-align: center;
            margin-bottom: 20px;
        }

        .print-button button {
            padding: 10px 20px;
            background-color: #8B6F47;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 14px;
        }

        .print-button button:hover {
            background-color: #6d5835;
        }
    </style>
</head>
<body>
    <div class="print-button no-print">
        <button onclick="window.print()">
            <i class="fas fa-print"></i> Cetak Slip Gaji
        </button>
    </div>

    <div class="container">
        <!-- Header -->
        <div class="header">
            <h1>SLIP GAJI</h1>
            <p>Periode: {{ ['', 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 
                'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'][$penggajian->periode_bulan] }}
                {{ $penggajian->periode_tahun }}</p>
        </div>

        <!-- Employee Information -->
        <div class="employee-info">
            <div class="info-item">
                <strong>Nama Karyawan</strong>
                <span>{{ $penggajian->pegawai->nama }}</span>
            </div>
            <div class="info-item">
                <strong>Kode Pegawai</strong>
                <span>{{ $penggajian->pegawai->kode_pegawai }}</span>
            </div>
            <div class="info-item">
                <strong>Jabatan</strong>
                <span>{{ $penggajian->pegawai->jabatan->nama_jabatan ?? '-' }}</span>
            </div>
            <div class="info-item">
                <strong>Departemen</strong>
                <span>{{ $penggajian->pegawai->departemen->nama_departemen ?? '-' }}</span>
            </div>
            <div class="info-item">
                <strong>Tanggal Penggajian</strong>
                <span>{{ $penggajian->tanggal_penggajian->format('d/m/Y') }}</span>
            </div>
            <div class="info-item">
                <strong>Metode Pembayaran</strong>
                <span>{{ ucfirst(str_replace('_', ' ', $penggajian->metode_pembayaran)) }}</span>
            </div>
        </div>

        <!-- Attendance Summary -->
        <div class="section">
            <div class="section-title">Ringkasan Kehadiran</div>
            <div class="attendance-summary">
                <div class="attendance-box">
                    <h3>{{ $penggajian->total_hari_hadir }}</h3>
                    <p>Hari Hadir</p>
                </div>
                <div class="attendance-box">
                    <h3>{{ $penggajian->total_alpha }}</h3>
                    <p>Hari Alpha</p>
                </div>
                <div class="attendance-box">
                    <h3>{{ number_format($penggajian->total_jam, 2) }}</h3>
                    <p>Total Jam Kerja</p>
                </div>
                <div class="attendance-box">
                    <h3>Rp {{ number_format($penggajian->tarif_per_jam, 0, ',', '.') }}</h3>
                    <p>Tarif/Jam</p>
                </div>
            </div>
        </div>

        <!-- Salary Breakdown -->
        <div class="section">
            <div class="section-title">Rincian Gaji</div>
            <table class="salary-table">
                <thead>
                    <tr>
                        <th>Keterangan</th>
                        <th class="text-right">Jumlah</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>Gaji Pokok ({{ number_format($penggajian->total_jam, 2) }} jam × Rp {{ number_format($penggajian->tarif_per_jam, 0, ',', '.') }})</td>
                        <td class="text-right amount">Rp {{ number_format($breakdown['gaji_pokok'], 0, ',', '.') }}</td>
                    </tr>
                    <tr>
                        <td>Tunjangan</td>
                        <td class="text-right amount">Rp {{ number_format($breakdown['tunjangan'], 0, ',', '.') }}</td>
                    </tr>
                    <tr>
                        <td>Bonus</td>
                        <td class="text-right amount">Rp {{ number_format($breakdown['bonus'], 0, ',', '.') }}</td>
                    </tr>
                    <tr>
                        <td>Potongan</td>
                        <td class="text-right amount">Rp {{ number_format($breakdown['potongan'], 0, ',', '.') }}</td>
                    </tr>
                    <tr class="total-row">
                        <td>TOTAL GAJI BERSIH</td>
                        <td class="text-right">Rp {{ number_format($breakdown['total'], 0, ',', '.') }}</td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Signature -->
        <div class="footer">
            <div class="signature">
                <p>Disetujui oleh,</p>
                <div class="signature-line">
                    Kepala Departemen
                </div>
            </div>
            <div class="signature">
                <p>Diterima oleh,</p>
                <div class="signature-line">
                    {{ $penggajian->pegawai->nama }}
                </div>
            </div>
        </div>

        <!-- Notes -->
        <div class="notes">
            <strong>Catatan Penting:</strong>
            <ul style="margin-left: 20px; margin-top: 5px;">
                <li>Gaji dihitung berdasarkan total jam kerja aktual dari presensi harian</li>
                <li>Silakan verifikasi data sebelum penandatanganan</li>
                <li>Simpan slip gaji ini sebagai bukti pembayaran</li>
            </ul>
        </div>
    </div>
</body>
</html>
